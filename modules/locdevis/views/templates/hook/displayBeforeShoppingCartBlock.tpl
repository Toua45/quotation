{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
{if ($quote->statut == 1 || $quote->statut == 2)}
    <p class="alert alert-warning">
        {l s='You are not able to modify this cart because is linked to quotation number %s.' sprintf=[$quote->id_locdevis] mod='locdevis'}<br />
        {l s='You can use it to order or create a new cart' mod='locdevis'}<br />  
        <a href="{$link->getModuleLink('locdevis','list',['newcart'=>true])|escape:'htmlall':'UTF-8'}" class="button od-new-cart-btn">{l s='Create a new cart' mod='locdevis'}</a>
        <a href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html':'UTF-8'}" class="button od-proceed-checkout-btn">{l s='Proceed to checkout' mod='locdevis'}</a> 
    </p>
{/if}
{if ($quote->statut == 0)}
    <p class="alert alert-warning">
        {l s='This cart is linked to the quotation number %s.' sprintf=[$quote->id_locdevis] mod='locdevis'}<br />
        {l s='Modfiying this cart will affect your quotation' mod='locdevis'}<br /> 
        {l s='You can also' mod='locdevis'} <a href="{$link->getModuleLink('locdevis','list',['newcart'=>true])|escape:'htmlall':'UTF-8'}">{l s='create a new cart clicking here' mod='locdevis'}</a>
    </p>
{/if}