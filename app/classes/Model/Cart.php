<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;
use \Loja\Model\Product;
use \Loja\Model\User;

class Cart extends Model {

    const SESSION = 'Cart';
    const SESSION_ERROR = "CartError";

    /**
     * Verifica a existência de um carrinho na atual sessão
     */
    public static function getFromSession()
    {
        $cart = new Cart();
        if (isset($_SESSION[Cart::SESSION]) && $_SESSION[Cart::SESSION]['idcart'] > 0)
        {
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        } else {
            $cart->getFromSessionID();
            if (!(int)$cart->getidcart() > 0) {
                $data = array(
                    'dessessionid' => session_id()
                );
                if (User::checkLogin(false)) {
                    $user = User::getFromSession();
                    $data['iduser'] = $user->getiduser();
                }
                $cart->setData($data);
                $cart->save();
                $cart->setToSession();
            }
        }
        return $cart;
    }

    /**
     * Cadastra um carrinho no banco de dados
     */
    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)', array(
            ':idcart' => $this->getidcart(),
            ':dessessionid' => $this->getdessessionid(),
            ':iduser' => $this->getiduser(),
            ':deszipcode' => $this->getdeszipcode(),
            ':vlfreight' => $this->getvlfreight(),
            ':nrdays' => $this->getnrdays()
        ));
        $this->setData($results[0]);
    }

    /**
     * Recupera o carrinho do banco de dados
     */
    public function get(int $idcart)
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_carts WHERE idcart = :idcart', array(
            ':idcart' => $idcart
        ));
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    /**
     * Recupera o carrinho pelo ID da sessão
     */
    public function getFromSessionID()
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_carts WHERE dessessionid = :dessessionid', array(
            ':dessessionid' => session_id()
        ));
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    /**
     * Salva o carrinho na sessão
     */
    public function setToSession()
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    /**
     * Adiciona um produto ao carrinho
     */
    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->select('INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)', array(
            ':idcart' => $this->getidcart(),
            ':idproduct' => $product->getidproduct()
        ));
        $this->getCalcTotal();
    }

    /**
     * Remove um ou mais produto(s) do carrinho
     */
    public function removeProduct(Product $product, $all = false)
    {
        $sql = new Sql();
        if ($all === true) {
            $sql->select('UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL', array(
                ':idcart' => $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ));
        } else {
            $sql->select('UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1', array(
                ':idcart' => $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ));
        }
        $this->getCalcTotal();
    }

    /**
     * Retorna todos os produtos do carrinho
     */
    public function getProducts()
    {
        $sql = new Sql();
        return Product::checkList($sql->select('SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
                                                FROM tb_cartsproducts a
                                                INNER JOIN tb_products b
                                                USING(idproduct)
                                                WHERE a.idcart = :idcart
                                                AND a.dtremoved IS NULL
                                                GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                                                ORDER BY b.desproduct', array(
                                                    ':idcart' => $this->getidcart()
                                                )));
    }

    /**
     * Retorna todas as somas de cada atributo de um produto
     */
    public function getProductsTotals()
    {
        $sql = new Sql();
        $results = $sql->select('SELECT
                        SUM(vlprice) AS vlprice,
                        SUM(vlwidth) AS vlwidth,
                        SUM(vlheight) AS vlheight,
                        SUM(vllength) AS vllength,
                        SUM(vlweight) AS vlweight,
                        COUNT(*) AS nrqtd
                    FROM tb_products a
                        INNER JOIN tb_cartsproducts b USING(idproduct)
                    WHERE b.idcart = :idcart AND dtremoved IS NULL', array(
                        ':idcart' => $this->getidcart()
                    ));
        if (count($results) > 0) {
            return $results[0];
        } else {
            return [];
        }
    }

    /**
     * Calcula o frete da entrega
     */
    public function setFreight(string $nrzipcode)
    {
        $nrzipcode = str_replace('-', '', $nrzipcode);
        $totals = $this->getProductsTotals();
        if ($totals['nrqtd'] > 0) {
            if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
            if ($totals['vllength'] < 16) $totals['vllength'] = 16;
            if ($totals['vlwidth'] < 11) $totals['vlwidth'] = 11;
            $qs = http_build_query(array(
                'nCdEmpresa' => COMPANY_NAME,
                'sDsSenha' => '',
                'nCdServico' => '40010',
                'sCepOrigem' => '03917050',
                'sCepDestino' => $nrzipcode,
                'nVlPeso' => $totals['vlheight'],
                'nCdFormato' => 1,
                'nVlComprimento' => $totals['vllength'],
                'nVlAltura' => $totals['vlheight'],
                'nVlLargura' => $totals['vlwidth'],
                'nVlDiametro' => 0,
                'sCdMaoPropria' => 'N',
                'nVlValorDeclarado' => $totals['vlprice'],
                'sCdAvisoRecebimento' => 'S'
            ));
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?$qs");
            $result = $xml->Servicos->cServico;
            if ($result->MsgErro != '') {
                Cart::setMsgerror($result->MsgErro);
            } else {
                Cart::clearMsgError();
            }
            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);
            $this->save();
            return $result;
        } else {

        }
    }

    /**
     * Formata o valor para o padrão americano
     */
    public static function formatValueToDecimal($value): float
    {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    /**
     * Define um erro na sessão
     */
    public static function setMsgError(string $msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    /**
     * Recupera e limpa um erro na sessão
     */
    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : '';
        Cart::clearMsgError();
        return $msg;
    }

    /**
     * Limpa um erro na sessão
     */
    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    /**
     * Atualiza o frete
     */
    public function updateFreight()
    {
        if ($this->getdeszipcode() != '') {
            $this->setFreight($this->getdeszipcode(), $this->getserivce());
        }
    }

    public function getValues()
    {
        $this->getCalcTotal();
        return parent::getValues();
    }

    /**
     * Realiza cálculos refentes a valores dos produtos no carrinho
     */
    public function getCalcTotal()
    {
        $this->updateFreight();
        $totals = $this->getProductsTotals();
        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
    }

    /**
     * Remove dados do carrinho ao fazer o logout
     */
    public static function removeFromSession(){
        $_SESSION[Cart::SESSION] = NULL;
    }

}

?>