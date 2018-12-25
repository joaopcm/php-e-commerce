<?php

use \Loja\Model\PageAdmin;
use \Loja\Model\User;
use \Loja\Model\Company;

/**
 * Página de edição de informações da empresa - GET
 */
$app->get('/sistema/empresa', function() {
    User::verifyLogin();
    $company = new Company();
    $page = new PageAdmin();
    $page->setTpl('company-info', array(
        'company' => $company->getCurrentValues(),
        'errorMsg' => '',
        'successMsg' => ''
    ));
});

/**
 * Rota de edição de informações da empresa - GET
 */
$app->post('/sistema/empresa', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $company = new Company();
    $company->setData($_POST);
    $company->save();
    header('Location: /admin/sistema/empresa');
    exit;
});