{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
{if isset($erreurs) && count($erreurs)>0}
<div class="alert alert-warning">
    {foreach from=$erreurs item=erreur}{$erreur|escape:'javascript':'UTF-8'}<br />{/foreach}
</div>
{/if}
<form action="{$adminModuleUrl|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" class="defaultForm form-horizontal">
    <div class="panel" id="fieldset_0">
        <div class="panel-heading"><i class="icon-cogs"></i> {l s='Configure loc devis' mod='locdevis'}</div>
        <div class="form-wrapper">
            <input type="hidden" value="small_default" name="locdevis_imagesize" />
            <div class="form-group">
                <label class="control-label col-lg-3" for="sendMailtoCustomer" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Send an email to customer with quotation pdf' mod='locdevis'}">{l s='Send mail to customer' mod='locdevis'} :</span>
                </label>
	            <div class="col-lg-9">
                  <label class="switch-light prestashop-switch fixed-width-lg">
                    <input name="sendMailtoCustomer" id="sendMailtoCustomer" type="checkbox" value="1" {if isset($fieldsValue.sendMailtoCustomer) && $fieldsValue.sendMailtoCustomer==1}checked="checked"{/if}/>
                    <span>
                      <span>{l s='Yes' d='Admin.Global'}</span>
                      <span>{l s='No' d='Admin.Global'}</span>
                    </span>
                    <a class="slide-button btn"></a>
                  </label>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="sendMailtoCustomer" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Send an email to admin with quotation pdf' mod='locdevis'}">{l s='Send mail to admin' mod='locdevis'} :</span>
                </label>
	            <div class="col-lg-9">
                  <label class="switch-light prestashop-switch fixed-width-lg">
                    <input name="sendMailtoAdmin" id="sendMailtoAdmin" type="checkbox" value="1" {if isset($fieldsValue.sendMailtoAdmin) && $fieldsValue.sendMailtoAdmin==1}checked="checked"{/if}/>
                    <span>
                      <span>{l s='Yes' d='Admin.Global'}</span>
                      <span>{l s='No' d='Admin.Global'}</span>
                    </span>
                    <a class="slide-button btn"></a>
                  </label>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="adminMail">
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='This contact will be used to receive quotations and customers messages' mod='locdevis'}">{l s='Choose admin contact' mod='locdevis'} :</span>
                </label>
                <div class="col-lg-9">
                    <select name="adminContactId">
                        {foreach $contacts as $contact}
                        <option value="{$contact.id_contact|escape:'htmlall':'UTF-8'}" {if isset($fieldsValue.adminContactId) && $fieldsValue.adminContactId==$contact.id_contact}selected="selected"{/if}>{$contact.name|escape:'htmlall':'UTF-8'} ({$contact.email|escape:'htmlall':'UTF-8'})</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="locShops">
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Select the shop you want to propose renting for Payment Option' mod='locdevis'}">{l s='Renting Shops' mod='locdevis'} :</span>
                </label>
                {if count($shops) && isset($shops)}
                <div class="row">
					<div class="col-lg-6">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th class="fixed-width-xs">
										<span class="title_box">
											<input type="checkbox" name="checkme" id="checkme" onclick="checkAllBoxes(this.form, 'shopBox', this.checked)" />
										</span>
									</th>
									<th class="fixed-width-xs"><span class="title_box">{l s='ID' mod='locdevis'}</span></th>
									<th>
										<span class="title_box">
											{l s='Shop name' mod='locdevis'}
										</span>
									</th>
								</tr>
							</thead>
							<tbody>
							{foreach $shops as $key => $shop}
								<tr>
									<td>
										{assign var=id_checkbox value=allowed_shop|cat:'_'|cat:$shop['id_shop']}
										<input type="checkbox" class="shopBox" id="{$id_checkbox}" name="{$id_checkbox}" value="1" {if isset($fieldsValue[$id_checkbox]) && $fieldsValue[$id_checkbox] ==1}checked="checked"{/if} />
									</td>
									<td>{$shop['id_shop']}</td>
									<td>
										<label for="{$id_checkbox}">{$shop['name']}</label>
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
				{else}
				<p>
					{l s='Only 1 shop exists' mod='locdevis'}
				</p>
				{/if}
			</div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="locCat">
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Select the Categories avalaible for Renting Option' mod='locdevis'}">{l s='Renting Categories' mod='locdevis'} :</span>
                </label>
                {if count($categories) && isset($categories)}
                <div class="col-lg-6">
	                {$tree}
                </div>
				{else}
				<p>
					{l s='No Categories available' mod='locdevis'}
				</p>
				{/if}
			</div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="coeff">{l s='Coefficients' mod='locdevis'} : </label>
                <div class="col-lg-9" id="carrier_wizard">
                	<table class="table">
	                	<tr class="limit_inf">
							<td class="range_type">{l s='Will be applied if total amount is' mod='locdevis'}</td>
							<td class="border_left border_bottom range_sign">&gt;=</td>
							<td class="border_bottom">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">€</span>
									<input class="form-control" name="limit_inf_1" type="text" value="{if isset($fieldsValue.limit_inf_1)}{$fieldsValue.limit_inf_1|escape:'htmlall':'UTF-8'}{else}0{/if}" />
								</div>
							</td>
							<td class="border_bottom">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">€</span>
									<input class="form-control" name="limit_inf_2" type="text" value="{if isset($fieldsValue.limit_inf_2)}{$fieldsValue.limit_inf_2}{else}{$fieldsValue.limit_sup_1}{/if}" />
								</div>
							</td>
							<td class="border_bottom">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">€</span>
									<input class="form-control" name="limit_inf_3" type="text" value="{if isset($fieldsValue.limit_inf_3)}{$fieldsValue.limit_inf_3}{else}{if isset($fieldsValue.limit_sup_2)}{$fieldsValue.limit_sup_2}{/if}{/if}" />
								</div>
							</td>
						</tr>
						<tr class="limit_sup">
							<td class="range_type">{l s='Will be applied if total amount is' mod='locdevis'}</td>
							<td class="border_left range_sign">&lt;</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">€</span>
									<input class="form-control" name="limit_sup_1" type="text" value="{if isset($fieldsValue.limit_sup_1)}{$fieldsValue.limit_sup_1}{else}0{/if}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">€</span>
									<input class="form-control" name="limit_sup_2" type="text" value="{if isset($fieldsValue.limit_sup_2)}{$fieldsValue.limit_sup_2}{else}0{/if}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">€</span>
									<input class="form-control" name="limit_sup_3" type="text" value="{if isset($fieldsValue.limit_sup_3)}{$fieldsValue.limit_sup_3}{else}0{/if}"/>
								</div>
							</td>
						</tr>
						<tr class="coeff">
							<td>
								<label for="12_months">{l s= 'Renting 12 months' mod='locdevis'}</label>
							</td>
							<td class="zone">
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_1_12" type="text" value="{$fieldsValue.coeff_1_12|default: 0}"/>
								</div>
{*								<pre>{var_dump($fieldsValue)}</pre>*}
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_2_12" type="text" value="{$fieldsValue.coeff_2_12|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_3_12" type="text" value="{$fieldsValue.coeff_3_12|default: 0}"/>
								</div>
							</td>
						</tr>
						<tr class="coeff">
							<td>
								<label for="24_months">{l s= 'Renting 24 months' mod='locdevis'}</label>
							</td>
							<td class="zone">
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_1_24" type="text" value="{$fieldsValue.coeff_1_24|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_2_24" type="text" value="{$fieldsValue.coeff_2_24|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_3_24" type="text" value="{$fieldsValue.coeff_3_24|default: 0}"/>
								</div>
							</td>
						</tr>
						<tr class="coeff">
							<td>
								<label for="36_months">{l s= 'Renting 36 months' mod='locdevis'}</label>
							</td>
							<td class="zone">
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_1_36" type="text" value="{$fieldsValue.coeff_1_36|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_2_36" type="text" value="{$fieldsValue.coeff_2_36|default: 0}"/>
								</div>
								<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_3_36" type="text" value="{$fieldsValue.coeff_3_36|default: 0}"/>
								</div>
							</td>
							</td>
						</tr>
						<tr class="coeff">
							<td>
								<label for="48_months">{l s= 'Renting 48 months' mod='locdevis'}</label>
							</td>
							<td class="zone">
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_1_48" type="text" value="{$fieldsValue.coeff_1_48|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_2_48" type="text" value="{$fieldsValue.coeff_2_48|default: 0}"/>
								</div>
								<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_3_48" type="text" value="{$fieldsValue.coeff_3_48|default: 0}"/>
								</div>
							</td>
							</td>
						</tr>
						<tr class="coeff">
							<td>
								<label for="60_months">{l s= 'Renting 60 months' mod='locdevis'}</label>
							</td>
							<td class="zone">
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_1_60" type="text" value="{$fieldsValue.coeff_1_60|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_2_60" type="text" value="{$fieldsValue.coeff_12_60|default: 0}"/>
								</div>
							</td>
							<td class="range_data">
								<div class="input-group fixed-width-md">
									<span class="input-group-addon price_unit">%</span>
									<input class="form-control" name="coeff_3_60" type="text" value="{$fieldsValue.coeff_3_60|default: 0}"/>
								</div>
							</td>
						</tr>
                	</table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="freeText">
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='This text will appear on the pdf file before the validation text' mod='locdevis'}">{l s='Free text' mod='locdevis'} :</span>
                </label>
                <div class="col-lg-9">
                    {if isset($fieldsValue.freeText)}{assign "freeTextValue" $fieldsValue.freeText}{else}{assign "freeTextValue" ""}{/if}
                    {html_entity_decode($freeTextTextArea|escape:'htmlall':'UTF-8')}
                   {* {include
				file="controllers/products/textarea_lang.tpl"
				languages=$languages
				input_name='freeText'
				input_value=$freeTextValue} *}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="validationText">
                	<span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{l s='Enter here the validation condition of your quotation' mod='locdevis'}. {l s='This text will appear at the bottom of the pdf file' mod='locdevis'}<br/>{l s='Ex:' mod='locdevis'} {l s='To validate your order, you just need to send us back the quote signed to the following address:' mod='locdevis'} {l s='company name' mod='locdevis'} - {l s='address' mod='locdevis'} - {l s='postcode' mod='locdevis'} - {l s='city' mod='locdevis'}">{l s='Validation text' mod='locdevis'} :</span>
                </label>
                <div class="col-lg-9">
                    {if isset($fieldsValue.validationText)}{assign "validationTextValue" $fieldsValue.validationText}{else}{assign "validationTextValue" ""}{/if}
                    {html_entity_decode($validationTextTextArea|escape:'htmlall':'UTF-8')}
                    {*{include
				file='/modules/locdevis/views/templates/admin/textarea_lang.tpl'
				languages=$languages
				input_name='validationText'
				input_value=$validationTextValue} *}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="goodforagrementText">
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Enter here the text good for agrement or another text' mod='locdevis'}.">{l s='Good for agrement text' mod='locdevis'} :</span>
                </label>
                <div class="col-lg-9">
                    {if isset($fieldsValue.goodforagrementText)}{assign "goodforagrementTextValue" $fieldsValue.goodforagrementText}{else}{assign "goodforagrementTextValue" ""}{/if}
                    {html_entity_decode($goodforagrementTextArea|escape:'htmlall':'UTF-8')}
                    {*{include
				file="controllers/products/textarea_lang.tpl"
				languages=$languages
				input_name='goodforagrementText'
				input_value=$goodforagrementTextValue}*}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="maxProdFirstPage" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Set here the maximum number of product will be displaying on the first pdf page.' mod='locdevis'}">{l s='Maximum product on first page' mod='locdevis'} :</span>
                </label>
                <div class="col-lg-9">
                    <input type="text" id="maxProdFirstPage" name="maxProdFirstPage" value="{if isset($fieldsValue.maxProdFirstPage)}{$fieldsValue.maxProdFirstPage|escape:'htmlall':'UTF-8'}{else}6{/if}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="maxProdPage" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Set here the maximum number of product will be displaying on pdf pages except first page.' mod='locdevis'}">{l s='Maximum product on others pages' mod='locdevis'} :</span>
                </label>
                <div class="col-lg-9">
                    <input type="text" id="maxProdPage" name="maxProdPage" value="{if isset($fieldsValue.maxProdPage)}{$fieldsValue.maxProdPage|escape:'htmlall':'UTF-8'}{else}10{/if}" />
                </div>
             </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="expireTime" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Set here the maximum number of day during wich quotes are valid. 0 to disable this feature' mod='locdevis'}">{l s='Quotation are valid for' mod='locdevis'} :
                </label>
                <div class="col-lg-9">
                    <input type="text" id="maxProdPage" name="expireTime" value="{$fieldsValue.expireTime|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="showFreeForm" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Set yes if you wan\'t that your customers be able to fill the free form' mod='locdevis'}">{l s='Display free form' mod='locdevis'} :
                </label>
                <div class="col-lg-9">
                  <label class="switch-light prestashop-switch fixed-width-lg">
                    <input name="showFreeForm" id="showFreeForm" type="checkbox" value="1" {if isset($fieldsValue.showFreeForm) && $fieldsValue.showFreeForm==1}checked="checked"{/if}/>
                    <span>
                      <span>{l s='Yes' d='Admin.Global'}</span>
                      <span>{l s='No' d='Admin.Global'}</span>
                    </span>
                    <a class="slide-button btn"></a>
                  </label>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="showAccountBtn" >
                	<span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Set no if you wan\'t hide the my quotation bouton when customer don\'t have any quotation in his account' mod='locdevis'}">{l s='The \'My quotation\' button is always displayed' mod='locdevis'} :
                </label>
                <div class="col-lg-9">
                  <label class="switch-light prestashop-switch fixed-width-lg">
                    <input name="showAccountBtn" id="showFreeForm" type="checkbox" value="1" {if isset($fieldsValue.showAccountBtn) && $fieldsValue.showAccountBtn==1}checked="checked"{/if}/>
                    <span>
                      <span>{l s='Yes' d='Admin.Global'}</span>
                      <span>{l s='No' d='Admin.Global'}</span>
                    </span>
                    <a class="slide-button btn"></a>
                  </label>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitSettings" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='locdevis'}
            </button>
        </div>
    </div>
</form>
<script type="text/javascript">
    var id_lang_default = {$defaultLangId|intval};
    $(document).ready(function() {
        var tabs_manager = new ProductTabsManager();
        tabs_manager.init();
        hideOtherLanguage({$defaultLangId|escape:'htmlall':'UTF-8'});
    })
	function checkAllBoxes(pForm, boxClass, parent)
	{
		for (i = 0; i < pForm.elements.length; i++)
			if (pForm.elements[i].className == boxClass)
				pForm.elements[i].checked = parent;
	}
</script>
