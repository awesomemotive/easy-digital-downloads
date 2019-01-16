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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
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
/* harmony import */ var core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.function.name */ "./node_modules/core-js/modules/es6.function.name.js");
/* harmony import */ var core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./models/tax-rate.js */ "./assets/js/admin/settings/tax-rates/models/tax-rate.js");
/* harmony import */ var _collections_tax_rates_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./collections/tax-rates.js */ "./assets/js/admin/settings/tax-rates/collections/tax-rates.js");
/* harmony import */ var _views_manager_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./views/manager.js */ "./assets/js/admin/settings/tax-rates/views/manager.js");


/* global _, eddTaxRates */

/**
 * Internal dependencies.
 */



/**
 * DOM ready.
 */

document.addEventListener('DOMContentLoaded', function () {
  // Start manager with a blank collection.
  var manager = new _views_manager_js__WEBPACK_IMPORTED_MODULE_3__["default"]({
    collection: new _collections_tax_rates_js__WEBPACK_IMPORTED_MODULE_2__["default"]()
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
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.array.find */ "./node_modules/core-js/modules/es6.array.find.js");
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_utils_chosen_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/utils/chosen.js */ "./assets/js/utils/chosen.js");


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
      this.$el.find('select').chosen(js_utils_chosen_js__WEBPACK_IMPORTED_MODULE_1__["chosenVars"]);
    }
  }
});
/* harmony default export */ __webpack_exports__["default"] = (RegionField);

/***/ }),

