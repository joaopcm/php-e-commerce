<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Category;
use \Loja\Model\Product;
use \Loja\Model\Order;
use \Loja\Model\Chart;

/**
 * Página principal administrativa - GET
 */
$app->get('/', function () {
    Chart::getProfitLastFourMonths();
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('index', array(
        'users' => count(User::listAll()),
        'categories' => count(Category::listAll()),
        'products' => count(Product::listAll()),
        'orders' => count(Order::listAll()),
        'chartProfit' => Chart::getProfitLastFourMonths(),
        'chartStatus' => Chart::getOrdersStatus()
    ));
});

/**
 * Página de login - GET
 */
$app->get('/login', function() {
    if (
        isset($_SESSION[User::SESSION])
        ||
        $_SESSION[User::SESSION]
        ||
        (int)$_SESSION[User::SESSION]['iduser'] > 0
        ||
        (bool)$_SESSION[User::SESSION]['inadmin'] === true
    ) {
        header('Location: /admin');
        exit;
    }
    $page = new PageAdmin(array(
        'header' => false,
        'footer' => false
    ));
    $page->setTpl('login', array(
        'error' => User::getError()
    ));
});

/**
 * Rota de login - POST
 */
$app->post('/login', function() {
    try {
        User::login($_POST['username'], $_POST['password']);
    } catch(Exception $e) {
        User::setError($e->getMessage());
    }
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

/**
 * Gera o usuário administrador padrão
 */
$app->get('/auth/generate/admin-user', function() {
    if (!User::checkAdminUsers()) {
        $page = new PageAdmin(array(
            'header' => false,
            'footer' => false
        ));
        $page->setTpl('new-admin-user', array(
            'username' => 'user.admin',
            'password' => User::generateDefaulAdminUser()
        ));
    } else {
        header('Location: /admin');
        exit;
    }
});

/**
 * Limpa o banco de dados - POR QUESTÕES DE SEGURANÇA, ESSA ROTA ESTÁ DESABILITADA
 */
// $app->get('/database/truncate/all', function() {
//     User::verifyLogin();
//     if (PRODUCTION_MODE === 'true') {
//         User::truncateAll();
//         echo 'Você resetou o banco de dados.';
//     }
// });