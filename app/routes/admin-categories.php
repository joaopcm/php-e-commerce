<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Category;
use \Loja\Model\Product;

/**
 * Página de categorias - GET
 */
$app->get('/categorias', function() {
    User::verifyLogin();
    $search = (isset($_GET['search'])) ? $_GET['search'] : '';
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    if ($search != '') {
        $pagination = Category::getPageSearch($search, $page);
    } else {
        $pagination = Category::getPage($page);
    }
    $pages = array();
    for ($i = 0; $i < $pagination['pages']; $i++) { 
        array_push($pages, array(
            'href' => '/admin/categorias?' . http_build_query(array(
                'page' => $i + 1,
                'search' => $search
            )),
            'text' => $i + 1
        ));    
    }
    $page = new PageAdmin();
    $page->setTpl('categories', array(
        'categories' => $pagination['data'],
        'search' => $search,
        'pages' => $pages
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
 * Página de relação entre caregorias e produtos - GET
 */
$app->get('/categorias/:idcategory/produtos', function($idcategory) {
    User::verifyLogin();
    $page = new PageAdmin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page->setTpl('categories-products', array(
        'category' => $category->getValues(),
        'productsRelated' => $category->getProducts(),
        'productsNotRelated' => $category->getProducts(false)
    ));
});

/**
 * Rota que adiciona um produto dentro de uma categoria - GET
 */
$app->get('/categorias/:idcategory/produtos/:idproduct/adicionar', function($idcategory, $idproduct) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $product = new Product();
    $product->get((int)$idproduct);
    $category->addProduct($product);
    header("Location: /admin/categorias/$idcategory/produtos");
    exit;
});

/**
 * Rota que remove um produto dentro de uma categoria - GET
 */
$app->get('/categorias/:idcategory/produtos/:idproduct/remover', function($idcategory, $idproduct) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $product = new Product();
    $product->get((int)$idproduct);
    $category->removeProduct($product);
    header("Location: /admin/categorias/$idcategory/produtos");
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