{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}

<div class="clearfix">
    <a href="{$link->getModuleLink('locdevis', 'createquotation',['create'=>true])|escape:'htmlall':'UTF-8'}" class="btn btn-primary locDevisCartToQuotationLink">
        {if !$quote}
            {l s='create quotation from my cart' mod='locdevis'}
        {else}
            {l s='update my quotation' mod='locdevis'}
        {/if}
    </a>
</div>
