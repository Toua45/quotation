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
        null,
        true,
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
            let modalCustomerInfos = '';

            // Build show customer link based on his id.
            // Exemple: http://localhost:8000/adminToua/index.php/modules/quotation/admin/show/customer/2
            let link = window.location.origin + '/adminToua/index.php/modules/quotation/admin/show/customer/';
            let show = window.location.origin + '/adminToua/index.php/sell/customers/';

            customers.forEach((customer, i) => {

                import('./templates_module').then(mod => {

                    modalCustomerInfos += mod.TemplateModule.modalCustomerInfos
                        .replace(/---id-customer-modal---/, customer.id_customer)
                        .replace(/---id-customer-orders---/, customer.id_customer)
                        .replace(/---id-customer-carts---/, customer.id_customer)
                        .replace(/---id-customer-addresses---/, customer.id_customer)
                        .replace(/---personal-datas---/,
                            mod.TemplateModule.personalData.replace(/---id-customer-modal---/, customer.id_customer))
                        .replace(/---customer-orders---/,
                            mod.TemplateModule.customerOrders.replace(/---id-customer-orders---/, customer.id_customer))
                        .replace(/---customer-carts---/,
                            mod.TemplateModule.customerCarts.replace(/---id-customer-carts---/, customer.id_customer))
                        .replace(/---customer-addresses---/,
                            mod.TemplateModule.customerAddresses.replace(/---id-customer-addresses---/, customer.id_customer));

                    output += mod.TemplateModule.card
                        .replace(/---increment---/, i)
                        .replace(/---lastname---/, customer.lastname)
                        .replace(/---firstname---/, customer.firstname)
                        .replace(/---customer-id---/, customer.id_customer)
                        .replace(/---email---/, customer.email)
                        .replace(/---birthday---/, customer.birthday)
                        .replace(/---id-customer-modal---/, customer.id_customer)
                        .replace(/---link-show-customer---/, link + customer.id_customer)
                        .replace(/---link-show-customer-carts---/, link + customer.id_customer + '/details')
                        .replace(/---id---/, customer.id_customer)
                        .replace(/---id-customer---/, customer.id_customer)
                        .replace(/---modal-customer-infos---/, modalCustomerInfos);


                    if (customers.length - 1 === i) {
                        document.getElementById('js-output-customers').innerHTML = output;

                        let urlCustomerShow = document.querySelector('[data-customershow]').dataset.customershow;
                        let newUrlCustomerShow;

                        if (document.querySelectorAll('button.customer-show') !== null) {
                            // On boucle sur chaque élément link auquel on attache l'évènement clic
                            document.querySelectorAll('button.customer-show').forEach(function (link) {
                                link.addEventListener('click', function (Event) {
                                    newUrlCustomerShow = window.location.origin + urlCustomerShow
                                        .replace(/\d+(?=\?_token)/, link.dataset.idcustomer);

                                    const getCustomerShow = (customer) => {

                                        let addressController = window.location.origin + '/adminLionel/index.php/?controller=AdminAddresses';

                                        let personalData = '';
                                        let tableCustomerOrders = '';
                                        let customerOrders = '';
                                        let tableCustomerCarts = '';
                                        let customerCarts = '';
                                        let tableCustomerAddresses = '';
                                        let customerAddresses = '';

                                        personalData = mod.TemplateModule.personalData
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
                                            .replace(/---badge-newsletter---/, (customer.newsletter === '1' ? 'badge-success' : 'badge-danger'))
                                            .replace(/---icon-newsletter---/, (customer.newsletter === '1' ? 'check' : 'cancel'))
                                            .replace(/---badge-partners---/, (customer.offer_partners === '1' ? 'badge-success' : 'badge-danger'))
                                            .replace(/---icon-partners---/, (customer.offer_partners === '1' ? 'check' : 'cancel'))
                                            .replace(/---badge-is-active---/, (customer.active === '1' ? 'badge-success' : 'badge-danger')).replace(/---icon-is-active---/, (customer['active'] === '1' ? 'check' : 'cancel'))
                                            .replace(/---is-active---/, (customer.active === 1 ? 'Activé' : 'Désactivé'));

                                        for (let order of customer['orders']) {
                                            tableCustomerOrders += mod.TemplateModule.tableCustomerOrders
                                                .replace(/---id-order---/, order.id_order)
                                                .replace(/---date-order---/, order.date_order)
                                                .replace(/---order-payment---/, order.payment)
                                                .replace(/---order-status---/, order.order_status)
                                                .replace(/---nb-products---/, order['nb_products'].nb_products)
                                                .replace(/---order-total-paid---/, order.total_paid + ' €');
                                        }

                                        customerOrders = mod.TemplateModule.customerOrders
                                            .replace(/---nb-orders---/, customer.nb_orders)
                                            .replace(/---table-customer-orders---/, tableCustomerOrders);

                                        for (let cart of customer['carts']) {
                                            tableCustomerCarts += mod.TemplateModule.tableCustomerCarts
                                                .replace(/---id-cart---/, cart.id_cart)
                                                .replace(/---date-cart---/, cart.date_cart)
                                                .replace(/---transporter---/, cart.carrier)
                                                .replace(/---price---/, cart.total_cart + ' €')
                                            ;
                                        }

                                        customerCarts = mod.TemplateModule.customerCarts
                                            .replace(/---nb-carts---/, customer['nb_carts'].nb_carts)
                                            .replace(/---table-customer-carts---/, tableCustomerCarts);

                                        for (let address of customer['addresses']) {
                                            tableCustomerAddresses += mod.TemplateModule.tableCustomerAddresses
                                                .replace(/---company---/, address.company)
                                                .replace(/---firstname---/, address.firstname)
                                                .replace(/---lastname---/, address.lastname)
                                                .replace(/---address---/, address.address)
                                                .replace(/---further-address---/, address.further_address)
                                                .replace(/---postcode---/, address.postcode)
                                                .replace(/---city---/, address.city)
                                                .replace(/---country---/, address.country)
                                                .replace(/---phone-number---/, address.phone);
                                        }

                                        customerAddresses = mod.TemplateModule.customerAddresses
                                            .replace(/---add-address---/, addressController + '&id_customer=' + customer.id_customer + '&addaddress=1')
                                            .replace(/---table-customer-addresses---/, tableCustomerAddresses);

                                        document.getElementById('modal-personal-data-infos_' + customer.id_customer).innerHTML = personalData;
                                        document.getElementById('modal-customer-orders_' + customer.id_customer).innerHTML = customerOrders;
                                        document.getElementById('modal-customer-carts_' + customer.id_customer).innerHTML = customerCarts;
                                        document.getElementById('modal-customer-addresses_' + customer.id_customer).innerHTML = customerAddresses;
                                    };

                                    QuotationModule.getData(
                                        newUrlCustomerShow,
                                        getCustomerShow,
                                        null,
                                        null,
                                        true,
                                        []
                                    );

                                })
                            })
                        }

                        // Initialisation de la variable urlCustomersDetails qui prend l'élément data-customerdetails du fichier add_quotation.html.twig
                        let urlCustomersDetails = document.querySelector('[data-customerdetails]').dataset.customerdetails;
                        let newUrlCustomersDetails;
                        let linkCart = window.location.origin + '/adminLionel/index.php/modules/quotation/admin/show/cart/';
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
                                    document.querySelectorAll('.hidden').forEach(function (card, index) {
                                        card.classList.add('d-none');
                                    });

                                    /*
                                     * window.location.origin renvoie le protocole, le nom d'hôte et le numéro de port d'une URL
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
                                        let selectAddress = '';
                                        let addressSelected = '';

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
                                                    .replace(/---link-show-customer-cart-use---/, linkCart + customer.id_cart);
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
                                                    .replace(/---link-show-customer-cart-use---/, linkCart + customer.id_cart);
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
                                                        .replace(/\d+(?=\?_token)/, link.dataset.idcart);

                                                    const getCustomerCartToUse = (cart) => {
                                                        let picture = window.location.origin + '/img/p/';
                                                        let outputCartToUse = '';
                                                        let outputCartProductsToUse = '';

                                                        for (let product of cart['products']) {
                                                            outputCartProductsToUse += mod.TemplateModule.quotationCartProducts
                                                                .replace(/---picture---/, picture + product.path.join('/') + '/' + product.id_image + '-small_default.jpg')
                                                                .replace(/---productName---/, product.product_name)
                                                                .replace(/---productAttribute---/, product.attributes)
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
                                                        null,
                                                        true,
                                                        []
                                                    );
                                                });
                                            });
                                        }

                                        // on ajoute l'attribut data-idcustomer à l'élément html add-product-to-cart pour récupérer l'id_customer qui nous servira pour la section search product section
                                        document.getElementById('add-product-to-cart').setAttribute('data-idcustomer', data['customer'].id_customer);
                                        // on ajoute l'attribut data-idcustomer à l'élément html add-product-to-cart pour récupérer l'id_customer qui nous servira pour la section search product section
                                        document.getElementById('add-product-to-cart').setAttribute('data-idcart', data.id_last_cart);

                                        /**
                                         * Addresses block
                                         */

                                        let addresses = data['addresses'];

                                        // placeholder, 1st option of the select
                                        selectAddress = mod.TemplateModule.placeholderAddress;

                                        // obtenir la liste des alias d'addresses dans les 2 selects
                                        for (let addressList of addresses) {
                                            selectAddress += mod.TemplateModule.selectAddress
                                                .replace(/---id-address---/, addressList.id_address)
                                                .replace(/---alias---/, addressList.alias)
                                        }

                                        // remplir le block par l'addresse de livraison sélectionnée
                                        if (document.querySelectorAll('.address-delivery') !== null) {
                                            document.querySelectorAll('.address-delivery').forEach(function (link) {
                                                link.addEventListener('change', function (Event) {

                                                    // filter (fonction js de base) permet de récupérer l'objet dont la value de l'option est égale à l'id_address
                                                    for (let address of addresses.filter(address => address.id_address === link.value)) {
                                                        addressSelected = mod.TemplateModule.addressSelected
                                                            .replace(/---firstname---/, address.firstname)
                                                            .replace(/---lastname---/, address.lastname)
                                                            .replace(/---company---/, address.company)
                                                            .replace(/---address---/, address.address)
                                                            .replace(/---further_address---/, address.further_address)
                                                            .replace(/---postcode---/, address.postcode)
                                                            .replace(/---city---/, address.city)
                                                            .replace(/---country---/, address.country)
                                                            .replace(/---phone---/, address.phone);
                                                    }

                                                    document.getElementById('address-delivery-selected').innerHTML = addressSelected;
                                                })
                                            })
                                        }

                                        if (document.querySelectorAll('.address-invoice') !== null) {
                                            document.querySelectorAll('.address-invoice').forEach(function (link) {
                                                link.addEventListener('change', function (Event) {

                                                    for (let address of addresses.filter(address => address.id_address === link.value)) {
                                                        addressSelected = mod.TemplateModule.addressSelected
                                                            .replace(/---firstname---/, address.firstname)
                                                            .replace(/---lastname---/, address.lastname)
                                                            .replace(/---company---/, address.company)
                                                            .replace(/---address---/, address.address)
                                                            .replace(/---further_address---/, address.further_address)
                                                            .replace(/---postcode---/, address.postcode)
                                                            .replace(/---city---/, address.city)
                                                            .replace(/---country---/, address.country)
                                                            .replace(/---phone---/, address.phone);
                                                    }

                                                    document.getElementById('address-invoice-selected').innerHTML = addressSelected;
                                                })
                                            })
                                        }

                                        document.getElementById('address-delivery').innerHTML = selectAddress;
                                        document.getElementById('address-invoice').innerHTML = selectAddress;
                                    };

                                    /*
                                     * Fonction qui récupère les données dans le json via le path 'quotation_admin_show_customer_details'
                                     */

                                    QuotationModule.getData(
                                        newUrlCustomersDetails,
                                        getCustomerDetails,
                                        null,
                                        null,
                                        true,
                                        []
                                    );

                                    // Ici, on récupère la class 'd-none' de l'élément id 'js-output-customer-details' et on la remplace par 'd-block'
                                    document.getElementById('js-output-customer-details').classList.replace('d-none', 'd-block');
                                    document.getElementById('js-output-cart-infos').classList.replace('d-none', 'd-block');
                                    document.getElementById('js-output-address').classList.replace('d-none', 'd-block');
                                });
                            });
                        }

                        // here
                    }
                });
            });
        };

        QuotationModule.getData(
            urlSearchCustomers.replace(/query/, Event.currentTarget.value),
            insertCustomerInDOM,
            null,
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
        null,
        true,
        []
    );

    QuotationModule.getData(
        QuotationModule.getProductsURL(),
        QuotationModule.autocomplete,
        null,
        null,
        true,
        ['#quotation_product_cartId', 'products', 2]
    );

    const getQueryProduct = (Event) => {
        if (typeof parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim()) === "number" &&
            // Number.isNaN() permet de déterminer si la valeur passée en argument est NaN
            !Number.isNaN(parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim()))
        ) {
            // Get route 'quotation_admin_search_attributes_product'
            let urlSearchAttributesProduct = document.getElementById('js-data-product').dataset.sourceattributes;

            // La fonction parseInt() analyse une chaîne de caractère fournie en argument et renvoie un entier exprimé dans une base donnée
            let idProduct = parseInt(Event.currentTarget.value.replace(/[^(\d)+(\s){1}]/, '').trim());
            urlSearchAttributesProduct = window.location.origin + urlSearchAttributesProduct.replace(/\d+(?=\?_token)/, idProduct);
            document.getElementById('js-output-attributes-products');

            const getAttributesProduct = (attributes) => {
                let index = 0;
                let selectProductAttributes = document.getElementById('js-output-attributes-products');
                let quantityInStock = document.getElementById('quantity-in-stock');
                let sectionProductAttributes = document.getElementById('section-attributes-product');
                let formAddProductToCart = document.getElementById('add-product-to-cart');

                // On cherche si l'id_product_attribute = 0
                if (attributes.id_product_attribute === '0') {
                    // On calcule la longueur du select et de ces options
                    if (selectProductAttributes.length > 0) {
                        // Si le tableau existe, on créé des options auquel on attribut la valeur à 0 et on les cache
                        for (let i = 0; i < selectProductAttributes.length; i++) {
                            selectProductAttributes[i] = new Option('', attributes.id_product_attribute, i === 0);
                            selectProductAttributes[i].hidden = true;
                        }
                        // On ajoute l'attribut data-idproduct auquel on affecte l'id_product au form
                        formAddProductToCart.setAttribute('data-idproduct', attributes.id_product);
                    }
                    // Create attribute id_product on form.add-product-to-cart
                    formAddProductToCart.setAttribute('data-idproduct', attributes.id_product);
                    // Create attribute id_product_attribute on form.add-product-to-cart
                    formAddProductToCart.setAttribute('data-idproductattribute', attributes.id_product_attribute);
                    sectionProductAttributes.classList.replace('d-flex', 'd-none'); // Hide select section
                } else {
                    for (let product of attributes) {
                        selectProductAttributes[index] = new Option(product.attributes, product.id_product_attribute, false, false);
                        selectProductAttributes[index].setAttribute('data-instock', product.quantity);
                        selectProductAttributes[index].setAttribute('data-idproduct', product.id_product);

                        // Create attribute id_product on form.add-product-to-cart
                        formAddProductToCart.setAttribute('data-idproduct', product.id_product);
                        // Remove attribute id_product_attribute on form.add-product-to-cart
                        formAddProductToCart.removeAttribute('data-idproductattribute', attributes.id_product_attribute);
                        sectionProductAttributes.classList.replace('d-none', 'd-flex');

                        if (index === 0 || typeof product.attributes === 'undefined') {
                            quantityInStock.innerHTML = product.quantity;
                        }
                        index++;
                    }

                    // Remove attributes not belonging to current product
                    let count = attributes.length !== selectProductAttributes.length ? Math.max(attributes.length, selectProductAttributes.length) : false;

                    if (count) {
                        for (let i = 0; i < count; i++) {
                            if (selectProductAttributes[i].dataset.idproduct !== formAddProductToCart.dataset.idproduct) {
                                selectProductAttributes[i].hidden = true;
                            }
                        }
                    }
                }

                selectProductAttributes.addEventListener('change', Event => {
                    for (let j = 0; j < selectProductAttributes.length; j++) {
                        if (selectProductAttributes[j].value === Event.currentTarget.value) {
                            quantityInStock.innerHTML = selectProductAttributes[j].dataset.instock;
                            break;
                        }
                    }
                });

                import('./templates_module').then(mod => {

                    let urlProductToCart = document.querySelector('[data-customercart]').dataset.customercart;
                    let newUrlProductToCart;

                    document.getElementById('add-product-to-cart').addEventListener('submit', Event => {
                        Event.preventDefault();

                        /*
                         * Insert product to cart on bdd
                         */
                        let id_product_without_attribute = formAddProductToCart.dataset.idproductattribute;
                        let id_prod_attr = document.getElementById('js-output-attributes-products').value;
                        let argsURL = '';

                        // S'il y a des éléments option dans le select alors les paramètres sont affectés par ces valeurs
                        if (selectProductAttributes.length > 0) {
                            argsURL = '/' +
                                formAddProductToCart.dataset.idproduct + '/' + // Get id_product
                                id_prod_attr + '/' + // Get id_product_attribute
                                document.getElementById('product-quantity').value + '/' + // Get quantity
                                formAddProductToCart.dataset.idcustomer + '/' + // Get id_customer
                                formAddProductToCart.dataset.idcart; // Get id_cart
                        } else {
                            argsURL = '/' +
                                formAddProductToCart.dataset.idproduct + '/' + // Get id_product
                                id_product_without_attribute + '/' + // Get id_product_attribute
                                document.getElementById('product-quantity').value + '/' + // Get quantity
                                formAddProductToCart.dataset.idcustomer + '/' + // Get id_customer
                                formAddProductToCart.dataset.idcart; // Get id_cart
                        }

                        let urlPost = Event.currentTarget.dataset.urlpost;
                        const getCustomerLastCart = (cart) => document.getElementById('add-product-to-cart').dataset.idcart = cart.id_cart;

                        QuotationModule.getData(
                            urlPost.replace(/(\/\d+){5}(?=\?_token)/, argsURL),
                            getCustomerLastCart,
                            null,
                            'POST',
                            true,
                            []
                        );

                        /*
                         * show product on cart
                         */
                        let paramsShowCart = formAddProductToCart.dataset.idcart;
                        newUrlProductToCart = window.location.origin + urlProductToCart.replace(/\d+(?=\?_token)/, paramsShowCart);

                        const showProductsOnCart = (cart) => {
                            let picture = window.location.origin + '/img/p/';
                            let outputCartTotal = '';
                            let outputProductOnCart = '';

                            for (let product of cart['products']) {
                                outputProductOnCart += mod.TemplateModule.quotationCartProducts
                                    .replace(/---picture---/, picture + product.path.join('/') + '/' + product.id_image + '-small_default.jpg')
                                    .replace(/---productName---/, product.product_name)
                                    .replace(/---productAttribute---/, product.attributes)
                                    .replace(/---productPrice---/, product.product_price + ' €')
                                    .replace(/---productQuantity---/, product.product_quantity)
                                    .replace(/---totalProduct---/, product.total_product + ' €');
                            }

                            outputCartTotal += mod.TemplateModule.quotationCart
                                .replace(/---totalCart---/, cart['total_cart'] + ' €');

                            document.getElementById('output-cart-products-to-use').innerHTML = outputProductOnCart;
                            document.getElementById('output-cart-to-use').innerHTML = outputCartTotal;
                        };

                        QuotationModule.getData(
                            newUrlProductToCart,
                            showProductsOnCart,
                            null,
                            null,
                            true,
                            []
                        );

                    });

                });
            };

            QuotationModule.getData(
                urlSearchAttributesProduct,
                getAttributesProduct,
                null,
                null,
                true,
                []
            );

            document.getElementById('js-output-product-to-cart').classList.replace('d-none', 'd-block');
        }
    };

    const inputSearchProducts = document.getElementById('quotation_product_cartId');
    ['keyup', 'click'].forEach(event => {
        inputSearchProducts.addEventListener(event, getQueryProduct, false);
    });
}

