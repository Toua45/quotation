import '../scss/app.scss';

import {QuotationModule} from './quotation_module';

// any SCSS you require will output into a single scss file (app.scss in this case)

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});

// Récupère le chemin du JSON par l'id 'js-data'
let url = document.getElementById('js-data').dataset.source;

// Attention à l'ordre de l'exécution des fonctions !

/**
 * Fonction principale => HTTP Request
 * url type=string
 * callback
 * path type=string
 * dataFetch type=bool
 * autocomplete = []
 */
QuotationModule.getData(
    url,
    QuotationModule.getData,
    QuotationModule.DOM.urlCustomers,
    false,
    []
);

/**
 * Fonction qui récupère les données dans le data-customer.js
 * Met aussi en parallèle en place l'autocomplétion
 * callback
 * path type=string
 * dataFetch type=bool
 * autocomplete = [(string) selector, (string) name, (int) minLength]
 */

QuotationModule.getData(
    QuotationModule.DOM.urlCustomers,
    QuotationModule.autocomplete,
    null,
    true,
    ['#quotation_customerId', 'customers', 2]
);
