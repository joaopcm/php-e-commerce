<?php

use \Loja\Model\Page;
use \Loja\Model\Category;
use \Loja\Model\Product;
use \Loja\Model\Cart;
use \Loja\Model\Address;
use \Loja\Model\User;
use \Loja\Model\Order;
use \Loja\Model\OrderStatus;

/**
 * Página principal da loja - GET
 */
$app->get('/', function () {
    $page = new Page();
    $products = Product::listAll();
    $page->setTpl('index', array(
        'products' => Product::checkList($products)
    ));
});

/**
 * Página de categoria - GET
 */
$app->get('/categoria/:idcategory', function($idcategory) {
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $pages = array();
    $category = new Category();
    $category->get((int)$idcategory);
    $pagination = $category->getProductsPage($page);
    for ($i = 1; $i <= $pagination['pages']; $i++) { 
        array_push($pages, array(
            'page' => $i,
            'link' => '/categoria/' . $category->getidcategory() . '?page=' . $i
        ));
    }
    $page = new Page();
    $page->setTpl('category', array(
        'category' => $category->getValues(),
        'products' => $pagination['data'],
        'pages' => $pages
    ));
});

/**
 * Página de detalhes de um produto - GET
 */
$app->get('/produto/:desurl', function($desurl) {
    $product = new Product();
    $product->getFromURL($desurl);
    $page = new Page();
    $page->setTpl('product-detail', array(
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ));
});

/**
 * Página do carrinho - GET
 */
$app->get('/carrinho', function() {
    $cart = Cart::getFromSession();
    $page = new Page();
    $page->setTpl('shoping-cart', array(
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts(),
        'error' => Cart::getMsgError()
    ));
});

/**
 * Adiciona um produto ao carrinho
 */
$app->get('/carrinho/:idproduct/adicionar', function($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
    for ($i = 0; $i < $qtd; $i++) { 
        $cart->addProduct($product);
    }
    header('Location: /carrinho');
    exit;
});

/**
 * Remove um item do carrinho
 */
$app->get('/carrinho/:idproduct/menos', function($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product);
    header('Location: /carrinho');
    exit;
});

/**
 * Remove todos os itens do carrinho
 */
$app->get('/carrinho/:idproduct/remover', function($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);
    header('Location: /carrinho');
    exit;
});

/**
 * Rota de cálculo de frete - POST
 */
$app->post('/carrinho/frete', function() {
    $cart = Cart::getFromSession();
    $cart->setFreight($_POST['postcode']);
    header('Location: /carrinho');
    exit;
});

/**
 * Página de finalizar compra - GET
 */
$app->get('/finalizar', function() {
    User::verifyLogin(false);
    $address = new Address();
    $cart = Cart::getFromSession();
    if (isset($_GET['deszipcode'])) {
        $_GET['deszipcode'] = $cart->getdeszipcode();
    }
    if (isset($_GET['deszipcode'])) {
        $address->loadFromCEP($_GET['deszipcode']);
        $cart->setdeszipcode($_GET['deszipcode']);
        $cart->save();
        $cart->getCalcTotal();
    }
    if (!$address->getdesaddress()) $address->setdesaddress('');
    if (!$address->getdescomplement()) $address->setdescomplement('');
    if (!$address->getdesdistrict()) $address->setdesdistrict('');
    if (!$address->getdescity()) $address->setdescity('');
    if (!$address->getdesstate()) $address->setdesstate('');
    if (!$address->getdescountry()) $address->setdescountry('');
    if (!$address->getdeszipcode()) $address->setdeszipcode('');
    $page = new Page();
    $page->setTpl('checkout', array(
        'cart' => $cart->getValues(),
        'address' => $address->getValues(),
        'products' => $cart->getProducts(),
        'error' => Address::getMsgError()
    ));
});

/**
 * Rota de finalizar compra - POST
 */
