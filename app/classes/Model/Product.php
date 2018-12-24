<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;

class Product extends Model {

    /**
     * Retorna todas os produtos cadastrados
     */
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select('SELECT * FROM tb_products ORDER BY desproduct');
    }

    /**
     * Faz uma checagem na lista retornada
     */
    public static function checkList($list)
    {
        foreach ($list as &$row) {
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }

    /**
     * Cadastra um produto no banco de dados
     */
    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :deslitledescription, :desdescription, :desurl)', array(
            ':idproduct' => $this->getidproduct(),
            ':desproduct' => $this->getdesproduct(),
            ':vlprice' => $this->getvlprice(),
            ':vlwidth' => $this->getvlwidth(),
            ':vlheight' => $this->getvlheight(),
            ':vllength' => $this->getvllength(),
            ':vlweight' => $this->getvlweight(),
            ':deslitledescription' => $this->getdeslitledescription(),
            ':desdescription' => $this->getdesdescription(),
            ':desurl' => $this->getdesurl()
        ));
        $this->setData($results[0]);
    }

    /**
     * Retorna os dados de uma categoria pelo ID
     */
    public function get($idproduct)
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_products WHERE idproduct = :idproduct', array(
            ':idproduct' => $idproduct
        ));
        $this->setData($results[0]);
    }

    /**
     * Deleta um cadastro de produtos no banco de dados
     */
    public function delete()
    {
        $sql = new Sql();
        $sql->select('DELETE FROM tb_productscategories WHERE idproduct = :idproduct', array(
            ':idproduct' => $this->getidproduct()
        ));
        $sql->select('DELETE FROM tb_cartsproducts WHERE idproduct = :idproduct', array(
            ':idproduct' => $this->getidproduct()
        ));
        $sql->select('DELETE FROM tb_products WHERE idproduct = :idproduct', array(
            ':idproduct' => $this->getidproduct()
        ));
    }

    /**
     * Verifica a existência da foto
     */
    public function checkPhoto()
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $this->getidproduct() . '.jpg'))
        {
            $url = '/res/site/images/products/' . $this->getidproduct() . '.jpg';
        } else {
            $url = '/res/site/images/product.jpg';
        }
        return $this->setdesphoto($url);
    }

    /**
     * Sobrescreve a função pai com algumas alterações
     */
    public function getValues()
    {
        $this->checkPhoto();
        $values = parent::getValues();
        return $values;
    }

    /**
     * Move a foto adicionada para o diretório de imagens de produtos
     */
    public function setPhoto($file)
    {
        $extension = explode('.', $file['name']);
        $extension = end($extension);
        switch ($extension) {
            case 'jpg':
                $image = \imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'jpeg':
                $image = \imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'gif':
                $image = \imagecreatefromgif($file['tmp_name']);
                break;
            case 'png':
                $image = \imagecreatefrompng($file['tmp_name']);
                break;
        }
        $dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $this->getidproduct() . '.jpg';
        imagejpeg($image, $dist);
        imagedestroy($image);
        $this->checkPhoto();
    }

    /**
     * Retorna dados de um produto de acordo com a URL passada
     */
    public function getFromURL(string $desurl)
    {
        $sql = new Sql();
        $rows = $sql->select('SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1', array(
            ':desurl' => $desurl
        ));
        $this->setData($rows[0]);
    }

    /**
     * Retorna todas as categorias de um produto
     */
    public function getCategories()
    {
        $sql = new Sql();
        return $sql->select('SELECT * FROM tb_categories a INNER JOIN tb_productscategories b USING (idcategory) WHERE b.idproduct = :idproduct', array(
            ':idproduct' => $this->getidproduct()
        ));
    }

    /**
     * Organiza a paginação
     */
    public static function getPage(int $page = 1, int $itemsPerPage = 25)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * FROM tb_products ORDER BY desproduct LIMIT $start, $itemsPerPage");
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
                                    FROM tb_products
                                    WHERE desproduct LIKE :search
                                    OR vlprice LIKE :search
                                    ORDER BY desproduct
                                    LIMIT $start, $itemsPerPage", array(
                                        ':search' => '%' . $search . '%'
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