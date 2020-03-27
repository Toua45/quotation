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
            let link = window.location.origin + '/adm/index.php/modules/quotation/admin/show/customer/';
            let show = window.location.origin + '/adm/index.php/sell/customers/';
            customers.forEach((customer, i) => {
                import('./templates_module').then(mod => {
                    output += mod.TemplateModule.card
                        .replace(/---lastname---/, customer.lastname.toUpperCase())
                        .replace(/---firstname---/, customer.firstname)
                        .replace(/---text---/, 'This is a good customer!')
                        .replace(/---modal-customer-infos---/, (TemplateModule.modalCustomerInfos
                                    .replace(/---personal-datas---/, (TemplateModule.personalData
                                        .replace(/---firstname---/, customer.firstname)
                                        .replace(/---lastname---/, customer.lastname)
                                        .replace(/---id-customer---/, customer.id_customer)
                                        .replace(/---customer-link-email---/, 'mailto:' + customer.email)
                                        .replace(/---customer-email---/, customer.email)
                                        .replace(/---edit---/, show + customer.id_customer + '/edit')
                                        .replace(/---gender---/, customer.title)
                                        .replace(/---old---/, Math.floor(customer.old))
                                        .replace(/---birthday---/, customer.birthday)
                                        .replace(/---registration---/, customer.registration)
                                        .replace(/---lang---/, customer.lang)
                                        .replace(/---last-update---/, customer.last_update)
                                        .replace(/---badge-newsletter---/, (customer.newsletter === 1 ? 'badge-success' : 'badge-danger'))
                                        .replace(/---icon-newsletter---/, (customer.newsletter === 1 ? 'check' : 'cancel'))
                                        .replace(/---badge-partners---/, (customer.offer_partners === 1 ? 'badge-success' : 'badge-danger'))
                                        .replace(/---icon-partners---/, (customer.offer_partners === 1 ? 'check' : 'cancel'))
                                        .replace(/---badge-is-active---/, (customer.active === 1 ? 'badge-success' : 'badge-danger'))
                                        .replace(/---icon-is-active---/, (customer.active === 1 ? 'check' : 'cancel'))
                                        .replace(/---is-active---/, (customer.active === 1 ? 'Activé' : 'Désactivé')))
                                    )
                                    .replace(/---customer-orders---/, (TemplateModule.customerOrders
                                        .replace(/---nb-orders---/, customer.orders)
                                        )
                                    )
                            )
                        )
                        .replace(/---id---/, customer.id_customer)
                        .replace(/---link-show-customer-carts---/, link + customer.id_customer + '/details')
                        .replace(/---increment---/, i)
                    ;
                    // console.log(mod.TemplateModule.card)
                    if (customers.length - 1 === i) {
                        document.getElementById('js-output-customers').innerHTML = output;

                        // Initialisation de la variable urlCustomersDetails qui prend l'élément data-customerdetails du fichier add_quotation.html.twig
                        let urlCustomersDetails = document.querySelector('[data-customerdetails]').dataset.customerdetails;
                        let newUrlCustomersDetails;

                        // document.querySelectorAll renvoie tous les éléments du document qui correspondent à un sélecteur CSS, ici, tous les éléments a de la class customer-details
                        if (document.querySelectorAll('a.customer-details') !== null) {
                            document.querySelectorAll('a.customer-details').forEach(function (link) { // On boucle sur chaque élément link auquel on attache l'évènement clic
                                link.addEventListener('click', function (Event) {
                                    // Annule le comportement par défaut de l'événement, ici empeche le lien d'ouvrir l'url quotation/admin/show/customer/{id_customer}/details
                                    Event.preventDefault();
                                    // Renvoie l'élément parent le plus proche de l'élément courant (ici id customer-card_ est le plus proche de la class hidden)
                                    Event.currentTarget.closest('.hidden').classList.toggle('hidden'); // La méthode toggle permet de masquer ou d'afficher le paramètre hidden à l'élément class
                                    // Pour chaque cards qui aura la class hidden, ces dernières seront en display-none
                                    document.querySelectorAll('.hidden').forEach(function (card, index) {
                                        card.classList.add('d-none');
                                    });

                                    // window.location.origin renvoie le protocole, le nom d'hôte et le numéro de port d'une URL (ici en dev http://localhost:8000, en prod, ce sera le nom de domaine)
                                    newUrlCustomersDetails = window.location.origin + urlCustomersDetails
                                        // On récupére l'id_customer (par défaut 0 ici) avec le regex et on le remplace par l'id_customer selectionné du lien
                                        .replace(/\d+(?=\/details)/, link.dataset.idcustomer);

                                    const getCustomerDetails = (data) => {
                                        console.log(data);
                                        let outputCart = '';
                                        let outputOrder = '';
                                        let outputQuotation = '';
                                        // L'instruction for...of permet de créer une boucle d'un array qui parcourt un objet itérable
                                        for (let customer of data) {
                                            outputCart += mod.TemplateModule.tableCart
                                                .replace(/---cartId---/, customer.id_cart)
                                                .replace(/---cartDate---/, customer.date_cart)
                                                .replace(/---totalCart---/, customer.total_cart + ' €');
                                        }
                                        for (let customer of data) {
                                            if (typeof customer.id_order !== 'undefined') {
                                                outputOrder += mod.TemplateModule.tableOrder
                                                    .replace(/---orderId---/, customer.id_order)
                                                    .replace(/---orderDate---/, customer.date_order)
                                                    .replace(/---totalOrder---/, customer.total_paid + ' €')
                                                    .replace(/---payment---/, customer.payment);
                                            }
                                        }
                                        for (let customer of data) {
                                            if (typeof customer.id_quotation !== 'undefined') {
                                                outputQuotation += mod.TemplateModule.tableQuotation
                                                    .replace(/---quotationId---/, customer.id_quotation)
                                                    .replace(/---quotationDate---/, customer.date_quotation)
                                                    .replace(/---totalQuotation---/, customer.total_quotation + ' €');
                                            }
                                        }

                                        /**
                                         * La propriété innerHTML définit ou retourne le contenu HTML d'un élément,
                                         * ici permet d'afficher le contenu de outputCart dans l'élément <tbody id="output-customer-carts"> du fichier add_quotation.html.twig
                                         */
                                        document.getElementById('output-customer-carts').innerHTML = outputCart;
                                        document.getElementById('output-customer-orders').innerHTML = outputOrder;
                                        document.getElementById('output-customer-quotations').innerHTML = outputQuotation;
                                    };

                                    /**
                                     * Fonction qui récupère les données dans le json via le path 'quotation_admin_show_customer_details'
                                     */
                                    QuotationModule.getData(
                                        newUrlCustomersDetails,
                                        getCustomerDetails,
                                        null,
                                        true,
                                        []
                                    );

                                    // Ici, on récupère la class 'd-none' de l'élément id 'js-output-customer-details' et on la remplace par 'd-block'
                                    document.getElementById('js-output-customer-details').classList.replace('d-none', 'd-block');
                                });
                            });
                        }
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
