<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;

class Category extends Model {

    /**
     * Retorna todas as categorias cadastradas
     */
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select('SELECT * FROM tb_categories ORDER BY descategory');
    }

    /**
     * Cadastra uma categoria no banco de dados
     */
    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_categories_save(:idcategory, :descategory)', array(
            ':idcategory' => $this->getidcategory(),
            ':descategory' => $this->getdescategory()
        ));
        $this->setData($results[0]);
    }

    /**
     * Retorna os dados de uma categoria pelo ID
     */
    public function get($idcategory)
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_categories WHERE idcategory = :idcategory', array(
            ':idcategory' => $idcategory
        ));
        $this->setData($results[0]);
    }

    /**
     * Deleta um cadastro de categoria no banco de dados
     */
    public function delete()
    {
        $sql = new Sql();
        $sql->select('DELETE FROM tb_categories WHERE idcategory = :idcategory', array(
            ':idcategory' => $this->getidcategory()
        ));
    }

}

?>