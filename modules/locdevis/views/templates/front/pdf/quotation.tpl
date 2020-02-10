{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<div style="font-size: 8pt; color: #444">
    <!-- nom -->
    <div style="text-align:center; font-size:1.2em; padding-bottom:3em; font-weight:bold;">{$quotation_name|escape:'htmlall':'UTF-8'}</div><br />
    <!-- ADDRESSES -->
    <table style="width: 100%">
        <tr>
            <td style="width: 17%"></td>
            <td style="width: 83%">
                {if !empty($delivery_address)}
                <table style="width: 100%">
                    <tr>
                        <td style="width: 50%"><span style="font-weight: bold; font-size: 10pt; color: #9E9F9E">{l s='Delivery Address' mod='locdevis'}</span><br />{$delivery_address|escape:'javascript':'UTF-8'}</td>
                        <td style="width: 50%"><span style="font-weight: bold; font-size: 10pt; color: #9E9F9E">{l s='Billing Address' mod='locdevis'}</span><br />{$invoice_address|escape:'javascript':'UTF-8'}</td>
                    </tr>
                </table>
                {else}
                <table style="width: 100%">
                    <tr>

                        <td style="width: 50%">
                            <span style="font-weight: bold; font-size: 10pt; color: #9E9F9E">{l s='Billing & Delivery Address.' mod='locdevis'}</span><br />
                            {$invoice_address|escape:'javascript':'UTF-8'}
                        </td>
                        <td style="width: 50%">

                        </td>
                    </tr>
                </table>
                {/if}
            </td>
        </tr>
    </table>
    <br /><br />
    <!-- / ADDRESSES -->
    <table id="cart_summary" width="100%" style="text-align:left;" cellpadding="3">
        <thead>
            {assign var='odd' value=0}
            <tr style="color:#FFFFFF; background-color: #4D4D4D;">
                <td style="font-weight: bold; text-align:left; width:10%">{l s='Product' mod='locdevis'}</td>
                <td style="font-weight: bold; text-align:left; width:35%">{l s='Description' mod='locdevis'}</td>
                <td style="font-weight: bold; text-align:left; width:10%">{l s='Ref.' mod='locdevis'}</td>
                <td style="font-weight: bold; text-align:left; width:15%">{l s='Availability' mod='locdevis'}</td>
                <td style="font-weight: bold; text-align:left; width:10%">{l s='Unit price' mod='locdevis'} {if $priceDisplay == 1}{l s='tax excl.' mod='locdevis'}{else}{l s='tax incl.' mod='locdevis'}{/if}</td>
                <td style="font-weight: bold; text-align:left; width:5%">{l s='Qty' mod='locdevis'}</td>
                <td style="font-weight: bold; text-align:right; width:15%">{l s='Total' mod='locdevis'} {if $priceDisplay == 1}{l s='tax excl.' mod='locdevis'}{else}{l s='tax incl.' mod='locdevis'}{/if}</td>
            </tr>
    </thead>
            {assign "firstpage" "true"}
            {assign "compteur" 1}
			<tbody>
            {*{foreach from=$cart_obj->getProducts() item=product}*}
			{foreach from=$products item=product}
				{cycle values='#FFF,#DDD' assign=bgcolor}
				{assign var='productId' value=$product.id_product}
				{assign var='productAttributeId' value=$product.id_product_attribute}
				{assign var='quantityDisplayed' value=0}
				{assign var='odd' value=($odd+1)%2}
				{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
				{* Display the product line *}

				{if $firstpage=="true"}
					{assign "modulo" $maxProdFirstPage}
				{else}
					{assign "modulo" $maxProdPage}
				{/if}
				{if $compteur!=1 && $compteur%$modulo==1}
					{assign "compteur" 1}
					{assign "firstpage" "false"}
					<br pagebreak="true"/>
				{/if}
				{assign "compteur" $compteur+1}

				{include file="$pdf_shopping_cart_dir" productLast=$product@last productFirst=$product@first cannotModify=1}
				{* Then the customized datas ones*}
				{if isset($customizedDatas.$productId.$productAttributeId)}
					{foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
						<tr id="product_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}_{$id_customization|escape:'htmlall':'UTF-8'}_{$product.id_address_delivery|intval}">
							<td></td>
							<td colspan="4">
								{foreach $customization.datas as $type => $custom_data}
								{if $type == $CUSTOMIZE_FILE}
								<div>
									<ul>
										{foreach $custom_data as $picture}
										<li><img src="{$PS_UPLOAD_DIR|escape:'htmlall':'UTF-8'}{$picture.value|escape:'htmlall':'UTF-8'}_small" alt="" /></li>
										{/foreach}
									</ul>
								</div>
								{elseif $type == $CUSTOMIZE_TEXTFIELD}
								<ul>
									{foreach $custom_data as $textField}
									<li>
										{if $textField.name}
										{$textField.name|escape:'htmlall':'UTF-8'}
										{else}
										{l s='Text #' mod='locdevis'}{$textField@index+1|escape:'htmlall':'UTF-8'}
										{/if}
										{l s=':' mod='locdevis'} {$textField.value|escape:'htmlall':'UTF-8'}
									</li>
									{/foreach}
								</ul>
								{/if}
								{/foreach}
							</td>
							<td>
								{if isset($cannotModify) AND $cannotModify == 1}
								{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed|escape:'htmlall':'UTF-8'}{/if}
								{else}
								{$customization.quantity|escape:'htmlall':'UTF-8'}
								{/if}
							</td>
							<td>
								&nbsp;
							</td>
						</tr>
						{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
					{/foreach}
					{* If it exists also some uncustomized products *}
					{if $product.quantity-$quantityDisplayed > 0}
						{include file="$pdf_shopping_cart_dir" productLast=$product@last productFirst=$product@first cannotModify=1}
					{/if}
				{/if}
            {/foreach}
            {assign var='last_was_odd' value=$product@iteration%2}
            {foreach $gift_products as $product}
				{assign var='productId' value=$product.id_product}
				{assign var='productAttributeId' value=$product.id_product_attribute}
				{assign var='quantityDisplayed' value=0}
				{assign var='odd' value=($product@iteration+$last_was_odd)%2}
				{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
				{assign var='cannotModify' value=1}
				{* Display the gift product line *}
				{include file="$pdf_shopping_cart_dir" productLast=$product@last productFirst=$product@first cannotModify=1}
            {/foreach}
        {if sizeof($discounts)}
            {foreach $discounts as $discount}
            <tr>
                <td colspan="4" style="text-align:left; width:70%">{$discount.name|escape:'htmlall':'UTF-8'}</td>
                <td style="text-align:left; width:10%"><span>
                        {if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
                    </span></td>
                <td style="text-align:left; width:5%">1</td>
                <td style="text-align:right; width:15%">
                    <span>{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
                </td>
                <td>
                    &nbsp;
                </td>
            </tr>
            {/foreach}
        {/if}
        </tbody>
        <tfoot>
            {if $total_wrapping != 0}
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">
                    {if $use_taxes && $priceDisplay == 0}
                        {l s='Total gift wrapping (tax incl.):' mod='locdevis'}
                    {else}
                        {l s='Total gift-wrapping cost:' mod='locdevis'}
                    {/if}
                </td>
                <td colspan="1"style="text-align:right;">
                    {if $use_taxes}
                        {if $priceDisplay}
                            {displayPrice price=$total_wrapping_tax_exc}
                        {else}
                            {displayPrice price=$total_wrapping}
                        {/if}
                    {else}
                        {displayPrice price=$total_wrapping_tax_exc}
                    {/if}
                </td>
            </tr>
            {/if}
           {if $total_discounts != 0}
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">
                    {if $use_taxes && $priceDisplay == 0}
                        {l s='Total vouchers (tax incl.):' mod='locdevis'}
                    {else}
                        {l s='Total vouchers (tax excl.)' mod='locdevis'}
                    {/if}
                </td>
                <td colspan="1" style="text-align:right;">
                    {if $use_taxes && $priceDisplay == 0}
                    {assign var='total_discounts_negative' value=$total_discounts * -1}
                    {else}
                    {assign var='total_discounts_negative' value=$total_discounts_tax_exc * -1}
                    {/if}
                    {displayPrice price=$total_discounts_negative}
                </td>
            </tr>
            {/if}
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">
                    {l s='Shipping cost' mod='locdevis'} ({if $priceDisplay == 1}{l s='tax excl.' mod='locdevis'}{else}{l s='tax incl.' mod='locdevis'}{/if})
                </td>
                <td colspan="1" style="text-align:right;">
                    <span id="total_price">{if $priceDisplay == 1}{displayPrice price=$total_shipping_tax_exc}{else}{displayPrice price=$total_shipping}{/if}</span>
                </td>
            </tr>
            {if $use_taxes}
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">{l s='Total (tax excl.)' mod='locdevis'}</td>
                <td colspan="1" id="total_price_without_tax" style="text-align:right;">{displayPrice price=$total_price_without_tax}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">{l s='Total tax' mod='locdevis'}</td>
                <td colspan="1" style="text-align:right;">{displayPrice price=$total_tax}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">
                    {l s='Total (tax incl.)' mod='locdevis'}
                </td>
                <td colspan="1" style="text-align:right;">
                    <span id="total_price">{displayPrice price=$total_price}</span>
                </td>
            </tr>
            {else}
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">
                    {l s='Total (tax excl.)' mod='locdevis'}
                </td>
                <td colspan="1" style="text-align:right;">
                    <span id="total_price">{displayPrice price=$total_price_without_tax}</span>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:right; font-weight:bold;">
                    {l s='Total' mod='locdevis'}
                </td>
                <td colspan="1" style="text-align:right;">
                    <span id="total_price">{displayPrice price=$total_price_without_tax}</span>
                </td>
            </tr>
            {/if}
        </tfoot>
    </table>
    <!-- detail tax -->
    {if count($tax_details)>0 && $use_taxes}
        <br />
        <table cellpadding="3">
            <thead>
            <tr style="color:#FFFFFF; background-color: #4D4D4D; font-weight:bold; padding:2px;">
                <th>{l s='TAX DETAILS' mod='locdevis'}</th>
                <th>{l s='Tax rate' mod='locdevis'}</th>
                <th>{l s='Total without tax' mod='locdevis'}</th>
                <th>{l s='Total tax' mod='locdevis'}</th>
            </tr>
            </thead>
            {foreach $tax_details as $tax}
            <tr>
                <td></td>
                <td>{$tax.name|escape:'htmlall':'UTF-8'}</td>
                <td>{displayPrice price=$tax.total_ht}</td>
                <td>{displayPrice price=$tax.total_tax}</td>
            </tr>
            {/foreach}
        </table>
    {/if}
    <!-- message -->
    {if !empty($message_visible)}
    <table cellpadding="3">
        <tr><td>&nbsp;</td></tr>
        <tr style="color:#FFFFFF; background-color: #4D4D4D; font-weight:bold;">
            <th>{l s='ADDITIONNAL INFORMATIONS' mod='locdevis'}</th>
        </tr>
        <tr>
            <td>{html_entity_decode($message_visible|escape:'htmlall':'UTF-8')}</td>{* HTML needed can't escape *}
        </tr>
        <tr><td>&nbsp;</td></tr>
    </table>
    {/if}
    <!-- footer -->
    <table>
        <tr><td>&nbsp;</td></tr>
        <tr style="font-weight:bold;">
            <td>{html_entity_decode($freeText|escape:'htmlall':'UTF-8')}</td>{* HTML needed can't escape *}
        </tr>
    </table>
    <table>
           <tr><td>&nbsp;</td></tr>
            <tr>
                <td>
                    {if $quote_object->statut == 0}
                        {l s='This quotation is not yet validated, you can modify it' mod='locdevis'}
                    {/if}
                    {if $quote_object->statut == 1}
                        {l s='This quotation has been validated' mod='locdevis'}
                    {/if}
                    {if $quote_object->statut == 2}
                        {l s='This quotation has been ordered' mod='locdevis'}
                    {/if}
                    {if $quote_object->statut == 3}
                        {l s='This quotation is expired' mod='locdevis'}
                    {/if}
                </td>
            </tr>
    </table>
    <table>
        <tr><td>&nbsp;</td></tr>
        <tr><td>{if $expire_time != 0 && $quote_object->statut == 1}{l s='This quotation will be valid for %d days' sprintf=$expire_time mod='locdevis'}{/if}</td></tr>
        <tr style="font-weight:bold;">
            <td>{html_entity_decode($validationText|escape:'htmlall':'UTF-8')}</td>{* HTML needed can't escape *}
        </tr>
    </table>
    <table>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight:bold">{html_entity_decode($goodforagrementText|escape:'htmlall':'UTF-8')}</td>{* HTML needed can't escape *}
        </tr>
    </table>
</div>
