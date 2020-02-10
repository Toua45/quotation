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
            <p>
                <strong>{l s='You have to be logged before create your quotation' mod='locdevis'}</strong>
                <a href="{$link->getPageLink('authentication', true, null, "&back={$back}")|escape:'htmlall':'UTF-8':'UTF-8'}" class="btn btn-default button button-small">
                    <span>{l s='Go to login page' mod='locdevis'} <i class="icon-chevron-right"></i> </span>
                </a>
            </p>
            {if $LOCDEVIS_SHOWFREEFORM == 1}
            <br />
            <p>
                {l s='You can also request a quoation without be logged using this form' mod='locdevis'}
                <a href="{$link->getModuleLink('locdevis', 'sendmessage',[])|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small">
                    <span>{l s='Use simple request form' mod='locdevis'} <i class="icon-chevron-right"></i> </span>
                </a>
            </p>
            {/if}
        </section>
    </div>								
</div>
        {/block}