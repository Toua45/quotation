import 'jquery';
import './typeahead/bloodhound.min';
import './typeahead/typeahead.jquery.min';

const DOM = {
    currentElement: null,
    urlCustomers: document.getElementById('customers').dataset.customers.replace(/\?(?=\d)(\w|\W)+/g, ''),
    customers: null,
};

var inputCustomer = document.getElementById('quotation_customerId');

inputCustomer.addEventListener('mouseenter', function (Event) {
    var customerJson = document.getElementById('js-data');
    var url = customerJson.dataset.source;
    fetch(url).then(function (response) {return response.json();}).then(function (data) {
            getCustomers();
        })
        .catch(function (error) {console.log(error);});
});

var substringMatcher = function (strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;
        matches = [];
        var substrRegex = new RegExp(q, 'i');
        $.each(strs, function (i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });
        cb(matches);
    };
};

const autocompletition = function (customers) {
    $('#quotation_customerId').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            name: 'customers',
            source: substringMatcher(customers)
        });
};


function getCustomers() {
    fetch(DOM.urlCustomers).then(response => response.json()).then(function (data) {
            DOM.customers = data;
            autocompletition(data);
        })
        .catch(function (error) {console.log(error)});
}


