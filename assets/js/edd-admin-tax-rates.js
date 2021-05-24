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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/settings/tax-rates/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/settings/tax-rates/collections/tax-rates.js":
/*!*********************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/collections/tax-rates.js ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../models/tax-rate.js */ "./assets/js/admin/settings/tax-rates/models/tax-rate.js");
/* global Backbone */

/**
 * Internal dependencies.
 */

/**
 * A collection of multiple tax rates.
 */

var TaxRates = Backbone.Collection.extend({
  // Map the model.
  model: _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_0__["default"],

  /**
   * Set initial state.
   */
  initialize: function initialize() {
    this.showAll = false;
    this.selected = [];
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TaxRates);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/index.js":
/*!*****************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/index.js ***!
  \*****************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./models/tax-rate.js */ "./assets/js/admin/settings/tax-rates/models/tax-rate.js");
/* harmony import */ var _collections_tax_rates_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./collections/tax-rates.js */ "./assets/js/admin/settings/tax-rates/collections/tax-rates.js");
/* harmony import */ var _views_manager_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./views/manager.js */ "./assets/js/admin/settings/tax-rates/views/manager.js");
/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* global _, eddTaxRates */

/**
 * Internal dependencies.
 */




/**
 * DOM ready.
 */

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_3__["jQueryReady"])(function () {
  // Show notice if taxes are not enabled.
  var noticeEl = document.getElementById('edd-tax-disabled-notice');

  if (noticeEl) {
    noticeEl.classList.add('notice');
    noticeEl.classList.add('notice-warning');
  } // Start manager with a blank collection.


  var manager = new _views_manager_js__WEBPACK_IMPORTED_MODULE_2__["default"]({
    collection: new _collections_tax_rates_js__WEBPACK_IMPORTED_MODULE_1__["default"]()
  });
  var rates = []; // Normalize rate data.

  _.each(eddTaxRates.rates, function (rate) {
    return rates.push({
      id: rate.id,
      country: rate.name,
      region: rate.description,
      global: 'country' === rate.scope,
      amount: rate.amount,
      status: rate.status
    });
  }); // Add initial rates.


  manager.collection.set(rates, {
    silent: true
  }); // Render manager.

  manager.render();
});

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/models/tax-rate.js":
/*!***************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/models/tax-rate.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* global Backbone */

/**
 * Model a tax rate.
 */
var TaxRate = Backbone.Model.extend({
  defaults: {
    id: '',
    country: '',
    region: '',
    global: true,
    amount: 0,
    status: 'active',
    unsaved: false,
    selected: false
  },

  /**
   * Format a rate amount (adds a %)
   *
   * @todo This should support dynamic decimal types.
   */
  formattedAmount: function formattedAmount() {
    var amount = 0;

    if (this.get('amount')) {
      amount = parseFloat(this.get('amount')).toFixed(2);
    }

    return "".concat(amount, "%");
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TaxRate);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/bulk-actions.js":
/*!******************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/bulk-actions.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* global wp, _ */

/**
 * Apply bulk actions.
 */
var BulkActions = wp.Backbone.View.extend({
  // See https://codex.wordpress.org/Javascript_Reference/wp.template
  template: wp.template('edd-admin-tax-rates-table-bulk-actions'),
  // Watch events.
  events: {
    'click .edd-admin-tax-rates-table-filter': 'filter',
    'change .edd-admin-tax-rates-table-hide input': 'showHide'
  },

  /**
   * Bulk actions for selected items.
   *
   * Currently only supports changing the status.
   *
   * @param {Object} event Event.
   */
  filter: function filter(event) {
    var _this = this;

    event.preventDefault(); // @hack - need to access the DOM directly here because the dropdown is not tied to the button event.

    var status = document.getElementById('edd-admin-tax-rates-table-bulk-actions');

    _.each(this.collection.selected, function (cid) {
      var model = _this.collection.get({
        cid: cid
      });

      model.set('status', status.value);
    });

    this.collection.trigger('filtered');
  },

  /**
   * Toggle show active/inactive rates.
   *
   * @param {Object} event Event.
   */
  showHide: function showHide(event) {
    this.collection.showAll = event.target.checked; // @hack -- shouldn't access this table directly.

    document.getElementById('edd_tax_rates').classList.toggle('has-inactive', this.collection.showAll);
    this.collection.trigger('filtered');
  }
});
/* harmony default export */ __webpack_exports__["default"] = (BulkActions);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/manager.js":
/*!*************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/manager.js ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _table_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./table.js */ "./assets/js/admin/settings/tax-rates/views/table.js");
/* harmony import */ var _bulk_actions_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./bulk-actions.js */ "./assets/js/admin/settings/tax-rates/views/bulk-actions.js");
/* global wp */

