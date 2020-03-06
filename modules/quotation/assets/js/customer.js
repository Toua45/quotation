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
            console.log(data)
        })
        .catch(function (error) {
            console.log(error);
        });
});
var substringMatcher = function (strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;
        // an array that will be populated with substring matches
        matches = [];
        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');
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
var data_test = ["Anonymous Anonymous","John DOE","Lionel Delamare"];
$('#the-basics .linked-select').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    },
    {
        name: 'customers',
        source: substringMatcher(data_test)
    });
