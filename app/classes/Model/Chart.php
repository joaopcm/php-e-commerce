<?php

namespace Loja\Model;

use \Loja\DB\Sql;

class Chart {

    /**
     * Retorna a quantidade de usuários cadastrados nos útlimos 4 meses
     */
    public static function getUsersLastFourMonths()
    {
        $sql = new Sql();
        $query = "";
        return $sql->select($query);
    }

    /**
     * Retorna a quantidade de pedidos cadastrados nos útlimos 4 meses
     */
    public static function getOrdersLastFourMonths()
    {
        $sql = new Sql();
        $query = "";
        return $sql->select($query);
    }

    /**
     * Retorna o valor financeiro dos últimos 4 meses
     */
    public static function getProfitLastFourMonths()
    {
        $sql = new Sql();
        $query = "SELECT
                    (SELECT IFNULL(SUM(vltotal), 0) FROM tb_orders WHERE idstatus = 3 AND dtregister BETWEEN CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 3 MONTH)),'-01') AND CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 0 MONTH)),'-31')) AS 'thisMonth',
                    (SELECT IFNULL(SUM(vltotal), 0) FROM tb_orders WHERE idstatus = 3 AND dtregister BETWEEN CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)),'-01') AND CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)),'-31')) AS 'lastMonth',
                    (SELECT IFNULL(SUM(vltotal), 0) FROM tb_orders WHERE idstatus = 3 AND dtregister BETWEEN CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 2 MONTH)),'-01') AND CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 2 MONTH)),'-31')) AS 'twoMonthsAgo',
                    (SELECT IFNULL(SUM(vltotal), 0) FROM tb_orders WHERE idstatus = 3 AND dtregister BETWEEN CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 3 MONTH)),'-01') AND CONCAT(YEAR(NOW()),'-', MONTH(DATE_SUB(NOW(), INTERVAL 3 MONTH)),'-31')) AS 'threeMonthsAgo'
                LIMIT 1";
        return $sql->select($query);
    }

    /**
     * Retorna os status de todos os pedidos
     */
    public static function getOrdersStatus()
    {
        $sql = new Sql();
        $query = "SELECT
                    (SELECT IFNULL(COUNT(idorder), 0) FROM tb_orders WHERE idstatus = 1) AS 'aberto',
                    (SELECT IFNULL(COUNT(idorder), 0) FROM tb_orders WHERE idstatus = 2) AS 'aguardando',
                    (SELECT IFNULL(COUNT(idorder), 0) FROM tb_orders WHERE idstatus = 3) AS 'pago',
                    (SELECT IFNULL(COUNT(idorder), 0) FROM tb_orders WHERE idstatus = 4) AS 'entregue'";
        return $sql->select($query);
    }

}

?>