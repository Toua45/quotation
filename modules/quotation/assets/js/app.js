import 'jquery';
import 'bloodhound.jquery.min.js';
import 'typehead.jquery.min.js';

var url = document.getElementById('js-data');

$(document).ready(function()
{
    var customers = new Bloodhound(
        {
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        // `customers` is an array of state names defined in "The Basics"
        local: customers
        remote: {
            url:url
            filter: function (customers)
            {
                return $.test(customers)
            }
        }
    });

    $('#bloodhound .typeahead').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            name: 'customers',
            source: customers
        })
});


// import * as test from './quotation';
// import {dataCustomers} from '../../../../adminLionel/data-customer';
// // import {QuotationModule} from './test';
// // import {QuotationCustomerModule} from './customer';
// // import $ from 'jquery';
//
// // console.log(dataCustomers.data);
// // QuotationModule.customerList();
// // QuotationModule.customers(QuotationModule.customerList());
//
// // constructs the suggestion engine
//
// const DOM = {
//     currentElement: null,
//     urlCustomers: document.getElementById('customers').dataset.customers.replace(/\?(?=\d)(\w|\W)+/g, ''),
//     customers: function () {
//         let customerTest = [];
//         fetch(DOM.urlCustomers)
//             .then(response => response.json())
//             .then(function (data) {
//                 // console.log(data);
//                 customerTest = data;
//                 // console.log(customerTest)
//             })
//             .catch(function (error) {
//                 console.log(error);
//             });
//         return customerTest;
//     },
// };
//
// var inputCustomer = document.getElementById('quotation_customerId');
//
// inputCustomer.addEventListener('mousedown', function (Event) {
//     var customerJson = document.getElementById('js-data');
//     var url = customerJson.dataset.source;
//     fetch(url)
//         .then(function (response) {
//             return response.json();
//         })
//
//         .then(function (data) {
//             DOM.customers();
//             // customers = data;
//             // console.log(DOM.customers)
//         })
//
//         .catch(function (error) {
//             console.log(error);
//         });
// });
//
// var customers = new Bloodhound({
//     datumTokenizer: Bloodhound.tokenizers.whitespace,
//     queryTokenizer: Bloodhound.tokenizers.whitespace,
//     // `states` is an array of state names defined in "The Basics"
//     local: dataCustomers
// });
//
// $('#bloodhound .typeahead').typeahead({
//         hint: true,
//         highlight: true,
//         minLength: 1
//     },
//     {
//         name: 'customers',
//         source: dataCustomers
//     });

// var customersCall = document.getElementById('customers');

// console.log(customersCall)
// function getCustomers() {
//     let customerTest = [];
//     fetch(DOM.urlCustomers)
//         .then(response => response.json())
//         .then(function (data) {
//             // console.log(data);
//             customerTest = DOM.customers = data;
//             // console.log(customerTest)
//         })
//         .catch(function (error) {
//             console.log(error);
//         });
//     return customerTest;
// }
// console.log(DOM.customers)




// var substringMatcher = function (strs) {
//     return function findMatches(q, cb) {
//         var matches, substringRegex;
//         // an array that will be populated with substring matches
//         matches = [];
//         // regex used to determine if a string contains the substring `q`
//         var substrRegex = new RegExp(q, 'i');
//         // iterate through the pool of strings and for any string that
//         // contains the substring `q`, add it to the `matches` array
//         $.each(strs, function (i, str) {
//             if (substrRegex.test(str)) {
//                 matches.push(str);
//             }
//         });
//         cb(matches);
//     };
// };
//
// $('#the-basics .linked-select').typeahead({
//         hint: true,
//         highlight: true,
//         minLength: 1
//     },
//     {
//         name: 'customers',
//         source: substringMatcher(DOM.customers())
//     });
