const regex = /\d+(?=\/ajax)/;
const DOM = {
    currentElement: null
};

var customerList = document.getElementById('quotation_customerId');
var cartList = document.getElementById('quotation_cartProductId');

customerList.addEventListener('change', function (Event) {
    DOM.currentElement = Event.currentTarget;


    var cartJson = document.getElementById('js-data');      // Récupère l'élement html
    var url = cartJson.dataset.source;                               // Récupère la valeur  de l'attribut data-source
    var newUrl = url.replace(regex, Event.currentTarget.value);      // Remplace l'id par défaut par l'id du customer selectionné
                                                                     // this = Event.currentTarget

    var firstCustomerValue = customerList.firstChild.value = 0;

    fetch(newUrl)                                                                                       // Prend en paramètre l'url

        .then(function (response) {                                                           // Trouve l'élément
            return response.json();
        })

        .then(function (data) {                                                                        // Donne les éléments à afficher
                let count = 0;
                let precedentOptions = document.querySelectorAll('[data-customer]');           // Get all precedent options

                if (precedentOptions.length > 0) {                                                     // Remove all precedent options
                    precedentOptions.forEach(function (option) {
                        option.remove();
                    });
                }

                if (data.length === 0) {
                    cartList[count] = new Option('Sélectionner un panier');
                    console.log(cartList);

                    if (customerList.value > 0) {
                        cartList[count] = new Option('Aucun panier trouvé');
                    }
                } else {
                    for (var option in data) {
                        cartList[count] = new Option(data[count].id_cart + ' - ' + data[count].date_cart, data[count].id_cart);
                        cartList[count].setAttribute('data-customer', data[count].id_customer);
                        count++;
                    }
                }

                console.log('customer value ' + customerList.value)
            }
        )

        .catch(function (error) {
            console.log(error);
        })
})
;