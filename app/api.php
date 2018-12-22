<?php

/**
 *
 * Loja Virtual - Aplicação E-Commerce feita com novas tecnologias e boas práticas.
 *
 * @author João Pedro da Cruz Melo <joao.pedro6532@gmail.com>
 * @license Uso exclusivo de clientes do autor
 * @copyright 2018 | João Melo
 *
 **/

/**
 * Inicia uma sessão
 */
session_start();

/**
 * Importa arquivo de configurações
 */
require_once 'config.php';

/**
 * Namespaces
 */
use \Loja\Model\User;
use \Loja\Model\PageAdmin;
use \Loja\Model\Page;
use \Slim\Slim;

/**
 * Instância do Slim Framework
 */
PRODUCTION_MODE === 'false' ? $app = new Slim(array('mode' => 'development', 'debug' => true)) : $app = new Slim(array('debug' => false));

/**
 * Redireciona para a página principal quando a rota não é encontrada
 */
$app->notFound(function () use ($app) {
    $app->redirect('/');
});

/**
 * Página principal da loja - GET
 */
$app->get('/', function () {
    $page = new Page();
    $page->setTpl('index');
});

/**
 * Página de login - GET
 */
$app->get('/admin/login', function() {
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('login');
});

/**
 * Rota de login - POST
 */
$app->post('/admin/login', function() {
    User::login($_POST['username'], $_POST['password']);
    header('Location: /admin');
    exit;
});

/**
 * Rota de logout - GET
 */
$app->get('/admin/logout', function() {
    User::logout();
    header('Location: /admin/login');
    exit;
});

/**
 * Grupo de rotas administrativas
 */
$app->group('/admin', function () use ($app) {

    /**
     * Página principal administrativa - GET
     */
    $app->get('/', function () {
        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl('index');
    });

    /**
     * Lista de todos os usuários - GET
     */
    $app->get('/usuarios', function() {
        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl('users', array(
            'users' => User::listAll()
        ));
    });

    /**
     * Página de criação de usuários - GET
     */
    $app->get('/usuarios/cadastrar', function() {
        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl('users-create');
    });

    /**
     * Rota de exclusão de usuários - GET
     */
    $app->get('/usuarios/:id/excluir', function($id) {
        User::verifyLogin();
        $user = new User();
        $user->get((int)$id);
        $user->delete();
        header('Location: /admin/usuarios');
        exit;
    });

    /**
     * Página de edição de usuários - GET
     */
    $app->get('/usuarios/:id', function($id) {
        User::verifyLogin();
        $user = new User();
        $user->get((int)$id);
        $page = new PageAdmin();
        $page->setTpl('users-update', array(
            'user' => $user->getValues()
        ));
    });

    /**
     * Rota de criação de usuários - POST
     */
    $app->post('/usuarios/cadastrar', function() {
        User::verifyLogin();
        $user = new User();
        $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
        $_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
            "cost" => 12
        ]);
        $user->setData($_POST);
        $user->save();
        header("Location: /admin/usuarios");
        exit;
    });

    /**
     * Rota de edição de usuários - POST
     */
    $app->POST('/usuarios/:id', function($id) {
        User::verifyLogin();
        $user = new User();
        $user->get((int)$id);
        $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
        $user->setData($_POST);
        $user->update();
        header('Location: /admin/usuarios');
        exit;
    });

});

/**
 * Inicia a API
 */
$app->run();
