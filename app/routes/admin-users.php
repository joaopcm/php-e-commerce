<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;

/**
 * Lista de todos os usuários - GET
 */
$app->get('/usuarios', function() {
    User::verifyLogin();
    $search = (isset($_GET['search'])) ? $_GET['search'] : '';
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    if ($search != '') {
        $pagination = User::getPageSearch($search, $page);
    } else {
        $pagination = User::getPage($page);
    }
    $pages = array();
    for ($i = 0; $i < $pagination['pages']; $i++) { 
        array_push($pages, array(
            'href' => '/admin/usuarios?' . http_build_query(array(
                'page' => $i + 1,
                'search' => $search
            )),
            'text' => $i + 1
        ));    
    }
    $page = new PageAdmin();
    $page->setTpl('users', array(
        'users' => $pagination['data'],
        'count' => count($pagination['data']),
        'search' => $search,
        'pages' => $pages
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
    $user->setData($_POST);
    $user->save();
    header("Location: /admin/usuarios");
    exit;
});

/**
 * Rota de edição de usuários - POST
 */
$app->post('/usuarios/:id', function($id) {
    User::verifyLogin();
    $user = new User();
    $user->get((int)$id);
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    $user->setData($_POST);
    $user->update();
    header('Location: /admin/usuarios');
    exit;
});

/**
 * Página de alterar senha de usuários - GET
 */
$app->get('/usuarios/:iduser/senha', function($iduser) {
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $page = new PageAdmin();
    $page->setTpl('users-password', array(
        'user' => $user->getValues(),
        'msgError' => User::getError(),
        'msgSuccess' => User::getSuccess()
    ));
});

/**
 * Rota de alterar senha de usuários - POST
 */
$app->post('/usuarios/:iduser/senha', function($iduser) {
    User::verifyLogin();
    if (!isset($_POST['despassword']) || $_POST['despassword'] === '') {
        User::setError('Preencha a nova senha.');
        header("Location: /admin/usuarios/$iduser/senha");
        exit;
    }
    if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === '') {
        User::setError('Confirme a nova senha.');
        header("Location: /admin/usuarios/$iduser/senha");
        exit;
    }
    if ($_POST['despassword'] !== $_POST['despassword-confirm']) {
        User::setError('As senhas não conferem.');
        header("Location: /admin/usuarios/$iduser/senha");
        exit;
    }
    $user = new User();
    $user->get((int)$iduser);
    $user->setPassword(User::getPasswordHash($_POST['despassword']));
    User::setSuccess('Senha alterada com sucesso!');
    header("Location: /admin/usuarios/$iduser/senha");
    exit;
});