$app->post('/finalizar', function() {
    if (!isset($_POST['deszipcode']) || $_POST['deszipcode'] === '') {
        Address::setMsgError('Informe o CEP.');
        header('Location: /finalizar');
        exit;
    }
	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		Address::setMsgError("Informe o endereço.");
		header('Location: /finalizar');
		exit;
	}
	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		Address::setMsgError("Informe o bairro.");
		header('Location: /finalizar');
		exit;
	}
	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		Address::setMsgError("Informe a cidade.");
		header('Location: /finalizar');
		exit;
	}
	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		Address::setMsgError("Informe o estado.");
		header('Location: /finalizar');
		exit;
	}
	if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
		Address::setMsgError("Informe o país.");
		header('Location: /finalizar');
		exit;
	}
	$user = User::getFromSession();
	$address = new Address();
	$_POST['idperson'] = $user->getidperson();	
	$address->setData($_POST);
	$address->save();
	$cart = Cart::getFromSession();
	$cart->getCalcTotal();
	$order = new Order();	
	$order->setData(array(
		'idcart' => $cart->getidcart(),
		'idaddress' => $address->getidaddress(),
		'iduser' => $user->getiduser(),
		'idstatus' => OrderStatus::EM_ABERTO,
		'vltotal' => $cart->getvltotal()
	));
	$order->save();
    header('Location: /pedido/' . $order->getidorder());
    exit;
});

/**
 * Página de login - GET
 */
$app->get('/login', function() {
    if (
        isset($_SESSION[User::SESSION])
        ||
        $_SESSION[User::SESSION]
        ||
        (int)$_SESSION[User::SESSION]['iduser'] > 0
    ) {
        header('Location: /');
        exit;
    }
    $page = new Page();
    $page->setTpl('login', array(
        'error' => User::getError(),
        'errorRegister' => User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : array(
            'name' => '',
            'email' => '',
            'phone' => ''
        )
    ));
});

/**
 * Rota de login - POST
 */
$app->post('/login', function() {
    try {
        User::login($_POST['username'], $_POST['password']);
    } catch(Exception $e) {
        User::setError($e->getMessage());
    }
    header('Location: /finalizar');
    exit;
});

/**
 * Rota de logout - GET
 */
$app->get("/logout", function(){
    User::logout();
    Cart::removeFromSession();
    session_regenerate_id();
    header('Location: /login');
    exit;
});

/**
 * Rota de registrar usuário - POST
 */
$app->post('/registrar', function() {
    $_SESSION['registerValues'] = $_POST;
    if (!isset($_POST['name']) || $_POST['name'] === '') {
        User::setErrorRegister('Preencha o seu nome.');
        header('Location: /login');
        exit;
    }
    if (!isset($_POST['email']) || $_POST['email'] === '') {
        User::setErrorRegister('Preencha o seu e-mail.');
        header('Location: /login');
        exit;
    }
    if (!isset($_POST['password']) || $_POST['password'] === '') {
        User::setErrorRegister('Preencha a sua senha.');
        header('Location: /login');
        exit;
    }
    if (User::checkLoginExists($_POST['email'])) {
        User::setErrorRegister('Este endereço de e-mail já está cadastrado.');
        header('Location: /login');
        exit;
    }
    $user = new User();
    $user->setData(array(
        'desperson' => $_POST['name'],
        'deslogin' => $_POST['email'],
        'despassword' => $_POST['password'],
        'desemail' => $_POST['email'],
        'nrphone' => $_POST['phone'],
        'inadmin' => '0'
    ));
    $user->save();
    User::login($_POST['email'], $_POST['password']);
    header('Location: /finalizar');
    exit;
});

/**
 * Página de recuperação de senha - GET
 */
$app->get('/forgot', function() {
    $page = new Page();
    $page->setTpl('forgot');
});

/**
 * Rota de recuperação de senha - POST
 */
$app->post('/forgot', function() {
    $user = User::getForgot($_POST['email'], false);
    header('Location: /forgot/sent');
    exit;
});

/**
 * Página de confirmação de envio para recuperação de senha - GET
 */
$app->get('/forgot/sent', function() {
    $page = new Page();
    $page->setTpl('forgot-sent');
});

/**
 * Página de resetar a senha - GET
 */
$app->get('/forgot/reset', function() {
    $user = User::validForgotDecrypt($_GET['code']);
    $page = new Page();
    $page->setTpl('forgot-reset', array(
        'name' => $user['desperson'],
        'code' => $_GET['code']
    ));
});

/**
 * Rota de redefinição de senha - POST
 */
$app->post('/forgot/reset', function() {
    $forgot = User::validForgotDecrypt($_POST['code']);
    User::setForgotUsed($forgot['idrecovery']);
    $user = new User();
    $user->get((int)$forgot['iduser']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT, [
        'cost' => 12
    ]);
    $user->setPassword($password);
    $page = new Page();
    $page->setTpl('forgot-reset-success');
});

/**
 * Minha conta - GET
 */
$app->get('/conta', function() {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile', array(
        'user' => $user->getValues(),
        'profileMsg' => User::getSuccess(),
        'profileError' => User::getError()
    ));
});

