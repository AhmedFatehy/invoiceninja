/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/clients/payment_methods/authorize-authorize-card.js":
/*!**************************************************************************!*\
  !*** ./resources/js/clients/payment_methods/authorize-authorize-card.js ***!
  \**************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */
var AuthorizeAuthorizeCard = /*#__PURE__*/function () {
  function AuthorizeAuthorizeCard(publicKey, loginId) {
    _classCallCheck(this, AuthorizeAuthorizeCard);

    this.publicKey = publicKey;
    this.loginId = loginId;
    this.cardHolderName = document.getElementById("cardholder_name");
    this.cardButton = document.getElementById("card_button");
  }

  _createClass(AuthorizeAuthorizeCard, [{
    key: "handleAuthorization",
    value: function handleAuthorization() {
      var authData = {};
      authData.clientKey = this.publicKey;
      authData.apiLoginID = this.loginId;
      var cardData = {};
      cardData.cardNumber = document.getElementById("card_number").value;
      cardData.month = document.getElementById("expiration_month").value;
      cardData.year = document.getElementById("expiration_year").value;
      cardData.cardCode = document.getElementById("cvv").value;
      var secureData = {};
      secureData.authData = authData;
      secureData.cardData = cardData; // If using banking information instead of card information,
      // send the bankData object instead of the cardData object.
      //
      // secureData.bankData = bankData;

      Accept.dispatchData(secureData, this.responseHandler);
      return false;
    }
  }, {
    key: "responseHandler",
    value: function responseHandler(response) {
      if (response.messages.resultCode === "Error") {
        var i = 0;

        while (i < response.messages.message.length) {
          console.log(response.messages.message[i].code + ": " + response.messages.message[i].text);
          i = i + 1;
        }
      } else if (response.messages.resultCode === "Ok") {
        document.getElementById("dataDescriptor").value = response.opaqueData.dataDescriptor;
        document.getElementById("dataValue").value = response.opaqueData.dataValue;
        document.getElementById("server_response").submit();
      }

      return false;
    }
  }, {
    key: "handle",
    value: function handle() {
      var _this = this;

      this.cardButton.addEventListener("click", function () {
        _this.handleAuthorization();
      });
      return this;
    }
  }]);

  return AuthorizeAuthorizeCard;
}();

var publicKey = document.querySelector('meta[name="authorize-public-key"]').content;
var loginId = document.querySelector('meta[name="authorize-login-id"]').content;
/** @handle */

new AuthorizeAuthorizeCard(publicKey, loginId).handle();

/***/ }),

/***/ 2:
/*!********************************************************************************!*\
  !*** multi ./resources/js/clients/payment_methods/authorize-authorize-card.js ***!
  \********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /home/david/Development/invoiceninja/resources/js/clients/payment_methods/authorize-authorize-card.js */"./resources/js/clients/payment_methods/authorize-authorize-card.js");


/***/ })

/******/ });