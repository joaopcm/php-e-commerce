<?php

namespace Loja\Model;

class PageAdmin extends Page {

    /**
     * Insere páginas HTML administrativas
     */
    public function __construct($opts = array(), $tpl_dir = "/views/admin/")
    {
        parent::__construct($opts, $tpl_dir);
    }

}