/***/ "./assets/js/admin/settings/tax-rates/views/table-add.js":
/*!***************************************************************!*\
  !*** ./assets/js/admin/settings/tax-rates/views/table-add.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.array.find */ "./node_modules/core-js/modules/es6.array.find.js");
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./../models/tax-rate.js */ "./assets/js/admin/settings/tax-rates/models/tax-rate.js");
/* harmony import */ var _views_region_field_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./../views/region-field.js */ "./assets/js/admin/settings/tax-rates/views/region-field.js");
/* harmony import */ var js_utils_chosen_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! js/utils/chosen.js */ "./assets/js/utils/chosen.js");


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
    this.model = new _models_tax_rate_js__WEBPACK_IMPORTED_MODULE_1__["default"]({
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
    this.$el.html(this.template());
    this.$el.find('select').chosen(js_utils_chosen_js__WEBPACK_IMPORTED_MODULE_3__["chosenVars"]);
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
      self.views.set('#tax_rate_region_wrapper', new _views_region_field_js__WEBPACK_IMPORTED_MODULE_2__["default"]({
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
   * Add a single rate when the "form" is submitted.
   *
   * @param {Object} event Event.
   */
  addTaxRate: function addTaxRate(event) {
    event.preventDefault(); // Merge cid as ID to make this a unique model.

    this.collection.add(_.extend(this.model.attributes, {
      id: this.model.cid
    }));
    this.render();
    this.initialize();
  }
});
/* harmony default export */ __webpack_exports__["default"] = (TableAdd);

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
/* harmony import */ var _babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/objectSpread */ "./node_modules/@babel/runtime/helpers/objectSpread.js");
/* harmony import */ var _babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0__);


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
    this.$el.html(this.template(_babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0___default()({}, this.model.toJSON(), {
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
/*! exports provided: chosenVars */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "chosenVars", function() { return chosenVars; });
/* global edd_vars */
var chosenVars = {
  disable_search_threshold: 13,
  search_contains: true,
  inherit_select_classes: true,
  single_backstroke_delete: false,
  placeholder_text_single: edd_vars.one_option,
  placeholder_text_multiple: edd_vars.one_or_more_option,
  no_results_text: edd_vars.no_results_text
};

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

/***/ "./node_modules/@babel/runtime/helpers/objectSpread.js":
/*!*************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/objectSpread.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var defineProperty = __webpack_require__(/*! ./defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");

function _objectSpread(target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i] != null ? arguments[i] : {};
    var ownKeys = Object.keys(source);

    if (typeof Object.getOwnPropertySymbols === 'function') {
      ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function (sym) {
        return Object.getOwnPropertyDescriptor(source, sym).enumerable;
      }));
    }

    ownKeys.forEach(function (key) {
      defineProperty(target, key, source[key]);
    });
  }

  return target;
}

module.exports = _objectSpread;

/***/ }),

/***/ "./node_modules/core-js/modules/_a-function.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/modules/_a-function.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (it) {
  if (typeof it != 'function') throw TypeError(it + ' is not a function!');
  return it;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_add-to-unscopables.js":
/*!*************************************************************!*\
  !*** ./node_modules/core-js/modules/_add-to-unscopables.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 22.1.3.31 Array.prototype[@@unscopables]
var UNSCOPABLES = __webpack_require__(/*! ./_wks */ "./node_modules/core-js/modules/_wks.js")('unscopables');
var ArrayProto = Array.prototype;
if (ArrayProto[UNSCOPABLES] == undefined) __webpack_require__(/*! ./_hide */ "./node_modules/core-js/modules/_hide.js")(ArrayProto, UNSCOPABLES, {});
module.exports = function (key) {
  ArrayProto[UNSCOPABLES][key] = true;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_an-object.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/modules/_an-object.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ./_is-object */ "./node_modules/core-js/modules/_is-object.js");
module.exports = function (it) {
  if (!isObject(it)) throw TypeError(it + ' is not an object!');
  return it;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_array-methods.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/modules/_array-methods.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 0 -> Array#forEach
// 1 -> Array#map
// 2 -> Array#filter
// 3 -> Array#some
// 4 -> Array#every
// 5 -> Array#find
// 6 -> Array#findIndex
var ctx = __webpack_require__(/*! ./_ctx */ "./node_modules/core-js/modules/_ctx.js");
var IObject = __webpack_require__(/*! ./_iobject */ "./node_modules/core-js/modules/_iobject.js");
var toObject = __webpack_require__(/*! ./_to-object */ "./node_modules/core-js/modules/_to-object.js");
var toLength = __webpack_require__(/*! ./_to-length */ "./node_modules/core-js/modules/_to-length.js");
var asc = __webpack_require__(/*! ./_array-species-create */ "./node_modules/core-js/modules/_array-species-create.js");
module.exports = function (TYPE, $create) {
  var IS_MAP = TYPE == 1;
  var IS_FILTER = TYPE == 2;
  var IS_SOME = TYPE == 3;
  var IS_EVERY = TYPE == 4;
  var IS_FIND_INDEX = TYPE == 6;
  var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
  var create = $create || asc;
  return function ($this, callbackfn, that) {
    var O = toObject($this);
    var self = IObject(O);
    var f = ctx(callbackfn, that, 3);
    var length = toLength(self.length);
    var index = 0;
    var result = IS_MAP ? create($this, length) : IS_FILTER ? create($this, 0) : undefined;
    var val, res;
    for (;length > index; index++) if (NO_HOLES || index in self) {
      val = self[index];
      res = f(val, index, O);
      if (TYPE) {
        if (IS_MAP) result[index] = res;   // map
        else if (res) switch (TYPE) {
          case 3: return true;             // some
          case 5: return val;              // find
          case 6: return index;            // findIndex
          case 2: result.push(val);        // filter
        } else if (IS_EVERY) return false; // every
      }
    }
    return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : result;
  };
};


/***/ }),

/***/ "./node_modules/core-js/modules/_array-species-constructor.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/modules/_array-species-constructor.js ***!
  \********************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ./_is-object */ "./node_modules/core-js/modules/_is-object.js");
var isArray = __webpack_require__(/*! ./_is-array */ "./node_modules/core-js/modules/_is-array.js");
var SPECIES = __webpack_require__(/*! ./_wks */ "./node_modules/core-js/modules/_wks.js")('species');

