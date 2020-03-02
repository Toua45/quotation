//window.alert('Hello wilders !!');

const regex = /\d/;
const options = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};

var customerList = document.getElementById('quotation_customerId');
var cartList = document.getElementById('quotation_cartProductId');

customerList.addEventListener('change', function (Event) {
    var currentElement = Event.currentTarget;

    //récupère l'élement html
    var cartJson = document.getElementById('js-data');
    //récupère la valeur  de l'attribut data-source
    var url = cartJson.dataset.source;
    //remplace l'id par défaut par l'id du customer selectionné
    var newUrl = url.replace(regex, this.value); //this = Event.currentTarget

    //prend en paramètre l'url
    fetch(newUrl)
        //trouve l'élément
        .then(function (response) {
            return response.json();
        })
        //donne les éléments à afficher
        .then(function (data) {
                let count = 0;
            console.log(currentElement)
                for (var option in data) {
                    console.log('-------------------')
                    console.log(customerList.value)
                    if (customerList.value !== Event.currentTarget.value) {
                        console.log('-------------------')
                        console.log(data[count]);

                    } else {

                        console.log('-------------------')
                        console.log(data[count].id_cart)
                        cartList[count] = new Option(data[count].id_cart + ' - ' + data[count].date_cart, data[count].id_cart);
                        count++;

                    }
                }
                console.log(data);
                //console.log(data);
            }
        )

        .catch(function (error) {
            console.log(error);
        })
});




