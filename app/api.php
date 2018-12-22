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

/**
 * Inicia uma sessão
 */
session_start();

/**
 * Importa arquivo de configurações
 */
require_once 'config.php';

/**
 * Namespaces
 */
use \Loja\Middleware\authenticateForRole;
use \Loja\Model\User;
use \Loja\Model\Senha;
use \Loja\Model\PageAdmin;
use \Loja\Model\Page;
use \Slim\Slim;

/**
 * Middlewares
 */
$authenticateForRole = new authenticateForRole();

/**
 * Instância do Slim Framework
 */
PRODUCTION_MODE === 'false' ? $app = new Slim(array('mode' => 'development', 'debug' => true)) : $app = new Slim(array('debug' => false));

/**
 * Redireciona para a página principal quando a rota não é encontrada
 */
$app->notFound(function () use ($app) {
    $app->redirect('/');
});

/**
 * Página principal da loja - GET
 */
$app->get('/', function () {
    $page = new Page();
    $page->setTpl('index');
});

/**
 * Grupo de rotas administrativas
 */
$app->group('/admin', function () use ($app) {

    /**
     * Página principal administrativa - GET
     */
    $app->get('/', function () {
        $page = new PageAdmin();
        $page->setTpl('index');
    });

});

/**
 * Inicia a API
 */
$app->run();
