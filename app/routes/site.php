<?php

use \Loja\Model\Page;
use \Loja\Model\Category;
use \Loja\Model\Product;
use \Loja\Model\Cart;

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