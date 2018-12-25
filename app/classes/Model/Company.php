<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;

class Company extends Model {

    /**
     * Retorna as informações atuais da empresa
     */
    public function getCurrentValues()
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_company WHERE idcompany = 1');
        if (count($results) > 0) {
            $this->setData($results[0]);
            return $results[0];
        }
    }

    /**
     * Salva as alterações no banco de dados
     */
    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_company_save(:idcompany, :descompany, :desslogan, :desaddress, :desdistrict, :descity, :desstate, :deszipcode, :nrphone, :desemail, :descnpj)', array(
            ':idcompany' => 1,
            ':descompany' => $this->getdescompany(),
            ':desslogan' => $this->getdesslogan(),
            ':desaddress' => $this->getdesaddress(),
            ':desdistrict' => $this->getdesdistrict(),
            ':descity' => $this->getdescity(),
            ':desstate' => $this->getdesstate(),
            ':deszipcode' => $this->getdeszipcode(),
            ':nrphone' => $this->getnrphone(),
            ':desemail' => $this->getdesemail(),
            ':descnpj' => $this->getdescnpj()
        ));
        if (count($results) > 0) {
            $this->setData($results[0]);
            Company::updateFile();
        }
    }

    /**
     * Atualiza o arquivo de menu de categorias
     */
    public static function updateFile()
    {
        $company = new Company();
        $info = $company->getCurrentValues();
        $html = [];
        $prefix = 'company_';
        foreach ($info as $key => $value) {
            array_push($html, '{$company_' . $key . "='" . $value . "'}");
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'company-variables.html', implode('', $html));
    }

}

?>