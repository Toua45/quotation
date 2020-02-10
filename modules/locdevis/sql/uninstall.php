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

$sql = array();
$sql[] = 'SET foreign_key_checks = 0;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'locdevis`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'locdevis_text`;';
$sql[] = 'SET foreign_key_checks = 1;';