/**
 * Internal dependencies.
 */


/**
 * Manage tax rates.
 */

var Manager = wp.Backbone.View.extend({
  // Append to this element.
  el: '#edd-admin-tax-rates',

  /**
   * Set bind changes to collection.
   */
  initialize: function initialize() {
    this.listenTo(this.collection, 'add change', this.makeDirty); // Clear unload confirmation when submitting parent form.

    document.querySelector('.edd-settings-form #submit').addEventListener('click', this.makeClean);
  },

  /**
   * Output the manager.
   */
  render: function render() {
    this.views.add(new _bulk_actions_js__WEBPACK_IMPORTED_MODULE_1__["default"]({
      collection: this.collection
    }));
    this.views.add(new _table_js__WEBPACK_IMPORTED_MODULE_0__["default"]({
      collection: this.collection
    }));
  },

  /**
   * Collection has changed so warn the user before exiting.
   */
  makeDirty: function makeDirty() {
    window.onbeforeunload = this.confirmUnload;
  },

  /**
   * When submitting the main form remove the dirty check.
   */
  makeClean: function makeClean() {
    window.onbeforeunload = null;
  },

  /**
   * Confirm page unload.
   *
   * @param {Object} event Close event.
   */
  confirmUnload: function confirmUnload(event) {
    event.preventDefault();
    return '';
  }
});
/* harmony default export */ __webpack_exports__["default"] = (Manager);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/region-field.js":
/*!******************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/region-field.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/chosen.js */ "./assets/js/utils/chosen.js");
/* global wp, _ */

/**
 * Internal dependencies.
 */

var RegionField = wp.Backbone.View.extend({
  /**
   * Bind passed arguments.
   *
   * @param {Object} options Extra options passed.
   */
  initialize: function initialize(options) {
    _.extend(this, options);
  },

  /**
   * Create a list of options.
   */
  render: function render() {
    if (this.global) {
      return;
    }

    if ('nostates' === this.states) {
      this.setElement('<input type="text" id="tax_rate_region" />');
    } else {
      this.$el.html(this.states);
      this.$el.find('select').each(function () {
        var el = $(this);
        el.chosen(Object(utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__["getChosenVars"])(el));
      });
    }
  }
});
/* harmony default export */ __webpack_exports__["default"] = (RegionField);
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table-add.js":
/*!***************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table-add.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../models/tax-rate.js */ "./assets/js/admin/settings/tax-rates/models/tax-rate.js");
/* harmony import */ var _views_region_field_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./../views/region-field.js */ "./assets/js/admin/settings/tax-rates/views/region-field.js");
/* harmony import */ var utils_chosen_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! utils/chosen.js */ "./assets/js/utils/chosen.js");
/* global wp */

/**
 * Internal dependencies.
 */



/**
 * Add a new rate "form".
 */

