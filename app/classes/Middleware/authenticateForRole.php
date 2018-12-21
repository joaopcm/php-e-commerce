<?php

namespace Loja\Middleware;

class AuthenticateForRole extends \Slim\Middleware
{
    public function call()
    {
        return function () {
            if (!isset($_SESSION['User'])) {
                $app = \Slim\Slim::getInstance();
                $app->redirect('/login');
            }
        };
    }
}
