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
        Category::updateFile();
    }

    /**
     * Retorna os dados de uma categoria pelo ID
     */
    public function get(int $idcategory)
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
        Category::updateFile();
    }

    /**
     * Atualiza o arquivo de menu de categorias
     */
    public static function updateFile()
    {
        $categories = Category::listAll();
        $html = [];
        foreach ($categories as $row) {
            array_push($html, '
            <li class="p-b-10">
                <a href="/categoria/' . $row['idcategory'] . '" class="stext-107 cl7 hov-cl1 trans-04">
                    ' . $row['descategory'] . '
                </a>
            </li>');
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'categories-menu-footer.html', implode('', $html));
        $html = [];
        foreach ($categories as $row) {
            array_push($html, '
            <a href="/categoria/' . $row['idcategory'] . '" class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5">
                ' . $row['descategory'] . '
            </a>');
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'categories-menu-index.html', implode('', $html));
    }

    /**
     * Retorna todos os produtos relacionados a uma categoria e todos os que não estão relacionados à mesma categoria
     */
    public function getProducts(bool $related = true)
    {
        $sql = new Sql();
        if ($related === true) {
            return $sql->select('SELECT * FROM tb_products a WHERE a.idproduct IN (
                                    SELECT
                                        a.idproduct
                                    FROM tb_products a
                                        INNER JOIN tb_productscategories b
                                        ON a.idproduct = b.idproduct
                                    WHERE b.idcategory = :idcategory
                                )', array(
                                    ':idcategory' => $this->getidcategory()
                                ));
        } else {
            return $sql->select('SELECT * FROM tb_products a WHERE a.idproduct NOT IN (
                                    SELECT
                                        a.idproduct
                                    FROM tb_products a
                                        INNER JOIN tb_productscategories b
                                        ON a.idproduct = b.idproduct
                                    WHERE b.idcategory = :idcategory
                                )', array(
                                    ':idcategory' => $this->getidcategory()
                                ));
        }
    }

    /**
     * Adiciona um produto em uma categoria
     */
    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query('INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)', array(
            ':idcategory' => $this->getidcategory(),
            ':idproduct' => $product->getidproduct()
        ));
    }

    /**
     * Remove um produto de uma categoria
     */
    public function removeProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query('DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct', array(
            ':idcategory' => $this->getidcategory(),
            ':idproduct' => $product->getidproduct()
        ));
    }

}

?>