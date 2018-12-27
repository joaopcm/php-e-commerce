<?php

use \Loja\Model\User;
use \Loja\Model\Cart;
use \Loja\Model\Company;

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

/**
 * Retorna a URL do perfil Gravatar de cada usuário logado
 */
function getGravatarUrl()
{
    $user = User::getFromSession();
    $url = 'https://www.gravatar.com/avatar/';
    $email = $user->getdesemail();
    $hash = md5(strtolower(trim($email)));
    return $url . $hash;
}

/**
 * Retorna a quantidade de itens no carrinho
 */
function getCartNrQtd()
{
    $cart = Cart::getFromSession();
    $totals = $cart->getProductsTotals();
    return $totals['nrqtd'];
}

/**
 * Retorna o sub-total do carrinho (Não inclui o frete no valor)
 */
function getCartVlSubTotal()
{
    $cart = Cart::getFromSession();
    $totals = $cart->getProductsTotals();
    return $totals['vlprice'];
}

function getCartProducts()
{
    $cart = Cart::getFromSession();
    return $cart->getProducts();
}

/**
 * Formata uma data
 */
function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

/**
 * Retorna dados da empresa
 */
function getCompany()
{
    $company = new Company();
    return $company->getCurrentValues();
}