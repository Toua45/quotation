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

require_once _PS_MODULE_DIR_.'locdevis/models/LocQuotation.php';

class LocDevisCreateQuotationModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;

        parent::init();
    }

    /* compatibility 1.6 et 1.5  ??? **/
    protected function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getModuleTranslation('locdevis', $string, 'createquotation');
    }

    /* this function is no longer used */
    public function displayError($string)
    {
        if (_PS_VERSION_ >= '1.7') {
            return $this->trans($string, array(), 'Shop.Notifications.Error');
        }

        return Tools::displayError($this->l($string));
    }

    /* for prestashop 1.7 compatibility */
    private function addMissingSmartyVar()
    {
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';

        $this->context->smarty->assign(array(
            'priceDisplay' => Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer),
            'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
            'ps_base_url' => _PS_BASE_URL_SSL_,
            'content_dir' => $protocol_content.Tools::getHttpHost().__PS_BASE_URI__,
            'currency' => $this->context->currency,
        ));
    }

    public function initContent()
    {
        if (Tools::getIsset('ajax_carrier_list')) {
            $quoteObj = new LocQuotation();
            $json = $quoteObj->getCarriersList();
            echo $json;
            die();
        }

        if (Tools::getIsset('change_carrier_cart')) {
            $quoteObj = new LocQuotation();
            $cart = $quoteObj->changeCarrierCart();
            $summary = $cart->getSummaryDetails(null, true);
            echo tools::jsonEncode($summary);
            die();
        }

        parent::initContent();

        if (_PS_VERSION_ >= '1.7') {
            $this->addMissingSmartyVar();
        }

        $show_form = true;
        $cart = $this->context->cart;
        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($customer)) {
            //$back_url = $this->context->link->getModuleLink('locdevis', 'createquotation', array('create' => true));
            $this->context->smarty->assign(array(
                'LOCDEVIS_SHOWFREEFORM' => Configuration::get('LOCDEVIS_SHOWFREEFORM'),
                'back' => ''
            ));

            if (_PS_VERSION_ >= '1.7') {
                $this->setTemplate('module:locdevis/views/templates/front/ps17/pleaselog.tpl');
            } else {
                $this->setTemplate('pleaselog.tpl');
            }

            return false;
        }

        if (Tools::getValue('create')) {
            //get customers addresses
            if (!Validate::isLoadedObject($customer)) {
                $addresses = array();
            } else {
                $addresses = $customer->getAddresses($this->context->language->id);
            }

            /*if (count($addresses) == 0)
            $this->errors[] = $this->displayError('You have to save at least one address, before creating your quotation');
            */

            if (count($addresses) == 0) {
                $this->errors[] = $this->displayError($this->l('You have to save at least one address, before creating your quotation'));
            }

            if ($cart->nbProducts() == 0) {
                $show_form = false;
                $this->context->smarty->assign('cartEmpty', true);
            }

            $from = (Tools::getIsset('from')) ? Tools::getValue('from') : '';

            //if(isset($useSSL) AND $useSSL AND Configuration::get('PS_SSL_ENABLED'))

            //if(Configuration::get('PS_SSL_ENABLED'))
            //    $ps_base_url = _PS_BASE_URL_SSL_.__PS_BASE_URI__;
            //else
            //    $ps_base_url = _PS_BASE_URL_.__PS_BASE_URI__;

            if ($this->errors) {
                $show_form = false;
            }

            //search id by cart
            $quotationObj = LocQuotation::getQuotationByCartId($cart->id);

            if (is_object($quotationObj)) {
                $quotationId = $quotationObj->id_locdevis;
                $quotationName = $quotationObj->name;
            } else {
                $quotationId = null;
                $quotationName = '';
            }

            $summary = $cart->getSummaryDetails();
            $customized_datas = Product::getAllCustomizedDatas($cart->id);

            if ($customized_datas) {
                foreach ($summary['products'] as &$product_update) {
                    $product_id = (isset($product_update['id_product']) ? $product_update['id_product'] : $product_update['product_id']);
                    $product_attribute_id = (isset($product_update['id_product_attribute']) ?
                        $product_update['id_product_attribute'] : $product_update['product_attribute_id']);

                    if (isset($customized_datas[$product_id][$product_attribute_id])) {
                        $product_update['tax_rate'] = Tax::getProductTaxRate($product_id, $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                    }
                }

                Product::addCustomizationPrice($summary['products'], $customized_datas);
            }

            /*echo "<pre>";
            print_r($customized_datas);
            print_r($summary['products']);*/

            $this->context->smarty->assign(array(
                'addresses' => $addresses,
                'loc_module_dir' => _MODULE_DIR_.'locdevis',
                //'ps_base_url' => $ps_base_url,
                'customerId' => $customer->id,
                'id_carrier' => $cart->id_carrier,
                'summary' => $summary,
                'customizedDatas' => $customized_datas,
                'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
                'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
                'PS_UPLOAD_DIR' => _PS_UPLOAD_DIR_,
                'id_cart' => $cart->id,
                'showForm' => $show_form,
                'from' => $from,
                'quotationId' => $quotationId,
                'quotationName' => $quotationName,
            ));

            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/front.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/createquotation.js');

            if (_PS_VERSION_ >= '1.7') {
                $this->setTemplate('module:locdevis/views/templates/front/ps17/create.tpl');
            } else {
                $this->setTemplate('create.tpl');
            }
        }

        if (Tools::isSubmit('submitQuotation')) {
            if (Tools::getIsset('change_carrier_cart')) {
                return false;
            }

            $cart->id_address_delivery = (int)Tools::getValue('delivery_address');
            $cart->id_address_invoice = (int)Tools::getValue('invoice_address');
            $cart->id_carrier = Tools::getValue('loc_devis_carrier_input');
            $cart->update();

            //create specific price
            $listProd=$cart->getProducts(true);

            foreach ($listProd as &$prod) {
                //if specific price already exist for this cart we don't create a new one
                if (!LocQuotation::specificPriceExist($cart->id, $prod['id_product'], $prod['id_product_attribute'], $cart->id_shop)) {
                    $price = Product::getPriceStatic($prod['id_product'], false, $prod['id_product_attribute'], 6, null, false, true, (int) 1, false, $customer->id, 0, $cart->id_address_delivery, $specific_price_output, false, true, $this->context, true);
                    $prod['specific_qty'] = $prod['cart_quantity'];
                    $prod['id'] = $prod['id_product'];
                    $prod['id_attribute'] = $prod['id_product_attribute'];
                }
            }

            LocQuotation::addSpecificPrice($listProd, $cart, $customer->id);
            $quotationId = Tools::getValue('quotationId');

            $new_quotation = LocQuotation::createQuotation(
                $cart,
                $customer,
                $quotationId,
                Tools::getValue('quotation_name'),
                Tools::getValue('message_visible'),
                Tools::getValue('delivery_conditions'),
                Tools::getValue('install_conditions'),               
                Tools::getValue('message_not_visible'),
                false
            );

            $link = new Link;
            $redirect_link = $link->getModuleLink('locdevis', 'createquotation', array('confirm' => $new_quotation->id));

            //reset current panier customer
            $this->context->cookie->__unset('id_cart', $cart->id);

            Tools::redirect($redirect_link);
        }

        if (Tools::getValue('confirm')) {
            $new_quotation = new LocQuotation(Tools::getValue('confirm'));
            if ($new_quotation->isAllowed()) {
                $this->context->smarty->assign('id_cart', $new_quotation->id_cart);

                if (_PS_VERSION_ >= '1.7') {
                    $this->setTemplate('module:locdevis/views/templates/front/ps17/confirm.tpl');
                } elseif (version_compare(_PS_VERSION_, '1.6.0', '<')) {
                    $this->setTemplate('ps15/confirm.tpl');
                } else {
                    $this->setTemplate('confirm.tpl');
                }

                if (Configuration::get('LOCDEVIS_SENDMAILTOCUSTOMER') == 1) {
                    $new_quotation->sendMailToCustommer($this->context);
                }

                if (Configuration::get('LOCDEVIS_SENDMAILTOADMIN') == 1) {
                    $new_quotation->sendMailToAdmin($this->context);
                }
            } else {
                //$this->errors[] = $this->displayError('You are not allowed to access this quote');
                $this->errors[] = Tools::displayError($this->l('You are not allowed to access this quote'));
            }
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/locdevis.css');

        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/locdevis_15.css');
        }
    }
}
