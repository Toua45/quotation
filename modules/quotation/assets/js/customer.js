export const QuotationModule = {
    DOM: {
        currentElement: null,
        urlCustomers: document.getElementById('customers').dataset.customers.replace(/\?(?=\d)(\w|\W)+/g, ''),
        customers: null,
    },

    customerList: function () {
        return document.getElementById('quotation_customerId')
    },

    getData: function (url, callback, path = null, dataFetch = false, autocomplete = []) {
        window.addEventListener('DOMContentLoaded', () => {
            console.log(url);
            fetch(url).then(response => response.json()).then(data => {
                if (typeof callback === 'function') {
                    console.log("callback works");
                    if (autocomplete.length >= 1) {
                        console.log('call autocompletition');
                        if (typeof autocomplete[0] === 'string') {
                            if (typeof autocomplete[2] === 'number') {
                                callback(autocomplete[0], autocomplete[1], autocomplete[2], data);
                            } else {
                                callback(autocomplete[0], autocomplete[1], 2, data);
                            }
                        } else {
                            console.log('Something went wrong :-(');
                        }
                    } else {
                        if (dataFetch) {
                            if (path !== null) {
                                callback(path, data);
                                console.log('path and data are true');
                            } else {
                                callback(data);
                                console.log('data is true');
                            }
                        } else if (path !== null) {
                            if (dataFetch) {
                                callback(path, data);
                                console.log('path and data are true');
                            } else {
                                callback(path);
                                console.log('path is true');
                            }
                        } else {
                            callback();
                            console.log('no path, no data');
                        }
                    }

                } else {
                    console.log("Callback doesn't work.");
                }
            }).catch(error => console.log(error));
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

    autocompletition: function (selector, name, minLength = 2, dataFetch) {
        $(selector).typeahead({
                hint: true,
                highlight: true,
                minLength: minLength
            },
            {
                name: name,
                source: QuotationModule.substringMatcher(dataFetch)
            })
    }
};
