{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<div class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module{if !$logged} warning{/if}">
			{if $logged}
			<a href="{$link->getModuleLink('locdevis', 'createquotation',['create'=>true,'from'=>'payment'])|escape:'htmlall':'UTF-8'}" title="{l s='Create a quotation.' mod='locdevis'}" class="cheque">
					{l s='Create a quote' mod='locdevis'}
				</a>
			{else}
				<a href="{$link->getPageLink('my-account',true)|escape:'htmlall':'UTF-8'}">
					<span class=""></span>{l s='You must be registered for be able to create your quote.' mod='locdevis'}
				</a>
			{/if}
		</p>
	</div>
</div>