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

class LocDevisSendmessageModuleFrontController extends ModuleFrontController {

	public function init()
	{
		$this->display_column_left = false;
		parent::init();
	}

        /* this function is no longer used */
        public function displayError($string) {
            if (_PS_VERSION_ >= '1.7') 
                return $this->trans($string, array(), 'Modules.Locdevis.Shop');
            else 
                return Tools::displayError($this->l($string));
        }
        
        /* for prestashop 1.7 compatibility */
        private function addMissingSmartyVar() {
            $this->context->smarty->assign(array(
                'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
            ));
        }
        
        /* compatibility 1.6 et 1.5  ??? **/
        protected function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
	//public function l($string)
	{
            return Translate::getModuleTranslation('locdevis', $string, 'sendmessage');
	}
        
	public function initContent()
	{
		parent::initContent();

		$customer = $this->context->customer;
                
                if (_PS_VERSION_ >= '1.7') 
                    $this->addMissingSmartyVar();
		//Tools::redirect('index.php?controller=order&step=1');
		if (Validate::isLoadedObject($customer))
		{
			$customer_id = $customer->id;
			$addresses = $customer->getAddresses($this->context->language->id);
		}
		else
		{
			$customer_id = 0;
			$addresses = array();
		}
		$this->context->smarty->assign(array(
			'customer_id' => $customer_id,
			'addresses' => $addresses,
		));

		if (Tools::isSubmit('submitMessage'))
		{
			$loc_quotation = new LocQuotation();

			$customer = $this->context->customer;

			//Tools::redirect('index.php?controller=order&step=1');
			if (!Validate::isLoadedObject($customer)) {
				/*if (!Tools::getValue('customer_firstname'))      
                                    $this->errors[] = $this->displayError('You have to specify your firstname');   
                                 * 
				if (!Tools::getValue('customer_lastname'))
                                    $this->errors[] = $this->displayError('You have to specify your lastname');
                                
				if (!Tools::getValue('customer_email'))
                                    $this->errors[] = $this->displayError('You have to specify your email');
                                
				else if (!Validate::isEmail(Tools::getValue('customer_email')))
                                    $this->errors[] = $this->displayError('Please specify a valid email');
                                
                                */
                                if (!Tools::getValue('customer_firstname'))      
                                    $this->errors[] = Tools::displayError($this->l('You have to specify your firstname'));
                                
                                if (!Tools::getValue('customer_lastname'))
                                    $this->errors[] = Tools::displayError($this->l('You have to specify your lastname'));
                                
				if (!Tools::getValue('customer_email'))
                                    $this->errors[] = Tools::displayError($this->l('You have to specify your email'));
                                
				else if (!Validate::isEmail(Tools::getValue('customer_email')))
                                    $this->errors[] = Tools::displayError($this->l('Please specify a valid email'));
                                
				if (!$this->errors)
				{
					$customer = array();
					$customer['firstname'] = Tools::getValue('customer_firstname');
					$customer['lastname'] = Tools::getValue('customer_lastname');
					$customer['email'] = Tools::getValue('customer_email');
				}
			}
			$invoice_address = (!Tools::getValue('invoice_address')) ? Tools::getValue('invoice_address_text') : Tools::getValue('invoice_address');
			$delivery_address = (!Tools::getValue('delivery_address')) ? Tools::getValue('delivery_address_text') : Tools::getValue('delivery_address');

			$phone = Tools::getValue('customer_phone');
			$message = Tools::getValue('quotation_message');

			/*if (empty($message))
                            $this->errors[] = $this->displayError('Please explain us your request');*/
                        
                        if (empty($message))
                            $this->errors[] = Tools::displayError($this->l('Please explain us your request'));
                        
			if (!$this->errors)
			{
				if ($loc_quotation->sendQuotationRequest($customer, $invoice_address, $delivery_address, $message, $phone, $this->context))
					$this->context->smarty->assign('confirmation', 1);
				else
					$this->errors[] = Tools::displayError($this->l('An error occured during the send of your request'));
			}
		}
                if (_PS_VERSION_ >= '1.7') 
                     $this->setTemplate('module:locdevis/views/templates/front/ps17/sendmessage.tpl');
                else                             
                    $this->setTemplate('sendmessage.tpl');
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/locdevis.css');
		if (version_compare(_PS_VERSION_, '1.6.0', '<'))
                    $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/locdevis_15.css');
                if (_PS_VERSION_ >= '1.7') 
                    $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/locdevis_17.css');
	}

}
