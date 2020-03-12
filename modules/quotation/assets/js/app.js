import {QuotationModule} from './customer';
import {customer} from '../../../../adminLionel/data-customer';

let urlCustomer = document.getElementById('js-data').dataset.source;
// let upload = QuotationModule.getData(QuotationModule.DOM.urlCustomers,QuotationModule.autocomplete, true);

// Permet de récupérer les données dans notre fichier data-customer.js

const autocomplete = function() {
    QuotationModule.autocomplete(true,'#quotation_customerId', 'customer', 2);
};

function upload() {
    QuotationModule.getData(QuotationModule.DOM.urlCustomers, autocomplete, QuotationModule.DOM.urlCustomers, true);
}

QuotationModule.getData(urlCustomer, upload); // Permet d'exécuter la fonction fetch qui se trouve dans customer
