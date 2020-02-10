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

class LocdevislistModuleFrontController extends ModuleFrontController {


    public function init() {
        $this->display_column_left = false;
        parent::init();
    }

    /* for prestashop 1.7 compatibility */
    private function addMissingSmartyVar() {
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
    
    
    public function getTemplateVarPage() {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-customer-account'] = true;
        return $page;
    }
    
    public function initContent() {
        parent::initContent();
        if (_PS_VERSION_ >= '1.7') 
                    $this->addMissingSmartyVar();
        if(Tools::getIsset('newcart') && Tools::getValue('newcart')==true) {
            //reset current panier customer
            $this->context->cookie->__set('id_cart', $id_cart);
            Tools::redirect('index.php?controller=order');
        }
            
        $id_customer = $this->context->customer->id;

        if (Tools::getValue('action') == 'delete') {
            $id_locdevis = (int) Tools::getValue('locquotationId');
            if (Db::getInstance()->delete('locdevis', 'id_customer =' . (int) $id_customer . ' AND id_locdevis=' . (int) $id_locdevis))
                $this->context->smarty->assign('deleted', 'success');
        }

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'locdevis` WHERE id_customer=' . (int) $id_customer;
        $quotations = Db::getInstance()->executeS($sql);        
        $expiretime = (int) Configuration::get('LOCDEVIS_EXPIRETIME', 0);

        foreach ($quotations as &$quotation) {
            //$quotation['is_valid'] = $obj->isValid($quotation['date_add']);
            //update statut for quote nore more valid
            $obj = new LocQuotation($quotation['id_locdevis']);
            $quotation['statut'] = $obj->checkValidity($quotation['date_add']);
            $quotation['expire_date'] = LocQuotation::calc_expire_date($quotation['date_add']);
        }
        
        $body_classes['page-customer-account'] = true;
        
        $this->context->smarty->assign(array(
            'quotations' => $quotations,
            'expiretime' => $expiretime,
            'body_classes' => $body_classes
            
        ));
        if (_PS_VERSION_ >= '1.7') 
            $this->setTemplate('module:locdevis/views/templates/front/ps17/list.tpl');
        else
            $this->setTemplate('list.tpl');
    }

}
?>