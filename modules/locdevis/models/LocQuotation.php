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

require_once _PS_MODULE_DIR_.'locdevis/HTMLTemplateLocQuotationPdf.php';

class LocQuotation extends ObjectModel
{
    /* @var string Name */

    public $id_locdevis;
    public $id_cart;
    public $id_customer;
    public $name;
    public $date_add;
    //public $expire_date;
    public $message_visible;
    public $id_customer_thread;
    public $statut; //0 to validate, 1 validated, 2 paid, 3 expired
    public $id_order;
    public $smarty;

    /*
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'locdevis',
        'primary' => 'id_locdevis',
        'multilang' => false,
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'date_add' => array('type' => self::TYPE_DATE, 'valide' => 'isDate', 'required' => true),
            'message_visible' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'id_customer_thread' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'statut' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    public static function calc_expire_date($date_add)
    {
        $expire_time = Configuration::get('LOCDEVIS_EXPIRETIME');

        if ($expire_time == 0) {
            return false;
        }

        $date = new DateTime(date('Y-m-d H:i:s', strtotime($date_add)));
        $date->add(new DateInterval('P' . $expire_time . 'D'));

        return $date->format('Y-m-d H:i:s');
    }

    public function isAllowed()
    {
        $cookie = new Cookie('psAdmin');
        $context = Context::getContext();

        if ($cookie->id_employee) {
            return true;
        } elseif ($this->id_customer == $context->customer->id) {
            return true;
        }

        return false;
    }


    public function createCarrierList($cart)
    {
        $result = array();

        if (empty($cart)) {
            return Tools::jsonEncode($result);
        }

        $option_list = $cart->getDeliveryOptionList();

        if (!count($option_list) > 0) {
            return Tools::jsonEncode($result);
        }

        //$customer_obj = new Customer($cart->id_customer);
        //$price_display = Group::getPriceDisplayMethod((int)$customer_obj->id_default_group);
        $price_display = Group::getPriceDisplayMethod(Group::getCurrent()->id);
        $with_tax = ($price_display == 0) ? true : false;
        //die('e = '.$with_tax.' price_display = '.$price_display.' c = '.$customer_obj->id_default_group);
        $result = array();
        $prefered_order = '';
        foreach ($option_list as $options) {
            foreach ($options as $option) {
                if ($option['unique_carrier'] == 1) {
                    foreach ($option['carrier_list'] as $key => $carrier_list) {
                        $result[$key]['price'] = $cart->getPackageShippingCost($key, $with_tax);
                        $result[$key]['name'] = $carrier_list['instance']->name;
                        $result[$key]['taxOrnot'] = ($with_tax == true) ? $this->l('tax incl.') : $this->l('tax excl.');
                        $prefered_order .= $key . ',';
                    }
                }
            }
        }

        /*if (!Tools::getValue('idCart'))
        $cart->delete();*/
        $result['id_cart'] = $cart->id;

        $result['prefered_order'] = rtrim($prefered_order, ',');

