<?php

use \Loja\Model\User;

/**
 * Formata os valores do sistema
 */
function formatPrice(float $vlprice)
{
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