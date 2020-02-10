<?php
/**
 * Module Locdevis
 *
 * @category Prestashop
 * @category Module
 * @author    Florian de ROCHEFORT
 * @copyright AQUAPURE
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

require_once _PS_MODULE_DIR_ . 'locdevis/models/LocQuotation.php';

class AdminLocdevisController extends ModuleAdminController
{
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (_PS_VERSION_ >= '1.7') {
            return Context::getContext()->getTranslator()->trans($string);
        } else {
            return parent::l($string, $class, $addslashes, $htmlentities);
        }
    }

    public function __construct()
    {
        $this->ps_versions= array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->table = 'locdevis';
        $this->name = 'locdevis';
        $this->className = 'LocQuotation';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items?')
            )
        );
        $this->context = Context::getContext();

        $this->_select = '
            a.*, a.date_add expire_date, a.id_cart company_name,
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)';

        $this->_orderBy = 'a.date_add';
        $this->_orderWay = 'DESC';
        $this->context->smarty->assign(array(
            'module_name' => $this->name,
            'moduledir' => _MODULE_DIR_ . $this->name . '/',
            'ps_base_url' => _PS_BASE_URL_SSL_
        ));

        if (!(int) Configuration::get('PS_SHOP_ENABLE')) {
            $this->errors[] = ($this->l('Your shop is not enable: Carrier and customer list will not be loaded'));
        }

        $this->fields_list = array(
            'id_locdevis' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'width' => 'auto',
                'orderby' => false,
                'search' => false,
            ),
            'id_customer_thread' => array(
                'title' => $this->l('Message'),
                'width' => 'auto',
                'callback' => 'showMessageLink',
                'orderby' => false,
                'search' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'width' => 'auto',
                'filter_key' => 'a!date_add'
            ),
            'expire_date' => array(
                'title' => $this->l('Expire date'),
                'width' => 'auto',
                'callback' => 'displayExpireDate',
                'orderby' => false,
                'search' => false,
            ),
            'id_cart' => array(
                'title' => $this->l('Total'),
                'width' => 'auto',
                'callback' => 'getOrderTotalUsingTaxCalculationMethod',
                'orderby' => false,
                'search' => false,
            ),
            'company_name' => array(
                'title' => $this->l('Company'),
                'width' => 'auto',
                'callback' => 'getCompanyName',
                'orderby' => false,
                'search' => false,
            ),
            'statut' => array(
                'title' => $this->l('Statut'),
                'width' => 'auto',
                'callback' => 'getStatutName',
                'orderby' => false,
                'search' => false,
            ),
            'id_order' => array(
                'title' => $this->l('Order'),
                'width' => 'auto',
                'callback' => 'showOrderLink',
                'orderby' => false,
                'search' => false,
            )
        );

        parent::__construct();
    }

    public function setMedia()
    {
        $this->addCSS(__PS_BASE_URI__ . 'modules/locdevis/views/css/locdevis_admin.css');

        return parent::setMedia();
    }

    public function getStatutName($val)
    {
        $nameArray[0] = $this->l('Validation needed');
        $nameArray[1] = $this->l('Validated');
        $nameArray[2] = $this->l('Ordered');
        $nameArray[3] = $this->l('Expired');

        return $nameArray[$val];
    }

    public function renderList()
    {
        /* delete quote without cart */
        LocQuotation::deleteQuoteWithoutCart();
        LocQuotation::checkValidityAllquote();

        $this->addRowAction('view');
        $this->addRowAction('edit');
        $this->addRowAction('viewcustomer');
        $this->addRowAction('createorder');
        $this->addRowAction('sendbymail');
        $this->addRowAction('sendbymailtoadmin');
        $this->addRowAction('validate');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        $this->initToolbar();
        $lists = parent::renderList();
        //parent::initToolbar();
        $html=$this->context->smarty->fetch(_PS_MODULE_DIR_ . '/locdevis/views/templates/admin/header.tpl');
        $html .= $lists;

        return $html;
    }

    public function displayExpireDate($val)
    {
        return LocQuotation::calc_expire_date($val);
    }

    public static function getOrderTotalUsingTaxCalculationMethod($id_cart)
    {
        //die('afficher msg erreur si cart existe plus');
        $context = Context::getContext();
        $context->cart = new Cart($id_cart);

        if (!$context->cart->id) {
            return 'error';
        }

        $context->currency = new Currency((int)$context->cart->id_currency);
        $context->customer = new Customer((int)$context->cart->id_customer);

        return Cart::getTotalCart($id_cart, true, Cart::BOTH);
    }

    public static function getCompanyName($id_cart)
    {
        //$context = Context::getContext();
        $cart = new Cart($id_cart);
        $address_invoice = new Address($cart->id_address_invoice);

        return $address_invoice->company;
    }

    public function showMessageLink($val)
    {
        if ($val != 0) {
            $token = Tools::getAdminToken('AdminCustomerThreads' .
            (int) Tab::getIdFromClassName('AdminCustomerThreads') .
            (int) $this->context->cookie->id_employee);
            $href = 'index.php?controller=AdminCustomerThreads&id_customer_thread=' . $val . '&viewcustomer_thread&token=' . $token;

            return '<a href="' . $href . '">' . $this->l('read') . '</a>';
        } else {
            return '-';
        }
    }

    public function showOrderLink($val)
    {
        if ($val != 0) {
            $token = Tools::getAdminToken('AdminOrders' .
            (int) Tab::getIdFromClassName('AdminOrders') .
            (int) $this->context->cookie->id_employee);
            $href = 'index.php?controller=AdminOrders&id_order=' . $val . '&vieworder&token=' . $token;
            return '<a href="' . $href . '">' . $val . '</a>';
        } else {
            return '-';
        }
    }

    public function displayViewcustomerLink($token = null, $id)
    {
        if (!array_key_exists('viewcustomer', self::$cache_lang)) {
            self::$cache_lang['viewcustomer'] = $this->l('View customer');
        }
        $token = Tools::getAdminToken('AdminCustomers' . (int) Tab::getIdFromClassName('AdminCustomers') . (int) $this->context->cookie->id_employee);

        $new_quotation = new LocQuotation($id);
        $this->context->smarty->assign(array(
            'href' => 'index.php?controller=AdminCustomers&id_customer=' . $new_quotation->id_customer . '&viewcustomer&token=' . $token,
            'action' => self::$cache_lang['viewcustomer'],
        ));
        //return $this->context->smarty->fetch('helpers/list/list_action_supply_order_change_state.tpl');

        return $this->context->smarty->fetch('helpers/list/list_action_default.tpl');
    }

    public function displayCreateorderLink($token = null, $id)
    {
        if (!array_key_exists('createorder', self::$cache_lang)) {
            self::$cache_lang['createorder'] = $this->l('Create order');
        }
        $token = Tools::getAdminToken('AdminOrders' . (int) Tab::getIdFromClassName('AdminOrders') . (int) $this->context->cookie->id_employee);

        $new_quotation = new LocQuotation($id);
        $this->context->smarty->assign(array(
            'href' => 'index.php?controller=AdminOrders&id_cart=' . $new_quotation->id_cart . '&addorder&token=' . $token,
            'action' => self::$cache_lang['createorder'],
        ));

        return $this->context->smarty->fetch('helpers/list/list_action_default.tpl');
    }

    public function displayValidateLink($token = null, $id)
    {
        if (!array_key_exists('validate', self::$cache_lang)) {
            self::$cache_lang['validate'] = $this->l('Validate');
        }

        //$token = Tools::getAdminToken('AdminOrders' . (int) Tab::getIdFromClassName('AdminOrders') . (int) $this->context->cookie->id_employee);

        $quote = new LocQuotation($id);
        if ($quote->statut != 0) {
            return '';
        }

        $this->context->smarty->assign(array(
            'href' => 'index.php?controller=AdminLocdevis&id_locdevis=' . $id . '&validate&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['validate'],
        ));

        //return $this->context->smarty->fetch('helpers/list/list_action_addstock.tpl');
        return $this->context->smarty->fetch('helpers/list/list_action_default.tpl');
    }

    public function displaySendbymailLink($token = null, $id)
    {
        if (!array_key_exists('sendbymail', self::$cache_lang)) {
            self::$cache_lang['sendbymail'] = $this->l('Send by email to customer');
        }

        $this->context->smarty->assign(array(
            'href' => 'index.php?controller=AdminLocdevis&id_locdevis=' . $id . '&sendbymail&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['sendbymail'],
        ));

        //return $this->context->smarty->fetch('helpers/list/list_action_addstock.tpl');
        return $this->context->smarty->fetch('helpers/list/list_action_default.tpl');
    }

    public function displaySendbymailtoadminLink($token = null, $id)
    {
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&sendbymailtoadmin&token=' . ($token != null ? $token : $this->token),
            'confirm' => $this->l('Are you sure you want to send this quotation to customer?'),
            'action' => $this->l('Send mail to admin'),
            'id' => $id,
        ));

        //return $this->context->smarty->fetch('helpers/list/list_action_addstock.tpl');
        return $this->context->smarty->fetch('helpers/list/list_action_default.tpl');
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        if (isset($obj->id_customer) && is_numeric($obj->id_customer)) {
            $customer = new Customer($obj->id_customer);
        }

        //p($obj);

        if (isset($obj->id_cart) && is_numeric($obj->id_cart)) {
            $cart = new Cart($obj->id_cart);
            $products = $cart->getProducts();
            $customized_datas = Product::getAllCustomizedDatas($cart->id);
            Context::getContext()->cart = $cart;
            $context = Context::getContext();
        }

        if (isset($products) && count($products) > 0) {
            foreach ($products as &$prod) {
                $row = $this->getYourPrice($obj->id_cart, $prod['id_product'], $prod['id_product_attribute'], $cart->id_customer, true);
                $prod['your_price'] = $row['price'];
                $prod['specific_qty'] = $row['from_quantity'];
                //get catalog price
                $prod['catalogue_price'] = Product::getPriceStatic($prod['id_product'], false, $prod['id_product_attribute'], 2, null, false, true, 1, false, null, null, null, $specific_price_output, false, true, null, false);
                $prod['specific_price'] = Product::getPriceStatic($prod['id_product'], false, $prod['id_product_attribute'], 2, null, false, true, $prod['cart_quantity'], false, $cart->id_customer, 0, null, $specific_price_output, false, true, $context, true);
                $prod['customization_datas_json'] = '';
            }
        }

        if (isset($customized_datas)) {
            foreach ($products as &$product) {
                if (!isset($customized_datas[$product['id_product']][$product['id_product_attribute']][$product['id_address_delivery']])) {
                    continue;
                }

                if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
                    foreach ($customized_datas[$product['id_product']][$product['id_product_attribute']][$product['id_address_delivery']] as $customized_data) {
                        if ($customized_data['datas'][1][0]['id_customization'] == $product['id_customization']) {
                            $product['customization_datas'][] = $customized_data;
                        }
                    }
                } else {
                    foreach ($customized_datas[$product['id_product']][$product['id_product_attribute']][$product['id_address_delivery']] as $customized_data) {
                        $product['customization_datas'][] = $customized_data;
                    }
                }

                $product['customization_datas_json'] = Tools::jsonEncode($product['customization_datas']);
            }
        }
        $accessories = array();

        $this->context->smarty->assign(array(
            'obj' => $obj,
            'customer' => (isset($customer)) ? $customer : null,
            'cart' => (isset($cart)) ? $cart : null,
            'summary' => (isset($cart)) ? $cart->getSummaryDetails() : null,
            'products' => (isset($products)) ? $products : null,
            'accessories' => $accessories,
            'flag' => false,
            'view_flag' => _MODULE_DIR_,
            'dir_flag' => Tools::getValue('id_locdevis'),
            'pathuploadfiles' => _PS_MODULE_DIR_ . 'locdevis/uploadfiles/'.Tools::getValue('id_locdevis'),
            'cart_rules' => $this->getAllCartRules(),
            'id_lang_default' => $this->context->language->id,
            'loc_module_dir' => _MODULE_DIR_ . $this->name,
            'href' => self::$currentIndex . '&AdminLocdevis&addlocdevis&token=' . $this->token,
            'hrefCancel' => self::$currentIndex . '&token=' . $this->token,
            'loc_token' => $this->token,
            'currency_sign' => $this->context->currency->sign,
            'json_carrier_list' => (isset($cart)) ? $obj->createCarrierList($cart) : '[]',
        ));

        $this->addJqueryPlugin(array('autocomplete'));
        $this->addJS(_MODULE_DIR_ . $this->name . '/views/js/admin.js');
        $this->addJS(_MODULE_DIR_ . $this->name . '/views/js/front.js');

        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/header.tpl');
        $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/form_quotation.tpl');

        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->name . '/views/css/admin_15.css');
        }

        return $html;
    }

    private function getAllCartRules()
    {
        $sql = 'SELECT c.id_cart_rule, c.code, c.description, cl.name FROM ' . _DB_PREFIX_ . 'cart_rule c LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule_lang';
        $sql .= ' cl ON (c.id_cart_rule=cl.id_cart_rule) WHERE c.active=1 AND cl.id_lang='.(int)$this->context->language->id.' GROUP BY c.id_cart_rule ORDER BY c.id_cart_rule';

        $rules = db::getInstance()->executeS($sql);

        return $rules;
    }

    public function getYourPrice($id_cart, $id_product, $id_product_attribute, $id_customer, $get_row = false)
    {
        $sql = 'SELECT price,from_quantity FROM ' . _DB_PREFIX_ . 'specific_price WHERE id_cart=' . (int) $id_cart
            . ' AND id_product=' . (int)$id_product . ' AND id_product_attribute=' . (int) $id_product_attribute.' AND id_customer=' . (int) $id_customer;
        $row = db::getInstance()->getRow($sql);
        if ($get_row) {
            return $row;
        }

        return $row['price'];
    }

    public function postProcess()
    {
        if (Tools::getIsset('ajax_carrier_list')) {
            $quoteObj = new LocQuotation();
            $json = $quoteObj->getCarriersList(true);
            echo $json;
            die();
        }

        if (Tools::getIsset('ajax_customer_list')) {
            $query = Tools::getValue('q', false);
            $context = Context::getContext();

            $sql = 'SELECT c.`id_customer`, c.`firstname`, c.`lastname`
                FROM `' . _DB_PREFIX_ . 'customer` c
                WHERE (c.firstname LIKE \'%' . pSQL($query) . '%\' OR c.lastname LIKE \'%' . pSQL($query) . '%\') GROUP BY c.id_customer';

            $customer_list = Db::getInstance()->executeS($sql);

            die(Tools::jsonEncode($customer_list));
        }

        if (Tools::getIsset('ajax_product_list')) {
            $query = Tools::getValue('q', false);
            $context = Context::getContext();
            $id_customer = Tools::getIsset('id_customer')?Tools::getValue('id_customer'):null;
            foreach (Shop::getShops() as $shop) {	
            	$allowedShops = Configuration::get('LOCDEVIS_ALLOWED_SHOP_'.$shop['id_shop']) == 1 ? $shop['id_shop'] : null ;
            }
            //$id_cart = Tools::getIsset('id_cart')?Tools::getValue('id_cart'):null;
            //echo "id customer = ".$id_customer;
            $sql = 'SELECT p.`id_product`, ps.`id_shop`, pl.`link_rewrite`, p.`reference`, p.`price`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) Context::getContext()->language->id . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (ps.id_product = p.id_product)
                WHERE ps.`id_shop`= ' . (int) $allowedShops . ' AND (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\') 
                GROUP BY p.id_product';

            $prod_list = Db::getInstance()->executeS($sql);

            $context = Context::getContext();
            foreach ($prod_list as $prod) {
                $prod['name']=$prod['name'].' ['.$prod['reference'].']';
                //$price = Product::getPriceStatic($prod['id_product'], false, null, 6, null, false, true, 1, false, $id_customer, null, null, $specific_price_output, true, true, $context, true);

                $price = Product::getPriceStatic($prod['id_product'], false, null, 4, null, false, true, 1, false, null, null, null, $specific_price_output, false, true, null, false);

                $reduced_price = Product::getPriceStatic($prod['id_product'], false, null, 4, null, false, true, 1, false, $id_customer, null, 0, $specific_price_output, false, true, $context, true);

                echo trim($prod['id_product']) . '|' . trim($prod['name']) . '|' . trim($price) . '|' .trim($reduced_price). "\n";
            }
            die();
        }

        if (Tools::getIsset('ajax_load_cart_rule')) {
            /* add cart to context */
            $id_cart = (int) Tools::getValue('idCart');
            $cart = LocQuotation::createCart($id_cart);
            $cart->getProducts();

            $context = Context::getContext();
            $id_obj = Tools::getValue('id_cart_rule');
            $obj = new CartRule($id_obj);
            $isNotValid = $obj->checkValidity($context);
            if ($isNotValid) {
                echo Tools::jsonEncode($isNotValid);
            } else {
                echo Tools::jsonEncode($obj);
            }
            die();
        }

        if (Tools::getIsset('ajax_load_declinaisons')) {
            $id_prod = Tools::getValue('id_prod');
            $context = Context::getContext();

            $prod = new Product($id_prod);
            $declinaisons = $prod->getAttributesResume($context->language->id);

            if (empty($declinaisons)) {
                die();
            }

            $result = array();
            foreach ($declinaisons as $dec) {
                $result[$dec['id_product_attribute']] = $dec;
            }
            echo Tools::jsonEncode($result);
            die();
        }

        if (Tools::getIsset('ajax_get_total_cart')) {
            $id_cart = (int) Tools::getValue('idCart');
            $cart = LocQuotation::createCart($id_cart);
            //$cart = LocQuotation::createCart();
            //$cart->getProducts();
            $summary = $cart->getSummaryDetails(null, true);
            $summary['id_cart'] = $cart->id;
            $summary["group_tax_method"] = false;
            $customer = New Customer($cart->id_customer);
            
            if (function_exists('getPriceDisplayMethod')) {
                $summary["group_tax_method"] = (bool) Group::getPriceDisplayMethod($customer->id_default_group);
            }


            echo tools::jsonEncode($summary);
            die();
        }

        if (Tools::getIsset('ajax_delete_upload_file')) {
            $dossier = _PS_MODULE_DIR_ . 'locdevis/uploadfiles/'.Tools::getValue('upload_id');
            $file = Tools::getValue('upload_name');
            unlink($dossier.'/'.$file);
            die();
        }

        if (Tools::getIsset('ajax_delete_specific_price')) {
            $id_cart = Tools::getValue('id_cart');
            $id_product = Tools::getValue('id_product');
            $id_attribute = Tools::getValue('id_attribute');
            LocQuotation::deleteSpecificPrice($id_cart, $id_product = null, $id_attribute = null);
            die();
        }

        if (Tools::getIsset('ajax_address_list')) {
            $id_customer = Tools::getValue('id_customer', false);
            $context = Context::getContext();

            $sql = 'SELECT  a.`alias`, a.`id_address`, a.`lastname`, a.`firstname`, a.`lastname`, a.`company`,
                a.`address1`, a.`address2`, a.`postcode`, a.`city`,cl.`name` as `country_name`
                FROM `' . _DB_PREFIX_ . 'address` a
                LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (a.`id_country`=cl.`id_country` AND cl.id_lang = ' . (int) $context->language->id . ')
                WHERE a.id_customer=' . (int) $id_customer;

            $result = array();
            $address_list = Db::getInstance()->executeS($sql);
            if (count($address_list) > 0) {
                foreach ($address_list as $address) {
                    $result[$address['id_address']] = $address;
                }
            } else {
                $result['erreur'] = 'no address founded';
            }
            echo Tools::jsonEncode($result);
            die();
        }

        if (Tools::getIsset('ajax_get_reduced_price')) {
            $result = array();
            $id_customer = Tools::getValue('loc_devis_customer_id', false);
            $context = Context::getContext();
            $who_is_list = Tools::getValue('whoIs');
            $attribute_list = Tools::getValue('add_attribute');
            $qty_list = Tools::getValue('add_prod');
            $specific_price_list = Tools::getValue('specific_price');

            if (empty($who_is_list)) {
                echo tools::jsonEncode($result);
                die();
            }

            $id_cart = (int) Tools::getValue('idCart');
            $result = array();
            $i=0;

            foreach ($who_is_list as $key => $value) {
                $id_prod = $value;
                $id_attribute = (isset($attribute_list[$key]))?$attribute_list[$key]:0;
                $qty = $qty_list[$key];

                $price = Product::getPriceStatic($id_prod, false, $id_attribute, 2, null, false, true, 1, false, null, null, null, $specific_price_output, false, false, null, false);

                $reduced_price = Product::getPriceStatic($id_prod, false, $id_attribute, 2, null, false, true, $qty, false, $id_customer, null, 0, $specific_price_output, false, true, $context, true);
                //$your_price = ($specific_price_list[$key]!='')?$specific_price_list[$key]:Product::getPriceStatic($id_prod, false, $id_attribute, 2, null, false, true, $qty, false, $id_customer, $id_cart, null, $specific_price_output, true, true, $context, true);

                $your_price = ($specific_price_list[$key]!='')?$specific_price_list[$key]:$this->getYourPrice($id_cart, $id_prod, $id_attribute, $id_customer);

                $computed_id = $value.'_'.$id_attribute;

                $result[$i]['random_id'] = $key;
                $result[$i]['product_id'] = $computed_id;
                $result[$i]['real_price'] = $price;
                $result[$i]['reduced_price'] = $reduced_price;
                $result[$i]['your_price'] = $your_price;
                //p($result);
                $i++;
            }
            echo tools::jsonEncode($result);
            die();
        }

        if (Tools::getIsset('transformThisCartId')) {
            $cart = new Cart(Tools::getValue('transformThisCartId'));
            $customer = new Customer($cart->id_customer);
            $new_quotation = LocQuotation::createQuotation($cart, $customer);

            Tools::redirectAdmin(self::$currentIndex . '&id_locdevis=' . $new_quotation->id . '&updatelocdevis&token=' . $this->token);
        }

        if (Tools::isSubmit('submitAddLocDevis')) {
            if (Tools::getIsset('change_carrier_cart')) {
                die();
            }

            $id_customer = (int) Tools::getValue('loc_devis_customer_id');
            if ($id_customer == '') {
                $this->errors[] = Tools::displayError($this->l('You have to choose a customer'));
            }
            if (count($this->errors) > 0) {
                return;
            }

            //create quotation
            $id_cart = (int) Tools::getValue('idCart');
            $cart = LocQuotation::createCart($id_cart);

            //p($cart);

            $customer = new Customer($id_customer);
            $id_loc_devis = Tools::getValue('id_locdevis');

            $new_quotation = LocQuotation::createQuotation(
                $cart,
                $customer,
                $id_loc_devis,
                Tools::getValue('quotation_name'),
                Tools::getValue('message_visible'),
                null,
                false
            );

            if (isset($_FILES['filelocdevis']) && ($_FILES['filelocdevis']['name'][0] !== '')) {
                $count = count($_FILES['filelocdevis']['name']);
                $dossier = _PS_MODULE_DIR_ . 'locdevis/uploadfiles';
                if (!is_dir($dossier)) {
                    $dr1 = mkdir($dossier, 0777);
                }
                if (!is_dir($dossier . '/' . $new_quotation->id)) {
                    $dr2 = mkdir($dossier . '/'. $new_quotation->id, 0777);
                }
                $taille_maxi = 5242880;
                $extensions = array('.png', '.gif', '.jpg', '.jpeg', '.pdf',
                    '.doc', '.docx', '.txt', '.ppt', '.xls');
                $filesave = array();
                for ($i = 0; $i < $count; $i++) {
                    $fichier = $_FILES['filelocdevis']['name'][$i];
                    $taille = filesize($_FILES['filelocdevis']['tmp_name'][$i]);
                    $extension = strrchr($_FILES['filelocdevis']['name'][$i], '.');
                    //Si l'extension n'est pas dans le tableau
                    if (!in_array($extension, $extensions)) {
                        $this->bulk_actions = array(
                            'extention' => array(
                                'text' => $this->l('You must upload a file type image, pdf, rtf, txt or doc.'),
                                'confirm' => $this->l('You must upload a file type image, pdf, rtf, txt or doc.')
                            )
                        );
                    } else {
                        if ($taille > $taille_maxi) {
                            $this->bulk_actions = array(
                                'extention' => array(
                                    'text' => $this->l('The file is too big...'),
                                    'confirm' => $this->l('The file is too big...')
                                )
                            );
                        } else {
                            if (!isset($erreur) && isset($_FILES['filelocdevis']['error'][$i])) {
                                move_uploaded_file($_FILES['filelocdevis']['tmp_name'][$i], $dossier . '/' . $new_quotation->id . '/' . $fichier);
                            } else {
                                $this->bulk_actions = array(
                                    'extention' => array(
                                        'text' => $this->$erreur,
                                        'confirm' => $this->$erreur
                                    )
                                );
                            }
                        }
                    }
                }
            }

            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
        }

        if (Tools::isSubmit('sendbymail')) {
            $id_locdevis = Tools::getValue('id_locdevis');
            $link = new Link;
            $redirect_link = $link->getModuleLink(
                'locdevis',
                'showpdf',
                array(
                    'id_locdevis' => $id_locdevis,
                    'admin_key' => Configuration::get('PS_LOC_DEVIS_SECURE_KEY'),
                    'sendMailToCustomer' => true
                )
            );

            Tools::redirect($redirect_link);
        }

        if (Tools::isSubmit('sendbymailtoadmin')) {
            $id_locdevis = Tools::getValue('id_locdevis');
            $link = new Link;
            $redirect_link = $link->getModuleLink(
                'locdevis',
                'showpdf',
                array(
                    'id_locdevis' => $id_locdevis,
                    'admin_key' => Configuration::get('PS_LOC_DEVIS_SECURE_KEY'),
                    'sendMailToAdmin' => true
                )
            );

            Tools::redirect($redirect_link);
        }

        if (Tools::isSubmit('view' . $this->table)) {
            $id_locdevis = Tools::getValue('id_locdevis');
            $link = new Link;
            $redirect_link = $link->getModuleLink(
                'locdevis',
                'showpdf',
                array(
                    'id_locdevis' => $id_locdevis,
                    'admin_key' => Configuration::get('PS_LOC_DEVIS_SECURE_KEY')
                )
            );

            Tools::redirect($redirect_link);
        }

        if (Tools::isSubmit('validate')) {
            $id_locdevis = Tools::getValue('id_locdevis');
            $quote = new LocQuotation($id_locdevis);
            //p($quote);
            $quote->validate();
            /*$link = new Link;
            $redirect_link = $link->getModuleLink('locdevis', 'showpdf', array('id_locdevis' => $id_locdevis, 'admin_key' => Configuration::get('PS_LOC_DEVIS_SECURE_KEY')));
            Tools::redirect($redirect_link);*/
        }

        return parent::postProcess();
    }

    public function renderView()
    {
        die('render view please');
    }
}
