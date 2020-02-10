<?php

class Product extends ProductCore
{
  
    public static function priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency,
        $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, &$specific_price, $use_group_reduction,
        $id_customer = 0, $use_customer_price = true, $id_cart = 0, $real_quantity = 0, $id_customization = 0)
    {
        $specific_price = SpecificPrice::getSpecificPrice(
            (int)$id_product,
            $id_shop,
            $id_currency,
            $id_country,
            $id_group,
            $quantity,
            $id_product_attribute,
            $id_customer,
            $id_cart,
            $real_quantity,
            $id_customization
        );
        
        if (isset($specific_price['price']) && $specific_price['price'] > 0 && isset($specific_price['id_cart']) && $specific_price['id_cart'] > 0) {
            $use_reduc = false;
            $use_group_reduction = false;
        }
        
        return parent::priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency,
        $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, $specific_price, $use_group_reduction,
        $id_customer, $use_customer_price, $id_cart, $real_quantity, $id_customization);
    }

    public static function getPriceStatic($id_product, $usetax = true, $id_product_attribute = null, $decimals = 6, $divisor = null,
        $only_reduc = false, $usereduc = true, $quantity = 1, $force_associated_tax = false, $id_customer = null, $id_cart = null,
        $id_address = null, &$specific_price_output = null, $with_ecotax = true, $use_group_reduction = true, Context $context = null,
        $use_customer_price = true, $id_customization = null)
    {
        $id_cart = ($id_cart == null || $id_cart == '')?0:$id_cart;
        
        return parent::getPriceStatic($id_product, $usetax, $id_product_attribute, $decimals, $divisor,
        $only_reduc, $usereduc, $quantity, $force_associated_tax, $id_customer, $id_cart,
        $id_address, $specific_price_output, $with_ecotax, $use_group_reduction,$context,
        $use_customer_price, $id_customization);
    }
    
}