module.exports = function (original) {
  var C;
  if (isArray(original)) {
    C = original.constructor;
    // cross-realm fallback
    if (typeof C == 'function' && (C === Array || isArray(C.prototype))) C = undefined;
    if (isObject(C)) {
      C = C[SPECIES];
      if (C === null) C = undefined;
    }
  } return C === undefined ? Array : C;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_array-species-create.js":
/*!***************************************************************!*\
  !*** ./node_modules/core-js/modules/_array-species-create.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 9.4.2.3 ArraySpeciesCreate(originalArray, length)
var speciesConstructor = __webpack_require__(/*! ./_array-species-constructor */ "./node_modules/core-js/modules/_array-species-constructor.js");

module.exports = function (original, length) {
  return new (speciesConstructor(original))(length);
};


/***/ }),

/***/ "./node_modules/core-js/modules/_cof.js":
/*!**********************************************!*\
  !*** ./node_modules/core-js/modules/_cof.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = function (it) {
  return toString.call(it).slice(8, -1);
};


/***/ }),

/***/ "./node_modules/core-js/modules/_core.js":
/*!***********************************************!*\
  !*** ./node_modules/core-js/modules/_core.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var core = module.exports = { version: '2.5.7' };
if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef


/***/ }),

/***/ "./node_modules/core-js/modules/_ctx.js":
/*!**********************************************!*\
  !*** ./node_modules/core-js/modules/_ctx.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// optional / simple context binding
var aFunction = __webpack_require__(/*! ./_a-function */ "./node_modules/core-js/modules/_a-function.js");
module.exports = function (fn, that, length) {
  aFunction(fn);
  if (that === undefined) return fn;
  switch (length) {
    case 1: return function (a) {
      return fn.call(that, a);
    };
    case 2: return function (a, b) {
      return fn.call(that, a, b);
    };
    case 3: return function (a, b, c) {
      return fn.call(that, a, b, c);
    };
  }
  return function (/* ...args */) {
    return fn.apply(that, arguments);
  };
};


/***/ }),

/***/ "./node_modules/core-js/modules/_defined.js":
/*!**************************************************!*\
  !*** ./node_modules/core-js/modules/_defined.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// 7.2.1 RequireObjectCoercible(argument)
module.exports = function (it) {
  if (it == undefined) throw TypeError("Can't call method on  " + it);
  return it;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_descriptors.js":
/*!******************************************************!*\
  !*** ./node_modules/core-js/modules/_descriptors.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// Thank's IE8 for his funny defineProperty
module.exports = !__webpack_require__(/*! ./_fails */ "./node_modules/core-js/modules/_fails.js")(function () {
  return Object.defineProperty({}, 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),

/***/ "./node_modules/core-js/modules/_dom-create.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/modules/_dom-create.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ./_is-object */ "./node_modules/core-js/modules/_is-object.js");
var document = __webpack_require__(/*! ./_global */ "./node_modules/core-js/modules/_global.js").document;
// typeof document.createElement is 'object' in old IE
var is = isObject(document) && isObject(document.createElement);
module.exports = function (it) {
  return is ? document.createElement(it) : {};
};


/***/ }),

/***/ "./node_modules/core-js/modules/_export.js":
/*!*************************************************!*\
  !*** ./node_modules/core-js/modules/_export.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ./_global */ "./node_modules/core-js/modules/_global.js");
var core = __webpack_require__(/*! ./_core */ "./node_modules/core-js/modules/_core.js");
var hide = __webpack_require__(/*! ./_hide */ "./node_modules/core-js/modules/_hide.js");
var redefine = __webpack_require__(/*! ./_redefine */ "./node_modules/core-js/modules/_redefine.js");
var ctx = __webpack_require__(/*! ./_ctx */ "./node_modules/core-js/modules/_ctx.js");
var PROTOTYPE = 'prototype';

