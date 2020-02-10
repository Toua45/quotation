/*
* @category Prestashop
* @category Module
* @author  Florian de ROCHEFORT
* @copyright  AQUAPURE
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**/

$( document ).ready(function() {
    var payment_button = $("#payment-confirmation button").html();
    $("#checkout-payment-step input").click(function (e) {
        setTimeout(function() {
            if ($("#loc-devis-payment").parent().css('display') == "block"){
                $("#payment-confirmation button").html($("#loc-devis-payment").html());
            } else {
                $("#payment-confirmation button").html(payment_button);
            }
        }, 100);
    })
});

function locDevisLoadCarrierList() {
    var form=$('#locDevisForm');
	data=form.serialize();
	//ajax call
	$.ajax({ 
    	type : 'POST', 
        //url :locDevisControllerUrl+'&ajax_carrier_list&'+data,
        url :locDevisControllerUrl+'&ajax_carrier_list',
        data : data,
        success : function(data){
            //update id_cart field
            var d = $.parseJSON(data);
           //console.log(data);
            $('#loc_devis_id_cart').val(d.id_cart);
            LocDevisPopulateSelectCarrier(data);
        }, error : function(XMLHttpRequest, textStatus, errorThrown) { 
           alert('Une erreur est survenue !'); 
        }
    });	
}
function LocDevisPopulateSelectCarrier(data) {
    //decode jsoon;
    data = $.parseJSON(data);

    if (data['prefered_order']) {
        // get prefered carrier order
        var order = data['prefered_order'].split(',');

        var carrierSelect = $('#loc_devis_carrier_input');
        carrierSelect.html('');

        for (var key of order) {
            if ($('#selected_carrier').val() == key)
                var selected = 'selected';
            else
                var selected = '';
            carrierSelect.append('<option value="' + key + '" ' + selected + '>' + data[key]['name'] + ' - ' + data[key]['price'] + ' '+currency_sign+' (' + data[key]['taxOrnot'] + ')</option>');
        }
        LocDevisChangeCarrier();
    }
}
function LocDevisChangeCarrier() {
    data=$('#locDevisForm').serialize();
    $.ajax({
        type: 'POST',
        //url: locDevisControllerUrl + '&change_carrier_cart&' + data,
        url: locDevisControllerUrl + '&change_carrier_cart',
        data : data,
        success: function (data) {
           if(data != '') {
                var data = $.parseJSON(data);
                $('#locQuotationTotalQuotationWithTax').html(formatCurrency(data.total_price, currency_format, currency_sign, currency_blank));
                $('#locQuotationTotalQuotation').html(formatCurrency(data.total_price_without_tax, currency_format, currency_sign, currency_blank));
                $('#locQuotationTotalTax').html(formatCurrency(data.total_tax, currency_format, currency_sign, currency_blank));
                $('#locQuotationTotalDiscounts').html(formatCurrency(data.total_discounts, currency_format, currency_sign, currency_blank));
                if(priceDisplay==1)
                    $('#locQuotationTotalShipping').html(formatCurrency(data.total_shipping_tax_exc, currency_format, currency_sign, currency_blank));					
		else    
                    $('#locQuotationTotalShipping').html(formatCurrency(data.total_shipping, currency_format, currency_sign, currency_blank));
            }
            //LocDevisCalcTotalDevis();
        }, error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('Une erreur est survenue !');
        }
    });
}
