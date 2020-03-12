export const QuotationModule = {
    // Variables qui vont servir pour le fonctionnement du module (équivaut à des constantes)
    DOM: {
        // Path de data-customer.js
        urlCustomers: document.getElementById('customers').dataset.customers.replace(/\?(?=\d)(\w|\W)+/g, ''),
    },

    getData: function (url, callback, path = null, data) { // Callback => permet de passer une fonction comme paramètre d'une autre fonction
        window.addEventListener('DOMContentLoaded', function (Event) { // DOM : représente l'architecture HTML (body, etc...).
            // DOMContentLoaded => attend que la page soit chargée pour que le getData se déclenche.
            fetch(url)
                .then(function (response) {
                    return response.json();
                })
                .then(function (data = []) {
                    console.log(data);
                    if (typeof callback === 'function') {
                        if (data) {
                            console.log("Params = true");
                            callback(data);
                        }
                        if (path !== null) {
                            console.log("Path works");
                            callback(path);
                        } else {
                            callback();
                            console.log('Params = false');
                        }
                    } else {
                        console.log("Callback doesn's work.");
                    }

                })
                .catch(function (error) {
                    console.log(error);
                });
        });
    },

    substringMatcher: function (strs) {
        return function findMatches(q, cb) {
            let matches, substringRegex;
            matches = [];
            let substrRegex = new RegExp(q, 'i');
            $.each(strs, function (i, str) {
                if (substrRegex.test(str)) {
                    matches.push(str);
                }
            });
            cb(matches);
        }
    },

    autocomplete: function (data, selector, name, minLength = 2) {
        $(selector).typeahead({
                hint: true,
                highlight: true,
                minLength: minLength
            },
            {
                name: name,
                source: QuotationModule.substringMatcher(data)
            })
    },
};
