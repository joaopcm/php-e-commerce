<?php

/**
 *
 * Loja Virtual - Aplicação E-Commerce feita com novas tecnologias e boas práticas.
 *
 * @author João Pedro da Cruz Melo <joao.pedro6532@gmail.com>
 * @license Uso exclusivo de clientes do autor
 * @copyright 2018 | João Melo
 *
 **/

// Inicia uma sessão
session_start();

// Configurações do sistema
require_once 'config.php';

// Namespaces
use \Slim\Slim;

// Instância do Slim Framework
PRODUCTION_MODE === 'false' ? $app = new Slim(array('mode' => 'development', 'debug' => true)) : $app = new Slim(array('debug' => false));

// Redireciona para a página principal quando a rota não é encontrada
$app->notFound(function () use ($app) {
    $app->redirect('/');
});

// Rotas do site
require_once './routes/site.php';

// Grupos de rotas administrativas
$app->group('/admin', function () use ($app) {

    /**
     * Rotas com funções de login, logout e recuperação de senha
     */
    require_once './routes/admin.php';

    /**
     * CRUD de usuários
     */
    require_once './routes/admin-users.php';

    /**
     * CRUD de categorias
     */
    require_once './routes/admin-categories.php';

    /**
     * CRUD de produtos
     */
    require_once './routes/admin-products.php';

});

// Inicia a API
$app->run();
