<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;

class User extends Model {

    const SESSION = 'User';

    /**
     * Efetua o login de um usuário
     */
    public static function login($username, $password)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ':LOGIN' => $username
        ));
        if (count($results) === 0)
        {
            throw new \Exception('Usuário inexistente ou senha inválida.');
        }
        $data = $results[0];
        if (password_verify($password, $data['despassword']))
        {
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] = $user->getValues();
        } else {
            throw new \Exception('Usuário inexistente ou senha inválida.');
        }
    }

    /**
     * Verifica a existência de um usuário logado
     */
    public static function verifyLogin($inadmin = true)
    {
        if (
            !isset($_SESSION[User::SESSION]) ||
            !$_SESSION[User::SESSION] ||
            !(int)$_SESSION[User::SESSION]['iduser'] > 0 ||
            (bool)$_SESSION[User::SESSION]['inadmin'] !== $inadmin)
        {
            header('Location: /admin/login');
            exit;
        }
    }

    /**
     * Efetua o logout de um usuário
     */
    public static function logout()
    {
        $_SESSION[User::SESSION] = NULL;
    }

    /**
     * Retorna todos os usuários cadastrados
     */
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select('SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson');
    } 

    /**
     * Cadastra um usuário no banco de dados
     */
    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)', array(
            ':desperson' => $this->getdesperson(),
            ':deslogin' => $this->getdeslogin(),
            ':despassword' => $this->getdespassword(),
            ':desemail' => $this->getdesemail(),
            ':nrphone' => $this->getnrphone(),
            ':inadmin' => $this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    /**
     * Retorna os dados de um usuário pelo ID
     */
    public function get($id)
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser', array(
            ':iduser' => $id
        ));
        $this->setData($results[0]);
    }

    /**
     * Atualiza o cadastro de um usuário no banco de dados
     */
    public function update()
    {
        $sql = new Sql();
        $results = $sql->select('CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)', array(
            ':iduser' => $this->getiduser(),
            ':desperson' => $this->getdesperson(),
            ':deslogin' => $this->getdeslogin(),
            ':despassword' => $this->getdespassword(),
            ':desemail' => $this->getdesemail(),
            ':nrphone' => $this->getnrphone(),
            ':inadmin' => $this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    /**
     * Deleta um usuário no banco de dados
     */
    public function delete()
    {
        $sql = new Sql();
        $sql->select('CALL sp_users_delete(:iduser)', array(
            ':iduser' => $this->getiduser()
        ));
    }

}

?>