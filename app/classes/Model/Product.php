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
        $results = $sql->select('CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)', array(
            ':idproduct' => $this->getidproduct(),
            ':desproduct' => $this->getdesproduct(),
            ':vlprice' => $this->getvlprice(),
            ':vlwidth' => $this->getvlwidth(),
            ':vlheight' => $this->getvlheight(),
            ':vllength' => $this->getvllength(),
            ':vlweight' => $this->getvlweight(),
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

}

?>