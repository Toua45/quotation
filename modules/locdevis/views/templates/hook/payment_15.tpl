{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<p class="payment_module {if !$logged}warning{/if}">
	{if $logged}
		<a href="{$link->getModuleLink('locdevis', 'createquotation',['create'=>true,'from'=>'payment'])|escape:'htmlall':'UTF-8'}" title="{l s='Create a quote' mod='locdevis'}">
			<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/quotation.jpg" alt="{l s='Create a quotation.' mod='locdevis'}" width="86" height="49" />
			{l s='Create a quote' mod='locdevis'}
		</a>
	{else}
		<a href="{$link->getPageLink('my-account',true)|escape:'htmlall':'UTF-8'}">
			<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/quotation.jpg" alt="{l s='Create a quotation.' mod='locdevis'}" width="86" height="49" />
			<span class=""></span>{l s='You must be registered for be able to create your quote.' mod='locdevis'}
		</a>
	{/if}
</p>