<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Product;

/**
 * Página de produtos - GET
 */
$app->get('/produtos', function() {
    User::verifyLogin();
    $search = (isset($_GET['search'])) ? $_GET['search'] : '';
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    if ($search != '') {
        $pagination = Product::getPageSearch($search, $page);
    } else {
        $pagination = Product::getPage($page);
    }
    $pages = array();
    for ($i = 0; $i < $pagination['pages']; $i++) { 
        array_push($pages, array(
            'href' => '/admin/produtos?' . http_build_query(array(
                'page' => $i + 1,
                'search' => $search
            )),
            'text' => $i + 1
        ));    
    }
    $page = new PageAdmin();
    $page->setTpl('products', array(
        'products' => $pagination['data'],
        'search' => $search,
        'pages' => $pages
    ));
});

/**
 * Página de cadastro de produtos - GET
 */
$app->get('/produtos/cadastrar', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('products-create');
});

/**
 * Rota de cadastro de produtos - POST
 */
$app->POST('/produtos/cadastrar', function() {
    User::verifyLogin();
    $product = new Product();
    $product->setData($_POST);
    $product->save();
    header('Location: /admin/produtos');
    exit;
});

/**
 * Página de edição de produtos - GET
 */
$app->get('/produtos/:idproduct', function($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int)$idproduct);
    $page = new PageAdmin();
    $page->setTpl('products-update', array(
        'product' => $product->getValues()
    ));
});

/**
 * Rota de edição de produtos - POST
 */
$app->post('/produtos/:idproduct', function($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int)$idproduct);
    $product->setData($_POST);
    $product->save();
    if ($_FILES["file"]["name"] !== "") $product->setPhoto($_FILES["file"]);
    header('Location: /admin/produtos');
    exit;
});

/**
 * Rota de exclusão de produtos - GET
 */
$app->get('/produtos/:idproduct/excluir', function($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int)$idproduct);
    $product->delete();
    header('Location: /admin/produtos');
    exit;
});