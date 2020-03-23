import '../scss/app.scss';
import {QuotationModule} from "./quotation_module";
import {TemplateModule} from "./templates_module";

if (QuotationModule.getParamFromURL('add') !== null && QuotationModule.getParamFromURL('add').length === 1) {
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
        QuotationModule.getCustomersURL(),
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
        QuotationModule.getCustomersURL(),
        QuotationModule.autocomplete,
        null,
        true,
        ['#quotation_customerId', 'customers', 1]
    );

    let urlSearchCustomers = document.querySelector('[data-searchcustomers]').dataset.searchcustomers;

    const getQuery = (Event) => {
        let query = Event.currentTarget.value !== ' ' || Event.currentTarget.value !== '' ?
            Event.currentTarget.value.replace(/\s(?=\w)(\w)+/, '') : false;

        const insertCustomerInDOM = (customers) => {
            let output = '';
            console.log(customers)
            // Build show customer link based on his id.
            // Exemple: http://localhost:8000/admin130mdhxh9/index.php/modules/quotation/admin/show/customer/2
            // let link = window.location.origin + '/adm/index.php/modules/quotation/admin/show/customer/';
            let show = window.location.origin + '/adm/index.php/sell/customers/';
            customers.forEach((customer, i) => {
                import('./templates_module').then(mod => {
                    output += mod.TemplateModule.card
                        .replace(/---lastname---/, customer.lastname.toUpperCase())
                        .replace(/---firstname---/, customer.firstname)
                        .replace(/---text---/, 'This is a good customer!')
                        // .replace(/---link---/, link + customer.id_customer)
                        // .replace(/---link-show-customer---/, show + customer.id_customer + '/view')
                        .replace(/---modal-customer-infos---/, (TemplateModule.modalCustomerInfos
                            .replace(/---personal-datas---/, (TemplateModule.personalData
                                .replace(/---firstname---/, customer.firstname)
                                .replace(/---lastname---/, customer.lastname)
                                .replace(/---id-customer---/, customer.id_customer)
                                .replace(/---customer-email---/, customer.email)
                                .replace(/---customer-link-email---/, 'mailto:' + customer.email)
                            )
                            )
                        ))
                    ;
                    if (customers.length - 1 === i) {
                        document.getElementById('js-output-customers').innerHTML = output;
                    }
                });
            });
        };

        console.log(urlSearchCustomers.replace(/query/, Event.currentTarget.value));
        QuotationModule.getData(
            urlSearchCustomers.replace(/query/, Event.currentTarget.value),
            insertCustomerInDOM,
            null,
            true,
            []
        );
    };

    const inputSearchCustomers = document.getElementById('quotation_customerId');
    ['keyup', 'change'].forEach(event => {
        inputSearchCustomers.addEventListener(event, getQuery, false);
    });
}

// any SCSS you require will output into a single scss file (app.scss in this case)

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
});