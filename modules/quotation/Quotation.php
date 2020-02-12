<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Quotation extends Module
{
    public function __construct()
    {
        $this->name = 'quotation';
        $this->tab = 'dashboard';
        $this->version = '1.0.0';
        $this->author = 'TeamWilders';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Quotation');
        $this->description = $this->l('Making quotations for clients');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        if (!Configuration::get('QUOTATION_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }
}