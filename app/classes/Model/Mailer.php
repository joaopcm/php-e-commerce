<?php

namespace Loja\Model;

use Rain\Tpl;
use \Loja\Model\Company;

class Mailer {

    private $mail;

    /**
     * Configura o envio do e-mail
     */
    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {
        $config = array(
		    "base_url"      => null,
			"tpl_dir"       => $_SERVER['DOCUMENT_ROOT'] . '/views/email/',
			"cache_dir"     => "/tmp/",
		    "debug"         => false
		);
		Tpl::configure($config);
        $tpl = new Tpl();
        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }
        $company = new Company();
        $html = $tpl->draw($tplName, true);
        $this->mail = new \PHPMailer();
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 0;
        $this->mail->Debugoutput = 'html';
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->Port = MAIL_PORT;
        $this->mail->SMTPSecure = 'tls';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = MAIL_ADDRESS;
        $this->mail->Password = MAIL_PASSWORD;
        $this->mail->setFrom(MAIL_ADDRESS, $company->getCurrentValues()['descompany']);
        $this->mail->addAddress($toAddress, $toName);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($html);
        $this->mail->AltBody = 'Você solicitou a recuperação da sua senha';
    }

    /**
     * Envia o e-mail
     */
    public function send()
    {
        return $this->mail->send();
    }

}