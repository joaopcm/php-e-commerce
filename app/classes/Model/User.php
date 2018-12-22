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

}

?>