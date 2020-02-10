/*
* @category Prestashop
* @category Module
* @author Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**/
$(document).ready(function(){
	//autocomplete product
	$('#loc_devis_product_autocomplete_input').autocomplete(
                'index.php?controller=AdminLocDevis&ajax_product_list',
                {
		minChars: 1,
		autoFill: true,
		max:200,
		matchContains: true,
		scroll:false,
		cacheLength:0,
		formatItem: function(item) {
			return item[0]+' - '+item[1]+' - '+item[2];
		},
                extraParams: {
                    token: token,
                    id_customer: function() {return $('#loc_devis_customer_id').val()}
		}
	}).result(function(e,i){
               console.log(i);
		if(i != undefined)
                    LocDevisAddProductToQuotation(i[0],i[1],i[2],1,0,i[3]);
		$(this).val('');
	});

	//Hide Renting
	if ($('#is_renting_on').prop('checked') === true) {		
		$('#renting_duration').show('slow');
	}
	
	if ($('#is_renting_off').prop('checked') === true) {
		$('#renting_duration').hide();
	}

	$('#is_renting_on').click(function(e) {
		$('#renting_duration').slideDown('slow');
	});

	$('#is_renting_off').click(function(e) {
		$('#renting_duration').slideUp();
	});

	//autocomplete customer
	$('#loc_devis_customer_autocomplete_input').autocomplete(
                'index.php?controller=AdminLocDevis&ajax_customer_list&token='+token,
                {
                    minChars: 1,
                    autoFill: true,
                    max:200,
                    matchContains: true,
                    scroll:false,
                    dataType: 'json',
                    cacheLength:0,
                    formatItem: function(data, i, max, value, term) {
                            return value;
                    },
                    parse: function(data) {
                        //console.log(data);
                            var mytab = new Array();
                            for (var i = 0; i < data.length; i++)
                                    mytab[mytab.length] = { data: data[i], value: (data[i].id_customer + ' - ' + data[i].lastname + ' - ' + data[i].firstname).trim() };
                            return mytab;
                    }
                }).result(function(e,i){
		if(i != undefined)
                    LocDevisAddCustomerToQuotation(i['id_customer'], i['lastname'],i['firstname']);
		$(this).val('');
	});

	$('#loc_devis_refresh_carrier_list').click(function(e) {
		locDevisLoadCarrierList();
		e.preventDefault();
	});

	$('#loc_devis_refresh_total_quotation').click(function(e) {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        $(this).addClass('disabled');
		LocDevisCalcTotalDevis();
		e.preventDefault();
	});

	$('#loc_devis_select_cart_rules').change(function(e) {

                $('#locDevisCartRulesMsgError').hide('fast');
		if($(this).val()!="-1") {
			if($('#trCartRule_'+$(this).val()).length>0) {
				alert('Cart rule already added');
				return false;
			}
			$.ajax({
			type : 'POST',
			url : 'index.php?controller=AdminLocDevis&ajax_load_cart_rule&token='+token,
			//data: 'id_cart_rule='+$(this).val(),
                        data: $('#locDevisForm').serialize()+'&id_cart_rule='+$(this).val(),
			success : function(data){
				data=$.parseJSON(data);
                                if(!data.id) {
                                    locDevisShowCartRuleErrors(data);
                                }
                                else {
                                    //console.log(id_lang_default);
                                    LocDevisAddRuleToQuotation(data.id,data.name[id_lang_default],data.description,data.code,data.free_shipping,data.reduction_percent,data.reduction_amount,'0',data.gift_product);
                                }
                            }, error : function(XMLHttpRequest, textStatus, errorThrown) {
			   alert('Une erreur est survenue !');
			}
		});
		}
		locDevisLoadCarrierList();
		e.preventDefault();
	});

        /*$('.calcTotalOnChange').change(function() {
            LocDevisCalcTotalDevis();
        })*/

	//LocDevisCalcTotalDevis();
        $('.upload_attachement').on('click', function(e) {
            locdeleteupload(this);
            e.preventDefault();
	});

})

