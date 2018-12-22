<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Category;

/**
 * Página de categorias - GET
 */
$app->get('/categorias', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $categories = Category::listAll();
    $page->setTpl('categories', array(
        'categories' => $categories
    ));
});

/**
 * Página de criação de categorias - GET
 */
$app->get('/categorias/cadastrar', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('categories-create');
});

/**
 * Rota de criação de categorias - POST
 */
$app->post('/categorias/cadastrar', function() {
    User::verifyLogin();
    $category = new Category();
    $category->setData($_POST);
    $category->save();
    header('Location: /admin/categorias');
    exit;
});

/**
 * Rota de exclusão de categorias - GET
 */
$app->get('/categorias/:idcategory/excluir', function($idcategory) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $category->delete();
    header('Location: /admin/categorias');
    exit;
});

/**
 * Página de edição de categorias - GET
 */
$app->get('/categorias/:idcategory', function($idcategory) {
    User::verifyLogin();
    $page = new PageAdmin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page->setTpl('categories-update', array(
        'category' => $category->getValues()
    ));
});

/**
 * Rota de edição de categorias - POST
 */
$app->post('/categorias/:idcategory', function($idcategory) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $category->setData($_POST);
    $category->save();
    header('Location: /admin/categorias');
    exit;
});