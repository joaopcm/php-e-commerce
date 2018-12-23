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

}

?>