function locDevisShowCartRuleErrors(msg) {
    $('#locDevisCartRulesMsgError').html(msg);
    $('#locDevisCartRulesMsgError').show('slow');
}

function locDevisAutoChangePrice(currentInput,inputClass) {
    $('.'+inputClass).each(function() {
        $(this).val(currentInput.value);
    })
}

var boolForLine=1;
function LocDevisAddProductToQuotation(prodId,prodName,prodPrice,qty,idAttribute,specificPrice,yourPrice,customization_datas_json) {
        LocToggleSubmitBtn(0);
        var id_attribute = (idAttribute == null)?idAttribute:null;
        var specificPrice = (specificPrice != undefined)?specificPrice:'';
        var specificQty = (specificQty != undefined)?specificQty:'';
        var yourPrice = (yourPrice != undefined)?yourPrice:'';

	randomId=new Date().getTime();
        boolForLine = (boolForLine == 1)?0:1;
        var customization_datas = $.parseJSON(customization_datas_json);
        //console.log(customization_datas);
        var displayedCustomizationDatas = '';
        var qtyInputType = 'text';
        var onChangeCustomizationPrice ='';
        var customPriceClass=''
        if(customization_datas) {
            for (var i = 0; i < customization_datas.length; i++){
                displayedCustomizationDatas+='<tr class="trAdminCustomizationData"><td colspan="6" class="tdAdminCustomizationDataValue">';
                var customization_datas_array = customization_datas[i]['datas'][1];
                for (var j = 0; j < customization_datas_array.length; j++){
                    var addBr = (j>0)?'<br />':'';
                    displayedCustomizationDatas+=addBr+customization_datas_array[j]['name']+ ' : '+customization_datas_array[j]['value'];
                }
                displayedCustomizationDatas+='<td class="tdAdminCustomizationDataQty"><input type="text" value="'+customization_datas[i]['quantity']+'" name="add_customization['+randomId+']['+customization_datas[i]['datas']['1']['0']['id_customization']+'][newQty]" /></td></td><td></td></tr>';
            }
            qtyInputType = 'hidden';
            customPriceClass = 'customprice_'+prodId+'_'+idAttribute;
            onChangeCustomizationPrice = 'onchange="locDevisAutoChangePrice(this,\''+customPriceClass+'\')"';
        }
    var newTr='<tr class="line_'+boolForLine+'" id="trProd_'+randomId+'" style="display:none;">';
	newTr+='<td id="tdIdprod_'+randomId+'">'+prodId+'<input type="hidden" name="whoIs['+randomId+']" value="'+prodId+'" id="whoIs_'+randomId+'"/></td>';
	newTr+='<td>'+prodName+'</td>';
	newTr+='<td id="declinaisonsProd_'+randomId+'"></td>';
	newTr+='<td class="prodPrice" id="prodPrice_'+randomId+'">'+prodPrice+'</td>';
	newTr+='<td class="prodPrice" id="prodReducedPrice_'+randomId+'">'+specificPrice+'</td>';
        newTr+='<td><input '+onChangeCustomizationPrice+' name="specific_price['+randomId+']" id="specificPriceInput_'+randomId+'" type="text" value="'+yourPrice+'" class="calcTotalOnChange '+customPriceClass+'"/></td>';
	newTr+='<td class="productPrice">';
        newTr+='<input id="inputQty_'+randomId+'" type="'+qtyInputType+'" value="'+qty+'" name="add_prod['+randomId+']" class="locDevisAddProdInput calcTotalOnChange"/>';
        if(customization_datas) {
            newTr+='<span></span>';
        }
        newTr+='</td>';
        newTr+='<td>';
        if(!customization_datas) {
            newTr+='<a href="#" onclick="locDevisDeleteProd(\''+randomId+'\'); return false;"><i class="icon-trash"></i></a>';
        }
        newTr+='</td>';
        newTr+='</tr>';
        newTr+=displayedCustomizationDatas;
	$('#locDevisProdList').append(newTr);
	$('#trProd_'+randomId).show('slow');
	//$('#trSpecificPrice_'+randomId).show('slow');
    //load declinaison
    LocDevisLoadDeclinaisons(randomId,idAttribute);
    LocBindOnChange();
}
function LocBindOnChange() {
    $('.calcTotalOnChange').unbind( "change" );
    $('.calcTotalOnChange').change(function() {
        //var randomId = $(this).attr('id').replace('select_attribute_','');
        var randomId = $(this).attr('id').substring($(this).attr('id').lastIndexOf('_')+1);
        if($('#specificPriceInput_' + randomId).val()=='') {
            if($('#last_selected_attribute_'+randomId).length)
                var id_attribute = $('#last_selected_attribute_'+randomId).val();
            else
                var id_attribute = 0;

            deleteOldSpecificPrice(randomId,id_attribute);
            var current_id_attribute = $('#select_attribute_' + randomId).val()
            $('#last_selected_attribute_' + randomId).val(current_id_attribute);
        }
        locDevisCalcReducedPrice();
     });
}

