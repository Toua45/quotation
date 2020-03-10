import * as test from './quotation';
import {dataCustomers} from '../../../../adminLionel/data-customer';
// import $ from './jquery';

// CustomerModule.customerList();
// CustomerModule.customers(CustomerModule.customerList());






console.log(dataCustomers.data);
// QuotationModule.customerList();
// QuotationModule.customers(QuotationModule.customerList());


var customers = [];
var inputCustomer = document.getElementById('quotation_customerId');
// console.log(inputCustomer);
inputCustomer.addEventListener('keyup', function (Event) {
    var customerJson = document.getElementById('js-data');
    var url = customerJson.dataset.source;
    fetch(url)
        .then(function (response) {
            return response.json();
        })

        .then(function (data) {
            customers = data;
            // autocompletion(dataCustomers.data);
            CustomerModule.customersCall();
            // console.log(dataCustomers.data)
        })

        .catch(function (error) {
            console.log(error);
        });

});

var customersCall = fetch(CustomerModule.DOM.urlCustomers).then(response => response.json()).then(customers => console.log(customers));

// console.log(dataCustomers);
var substringMatcher = function (strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;
        // an array that will be populated with substring matches
        matches = [];
        // regex used to determine if a string contains the substring `q`
        var substrRegex = new RegExp(q, 'i');
        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function (i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });
        cb(matches);
    };
};
// console.log(dataCustomers.data);

function autocompletion(customers) {
    $('#the-basics .linked-select').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            name: 'customers',
            source: substringMatcher(customers)
        });
}
