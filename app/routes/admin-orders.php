<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Order;
use \Loja\Model\OrderStatus;

/**
 * Administração de pedidos - GET
 */
$app->get('/pedidos', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('orders', array(
        'orders' => Order::listAll()
    ));
});

/**
 * Rota de excluir um pedido - GET
 */
$app->get('/pedidos/:idorder/excluir', function($idorder) {
    User::verifyLogin();
    $order = new Order();
    $order->get((int)$idorder);
    $order->delete();
    header('Location: /admin/pedidos');
    exit;
});

/**
 * Página de detalhes de um pedido - GET
 */
$app->get('/pedido/:idorder', function($idorder) {
    User::verifyLogin();
    $order = new Order();
    $order->get((int)$idorder);
    $cart = $order->getCart();
    $page = new PageAdmin();
    $page->setTpl('order', array(
        'order' => $order->getValues(),
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts()
    ));
});

/**
 * Página de editar status de um pedido - GET
 */
$app->get('/pedido/:idorder/status', function($idorder) {
    User::verifyLogin();
    $order = new Order();
    $order->get((int)$idorder);
    $page = new PageAdmin();
    $page->setTpl('order-status', array(
        'order' => $order->getValues(),
        'status' => OrderStatus::listAll(),
        'msgSuccess' => Order::getSuccess(),
        'msgError' => Order::getError()
    ));
});

/**
 * Rota de editar status de um pedido - POST
 */
$app->post('/pedido/:idorder/status', function($idorder) {
    User::verifyLogin();
    if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
        Order::setError('Informe o status atual do pedido.');
        header('Location: /admin/pedido/' . $idorder . '/status');
        exit;
    }
    $order = new Order();
    $order->get((int)$idorder);
    $order->setidstatus($_POST['idstatus']);
    $order->save();
    Order::setSuccess('Status atualizado!');
    header('Location: /admin/pedido/' . $idorder . '/status');
    exit;
});