$(document).ready(function() {
    locDevisLoadCarrierList();
    $('#loc_devis_carrier_input').change(function() {
        LocDevisChangeCarrier();
    });
})

