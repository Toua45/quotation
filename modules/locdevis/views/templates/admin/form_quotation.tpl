{**
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<script type="text/javascript">
    var id_lang_default = {$id_lang_default|escape:'htmlall':'UTF-8'};
    var loc_module_dir = "{$ps_base_url|escape:'htmlall':'UTF-8'}{$loc_module_dir|escape:'htmlall':'UTF-8'}";
    var token = '{$loc_token|escape:'htmlall':'UTF-8'}';
</script>
<div id="locDevisMsgAlwaysTop"></div>
<form action="{$href|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" id="locDevisForm">	
    <input type="hidden" name="submitAddLocDevis" value="1">
    {if isset($obj->id_locdevis) && $obj->id_locdevis!=""}
        <input type="hidden" value="{$obj->id_locdevis|escape:'htmlall':'UTF-8'}" name="id_locdevis" />
    {/if}
    
    <input type="hidden" value="{if isset($obj->id_locdevis) && $obj->id_locdevis!=""}{$obj->id_cart|escape:'htmlall':'UTF-8'}{/if}" name="idCart" id="loc_devis_id_cart" />
 
    <!-- name -->
    <div class="panel">
        <h3><i class="icon-user"></i> {l s='Quotation name' mod='locdevis'}</h3>
        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-lg-1"><span class="pull-right"></span></div>	
                <label class="control-label col-lg-2">
                    {l s='Add a name to this quotation:' mod='locdevis'}
                </label>
                <div class="col-lg-7">
                    <input type="text" value="{if isset($obj)}{$obj->name|escape:'htmlall':'UTF-8'}{/if}" name="quotation_name" />
                </div>
            </div>
        </div>
    </div>
    <!-- user -->
    <div class="panel">
        <h3><i class="icon-user"></i> {l s='Customer' mod='locdevis'}</h3>
        <div class="form-horizontal">
            <div class="form-group redirect_product_options redirect_product_options_product_choise">	
                <div class="col-lg-1"><span class="pull-right"></span></div>	
                <label class="control-label col-lg-2" for="loc_devis_customer_autocomplete_input">
                    {l s='choose customer:' mod='locdevis'}
                </label>
                <div class="col-lg-7">
                    <input type="hidden" value="" name="id_product_redirected" />
                    <div class="input-group">
                        <input type="text" id="loc_devis_customer_autocomplete_input" name="loc_devis_customer_autocomplete_input" autocomplete="off" class="ac_input" />
                        <span class="input-group-addon"><i class="icon-search"></i></span>
                    </div>
                    <p class="help-block">{l s='Start by typing the first letters of the customer\'s firstname or lastname, then select the customer from the drop-down list.' mod='locdevis'}</p>				
                    <h2 style="clear:both;">
                        <i class="icon-male"></i> 
                        <span href="" id="loc_devis_customer_info"><span style="color:red">{l s='Please choose a customer' mod='locdevis'}</span></span>
                    </h2>			
                </div>
                <input type="hidden" name="loc_devis_customer_id" id="loc_devis_customer_id" value=""/>
            </div>
        </div>
    </div>
    <!--  address -->
    <div class="panel">
        <h3><i class="icon-envelope-alt"></i> {l s='Address' mod='locdevis'}</h3>
        <div class="form-horizontal">
            <div class="col-lg-1"><span class="pull-right"></span></div>
            <label class="control-label col-lg-2" for="loc_devis_customer_autocomplete_input">
                {l s='Invoice address:' mod='locdevis'}
            </label>
            <div class="col-lg-7">
                <select id="loc_devis_invoice_address_input" name="invoice_address"></select>	
            </div>
            <div style="clear:both; height:20px;"></div>
            <div class="col-lg-1"><span class="pull-right"></span></div>
            <label class="control-label col-lg-2" for="loc_devis_customer_autocomplete_input">
                {l s='delivery address:' mod='locdevis'}
            </label>					
            <div class="col-lg-7">
                <select id="loc_devis_delivery_address_input" name="delivery_address"></select>				
                <p class="help-block">{l s='First, you have to choose a customer and you will be able to choose one of his addresses.' mod='locdevis'}</p>
            </div>			
            <div style="clear:both;"></div>
            <input type="hidden" name="selected_invoice" id="selected_invoice" value="{if isset($cart->id_address_invoice)}{$cart->id_address_invoice|escape:'htmlall':'UTF-8'}{/if}" />
            <input type="hidden" name="selected_delivery" id="selected_delivery" value="{if isset($cart->id_address_delivery)}{$cart->id_address_delivery|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>
    <!-- products -->
    <div class="panel">
        <h3><i class="icon-shopping-cart"></i> {l s='Products' mod='locdevis'}</h3>
        
        <div class="form-horizontal">
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='add product:' mod='locdevis'}
            </label>
            <div class="col-lg-7">
                <input type="hidden" value="" name="id_product_redirected" />
                <div class="input-group">
                    <input type="text" id="loc_devis_product_autocomplete_input" name="loc_devis_product_autocomplete_input" autocomplete="off" class="ac_input" />
                    <span class="input-group-addon"><i class="icon-search"></i></span>					
                </div>
                <p class="help-block">{l s='Start by typing the first letters of the products\'s name, then select the product from the drop-down list.' mod='locdevis'}</p>					
            </div>
            <div style="clear:both; height:20px;"></div>	
            <div class="col-lg-1"><span class="pull-right"></span></div>			
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='products in quotation:' mod='locdevis'}
            </label>
            <div class="col-lg-7">
                <!--<div id="waitProductLoad">{l s='loading' mod='locdevis'}</div>-->
                <table class="table" id="locDevisProdList">
                    <tr>
                        <th style="width:5%">{l s='id' mod='locdevis'}</th>
                        <th>{l s='name' mod='locdevis'}</th>
                        <th>{l s='Attributes' mod='locdevis'}</th>
                        <th style="width:10%">{l s='Catalog price without tax' mod='locdevis'}</th>
                        <th style="width:10%">{l s='Reduced price without tax' mod='locdevis'}</th>
                        <!--<th style="width:10%">{l s='real price' mod='locdevis'}</th>-->
                        <th style="width:10%">{l s='Your price' mod='locdevis'}</th>
                        <th style="width:10%">{l s='Quantity' mod='locdevis'}</th>
                        <th style="width:5%">&nbsp;</th>
                    </tr>	
                </table>	
            </div>
            <div style="clear:both;"></div>			
        </div>
    </div>
    <!-- renting -->
	<div class="panel">
		<h3><i class="icon-calendar"></i> {l s='Renting' mod='locdevis'}</h3>
		 <div class="form-horizontal">
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='Renting Option:' mod='locdevis'}
            </label>
            <div class="col-lg-7">
				<span class="switch prestashop-switch fixed-width-lg" id="renting_option">
					<input type="radio" name="is_renting" id="is_renting_on" value="1">
					<label for="is_renting_on">{l s='Yes' d='Admin.Global'}</label>
					<input type="radio" name="is_renting" id="is_renting_off" value="0" checked="checked">
					<label for="is_renting_off">{l s='No' d='Admin.Global'}</label>
					<a class="slide-button btn"></a>
				</span>
            </div>
            <div class="col-lg-3"></div>
            <div class="col-lg-7">
	            <div id="renting_duration">
		            <div style="clear:both; height:20px;"></div>
		            <select id="loc_devis_renting_duration" class="col-lg-4">
			            <option value="12">{l s='12 months' mod='locdevis'}</option>
			            <option value="24">{l s='24 months' mod='locdevis'}</option>
			            <option value="36">{l s='36 months' mod='locdevis'}</option>
			            <option value="48">{l s='48 months' mod='locdevis'}</option>
			            <option value="60">{l s='60 months' mod='locdevis'}</option>
		            </select>
					<div class="date_range col-lg-3">
						<div class="input-group fixed-width-md center">
							<input type="text" class="datepicker" id="start_renting" name="start_renting"  placeholder="{l s='From'}" />
							
							<span class="input-group-addon">
								<i class="icon-calendar"></i>
							</span>
						</div>
						<script type="text/javascript">
							$.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );
							$('.datepicker').datepicker({
								dateFormat:'dd-mm-yy',
								minDate: 0
		    				});
							$('.datepicker').change(function(){
								$('#datepicker').val($(this).val());
		    				});
	  					</script>
					</div>
	            </div>
            </div>			
            <div style="clear:both; height:20px;"></div>
		 </div>
	</div>
	<!-- discounts -->
	<div class="panel">
        <h3><i class="icon-tags"></i> {l s='Reductions' mod='locdevis'}</h3>
		<div class="form-horizontal">
			<div class="col-lg-1"><span class="pull-right"></span></div>
			<label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='add reduction:' mod='locdevis'}
            </label>
            <div class="col-lg-7">
                <div class="input-group">
                    <select id="loc_devis_select_cart_rules">
					{if count($cart_rules)>0}
						<option value="-1">--- {l s='cart rules' mod='locdevis'} ---</option>
						{foreach $cart_rules as $rule}
		                <option value="{$rule.id_cart_rule|escape:'htmlall':'UTF-8'}">catégorie : {$category.id_category|escape:'htmlall':'UTF-8'} rule : {$rules.id_item|escape:'htmlall':'UTF-8'} {$rule.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					{/if}
					{if count($category_rules)>0}
						<option value="-1">--- {l s='category rules' mod='locdevis'} ---</option>
						{foreach $category_rules as $category}
		                	<option value="{$category.id_category|escape:'htmlall':'UTF-8'}-{$category.shopname|escape:'htmlall':'UTF-8'}">{$category.name|escape:'htmlall':'UTF-8'} {$category.id_category|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					{else}						
		                <option value="-1">--- {l s='no cart rules avaibles' mod='locdevis'} ---</option>
					{/if}
                    </select>
				</div>
                <div id="locDevisCartRulesMsgError" style="display:none;"></div>
            </div>
			<div style="clear:both; height:20px;"></div>	
            <div class="col-lg-1"><span class="pull-right"></span></div>			
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='discount in quotation:' mod='locdevis'}
            </label>
            <div class="col-lg-7">
                <table class="table" id="locDevisCartRuleList">
                    <tr>
                        <th style="width:5%">{l s='id' mod='locdevis'}</th>
                        <th>{l s='name' mod='locdevis'}</th>
						<th>{l s='description' mod='locdevis'}</th>
                        <th>{l s='code' mod='locdevis'}</th>
						<th>{l s='free shipping' mod='locdevis'}</th>
						<th>{l s='reduction percent' mod='locdevis'}</th>
						<th>{l s='reduction amount' mod='locdevis'}</th>
						<th>{l s='reduction type' mod='locdevis'}</th>
						<th>{l s='gift product' mod='locdevis'}</th>
						<th>&nbsp;</th>
                    </tr>	
                </table>	
            </div>
            <div style="clear:both;"></div>	
		</div>
	</div>
    <!-- carriers -->
    <div class="panel">
        <h3><i class="icon-truck"></i> {l s='Carriers' mod='locdevis'}</h3>
        <div class="form-horizontal">
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='choose carrier:' mod='locdevis'} <a href="#" id="loc_devis_refresh_carrier_list" style="display:inline-block; vertical-align:middle;"><i class="process-icon-refresh"></i></a>	
            </label>
            <div class="col-lg-7">			
                <select id="loc_devis_carrier_input" name="loc_devis_carrier_input" onchange="$('#selected_carrier').val($(this).val())" class="calcTotalOnChange"></select>	
                <p class="help-block">{l s='First you have to choose customer, addresses and all products then click on the reload button and you will be able to choose a carrier.' mod='locdevis'}</p>				
            </div>
            <div style="clear:both;"></div>
            <input type="hidden" name="selected_carrier" value="{if isset($cart->id_carrier)}{$cart->id_carrier|escape:'htmlall':'UTF-8'}{/if}" id="selected_carrier" />
        </div>
    </div>
    <!-- upload file-->
    <div class="panel">
        <h3><i class="icon-upload-alt"></i> {l s='upload files attachment for mail' mod='locdevis'}</h3>
        <div class="form-horizontal clearfix">
            <center>{l s='You can choose several files, pressing the CTRL key (size max:5MB)' mod='locdevis'}</center>
            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
             <br><center><input id="file-name" type="file" name="filelocdevis[]" multiple enctype="multipart/form-data"></center>
            {if (is_dir($pathuploadfiles)) AND ($dir_flag neq false)}
                {assign var= file value= opendir($pathuploadfiles)}
                {while $files = readdir($file)}
                    {if $files != '.' AND $files != '..'}
                        <br>
                        <div>
                            <center>
                                <a href="{$view_flag|escape:'htmlall':'UTF-8'}locdevis/uploadfiles/{$dir_flag|escape:'htmlall':'UTF-8'}/{$files|escape:'htmlall':'UTF-8'}" target="_blank">{$files|escape:'htmlall':'UTF-8'}</a>&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="button" class="upload_attachement" data-name="{$files|escape:'htmlall':'UTF-8'}" data-id="{$dir_flag|escape:'htmlall':'UTF-8'}" style="background: transparent; border: 0px; padding: 0px; opacity:0.2px; -webkit-appearance: none;" data-dismiss="alert">×</button>
                            </center>
                        </div>
                    {/if}
                {/while}
                {closedir(html_entity_decode($file|escape:'htmlall':'UTF-8'))}{* HTML needed can't escape *}
            {/if}
        </div>
    </div>
    <!-- additional information -->
    <div class="panel">
        <h3><i class="icon-archive"></i> {l s='Additional informations' mod='locdevis'}</h3>
        <div class="form-horizontal">
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='Message:' mod='locdevis'}
            </label>
            <div class="col-lg-7">			
                <textarea name="message_visible">{if isset($obj->message_visible) && $obj->message_visible!=""}{$obj->message_visible|escape:'htmlall':'UTF-8'}{/if}</textarea>	
                <p class="help-block">{l s='Visible on quotation.' mod='locdevis'}</p>						
            </div>
            <div style="clear:both;"></div>
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='Livraison:' mod='locdevis'}
            </label>
            <div class="col-lg-7">			
                <textarea name="delivery_conditions">{if isset($obj->delivery_conditions) && $obj->delivery_conditions!=""}{$obj->delivery_conditions|escape:'htmlall':'UTF-8'}{/if}</textarea>
            </div>
            <div class="date_range col-lg-2">
				<div class="input-group fixed-width-md center">
					<input type="text" class="datepicker" id="deliveryDate" name="delivery_date"  placeholder="{l s='Delivery' mod='locdevis'}" />
					
					<span class="input-group-addon">
						<i class="icon-calendar"></i>
					</span>
				</div>
				<script type="text/javascript">
					$.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );
					$('.datepicker').datepicker({
						dateFormat:'dd-mm-yy',
						minDate: 0
    				});
					$('.datepicker').change(function(){
						$('#datepicker').val($(this).val());
    				});
					</script>
			</div>
			<div class="col-lg-3"></div>
			<div class="col-lg-7">
                <p class="help-block">{l s='Visible on quotation.' mod='locdevis'}</p>						
            </div>
            <div style="clear:both;"></div>
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='Installation:' mod='locdevis'}
            </label>
            <div class="col-lg-7">			
                <textarea name="install_conditions">{if isset($obj->install_conditions) && $obj->install_conditions!=""}{$obj->install_conditions|escape:'htmlall':'UTF-8'}{/if}</textarea>
            </div>
                        <div class="date_range col-lg-2">
				<div class="input-group fixed-width-md center">
					<input type="text" class="datepicker" id="installDate" name="install_date"  placeholder="{l s='Instalation' mod='locdevis'}" />
					
					<span class="input-group-addon">
						<i class="icon-calendar"></i>
					</span>
				</div>
				<script type="text/javascript">
					$.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );
					$('.datepicker').datepicker({
						dateFormat:'dd-mm-yy',
						minDate: 0
    				});
					$('.datepicker').change(function(){
						$('#datepicker').val($(this).val());
    				});
					</script>
			</div>
			<div class="col-lg-3"></div>
			<div class="col-lg-7">	
                <p class="help-block">{l s='Visible on quotation.' mod='locdevis'}</p>						
            </div>
            <div style="clear:both;"></div>
            <div class="form-horizontal">
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" for="loc_devis_product_autocomplete_input">
                {l s='Additional Prestations:' mod='locdevis'}
            </label>
            <div class="col-lg-7">			
                <textarea name="add_presta">{if isset($obj->add_presta) && $obj->add_presta!=""}{$obj->add_presta|escape:'htmlall':'UTF-8'}{/if}</textarea>	
                <p class="help-block">{l s='Visible on quotation.' mod='locdevis'}</p>						
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <!-- TOTAL -->
    <div class="panel">
        <h3><i class="icon-archive"></i> {l s='Total' mod='locdevis'}</h3>
        <div class="form-horizontal">
		
            <!-- total product ht -->
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" style="padding-top:0">
                {l s='Total product without tax:' mod='locdevis'} = 
            </label>
            <div class="col-lg-7"><span id="totalProductHt"></span></div>            
            <div style="clear:both;"></div>
	
            <!-- total discounts ht-->
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" style="padding-top:0">
                {l s='Total discounts without tax' mod='locdevis'} = 
            </label>
            <div class="col-lg-7"><span id="totalDiscountsHt"></span></div>            
            <div style="clear:both;"></div>
            
            <!-- total shipping ht-->
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" style="padding-top:0">
                {l s='Total shipping with out tax' mod='locdevis'} = 
            </label>
            <div class="col-lg-7"><span id="totalShippingHt"></span></div>            
            <div style="clear:both;"></div>
            
            <!-- total tax -->
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" style="padding-top:0">
                {l s='Total tax' mod='locdevis'} = 
            </label>
            <div class="col-lg-7"><span id="totalTax"></span></div>            
            <div style="clear:both;"></div>
			
			
            <!-- total quotation with tax -->
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2" style="padding-top:0; font-size:1.5em;">
                {l s='Total quotation with tax:' mod='locdevis'} = 
            </label>
            <span id="totalQuotationWithTax" style="color:red; font-weight:bold; font-size:1.5em;"></span>     
            <div style="clear:both;"></div>
            <div class="col-lg-1"><span class="pull-right"></span></div>	
            <label class="control-label col-lg-2">
                <a href="#" id="loc_devis_refresh_total_quotation" style="display:inline-block; vertical-align:middle;"><i class="process-icon-refresh"></i></a>{l s='Refesh total' mod='locdevis'}
            </label>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div style="clear:both";></div>
    <div class="panel">
        <div class="panel-footer">
            <a href="{$hrefCancel|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='cancel' mod='locdevis'}</a>
            <button id="locBtnSubmit" disable="true" type="submit" name="submitAddlocDevis" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='save' mod='locdevis'}</button>
        </div>
    </div>
