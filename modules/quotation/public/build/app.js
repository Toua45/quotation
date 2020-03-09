(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["app"],{

/***/ "../../adminLionel/test.js":
/*!**************************************************************************************!*\
  !*** /home/lionel/Bureau/Aquapure/prestashop_1.7.6.3/prestashop/adminLionel/test.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {



/***/ }),

/***/ "./assets/js/app.js":
/*!**************************!*\
  !*** ./assets/js/app.js ***!
  \**************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _quotation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./quotation */ "./assets/js/quotation.js");
/* harmony import */ var _adminLionel_test__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../adminLionel/test */ "../../adminLionel/test.js");
/* harmony import */ var _adminLionel_test__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_adminLionel_test__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _test__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./test */ "./assets/js/test.js");



console.log(_adminLionel_test__WEBPACK_IMPORTED_MODULE_1__["customers"]);
_test__WEBPACK_IMPORTED_MODULE_2__["QuotationModule"].customerList();
_test__WEBPACK_IMPORTED_MODULE_2__["QuotationModule"].customers(_test__WEBPACK_IMPORTED_MODULE_2__["QuotationModule"].customerList());

/***/ }),

/***/ "./assets/js/quotation.js":
/*!********************************!*\
  !*** ./assets/js/quotation.js ***!
  \********************************/
/*! exports provided: log */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "log", function() { return log; });
function log(value) {
  console.log(value);
}

/***/ }),

/***/ "./assets/js/test.js":
/*!***************************!*\
  !*** ./assets/js/test.js ***!
  \***************************/
/*! exports provided: QuotationModule */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "QuotationModule", function() { return QuotationModule; });
/* harmony import */ var core_js_modules_es_array_for_each__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.for-each */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_to_string__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.to-string */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_promise__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.promise */ "./node_modules/core-js/modules/es.promise.js");
/* harmony import */ var core_js_modules_es_promise__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_promise__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es_regexp_exec__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es.regexp.exec */ "./node_modules/core-js/modules/es.regexp.exec.js");
/* harmony import */ var core_js_modules_es_regexp_exec__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_exec__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var core_js_modules_es_string_replace__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.string.replace */ "./node_modules/core-js/modules/es.string.replace.js");
/* harmony import */ var core_js_modules_es_string_replace__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_replace__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_web_dom_collections_for_each__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/web.dom-collections.for-each */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");
/* harmony import */ var core_js_modules_web_dom_collections_for_each__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_for_each__WEBPACK_IMPORTED_MODULE_5__);






var QuotationModule = {
  regex: /\d+(?=\/ajax)/,
  DOM: {
    currentElement: null,
    placeholderClient: 'Sélectionnez le client',
    placeholderCart: 'Sélectionnez le panier'
  },
  customerList: function customerList() {
    return document.getElementById('quotation_customerId');
  },
  cartList: function cartList() {
    return document.getElementById('quotation_cartProductId');
  },
  customers: function customers(element) {
    element.addEventListener('change', function (Event) {
      QuotationModule.DOM.currentElement = Event.currentTarget;

      if (Event.currentTarget.options[Event.currentTarget.selectedIndex].text === QuotationModule.DOM.placeholderClient) {
        QuotationModule.cartList().options[QuotationModule.cartList().selectedIndex].text = QuotationModule.DOM.placeholderCart;
      }

      var cartJson = document.getElementById('js-data'); // Récupère l'élement html

      var url = cartJson.dataset.source; // Récupère la valeur  de l'attribut data-source

      var newUrl = url.replace(QuotationModule.regex, Event.currentTarget.value); // Remplace l'id par défaut par l'id du customer selectionné

      fetch(newUrl) // Prend en paramètre l'url
      .then(function (response) {
        // Trouve l'élément
        return response.json();
      }).then(function (data) {
        // Donne les éléments à afficher
        var count = 0;
        var precedentOptions = document.querySelectorAll('[data-customer]'); // Get all precedent options

        if (precedentOptions.length > 0) {
          // Remove all precedent options
          precedentOptions.forEach(function (option) {
            return option.remove();
          });
        }

        if (data.length === 0) {
          QuotationModule.cartList()[count] = new Option('Aucun panier trouvé');
        } else {
          for (var option in data) {
            QuotationModule.cartList()[count] = new Option(data[count].id_cart + ' - ' + data[count].date_cart, data[count].id_cart);
            QuotationModule.cartList()[count].setAttribute('data-customer', data[count].id_customer);
            count++;
          }
        }
      })["catch"](function (error) {
        console.log(error);
      });
    });
  }
};

