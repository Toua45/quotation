<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'quotation` (
           `id_quotation` int(11) NOT NULL AUTO_INCREMENT,
    	   `id_cart` int(10) NOT NULL,
		   `id_customer` int(10) NOT NULL,
           `reference` varchar(128),
           `message_visible` TEXT,
           `id_customer_thread` int(10),
		   `date_add` DATETIME NOT NULL,
           `status` int(2) DEFAULT 0,
    PRIMARY KEY  (`id_quotation`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'quotation_text` (
		  `id_quotation_text` int(10) NOT NULL AUTO_INCREMENT,
		  `text_value` TEXT NOT NULL,
		  `text_type` int(10) NOT NULL,
                  `id_lang` int(10) NOT NULL,
  		PRIMARY KEY (`id_quotation_text`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
