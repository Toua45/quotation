{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
{extends file='customer/page.tpl'}

{block name='page_title'}
  {l s='Your quotations' mod='locdevis'}
{/block}

{block name='page_content'}

			
			{if isset($deleted) && $deleted=="success"}
                            <div class="alert alert-success">{l s='Quote deleted' mod='locdevis'}</div>
			{/if}
				{if $quotations && count($quotations)}
				<table id="order-list" class="std">
                                    <thead>
                                        <tr>
                                            <th class="item"></th>
                                            <th class="item">{l s='Date' mod='locdevis'}</th>
                                        {if isset($expiretime) && $expiretime > 0} 
                                            <th class="item">{l s='Expired date' mod='locdevis'}</th>
                                        {/if}
                                            <th class="item">{l s='Name' mod='locdevis'}</th>
                                            <th class="item">&nbsp;</th>
                                            <th class="last_item">&nbsp;</th>
                                            <th class="last_item">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach from=$quotations item=quotation name=myLoop}
                                        <tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
                                            <td class="history_method">{$quotation.id_locdevis|escape:'htmlall':'UTF-8'}</td>
                                            <td class="history_method">{dateFormat date=$quotation.date_add full=1}</td>
                                        {if isset($quotation.expire_date) && $quotation.expire_date > 0} 
                                            <td class="history_method">{dateFormat date=$quotation.expire_date full=1}</td>
                                        {/if}
                                            <td class="history_method">{$quotation.name|escape:'htmlall':'UTF-8'}</td>
                                            <td class="history_method">       
                                                    {if $quotation.statut == 1}
                                                    <a href="{$link->getModuleLink('locdevis','load',['locquotationId'=>$quotation.id_locdevis,'proceedCheckout'=>true])|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small">
                                                        <span class="locDevisHide">{l s='proceed to checkout' mod='locdevis'}<i class="icon-chevron-right right"></i></span><i class="icon-chevron-right right locDevisShow"></i>
                                                    </a>
                                                    {else if $quotation.statut == 2}
                                                    <a href="{$link->getPageLink('order-detail', true, NULL, "id_order={$quotation.id_order|intval}")|escape:'html':'UTF-8'}" class="btn btn-default button button-small">
                                                        <span class="locDevisHide">{l s='Display order' mod='locdevis'}<i class="icon-chevron-right right"></i></span><i class="icon-chevron-right right locDevisShow"></i>
                                                    </a>
                                                    {else if $quotation.statut == 3}
                                                        {l s='Expired' mod='locdevis'}
                                                    {else}
                                                    <a href="{$link->getModuleLink('locdevis','load',['locquotationId'=>$quotation.id_locdevis])|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small">
                                                        <span class="locDevisHide">{l s='Modify' mod='locdevis'}<i class="icon-chevron-right right"></i></span><i class="icon-chevron-right right locDevisShow"></i>
                                                    </a>
                                                    {/if}
                                            </td>
                                            <td class="history_method">
                                                {if $quotation.statut == 0 || $quotation.statut == 3}
                                                <a href="{$link->getModuleLink('locdevis','list',['action'=>'delete','locquotationId'=>$quotation.id_locdevis])|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small"><span class="locDevisHide">{l s='Delete' mod='locdevis'}<i class="icon-trash right"></i></span><i class="icon-trash right locDevisShow"></i> </a>
                                                {/if}
                                            </td>
                                            <td class="history_method"><a href="{$link->getModuleLink('locdevis','showpdf',['idCart'=>$quotation.id_cart])|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small"><span class="locDevisHide">{l s='Download' mod='locdevis'}<i class="icon-download-alt right"></i></span><i class="icon-download-alt right locDevisShow"> </a></td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
				</table>
				<div id="block-order-detail" class="hidden">&nbsp;</div>
				{else}
					<p class="warning">{l s='You have no quote' mod='locdevis'}</p>
				{/if}

{/block}