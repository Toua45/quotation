<?php
/**
 * Module locdevis
 *
 * @category Prestashop
 * @category Module
 * @author    Florian de ROCHEFORT
 * @copyright AQUAPURE
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

require_once(_PS_MODULE_DIR_.'locdevis/models/LocQuotation.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class Locdevis extends PaymentModule
{
    private $config;

    public function __construct()
    {
        $this->name = 'locdevis';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'AQUAPURE - Florian de ROCHEFORT';
        $this->need_instance = 0;
        $this->erreurs = array();
        $this->bootstrap = true;

        $this->config = new Configuration();

        parent::__construct();

        $this->displayName = $this->l('Renting Quotations');
        $this->description = $this->l('This module allows your sales representatives to create a quotation with rent option.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete these great module ?');

        $this->context->smarty->assign(array(
            'module_name' => $this->name,
            'moduledir' => _MODULE_DIR_.$this->name.'/'
        ));

        if (!extension_loaded('curl')) {
            $this->warning = $this->l(' To properly display PDF, Php Curl extensions have to be loaded.');
        }

        if (!$this->config->get('LOCDEVIS_ADMINCONTACTID')) {
            $this->warning = $this->l('To allow guests to send quotation requests, you have to set an admin contact.');
        }
    }

    public function install()
    {
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            return false;
        }

        $sql = array();
        include(dirname(__FILE__).'/sql/install.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        //1.6.1.0 specific price bug fixe
        if (version_compare(_PS_VERSION_, '1.6.1.0', '=')) {
            $sqlUpdateIndex[]="ALTER TABLE "._DB_PREFIX_."specific_price DROP INDEX id_product_2";
            $sqlUpdateIndex[]="ALTER TABLE "._DB_PREFIX_."specific_price ADD INDEX id_product_2 (id_product,id_shop,id_shop_group,id_currency,id_country,id_group,id_customer,id_product_attribute,from_quantity,id_specific_price_rule,id_cart,`from`,`to`)";
            foreach ($sqlUpdateIndex as $sql) {
                if (!Db::getInstance()->execute($sql)) {
                    return false;
                }
            }
        }
        // Install Tabs
        $this->installQuotationModuleTab();

        //Init

        Configuration::updateValue('LOCDEVIS_LIMITINF1', 0);
        Configuration::updateValue('LOCDEVIS_LIMITINF2', 0);
        Configuration::updateValue('LOCDEVIS_LIMITINF3', 0);
        Configuration::updateValue('LOCDEVIS_LIMITSUP1', 0);
        Configuration::updateValue('LOCDEVIS_LIMITSUP2', 0);
        Configuration::updateValue('LOCDEVIS_LIMITSUP3', 0);
        Configuration::updateValue('LOCDEVIS_COEFF1_12', 0);
        Configuration::updateValue('LOCDEVIS_COEFF2_12', 0);
        Configuration::updateValue('LOCDEVIS_COEFF3_12', 0);
        Configuration::updateValue('LOCDEVIS_COEFF1_24', 0);
        Configuration::updateValue('LOCDEVIS_COEFF2_24', 0);
        Configuration::updateValue('LOCDEVIS_COEFF3_24', 0);
        Configuration::updateValue('LOCDEVIS_COEFF1_36', 0);
        Configuration::updateValue('LOCDEVIS_COEFF2_36', 0);
        Configuration::updateValue('LOCDEVIS_COEFF3_36', 0);
        Configuration::updateValue('LOCDEVIS_COEFF1_48', 0);
        Configuration::updateValue('LOCDEVIS_COEFF2_48', 0);
        Configuration::updateValue('LOCDEVIS_COEFF3_48', 0);
        Configuration::updateValue('LOCDEVIS_COEFF1_60', 0);
        Configuration::updateValue('LOCDEVIS_COEFF2_60', 0);
        Configuration::updateValue('LOCDEVIS_COEFF3_60', 0);
        Configuration::updateValue('LOCDEVIS_EXPIRETIME', 0);
        Configuration::updateValue('LOCDEVIS_IMAGESIZE', "");
        Configuration::updateValue('LOCDEVIS_MAXPRODFIRSTPAGE', 7);
        Configuration::updateValue('LOCDEVIS_MAXPRODPAGE', 10);
        Configuration::updateValue('LOCDEVIS_SHOWFREEFORM', 1);
        Configuration::updateValue('LOCDEVIS_SHOWACCOUNTBTN', 1);

        $hookName = (version_compare(_PS_VERSION_, '1.7.0', '>='))?'paymentOptions':'Payment';

        if (!parent::install() ||
            !$this->registerHook($hookName) ||
            !$this->registerHook('displayLeftColumn') ||
            !$this->registerHook('displayShoppingCart') ||
            !$this->registerHook('displayCustomerAccount') ||
            !$this->registerHook('header') ||
            !$this->registerHook('displayAdminView') ||
            !$this->registerHook('actionBeforeCartUpdateQty') ||
            !$this->registerHook('displayBeforeShoppingCartBlock') ||
            !$this->registerHook('actionOrderStatusUpdate') ||
            !$this->setAdminContactID()
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
       	Configuration::deleteByName('LOCDEVIS_LIMITINF1');
        Configuration::deleteByName('LOCDEVIS_LIMITINF2');
        Configuration::deleteByName('LOCDEVIS_LIMITINF3');
        Configuration::deleteByName('LOCDEVIS_LIMITSUP1');
        Configuration::deleteByName('LOCDEVIS_LIMITSUP2');
        Configuration::deleteByName('LOCDEVIS_LIMITSUP3');
        Configuration::deleteByName('LOCDEVIS_COEFF1_12');
        Configuration::deleteByName('LOCDEVIS_COEFF2_12');
        Configuration::deleteByName('LOCDEVIS_COEFF3_12');
        Configuration::deleteByName('LOCDEVIS_COEFF1_24');
        Configuration::deleteByName('LOCDEVIS_COEFF2_24');
        Configuration::deleteByName('LOCDEVIS_COEFF3_24');
        Configuration::deleteByName('LOCDEVIS_COEFF1_36');
        Configuration::deleteByName('LOCDEVIS_COEFF2_36');
        Configuration::deleteByName('LOCDEVIS_COEFF3_36');
        Configuration::deleteByName('LOCDEVIS_COEFF1_48');
        Configuration::deleteByName('LOCDEVIS_COEFF2_48');
        Configuration::deleteByName('LOCDEVIS_COEFF3_48');
        Configuration::deleteByName('LOCDEVIS_COEFF1_60');
        Configuration::deleteByName('LOCDEVIS_COEFF2_60');
        Configuration::deleteByName('LOCDEVIS_COEFF3_60');
        Configuration::deleteByName('LOCDEVIS_SENDMAILTOCUSTOMER');
        Configuration::deleteByName('LOCDEVIS_SENDMAILTOADMIN');
        Configuration::deleteByName('LOCDEVIS_ADMINCONTACTID');
        Configuration::deleteByName('LOCDEVIS_MAXPRODFIRSTPAGE');
        Configuration::deleteByName('LOCDEVIS_MAXPRODPAGE');
        Configuration::deleteByName('LOCDEVIS_EXPIRETIME');
        Configuration::deleteByName('LOCDEVIS_SHOWFREEFORM');
        Configuration::deleteByName('LOCDEVIS_SHOWACCOUNTBTN');
        Configuration::deleteByName('LOCDEVIS_IMAGESIZE');
        foreach (Shop::getShops() as $shop) {
	    	Configuration::deleteByName('LOCDEVIS_ALLOWED_SHOP_'.$shop['id_shop']);
	    }

        $sql = array();
        include(dirname(__FILE__).'/sql/uninstall.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        /* Uninstall Tabs */
        $tab = new Tab((int)Tab::getIdFromClassName('AdminLocdevis'));
        $tab->delete();

        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    private function setAdminContactID()
    {
        $contact_ids = Contact::getContacts($this->context->language->id);

        if (count($contact_ids) > 0)
        {
            return Configuration::updateValue('LOCDEVIS_ADMINCONTACTID', $contact_ids[0]['id_contact']);
        }

        return Configuration::updateValue('LOCDEVIS_ADMINCONTACTID', null);
    }

    private function installQuotationModuleTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminLocdevis';
        /* faire un tableau de retro compatibilite pour les menu
        * https://www.prestashop.com/forums/topic/527046-new-admin-tab-bug/
        */
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentOrders');
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = 'Renting Quotations';
        }
        $tab->module = $this->name;
        return $tab->add();
    }

    public function isFreezCart($cart = null)
    {
        if ($cart == null && !isset($this->context->cart)) {
            return false;
        }
        $cart=($cart != null)?$cart:$this->context->cart;
        $quote = LocQuotation::getByIdCart($cart->id);
        if (!(is_object($quote) && $quote->statut != 0)) {
            return false;
        }

        return $quote;
    }

    public function hookdisplayBeforeShoppingCartBlock()
    {
        if (!isset($this->context->cart)) {
            return false;
        }
        $quote = LocQuotation::getByIdCart($this->context->cart->id);
        if (is_object($quote)) {
            $this->smarty->assign(array(
                'quote' => $quote,
            ));
            return $this->display(__FILE__, 'views/templates/hook/displayBeforeShoppingCartBlock.tpl');
        }
    }

    public function hookPayment()
    {
        if ($this->isFreezCart()) {
            return false;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_locdevis' => $this->_path,
        ));
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            return $this->display(__FILE__, 'views/templates/hook/payment_15.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
        }
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption->setModuleName($this->name)
            ->setCallToActionText($this->l('Create a quote'))
            ->setAction($this->context->link->getModuleLink($this->name, 'createquotation', array('create'=>true,'from'=>'payment'), true))
            ->setAdditionalInformation("<div id='loc-devis-payment'>".$this->l('Create a quote')."</div>");

        return array($newOption);
    }

    public function hookHeader()
    {
        $this->context->controller->addJS(_MODULE_DIR_.'locdevis/views/js/front.js');
        $this->context->controller->addCSS(_MODULE_DIR_.'locdevis/views/css/locdevis_1.css');
    }

    public function hookActionBeforeCartUpdateQty($vars)
    {
        // test if cart is linked to validated quote
        if ($quote = $this->isFreezCart($vars['cart'])) {
            if (Tools::getIsset('add') || Tools::getIsset('update') || Tools::getIsset('delete') || Tools::getIsset('changeAddressDelivery')) {
                $this->ajaxDie(Tools::jsonEncode(array(
                    'hasError' => true,
                    'errors' => array(Tools::displayError('You are not allowed to modify this cart because it is linked to a quotation. Go to your cart for more information')),
                )));
            }
        }
    }

    /**
     * Dies and echoes output value
     *
     * @param string|null $value
     * @param string|null $controller
     * @param string|null $method
     */
    protected function ajaxDie($value = null, $controller = null, $method = null)
    {
        if ($controller === null) {
            $controller = get_class($this);
        }

        if ($method === null) {
            $bt = debug_backtrace();
            $method = $bt[1]['function'];
        }

        Hook::exec('actionBeforeAjaxDie', array('controller' => $controller, 'method' => $method, 'value' => $value));
        Hook::exec('actionBeforeAjaxDie'.$controller.$method, array('value' => $value));

        // PS 1.7
        Hook::exec('actionAjaxDie'.$controller.$method.'Before', array('value' => $value));

        die($value);
    }

    public function hookActionOrderStatusUpdate($vars)
    {
        $orderObj = new Order($vars['id_order']);
        $quote = LocQuotation::getQuotationByCartId($orderObj->id_cart);
        if (is_object($quote) && $quote->statut == 1) {
            $quote->statut = 2;
            $quote->id_order = $vars['id_order'];
            $quote->save();

            $message = sprintf($this->l('Order created from quotation number: %s'), $quote->id_locdevis);
            //add msg to order
            $msg = new Message();
            $msg->message = $message;
            $msg->id_cart = (int)$orderObj->id_cart;
            $msg->id_customer = (int)($orderObj->id_customer);
            $msg->id_order = (int)$orderObj->id;
            $msg->private = 1;
            $msg->add();
        }
    }

    public function hookDisplayShoppingCart()
    {
        $cartId = $this->context->cart->id;
        $quotationObj = LocQuotation::getQuotationByCartId($cartId);
        $this->smarty->assign(array(
            'quote' => $quotationObj
        ));
        if ($this->isFreezCart()) {
            $html = '';
        } else {
            $html = $this->display(__FILE__, 'views/templates/hook/displayButtonCart.tpl');
        }

        if (is_object($quotationObj)) {
            if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
                $html .= $this->display(__FILE__, 'views/templates/hook/displayBeforeShoppingCartBlock.tpl');
            }
        }
        return $html;
    }

    public function hookDisplayLeftColumn()
    {
        /* $this->smarty->assign('idCart',$this->context->cart->id); */
        $html = $this->display(__FILE__, 'views/templates/hook/displayButton.tpl');
        if (Configuration::get('LOCDEVIS_SHOWFREEFORM') == 1) {
            $html .= $this->display(__FILE__, 'views/templates/hook/displayButton2.tpl');
        }
        return $html;
    }

    public function hookDisplayRightColumn()
    {
        return $this->hookDisplayLeftColumn();
    }

    public function hookDisplayFooter()
    {
        return $this->hookDisplayLeftColumn();
    }

    public function hookDisplayTop()
    {
        $this->smarty->assign('this_path', dirname(__FILE__));
        return $this->display(__FILE__, 'views/templates/hook/displayTop.tpl');
    }

    public function hookDisplayCustomerAccount()
    {
        if (Configuration::get('LOCDEVIS_SHOWACCOUNTBTN') == 0) {
            $id_customer = $this->context->customer->id;
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'locdevis` WHERE id_customer='.(int)$id_customer;
            $quotations = Db::getInstance()->executeS($sql);
            if (count($quotations) == 0) {
                return false;
            }
        }
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            return $this->display(__FILE__, 'views/templates/front/ps15/myaccount.tpl');
        } elseif (_PS_VERSION_ >= '1.7') {
            return $this->display(__FILE__, 'views/templates/front/ps17/myaccount.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/front/myaccount.tpl');
        }
    }

    public function hookDisplayAdminView()
    {
        $controller_name = Tools::getValue('controller');

        if ($controller_name == 'AdminCarts') {
            $token = Tools::getAdminToken('AdminLocdevis'.(int)Tab::getIdFromClassName('AdminLocdevis').(int)Context::getContext()->employee->id);
            $id_cart = Tools::getValue('id_cart');
            $href = 'index.php?controller=AdminLocdevis&transformThisCartId='.$id_cart.'&token='.$token;
            return '<a class="btn btn-default" href="'.$href.'"><i class="icon-shopping-cart"></i> '.$this->l('Create a quotation from this cart').'</a>';
        }
    }

    private function getTextAreaField($languages, $inputName, $inputValue)
    {
        $this->context->smarty->assign(array(
            'languages' => $languages,
            'input_name' => $inputName,
            'input_value' => $inputValue
        ));
        $return = $this->display(__FILE__, 'views/templates/admin/textarea_lang.tpl');
        return $return;
    }

    public function getContent()
    {
        $fields_value = array();
        $this->postProcess();
        $fields_value['sendMailtoCustomer'] = $this->config->get('LOCDEVIS_SENDMAILTOCUSTOMER');
        $fields_value['sendMailtoAdmin'] = $this->config->get('LOCDEVIS_SENDMAILTOADMIN');
        $fields_value['adminContactId'] = $this->config->get('LOCDEVIS_ADMINCONTACTID');
        $fields_value['limit_inf_1'] = (int) $this->config->get('LOCDEVIS_LIMITINF1');
        $fields_value['limit_inf_2'] = (int) $this->config->get('LOCDEVIS_LIMITINF2');
        $fields_value['limit_inf_3'] = (int) $this->config->get('LOCDEVIS_LIMITINF3');
        $fields_value['limit_sup_1'] = (int) $this->config->get('LOCDEVIS_LIMITSUP1');
        $fields_value['limit_sup_2'] = (int) $this->config->get('LOCDEVIS_LIMITSUP2');
        $fields_value['limit_sup_3'] = (int) $this->config->get('LOCDEVIS_LIMITSUP3');
        $fields_value['coeff_1_12'] = (int) $this->config->get('LOCDEVIS_COEFF1_12');
        $fields_value['coeff_2_12'] = (int) $this->config->get('LOCDEVIS_COEFF2_12');
        $fields_value['coeff_3_12'] = (int) $this->config->get('LOCDEVIS_COEFF3_12');
        $fields_value['coeff_1_24'] = (int) $this->config->get('LOCDEVIS_COEFF1_24');
        $fields_value['coeff_2_24'] = (int) $this->config->get('LOCDEVIS_COEFF2_24');
        $fields_value['coeff_3_24'] = (int) $this->config->get('LOCDEVIS_COEFF3_24');
        $fields_value['coeff_1_36'] = (int) $this->config->get('LOCDEVIS_COEFF1_36');
        $fields_value['coeff_2_36'] = (int) $this->config->get('LOCDEVIS_COEFF2_36');
        $fields_value['coeff_3_36'] = (int) $this->config->get('LOCDEVIS_COEFF3_36');
        $fields_value['coeff_1_48'] = (int) $this->config->get('LOCDEVIS_COEFF1_48');
        $fields_value['coeff_2_48'] = (int) $this->config->get('LOCDEVIS_COEFF2_48');
        $fields_value['coeff_3_48'] = (int) $this->config->get('LOCDEVIS_COEFF3_48');
        $fields_value['coeff_1_60'] = (int) $this->config->get('LOCDEVIS_COEFF1_60');
        $fields_value['coeff_2_60'] = (int) $this->config->get('LOCDEVIS_COEFF2_60');
        $fields_value['coeff_3_60'] = (int) $this->config->get('LOCDEVIS_COEFF3_60');
        $fields_value['freeText'] = LocQuotation::getQuotationText(0);
        $fields_value['validationText'] = LocQuotation::getQuotationText(1);
        $fields_value['goodforagrementText'] = LocQuotation::getQuotationText(2);
        $fields_value['maxProdFirstPage'] = $this->config->get('LOCDEVIS_MAXPRODFIRSTPAGE');
        $fields_value['maxProdPage'] = $this->config->get('LOCDEVIS_MAXPRODPAGE');
        $fields_value['expireTime'] = $this->config->get('LOCDEVIS_EXPIRETIME');
        $fields_value['showFreeForm'] = $this->config->get('LOCDEVIS_SHOWFREEFORM');
        $fields_value['showAccountBtn'] = $this->config->get('LOCDEVIS_SHOWACCOUNTBTN');
        $fields_value['LOCDEVIS_IMAGESIZE'] = $this->config->get('LOCDEVIS_IMAGESIZE');

        foreach (Shop::getShops() as $shop) {
            $fields_value['allowed_shop_'.$shop['id_shop']] =
                $this->config->get('LOCDEVIS_ALLOWED_SHOP_'.$shop['id_shop']);
        }

        $fields_value['renting_cats'] = $this->config
            ->get('LOCDEVIS_RENTING_CATEGORY_', (int) Category::getRootCategory()->id_category);

        if (isset($fields_value)) {
            $this->context->smarty->assign('fieldsValue', $fields_value);
        }

        /* 1.5 compatibility */
        $languages = Language::getLanguages();
        foreach (Language::getLanguages() as $key => $lang) {
            $languages[$key]['is_default'] = ($lang['id_lang'] == Context::getContext()->language->id) ? 1 : 0;
        }

		$root = Category::getRootCategory();
		$selected_cat[] = $fields_value['renting_cats']; // var $renting_cats replaced

		$tree = new HelperTreeCategories('categories-treeview', $this->l('Choose a category'));
		$tree->setUseCheckBox(true)
            ->setAttribute('is_category_filter', $root->id)
            ->setRootCategory($root->id)
            ->setInputName($root->id_category) // var $category replaced
            ->setSelectedCategories($selected_cat)
            ->setUseSearch(true);

        $this->context->smarty->assign(array(
            'adminModuleUrl' => 'index.php?controller=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'erreurs' => $this->erreurs,
            'languages' => $languages,
            'defaultLangId' => $this->context->language->id,
            'contacts' => Contact::getContacts($this->context->language->id),
            'shops' => Shop::getShops($this->context->language->id),
            'categories' => Category::getCategories(),
            'tree'=> $tree->render(),
        ));

        /* 1.7 compatibility */
        $this->context->smarty->assign(array(
            'validationTextTextArea' => $this->getTextAreaField($languages, 'validationText', $fields_value['validationText']),
            'goodforagrementTextArea' => $this->getTextAreaField($languages, 'goodforagrementText', $fields_value['goodforagrementText']),
            'freeTextTextArea' => $this->getTextAreaField($languages, 'freeText', $fields_value['freeText']),
        ));

        $html = '';
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $html .= $this->display(__FILE__, 'views/templates/admin/form_15.tpl');
            $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            $this->context->controller->addJS(_PS_JS_DIR_.'tinymce.inc.js');
        } else {
            $this->context->controller->addJS(_PS_JS_DIR_.'admin/products.js');
            $html .= $this->display(__FILE__, 'views/templates/admin/form.tpl');
        }

        $html .= $this->display(__FILE__, 'views/templates/admin/help.tpl');
        return $html;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitSettings')) {
            Configuration::updateValue('LOCDEVIS_IMAGESIZE', Tools::getValue("locdevis_imagesize"));
            Configuration::updateValue('LOCDEVIS_SENDMAILTOCUSTOMER', (Tools::getValue('sendMailtoCustomer') == 1) ? 1 : 0);
            Configuration::updateValue('LOCDEVIS_SENDMAILTOADMIN', (Tools::getValue('sendMailtoAdmin') == 1) ? 1 : 0);

            Configuration::updateValue('LOCDEVIS_ADMINCONTACTID', Tools::getValue('adminContactId'));

            foreach (Shop::getShops() as $shop) {
                Configuration::updateValue('LOCDEVIS_ALLOWED_SHOP_'.$shop['id_shop'], (Tools::getValue('allowed_shop_'.$shop['id_shop']) == 1) ? 1 : 0);
        	}

            Configuration::updateValue('LOCDEVIS_RENTING_CATEGORY_'. (int) Category::getRootCategory()->id_category, Tools::getValue('renting_cats'));

            $limit_inf_1 = trim(Tools::getValue('limit_inf_1'));

            if (is_numeric($limit_inf_1) OR is_null($limit_inf_1)) {
                Configuration::updateValue('LOCDEVIS_LIMITINF1', $limit_inf_1);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Amount have to be a number'));
            }

            $limit_inf_2 = trim(Tools::getValue('limit_inf_2'));

            if (is_numeric($limit_inf_2) OR is_null($limit_inf_2)) {
                Configuration::updateValue('LOCDEVIS_LIMITINF2', $limit_inf_2);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Amount have to be a number'));
            }

            $limit_inf_3 = trim(Tools::getValue('limit_inf_3'));

            if (is_numeric($limit_inf_3) OR is_null($limit_inf_3)) {
                Configuration::updateValue('LOCDEVIS_LIMITINF3', $limit_inf_3);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Amount have to be a number'));
            }

            $limit_sup_1 = trim(Tools::getValue('limit_sup_1'));

            if (is_numeric($limit_sup_1) OR is_null($limit_sup_1)) {
                Configuration::updateValue('LOCDEVIS_LIMITSUP1', $limit_sup_1);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Amount have to be a number'));
            }

            $limit_sup_2 = trim(Tools::getValue('limit_sup_2'));

            if (is_numeric($limit_sup_2) OR is_null($limit_sup_2)) {
                Configuration::updateValue('LOCDEVIS_LIMITSUP2', $limit_sup_2);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Amount have to be a number'));
            }

            $limit_sup_3 = trim(Tools::getValue('limit_sup_3'));

            if (is_numeric($limit_sup_3) OR is_null($limit_sup_3)) {
                Configuration::updateValue('LOCDEVIS_LIMITSUP3', $limit_sup_3);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Amount have to be a number'));
            }

            $coeff_1_12 = trim(Tools::getValue('coeff_1_12'));

            if (is_numeric($coeff_1_12) OR is_null($coeff_1_12)) {
                Configuration::updateValue('LOCDEVIS_COEFF1_12', $coeff_1_12);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_2_12 = trim(Tools::getValue('coeff_2_12'));

            if (is_numeric($coeff_2_12) OR is_null($coeff_2_12)) {
                Configuration::updateValue('LOCDEVIS_COEFF2_12', $coeff_2_12);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_3_12 = trim(Tools::getValue('coeff_3_12'));

            if (is_numeric($coeff_3_12) OR is_null($coeff_3_12)) {
                Configuration::updateValue('LOCDEVIS_COEFF3_12', $coeff_3_12);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_1_24 = trim(Tools::getValue('coeff_1_24'));

            if (is_numeric($coeff_1_24) OR is_null($coeff_1_24)) {
                Configuration::updateValue('LOCDEVIS_COEFF1_24', $coeff_1_24);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_2_24 = trim(Tools::getValue('coeff_2_24'));

            if (is_numeric($coeff_2_24) OR is_null($coeff_2_24)) {
                Configuration::updateValue('LOCDEVIS_COEFF2_24', $coeff_2_24);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_3_24 = trim(Tools::getValue('coeff_3_24'));

            if (is_numeric($coeff_3_24) OR is_null($coeff_3_24)) {
                Configuration::updateValue('LOCDEVIS_COEFF3_24', $coeff_3_24);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_1_36 = trim(Tools::getValue('coeff_1_36'));

            if (is_numeric($coeff_1_36) OR is_null($coeff_1_36)) {
                Configuration::updateValue('LOCDEVIS_COEFF1_36', $coeff_1_36);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_2_36 = trim(Tools::getValue('coeff_2_36'));

            if (is_numeric($coeff_2_36) OR is_null($coeff_2_36)) {
                Configuration::updateValue('LOCDEVIS_COEFF2_36', $coeff_2_36);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_3_36 = trim(Tools::getValue('coeff_3_36'));

            if (is_numeric($coeff_3_36) OR is_null($coeff_3_36)) {
                Configuration::updateValue('LOCDEVIS_COEFF3_36', $coeff_3_36);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_1_48 = trim(Tools::getValue('coeff_1_48'));

            if (is_numeric($coeff_1_48) OR is_null($coeff_1_48)) {
                Configuration::updateValue('LOCDEVIS_COEFF1_48', $coeff_1_48);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_2_48 = trim(Tools::getValue('coeff_2_48'));

            if (is_numeric($coeff_2_48) OR is_null($coeff_2_48)) {
                Configuration::updateValue('LOCDEVIS_COEFF2_48', $coeff_2_48);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_3_48 = trim(Tools::getValue('coeff_3_48'));

            if (is_numeric($coeff_3_48) OR is_null($coeff_3_48)) {
                Configuration::updateValue('LOCDEVIS_COEFF3_48', $coeff_3_48);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_1_60 = trim(Tools::getValue('coeff_1_60'));

            if (is_numeric($coeff_1_60) OR is_null($coeff_1_60)) {
                Configuration::updateValue('LOCDEVIS_COEFF1_60', $coeff_1_60);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_2_60 = trim(Tools::getValue('coeff_2_60'));

            if (is_numeric($coeff_2_60) OR is_null($coeff_2_60)) {
                Configuration::updateValue('LOCDEVIS_COEFF2_60', $coeff_2_60);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $coeff_3_60 = trim(Tools::getValue('coeff_3_60'));

            if (is_numeric($coeff_3_60) OR is_null($coeff_3_60)) {
                Configuration::updateValue('LOCDEVIS_COEFF3_60', $coeff_3_60);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('The Coefficient have to be a number'));
            }

            $max_prod_first_page = trim(Tools::getValue('maxProdFirstPage'));
            if (is_numeric($max_prod_first_page)) {
                Configuration::updateValue('LOCDEVIS_MAXPRODFIRSTPAGE', $max_prod_first_page);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('max product on first page have to be a number'));
            }

            $max_prod_page = trim(Tools::getValue('maxProdPage'));
            if (is_numeric($max_prod_page)) {
                Configuration::updateValue('LOCDEVIS_MAXPRODPAGE', $max_prod_page);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('max product on pages have to be a number'));
            }

            $expire_time = trim(Tools::getValue('expireTime'));

            if (is_numeric($expire_time)) {
                Configuration::updateValue('LOCDEVIS_EXPIRETIME', $expire_time);
            } else {
                $this->erreurs[] = Tools::displayError($this->l('Expiration time have to be a number'));
            }

            Configuration::updateValue('LOCDEVIS_SHOWFREEFORM', (Tools::getValue('showFreeForm') == 1) ? 1 : 0);

            Configuration::updateValue('LOCDEVIS_SHOWACCOUNTBTN', (Tools::getValue('showAccountBtn') == 1) ? 1 : 0);

            /* delete all text */
            $sql = 'DELETE FROM '._DB_PREFIX_.'locdevis_text';
            db::getInstance()->execute($sql);
            $insert = '';
            foreach (Language::getLanguages() as $lang) {
                //freetext
                $values = '"'.pSQL(Tools::getValue('freeText_'.$lang['id_lang']), true).'",0,'.(int)$lang['id_lang'];
                $insert .= ($insert == '') ? '('.$values.')' : ',('.$values.')';
                //validationText
                $values = "'".pSQL(Tools::getValue('validationText_'.$lang['id_lang']), true)."',1,".(int)$lang['id_lang'];
                $insert .= ($insert == '') ? '('.$values.')' : ',('.$values.')';
                //goodforagrementText
                $values = "'".pSQL(Tools::getValue('goodforagrementText_'.$lang['id_lang']), true)."',2,".(int)$lang['id_lang'];
                $insert .= ($insert == '') ? '('.$values.')' : ',('.$values.')';
            }
            $sql = 'INSERT INTO '._DB_PREFIX_.'locdevis_text (text_value,text_type,id_lang) VALUES '.$insert;

            db::getInstance()->execute($sql);
        }
    }
}
