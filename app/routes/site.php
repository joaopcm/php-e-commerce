<?php

use \Loja\Model\Page;

/**
 * Página principal da loja - GET
 */
$app->get('/', function () {
    $page = new Page();
    $page->setTpl('index');
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
        'products' => array()
    ));
});