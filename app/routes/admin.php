<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;

/**
 * Página principal administrativa - GET
 */
$app->get('/', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('index');
});

/**
 * Página de login - GET
 */
$app->get('/login', function() {
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('login');
});

/**
 * Rota de login - POST
 */
$app->post('/login', function() {
    User::login($_POST['username'], $_POST['password']);
    header('Location: /admin');
    exit;
});

/**
 * Rota de logout - GET
 */
$app->get('/logout', function() {
    User::logout();
    header('Location: /admin/login');
    exit;
});

/**
 * Página de recuperação de senha - GET
 */
$app->get('/forgot', function() {
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('forgot');
});

/**
 * Rota de recuperação de senha - POST
 */
$app->post('/forgot', function() {
    $user = User::getForgot($_POST['email']);
    header('Location: /admin/forgot/sent');
    exit;
});

/**
 * Página de confirmação de envio para recuperação de senha - GET
 */
$app->get('/forgot/sent', function() {
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('forgot-sent');
});

/**
 * Página de resetar a senha - GET
 */
$app->get('/forgot/reset', function() {
    $user = User::validForgotDecrypt($_GET['code']);
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('forgot-reset', array(
        'name' => $user['desperson'],
        'code' => $_GET['code']
    ));
});

/**
 * Rota de redefinição de senha - POST
 */
$app->post('/forgot/reset', function() {
    $forgot = User::validForgotDecrypt($_POST['code']);
    User::setForgotUsed($forgot['idrecovery']);
    $user = new User();
    $user->get((int)$forgot['iduser']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT, [
        'cost' => 12
    ]);
    $user->setPassword($password);
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('forgot-reset-success');
});