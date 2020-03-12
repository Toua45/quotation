import {QuotationModule} from './customer';
import {customer} from '../../../../adminLionel/data-customer';

let customerJson = document.getElementById('js-data');
let url = customerJson.dataset.source;

QuotationModule.getData(
    url,
    QuotationModule.getData,
    QuotationModule.DOM.urlCustomers,
    false,
    []
);

QuotationModule.getData(
    QuotationModule.DOM.urlCustomers,
    QuotationModule.autocompletition,
    null,
    true,
    ['#quotation_customerId', 'customers', 2]
);
