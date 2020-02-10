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

class LocDevisShowPdfModuleFrontController extends ModuleFrontController {

	public function init()
	{
		$this->display_column_left = false;
		parent::init();
	}

	public function initContent()
	{
		if (Tools::getValue('sendMailToCustomer') && Tools::getValue('sendMailToCustomer') == true)
		{
			$new_quotation = new LocQuotation(Tools::getValue('id_locdevis'));
                        if(!$new_quotation->isAllowed())
                            return false;
                        
			if ($new_quotation->sendMailToCustommer($this->context) == true)
				$text = '<span style="color:green">Mail to customer has been sent successfully.</a>';
			else
				$text = '<span style="color:red">An error occurred during sending mail.</a>';

			$text .= '<br /><a href="#" onClick="history.back(-1); return false;">back to quotation list</a>';
			echo $text;
			die();
		}

		if (Tools::getValue('sendMailToAdmin') && Tools::getValue('sendMailToAdmin') == true)
		{
			$new_quotation = new LocQuotation(Tools::getValue('id_locdevis'));
                        if(!$new_quotation->isAllowed())
                            return false;
                        
			if ($new_quotation->sendMailToAdmin($this->context) === true)
                            $text = '<span style="color:green">Mail to admin has been sent successfully.</a>';
			/*else if('noadmincontact')
                            $text = '<span style="color:red">You have to configure your module before use this feature.</a>';*/
                        else 
                            $text = '<span style="color:red">An error occurred during sending mail.</a>';
                        
			$text .= '<br /><a href="#" onClick="history.back(-1); return false;">back to quotation list</a>';
			echo $text;
			die();
		}

		if (Tools::getValue('idCart')) 
			$loc_quotation = LocQuotation::getByIdCart((int)Tools::getValue('idCart'));

		if (Tools::getValue('id_locdevis'))
			$loc_quotation = new LocQuotation((int)Tools::getValue('id_locdevis'));

		if ($loc_quotation == false)
			die('no quotation found');
                
                if(!$loc_quotation->isAllowed())
                    return false;
		$loc_quotation->renderPdf(Context::getContext()->smarty, true, Context::getContext());
	}

}
