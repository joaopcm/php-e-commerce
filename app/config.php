<?php

/**
 * Configurações do PHP
 */
require_once "libs/autoload.php";
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utl-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

/**
 * Variáveis de ambiente
 */
define('COMPANY_NAME', getenv('COMPANY_NAME'));
define('BASE_URL', getenv('BASE_URL'));
define('PRODUCTION_MODE', getenv('PRODUCTION_MODE'));
define('MYSQL_USER', getenv('MYSQL_USER'));
define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD'));
define('MYSQL_ROOT_PASSWORD', getenv('MYSQL_ROOT_PASSWORD'));
define('MYSQL_DATABASE', getenv('MYSQL_DATABASE'));
define('MYSQL_HOST', getenv('MYSQL_HOST'));
define('MAIL_ADDRESS', getenv('MAIL_ADDRESS'));
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD'));
define('MAIL_PORT', getenv('MAIL_PORT'));
define('AGENCY', getenv('AGENCY'));
define('ACCOUNT', getenv('ACCOUNT'));
define('ACCOUNT_DIGIT', getenv('ACCOUNT_DIGIT'));
define('CNPJ', getenv('CNPJ'));
define('ENDERECO', getenv('ENDERECO'));
define('CIDADE', getenv('CIDADE'));
define('UF', getenv('UF'));
define('APP_NAME', getenv('APP_NAME'));
define('COMPANY_SLOGAN', getenv('COMPANY_SLOGAN'));
define('TELEFONE', getenv('TELEFONE'));
define('PAYPAL_MAIL', getenv('PAYPAL_MAIL'));
define('PAGSEGURO_MAIL', getenv('PAGSEGURO_MAIL'));
