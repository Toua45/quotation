{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<tr style="background-color: {$bgcolor|escape:'htmlall':'UTF-8'};">
					<td style="text-align:left; width:10%">{html_entity_decode($product.image_tag|escape:'htmlall':'UTF-8')}{* HTML needed can't escape *}</td>
					<td style="text-align:left; width:35%">{$product.name|escape:'htmlall':'UTF-8'}
						{if isset($product.attributes) && $product.attributes}<br />{$product.attributes|escape:'htmlall':'UTF-8'}{/if}
                                                {if $product.ecotax != 0}<br />{l s='ecotax per product' mod='locdevis'}: {convertPrice price=$product.ecotax}{/if}
                                        </td>
					<td style="text-align:left; width:10%">{if $product.reference}{$product.reference|escape:'htmlall':'UTF-8'}{else}--{/if} {$smarty.foreach.foo.iteration|escape:'htmlall':'UTF-8'}</td>
					<td style="text-align:left; width:15%">{if $product.quantity_available>0}{$product.available_now|escape:'htmlall':'UTF-8'}{else}{$product.available_later|escape:'htmlall':'UTF-8'}{/if}</td>
					<td style="text-align:left; width:10%">
							{if !empty($product.gift)}
								<span>{l s='Gift!'  mod='locdevis'}</span>
							{else}
								{if !$priceDisplay}
									{convertPrice price=$product.price_wt}
								{else}
									{convertPrice price=$product.price}
								{/if}
							{/if}
					</td>
					<td style="text-align:left; width:5%">
                                            {$product.cart_quantity|escape:'htmlall':'UTF-8'}
					{*{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                            ddd
						{$customizedDatas.$productId.$productAttributeId|@count|escape:'htmlall':'UTF-8'}
					{else}
                                            rrr
						{$product.cart_quantity-$quantityDisplayed|escape:'htmlall':'UTF-8'}
					{/if}*}
					</td>
					<td style="text-align:right; width:15%">
						<span id="total_product_price_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}{if $quantityDisplayed > 0}_nocustom{/if}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
							{if !empty($product.gift)}
							<span>{l s='Gift!' mod='locdevis'}</span>
							{else}
							{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
							{if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
							{else}
							{if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
							{/if}
							{/if}
						</span>
					</td>
				</tr>
				{* debug product *}
                                {*<tr>
                                    <td colspan="5">
                                        {$product|@print_r}
                                    </td>
                                </tr>*}