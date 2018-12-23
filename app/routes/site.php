<?php

use \Loja\Model\Page;
use \Loja\Model\Category;
use \Loja\Model\Product;
use \Loja\Model\Cart;
use \Loja\Model\Address;
use \Loja\Model\User;

/**
 * Página principal da loja - GET
 */
$app->get('/', function () {
    $page = new Page();
    $products = Product::listAll();
    $page->setTpl('index', array(
        'products' => Product::checkList($products)
    ));
});

/**
 * Página de categoria - GET
 */
$app->get('/categoria/:idcategory', function($idcategory) {
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $pages = array();
    $category = new Category();
    $category->get((int)$idcategory);
    $pagination = $category->getProductsPage($page);
    for ($i = 1; $i <= $pagination['pages']; $i++) { 
        array_push($pages, array(
            'page' => $i,
            'link' => '/categoria/' . $category->getidcategory() . '?page=' . $i
        ));
    }
    $page = new Page();
    $page->setTpl('category', array(
        'category' => $category->getValues(),
        'products' => $pagination['data'],
        'pages' => $pages
    ));
});

/**
 * Página de detalhes de um produto - GET
 */
$app->get('/produto/:desurl', function($desurl) {
    $product = new Product();
    $product->getFromURL($desurl);
    $page = new Page();
    $page->setTpl('product-detail', array(
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ));
});

/**
 * Página do carrinho - GET
 */
$app->get('/carrinho', function() {
    $cart = Cart::getFromSession();
    $page = new Page();
    $page->setTpl('shoping-cart', array(
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts(),
        'error' => Cart::getMsgError()
    ));
});

/**
 * Adiciona um produto ao carrinho
 */
$app->get('/carrinho/:idproduct/adicionar', function($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
    for ($i = 0; $i < $qtd; $i++) { 
        $cart->addProduct($product);
    }
    header('Location: /carrinho');
    exit;
});

/**
 * Remove um item do carrinho
 */
$app->get('/carrinho/:idproduct/menos', function($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product);
    header('Location: /carrinho');
    exit;
});

/**
 * Remove todos os itens do carrinho
 */
$app->get('/carrinho/:idproduct/remover', function($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);
    header('Location: /carrinho');
    exit;
});

/**
 * Rota de cálculo de frete - POST
 */
$app->post('/carrinho/frete', function() {
    $cart = Cart::getFromSession();
    $cart->setFreight($_POST['postcode']);
    header('Location: /carrinho');
    exit;
});

/**
 * Página de finalizar compra - GET
 */
$app->get('/finalizar', function() {
    User::verifyLogin(false);
    $page = new Page();
    $cart = Cart::getFromSession();
    $address = new Address();
    $page->setTpl('checkout', array(
        'cart' => $cart->getValues(),
        'address' => $address->getValues()
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
    ) {
        header('Location: /');
        exit;
    }
    $page = new Page();
    $page->setTpl('login', array(
        'error' => User::getError(),
        'errorRegister' => User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : array(
            'name' => '',
            'email' => '',
            'phone' => ''
        )
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
    header('Location: /finalizar');
    exit;
});

/**
 * Rota de logout - GET
 */
$app->get("/logout", function(){
    User::logout();
    Cart::removeFromSession();
    session_regenerate_id();
    header('Location: /login');
    exit;
});

/**
 * Rota de registrar usuário - POST
 */
$app->post('/registrar', function() {
    $_SESSION['registerValues'] = $_POST;
    if (!isset($_POST['name']) || $_POST['name'] === '') {
        User::setErrorRegister('Preencha o seu nome.');
        header('Location: /login');
        exit;
    }
    if (!isset($_POST['email']) || $_POST['email'] === '') {
        User::setErrorRegister('Preencha o seu e-mail.');
        header('Location: /login');
        exit;
    }
    if (!isset($_POST['password']) || $_POST['password'] === '') {
        User::setErrorRegister('Preencha a sua senha.');
        header('Location: /login');
        exit;
    }
    if (User::checkLoginExists($_POST['email'])) {
        User::setErrorRegister('Este endereço de e-mail já está cadastrado.');
        header('Location: /login');
        exit;
    }
    $user = new User();
    $user->setData(array(
        'desperson' => $_POST['name'],
        'deslogin' => $_POST['email'],
        'despassword' => $_POST['password'],
        'desemail' => $_POST['email'],
        'nrphone' => $_POST['phone'],
        'inadmin' => '0'
    ));
    $user->save();
    User::login($_POST['email'], $_POST['password']);
    header('Location: /finalizar');
    exit;
});

/**
 * Página de recuperação de senha - GET
 */
$app->get('/forgot', function() {
    $page = new Page();
    $page->setTpl('forgot');
});

/**
 * Rota de recuperação de senha - POST
 */
$app->post('/forgot', function() {
    $user = User::getForgot($_POST['email'], false);
    header('Location: /forgot/sent');
    exit;
});

/**
 * Página de confirmação de envio para recuperação de senha - GET
 */
$app->get('/forgot/sent', function() {
    $page = new Page();
    $page->setTpl('forgot-sent');
});

/**
 * Página de resetar a senha - GET
 */
$app->get('/forgot/reset', function() {
    $user = User::validForgotDecrypt($_GET['code']);
    $page = new Page();
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
    $page = new Page();
    $page->setTpl('forgot-reset-success');
});

/**
 * Minha conta - GET
 */
$app->get('/perfil', function() {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile', array(
        'user' => $user->getValues(),
        'profileMsg' => User::getSuccess(),
        'profileError' => User::getError()
    ));
});

/**
 * Salva a edição de um usuário - POST
 */
$app->post('/perfil', function() {
    User::verifyLogin(false);
    if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
        User::setError('Preencha o seu nome.');
        header('Location: /perfil');
        exit;
    }
    if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
        User::setError('Preencha o seu e-mail.');
        header('Location: /perfil');
        exit;
    }
    $user = User::getFromSession();
    if ($_POST['desemail'] !== $user->getdesemail()) {
        if (User::checkLoginExists($_POST['desemail'])) {
            User::setError('Este endereço de e-mail já está cadastrado.');
        }
    }
    $_POST['idadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];
    $user->setData($_POST);
    $user->update();
    User::setSuccess('Dados alterados com sucesso!');
    header('Location: /perfil');
    exit;
});