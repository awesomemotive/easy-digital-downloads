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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/settings/email-tags/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/settings/email-tags/index.js":
/*!******************************************************!*\
  !*** ./assets/js/admin/settings/email-tags/index.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.js */ "./assets/js/admin/settings/email-tags/utils.js");
/* global eddEmailTagsInserter, tb_remove, tb_position, send_to_editor, _, window, document */

/**
 * Internal dependencies.
 */

/**
 * Make tags clickable and send them to the email content (wp_editor()).
 */

function setupEmailTags() {
  // Find all of the buttons.
  var insertButtons = document.querySelectorAll('.edd-email-tags-list-button');
  /**
   * Listen for clicks on tag buttons.
   *
   * @param {object} node Button node.
   */

  _.each(insertButtons, function (node) {
    /**
     * Listen for clicks on tag buttons.
     */
    node.addEventListener('click', function () {
      // Close Thickbox.
      tb_remove();
      window.send_to_editor(node.dataset.to_insert);
    });
  });
}
/**
 * Filter tags.
 */


function filterEmailTags() {
  var filterInput = document.querySelector('.edd-email-tags-filter-search');
  var tagItems = document.querySelectorAll('.edd-email-tags-list-item');
  filterInput.addEventListener('keyup', function (event) {
    var searchTerm = event.target.value;
    var foundTags = Object(_utils_js__WEBPACK_IMPORTED_MODULE_0__["searchItems"])(eddEmailTagsInserter.items, searchTerm);

    _.each(tagItems, function (node) {
      var found = _.findWhere(foundTags, {
        tag: node.dataset.tag
      });

      node.style.display = !found ? 'none' : 'block';
    });
  });
}
/**
 * DOM ready.
 */


document.addEventListener('DOMContentLoaded', function () {
  // Resize Thickbox when media button is clicked.
  var mediaButton = document.querySelector('.edd-email-tags-inserter');
  mediaButton.addEventListener('click', tb_position); // Clickable tags.

  setupEmailTags(); // Filterable tags.

  filterEmailTags();
});

/***/ }),

/***/ "./assets/js/admin/settings/email-tags/utils.js":
/*!******************************************************!*\
  !*** ./assets/js/admin/settings/email-tags/utils.js ***!
  \******************************************************/
/*! exports provided: searchItems, normalizeTerm */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "searchItems", function() { return searchItems; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "normalizeTerm", function() { return normalizeTerm; });
/* global _ */

/**
 * Filters an item list given a search term.
 *
 * @param {Array} items        Item list
 * @param {string} searchTerm  Search term.
 *
 * @return {Array}             Filtered item list.
 */
var searchItems = function searchItems(items, searchTerm) {
  var normalizedSearchTerm = normalizeTerm(searchTerm);

  var matchSearch = function matchSearch(string) {
    return normalizeTerm(string).indexOf(normalizedSearchTerm) !== -1;
  };

  return _.filter(items, function (item) {
    return matchSearch(item.title) || _.some(item.keywords, matchSearch);
  });
};
/**
 * Converts the search term into a normalized term.
 *
 * @param {string} term The search term to normalize.
 *
 * @return {string} The normalized search term.
 */

var normalizeTerm = function normalizeTerm(term) {
  // Lowercase.
  //  Input: "MEDIA"
  term = term.toLowerCase(); // Strip leading and trailing whitespace.
  //  Input: " media "

  term = term.trim();
  return term;
};

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-email-tags.js.map