function LocDevisAddRuleToQuotation(ruleId,name,description,code,free_shipping,reduction_percent,reduction_amount,reduction_type,gift_product) {

        var gift_product_link=(gift_product==0)?'':gift_product;
	var newTr='<tr id="trCartRule_'+ruleId+'" style="display:none;">';
	newTr+='<td>'+ruleId+'<input type="hidden" name="add_rule[]" value="'+ruleId+'" /></td>';
	newTr+='<td>'+name+'</td>';
	newTr+='<td>'+description+'</td>';
	newTr+='<td>'+code+'</td>';
	newTr+='<td>'+((free_shipping==1)?'<i class="icon-check"></i>':'')+'</td>';
	newTr+='<td>'+reduction_percent+'</td>';
	newTr+='<td>'+reduction_amount+'</td>';
	newTr+='<td>'+reduction_type+'</td>';
	newTr+='<td>'+gift_product_link+'</td>';
	newTr+='<td><a href="#" onclick="locDevisDeleteRule(\''+ruleId+'\'); return false;"><i class="icon-trash"></i></a></td>';
	newTr+='</tr>';
	$('#locDevisCartRuleList').append(newTr);
	$('#trCartRule_'+ruleId).show('slow');
    //LocDevisCalcTotalDevis();
}
/*
function LocDevisLoadPrice(randomId) {
  	$.ajax({
    	type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_get_total_line&token='+token,
        data: $('#locDevisForm').serialize(),
        success : function(data){
            var data = $.parseJSON(data);
            //console.log(data);
            //console.log(data);
            $('#totalQuotationWithTax').html(data.total_price.toFixed(2));
			$('#totalQuotation').html(data.total_price_without_tax.toFixed(2));
			$('#totalTax').html(data.total_tax.toFixed(2));
			$('#totalDiscounts').html(data.total_discounts.toFixed(2));
			$('#totalShipping').html(data.total_shipping.toFixed(2));
			//calc reduced price
			locDevisCalcReducedPrice();

        }, error : function(XMLHttpRequest, textStatus, errorThrown) {
           alert('Une erreur est survenue !');
        }
    });
}
*/
function LocDevisLoadDeclinaisons(randomId,idAttribute) {
    LocToggleSubmitBtn(0);
    var prodId=$('#whoIs_'+randomId).val();
    $.ajax({
    	type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_load_declinaisons&token='+token,
        data: 'id_prod='+prodId,
        success : function(data,prodId){
            LocDevisPopulateDeclinaisons(data,randomId,idAttribute);
        }, error : function(XMLHttpRequest, textStatus, errorThrown) {
           alert('Une erreur est survenue !');
        }
    });
}

