import '../scss/app.scss';
import {QuotationModule} from "./quotation_module";

if (QuotationModule.getParamFromURL('add') !== null &&QuotationModule.getParamFromURL('add').length === 1) {
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
            // Build show customer link based on his id.
            // Exemple: http://localhost:8000/adminToua/index.php/modules/quotation/admin/show/customer/2
            let link = window.location.origin + '/adminToua/index.php/modules/quotation/admin/show/customer/';
            customers.forEach((customer, i) => {
                import('./templates_module').then(mod => {
                    output += mod.TemplateModule.card
                        .replace(/---lastname---/, customer.lastname.toUpperCase())
                        .replace(/---firstname---/, customer.firstname)
                        .replace(/---text---/, 'This is a good customer!')
                        .replace(/---link-show-customer---/, link + customer.id_customer)
                        .replace(/---link-show-customer-carts---/, link + customer.id_customer + '/details')
                    ;

                    if (customers.length - 1 === i) {
                        document.getElementById('js-output-customers').innerHTML = output;

                        let urlCustomersDetails = document.querySelector('[data-customerdetails]').dataset.customerdetails;
                        //console.log(document.querySelectorAll('.customer-details'));

                        const getCustomerDetails = function (data) {
                            console.log(data);
                            /**
                             *
                             * @type {string}
                             */
                            // console.log(document.querySelectorAll('.customer-details'));
                            // document.getElementsByClassName('customer-details').forEach(function (link, i) {
                            //     link.addEventListener('click', function (Event) {
                            //         Event.preventDefault();
                            //         console.log(link);
                            //         let newUrlCustomersDetails = urlCustomersDetails.replace(/\d+(?=\/details)/, Event.currentTarget.value);
                            //         mod.TemplateModule.table
                            //             .replace(/---cartID---/, cart.cartId)
                            //             .replace();
                            //     });
                            // })

                        };

                        QuotationModule.getData(
                            urlCustomersDetails.replace(/id_customer/, 2),
                            getCustomerDetails,
                            null,
                            true,
                            []
                        );
                    }
                });
            });


        };

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

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});


