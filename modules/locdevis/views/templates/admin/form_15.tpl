{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
{if isset($erreurs) && count($erreurs)>0}
<div class="alert alert-warning">
{foreach from=$erreurs item=erreur}
{$erreur|escape:'javascript':'UTF-8'}
{/foreach}
</div>
{/if}
	
<form action="{$adminModuleUrl|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" class="defaultForm form-horizontal">
    <fieldset>
        <legend><i class="icon-cogs"></i> {l s='Configure loc devis' mod='locdevis'}</legend>
        <div class="form-wrapper">
            <input type="hidden" value="small_default" name="locdevis_imagesize" />
            <div class="form-group">
                <label class="control-label col-lg-3" for="sendMailtoCustomer" >{l s='Send mail to customer' mod='locdevis'} :</label>
                <div class="margin-form">
                    <input type="checkbox" id="sendMailtoCustomer" name="sendMailtoCustomer" value="1" {if isset($fieldsValue.sendMailtoCustomer) && $fieldsValue.sendMailtoCustomer==1}checked="checked"{/if}>
                           <p class="help-block">{l s='Send an email to customer with quotation pdf' mod='locdevis'}</p>
                </div>
                <label class="control-label col-lg-3" for="sendMailtoAdmin">{l s='Send mail to admin' mod='locdevis'} :</label>
                <div class="margin-form">
                    <input type="checkbox" id="sendMailtoAdmin" name="sendMailtoAdmin"  value="1" {if isset($fieldsValue.sendMailtoAdmin) && $fieldsValue.sendMailtoAdmin==1}checked="checked"{/if}>				
                           <p class="help-block">{l s='Send an email to admin with quotation pdf' mod='locdevis'}</p>		
                </div>
                <label class="control-label col-lg-3" for="adminMail">{l s='Choose admin contact' mod='locdevis'} :</label>
                <div class="margin-form">
                    <select name="adminContactId">
                        {foreach $contacts as $contact}
                        <option value="{$contact.id_contact|escape:'htmlall':'UTF-8'}" {if isset($fieldsValue.adminContactId) && $fieldsValue.adminContactId==$contact.id_contact}selected="selected"{/if}>{$contact.name|escape:'htmlall':'UTF-8'} ({$contact.email|escape:'htmlall':'UTF-8'})</option>
                        {/foreach}
                    </select>
                    <p class="help-block">{l s='This contact will be used to receive quotations and customers messages' mod='locdevis'}</p>
                </div>
            </div>
            {assign "iso_tiny_mce" "en"}
            {assign "ad" "/admin9654"}
            <div style="clear:both"></div>
            <label class="control-label col-lg-3" for="freeText">{l s='Free text' mod='locdevis'} :</label>
            <div class="margin-form">
                {if isset($fieldsValue.freeText)}{assign "freeTextValue" $fieldsValue.freeText}{else}{assign "freeTextValue" ""}{/if}
                {include file="controllers/products/textarea_lang.tpl"
                languages=$languages
                input_name='freeText'
                input_value=$freeTextValue}
                <p class="help-block" style="clear:both">{l s='This text will appear on the pdf file before the validation text' mod='locdevis'}</p>	
            </div>
            <div style="clear:both"></div>
            <label class="control-label col-lg-3" for="validationText">{l s='Validation text' mod='locdevis'} :</label>
            <div class="margin-form">
                {if isset($fieldsValue.validationText)}{assign "validationTextValue" $fieldsValue.validationText}{else}{assign "validationTextValue" ""}{/if}
                {include file="controllers/products/textarea_lang.tpl"
                languages=$languages
                input_name='validationText'
                input_value=$validationTextValue}
                <p class="help-block" style="clear:both">
                    {l s='Enter here the validation condition of your quotation' mod='locdevis'}.
                    {l s='This text will appear at the bottom of the pdf file' mod='locdevis'}<br />
                    {l s='Ex:' mod='locdevis'} {l s='To validate your order, you just need to send us back the quote signed to the following address:' mod='locdevis'} {l s='company name' mod='locdevis'} - {l s='address' mod='locdevis'} - {l s='postcode' mod='locdevis'} - {l s='city' mod='locdevis'}
                </p>		
            </div>
<br />
            <div style="clear:both"></div>
            <label class="control-label col-lg-3" for="goodforagrementText">{l s='Good for agrement text' mod='locdevis'} :</label>
            <div class="margin-form">
                {if isset($fieldsValue.goodforagrementText)}{assign "goodforagrementTextValue" $fieldsValue.goodforagrementText}{else}{assign "goodforagrementTextValue" ""}{/if}
                {include
                file="controllers/products/textarea_lang.tpl"
                languages=$languages
                input_name='goodforagrementText'
                input_value=$goodforagrementTextValue}
                <p class="help-block" style="clear:both">
                    {l s='Enter here the text good for agrement or another text' mod='locdevis'}.
                </p>		
            </div>

            <div style="clear:both"></div>
            <label class="control-label col-lg-3" for="maxProdFirstPage" >{l s='Maximum product on first page' mod='locdevis'} :</label>
            <div class="margin-form">
                <input type="text" id="maxProdFirstPage" name="maxProdFirstPage" value="{if isset($fieldsValue.maxProdFirstPage)}{$fieldsValue.maxProdFirstPage|escape:'htmlall':'UTF-8'}{else}6{/if}" />
                <p class="help-block">{l s='Set here the maximum number of product will be displaying on the first pdf page.' mod='locdevis'}</p>
            </div>

            <label class="control-label col-lg-3" for="maxProdPage" >{l s='Maximum product on others pages' mod='locdevis'} :</label>
            <div class="margin-form">
                <input type="text" id="maxProdPage" name="maxProdPage" value="{if isset($fieldsValue.maxProdPage)}{$fieldsValue.maxProdPage|escape:'htmlall':'UTF-8'}{else}10{/if}" />
                <p class="help-block">{l s='Set here the maximum number of product will be displaying on pdf pages except first page.' mod='locdevis'}</p>
            </div>
                
            <label class="control-label col-lg-3" for="expireTime" >{l s='Quotation are valid for' mod='locdevis'} :</label>
            <div class="margin-form">
                <input type="text" id="expireTime" name="expireTime" value="{$fieldsValue.expireTime|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='Set here the maximum number of day during wich quotes are valid. 0 to disable this feature' mod='locdevis'}</p>
            </div>
            
            <label class="control-label col-lg-3" for="showFreeForm" >{l s='Display free form' mod='locdevis'} :</label>
            <div class="margin-form">
                <select name="showFreeForm" id="showFreeForm">
                    <option value="1" {if isset($fieldsValue.showFreeForm) && $fieldsValue.showFreeForm==1}selected="selected"{/if}>{l s='yes' mod='locdevis'}</option>
                    <option value="0" {if isset($fieldsValue.showFreeForm) && $fieldsValue.showFreeForm==0}selected="selected"{/if}>{l s='no' mod='locdevis'}</option>
                </select>
                <p class="help-block">{l s='Set yes if you wan\'t that your customers be able to fill the free form' mod='locdevis'}</p>
            </div>
            
            <label class="control-label col-lg-3" for="showAccountBtn" >{l s='The \'My quotation\' button is always displayed' mod='locdevis'} :</label>
            <div class="margin-form">
                <select name="showAccountBtn" id="showFreeForm">
                    <option value="1" {if isset($fieldsValue.showAccountBtn) && $fieldsValue.showAccountBtn==1}selected="selected"{/if}>{l s='yes' mod='locdevis'}</option>
                    <option value="0" {if isset($fieldsValue.showAccountBtn) && $fieldsValue.showAccountBtn==0}selected="selected"{/if}>{l s='no' mod='locdevis'}</option>
                </select>
                <p class="help-block">{l s='Set no if you wan\'t hide the my quotation bouton when customer don\'t have any quotation in his account' mod='locdevis'}</p>
            </div>
        </div>

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitSettings" class="button">
                <i class="process-icon-save"></i> {l s='Save' mod='locdevis'}
            </button>
        </div>
    </fieldset>
</form>
<script type="text/javascript">
    var languages = new Array();
    {foreach from = $languages item = language key = k}
    languages[{$k|escape:'htmlall':'UTF-8'}] = {
    id_lang: {$language.id_lang|escape:'htmlall':'UTF-8'},
            iso_code: '{$language.iso_code|escape:'quotes':'UTF-8'}',
            name: '{$language.name|escape:'quotes':'UTF-8'}'
    };
    {/foreach}
            displayFlags(languages, {$defaultLangId|escape:'htmlall':'UTF-8'});
            tinySetup({
            editor_selector :"autoload_rte",
                    setup : function(ed) {
                    ed.onInit.add(function(ed)
                    {
                    /*if (typeof ProductMultishop.load_tinymce[ed.id] != 'undefined')
                     {
                     if (typeof ProductMultishop.load_tinymce[ed.id])
                     ed.hide();
                     else
                     ed.show();
                     }*/
                    });
                            ed.onKeyUp.add(function(ed, e) {
                            tinyMCE.triggerSave();
                                    textarea = $('#' + ed.id);
                                    max = textarea.parent('div').find('span.counter').attr('max');
                                    if (max != 'none')
                            {
                            textarea_value = textarea.val();
                                    count = stripHTML(textarea_value).length;
                                    rest = max - count;
                                    if (rest < 0)
                                    textarea.parent('div').find('span.counter').html('<span style="color:red;">{l s='Maximum' mod='locdevis'} ' + max + ' {l s='characters' mod='locdevis'} : ' + rest + '</span>');
                                    else
                                    textarea.parent('div').find('span.counter').html(' ');
                            }
                            });
                    }
            });

</script>