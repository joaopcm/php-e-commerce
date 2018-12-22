<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Product;

/**
 * Página de produtos - GET
 */
$app->get('/produtos', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $products = Product::listAll();
    $page->setTpl('products', array(
        'products' => $products
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
    $product->setPhoto($_FILES['file']);
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