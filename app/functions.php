<?php

use \Loja\Model\User;

/**
 * Formata os valores do sistema
 */
function formatPrice($vlprice)
{
    if (!$vlprice > 0) {
        $vlprice = 0;
    }
    return number_format($vlprice, 2, ',', '.');
}

/**
 * Verfica o login
 */
function checkLogin($inadmin = true)
{
 return User::checklogin($inadmin);
}

/**
 * Retorna o nome do usuário
 */
function getUserName()
{
    $user = User::getFromSession();
    return $user->getdesperson();
}