/***/ })

},[["./assets/js/app.js","runtime","vendors~app"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYXBwLmpzIiwid2VicGFjazovLy8uL2Fzc2V0cy9qcy9xdW90YXRpb24uanMiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL3Rlc3QuanMiXSwibmFtZXMiOlsiY29uc29sZSIsImxvZyIsImN1c3RvbWVycyIsIlF1b3RhdGlvbk1vZHVsZSIsImN1c3RvbWVyTGlzdCIsInZhbHVlIiwicmVnZXgiLCJET00iLCJjdXJyZW50RWxlbWVudCIsInBsYWNlaG9sZGVyQ2xpZW50IiwicGxhY2Vob2xkZXJDYXJ0IiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsImNhcnRMaXN0IiwiZWxlbWVudCIsImFkZEV2ZW50TGlzdGVuZXIiLCJFdmVudCIsImN1cnJlbnRUYXJnZXQiLCJvcHRpb25zIiwic2VsZWN0ZWRJbmRleCIsInRleHQiLCJjYXJ0SnNvbiIsInVybCIsImRhdGFzZXQiLCJzb3VyY2UiLCJuZXdVcmwiLCJyZXBsYWNlIiwiZmV0Y2giLCJ0aGVuIiwicmVzcG9uc2UiLCJqc29uIiwiZGF0YSIsImNvdW50IiwicHJlY2VkZW50T3B0aW9ucyIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJsZW5ndGgiLCJmb3JFYWNoIiwib3B0aW9uIiwicmVtb3ZlIiwiT3B0aW9uIiwiaWRfY2FydCIsImRhdGVfY2FydCIsInNldEF0dHJpYnV0ZSIsImlkX2N1c3RvbWVyIiwiZXJyb3IiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFFQUEsT0FBTyxDQUFDQyxHQUFSLENBQVlDLDJEQUFaO0FBQ0FDLHFEQUFlLENBQUNDLFlBQWhCO0FBQ0FELHFEQUFlLENBQUNELFNBQWhCLENBQTBCQyxxREFBZSxDQUFDQyxZQUFoQixFQUExQixFOzs7Ozs7Ozs7Ozs7QUNOQTtBQUFBO0FBQU8sU0FBU0gsR0FBVCxDQUFhSSxLQUFiLEVBQW9CO0FBQ3ZCTCxTQUFP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cEM7QUFDQXJDLDJCQUFlLENBQUNVLFFBQWhCLEdBQTJCbUIsS0FBM0IsRUFBa0NVLFlBQWxDLENBQStDLGVBQS9DLEVBQWdFWCxJQUFJLENBQUNDLEtBQUQsQ0FBSixDQUFZVyxXQUE1RTtBQUNBWCxpQkFBSztBQUNSO0FBQ0o7QUFDSixPQXhCTCxXQXlCVyxVQUFVWSxLQUFWLEVBQWlCO0FBQ3BCNUMsZUFBTyxDQUFDQyxHQUFSLENBQVkyQyxLQUFaO0FBQ0gsT0EzQkw7QUE0QkgsS0F2Q0Q7QUF3Q0g7QUFuRDBCLENBQXhCLEMiLCJmaWxlIjoiYXBwLmpzIiwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0ICogYXMgdGVzdCBmcm9tICcuL3F1b3RhdGlvbic7XG5pbXBvcnQge2N1c3RvbWVyc30gZnJvbSAnLi4vLi4vLi4vLi4vYWRtaW5MaW9uZWwvdGVzdCc7XG5pbXBvcnQge1F1b3RhdGlvbk1vZHVsZX0gZnJvbSAnLi90ZXN0JztcblxuY29uc29sZS5sb2coY3VzdG9tZXJzKTtcblF1b3RhdGlvbk1vZHVsZS5jdXN0b21lckxpc3QoKTtcblF1b3RhdGlvbk1vZHVsZS5jdXN0b21lcnMoUXVvdGF0aW9uTW9kdWxlLmN1c3RvbWVyTGlzdCgpKTtcblxuIiwiZXhwb3J0IGZ1bmN0aW9uIGxvZyh2YWx1ZSkge1xuICAgIGNvbnNvbGUubG9nKHZhbHVlKTtcbn0iLCJleHBvcnQgY29uc3QgUXVvdGF0aW9uTW9kdWxlID0ge1xuICAgIHJlZ2V4OiAvXFxkKyg/PVxcL2FqYXgpLyxcbiAgICBET006IHtcbiAgICAgICAgY3VycmVudEVsZW1lbnQ6IG51bGwsXG4gICAgICAgIHBsYWNlaG9sZGVyQ2xpZW50OiAnU8OpbGVjdGlvbm5leiBsZSBjbGllbnQnLFxuICAgICAgICBwbGFjZWhvbGRlckNhcnQ6ICdTw6lsZWN0aW9ubmV6IGxlIHBhbmllcidcbiAgICB9LFxuICAgIGN1c3RvbWVyTGlzdDogZnVuY3Rpb24gKCkgeyByZXR1cm4gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3F1b3RhdGlvbl9jdXN0b21lcklkJykgfSxcbiAgICBjYXJ0TGlzdDogZnVuY3Rpb24gKCkgeyByZXR1cm4gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3F1b3RhdGlvbl9jYXJ0UHJvZHVjdElkJykgfSxcblxuICAgIGN1c3RvbWVyczogZnVuY3Rpb24gKGVsZW1lbnQpIHtcbiAgICAgICAgZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbiAoRXZlbnQpIHtcbiAgICAgICAgICAgIFF1b3RhdGlvbk1vZHVsZS5ET00uY3VycmVudEVsZW1lbnQgPSBFdmVudC5jdXJyZW50VGFyZ2V0O1xuXG4gICAgICAgICAgICBpZiAoRXZlbnQuY3VycmVudFRhcmdldC5vcHRpb25zW0V2ZW50LmN1cnJlbnRUYXJnZXQuc2VsZWN0ZWRJbmRleF0udGV4dCA9PT0gUXVvdGF0aW9uTW9kdWxlLkRPTS5wbGFjZWhvbGRlckNsaWVudCkge1xuICAgICAgICAgICAgICAgIFF1b3RhdGlvbk1vZHVsZS5jYXJ0TGlzdCgpLm9wdGlvbnNbUXVvdGF0aW9uTW9kdWxlLmNhcnRMaXN0KCkuc2VsZWN0ZWRJbmRleF0udGV4dCA9IFF1b3RhdGlvbk1vZHVsZS5ET00ucGxhY2Vob2xkZXJDYXJ0O1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBsZXQgY2FydEpzb24gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnanMtZGF0YScpOyAvLyBSw6ljdXDDqHJlIGwnw6lsZW1lbnQgaHRtbFxuICAgICAgICAgICAgbGV0IHVybCA9IGNhcnRKc29uLmRhdGFzZXQuc291cmNlOyAvLyBSw6ljdXDDqHJlIGxhIHZhbGV1ciAgZGUgbCdhdHRyaWJ1dCBkYXRhLXNvdXJjZVxuICAgICAgICAgICAgbGV0IG5ld1VybCA9IHVybC5yZXBsYWNlKFF1b3RhdGlvbk1vZHVsZS5yZWdleCwgRXZlbnQuY3VycmVudFRhcmdldC52YWx1ZSk7IC8vIFJlbXBsYWNlIGwnaWQgcGFyIGTDqWZhdXQgcGFyIGwnaWQgZHUgY3VzdG9tZXIgc2VsZWN0aW9ubsOpXG5cbiAgICAgICAgICAgIGZldGNoKG5ld1VybCkgLy8gUHJlbmQgZW4gcGFyYW3DqHRyZSBsJ3VybFxuXG4gICAgICAgICAgICAgICAgLnRoZW4oZnVuY3Rpb24gKHJlc3BvbnNlKSB7IC8vIFRyb3V2ZSBsJ8OpbMOpbWVudFxuICAgICAgICAgICAgICAgICAgICByZXR1cm4gcmVzcG9uc2UuanNvbigpO1xuICAgICAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgICAgICAudGhlbihmdW5jdGlvbiAoZGF0YSkgeyAvLyBEb25uZSBsZXMgw6lsw6ltZW50cyDDoCBhZmZpY2hlclxuICAgICAgICAgICAgICAgICAgICBsZXQgY291bnQgPSAwO1xuXG4gICAgICAgICAgICAgICAgICAgIGxldCBwcmVjZWRlbnRPcHRpb25zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnW2RhdGEtY3VzdG9tZXJdJyk7IC8vIEdldCBhbGwgcHJlY2VkZW50IG9wdGlvbnNcblxuICAgICAgICAgICAgICAgICAgICBpZiAocHJlY2VkZW50T3B0aW9ucy5sZW5ndGggPiAwKSB7IC8vIFJlbW92ZSBhbGwgcHJlY2VkZW50IG9wdGlvbnNcbiAgICAgICAgICAgICAgICAgICAgICAgIHByZWNlZGVudE9wdGlvbnMuZm9yRWFjaChvcHRpb24gPT4gb3B0aW9uLnJlbW92ZSgpKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIGlmIChkYXRhLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgUXVvdGF0aW9uTW9kdWxlLmNhcnRMaXN0KClbY291bnRdID0gbmV3IE9wdGlvbignQXVjdW4gcGFuaWVyIHRyb3V2w6knKTtcbiAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGZvciAodmFyIG9wdGlvbiBpbiBkYXRhKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgUXVvdGF0aW9uTW9kdWxlLmNhcnRMaXN0KClbY291bnRdID0gbmV3IE9wdGlvbihkYXRhW2NvdW50XS5pZF9jYXJ0ICsgJyAtICcgKyBkYXRhW2NvdW50XS5kYXRlX2NhcnQsIGRhdGFbY291bnRdLmlkX2NhcnQpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIFF1b3RhdGlvbk1vZHVsZS5jYXJ0TGlzdCgpW2NvdW50XS5zZXRBdHRyaWJ1dGUoJ2RhdGEtY3VzdG9tZXInLCBkYXRhW2NvdW50XS5pZF9jdXN0b21lcik7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY291bnQrKztcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgICAgLmNhdGNoKGZ1bmN0aW9uIChlcnJvcikge1xuICAgICAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhlcnJvcik7XG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgfSlcbiAgICB9XG59O1xuIl0sInNvdXJjZVJvb3QiOiIifQ==