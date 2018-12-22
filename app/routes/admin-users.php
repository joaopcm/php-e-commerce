<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;

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