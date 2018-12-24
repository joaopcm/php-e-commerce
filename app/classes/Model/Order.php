<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;
use \Loja\Model\Cart;

class Order extends Model {

    const SUCCESS = 'OrderSuccess';
    const ERROR = 'OrderError';

    /**
     * Salva informações no banco de dados
     */
    public function save()
    {		
		$sql = new Sql();
		$results = $sql->select('CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)', array(		
			':idorder'=>$this->getidorder(),
			':idcart'=>$this->getidcart(),
			':iduser'=>$this->getiduser(),
			':idstatus'=>$this->getidstatus(),
			':idaddress'=>$this->getidaddress(),
            ':vltotal'=>$this->getvltotal()
        ));
		if (count($results) > 0) {
			$this->setData($results[0]);
		}
    }

    /**
     * Retorna informações do banco de dados
     */
    public function get($idorder)
    {
        $sql = new Sql();
		$results = $sql->select('SELECT * 
                                    FROM tb_orders a 
                                    INNER JOIN tb_ordersstatus b USING(idstatus) 
                                    INNER JOIN tb_carts c USING(idcart)
                                    INNER JOIN tb_users d ON d.iduser = a.iduser
                                    INNER JOIN tb_addresses e USING(idaddress)
                                    INNER JOIN tb_persons f ON f.idperson = d.idperson
                                    WHERE a.idorder = :idorder
                                ', array(
                                    ':idorder'=>$idorder
                                ));
		if (count($results) > 0) {
			$this->setData($results[0]);
		}
    }

    /**
     * Retorna todos os pedidos
     */
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select('SELECT * 
                                FROM tb_orders a 
                                INNER JOIN tb_ordersstatus b USING(idstatus) 
                                INNER JOIN tb_carts c USING(idcart)
                                INNER JOIN tb_users d ON d.iduser = a.iduser
                                INNER JOIN tb_addresses e USING(idaddress)
                                INNER JOIN tb_persons f ON f.idperson = d.idperson
                                ORDER BY a.dtregister DESC
                            ');
    }

    /**
     * Deleta um pedido do banco de dados
     */
    public function delete()
    {
        $sql = new Sql();
        $sql->query('DELETE FROM tb_orders WHERE idorder = :idorder', array(
            ':idorder' => $this->getidorder()
        ));
    }

    /**
     * Recupera o carrinho do pedido
     */
    public function getCart(): Cart
    {
        $cart = new Cart();
        $cart->get((int)$this->getidcart());
        return $cart;
    }

    /**
     * Define uma mensagem
     */
    public static function setSuccess($msg)
    {
        $_SESSION[Order::SUCCESS] = $msg;
    }
    
    /**
     * Retorna uma mensagem
     */
    public static function getSuccess()
    {
        $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';
        Order::clearSuccess();
        return $msg;
    }

    /**
     * Limpa uma mensagem
     */
    public static function clearSuccess()
    {
        $_SESSION[Order::SUCCESS] = NULL;
    }

        /**
     * Define um erro de usuário ao tentar logar-se
     */
    public static function setError($msg)
    {
        $_SESSION[Order::ERROR] = $msg;
    }
    
    /**
     * Retorna o erro do usuário ao tentar logar-se
     */
    public static function getError()
    {
        $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : '';
        Order::clearError();
        return $msg;
    }

    /**
     * Limpa o erro do usuário
     */
    public static function clearError()
    {
        $_SESSION[Order::ERROR] = NULL;
    }

    /**
     * Organiza a paginação
     */
    public static function getPage(int $page = 1, int $itemsPerPage = 25)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
                                    FROM tb_orders a 
                                    INNER JOIN tb_ordersstatus b USING(idstatus) 
                                    INNER JOIN tb_carts c USING(idcart)
                                    INNER JOIN tb_users d ON d.iduser = a.iduser
                                    INNER JOIN tb_addresses e USING(idaddress)
                                    INNER JOIN tb_persons f ON f.idperson = d.idperson
                                    ORDER BY a.dtregister DESC LIMIT $start, $itemsPerPage");
        $resultTotal = $sql->select('SELECT FOUND_ROWS() AS nrtotal;');
        return array(
            'data' => $results,
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
        );
    }

    /**
     * Organiza a paginação e busca
     */
    public static function getPageSearch($search, int $page = 1, int $itemsPerPage = 25)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
                                    FROM tb_orders a
                                    INNER JOIN tb_ordersstatus b USING(idstatus) 
                                    INNER JOIN tb_carts c USING(idcart)
                                    INNER JOIN tb_users d ON d.iduser = a.iduser
                                    INNER JOIN tb_addresses e USING(idaddress)
                                    INNER JOIN tb_persons f ON f.idperson = d.idperson
                                    WHERE a.idorder = :id
                                    OR f.desperson LIKE :search
                                    OR e.desaddress LIKE :search
                                    OR e.desdistrict LIKE :search
                                    OR e.descity LIKE :search
                                    OR e.desstate LIKE :search
                                    OR e.descountry LIKE :search
                                    ORDER BY a.dtregister DESC
                                    LIMIT $start, $itemsPerPage", array(
                                        ':search' => '%' . $search . '%',
                                        ':id' => $search
                                    ));
        $resultTotal = $sql->select('SELECT FOUND_ROWS() AS nrtotal;');
        return array(
            'data' => $results,
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
        );
    }

}

?>