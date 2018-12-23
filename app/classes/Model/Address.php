<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;

class Address extends Model {

    protected $fields = [
    "idaddress", "idperson", "desaddress", 'descomplement', 'desdistrict', 'descity', 'desstate', 'descountry', 'desnumber', 'deszipcode'
    ];

    const SESSION_ERROR = 'AddressError';

    /**
     * Retorna dados de localização de acordo com o CEP
     */
    public static function getCep($nrcep)
    {
        $nrcep = str_replace('-', '', $nrcep);
        $ch = curl_init();
        \curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$nrcep/json/");
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $data;
    }

    /**
     * Define os dados de localização de acordo com o CEP
     */
    public function loadFromCEP($nrcep)
    {
        $data = Address::getCEP($nrcep);
        if (isset($data['logradouro']) && $data['logradouro']) {
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($nrcep);
        }
    }

    /**
     * Salva um endereço no banco de dados
     */
    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)', array(
            ':idaddress' => $this->getidaddress(),
            ':idperson' => $this->getidperson(),
            ':desaddress' => $this->getdesaddress(),
            ':descomplement' => $this->getdescomplement(),
            ':descity' => $this->getdescity(),
            ':desstate' => $this->getdesstate(),
            ':descountry' => $this->getdescountry(),
            ':deszipcode' => $this->getdeszipcode(),
            ':desdistrict' => $this->getdesdistrict()
        ));
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    /**
     * Define um erro na sessão
     */
    public static function setMsgError(string $msg)
    {
        $_SESSION[Address::SESSION_ERROR] = $msg;
    }

    /**
     * Recupera e limpa um erro na sessão
     */
    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : '';
        Address::clearMsgError();
        return $msg;
    }

    /**
     * Limpa um erro na sessão
     */
    public static function clearMsgError()
    {
        $_SESSION[Address::SESSION_ERROR] = NULL;
    }

}

?>