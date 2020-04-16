import '../scss/app.scss';
import {QuotationModule} from "./quotation_module";

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
        ['#quotation_customer_customerId', 'customers', 1]
    );

    let urlSearchCustomers = document.querySelector('[data-searchcustomers]').dataset.searchcustomers;


    const getQuery = (Event) => {
        let query = Event.currentTarget.value !== ' ' || Event.currentTarget.value !== '' ?
            Event.currentTarget.value.replace(/\s(?=\w)(\w)+/, '') : false;

        const insertCustomerInDOM = (customers) => {
            let output = '';
            // Build show customer link based on his id.
            // Exemple: http://localhost:8000/admin130mdhxh9/index.php/modules/quotation/admin/show/customer/2
            let link = window.location.origin + '/admin130mdhxh9/index.php/modules/quotation/admin/show/customer/';
            let show = window.location.origin + '/admin130mdhxh9/index.php/sell/customers/';

            customers.forEach((customer, i) => {

                import('./templates_module').then(mod => {
                    output += mod.TemplateModule.card
                        .replace(/---lastname---/, customer.lastname.toUpperCase())
                        .replace(/---firstname---/, customer.firstname)
                        .replace(/---text---/, 'This is a good customer!')
                        .replace(/---id-customer-modal---/, customer.id_customer)
                        .replace(/---modal-customer-infos---/,
                            mod.TemplateModule.modalCustomerInfos
                                .replace(/---id-customer-modal---/, customer.id_customer)
                                .replace(/---personal-datas---/,
                                    mod.TemplateModule.personalData
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
                                        .replace(/---is-active---/, (customer.active === 1 ? 'Activé' : 'Désactivé'))
                                )
                        )
                        .replace(/---id---/, customer.id_customer)
                        .replace(/---link-show-customer-carts---/, link + customer.id_customer + '/details')
                        .replace(/---increment---/, i)
                    ;


                    if (customers.length - 1 === i) {
                        document.getElementById('js-output-customers').innerHTML = output;


                        // Initialisation de la variable urlCustomersDetails qui prend l'élément data-customerdetails du fichier add_quotation.html.twig
                        let urlCustomersDetails = document.querySelector('[data-customerdetails]').dataset.customerdetails;
                        let newUrlCustomersDetails;
                        let linkCart = window.location.origin + '/admin130mdhxh9/index.php/modules/quotation/admin/show/cart/';
                        let urlCart = document.querySelector('[data-customercart]').dataset.customercart;
                        let newUrlCart;

                        // document.querySelectorAll renvoie tous les éléments du document qui correspondent à un sélecteur CSS, ici, tous les éléments a de la class customer-details
                        if (document.querySelectorAll('a.customer-details') !== null) {
                            // On boucle sur chaque élément link auquel on attache l'évènement clic
                            document.querySelectorAll('a.customer-details').forEach(function (link) {
                                link.addEventListener('click', function (Event) {
                                    // Annule le comportement par défaut de l'événement, ici empeche le lien d'ouvrir l'url quotation/admin/show/customer/{id_customer}/details
                                    Event.preventDefault();
                                    // La méthode closest renvoie l'élément parent le plus proche de l'élément courant (ici id customer-card_ est le plus proche de la class hidden)
                                    // La méthode toggle permet de masquer ou d'afficher le paramètre hidden à l'élément class
                                    Event.currentTarget.closest('.hidden').classList.toggle('hidden');
                                    // Pour chaque cards qui aura la class hidden, ces dernières seront en display-none
                                    document.querySelectorAll('.hidden').forEach(function (card,index) {
                                        card.classList.add('d-none');
                                    });

                                    /*
                                     *window.location.origin renvoie le protocole, le nom d'hôte et le numéro de port d'une URL
                                     * (ici en dev http://localhost:8000, en prod, ce sera le nom de domaine)
                                     */
                                    newUrlCustomersDetails = window.location.origin + urlCustomersDetails
                                        // On récupére l'id_customer (par défaut 0 ici) avec le regex et on le remplace par l'id_customer selectionné du lien
                                        .replace(/\d+(?=\/details)/, link.dataset.idcustomer);

                                    const getCustomerDetails = (data) => {
                                        let outputCart = '';
                                        let outputOrder = '';
                                        let outputQuotation = '';
                                        let modalCustomerDetails = '';
                                        let modalCustomerDetailsCart = '';
                                        let modalCustomerOrderDetails = '';
                                        let modalCustomerOrderDetailsCart = '';
                                        let modalCustomerQuotationDetails = '';
                                        let modalCustomerQuotationDetailsCart = '';

                                        /*
                                        * Cart section
                                         */
                                        // L'instruction for...of permet de créer une boucle d'un array qui parcourt un objet itérable
                                        // Attention à l'ordre d'éxécution des boucles, on éxecute dans cartData, ensuite dans modalCartInfos et enfin tableCart
                                        for (let cart of data['carts']) {

                                            for (let product of cart['products']) {

                                                // TemplateModule.cartData correspond à cartData dans le fichier templates_module.js
                                                modalCustomerDetailsCart += mod.TemplateModule.cartData
                                                    .replace(/---productName---/, product.product_name)
                                                    .replace(/---productPrice---/, product.product_price + ' €')
                                                    .replace(/---productQuantity---/, product.product_quantity)
                                                    .replace(/---totalProduct---/, product.total_product + ' €');

                                            }
                                            modalCustomerDetails += mod.TemplateModule.modalCartInfos
                                                .replace(/---id-cart-modal---/, cart.id_cart)
                                                .replace(/---id-cart-link---/, cart.id_cart)
                                                .replace(/---firstname---/, cart.firstname)
                                                .replace(/---lastname---/, cart.lastname)
                                                .replace(/---id-customer---/, cart.id_customer)
                                                .replace(/---id-cart---/, cart.id_cart)
                                                .replace(/---cart-data---/, modalCustomerDetailsCart)
                                                .replace(/---totalCart---/, cart.total_cart + ' €');

                                            // Une fois les boucles effectuées, on vide la modalCustomerDetailsCart
                                            modalCustomerDetailsCart = '';
                                        }

                                        for (let customer of data['carts']) {
                                            if (customer.orders.length === 0) {
                                                outputCart += mod.TemplateModule.tableCart
                                                    .replace(/---cartId---/, customer.id_cart)
                                                    .replace(/---cartDate---/, customer.date_cart)
                                                    .replace(/---totalCart---/, customer.total_cart + ' €')
                                                    .replace(/---id-cart-modal---/, customer.id_cart)
                                                    .replace(/---id---/, customer.id_cart)
                                                    .replace(/---link-show-customer-cart-use---/, linkCart + customer.id_cart);
                                            }
                                        }

                                        document.getElementById('tableCart').insertAdjacentHTML('afterend', modalCustomerDetails);

                                        /*
                                        * Order section
                                         */
                                        for (let cart of data['carts']) {

                                            for (let product of cart['products']) {

                                                // Etant donné que les produits d'un panier sont déjà récupérés, on va réutiliser le template correspondant, ici TemplateModule.cartData
                                                modalCustomerOrderDetailsCart += mod.TemplateModule.cartData
                                                    .replace(/---productName---/, product.product_name)
                                                    .replace(/---productPrice---/, product.product_price + ' €')
                                                    .replace(/---productQuantity---/, product.product_quantity)
                                                    .replace(/---totalProduct---/, product.total_product + ' €');
                                            }

                                            for (let order of cart['orders']) {

                                                modalCustomerOrderDetails += mod.TemplateModule.modalCartOrderInfos
                                                    .replace(/---id-order-modal---/, order.id_order)
                                                    .replace(/---firstname---/, order.firstname)
                                                    .replace(/---lastname---/, order.lastname)
                                                    .replace(/---id-customer---/, order.id_customer)
                                                    .replace(/---address1---/, order.address1)
                                                    .replace(/---address2---/, order.address2)
                                                    .replace(/---postcode---/, order.postcode)
                                                    .replace(/---city---/, order.city)
                                                    .replace(/---id-order---/, order.id_order)
                                                    .replace(/---reference---/, order.order_reference)
                                                    .replace(/---orderStatus---/, order.order_status)
                                                    .replace(/---id-cart---/, order.id_cart)
                                                    .replace(/---totalProducts---/, order.total_products + ' €')
                                                    .replace(/---totalShipping---/, order.total_shipping + ' €')
                                                    .replace(/---totalPaid---/, order.total_paid + ' €')
                                                    .replace(/---order-cart-data---/, modalCustomerOrderDetailsCart);
                                            }

                                            modalCustomerOrderDetailsCart = '';
                                        }

                                        for (let customer of data['orders']) {
                                            if (typeof customer.id_order !== 'undefined') {
                                                outputOrder += mod.TemplateModule.tableOrder
                                                    .replace(/---orderId---/, customer.id_order)
                                                    .replace(/---orderDate---/, customer.date_order)
                                                    .replace(/---totalOrder---/, customer.total_paid + ' €')
                                                    .replace(/---payment---/, customer.payment)
                                                    .replace(/---orderStatus---/, customer.order_status)
                                                    .replace(/---id-order-modal---/, customer.id_order)
                                                    .replace(/---id---/, customer.id_cart)
                                                    .replace(/---link-show-customer-cart-use---/, linkCart+ customer.id_cart);
                                            }
                                        }

                                        document.getElementById('tableOrder').insertAdjacentHTML('afterend', modalCustomerOrderDetails);

                                        /*
                                        * Quotation section
                                         */
                                        for (let cart of data['carts']) {

                                            for (let product of cart['products']) {

                                                modalCustomerQuotationDetailsCart += mod.TemplateModule.cartData
                                                    .replace(/---productName---/, product.product_name)
                                                    .replace(/---productPrice---/, product.product_price + ' €')
                                                    .replace(/---productQuantity---/, product.product_quantity)
                                                    .replace(/---totalProduct---/, product.total_product + ' €');
                                            }

                                            for (let quotation of cart['quotations']) {

                                                modalCustomerQuotationDetails += mod.TemplateModule.modalCartQuotationInfos
                                                    .replace(/---id-quotation-modal---/, quotation.id_quotation)
                                                    .replace(/---firstname---/, cart.firstname)
                                                    .replace(/---lastname---/, cart.lastname)
                                                    .replace(/---id-customer---/, cart.id_customer)
                                                    .replace(/---id-quotation---/, quotation.id_quotation)
                                                    .replace(/---reference---/, quotation.quotation_reference)
                                                    .replace(/---id-cart---/, quotation.id_cart)
                                                    .replace(/---totalQuotation---/, quotation.total_quotation + ' €')
                                                    .replace(/---quotation-cart-data---/, modalCustomerQuotationDetailsCart);
                                            }

                                            modalCustomerQuotationDetailsCart = '';
                                        }

                                        for (let customer of data['response']) {
                                            if (typeof customer.id_quotation !== 'undefined') {
                                                outputQuotation += mod.TemplateModule.tableQuotation
                                                    .replace(/---quotationId---/, customer.id_quotation)
                                                    .replace(/---quotationDate---/, customer.date_quotation)
                                                    .replace(/---totalQuotation---/, customer.total_quotation + ' €')
                                                    .replace(/---id-quotation-modal---/, customer.id_quotation)
                                                    .replace(/---id---/, customer.id_cart)
                                                    .replace(/---link-show-customer-cart-use---/, linkCart+ customer.id_cart);
                                            }
                                        }

                                        document.getElementById('tableQuotation').insertAdjacentHTML('afterend', modalCustomerQuotationDetails);

                                        /**
                                         * La propriété innerHTML définit ou retourne le contenu HTML d'un élément,
                                         * ici permet d'afficher le contenu de outputCart dans l'élément <tbody id="output-customer-carts"> du fichier add_quotation.html.twig
                                         */
                                        document.getElementById('output-customer-carts').innerHTML = outputCart;
                                        document.getElementById('output-customer-orders').innerHTML = outputOrder;
                                        document.getElementById('output-customer-quotations').innerHTML = outputQuotation;

                                        // Implement 'Utiliser' button here to take benefit of table displaying carts, orders and quotations
                                        /*
                                         * cart to use
                                         */
                                        if (document.querySelectorAll('a.customer-cart-to-use') !== null) {
                                            document.querySelectorAll('a.customer-cart-to-use').forEach(function (link) {
                                                link.addEventListener('click', function (Event) {
                                                    Event.preventDefault();

                                                    newUrlCart = window.location.origin + urlCart
                                                        .replace(/\d+/, link.dataset.idcart);

                                                    const getCustomerCartToUse = (cart) => {
                                                        let outputCartToUse = '';
                                                        let outputCartProductsToUse = '';

                                                        for (let product of cart['products']) {

                                                            outputCartProductsToUse += mod.TemplateModule.quotationCartProducts
                                                                .replace(/---productName---/, product.product_name)
                                                                .replace(/---productPrice---/, product.product_price + ' €')
                                                                .replace(/---productQuantity---/, product.product_quantity)
                                                                .replace(/---totalProduct---/, product.total_product + ' €');
                                                        }

                                                        outputCartToUse += mod.TemplateModule.quotationCart
                                                            .replace(/---totalCart---/, cart['total_cart'] + ' €');

                                                        document.getElementById('output-cart-products-to-use').innerHTML = outputCartProductsToUse;
                                                        document.getElementById('output-cart-to-use').innerHTML = outputCartToUse;
                                                    };

                                                    /*
                                                    * Fonction qui récupère les données dans le json via le path 'quotation_admin_show_cart' dans le fichier _cart.html.twig
                                                    */
                                                    QuotationModule.getData(
                                                        newUrlCart,
                                                        getCustomerCartToUse,
                                                        null,
                                                        true,
                                                        []
                                                    );
                                                });
                                            });
                                        }
                                    };

                                    /*
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
                                    document.getElementById('js-output-cart-infos').classList.replace('d-none', 'd-block');

                                });
                            });
                        }
                    }
                });
            });
        };

        // console.log(urlSearchCustomers.replace(/query/, Event.currentTarget.value));
        QuotationModule.getData(
            urlSearchCustomers.replace(/query/, Event.currentTarget.value),
            insertCustomerInDOM,
            null,
            true,
            []
        );
    };

    const inputSearchCustomers = document.getElementById('quotation_customer_customerId');
    ['keyup', 'change'].forEach(event => {
        inputSearchCustomers.addEventListener(event, getQuery, false);

    });

    /*
     *Search product section
     */
    let urlProduct = document.getElementById('js-data-product').dataset.source;

    QuotationModule.getData(
        urlProduct,
        QuotationModule.getData,
        QuotationModule.getProductsURL(),
        false,
        []
    );

    QuotationModule.getData(
        QuotationModule.getProductsURL(),
        QuotationModule.autocomplete,
        null,
        true,
        ['#quotation_product_cartId', 'products', 1]
    );

    // let urlSearchProducts = document.querySelector('[data-searchproducts]').dataset.searchproducts;

    const getQueryProduct = (Event) => {
        if (typeof parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim()) === "number" &&
            !Number.isNaN(parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim()))
        ) {
            let urlSearchAttributesProduct = window.location.origin + '/adminToua/index.php/modules/quotation/admin/show/attributes/product/';
            let id = parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim());
            urlSearchAttributesProduct = urlSearchAttributesProduct + id;
            console.log(urlSearchAttributesProduct)

            const getAttributesProduct = (attributes) => {
                console.log(attributes)
            };

            QuotationModule.getData(
                urlSearchAttributesProduct,
                getAttributesProduct,
                null,
                true,
                []
            );

           // console.log(parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim()));
        }

        // console.log(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim());
        // let queryProduct = Event.currentTarget.value !== ' ' || Event.currentTarget.value !== '' ?
        //     Event.currentTarget.value.replace(/\s(?=\w)(\w)+/, '') : false;
        //
        // const insertProductInDOM = (products) => {
        //     // console.log(products)
        //     let outputProduct = '';
        //     let linkProduct = window.location.origin + '/adminToua/index.php/modules/quotation/admin/show/product/';
        //
        //     // products.forEach((products, i) => {
        //     //     import('./templates_module').then(mod => {
        //     //
        //     //     });
        //     // });
        // };
        //
        // // console.log(urlSearchProducts.replace(/query/, Event.currentTarget.value));
        // QuotationModule.getData(
        //     urlSearchProducts.replace(/query/, Event.currentTarget.value),
        //     insertProductInDOM,
        //     null,
        //     true,
        //     []
        // );
    };


    const inputSearchProducts = document.getElementById('quotation_product_cartId');
    ['keyup', 'change'].forEach(event => {
        inputSearchProducts.addEventListener(event, getQueryProduct, false);
    });

}

// any SCSS you require will output into a single scss file (app.scss in this case)

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
});
