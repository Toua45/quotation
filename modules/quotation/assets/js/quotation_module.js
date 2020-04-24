export const QuotationModule = {
    getParamFromURL : (matcher) => window.location.href.match(new RegExp(matcher + '(?=\\?)', 'g')),

    getCustomersURL: function () {
        if (QuotationModule.getParamFromURL('add') !== null && QuotationModule.getParamFromURL('add').length === 1) {
            return document.getElementById('customers').dataset.customers.replace(/\?(?=\d)(\w|\W)+/g, '');
        }
    },

    getProductsURL: function () {
        if (QuotationModule.getParamFromURL('add') !== null && QuotationModule.getParamFromURL('add').length === 1) {
            return document.getElementById('products').dataset.products.replace(/\?(?=\d)(\w|\W)+/g, '');
        }
    },

    getData: function (url, callback, path = null, method = null, dataFetch = false, autocomplete = []) {
        let Mth = method !== null ? method.toUpperCase() : 'GET';
        fetch(url, {method: Mth}).then(response => response.json()).then(data => {
            if (typeof callback === 'function') {
                if (autocomplete.length >= 1) {
                    // autocomplete[0] => correspond au paramètre 'selector' de la fonction 'autocomplete' type=string
                    if (typeof autocomplete[0] === 'string') {
                        // autocomplete[1] => correspond au paramètre 'name' de la fonction 'autocomplete' type=string
                        if (typeof autocomplete[1] === 'string') {
                            if (typeof autocomplete[2] === 'number') {
                                callback(autocomplete[0], autocomplete[1], autocomplete[2], data);
                            } else {
                                callback(autocomplete[0], autocomplete[1], 2, data);
                            }
                        }
                    }
                } else {
                    if (dataFetch) {
                        if (path !== null) {
                            callback(path, data);
                        } else {
                            callback(data);
                        }
                    } else {
                        callback();
                    }
                }
            }
        }).catch(error => console.log(error));
    },

    substringMatcher: function (strs) {
        return function findMatches(q, cb) {
            let matches, substringRegex;
            // Tableau qui récupère les occurences lors de la recherche
            matches = [];
            // Expression régulière utilisée pour déterminer si une chaîne contient la sous-chaîne `q`
            let substrRegex = new RegExp(q, 'i');
            $.each(strs, function (i, str) {
                if (substrRegex.test(str)) {
                    matches.push(str);
                }
            });
            cb(matches);
        }
    },

    autocomplete: function (selector, name, minLength = 2, dataFetch) {
        $(selector).typeahead({
                hint: true,
                highlight: true,
                minLength: minLength
            },
            {
                name: name,
                source: QuotationModule.substringMatcher(dataFetch)
            })
    },
};