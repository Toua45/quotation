// <<<<<<< HEAD
// // export const CustomerModule = {
// //     regex: /\d+(?=\/ajax)/,
// //     DOM: {
// //         placeholderClient: 'Sélectionnez le client',
// //         urlCustomers: document.getElementById('customers').dataset.customers.replace(/[^(/adminLionel/data-customer.js)](\w|\W)+/g, '')
// //     },
// //
// //     customerList: function () { return document.getElementById('quotation_customerId') },
// //
// //     // var customers = [];
// // // var inputCustomer = document.getElementById('quotation_customerId');
// // // console.log(inputCustomer);
// // inputCustomer.addEventListener('keyup', function (Event) {
// //     var customerJson = document.getElementById('js-data');
// //     var url = customerJson.dataset.source;
// //     fetch(url)
// //         .then(function (response) {
// //             return response.json();
// //         })
// //
// //         .then(function (data) {
// //             customers = data;
// //             // autocompletion(dataCustomers.data);
// //             CustomerModule.customersCall();
// //             // console.log(dataCustomers.data)
// //         })
// //
// //         .catch(function (error) {
// //             console.log(error);
// //         });
// //
// // });
// //
// // customersCall: function fetchCustomers() {
// //     fetch(CustomerModule.DOM.urlCustomers).then(response => response.json()).then(customers => console.log(customers));
// // };
// //
// //
// // // console.log(dataCustomers);
// // var substringMatcher = function (strs) {
// =======
// // import $ from 'jquery';
// //
// // export const QuotationCustomerModule = {
// //     DOM: {
// //         currentElement: null,
// //         urlCustomers: document.getElementById('customers').dataset.customers.replace(/[^(/adminToua/data-customer.js)](\w|\W)+/g, '')
// //     },
// //
// // inputCustomer: function () { return document.getElementById('quotation_customerId') },
// //     customers: function (element) {
// //         element.addEventListener('mousedown', function (Event) {
// //             QuotationCustomerModule.DOM.currentElement = Event.currentTarget;
// //
// //     let customerJson = document.getElementById('js-data');
// //     let url = customerJson.dataset.source;
// //     fetch(url)
// //         .then(function (response) {
// //             return response.json();
// //         })
// //         .then(function (data) {
// //             // console.log(data)
// //         })
// //         .catch(function (error) {
// //             console.log(error);
// //         });
// // });
// //
// // customersCall: function fetchCustomers() {
// //     fetch(QuotationModule.DOM.urlCustomers).then(response => response.json()).then(customers => console.log(customers));
// // };
// //
// // substringMatcher: function (strs) {
// >>>>>>> 3ce160e7929981a5ceeee3b271ac25340d9a9813
// //     return function findMatches(q, cb) {
// //         var matches, substringRegex;
// //         // an array that will be populated with substring matches
// //         matches = [];
// //         // regex used to determine if a string contains the substring `q`
// <<<<<<< HEAD
// //         var substrRegex = new RegExp(q, 'i');
// =======
// //         let substrRegex = new RegExp(q, 'i');
// >>>>>>> 3ce160e7929981a5ceeee3b271ac25340d9a9813
// //         // iterate through the pool of strings and for any string that
// //         // contains the substring `q`, add it to the `matches` array
// //         $.each(strs, function (i, str) {
// //             if (substrRegex.test(str)) {
// //                 matches.push(str);
// //             }
// //         });
// //         cb(matches);
// //     };
// <<<<<<< HEAD
// // };
// // // console.log(dataCustomers.data);
// //
// // function autocompletion(customers) {
// //     $('#the-basics .linked-select').typeahead({
// //             hint: true,
// //             highlight: true,
// //             minLength: 1
// //         },
// //         {
// //             name: 'customers',
// //             source: substringMatcher(customers)
// //         });
// // }
// =======
// // }
// // ,
// //
// // $('#the-basics .linked-select').typeahead({
// //         hint: true,
// //         highlight: true,
// //         minLength: 1
// //     },
// //     {
// //         name: 'customers',
// //         source: substringMatcher(data_test)
// //     })
// // }
// // ;
// //
// // // var substringMatcher = function (strs) {
// // //     return function findMatches(q, cb) {
// // //         var matches, substringRegex;
// // //         // an array that will be populated with substring matches
// // //         matches = [];
// // //         // regex used to determine if a string contains the substring `q`
// // //         substrRegex = new RegExp(q, 'i');
// // //         // iterate through the pool of strings and for any string that
// // //         // contains the substring `q`, add it to the `matches` array
// // //         $.each(strs, function (i, str) {
// // //             if (substrRegex.test(str)) {
// // //                 matches.push(str);
// // //             }
// // //         });
// // //         cb(matches);
// // //     };
// // // };
// // // var data_test = ["Anonymous Anonymous","John DOE","Lionel Delamare"];
// // //
// // // $('#the-basics .linked-select').typeahead({
// // //         hint: true,
// // //         highlight: true,
// // //         minLength: 1
// // //     },
// // //     {
// // //         name: 'customers',
// // //         source: substringMatcher(data_test)
// // //     });
// >>>>>>> 3ce160e7929981a5ceeee3b271ac25340d9a9813