/**
 * Salva a edição de um usuário - POST
 */
$app->post('/conta', function() {
    User::verifyLogin(false);
    if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
        User::setError('Preencha o seu nome.');
        header('Location: /conta');
        exit;
    }
    if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
        User::setError('Preencha o seu e-mail.');
        header('Location: /conta');
        exit;
    }
    $user = User::getFromSession();
    if ($_POST['desemail'] !== $user->getdesemail()) {
        if (User::checkLoginExists($_POST['desemail'])) {
            User::setError('Este endereço de e-mail já está cadastrado.');
        }
    }
    $_POST['idadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];
    $user->setData($_POST);
    $user->update();
    User::setSuccess('Dados alterados com sucesso!');
    header('Location: /conta');
    exit;
});

/**
 * Página de pedido - GET
 */
$app->get('/pedido/:idorder', function($idorder) {
    User::verifyLogin(false);
    $order = new Order();
    $order->get((int)$idorder);
    $page = new Page();
    $page->setTpl('payment', array(
        'order' => $order->getValues()
    ));
});

/**
 * Página de geração de boleto - GET
 */
$app->get('/boleto/:idorder', function($idorder) {

    User::verifyLogin(false);
    $order = New Order();
    $order->get((int)$idorder);

    // DADOS DO BOLETO PARA O SEU CLIENTE
    $dias_de_prazo_para_pagamento = 10;
    $taxa_boleto = 5.00;
    $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
    $valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
    $valor_cobrado = str_replace('.', '', $valor_cobrado);
    $valor_cobrado = str_replace(",", ".",$valor_cobrado);
    $valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

    $dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
    $dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
    $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
    $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
    $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
    $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

    // DADOS DO SEU CLIENTE
    $dadosboleto["sacado"] = $order->getdesperson();
    $dadosboleto["endereco1"] = $order->getdesaddress() . ' ' . $order->getdesdistrict();
    $dadosboleto["endereco2"] = $order->getdescity() . ' - ' . $order->getdesstate() . ' - ' . $order->getdescountry() . ' - CEP: ' . $order->getdeszipcode();

    // INFORMACOES PARA O CLIENTE
    $dadosboleto["demonstrativo1"] = "Pagamento de Compra na " . COMPANY_NAME;
    $dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
    $dadosboleto["demonstrativo3"] = "";
    $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
    $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
    $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: " . MAIL_ADDRESS;
    $dadosboleto["instrucoes4"] = "&nbsp; Emitido por " . COMPANY_NAME . " - " . BASE_URL;

    // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
    $dadosboleto["quantidade"] = "";
    $dadosboleto["valor_unitario"] = "";
    $dadosboleto["aceite"] = "";		
    $dadosboleto["especie"] = "R$";
    $dadosboleto["especie_doc"] = "";

    // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //

    // DADOS DA SUA CONTA - ITAÚ
    $dadosboleto["agencia"] = AGENCY; // Num da agencia, sem digito
    $dadosboleto["conta"] = ACCOUNT;	// Num da conta, sem digito
    $dadosboleto["conta_dv"] = ACCOUNT_DIGIT; 	// Digito do Num da conta

    // DADOS PERSONALIZADOS - ITAÚ
    $dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

    // SEUS DADOS
    $dadosboleto["identificacao"] = COMPANY_NAME;
    $dadosboleto["cpf_cnpj"] = CNPJ;
    $dadosboleto["endereco"] = ENDERECO;
    $dadosboleto["cidade_uf"] = CIDADE . ' - ' . UF;
    $dadosboleto["cedente"] = COMPANY_NAME;

    // NÃO ALTERAR!
    $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'boletophp' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR;
    require_once $path . 'funcoes_itau.php';
    require_once $path . 'layout_itau.php';
});

/**
 * Página que lista todas as compras de um usuário - GET
 */
$app->get('/conta/pedidos', function () {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile-orders', array(
        'orders' => $user->getOrders()
    ));
});

/**
 * Página que mostra detalhes de uma compra de um usuário - GET
 */
$app->get('/conta/pedido/:idorder', function ($idorder) {
    User::verifyLogin(false);
    $order = new Order();
    $order->get((int)$idorder);
    $cart = new Cart();
    $cart->get((int)$order->getidcart());
    $cart->getCalcTotal();
    $page = new Page();
    $page->setTpl('profile-orders-detail', array(
        'order' => $order->getValues(),
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts()
    ));
});