</form>
       {*<pre>
            {$products|@print_r}
        </pre>*}
<script type="text/javascript">
    id_lang_default = {$id_lang_default|escape:'htmlall':'UTF-8'};
    specific_price_txt = "{l s='Specific price'  mod='locdevis'}";
    from_qty_text = "{l s='from'  mod='locdevis'}";
    qty_text = "{l s='quantity'  mod='locdevis'}";
    locDevisControllerUrl = 'index.php?controller=AdminLocDevis&token={$loc_token|escape:'htmlall':'UTF-8'}';
    locDevisMsgQuoteSaved = "{l s='Your quote has been saved' mod='locdevis'}";
    currency_sign = "{$currency_sign|escape:'htmlall':'UTF-8'}";
    nbProductToLoad = 0;
    //urlLoadCarrier = 'index.php?controller=AdminLocDevis&ajax_carrier_list&token={$loc_token|escape:'htmlall':'UTF-8'}';
    {if $customer!=null}
        setTimeout(function(){
            LocDevisAddCustomerToQuotation({$customer->id|escape:'htmlall':'UTF-8'},'{$customer->firstname|escape:'htmlall':'UTF-8'}','{$customer->lastname|escape:'htmlall':'UTF-8'}');
        }, 300); 
    {/if}
    {if $cart!=null}
        {foreach $products AS $product}
            nbProductToLoad++;
            LocDevisAddProductToQuotation({$product.id_product|escape:'htmlall':'UTF-8'},'{$product.name|escape:'htmlall':'UTF-8'}','{$product.catalogue_price|escape:'htmlall':'UTF-8'}',{$product.cart_quantity|escape:'htmlall':'UTF-8'},{$product.id_product_attribute|escape:'htmlall':'UTF-8'},'{$product.specific_price|escape:'htmlall':'UTF-8'}','{$product.your_price|escape:'htmlall':'UTF-8'}','{$product.customization_datas_json}'); {*can't escape this value*}
        {/foreach}
    {/if}
	{if $cart!=null && !empty($summary.discounts)}		
		{foreach $summary.discounts AS $rule}
			{if $rule.reduction_product==-2}
				reduction_type = "{l s='selected product' mod='locdevis'}"
			{else if $rule.reduction_product==-1}
				reduction_type = "{l s='cheapest product' mod='locdevis'}"
			{else if $rule.reduction_product==0}
				reduction_type = "{l s='order' mod='locdevis'}"	
			{else}
				reduction_type = "{l s='specific product' mod='locdevis'} ({$rule.reduction_product})"{/if}
					
			LocDevisAddRuleToQuotation({$rule.id_cart_rule|escape:'htmlall':'UTF-8'},'{$rule.name|escape:'htmlall':'UTF-8'}','{$rule.description|escape:'htmlall':'UTF-8'}','{$rule.code|escape:'htmlall':'UTF-8'}',{$rule.free_shipping|escape:'htmlall':'UTF-8'},'{$rule.reduction_percent|escape:'htmlall':'UTF-8'}','{$rule.reduction_amount|escape:'htmlall':'UTF-8'}',reduction_type,{$rule.gift_product|escape:'htmlall':'UTF-8'});
		{/foreach}
	{/if}
        //locDevisCalcReducedPrice();
        $(document).ready(function() {
            LocDevisPopulateSelectCarrier('{$json_carrier_list|escape:'quotes'}');         
        });
</script>