var current_page = document.getElementById("index_page").dataset.page;

if (window.location.pathname.replace(/.*(?=\/quotation\/admin\/research)/ || /.*(?=\/quotation\/admin\/research\/)/, '') === '/quotation/admin/research' || '/quotation/admin/research/' + current_page) {
    document.getElementById('filter_page').addEventListener('click', Event => {
        Event.preventDefault();
        let form = Event.currentTarget.closest('thead').querySelector('form'); // Get form
        const _url = window.location.origin + '/adminLionel/index.php/modules/quotation/admin/research?';
        const params = {
            tokenSearch: 'quotation_search[_token]=' + document.getElementById('quotation_search__token').value,
            end: 'quotation_search[end]=' + document.getElementById('quotation_search_end').value,
            name: 'quotation_search[name]=' + document.getElementById('quotation_search_name').value,
            reference: 'quotation_search[reference]=' + document.getElementById('quotation_search_reference').value,
            start: 'quotation_search[start]=' + document.getElementById('quotation_search_start').value,
            status: 'quotation_search[status]=' + document.getElementById('quotation_search_status').value,
        };
        const url = Object.values(params).join('&');
        form.method = 'GET';
        form.action = _url + url;
        form.submit();
    });
}

// any SCSS you require will output into a single scss file (app.scss in this case)

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
});