var $export = function (type, name, source) {
  var IS_FORCED = type & $export.F;
  var IS_GLOBAL = type & $export.G;
  var IS_STATIC = type & $export.S;
  var IS_PROTO = type & $export.P;
  var IS_BIND = type & $export.B;
  var target = IS_GLOBAL ? global : IS_STATIC ? global[name] || (global[name] = {}) : (global[name] || {})[PROTOTYPE];
  var exports = IS_GLOBAL ? core : core[name] || (core[name] = {});
  var expProto = exports[PROTOTYPE] || (exports[PROTOTYPE] = {});
  var key, own, out, exp;
  if (IS_GLOBAL) source = name;
  for (key in source) {
    // contains in native
    own = !IS_FORCED && target && target[key] !== undefined;
    // export native or passed
    out = (own ? target : source)[key];
    // bind timers to global for call from export context
    exp = IS_BIND && own ? ctx(out, global) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
    // extend global
    if (target) redefine(target, key, out, type & $export.U);
    // export
    if (exports[key] != out) hide(exports, key, exp);
    if (IS_PROTO && expProto[key] != out) expProto[key] = out;
  }
};
global.core = core;
// type bitmap
$export.F = 1;   // forced
$export.G = 2;   // global
$export.S = 4;   // static
$export.P = 8;   // proto
$export.B = 16;  // bind
$export.W = 32;  // wrap
$export.U = 64;  // safe
$export.R = 128; // real proto method for `library`
module.exports = $export;


/***/ }),

/***/ "./node_modules/core-js/modules/_fails.js":
/*!************************************************!*\
  !*** ./node_modules/core-js/modules/_fails.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (e) {
    return true;
  }
};


/***/ }),

/***/ "./node_modules/core-js/modules/_global.js":
/*!*************************************************!*\
  !*** ./node_modules/core-js/modules/_global.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
var global = module.exports = typeof window != 'undefined' && window.Math == Math
  ? window : typeof self != 'undefined' && self.Math == Math ? self
  // eslint-disable-next-line no-new-func
  : Function('return this')();
if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef


/***/ }),

/***/ "./node_modules/core-js/modules/_has.js":
/*!**********************************************!*\
  !*** ./node_modules/core-js/modules/_has.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var hasOwnProperty = {}.hasOwnProperty;
module.exports = function (it, key) {
  return hasOwnProperty.call(it, key);
};


/***/ }),

/***/ "./node_modules/core-js/modules/_hide.js":
/*!***********************************************!*\
  !*** ./node_modules/core-js/modules/_hide.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(/*! ./_object-dp */ "./node_modules/core-js/modules/_object-dp.js");
var createDesc = __webpack_require__(/*! ./_property-desc */ "./node_modules/core-js/modules/_property-desc.js");
module.exports = __webpack_require__(/*! ./_descriptors */ "./node_modules/core-js/modules/_descriptors.js") ? function (object, key, value) {
  return dP.f(object, key, createDesc(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_ie8-dom-define.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/modules/_ie8-dom-define.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = !__webpack_require__(/*! ./_descriptors */ "./node_modules/core-js/modules/_descriptors.js") && !__webpack_require__(/*! ./_fails */ "./node_modules/core-js/modules/_fails.js")(function () {
  return Object.defineProperty(__webpack_require__(/*! ./_dom-create */ "./node_modules/core-js/modules/_dom-create.js")('div'), 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),

/***/ "./node_modules/core-js/modules/_iobject.js":
/*!**************************************************!*\
  !*** ./node_modules/core-js/modules/_iobject.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// fallback for non-array-like ES3 and non-enumerable old V8 strings
var cof = __webpack_require__(/*! ./_cof */ "./node_modules/core-js/modules/_cof.js");
// eslint-disable-next-line no-prototype-builtins
module.exports = Object('z').propertyIsEnumerable(0) ? Object : function (it) {
  return cof(it) == 'String' ? it.split('') : Object(it);
};


/***/ }),

/***/ "./node_modules/core-js/modules/_is-array.js":
/*!***************************************************!*\
  !*** ./node_modules/core-js/modules/_is-array.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 7.2.2 IsArray(argument)
var cof = __webpack_require__(/*! ./_cof */ "./node_modules/core-js/modules/_cof.js");
module.exports = Array.isArray || function isArray(arg) {
  return cof(arg) == 'Array';
};


/***/ }),

