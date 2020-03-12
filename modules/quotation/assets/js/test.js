export const QuotationModule = {
    regex: /\d+(?=\/ajax)/,
    DOM: {
        currentElement: null,
        placeholderClient: 'Sélectionnez le client',
        placeholderCart: 'Sélectionnez le panier'
    },
    customerList: function () { return document.getElementById('quotation_customerId') },
    cartList: function () { return document.getElementById('quotation_cartProductId') },

    customers: function (element) {
        element.addEventListener('keyup', function (Event) {
            QuotationModule.DOM.currentElement = Event.currentTarget;

            if (Event.currentTarget.options[Event.currentTarget.selectedIndex].text === QuotationModule.DOM.placeholderClient) {
                QuotationModule.cartList().options[QuotationModule.cartList().selectedIndex].text = QuotationModule.DOM.placeholderCart;
            }

            let cartJson = document.getElementById('js-data'); // Récupère l'élement html
            let url = cartJson.dataset.source; // Récupère la valeur  de l'attribut data-source
            let newUrl = url.replace(QuotationModule.regex, Event.currentTarget.value); // Remplace l'id par défaut par l'id du customer selectionné

            fetch(newUrl) // Prend en paramètre l'url

                .then(function (response) { // Trouve l'élément
                    return response.json();
                })

                .then(function (data) { // Donne les éléments à afficher
                    let count = 0;

                    let precedentOptions = document.querySelectorAll('[data-customer]'); // Get all precedent options

                    if (precedentOptions.length > 0) { // Remove all precedent options
                        precedentOptions.forEach(option => option.remove());
                    }

                    if (data.length === 0) {
                        QuotationModule.cartList()[count] = new Option('Aucun panier trouvé');
                    } else {
                        for (var option in data) {
                            QuotationModule.cartList()[count] = new Option(data[count].id_cart + ' - ' + data[count].date_cart, data[count].id_cart);
                            QuotationModule.cartList()[count].setAttribute('data-customer', data[count].id_customer);
                            count++;
                        }
                    }
                })
                .catch(function (error) {
                    console.log(error);
                })
        })
    }
};

