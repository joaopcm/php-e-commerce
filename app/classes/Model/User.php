<?php

namespace Loja\Model;

use \Loja\DB\Sql;
use \Loja\Model\Model;

class User extends Model {

    const SESSION = 'User';
    const SECRET = 'developSecretKey';
    const ERROR = 'UserError';
    const ERROR_REGISTER = 'UserErrorRegister';
    const SUCCESS = 'UserSuccess';

    /**
     * Efetua o login de um usuário
     */
    public static function login($username, $password)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b
                                    ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
                                    ":LOGIN" => $username
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
        $isLogged = User::checkLogin($inadmin);
        if ($isLogged === false) {
            if ($inadmin) {
                header("Location: /admin/login");
            } else {
                header("Location: /login");
            }
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
        if ($this->getnrphone() == '') {
            $this->setnrphone(NULL);
        }
        $results = $sql->select('CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)', array(
            ':desperson' => $this->getdesperson(),
            ':deslogin' => $this->getdeslogin(),
            ':despassword' => User::getPasswordHash($this->getdespassword()),
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
        $data = $results[0];
        $this->setData($data);
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
            ':despassword' => User::getPasswordHash($this->getdespassword()),
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

    /**
     * Inicia o método de recuperação de senha
     */
    public static function getForgot($email, $inadmin = true)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email", array(
            ":email"=>$email
        ));
        if (count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser" => $data['iduser'],
                ":desip" => $_SERVER['REMOTE_ADDR']
            ));
            if (count($results2) === 0)
            {
                throw new \Exception('Não foi possível recuperar a senha.');
            } else {
                $dataRecovery = $results2[0];
                $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
                $result = base64_encode($iv.$code);
                if ($inadmin === true) {
                    $link = BASE_URL . "admin/forgot/reset?code=$result";
                } else {
                    $link = BASE_URL . "forgot/reset?code=$result";
                } 
                $mailer = new Mailer($data['desemail'], $data['desperson'], 'Redefinir senha da ' . COMPANY_NAME, 'forgot', array(
                    'name' => $data['desperson'],
                    'link' => $link,
                    'company' => COMPANY_NAME
                )); 
                $mailer->send();
                return $link;
            }
        }
    }

    /**
     * Descriptografa o código passado
     */
    public static function validForgotDecrypt($result)
    {
        $result = base64_decode($result);
        $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
        $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;
        $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_userspasswordsrecoveries a INNER JOIN tb_users b USING(iduser) INNER JOIN tb_persons c USING(idperson) WHERE a.idrecovery = :idrecovery AND a.dtrecovery IS NULL AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()', array(
            ':idrecovery' => $idrecovery
        ));
        if (count($results) === 0)
        {
            throw new \Exception('Não foi possível recuperar a senha.');
        } else {
            return $results[0];
        }
    }

    /**
     * Define um código de recuperação como usado
     */
    public static function setForgotUsed($idrecovery)
    {
        $sql = new Sql();
        $sql->select('UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery', array(
            ':idrecovery' => $idrecovery
        ));
    }

    /**
     * Atualiza a senha de um usuário
     */
    public function setPassword($password)
    {
        $sql = new Sql();
        $sql->select('UPDATE tb_users SET despassword = :password WHERE iduser = :iduser', array(
            ':password' => $password,
            ':iduser' => $this->getiduser()
        ));
    }

    /**
     * Verifica se a sessão existe
     */
    public static function getFromSession()
    {
        $user = new User();
        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {
            $user->setData($_SESSION[User::SESSION]);
        }
        return $user;
    }

    /**
     * Verifica se o usuário está logado
     */
    public static function checkLogin($inadmin = true)
    {
        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
         ) {
             return false;
         } else {
            if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
                return true;    
            } else if ($inadmin === false) {
                return true;
            } else {
                return false;
            }
         }
    }

    /**
     * Define um erro de usuário ao tentar logar-se
     */
    public static function setError($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }
    
    /**
     * Retorna o erro do usuário ao tentar logar-se
     */
    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
        User::clearError();
        return $msg;
    }

    /**
     * Limpa o erro do usuário
     */
    public static function clearError()
    {
        $_SESSION[User::ERROR] = NULL;
    }

    /**
     * Criptografa a senha
     */
    public static function getPasswordHash($password)
    {
        return \password_hash($password, PASSWORD_DEFAULT, array(
            'cost' => 12
        ));
    }

    /**
     * Define um erro de usuário ao tentar cadastrar-se
     */
    public static function setErrorRegister($msg)
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }
    
    /**
     * Retorna o erro do usuário ao tentar cadastrar-se
     */
    public static function getErrorRegister()
    {
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
        User::clearErrorRegister();
        return $msg;
    }

    /**
     * Limpa o erro do usuário
     */
    public static function clearErrorRegister()
    {
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }

    /**
     * Verifica se um usuário já existe
     */
    public static function checkLoginExists($login)
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_users WHERE deslogin = :deslogin', array(
            ':deslogin' => $login
        ));
        return (count($results) > 0);
    }

    /**
     * Define uma mensagem
     */
    public static function setSuccess($msg)
    {
        $_SESSION[User::SUCCESS] = $msg;
    }
    
    /**
     * Retorna uma mensagem
     */
    public static function getSuccess()
    {
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
        User::clearSuccess();
        return $msg;
    }

    /**
     * Limpa uma mensagem
     */
    public static function clearSuccess()
    {
        $_SESSION[User::SUCCESS] = NULL;
    }

    /**
     * Retorna o usuário logado na sessão
     */
    public function getOrders()
    {
        $sql = new Sql();
		$results = $sql->select('SELECT * 
                                    FROM tb_orders a 
                                    INNER JOIN tb_ordersstatus b USING(idstatus) 
                                    INNER JOIN tb_carts c USING(idcart)
                                    INNER JOIN tb_users d ON d.iduser = a.iduser
                                    INNER JOIN tb_addresses e USING(idaddress)
                                    INNER JOIN tb_persons f ON f.idperson = d.idperson
                                    WHERE a.iduser = :iduser
                                ', array(
                                    ':iduser' => $this->getiduser()
                                ));
        return $results;
    }

}

?>