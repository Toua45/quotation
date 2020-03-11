export const QuotationModule = {
    DOM: {
        currentElement: null,
        urlCustomers: document.getElementById('customers').dataset.customers.replace(/\?(?=\d)(\w|\W)+/g, ''),
        customers: null,
    },

    customerList: function () {
        return document.getElementById('quotation_customerId')
    },

    fetch: function (url, callback) {
        window.addEventListener('DOMContentLoaded', function (Event) {
            QuotationModule.DOM.currentElement = Event.currentTarget;

            fetch(url).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (typeof callback === undefined) {
                    console.log("callback works");
                    callback();
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

    autocompletition: function (customers) {
        $('#quotation_customerId').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: 'customers',
                source: QuotationModule.substringMatcher(customers)
            })
    },

    customers: function () {
        fetch(QuotationModule.DOM.urlCustomers).then(response => response.json()).then(function (data) {
            QuotationModule.autocompletition(data);
        })
            .catch(function (error) {
                console.log(error)
            });
    },
};
