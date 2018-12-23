<?php

use \Loja\Model\Page;
use \Loja\Model\Category;
use \Loja\Model\Product;

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