        return Tools::jsonEncode($result);
    }

    public function getCarriersList($isAdminControler=false)
    {
        $result = array();
        $id_customer = Tools::getValue('loc_devis_customer_id');
        $context = Context::getContext();

        if ($id_customer == '') {
            return Tools::jsonEncode($result);
        }

        $customer_obj = new Customer($id_customer);
        $context->customer = $customer_obj;

        if (!Tools::getValue('idCart')) {
            $cart = new Cart();
            $cart->id_customer = $id_customer;
            $cart->id_address_delivery = Tools::getValue('invoice_address');
            $cart->id_address_invoice = Tools::getValue('delivery_address');
            $cart->id_currency = $context->currency->id;
            $cart->id_lang = $context->language->id;
            $cart->add();
            $result['id_cart'] = $cart->id;
        } else {
            $cart = new Cart((int)Tools::getValue('idCart'));
            //delete old product
            if ($isAdminControler) {
                foreach ($cart->getProducts() as $prod) {
                    $cart->deleteProduct($prod['id_product'], (int)$prod['id_product_attribute']);
                }
            }

            $cart->updateAddressId($cart->id_address_invoice, (int)Tools::getValue('invoice_address'));
            $cart->updateAddressId($cart->id_address_delivery, (int)Tools::getValue('delivery_address'));
        }
        $result['id_cart'] = $cart->id;

        if ($isAdminControler) {
            //add product
            $add_prod_list = Tools::getValue('add_prod');
            $add_attribute_list = Tools::getValue('add_attribute');
            $who_is_list = Tools::getValue('whoIs');

            if (empty($who_is_list)) {
                return Tools::jsonEncode($result);
            }

            $list_prod = array();
            foreach ($who_is_list as $random_id => $prod_id) {
                $list_prod[$random_id]['id'] = $prod_id;
                $list_prod[$random_id]['qty'] = $add_prod_list[$random_id];
                if (isset($add_attribute_list[$random_id])) {
                    $list_prod[$random_id]['id_attribute'] = $add_attribute_list[$random_id];
                }
            }

            if (!empty($list_prod)) {
                foreach ($list_prod as $random_id => $prod) {
                    if (isset($list_prod[$random_id]['id_attribute'])) {
                        $cart->updateQty($prod['qty'], $prod['id'], $prod['id_attribute']);
                    } else {
                        $cart->updateQty($prod['qty'], $prod['id']);
                    }
                }
            }
        }

        return $this->createCarrierList($cart);
    }

    public function checkValidity($quotation_date = null)
    {
        if ($this->statut == 2 || $this->statut == 3 || Configuration::get('LOCDEVIS_EXPIRETIME') == 0) {
            return $this->statut;
        }

        $quotation_date = ($quotation_date == null) ? $this->date_add : $quotation_date;
        $today = time();
        $quotation_time = strtotime($quotation_date.'+'.Configuration::get('LOCDEVIS_EXPIRETIME').' days');

        if (($quotation_time - $today) <= 0) {
            $this->statut = 3;
            $this->update();
        }

        return $this->statut;
    }

    public static function checkValidityAllquote()
    {
        $sql = 'select id_locdevis from '._DB_PREFIX_.'locdevis where statut=1';
        $res = db::getInstance()->executeS($sql);

        foreach ($res as $resultat) {
            $obj = new LocQuotation($resultat['id_locdevis']);
            $obj->checkValidity();
        }
    }

    /*public static function createQuotation($cart, $customer, $id_loc_devis = null, $quotation_name = null, $message_visible = '',
    $message_not_visible = '', $duplicate_cart = true)*/
    public static function createQuotation(
        $cart,
        $customer,
        $id_loc_devis = null,
        $quotation_name = null,
        $message_visible = '',
        $message_not_visible = '',
        $duplicate_cart = true
    ) {
        if ($duplicate_cart == true) {
            $duplicate = $cart->duplicate();
            $id_cart = $duplicate['cart']->id;
            $new_cart = new Cart($id_cart);
            if (count($cart->getCartRules()) > 0) {
                foreach ($cart->getCartRules() as $rule) {
                    $new_cart->addCartRule($rule['id_cart_rule']);
                }
            }
        } else {
            $new_cart = $cart;
        }

        $customs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
                SELECT *
                FROM '._DB_PREFIX_.'customization c
                LEFT JOIN '._DB_PREFIX_.'customized_data cd ON cd.id_customization = c.id_customization
                WHERE c.id_cart = '.(int)$new_cart->id
        );

        foreach ($customs as $custom) {
            $prod_array = $new_cart->getProducts($custom['id_product']);
            $sql = 'UPDATE '._DB_PREFIX_.'customization SET id_address_delivery='.(int)$prod_array[0]['id_address_delivery'].
                ' WHERE id_customization='.(int)$custom['id_customization'];

            db::getInstance()->execute($sql);
        }

        $date_time = date('Y-m-d H:i:s');

        //save it
        if ($id_loc_devis != null) {
            $new_quotation = new LocQuotation($id_loc_devis);
        } else {
            $new_quotation = new LocQuotation();
        }

        if ($quotation_name == null) {
            $quotation_name = $new_quotation->l('Quote').' '.$date_time;
        }

        $new_quotation->name = $quotation_name;
        $new_quotation->id_cart = $new_cart->id;
        $new_quotation->id_customer = (int)$customer->id;
        $new_quotation->date_add = $date_time;

        /* $message_visible = Tools::getValue('message_visible'); */
        if ($message_visible != '') {
            $new_quotation->message_visible = $message_visible;
        }

        /* $message_not_visible = Tools::getValue('message_not_visible'); */
        if ($message_not_visible != '') {
            $ct = new CustomerThread();

            if (isset($customer->id)) {
                $ct->id_customer = (int)$customer->id;
            }

            $ct->id_shop = (int)$new_cart->id_shop;
            $ct->id_order = 0;
            $ct->id_contact = (int)Configuration::get('LOCDEVIS_ADMINCONTACTID'); //a recuperer de la config
            $ct->id_lang = (int)$new_cart->id_lang;
            $ct->email = $customer->email;
            $ct->status = 'open';
            $ct->token = Tools::passwdGen(12);
            $ct->add();

            $customer_message = new CustomerMessage();
            $customer_message->id_customer_thread = $ct->id;
            $customer_message->message = $message_not_visible;
            $customer_message->ip_address = ip2long(Tools::getRemoteAddr());
            $customer_message->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $customer_message->save();
            $new_quotation->id_customer_thread = $customer_message->id_customer_thread;
        }

        $new_quotation->save();

        return $new_quotation;
    }

    public function renderPdf($smarty, $render = true)
    {
        $this->smarty = $smarty;
        $cart_obj = new Cart($this->id_cart);
        $this->assignSummaryInformations($cart_obj);

        //invoice address
        $invoice_address = new Address($cart_obj->id_address_invoice);
        $formatted_invoice_address = AddressFormat::generateAddress($invoice_address, array(), '<br />', ' ');
        $this->smarty->assign('invoice_address', $formatted_invoice_address);

        //delivery address
        $delivery_address = new Address($cart_obj->id_address_delivery);
        $formatted_delivery_address = AddressFormat::generateAddress($delivery_address, array(), '<br />', ' ');
        $this->smarty->assign('delivery_address', $formatted_delivery_address);

        $this->smarty->assign('quotation_number', $this->id_locdevis);

        $pdf = new PDF($this, 'QuotationPdf', $this->smarty);

        if ($render == false) {
            return $pdf->render(false);
        } else {
            $pdf->render();
        }

        die(); //evite les erreur 500 dans certain cas ?
    }

    public static function specificPriceExist($id_cart, $id_product, $id_product_attribute, $id_shop)
    {
        $sql = 'SELECT id_specific_price FROM '._DB_PREFIX_.'specific_price WHERE id_cart='.(int)$id_cart.' AND id_product='.(int)$id_product.' AND id_product_attribute='.(int)$id_product_attribute.' AND id_shop='.(int)$id_shop;
        if (!db::getInstance()->getValue($sql)) {
            return false;
        } else {
            return true;
        }
    }

    public static function getByIdCart($id_cart)
    {
        if (!is_numeric($id_cart)) {
            return false;
        }

        $sql = 'SELECT id_locdevis FROM '._DB_PREFIX_.'locdevis WHERE id_cart='.(int)$id_cart;
        $id_loc_devis = db::getInstance()->getValue($sql);

        if (!$id_loc_devis) {
            return false;
        }

        $obj = new LocQuotation($id_loc_devis);

        return $obj;
    }

    public function getDetailsTax($cart_obj)
    {
        /*p($cart_obj);
        die();*/
        $products = $cart_obj->getProducts();
        $tax_details = array();
        $cart_rules = $cart_obj->getCartRules();

        if (isset($cart_rules) && count($cart_rules)>0) {
            foreach ($cart_rules as $cart_rule) {
                $tax_details['discount']['total_ttc'] =
                    (!isset($tax_details['discount']['total_ttc']) ? 0 : $tax_details['discount']['total_ttc']) + $cart_rule['value_real'];
                $tax_details['discount']['total_ht'] =
                    (!isset($tax_details['discount']['total_ht']) ? 0 : $tax_details['discount']['total_ht']) + $cart_rule['value_tax_exc'];
            }

            $tax_details['discount']['total_tax'] = $tax_details['discount']['total_ttc'] - $tax_details['discount']['total_ht'];
            //$tax_details['discount']['name'] = sprintf($this->l('Discount'), $shipping_tax_rate);
            $tax_details['discount']['name'] = $this->l('Discount');
        }

        $tax_details['ecotax']['total_tax'] = 0;
        $tax_details['ecotax']['total_ht'] = '--';

        foreach ($products as $product) {
            $rate = number_format($product['rate'], 3);
            $tax_details[$rate]['total_tax'] =
                (!isset($tax_details[$rate]['total_tax']) ? 0 : $tax_details[$rate]['total_tax']) + $product['total_wt'] - $product['total'];

            $tax_details[$rate]['total_ht'] =
                (!isset($tax_details[$rate]['total_ht']) ? 0 : $tax_details[$rate]['total_ht']) + $product['total'];

            if ($product['ecotax']!=0) {
                $tax_details['ecotax']['total_tax'] += $product['ecotax'] * $product['quantity'];
            }

            $tax_details['ecotax']['name']=$this->l('Ecotax');
            $tax_details[$rate]['name'] = $product['tax_name'];
        }

        /*get carrier tax rate*/
        $shipping_tax_rate = Tax::getCarrierTaxRate($cart_obj->id_carrier, $cart_obj->id_address_delivery);
        $shipping_cost_ht = $cart_obj->getTotalShippingCost(null, false);
        $shipping_tax = $cart_obj->getTotalShippingCost(null, true) - $shipping_cost_ht;

        $tax_details['shipping']['total_tax'] = $shipping_tax;
        $tax_details['shipping']['total_ht'] = $shipping_cost_ht;
        $tax_details['shipping']['name'] = sprintf($this->l('shipping tax (%d%%)'), $shipping_tax_rate);

        return $tax_details;
    }

    /* on repete les fonction du orderController :'( * */
    private function getFinalImgSize($src_file)
    {
        $max_height = 80;
        $max_width = 100;
        list($src_width, $src_height, $type) = getimagesize($src_file);
        $width_diff = $max_width / $src_width;
        $height_diff = $max_height / $src_height;

        if ($width_diff > 1 && $height_diff > 1) {
            $final_width = $src_width;
            $final_height = $src_height;
        } elseif ($width_diff > $height_diff) {
            $final_height = $max_height;
            $final_width = round(($src_width * $max_height) / $src_height);
        } else {
            $final_width = $max_width;
            $final_height = round($src_height * $max_width / $src_width);
        }

        return array($final_width,$final_height);
    }

    protected function assignSummaryInformations($cart_obj)
    {
        Context::getContext()->customer = new Customer($cart_obj->id_customer);
        Context::getContext()->cart = $cart_obj;
        $context = Context::getContext();

        $summary = $cart_obj->getSummaryDetails();
        $customized_datas = Product::getAllCustomizedDatas($cart_obj->id);

        if ($customized_datas) {
            foreach ($summary['products'] as &$product_update) {
                $product_id = (isset($product_update['id_product']) ? $product_update['id_product'] : $product_update['product_id']);
                $product_attribute_id = (isset($product_update['id_product_attribute']) ?
                    $product_update['id_product_attribute'] : $product_update['product_attribute_id']);

                if (isset($customized_datas[$product_id][$product_attribute_id])) {
                    $product_update['tax_rate'] = Tax::getProductTaxRate($product_id, $cart_obj->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                }
            }

            Product::addCustomizationPrice($summary['products'], $customized_datas);
        }

        $cart_product_context = Context::getContext()->cloneContext();
        $link = new Link();

        foreach ($summary['products'] as $key => &$product) {
            $product['quantity'] = $product['cart_quantity']; // for compatibility with 1.2 themes

            if ($cart_product_context->shop->id != $product['id_shop']) {
                $cart_product_context->shop = new Shop((int)$product['id_shop']);
            }
            $null = null;
            $product['price_without_specific_price'] = Product::getPriceStatic(
                $product['id_product'],
                !Product::getTaxCalculationMethod(),
                $product['id_product_attribute'],
                2,
                null,
                false,
                false,
                1,
                false,
                $this->id_customer,
                null,
                null,
                $null,
                true,
                true,
                $cart_product_context
            );

            if (Product::getTaxCalculationMethod()) {
                $product['is_discounted'] = $product['price_without_specific_price'] != $product['price'];
            } else {
                $product['is_discounted'] = $product['price_without_specific_price'] != $product['price_wt'];
            }

            $product['image'] = $this->setProductImageInformations($product['id_product'], $product['id_product_attribute']);

            if (is_object($product['image'])) {
                $img_src = _PS_IMG_DIR_.'p/'.$product['image']->getImgPath().'.jpg';
                if (file_exists($img_src)) {
                    $imgSizes = $this->getFinalImgSize($img_src);
                    $product['image_tag'] = '<img src="'.$img_src.'" alt="" width="'.$imgSizes[0].'px" height="'.$imgSizes[1].'px"/>';
                } else {
                    $product['image_tag'] = '';
                }
            } else {
                $product['image_tag'] = '';
            }
        }

        foreach ($summary['gift_products'] as $key => &$gift_product) {
            $gift_product['image'] = $this->setProductImageInformations($gift_product['id_product'], $gift_product['id_product_attribute']);
            if (is_object($gift_product['image'])) {
                $img_src = _PS_IMG_DIR_.'p/'.$gift_product['image']->getImgPath().'.jpg';
                $imgSizes = $this->getFinalImgSize($img_src);
                $gift_product['image_tag'] = '<img src="'.$img_src.'" alt=""  width="'.$imgSizes[0].'px" height="'.$imgSizes[1].'px" />';
            } else {
                $gift_product['image_tag'] = '';
            }
        }

        // Get available cart rules and unset the cart rules already in the cart
        $available_cart_rules = CartRule::getCustomerCartRules(
            $context->language->id,
            (isset($context->customer->id) ? $context->customer->id : 0),
            true,
            true,
            true,
            $cart_obj
        );

        $cart_cart_rules = $cart_obj->getCartRules();

        foreach ($available_cart_rules as $key => $available_cart_rule) {
            if (!$available_cart_rule['highlight'] || strpos($available_cart_rule['code'], 'BO_ORDER_') === 0) {
                unset($available_cart_rules[$key]);
                continue;
            }

            foreach ($cart_cart_rules as $cart_cart_rule) {
                if ($available_cart_rule['id_cart_rule'] == $cart_cart_rule['id_cart_rule']) {
                    unset($available_cart_rules[$key]);
                    continue 2;
                }
            }
        }

        $show_option_allow_separate_package = (!$cart_obj->isAllProductsInStock(true) && Configuration::get('PS_SHIP_WHEN_AVAILABLE'));
        //fix for ps 1.5.2.0 and minor
        $configSize = Configuration::get('LOCDEVIS_IMAGESIZE');
        $smallSize = (method_exists('ImageType', 'getFormatedName'))?Image::getSize(ImageType::getFormatedName('small')):Image::getSize($configSize);


        $context->smarty->assign($summary);
        $context->smarty->assign(array(
            //'token_cart' => Tools::getToken(false),
            //'isLogged' => $this->isLogged,
            'isVirtualCart' => $cart_obj->isVirtualCart(),
            'productNumber' => $cart_obj->nbProducts(),
            'voucherAllowed' => CartRule::isFeatureActive(),
            'shippingCost' => $cart_obj->getOrderTotal(true, Cart::ONLY_SHIPPING),
            'shippingCostTaxExc' => $cart_obj->getOrderTotal(false, Cart::ONLY_SHIPPING),
            'customizedDatas' => $customized_datas,
            'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
            'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
            'lastProductAdded' => $cart_obj->getLastProduct(),
            //'discounts' => $available_cart_rules,
            'currencySign' => $context->currency->sign,
            'currencyRate' => $context->currency->conversion_rate,
            'currencyFormat' => $context->currency->format,
            'currencyBlank' => $context->currency->blank,
            'show_option_allow_separate_package' => $show_option_allow_separate_package,
            'PS_UPLOAD_DIR' => _PS_UPLOAD_DIR_,
            'smallSize' => $smallSize
        ));
    }

    private function setProductImageInformations($product_id, $product_attribute_id)
    {
        if (isset($product_attribute_id) && $product_attribute_id) {
            $id_image = Db::getInstance()->getValue('
                SELECT image_shop.id_image
                FROM '._DB_PREFIX_.'product_attribute_image pai'.
                Shop::addSqlAssociation('image', 'pai', true).'
                WHERE id_product_attribute = '.(int)$product_attribute_id
            );
        }

        if (!isset($id_image) || !$id_image) {
            $id_image = Db::getInstance()->getValue('
                SELECT image_shop.id_image
                FROM '._DB_PREFIX_.'image i'.
                Shop::addSqlAssociation('image', 'i', true, 'image_shop.cover=1').'
                WHERE i.id_product = '.(int)($product_id)
            );
        }

        if ($id_image) {
            return new Image($id_image);
        }

        return false;
    }

    private function getDataArray($context)
    {
        $customer_obj = new Customer($this->id_customer);

        if ($this->id_customer_thread != 0) {
            $ct = new CustomerThread($this->id_customer_thread);
            $messages = $ct->getWsCustomerMessages();
            $cm = new CustomerMessage($messages[0]['id']);
            $customer_message = $cm->message;
        } else {
            $customer_message = '';
        }

        $data = array(
            '{firstname}' => $customer_obj->firstname,
            '{lastname}' => $customer_obj->lastname,
            '{customerMail}' => $customer_obj->email,
            '{shopName}' => Configuration::get('PS_SHOP_NAME'),
            '{shopUrl}' => $context->shop->domain.$context->shop->physical_uri,
            '{shopMail}' => Configuration::get('PS_SHOP_EMAIL'),
            '{shopTel}' => Configuration::get('PS_SHOP_PHONE'),
            '{customerMessage}' => nl2br($customer_message)
        );

        return $data;
    }

    public function sendMailToCustommer($context)
    {
        $data = $this->getDataArray($context);

        //$showPdf=new LocQuotation();

        $customer_obj = new Customer($this->id_customer);
        $filename = $this->l('quotation_').$this->id.'.pdf';
        $dossier = _PS_MODULE_DIR_ . 'locdevis/uploadfiles/'.$this->id_locdevis;
        $file_attachement = array();

        if (is_dir($dossier) && isset($this->id_locdevis)) {
            $file_attachement[0]['content'] = $this->renderPDf($context->smarty, false);
            $file_attachement[0]['name'] = $filename;
            $file_attachement[0]['mime'] = 'application/pdf';
            $file = opendir($dossier);
            $i = 1;
            while (($files = readdir($file)) !== false) {
                if ($files != '.' and $files != '..' and $files != 'index.php') {
                    $file_attachement[$i] = array(
                        'content' => Tools::file_get_contents($dossier.'/'.$files),
                        'name' => $files,
                        'mime' => $this->get_mime_type($files),
                    );
                    $i++;
                }
            }
            closedir($file);
        } else {
            $file_attachement['content'] = $this->renderPDf($context->smarty, false);
            $file_attachement['name'] = $filename;
            $file_attachement['mime'] = 'application/pdf';
        }
        //send mail to customer
        if (Mail::Send(
            (int)$context->language->id,
            'locdevis_customer',
            $this->l('Your quotation'),
            $data,
            $customer_obj->email,
            $customer_obj->firstname.' '.$customer_obj->lastname,
            null,
            null,
            $file_attachement,
            null,
            _PS_MODULE_DIR_.'locdevis/mails/',
            false,
            (int)$context->shop->id
        )) {
            return true;
        } else {
            return false;
        }
    }

    public function sendQuotationRequest($customer, $invoice_address, $delivery_address, $message, $phone, $context)
    {
        $admin_contact = new Contact(Configuration::get('LOCDEVIS_ADMINCONTACTID'));

        if (Validate::isLoadedObject($customer)) {
            $firstname = $customer->firstname;
            $lastname = $customer->lastname;
            $email = $customer->email;
        } else {
            $firstname = $customer['firstname'];
            $lastname = $customer['lastname'];
            $email = $customer['email'];
        }

        if (is_numeric($invoice_address)) {
            $address_obj = new Address($invoice_address);
            $invoice_address = $address_obj->firstname.' '.$address_obj->lastname.'\n '.$address_obj->address1.' \n'.
                $address_obj->address2.'\n '.$address_obj->postcode.' '.$address_obj->city.' '.$address_obj->country;
        }

        if (is_numeric($delivery_address)) {
            $address_obj = new Address($delivery_address);
            $delivery_address = $address_obj->firstname.' '.$address_obj->lastname.'\n '.$address_obj->address1.' \n'.
                $address_obj->address2.'\n '.$address_obj->postcode.' '.$address_obj->city.' '.$address_obj->country;
        }

        $data = array(
            '{firstname}' => $firstname,
            '{lastname}' => $lastname,
            '{customerMail}' => $email,
            '{customerPhone}' => $phone,
            '{customerMessage}' => nl2br($message),
            '{invoiceAddress}' => nl2br($invoice_address),
            '{deliveryAddress}' => nl2br($delivery_address),
        );

        /* send mail to customer ? */
        if (Configuration::get('LOCDEVIS_SENDMAILTOCUSTOMER') == 1) {
            Mail::Send(
                (int)$context->language->id,
                'request_quotation_customer',
                $this->l('Quotation request'),
                $data,
                $email,
                null,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_.'locdevis/mails/',
                false,
                (int)$context->shop->id
            );
        }

        if (Mail::Send(
            (int)$context->language->id,
            'request_quotation_admin',
            $this->l('Quotation request'),
            $data,
            $admin_contact->email,
            null,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_.'locdevis/mails/',
            false,
            (int)$context->shop->id
        )) {
            return true;
        } else {
            return false;
        }
    }

    public function get_mime_type($file)
    {
        // our list of mime types
        $mime_types = array(
            "pdf"=>"application/pdf"
            ,"docx"=>"application/msword"
            ,"doc"=>"application/msword"
            ,"xls"=>"application/vnd.ms-excel"
            ,"ppt"=>"application/vnd.ms-powerpoint"
            ,"gif"=>"image/gif"
            ,"png"=>"image/png"
            ,'txt' => 'text/plain'
            ,'jpe' => 'image/jpeg'
            ,'jpeg' => 'image/jpeg'
            ,'jpg' => 'image/jpeg'
        );

        $exten = explode('.', $file);
        $extension = Tools::strtolower(end($exten));

        return $mime_types[$extension];
    }

    public function sendMailToAdmin($context)
    {
        $data = $this->getDataArray($context);
        //$context=new Context();
        $file_attachement = array();
        $filename = $this->l('quotation_').$this->id.'.pdf';
        $dossier = _PS_MODULE_DIR_ . 'locdevis/uploadfiles/'.$this->id_locdevis;
        $file_attachement = array();

        if (is_dir($dossier) && isset($this->id_locdevis)) {
            $file_attachement[0]['content'] = $this->renderPDf($context->smarty, false);
            $file_attachement[0]['name'] = $filename;
            $file_attachement[0]['mime'] = 'application/pdf';
            $file = opendir($dossier);
            $i = 1;
            while (($files = readdir($file)) !== false) {
                if ($files != '.' and $files != '..' and $files != 'index.php') {
                    $file_attachement[$i] = array(
                        'content' => Tools::file_get_contents($dossier.'/'.$files),
                        'name' => $files,
                        'mime' => $this->get_mime_type($files),
                    );
                    $i++;
                }
            }

            closedir($file);
        } else {
            $file_attachement['content'] = $this->renderPDf($context->smarty, false);
            $file_attachement['name'] = $filename;
            $file_attachement['mime'] = 'application/pdf';
        }
        $admin_contact = new Contact(Configuration::get('LOCDEVIS_ADMINCONTACTID'));
        if (empty($admin_contact)) {
            return 'noadmincontact';
        }
        //send mail to admin
        if (Mail::Send(
            (int)$context->language->id,
            'locdevis_admin',
            $this->l('New quotation'),
            $data,
            $admin_contact->email,
            null,
            null,
            null,
            $file_attachement,
            null,
            _PS_MODULE_DIR_.'locdevis/mails/',
            false,
            (int)$context->shop->id
        )) {
            return true;
        } else {
            return false;
        }
    }

    public static function l($string)
    {
        return Translate::getModuleTranslation('locdevis', $string, 'locquotation');
    }

    public function clearDir($dossier)
    {
        $ouverture=@opendir($dossier);

        if (!$ouverture) {
            return;
        }

        while ($fichier=readdir($ouverture)) {
            if ($fichier == '.' || $fichier == '..') {
                continue;
            }
            if (is_dir($dossier."/".$fichier)) {
                $r=$this->clearDir($dossier."/".$fichier);
                if (!$r) {
                    return false;
                }
            } else {
                $r=@unlink($dossier."/".$fichier);
                if (!$r) {
                    return false;
                }
            }
        }

        closedir($ouverture);
        $r=@rmdir($dossier);

        if (!$r) {
            return false;
        }

        return true;
    }

    public static function deleteSpecificPrice($id_cart, $id_product = null, $id_attribute = null)
    {
        $id_attribute = ($id_attribute == null)?0:$id_attribute;

        if (!isset($id_cart) || $id_cart == 0) {
            return false;
        }

        if ($id_product == null) {
            $sql = 'DELETE FROM `'._DB_PREFIX_.'specific_price` WHERE `id_cart` = '.(int)$id_cart;
        } else {
            $sql ='DELETE FROM `'._DB_PREFIX_.'specific_price` WHERE `id_cart` = '.(int)$id_cart.' AND id_product='.(int)$id_product.' AND id_attribute='.(int)$id_attribute;
        }
        Db::getInstance()->execute($sql);
    }

    public function delete()
    {
        //delete associed card
        $cart = new Cart($this->id_cart);
        $cart->delete();

        //delete specfic price
        LocQuotation::deleteSpecificPrice($this->id_cart);
        $dossier = _PS_MODULE_DIR_ . 'locdevis/uploadfiles/'.$this->id_locdevis;

        if (Db::getInstance()->delete('locdevis', 'id_locdevis='.$this->id_locdevis)) {
            $this->clearDir($dossier);
            return true;
        } else {
            return false;
        }
    }

    public static function getQuotationText($text_type, $id_lang = null)
    {
        if ($id_lang != null) {
            $sql = 'SELECT text_value FROM '._DB_PREFIX_.'locdevis_text WHERE text_type='.(int)$text_type.' AND id_lang='.(int)$id_lang;
            return db::getInstance()->getValue($sql);
        }

        $sql = 'SELECT * FROM '._DB_PREFIX_.'locdevis_text WHERE text_type='.(int)$text_type;
        $results = db::getInstance()->executeS($sql);
        $array = array();

        foreach ($results as $result) {
            $array[$result['id_lang']] = $result['text_value'];
        }

        return $array;
    }

    public function changeCarrierCart()
    {
        $context = Context::getContext();
        $cart = $context->cart;
        $cart->id_address_delivery = (int)Tools::getValue('delivery_address');
        $cart->id_address_invoice = (int)Tools::getValue('invoice_address');
        $cart->id_carrier = (int)Tools::getValue('loc_devis_carrier_input');
        //$cart->save();
        $cart->setDeliveryOption(array($cart->id_address_delivery => (int)$cart->id_carrier.','));
        $cart->update();
        $context->cart = $cart;
        return $cart;
    }

    public static function createCart($id_cart = null)
    {
        $context = Context::getContext();
        if ($id_cart == null) {
            $cart = new Cart();
        } else {
            $cart = new Cart($id_cart);
        }

        Context::getContext()->cart = $cart;
        $id_customer = (int)Tools::getValue('loc_devis_customer_id');
        $cart->id_customer = $id_customer;
        $customer_obj = new Customer($id_customer);
        $context->customer = $customer_obj;
        //empty cart
        $old_prod = $cart->getProducts();
        //p($cart->getProducts());

        foreach ($old_prod as $prod) {
            $customizations = $cart->getProductCustomization($prod['id_product']);
            /*p($customizations);
            if(count($customizations)>0)
            foreach($customizations as $customization) {
            //save old customization
            $cart->deleteProduct($prod['id_product'],$prod['id_product_attribute'],$customization['id_customization'],$prod['id_address_delivery']);
            }
            else*/
            //do not delete custom product here
            if (!count($customizations)>0) {
                $cart->deleteProduct($prod['id_product'], $prod['id_product_attribute']);
            }
        }

        $cart->id_currency = $context->currency->id;
        $cart->id_lang = $context->language->id;
        $cart->id_address_delivery = (int)Tools::getValue('delivery_address');
        $cart->id_address_invoice = (int)Tools::getValue('invoice_address');
        $cart->id_carrier = (int)Tools::getValue('loc_devis_carrier_input');
        $cart->save();

        $add_prod_list = Tools::getValue('add_prod');
        $add_attribute_list = Tools::getValue('add_attribute');
        $add_customization_list = Tools::getValue('add_customization');
        $who_is_list = Tools::getValue('whoIs');
        $list_prod = array();
        $specific_price_list = Tools::getValue('specific_price');
        $specific_qty_list = Tools::getValue('specific_qty');
        if (!empty($who_is_list)) {
            foreach ($who_is_list as $random_id => $prod_id) {
                $list_prod[$random_id]['id'] = $prod_id;
                $list_prod[$random_id]['qty'] = $add_prod_list[$random_id];

                if (isset($add_attribute_list[$random_id])) {
                    $list_prod[$random_id]['id_attribute'] = $add_attribute_list[$random_id];
                } else {
                    $list_prod[$random_id]['id_attribute'] = 0;
                }

                /* customization */
                if (isset($add_customization_list[$random_id])) {
                    //get old qty
                    $oldCustoms=$cart->getProductCustomization($prod_id);
                    $list_prod[$random_id]['qty']=0;
                    foreach ($add_customization_list[$random_id] as $id_customization => $qtyArray) {
                        foreach ($oldCustoms as $oldCustom) {
                            if ($oldCustom['id_customization']==$id_customization) {
                                $oldQty = $oldCustom['quantity'];
                            }
                        }
                        //$qtyToAdd = $qtyArray['newQty'] - $qtyArray['oldQty'];
                        $qtyToAdd = $qtyArray['newQty'] - $oldQty;
                        $list_prod[$random_id]['id_customization'][$id_customization]['operator'] = ($qtyToAdd>0)?'up':'down';
                        $list_prod[$random_id]['id_customization'][$id_customization]['qty'] = abs($qtyToAdd);
                        $list_prod[$random_id]['qty']+=$qtyArray['newQty'];
                    }
                }

                /** specific price * */
                if (isset($specific_price_list[$random_id]) && $specific_price_list[$random_id] != '') {
                    $list_prod[$random_id]['specific_price'] = str_replace(',', '.', $specific_price_list[$random_id]);
                    $list_prod[$random_id]['specific_qty'] = $list_prod[$random_id]['qty'];
                    //$list_prod[$random_id]['specific_qty'] = ($specific_qty_list[$random_id] == '') ? 1 : $specific_qty_list[$random_id];
                } else {//si pas de prix specifique indique alors on enregistre le prix du produit en tant que prix specifique
                    $specific_price_output = null;
                    $price = Product::getPriceStatic($list_prod[$random_id]['id'], false, $list_prod[$random_id]['id_attribute'], 6, null, false, true, (int)$list_prod[$random_id]['qty'], false, $id_customer, 0, $cart->id_address_delivery, $specific_price_output, false, true, $context, true);

                    $list_prod[$random_id]['specific_price'] = $price;
                    $list_prod[$random_id]['specific_qty'] = $list_prod[$random_id]['qty'];
                }
            }
        }

        if (!empty($list_prod)) {
            foreach ($list_prod as $prod) {
                if (isset($prod['id_attribute']) && isset($prod['id_customization'])) {
                    foreach ($prod['id_customization'] as $id_customization=>$customization_array) {
                        if ($customization_array['qty']!=0) {
                            $cart->updateQty($customization_array['qty'], $prod['id'], $prod['id_attribute'], $id_customization, $customization_array['operator']);
                        }
                    }
                } elseif (isset($prod['id_customization'])>0) {
                    foreach ($prod['id_customization'] as $id_customization=>$qty_customization) {
                        if ($customization_array['qty']!=0) {
                            $cart->updateQty($customization_array['qty'], $prod['id'], $prod['id_attribute'], $id_customization, $customization_array['operator']);
                        }
                    }
                } elseif (isset($prod['id_attribute'])) {
                    $cart->updateQty($prod['qty'], $prod['id'], $prod['id_attribute']);
                } else {
                    $cart->updateQty($prod['qty'], $prod['id']);
                }
            }
        }

        //delete old rules
        $old_rules = $cart->getCartRules();
        if (count($old_rules) > 0) {
            foreach ($old_rules as $old_rule) {
                $cart->removeCartRule($old_rule['id_cart_rule']);
            }
        }

        $add_rule = Tools::getValue('add_rule');
        if (!empty($add_rule)) {
            foreach ($add_rule as $id_rule) {
                $cart->addCartRule($id_rule);
            }
        }

        $cart->setDeliveryOption(array($cart->id_address_delivery => (int)$cart->id_carrier.','));
        $cart->update();

        /** add specific price into table * */
        LocQuotation::addSpecificPrice($list_prod, $cart, $id_customer);

        return $cart;
    }

    public static function addSpecificPrice($list_prod, $cart, $id_customer)
    {
        if (!empty($list_prod)) {
            foreach ($list_prod as $prod) {
                if (isset($prod['specific_price']) && $prod['specific_price'] != '' && $cart->id!=0) {
                    SpecificPrice::deleteByIdCart($cart->id, $prod['id'], $prod['id_attribute']);
                    $specific_price = new SpecificPrice();
                    $specific_price->id_cart = (int)$cart->id;
                    $specific_price->id_specific_price_rule = 0;
                    $specific_price->id_product = (int)$prod['id'];
                    $specific_price->id_product_attribute = (int)$prod['id_attribute'];
                    $specific_price->id_customer = $id_customer;
                    $specific_price->id_shop = (int)$cart->id_shop;
                    $specific_price->id_country = 0;
                    $specific_price->id_currency = 0;
                    $specific_price->id_group = 0;
                    $specific_price->from_quantity = (int)$prod['specific_qty'];
                    $specific_price->price = (float)$prod['specific_price'];
                    $specific_price->reduction_type = 'amount';
                    $specific_price->reduction_tax = 0;
                    $specific_price->reduction = 0;
                    $specific_price->from = 0;
                    $specific_price->to = 0;
                    $specific_price->add();
                }
            }
        }
    }

    public static function deleteQuoteWithoutCart()
    {
        $sql = 'SELECT id_locdevis FROM '._DB_PREFIX_.'locdevis o WHERE o.id_cart NOT IN (SELECT id_cart FROM '._DB_PREFIX_.'cart c WHERE c.id_cart = o.id_cart)';

        $results = db::getInstance()->executeS($sql);
        $where = '';

        foreach ($results as $result) {
            $where .= ($where != '')?' OR id_locdevis='.(int)$result['id_locdevis']:' id_locdevis='.(int)$result['id_locdevis'];
        }

        if ($where!='') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'locdevis WHERE '.$where;
            db::getInstance()->execute($sql);
        }
    }

    public static function getQuotationByCartId($idCart)
    {
        if ($idCart == null || !is_numeric($idCart)) {
            return false;
        }

        $sql = 'SELECT id_locdevis FROM '._DB_PREFIX_.'locdevis o WHERE o.id_cart='.pSQL($idCart);
        $quotationId = db::getInstance()->getValue($sql);

        if ($quotationId == false) {
            return false;
        }

        return new LocQuotation($quotationId);
    }

    public function validate()
    {
        $this->date_add = date('Y-m-d H:i:s');
        $this->statut = 1;
        $this->save();
    }

    //send validation mail
    public function sendValidationMail()
    {
    }
}