/***/ "./node_modules/core-js/modules/_is-object.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/modules/_is-object.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (it) {
  return typeof it === 'object' ? it !== null : typeof it === 'function';
};


/***/ }),

/***/ "./node_modules/core-js/modules/_library.js":
/*!**************************************************!*\
  !*** ./node_modules/core-js/modules/_library.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = false;


/***/ }),

/***/ "./node_modules/core-js/modules/_object-dp.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/modules/_object-dp.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(/*! ./_an-object */ "./node_modules/core-js/modules/_an-object.js");
var IE8_DOM_DEFINE = __webpack_require__(/*! ./_ie8-dom-define */ "./node_modules/core-js/modules/_ie8-dom-define.js");
var toPrimitive = __webpack_require__(/*! ./_to-primitive */ "./node_modules/core-js/modules/_to-primitive.js");
var dP = Object.defineProperty;

exports.f = __webpack_require__(/*! ./_descriptors */ "./node_modules/core-js/modules/_descriptors.js") ? Object.defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPrimitive(P, true);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return dP(O, P, Attributes);
  } catch (e) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};


/***/ }),

/***/ "./node_modules/core-js/modules/_property-desc.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/modules/_property-desc.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};


/***/ }),

/***/ "./node_modules/core-js/modules/_redefine.js":
/*!***************************************************!*\
  !*** ./node_modules/core-js/modules/_redefine.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ./_global */ "./node_modules/core-js/modules/_global.js");
var hide = __webpack_require__(/*! ./_hide */ "./node_modules/core-js/modules/_hide.js");
var has = __webpack_require__(/*! ./_has */ "./node_modules/core-js/modules/_has.js");
var SRC = __webpack_require__(/*! ./_uid */ "./node_modules/core-js/modules/_uid.js")('src');
var TO_STRING = 'toString';
var $toString = Function[TO_STRING];
var TPL = ('' + $toString).split(TO_STRING);

__webpack_require__(/*! ./_core */ "./node_modules/core-js/modules/_core.js").inspectSource = function (it) {
  return $toString.call(it);
};

(module.exports = function (O, key, val, safe) {
  var isFunction = typeof val == 'function';
  if (isFunction) has(val, 'name') || hide(val, 'name', key);
  if (O[key] === val) return;
  if (isFunction) has(val, SRC) || hide(val, SRC, O[key] ? '' + O[key] : TPL.join(String(key)));
  if (O === global) {
    O[key] = val;
  } else if (!safe) {
    delete O[key];
    hide(O, key, val);
  } else if (O[key]) {
    O[key] = val;
  } else {
    hide(O, key, val);
  }
// add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
})(Function.prototype, TO_STRING, function toString() {
  return typeof this == 'function' && this[SRC] || $toString.call(this);
});


/***/ }),

/***/ "./node_modules/core-js/modules/_shared.js":
/*!*************************************************!*\
  !*** ./node_modules/core-js/modules/_shared.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var core = __webpack_require__(/*! ./_core */ "./node_modules/core-js/modules/_core.js");
var global = __webpack_require__(/*! ./_global */ "./node_modules/core-js/modules/_global.js");
var SHARED = '__core-js_shared__';
var store = global[SHARED] || (global[SHARED] = {});

(module.exports = function (key, value) {
  return store[key] || (store[key] = value !== undefined ? value : {});
})('versions', []).push({
  version: core.version,
  mode: __webpack_require__(/*! ./_library */ "./node_modules/core-js/modules/_library.js") ? 'pure' : 'global',
  copyright: '© 2018 Denis Pushkarev (zloirock.ru)'
});


/***/ }),

/***/ "./node_modules/core-js/modules/_to-integer.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/modules/_to-integer.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// 7.1.4 ToInteger
var ceil = Math.ceil;
var floor = Math.floor;
module.exports = function (it) {
  return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
};