function LocDevisPopulateDeclinaisons(data,randomId,idAttribute) {
    if(data.length==0)
        return false;
    data=$.parseJSON(data);
    //select soit defaut soit selected
    var s = $('<select id="select_attribute_'+randomId+'" name="add_attribute['+randomId+']" class="calcTotalOnChange calcTotalOnChangeDec" />');
    for (var key in data) {
       var selected="";
       if(idAttribute!=0 && key==idAttribute)
           selected="selected";
       else if(idAttribute==0 && data['default_on']==1)
            selected="selected";
       s.append('<option '+selected+' value="' + key + '" title="'+data[key]['price']+'">'+ data[key]['attribute_designation']+' ['+data[key]['reference']+'] ('+data[key]['price']+')</option>');
    }

    $('#declinaisonsProd_'+randomId).append(s);
    //add hidden field last id attribute
    var hidden_field_value = $('#select_attribute_'+randomId).val();
    var hidden_field = '<input type="hidden" value="'+hidden_field_value+'" id="last_selected_attribute_'+randomId+'" />';
    $('#declinaisonsProd_'+randomId).append(hidden_field);

    LocBindOnChange();
    LocToggleSubmitBtn(1);
}
function LocDevisCalcTotalDevis() {
        //console.log('total');
        LocToggleSubmitBtn(0);
	$.ajax({
    	type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_get_total_cart&token='+token,
        data: $('#locDevisForm').serialize(),
        success : function(data){
                        console.log(data);
			var data = $.parseJSON(data);
			$('#totalProductHt').html(data.total_products.toFixed(2));
			$('#totalDiscountsHt').html(data.total_discounts_tax_exc.toFixed(2));
			$('#totalShippingHt').html(data.total_shipping_tax_exc.toFixed(2));
			$('#totalTax').html(data.total_tax.toFixed(2));
			// $('#totalQuotationWithTax').html(data.total_price.toFixed(2));
            if (data.group_tax_method) {
                $('#totalTax').html('<strike>'+(data.total_tax.toFixed(2))+'</strike>');
                $('#totalQuotationWithTax').html((data.total_price-data.total_tax).toFixed(2));
            } else {
                $('#totalTax').html(data.total_tax.toFixed(2));
                $('#totalQuotationWithTax').html(data.total_price.toFixed(2));
            }
			$('#loc_devis_id_cart').val(data.id_cart);
			//calc reduced price
			locDevisCalcReducedPrice();
            $('#loc_devis_refresh_total_quotation').removeClass('disabled');

        }, error : function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(XMLHttpRequest);
           console.log(XMLHttpRequest.responseText);
           alert('Une erreur est survenue !');
           $('#loc_devis_refresh_total_quotation').removeClass('disabled');
        }
    });
    if($("input[name=id_locdevis]").length>0)
        LocDevisShowAjaxMsg(locDevisMsgQuoteSaved,'locDevisMsg');


}

function LocDevisAddCustomerToQuotation(customerId,firstname,lastname) {
	var newHtml='('+customerId+') '+lastname+' '+firstname;
	$('#loc_devis_customer_info').html(newHtml);
	$('#loc_devis_customer_id').val(customerId);
 	$.ajax({
    	type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_address_list&token='+token,
        data: 'id_customer='+customerId,
        success : function(data){
        	//console.log(data);
        	LocDevisPopulateSelectAddress(data);
        }, error : function(XMLHttpRequest, textStatus, errorThrown) {
           alert('Une erreur est survenue !');
        }
    });
}

