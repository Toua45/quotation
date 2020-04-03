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
        // Utilisation de la méthode query dans quotation_module.js
        let query = QuotationModule.getQueryURL(Event.currentTarget.value);

        const insertCustomerInDOM = (customers) => {
            let output = '';
            // Crée un lien basé sur l'identifiant (id) du client
            // Exemple: http://localhost:8000/admin130mdhxh9/index.php/modules/quotation/admin/show/customer/2

            customers.forEach((customer, i) => {
                import('./templates_module').then(mod => {
                    output += mod.TemplateModule.card
                        .replace(/---lastname---/, customer.lastname.toUpperCase())
                        .replace(/---firstname---/, customer.firstname)
                        .replace(/---text---/, 'This is a good customer!')
                        .replace(/---link---/, QuotationModule.getShowCustomerURL('adminLionel') + customer.id_customer)
                    ;
                    if (customers.length - 1 === i) {
                        document.getElementById('js-output-customers').innerHTML = output;
                    }
                });
            });
        };

        QuotationModule.getData(
            urlSearchCustomers.replace(/query/, query),
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



// (function() {
//    document.getElementById("refresh").addEventListener('click',Event => {
//        // console.log(Event.currentTarget);
//        document.getElementById("filter_quotation").reset();
//        window.location.reload();
//    });
// })();

$(document).ready(function() {
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
    })
});

// <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
//     <link rel="stylesheet" href="/resources/demos/style.css">
//     <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
//     <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

// $( function() {
//     $( "#quotation_search_start" ).datepicker();
// } );
