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

class HTMLTemplateLocQuotationPdf extends HTMLTemplate {

	public $cart_object;
	public $loc_quotation;

	public function __construct($loc_quotation, $smarty)
	{
		$this->loc_quotation = $loc_quotation;
		$this->cart_object = new Cart($loc_quotation->id_cart);
		$this->delivery_conditions = $loc_quotation->delivery_conditions;
		$this->pricing_conditions = $loc_quotation->pricing_conditions;
		$this->install_conditions = $loc_quotation->install_conditions;
		$this->smarty = $smarty;
		//header
		//$id_lang = Context::getContext()->language->id;
		//$this->title = $this->l('Quotation');
		//footer
		$this->shop = new Shop(Context::getContext()->shop->id);
	}

    public function getContent()
	{
        $context = Context::getContext();
		$max_prod_page = ((int)Configuration::get('LOCDEVIS_MAXPRODPAGE') == 0) ? 13 : (int)Configuration::get('LOCDEVIS_MAXPRODPAGE');
        $max_prod_first_page = ((int)Configuration::get('LOCDEVIS_MAXPRODFIRSTPAGE') == 0) ? 8 : (int)Configuration::get('LOCDEVIS_MAXPRODFIRSTPAGE');

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $pdf_shopping_cart_dir = 'module:locdevis/views/templates/front/pdf/shopping-cart-product-line.tpl';
        } else {
            $pdf_shopping_cart_dir = _PS_MODULE_DIR_ . 'locdevis/views/templates/front/pdf/shopping-cart-product-line.tpl';
        }

        $priceDisplay = ((int)Configuration::get('PS_TAX') == 0)?1:Product::getTaxCalculationMethod((int)$this->cart_object->id_customer);
		$this->smarty->assign(array(
			'cart_obj' => $this->cart_object,
			'message_visible' => nl2br($this->message_visible),
			'delivery_conditions' => nl2br($this->delivery_conditions),
			'pricing_conditions' => nl2br($this->pricing_conditions),
			'install_conditions' => nl2br($this->install_conditions),
			'add_presta' => nl2br($this->add_presta),
			'expire_time' => (int)Configuration::get('LOCDEVIS_EXPIRETIME'),
			'priceDisplay' => $priceDisplay,
			'use_taxes' => (int)Configuration::get('PS_TAX'),
			'validationText' => nl2br($this->loc_quotation->getQuotationText(1, Context::getContext()->language->id)),
			'freeText' => nl2br($this->loc_quotation->getQuotationText(0, Context::getContext()->language->id)),
			'goodforagrementText' => nl2br($this->loc_quotation->getQuotationText(2, Context::getContext()->language->id)),
			'maxProdFirstPage' => $max_prod_first_page,
			'maxProdPage' => $max_prod_page,
			'pdf_shopping_cart_dir' => $pdf_shopping_cart_dir,
			'tax_details' => $this->loc_quotation->getDetailsTax($this->cart_object),
			'quotation_name' => $this->loc_quotation->name,
            'quote_object' => $this->loc_quotation
		));
        // echo "dir=".$pdf_shopping_cart_dir;
        // die();
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            return $this->smarty->fetch('module:locdevis/views/templates/front/pdf/ps17/quotation.tpl');
        } else {
            return $this->smarty->fetch($context->controller->getTemplatePath('pdf/quotation.tpl'));
        //return $this->smarty->fetch(_PS_MODULE_DIR_.'locdevis/views/templates/front/pdf/footer.tpl');
        }
	}

	public function getHeader()
	{
		$shop_name = Configuration::get('PS_SHOP_NAME', null, null, (int)$this->cart_object->id_shop);
		$path_logo = $this->getLogo();
		$width = 0;
		$height = 0;
		if (!empty($path_logo))
			list($width, $height) = getimagesize($path_logo);

                //Limit the height of the logo for the PDF render
                $maximum_height = 100;
                if ($height > $maximum_height) {
                    $ratio = $maximum_height / $height;
                    $height *= $ratio;
                    $width *= $ratio;
                }

		$this->smarty->assign(array(
			'logo_path' => $path_logo,
			'img_ps_dir' => 'http://'.Tools::getMediaServer(_PS_IMG_)._PS_IMG_,
			'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
			'title' => $this->l('Quotation number').':'.$this->loc_quotation->id,
			'date' => Tools::displayDate($this->cart_object->date_upd),
			'shop_name' => $shop_name,
			'width_logo' => $width,
			'height_logo' => $height
		));
		return $this->smarty->fetch($this->getTemplate('header'));
	}

	protected static function l($string)
	{
		return Translate::getModuleTranslation('locdevis', $string, 'htmltemplatequotationpdf');
	}

	public function getFooter()
	{
		$shop_address = $this->getShopAddress();
		$this->smarty->assign(array(
			'available_in_your_account' => $this->available_in_your_account,
			'shop_address' => $shop_address,
			'shop_fax' => Configuration::get('PS_SHOP_FAX', null, null, (int)$this->cart_object->id_shop),
			'shop_phone' => Configuration::get('PS_SHOP_PHONE', null, null, (int)$this->cart_object->id_shop),
			'shop_details' => Configuration::get('PS_SHOP_DETAILS', null, null, (int)$this->cart_object->id_shop),
			'free_text' => Configuration::get('PS_INVOICE_FREE_TEXT', (int)Context::getContext()->language->id, null, (int)$this->cart_object->id_shop)
		));
		return $this->smarty->fetch(_PS_MODULE_DIR_.'locdevis/views/templates/front/pdf/footer.tpl');
	}

	/*
	 * Returns the template filename
	 * @return string filename
	 */

	public function getFilename()
	{
		return self::l('Quotation').'_'.$this->loc_quotation->id.'.pdf';
	}

	/*
	 * Returns the template filename when using bulk rendering
	 * @return string filename
	 */

	public function getBulkFilename()
	{
		return self::l('quotation').'.pdf';
	}

	protected function getLogo()
	{
		$logo = '';
		if (Configuration::get('PS_LOGO_INVOICE', null, null, (int)$this->cart_object->id_shop) != false &&
			file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, null, (int)$this->cart_object->id_shop)))
			$logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, null, (int)$this->cart_object->id_shop);
		elseif (Configuration::get('PS_LOGO', null, null, (int)$this->cart_object->id_shop) != false &&
			file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, (int)$this->cart_object->id_shop)))
			$logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, (int)$this->cart_object->id_shop);
		return $logo;
	}

    /** since 1.6.1.5 **/
    public function getPagination()    {
        return false;
        //return $this->smarty->fetch($this->getTemplate('pagination'));
    }

}
