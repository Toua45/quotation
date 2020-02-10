<?php

class SpecificPrice extends SpecificPriceCore
{
    public static function getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, 
        $id_product_attribute = null, $all_combinations = false, $id_customer = 0)
    {        
        $targeted_prices = parent::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, 
        $id_product_attribute, $all_combinations, $id_customer);
        foreach($targeted_prices as $key=>$target) {
            if($target['id_cart']!=0)
                unset($targeted_prices[$key]);
        }        
        return $targeted_prices;
    }    
        
    public static function getSpecificPrice($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute = null, $id_customer = 0, $id_cart = 0, $real_quantity = 0)
	{
        if($id_cart == 0 && $quantity == 0)
            $quantity = $real_quantity;
        return parent::getSpecificPrice($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute, $id_customer, $id_cart, $real_quantity);
    }
}