var TableAdd = wp.Backbone.View.extend({
  // Use <tfoot>
  tagName: 'tfoot',
  // Set class.
  className: 'add-new',
  // See https://codex.wordpress.org/Javascript_Reference/wp.template
  template: wp.template('edd-admin-tax-rates-table-add'),
  // Watch events.
  events: {
    'click button': 'addTaxRate',
    'keypress': 'maybeAddTaxRate',
    'change #tax_rate_country': 'setCountry',
    // Can be select or input.
    'keyup #tax_rate_region': 'setRegion',
    'change #tax_rate_region': 'setRegion',
    'change input[type="checkbox"]': 'setGlobal',
    // Can be click increase or keyboard.
    'keyup #tax_rate_amount': 'setAmount',
    'change #tax_rate_amount': 'setAmount'
  },

  /**
   * Set initial state and bind changes to model.
   */
  initialize: function initialize() {
    this.model = new _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_0__["default"]({
      global: true,
      unsaved: true
    });
    this.listenTo(this.model, 'change:country', this.updateRegion);
    this.listenTo(this.model, 'change:global', this.updateRegion);
  },

  /**
   * Render. Only overwritten so we can reinit chosen once cleared.
   */
  render: function render() {
    wp.Backbone.View.prototype.render.apply(this, arguments);
    this.$el.find('select').each(function () {
      var el = $(this);
      el.chosen(Object(utils_chosen_js__WEBPACK_IMPORTED_MODULE_2__["getChosenVars"])(el));
    });
    return this;
  },

  /**
   * Show a list of states or an input field.
   */
  updateRegion: function updateRegion() {
    var self = this;
    var data = {
      action: 'edd_get_shop_states',
      country: this.model.get('country'),
      nonce: eddTaxRates.nonce,
      field_name: 'tax_rate_region'
    };
    $.post(ajaxurl, data, function (response) {
      self.views.set('#tax_rate_region_wrapper', new _views_region_field_js__WEBPACK_IMPORTED_MODULE_1__["default"]({
        states: response,
        global: self.model.get('global')
      }));
    });
  },

  /**
   * Set a country value.
   *
   * @param {Object} event Event.
   */
  setCountry: function setCountry(event) {
    this.model.set('country', event.target.options[event.target.selectedIndex].value);
  },

  /**
   * Set a region value.
   *
   * @param {Object} event Event.
   */
  setRegion: function setRegion(event) {
    var value = false;

    if (event.target.value) {
      value = event.target.value;
    } else {
      value = event.target.options[event.target.selectedIndex].value;
    }

    this.model.set('region', value);
  },

  /**
   * Set a global scope.
   *
   * @param {Object} event Event.
   */
  setGlobal: function setGlobal(event) {
    this.model.set('global', event.target.checked);
    this.model.set('region', '');
  },

  /**
   * Set an amount value.
   *
   * @param {Object} event Event.
   */
  setAmount: function setAmount(event) {
    this.model.set('amount', event.target.value);
  },

  /**
   * Monitors keyepress for "Enter" key.
   *
   * We cannot use the `submit` event because we cannot nest <form>
   * elements inside the settings API.
   *
   * @param {Object} event Keypress event.
   */
  maybeAddTaxRate: function maybeAddTaxRate(event) {
    if (13 !== event.keyCode) {
      return;
    }

    this.addTaxRate(event);
  },

  /**
   * Add a single rate when the "form" is submitted.
   *
   * @param {Object} event Event.
   */
  addTaxRate: function addTaxRate(event) {
    event.preventDefault();
    var _eddTaxRates = eddTaxRates,
        i18n = _eddTaxRates.i18n;

    if (!this.model.get('country')) {
      alert(i18n.emptyCountry);
      return;
    }

    var existingCountryWide = this.collection.where({
      region: this.model.get('region'),
      country: this.model.get('country'),
      global: '' === this.model.get('region'),
      status: 'active'
    });

    if (existingCountryWide.length > 0) {
      var regionString = '' === this.model.get('region') ? '' : ': ' + this.model.get('region');
      var taxRateString = this.model.get('country') + regionString;
      alert(i18n.duplicateRate.replace('%s', "\"".concat(taxRateString, "\"")));
      return;
    }

    if (this.model.get('amount') <= 0) {
      alert(i18n.emptyTax);
      return;
    } // Merge cid as ID to make this a unique model.


    this.collection.add(_.extend(this.model.attributes, {
      id: this.model.cid
    }));
    this.render();
    this.initialize();
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TableAdd);
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table-meta.js":
/*!****************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table-meta.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* global wp, _ */

/**
 * Output a table header and footer.
 */
var TableMeta = wp.Backbone.View.extend({
  // See https://codex.wordpress.org/Javascript_Reference/wp.template
  template: wp.template('edd-admin-tax-rates-table-meta'),
  // Watch events.
  events: {
    'change [type="checkbox"]': 'selectAll'
  },

  /**
   * Select all items in the collection.
   *
   * @param {Object} event Event.
   */
  selectAll: function selectAll(event) {
    var _this = this;

    var checked = event.target.checked;

    _.each(this.collection.models, function (model) {
      // Check individual models.
      model.set('selected', checked); // Add to global selection.

      _this.collection.selected.push(model.cid);
    });
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TableMeta);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table-row-empty.js":
/*!*********************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table-row-empty.js ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* global wp */

/**
 * Empty tax rates table.
 */
var TableRowEmpty = wp.Backbone.View.extend({
  // Insert as a <tr>
  tagName: 'tr',
  // Set class.
  className: 'edd-tax-rate-row edd-tax-rate-row--is-empty',
  // See https://codex.wordpress.org/Javascript_Reference/wp.template
  template: wp.template('edd-admin-tax-rates-table-row-empty')
});
/* harmony default export */ __webpack_exports__["default"] = (TableRowEmpty);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table-row.js":
/*!***************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table-row.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/* global wp, _ */

/**
 * A row inside a table of rates.
 */
var TableRow = wp.Backbone.View.extend({
  // Insert as a <tr>
  tagName: 'tr',
  // Set class.
  className: function className() {
    return 'edd-tax-rate-row edd-tax-rate-row--' + this.model.get('status');
  },
  // See https://codex.wordpress.org/Javascript_Reference/wp.template
  template: wp.template('edd-admin-tax-rates-table-row'),
  // Watch events.
  events: {
    'click .remove': 'removeRow',
    'click .activate': 'activateRow',
    'click .deactivate': 'deactivateRow',
    'change [type="checkbox"]': 'selectRow'
  },

  /**
   * Bind model to view.
   */
  initialize: function initialize() {
    this.listenTo(this.model, 'change', this.render);
  },

  /**
   * Render
   */
  render: function render() {
    this.$el.html(this.template(_objectSpread(_objectSpread({}, this.model.toJSON()), {}, {
      formattedAmount: this.model.formattedAmount()
    }))); // Ensure the wrapper class has the new name.

    this.$el.attr('class', _.result(this, 'className'));
  },

  /**
   * Remove a rate (can only be done if it has not been saved to the database).
   *
   * Don't use this.model.destroy() to avoid sending a DELETE request.
   *
   * @param {Object} event Event.
   */
  removeRow: function removeRow(event) {
    event.preventDefault();
    this.collection.remove(this.model);
  },

  /**
   * Activate a rate.
   *
   * @param {Object} event Event.
   */
  activateRow: function activateRow(event) {
    event.preventDefault();
    var _eddTaxRates = eddTaxRates,
        i18n = _eddTaxRates.i18n;
    var existingCountryWide = this.collection.where({
      region: this.model.get('region'),
      country: this.model.get('country'),
      global: '' === this.model.get('region'),
      status: 'active'
    });

    if (existingCountryWide.length > 0) {
      var regionString = '' === this.model.get('region') ? '' : ': ' + this.model.get('region');
      var taxRateString = this.model.get('country') + regionString;
      alert(i18n.duplicateRate.replace('%s', "\"".concat(taxRateString, "\"")));
      return;
    }

    this.model.set('status', 'active');
  },

  /**
   * Deactivate a rate.
   *
   * @param {Object} event Event.
   */
  deactivateRow: function deactivateRow(event) {
    event.preventDefault();
    this.model.set('status', 'inactive');
  },

  /**
   * Select or deselect for bulk actions.
   *
   * @param {Object} event Event.
   */
  selectRow: function selectRow(event) {
    var _this = this;

    var checked = event.target.checked;

    if (!checked) {
      this.collection.selected = _.reject(this.collection.selected, function (cid) {
        return cid === _this.model.cid;
      });
    } else {
      this.collection.selected.push(this.model.cid);
    }
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TableRow);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table-rows.js":
/*!****************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table-rows.js ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _table_row_empty_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./table-row-empty.js */ "./assets/js/admin/settings/tax-rates/views/table-row-empty.js");
/* harmony import */ var _table_row_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./table-row.js */ "./assets/js/admin/settings/tax-rates/views/table-row.js");
/* global wp, _ */

/**
 * Internal dependencies.
 */


/**
 * A bunch of rows inside a table of rates.
 */

var TableRows = wp.Backbone.View.extend({
  // Insert as a <tbody>
  tagName: 'tbody',

  /**
   * Bind events to collection.
   */
  initialize: function initialize() {
    this.listenTo(this.collection, 'add', this.render);
    this.listenTo(this.collection, 'remove', this.render);
    this.listenTo(this.collection, 'filtered change', this.filtered);
  },

  /**
   * Render a collection of rows.
   */
  render: function render() {
    var _this = this;

    // Clear to handle sorting.
    this.views.remove(); // Show empty placeholder.

    if (0 === this.collection.models.length) {
      return this.views.add(new _table_row_empty_js__WEBPACK_IMPORTED_MODULE_0__["default"]());
    } // Add items.


    _.each(this.collection.models, function (rate) {
      _this.views.add(new _table_row_js__WEBPACK_IMPORTED_MODULE_1__["default"]({
        collection: _this.collection,
        model: rate
      }));
    });
  },

  /**
   * Show an empty state if all items are deactivated.
   */
  filtered: function filtered() {
    var disabledRates = this.collection.where({
      status: 'inactive'
    }); // Check if all rows are invisible, and show the "No Items" row if so

    if (disabledRates.length === this.collection.models.length && !this.collection.showAll) {
      this.views.add(new _table_row_empty_js__WEBPACK_IMPORTED_MODULE_0__["default"]()); // Possibly re-render the view
    } else {
      this.render();
    }
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TableRows);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table.js":
/*!***********************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _table_meta_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./table-meta.js */ "./assets/js/admin/settings/tax-rates/views/table-meta.js");
/* harmony import */ var _table_rows_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./table-rows.js */ "./assets/js/admin/settings/tax-rates/views/table-rows.js");
/* harmony import */ var _table_add_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./table-add.js */ "./assets/js/admin/settings/tax-rates/views/table-add.js");
/* global wp */

/**
 * Internal dependencies.
 */



/**
 * Manage the tax rate rows in a table.
 */

var Table = wp.Backbone.View.extend({
  // Render as a <table> tag.
  tagName: 'table',
  // Set class.
  className: 'wp-list-table widefat fixed tax-rates',
  // Set ID.
  id: 'edd_tax_rates',

  /**
   * Output a table with a header, body, and footer.
   */
  render: function render() {
    this.views.add(new _table_meta_js__WEBPACK_IMPORTED_MODULE_0__["default"]({
      tagName: 'thead',
      collection: this.collection
    }));
    this.views.add(new _table_rows_js__WEBPACK_IMPORTED_MODULE_1__["default"]({
      collection: this.collection
    }));
    this.views.add(new _table_add_js__WEBPACK_IMPORTED_MODULE_2__["default"]({
      collection: this.collection
    }));
    this.views.add(new _table_meta_js__WEBPACK_IMPORTED_MODULE_0__["default"]({
      tagName: 'tfoot',
      collection: this.collection
    })); // Trigger the `filtered` action to show/hide rows accordingly

    this.collection.trigger('filtered');
  }
});
/* harmony default export */ __webpack_exports__["default"] = (Table);

/***/ }),

/***/ "./assets/js/utils/chosen.js":
/*!***********************************!*\
  !*** ./assets/js/utils/chosen.js ***!
  \***********************************/
/*! exports provided: chosenVars, getChosenVars */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "chosenVars", function() { return chosenVars; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getChosenVars", function() { return getChosenVars; });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/* global jQuery, edd_vars */
var chosenVars = {
  disable_search_threshold: 13,
  search_contains: true,
  inherit_select_classes: true,
  single_backstroke_delete: false,
  placeholder_text_single: edd_vars.one_option,
  placeholder_text_multiple: edd_vars.one_or_more_option,
  no_results_text: edd_vars.no_results_text
};
/**
 * Determine the variables used to initialie Chosen on an element.
 *
 * @param {Object} el select element.
 * @return {Object} Variables for Chosen.
 */

var getChosenVars = function getChosenVars(el) {
  if (!el instanceof jQuery) {
    el = jQuery(el);
  }

  var inputVars = chosenVars; // Ensure <select data-search-type="download"> or similar can use search always.
  // These types of fields start with no options and are updated via AJAX.

  if (el.data('search-type')) {
    delete inputVars.disable_search_threshold;
  }

  return _objectSpread(_objectSpread({}, inputVars), {}, {
    width: el.css('width')
  });
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/utils/jquery.js":
/*!***********************************!*\
  !*** ./assets/js/utils/jquery.js ***!
  \***********************************/
/*! exports provided: jQueryReady */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "jQueryReady", function() { return jQueryReady; });
/* global jQuery */

/**
 * Safe wrapper for jQuery DOM ready.
 *
 * This should be used only when a script requires the use of jQuery.
 *
 * @param {Function} callback Function to call when ready.
 */
var jQueryReady = function jQueryReady(callback) {
  (function ($) {
    $(callback);
  })(jQuery);
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/defineProperty.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/defineProperty.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

module.exports = _defineProperty;

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-tax-rates.js.map