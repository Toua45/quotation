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

class LocdevisloadModuleFrontController extends ModuleFrontController {

	public function initContent()
	{
		parent::initContent();
		$id_customer = $this->context->customer->id;
		$id_locdevis = Tools::getValue('locquotationId');
		if (is_numeric($id_locdevis))
		{
			$id_locdevis = (int)Tools::getValue('locquotationId');
			$sql = 'SELECT * FROM `'._DB_PREFIX_.'locdevis` WHERE id_customer='.(int)$id_customer.' AND id_locdevis='.(int)$id_locdevis;
			$result = Db::getInstance()->getRow($sql);
			if (is_array($result))
			{
				$obj = new LocQuotation();
				if ($obj->statut == 3 || $obj->statut == 2)
					die($this->l('This quotation is no more valid'));

				$cart_obj = new Cart($result['id_cart']);
				$id_cart = $cart_obj->id;
				$this->context->cookie->__set('id_cart', $id_cart);
                                if(Tools::getValue('proceedCheckout') == true) 
                                    Tools::redirect('index.php?controller=order&step=3');
                                else
                                    if (_PS_VERSION_ >= '1.7') 
                                        Tools::redirect('index.php?controller=cart&action=show');
                                    else                                        
                                        Tools::redirect('index.php?controller=order');
			}
		}
		else
                    Tools::redirect('index.php?controller=my-account');
	}

	public function l($string)
	{
		return Translate::getModuleTranslation('locdevis', $string, 'createquotation');
	}

}

?>