function LocDevisPopulateSelectAddress(data) {
	//decode jsoon;
	data=$.parseJSON(data);
	if(typeof data['erreur']!='undefined') {
            console.log(data['erreur']);
            return false;
        }
	//console.log(data);
	var invoiceSelect=$('#loc_devis_invoice_address_input');
	var deliverySelect=$('#loc_devis_delivery_address_input');
	invoiceSelect.html('');
	deliverySelect.html('');
	for (var key in data) {
            if(data[key]['address2']!="")
                var address2=data[key]['address2']+" - "
            else
                var address2="";
            if($('#selected_invoice').val()==key)
                var selectedInvoice="selected";
            else
                var selectedInvoice="";
            if($('#selected_delivery').val()==key)
                var selectedDelivery="selected";
            else
                var selectedDelivery="";
            invoiceSelect.append('<option '+selectedInvoice+' value="' + key + '">['+ data[key]['alias']+'] - '+ data[key]['company']+' - '+ data[key]['lastname']+' '+data[key]['firstname']+' - '+data[key]['address1']+' - '+address2+data[key]['postcode']+' - '+data[key]['city']+' - '+data[key]['country_name']+'</option>');
            deliverySelect.append('<option '+selectedDelivery+' value="' + key + '">['+ data[key]['alias']+'] - '+ data[key]['company']+' - '+ data[key]['lastname']+' '+data[key]['firstname']+' - '+data[key]['address1']+' - '+address2+data[key]['postcode']+' - '+data[key]['city']+' - '+data[key]['country_name']+'</option>');
	}
        //locDevisLoadCarrierList();
}

function deleteOldSpecificPrice(idRandom,id_attribute) {
    var id_cart = $('#loc_devis_id_cart').val();
    var id_prod = $('#whoIs_'+idRandom).val();

    $.ajax({
        type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_delete_specific_price&token='+token,
        data: 'id_cart='+id_cart+'&id_prod='+id_prod+'&id_attribute='+id_attribute,
        success : function(data){
        }, error : function(XMLHttpRequest, textStatus, errorThrown) {
            alert('Une erreur est survenue !');
        }
    });
}

function locDevisDeleteProd(idRandom) {
    $('#trProd_'+idRandom).hide("slow", function() {
        if($('#select_attribute_'+idRandom).length)
            var id_attribute = $('#select_attribute_'+idRandom).val();
        else
            var id_attribute = null;
        deleteOldSpecificPrice(idRandom,id_attribute);
        $('#trProd_'+idRandom).remove();
        //LocDevisCalcTotalDevis();

    });
}

function locDevisDeleteRule(ruleId) {
    $('#trCartRule_'+ruleId).hide("slow", function() {
        $('#trCartRule_'+ruleId).remove();
       //LocDevisCalcTotalDevis();
    });
}

function locDevisCalcReducedPrice() {
    LocToggleSubmitBtn(0);
    //add id attribute to whoIs value to
    $.ajax({
    	type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_get_reduced_price&token='+token,
        data: $('#locDevisForm').serialize(),
        success : function(data){
               console.log(data);
			var data = $.parseJSON(data);
			for(i = 0; i < data.length; i++) {
                            $('#prodPrice_'+data[i]['random_id']).html(data[i]['real_price']);
                            $('#prodReducedPrice_'+data[i]['random_id']).html(data[i]['reduced_price']);
                            $('#specificPriceInput_'+data[i]['random_id']).val(data[i]['your_price']);
			}
                         LocToggleSubmitBtn(1);
                        //$('#waitProductLoad').hide('slow');
        	//LocDevisPopulateSelectAddress(data);
			//$('#'+randomId).html(data);
        }, error : function(XMLHttpRequest, textStatus, errorThrown) {
           alert('Une erreur est survenue !');
        }
    });
}
function locdeleteupload(elt){
    $.ajax({
    	type : 'POST',
        url : 'index.php?controller=AdminLocDevis&ajax_delete_upload_file&token='+token,
        data:{upload_name: $(elt).attr('data-name'),upload_id:$(elt).attr('data-id'),},
        //success : console.log('ok'),
    });
}

function LocDevisShowAjaxMsg(msg,className) {
    $('#locDevisMsgAlwaysTop').html(msg);
    $('#locDevisMsgAlwaysTop').removeClass('locDevisMsg','locDevisError');
    $('#locDevisMsgAlwaysTop').addClass(className);
    $('#locDevisMsgAlwaysTop').show(300).delay(2000).hide(300);
}

function LocToggleSubmitBtn(showMe) {
    if(showMe == 0)
        $('#locBtnSubmit').prop('disabled',true);
    else
        $('#locBtnSubmit').prop('disabled',false);
}