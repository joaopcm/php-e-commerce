<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;
use \Loja\Model\Product;
use \Loja\Model\User;

class Cart extends Model {

    const SESSION = 'Cart';

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
    }

    /**
     * 
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

}

?>