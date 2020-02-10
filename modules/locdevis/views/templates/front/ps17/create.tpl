{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
{extends file='page.tpl'}

{block name="page_content"}
<div class="content">
    <div class="row">
        <section id="center_column" class="span9">
            {capture name=path}{l s='Create your quotation' mod='locdevis'}{/capture}
            <h1>{l s='Create your quotation' mod='locdevis'}</h1>
            {if isset($errors)}
                {include file='_partials/form-errors.tpl' errors=$errors}
            {/if}
            {if isset($cartEmpty) && $cartEmpty==true}
            <p>{l s='Your cart is empty, please add product into your cart before creating your quotation.' mod='locdevis'}</p>
            {/if}
            {if $showForm}
            <form action="{$link->getModuleLink('locdevis', 'createquotation')}" method="post" class="contact-form-box" enctype="multipart/form-data" id="locDevisForm">
                <input type="hidden" name="idCart" value="{$id_cart|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="quotationId" value="{$quotationId|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="loc_devis_customer_id" id="loc_devis_customer_id" value="{$customerId|escape:'htmlall':'UTF-8'}"/>
                <!--<fieldset>-->
                <div class="container">
                    <h4>{l s='Products in your quotation' mod='locdevis'}</h4>
                    <table class="table table-bordered stock-management-on" id="cart_summary">
                        <thead>
                        <tr>
                            <th>{l s='Product' mod='locdevis'}</th>
                            <th><span class="locMaxWidthDevice">{l s='Availability' mod='locdevis'}</span></th>
                            <th>{l s='Qty' mod='locdevis'}</th>
                            <th>{l s='Unit price' mod='locdevis'} {if $priceDisplay == 1}{l s='tax excl.' mod='locdevis'}{else}{l s='tax incl.' mod='locdevis'}{/if}</th>
                            <th>{l s='Total' mod='locdevis'} {if $priceDisplay == 1}{l s='tax excl.' mod='locdevis'}{else}{l s='tax incl.' mod='locdevis'}{/if}</th>
                        </tr>
                        </thead>
                        {foreach $summary.products as $product}
                        {assign var='productIdAddressDelivery' value=$product.id_address_delivery}
                        {* choose price to display *}
                        {* unit product price *}
                        {if $priceDisplay == 1}{assign var='unitProductPrice' value=$product.price}{else}{assign var='unitProductPrice' value=$product.price_wt}{/if}
                        {* total product price *}
                        {if $priceDisplay == 1}
                            {if isset($product.total_customization)}
                                {assign var='totalProductPrice' value=$product.total_customization}
                            {else}
                                {assign var='totalProductPrice' value=$product.total}
                            {/if}
                        {else}
                            {if isset($product.total_customization_wt)}
                                {assign var='totalProductPrice' value=$product.total_customization_wt}
                            {else}
                                {assign var='totalProductPrice' value=$product.total_wt}
                            {/if}
                        {/if}
                        {assign var='productId' value=$product.id_product}
                        {assign var='productAttributeId' value=$product.id_product_attribute}
                        <tr>
                            <td>
                                {$product.name|escape:'htmlall':'UTF-8'}{if isset($product.attributes_small)} - {$product.attributes_small|escape:'htmlall':'UTF-8'}{/if}
                            </td>
                            <td>{if $product.quantity_available>0}{$product.available_now}{else}{$product.available_later}{/if}</td>
                            <td>{$product.cart_quantity|escape:'htmlall':'UTF-8'}</td>
                            {*<td>{if $priceDisplay == 1}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_wt}{/if}</td>
                            <td>{if $priceDisplay == 1}{convertPrice price=$product.total}{else}{convertPrice price=$product.total_wt}{/if}</td>*}
                            <td>{Tools::displayPrice($unitProductPrice)}</td>
                            <td>{Tools::displayPrice($totalProductPrice)}</td>
                        </tr>
                        {* customized data*}
                                {if isset($customizedDatas.$productId.$productAttributeId.$productIdAddressDelivery)}
                                    {foreach $customizedDatas.$productId.$productAttributeId.$productIdAddressDelivery as $id_customization => $customization}
                                        {if $id_customization == $product.id_customization}
                                            <tr>
                                            <td colspan="5">
                                            {foreach $customization.datas as $type => $custom_data}
                                                {if $type == $CUSTOMIZE_FILE}
                                                    {foreach $custom_data as $picture}
                                                        <br />&nbsp; &nbsp;<img src="{$ps_base_url}/upload/{$picture.value}_small" alt="" />
                                                    {/foreach}
                                                {elseif $type == $CUSTOMIZE_TEXTFIELD}
                                                    {foreach $custom_data as $textField}
                                                        <br />&nbsp; &nbsp;
                                                        {if $textField.name}
                                                            {$textField.name|escape:'htmlall':'UTF-8'}
                                                        {else}
                                                            {l s='Text #' mod='locdevis'}{$textField@index+1}
                                                        {/if}
                                                        {l s=':' mod='locdevis'} {$textField.value nofilter}
                                                    {/foreach}
                                                {/if}
                                            {/foreach}
                                            </td>
                                        </tr>
                                       {/if}
                                    {/foreach}
                                {/if}

                        {* end customized data*}
                        {/foreach}
						<!-- discount -->
						{if sizeof($summary.discounts)}
							{foreach $summary.discounts as $discount}
								<tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}"><td colspan="2">{$discount.name}</td>
									<td>1</td>
									<td>
										<span class="price-discount">
										{if !$priceDisplay}{Tools::displayPrice($discount.value_real*-1)}{else}{Tools::displayPrice($discount.value_tax_exc*-1)}{/if}
										</span>
									</td>
									<td class="cart_discount_price">
										<span class="price-discount price">{if !$priceDisplay}{Tools::displayPrice($discount.value_real*-1)}{else}{Tools::displayPrice($discount.value_tax_exc*-1)}{/if}</span>
									</td>
								</tr>
							{/foreach}
						{/if}
                        <tfoot>
                        <tr class="cart_total_price">
                         <td colspan="4" class="text-right">{l s='Total' mod='locdevis'} {l s='tax excl.' mod='locdevis'}</td>
                         <td class="price" id="locQuotationTotalQuotation">{Tools::displayPrice($summary.total_price_without_tax)}</td>
                        </tr>
                        <tr class="cart_total_price">
                         <td colspan="4" class="text-right">{l s='Total shipping' mod='locdevis'} {if $priceDisplay==1}{l s='tax excl.' mod='locdevis'}{/if}</td>
                         <td class="price" id="locQuotationTotalShipping">{if $priceDisplay==1}{Tools::displayPrice($summary.total_shipping_tax_exc)}{else}{Tools::displayPrice($summary.total_shipping)}{/if}</td>
                        </tr>
                        <tr class="cart_total_price">
                         <td colspan="4" class="text-right">{l s='Total tax' mod='locdevis'}</td>
                         <td class="price" id="locQuotationTotalTax">{Tools::displayPrice($summary.total_tax)}</td>
                        </tr>
                        <tr class="cart_total_price">
                         <td colspan="4" class="total_price_container text-right">{l s='Total' mod='locdevis'} {l s='tax incl.' mod='locdevis'}</td>
                         <td class="price" id="total_price_container"><span id="locQuotationTotalQuotationWithTax">{Tools::displayPrice($summary.total_price)}</span></td>
                        </tr>
                        </tfoot>
                    </table>
                    <p class="locDevisInfos">{l s='To edit your product list, open your cart and make your change.' mod='locdevis'}<br />{l s='Then click again on the "create quotation" button.' mod='locdevis'}</p>
                    <!-- addresses -->
                    {if count($addresses)>0}
                    <div class="clearfix">
                        <h4>{l s='Choose your addresses' mod='locdevis'}</h4>
                        <div class="form-group locDevisConteneurTextarea">
                            <label for="delivery_address"> {l s='Delivery addresse' mod='locdevis'}</label>
                            <select name="delivery_address" {if isset($summary)}onChange="locDevisLoadCarrierList();"{/if} class="delivery_address">
                                {foreach $addresses as $address}
                                <option value="{$address.id_address}" {if isset($summary) && $summary.delivery->id == $address.id_address}selected="selected"{/if}>{$address.firstname} {$address.lastname} - {$address.address1}{if $address.address2!=""} {$address.address2}{/if} - {$address.postcode} {$address.city}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group locDevisConteneurTextarea">
                            <label for="invoice_address"> {l s='Invoice addresse' mod='locdevis'}</label>
                            <select name="invoice_address" class="invoice_address">
                                {foreach $addresses as $address}
                                <option value="{$address.id_address|escape:'htmlall':'UTF-8'}" {if isset($summary) && $summary.invoice->id == $address.id_address}selected="selected"{/if}>{$address.firstname|escape:'htmlall':'UTF-8'} {$address.lastname|escape:'htmlall':'UTF-8'} - {$address.address1|escape:'htmlall':'UTF-8'}{if $address.address2!=""} {$address.address2|escape:'htmlall':'UTF-8'}{/if} - {$address.postcode|escape:'htmlall':'UTF-8'} {$address.city|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    {else}
                    <p class="locDevisInfos">{l s='We didn\'t found any addresses, please go to your personnal account and add addresses' mod='locdevis'}</p>
                    {/if}
                    {if isset($summary)}
						{if $from!='payment'}
							<!-- carriers -->
							<div class="clearfix">
								<div class="form-group">
								<h4>{l s='Choose your carrier' mod='locdevis'}</h4>
								<select id="loc_devis_carrier_input" name="loc_devis_carrier_input"></select>
								</div>
								<input type="hidden" name="selected_carrier" value="{if isset($id_carrier)}{$id_carrier|escape:'htmlall':'UTF-8'}{/if}" id="selected_carrier" />
							</div>
                                                {else}
                                                    <input type="hidden" name="loc_devis_carrier_input" value="{if isset($id_carrier)}{$id_carrier|escape:'htmlall':'UTF-8'}{/if}" />
						{/if}
                    {/if}
                    <!-- messages -->

                    <div class="clearfix">
                        <h4>{l s='Additionnal informations' mod='locdevis'}</h4>
                        <div class="form-group locDevisConteneurTextarea">
                            <label for="message_visible">{l s='Add information (visible on quotation)' mod='locdevis'}</label>
                            <textarea class="form-control locDevisTextArea" id="messageVisible" name="message_visible">{if isset($message_visible)}{$message_visible|stripslashes}{/if}</textarea>
                        </div>
                        <div class="form-group locDevisConteneurTextarea">
                            <label for="message_not_visible">{l s='Leave us a message (not visible on quotation)' mod='locdevis'}</label>
                            <textarea class="form-control locDevisTextArea" id="messageNotVisible" name="message_not_visible">{if isset($message_not_visible)}{$message_not_visible|stripslashes}{/if}</textarea>
                        </div>
                    </div>
                    <div class="clearfix">
                        <div class="form-group locDevisConteneurQuotationName">
                            <label for="quotation_name">{l s='Add a name to your quotation' mod='locdevis'}</label>
                            <input type="text" name="quotation_name" id="quotation_name" value="{$quotationName|escape:'htmlall':'UTF-8'}"/>
                        </div>
                    </div>
                    <p class="cart_navigation clearfix">
                        <a href="{$link->getPageLink('my-account', true)}" class="btn btn-default button button-small">
                            <span><i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='locdevis'}</span>
                        </a>
                        <a href="{$base_dir}" class="btn btn-default button button-small">
                            <span><i class="icon-chevron-left"></i> {l s='Home' mod='locdevis'}</span>
                        </a>
                        <button type="submit" name="submitQuotation" id="submitMessage" class="button btn btn-default button-medium"><span><i class="icon-save"></i> {l s='Save and send your quotation' mod='locdevis'}</span></button>
                    </p>
                </div>
                <!--</fieldset>     -->
            </form>
            {/if}
        </section>
    </div>
</div>
<script type="text/javascript">
    var loc_module_dir = "{$content_dir}{$loc_module_dir}";
    locDevisControllerUrl = "{$content_dir}/index.php?fc=module&module=locdevis&controller=createquotation";
    priceDisplay = {$priceDisplay};
    currency_format = '{$currency->format}';
    currency_sign = '{$currency->sign}';
    currency_blank = {$currency->blank};
</script>
{/block}
