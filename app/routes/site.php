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
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new Page();
    $page->setTpl('category', array(
        'category' => $category->getValues(),
        'products' => Product::checkList($category->getProducts())
    ));
});