/***/ }),

/***/ "./node_modules/core-js/modules/_to-length.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/modules/_to-length.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.15 ToLength
var toInteger = __webpack_require__(/*! ./_to-integer */ "./node_modules/core-js/modules/_to-integer.js");
var min = Math.min;
module.exports = function (it) {
  return it > 0 ? min(toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
};


/***/ }),

/***/ "./node_modules/core-js/modules/_to-object.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/modules/_to-object.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.13 ToObject(argument)
var defined = __webpack_require__(/*! ./_defined */ "./node_modules/core-js/modules/_defined.js");
module.exports = function (it) {
  return Object(defined(it));
};


/***/ }),

/***/ "./node_modules/core-js/modules/_to-primitive.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/modules/_to-primitive.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.1 ToPrimitive(input [, PreferredType])
var isObject = __webpack_require__(/*! ./_is-object */ "./node_modules/core-js/modules/_is-object.js");
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string
module.exports = function (it, S) {
  if (!isObject(it)) return it;
  var fn, val;
  if (S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  if (typeof (fn = it.valueOf) == 'function' && !isObject(val = fn.call(it))) return val;
  if (!S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  throw TypeError("Can't convert object to primitive value");
};


/***/ }),

/***/ "./node_modules/core-js/modules/_uid.js":
/*!**********************************************!*\
  !*** ./node_modules/core-js/modules/_uid.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var id = 0;
var px = Math.random();
module.exports = function (key) {
  return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
};


/***/ }),

/***/ "./node_modules/core-js/modules/_wks.js":
/*!**********************************************!*\
  !*** ./node_modules/core-js/modules/_wks.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var store = __webpack_require__(/*! ./_shared */ "./node_modules/core-js/modules/_shared.js")('wks');
var uid = __webpack_require__(/*! ./_uid */ "./node_modules/core-js/modules/_uid.js");
var Symbol = __webpack_require__(/*! ./_global */ "./node_modules/core-js/modules/_global.js").Symbol;
var USE_SYMBOL = typeof Symbol == 'function';

var $exports = module.exports = function (name) {
  return store[name] || (store[name] =
    USE_SYMBOL && Symbol[name] || (USE_SYMBOL ? Symbol : uid)('Symbol.' + name));
};

$exports.store = store;


/***/ }),

/***/ "./node_modules/core-js/modules/es6.array.find.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/modules/es6.array.find.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// 22.1.3.8 Array.prototype.find(predicate, thisArg = undefined)
var $export = __webpack_require__(/*! ./_export */ "./node_modules/core-js/modules/_export.js");
var $find = __webpack_require__(/*! ./_array-methods */ "./node_modules/core-js/modules/_array-methods.js")(5);
var KEY = 'find';
var forced = true;
// Shouldn't skip holes
if (KEY in []) Array(1)[KEY](function () { forced = false; });
$export($export.P + $export.F * forced, 'Array', {
  find: function find(callbackfn /* , that = undefined */) {
    return $find(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  }
});
__webpack_require__(/*! ./_add-to-unscopables */ "./node_modules/core-js/modules/_add-to-unscopables.js")(KEY);


/***/ }),

/***/ "./node_modules/core-js/modules/es6.function.name.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/modules/es6.function.name.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(/*! ./_object-dp */ "./node_modules/core-js/modules/_object-dp.js").f;
var FProto = Function.prototype;
var nameRE = /^\s*function ([^ (]*)/;
var NAME = 'name';

// 19.2.4.2 name
NAME in FProto || __webpack_require__(/*! ./_descriptors */ "./node_modules/core-js/modules/_descriptors.js") && dP(FProto, NAME, {
  configurable: true,
  get: function () {
    try {
      return ('' + this).match(nameRE)[1];
    } catch (e) {
      return '';
    }
  }
});


/***/ })

/******/ });
//# sourceMappingURL=edd-admin-settings-tax-rates.js.map