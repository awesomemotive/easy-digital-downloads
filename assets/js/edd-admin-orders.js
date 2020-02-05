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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/orders/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/orders/index.js":
/*!*****************************************!*\
  !*** ./assets/js/admin/orders/index.js ***!
  \*****************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var _order_items__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./order-items */ "./assets/js/admin/orders/order-items/index.js");
/* harmony import */ var _order_adjustments__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./order-adjustments */ "./assets/js/admin/orders/order-adjustments/index.js");
/* harmony import */ var _order_amounts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./order-amounts */ "./assets/js/admin/orders/order-amounts/index.js");
/* harmony import */ var _order_details__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./order-details */ "./assets/js/admin/orders/order-details/index.js");
/* harmony import */ var _list_table_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./list-table.js */ "./assets/js/admin/orders/list-table.js");
/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/**
 * Internal dependencies
 */






Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_5__["jQueryReady"])(function () {
  // "Validate" order form before submitting.
  // @todo move somewhere else?
  $('#edd-add-order-form').on('submit', function () {
    $('#publishing-action .spinner').css('visibility', 'visible');
    $('#edd-order-submit').prop('disabled', true);

    if ($('.orderitems tr.no-items').is(':visible')) {
      $('#edd-add-order-no-items-error').slideDown();
    } else {
      $('#edd-add-order-no-items-error').slideUp();
    }

    if ($('.order-customer-info').is(':visible')) {
      $('#edd-add-order-customer-error').slideDown();
    } else {
      $('#edd-add-order-customer-error').slideUp();
    }

    if ($('.notice:not(.updated)').is(':visible')) {
      $('#publishing-action .spinner').css('visibility', 'hidden');
      $('#edd-order-submit').prop('disabled', false);
      return false;
    }
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/list-table.js":
/*!**********************************************!*\
  !*** ./assets/js/admin/orders/list-table.js ***!
  \**********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* global $, ajaxurl */

/**
 * Internal dependencies
 */

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  $('.edd-advanced-filters-button').on('click', function (e) {
    e.preventDefault();
    $('#edd-advanced-filters').toggleClass('open');
  });
  $('.edd_countries_filter').on('change', function () {
    var select = $(this),
        data = {
      action: 'edd_get_shop_states',
      country: select.val(),
      nonce: select.data('nonce'),
      field_name: 'edd_regions_filter'
    };
    $.post(ajaxurl, data, function (response) {
      $('select.edd_regions_filter').find('option:gt(0)').remove();

      if ('nostates' !== response) {
        $(response).find('option:gt(0)').appendTo('select.edd_regions_filter');
      }

      $('select.edd_regions_filter').trigger('chosen:updated');
    });
    return false;
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-adjustments/add-adjustment.js":
/*!********************************************************************!*\
  !*** ./assets/js/admin/orders/order-adjustments/add-adjustment.js ***!
  \********************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var _index_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./index.js */ "./assets/js/admin/orders/order-adjustments/index.js");
/* harmony import */ var utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! utils */ "./assets/js/utils/index.js");
/* harmony import */ var _order_amounts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./../order-amounts */ "./assets/js/admin/orders/order-amounts/index.js");
/* global $ */

/**
 * Internal dependencies
 */



Object(utils__WEBPACK_IMPORTED_MODULE_1__["jQueryReady"])(function () {
  var currency = new utils__WEBPACK_IMPORTED_MODULE_1__["Currency"](); // Toggle form.

  $('#edd-order-adjustments').on('click', 'h3 .edd-metabox-title-action', function (e) {
    e.preventDefault();
    $('#edd-order-adjustments').children('.edd-add-adjustment-to-purchase').slideToggle();
  });
  $('.edd-order-add-adjustment-select').on('change', function () {
    var type = $(this).val();
    $('.edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.discount, .edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.credit').hide();
    $('.' + type, '.edd-add-adjustment-to-purchase').show();
  });
  $('.edd-add-order-adjustment-button').on('click', function (e) {
    e.preventDefault();
    var data = {
      action: 'edd_add_adjustment_to_order',
      nonce: $('#edd_add_order_nonce').val(),
      type: $('.edd-order-add-adjustment-select').val(),
      adjustment_data: {
        fee: $('.edd-order-add-fee-select').val(),
        discount: $('.edd-order-add-discount-select').val(),
        credit: {
          description: $('.edd-add-order-credit-description').val(),
          amount: currency.unformat($('.edd-add-order-credit-amount').val())
        }
      }
    },
        spinner = $('.edd-add-adjustment-to-purchase .spinner');
    spinner.css('visibility', 'visible');
    $.post(ajaxurl, data, function (response) {
      var success = response.success,
          data = response.data;

      if (!success) {
        return;
      }

      $('.orderadjustments .no-items').hide();
      $('.orderadjustments tbody').append(data.html);
      Object(_order_amounts__WEBPACK_IMPORTED_MODULE_2__["updateAmounts"])();
      Object(_index_js__WEBPACK_IMPORTED_MODULE_0__["reindex"])();
      spinner.css('visibility', 'hidden'); // Let other things happen. jQuery event for now.

      $(document).trigger('edd-admin-add-order-adjustment', response);
    }, 'json');
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-adjustments/index.js":
/*!***********************************************************!*\
  !*** ./assets/js/admin/orders/order-adjustments/index.js ***!
  \***********************************************************/
/*! exports provided: reindex */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "reindex", function() { return reindex; });
/* harmony import */ var _utils_list_table_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../utils/list-table.js */ "./assets/js/admin/orders/utils/list-table.js");
/* harmony import */ var _add_adjustment_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./add-adjustment.js */ "./assets/js/admin/orders/order-adjustments/add-adjustment.js");
/* global $ */

/**
 * Internal dependencies
 */


/**
 * Reindex order item table rows.
 * 
 * @since 3.0
 */

var reindex = function reindex() {
  return Object(_utils_list_table_js__WEBPACK_IMPORTED_MODULE_0__["reindexRows"])($('.orderadjustments tbody tr:not(.no-items)'));
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-amounts/index.js":
/*!*******************************************************!*\
  !*** ./assets/js/admin/orders/order-amounts/index.js ***!
  \*******************************************************/
/*! exports provided: updateAmounts */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _override_amounts_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./override-amounts.js */ "./assets/js/admin/orders/order-amounts/override-amounts.js");
/* empty/unused harmony star reexport *//* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils.js */ "./assets/js/admin/orders/order-amounts/utils.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "updateAmounts", function() { return _utils_js__WEBPACK_IMPORTED_MODULE_1__["updateAmounts"]; });




/***/ }),

/***/ "./assets/js/admin/orders/order-amounts/override-amounts.js":
/*!******************************************************************!*\
  !*** ./assets/js/admin/orders/order-amounts/override-amounts.js ***!
  \******************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils.js */ "./assets/js/admin/orders/order-amounts/utils.js");
/* global $, _ */

/**
 * Internal dependencies.
 */


Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  var toggle = document.getElementById('edd-override-amounts');

  if (!toggle) {
    return;
  }

  var isOverrideableEl = document.querySelector('input[name="edd-order-download-is-overrideable"]');
  /**
   * A new download has been added.
   */

  $(document).on('edd-admin-add-order-download', function (response) {
    // Update on change.
    _.each(document.querySelectorAll('.overridable input'), function (el) {
      return el.addEventListener('input', _utils_js__WEBPACK_IMPORTED_MODULE_1__["updateAmounts"]);
    }); // Update on addition.


    Object(_utils_js__WEBPACK_IMPORTED_MODULE_1__["updateAmounts"])(); // Keep toggle disabled if necesseary.

    toggle.disabled = 1 == isOverrideableEl.value;
  });
  /**
   * Allow edits.
   */

  toggle.addEventListener('change', function () {
    // Disable the button.
    this.disabled = true; // Tell future download item additions to be editable.

    isOverrideableEl.value = 1; // Get a fresh set of inputs. Mark current inputs as editable.

    _.each(document.querySelectorAll('.overridable input'), function (el) {
      return el.readOnly = false;
    }); // Mark the override for saving the data.


    var input = document.createElement('input');
    input.name = 'edd_add_order_override';
    input.value = true;
    input.type = 'hidden';
    document.getElementById('edd-add-order-form').appendChild(input);
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-amounts/utils.js":
/*!*******************************************************!*\
  !*** ./assets/js/admin/orders/order-amounts/utils.js ***!
  \*******************************************************/
/*! exports provided: updateAmounts */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "updateAmounts", function() { return updateAmounts; });
/* harmony import */ var utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils */ "./assets/js/utils/index.js");
/* global $ */

/**
 * Internal dependencies
 */

/**
 * Updates the values in the "Order Amounts" metabox.
 *
 * @note This only updates the UI and does not affect server-side processing.
 *
 * @since 3.0.0
 */

function updateAmounts() {
  var currency = new utils__WEBPACK_IMPORTED_MODULE_0__["Currency"]();
  var subtotal = 0,
      discounts = 0,
      adjustments = 0,
      tax = 0,
      total = 0;
  $('.orderitems tbody tr:not(.no-items)').each(function () {
    var row = $(this),
        item_amount,
        item_quantity = 1,
        item_tax = 0,
        item_total;
    item_amount = currency.unformat(row.find('.amount input').val());

    if (row.find('.quantity').length) {
      item_quantity = parseFloat(row.find('.quantity input').val());
    }

    subtotal += item_amount * item_quantity;

    if (row.find('.tax').length) {
      item_tax = currency.unformat(row.find('.tax input').val());

      if (!isNaN(item_tax) && !edd_vars.taxes_included) {
        item_amount += item_tax;
        tax += item_tax;
      }
    }

    item_total = subtotal + tax;
    total += item_total;
  });
  $('.orderadjustments tbody tr:not(.no-items)').each(function () {
    var row = $(this),
        type,
        amount = 0;
    type = row.data('adjustment');
    amount = currency.unformat(row.find('.column-amount .value', row).text());

    switch (type) {
      case 'credit':
        adjustments += amount;
        total -= amount;
        break;

      case 'discount':
        if ('percent' === row.find('input.discount-type').val()) {
          $('.orderitems tbody tr:not(.no-items)').each(function () {
            var reduction = parseFloat(amount / 100 * subtotal);
            discounts += reduction;
            total -= reduction;
          });
        } else {
          adjustments += amount;
          total -= amount;
        }

        break;
    }
  });

  if (isNaN(total)) {
    total = 0;
  }

  if (isNaN(subtotal)) {
    subtotal = 0;
  }

  if (isNaN(tax)) {
    tax = 0;
  }

  if (isNaN(discounts)) {
    discounts = 0;
  }

  if (isNaN(adjustments)) {
    adjustments = 0;
  }

  $('.edd-order-subtotal .value').html(currency.formatCurrency(subtotal));
  $('.edd-order-discounts .value').html(currency.formatCurrency(discounts));
  $('.edd-order-adjustments .value').html(currency.formatCurrency(adjustments));
  $('.edd-order-taxes .value').html(currency.formatCurrency(tax));
  $('.edd-order-total .value').html(currency.formatCurrency(total));
}
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-details/address.js":
/*!*********************************************************!*\
  !*** ./assets/js/admin/orders/order-details/address.js ***!
  \*********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/chosen.js */ "./assets/js/utils/chosen.js");
/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* harmony import */ var _order_amounts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./../order-amounts */ "./assets/js/admin/orders/order-amounts/index.js");
/* global $, ajaxurl */

/**
 * Internal dependencies
 */


 // Store customer search results to help prefill address data.

var CUSTOMER_SEARCH_RESULTS = {};
/**
 * Recalculates tax amounts when an address changes.
 *
 * @note This only updates the UI and does not affect server-side processing.
 *
 * @since 3.0.0
 */

function recalculateTaxes() {
  $('#publishing-action .spinner').css('visibility', 'visible');
  var data = {
    action: 'edd_add_order_recalculate_taxes',
    country: $('.edd-order-address-country').val(),
    region: $('.edd-order-address-region').val(),
    nonce: $('#edd_add_order_nonce').val()
  };
  $.post(ajaxurl, data, function (response) {
    var success = response.success,
        data = response.data;

    if (!success) {
      return;
    }

    if ('' !== data.tax_rate) {
      var tax_rate = parseFloat(data.tax_rate);
      $('.orderitems tbody tr:not(.no-items)').each(function () {
        var amount = parseFloat($('.download-amount', this).val());
        var quantity = $('.download-quantity', this).length > 0 ? parseFloat($('.download-quantity', this).val()) : 1;
        var calculated = amount * quantity;
        var tax = 0;

        if (data.prices_include_tax) {
          var pre_tax = parseFloat(calculated / (1 + tax_rate));
          tax = parseFloat(calculated - pre_tax);
        } else {
          tax = calculated * tax_rate;
        }

        var storeCurrency = edd_vars.currency;
        var decimalPlaces = edd_vars.currency_decimals;
        var total = calculated + tax;
        $('.download-tax', this).val(tax.toLocaleString(storeCurrency, {
          style: 'decimal',
          minimumFractionDigits: decimalPlaces,
          maximumFractionDigits: decimalPlaces
        }));
        $('.download-total', this).val(total.toLocaleString(storeCurrency, {
          style: 'decimal',
          minimumFractionDigits: decimalPlaces,
          maximumFractionDigits: decimalPlaces
        }));
      });
    }
  }, 'json').done(function () {
    $('#publishing-action .spinner').css('visibility', 'hidden');
    Object(_order_amounts__WEBPACK_IMPORTED_MODULE_2__["updateAmounts"])();
  });
}

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_1__["jQueryReady"])(function () {
  // Update base state field based on selected base country
  $('select[name="edd-payment-address[0][country]"]').change(function () {
    var select = $(this),
        data = {
      action: 'edd_get_shop_states',
      country: select.val(),
      nonce: select.data('nonce'),
      field_name: 'edd-payment-address[0][region]'
    };
    $.post(ajaxurl, data, function (response) {
      var state_wrapper = $('#edd-order-address-state-wrap select, #edd-order-address-state-wrap input'); // Remove any chosen containers here too

      $('#edd-order-address-state-wrap .chosen-container').remove();

      if ('nostates' === response) {
        state_wrapper.replaceWith('<input type="text" name="edd-payment-address[0][region]" value="" class="edd-edit-toggles medium-text"/>');
      } else {
        state_wrapper.replaceWith(response);
        $('#edd-order-address-state-wrap select').each(function () {
          var el = $(this);
          el.chosen(Object(utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__["getChosenVars"])(el));
        });
      }
    });
    return false;
  });
  $('.edd-payment-change-customer-input').on('change', function () {
    var $this = $(this),
        data = {
      action: 'edd_customer_addresses',
      customer_id: $this.val(),
      nonce: $('#edd_add_order_nonce').val()
    };
    $.post(ajaxurl, data, function (response) {
      var success = response.success,
          data = response.data;

      if (!success) {
        $('.customer-address-select-wrap').html('').hide();
        return;
      } // Store response for later use.


      CUSTOMER_SEARCH_RESULTS = data;

      if (data.html) {
        $('.customer-address-select-wrap').html(data.html).show();
        $('.customer-address-select-wrap select').each(function () {
          var el = $(this);
          el.chosen(Object(utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__["getChosenVars"])(el));
        });
      } else {
        $('.customer-address-select-wrap').html('').hide();
      }
    }, 'json');
    return false;
  });
  $(document.body).on('change', '.customer-address-select-wrap .add-order-customer-address-select', function () {
    var $this = $(this),
        val = $this.val(),
        select = $('#edd-add-order-form select#edd_order_address_country'),
        address = CUSTOMER_SEARCH_RESULTS.addresses[val];
    $('#edd-add-order-form input[name="edd_order_address[address]"]').val(address.address);
    $('#edd-add-order-form input[name="edd_order_address[address2]"]').val(address.address2);
    $('#edd-add-order-form input[name="edd_order_address[postal_code]"]').val(address.postal_code);
    $('#edd-add-order-form input[name="edd_order_address[city]"]').val(address.city);
    select.val(address.country).trigger('chosen:updated');
    $('#edd-add-order-form input[name="edd_order_address[address_id]"]').val(val);
    var data = {
      action: 'edd_get_shop_states',
      country: select.val(),
      nonce: $('.add-order-customer-address-select').data('nonce'),
      field_name: 'edd_order_address_region'
    };
    $.post(ajaxurl, data, function (response) {
      $('select#edd_order_address_region').find('option:gt(0)').remove();

      if ('nostates' !== response) {
        $(response).find('option:gt(0)').appendTo('select#edd_order_address_region');
      }

      $('select#edd_order_address_region').trigger('chosen:updated');
      $('select#edd_order_address_region').val(address.region).trigger('chosen:updated');
    });
    return false;
  }); // Country change.

  $('.edd-order-address-country').on('change', function () {
    var select = $(this),
        data = {
      action: 'edd_get_shop_states',
      country: select.val(),
      nonce: select.data('nonce'),
      field_name: 'edd-order-address-country'
    };
    $.post(ajaxurl, data, function (response) {
      $('select.edd-order-address-region').find('option:gt(0)').remove();

      if ('nostates' !== response) {
        $(response).find('option:gt(0)').appendTo('select.edd-order-address-region');
      }

      $('select.edd-order-address-region').trigger('chosen:updated');
    }).done(recalculateTaxes);
  }); // Region change.

  $('.edd-order-address-region').on('change', recalculateTaxes);
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-details/customer.js":
/*!**********************************************************!*\
  !*** ./assets/js/admin/orders/order-details/customer.js ***!
  \**********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* global $ */

/**
 * Internal dependencies
 */

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  // Change Customer.
  $('#edd-customer-details').on('click', '.edd-payment-change-customer, .edd-payment-change-customer-cancel', function (e) {
    e.preventDefault();
    var change_customer = $(this).hasClass('edd-payment-change-customer'),
        cancel = $(this).hasClass('edd-payment-change-customer-cancel');

    if (change_customer) {
      $('.order-customer-info').hide();
      $('.change-customer').show();
      setTimeout(function () {
        $('.edd-payment-change-customer-input').css('width', '300');
      }, 1);
    } else if (cancel) {
      $('.order-customer-info').show();
      $('.change-customer').hide();
    }
  }); // New Customer.

  $('#edd-customer-details').on('click', '.edd-payment-new-customer, .edd-payment-new-customer-cancel', function (e) {
    e.preventDefault();
    var new_customer = $(this).hasClass('edd-payment-new-customer'),
        cancel = $(this).hasClass('edd-payment-new-customer-cancel');

    if (new_customer) {
      $('.order-customer-info').hide();
      $('.new-customer').show();
    } else if (cancel) {
      $('.order-customer-info').show();
      $('.new-customer').hide();
    }

    var new_customer = $('#edd-new-customer');

    if ($('.new-customer').is(':visible')) {
      new_customer.val(1);
    } else {
      new_customer.val(0);
    }
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-details/index.js":
/*!*******************************************************!*\
  !*** ./assets/js/admin/orders/order-details/index.js ***!
  \*******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _address_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./address.js */ "./assets/js/admin/orders/order-details/address.js");
/* harmony import */ var _customer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./customer.js */ "./assets/js/admin/orders/order-details/customer.js");
/* harmony import */ var _receipt_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./receipt.js */ "./assets/js/admin/orders/order-details/receipt.js");




/***/ }),

/***/ "./assets/js/admin/orders/order-details/receipt.js":
/*!*********************************************************!*\
  !*** ./assets/js/admin/orders/order-details/receipt.js ***!
  \*********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* global $, ajaxurl */

/**
 * Internal dependencies
 */

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  var emails_wrap = $('.edd-order-resend-receipt-addresses');
  $(document.body).on('click', '#edd-select-receipt-email', function (e) {
    e.preventDefault();
    emails_wrap.slideDown();
  });
  $(document.body).on('change', '.edd-order-resend-receipt-email', function () {
    var selected = $('input:radio.edd-order-resend-receipt-email:checked').val();
    $('#edd-select-receipt-email').data('email', selected);
  });
  $(document.body).on('click', '#edd-select-receipt-email', function () {
    if (confirm(edd_vars.resend_receipt)) {
      var href = $(this).prop('href') + '&email=' + $(this).data('email');
      window.location = href;
    }
  });
  $(document.body).on('click', '#edd-resend-receipt', function () {
    return confirm(edd_vars.resend_receipt);
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-items/add.js":
/*!***************************************************!*\
  !*** ./assets/js/admin/orders/order-items/add.js ***!
  \***************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* harmony import */ var _index_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./index.js */ "./assets/js/admin/orders/order-items/index.js");
/* harmony import */ var _order_amounts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./../order-amounts */ "./assets/js/admin/orders/order-amounts/index.js");
/* global $ */

/**
 * Internal dependencies
 */



Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  var button = $('.edd-add-order-item-button'); // Toggle form.

  $('#edd-order-items').on('click', 'h3 .edd-metabox-title-action', function (e) {
    e.preventDefault();
    $('#edd-order-items').children('.edd-add-download-to-purchase').slideToggle();
  });
  button.prop('disabled', 'disabled');
  $('.edd-order-add-download-select').on('change', function () {
    button.removeAttr('disabled');
  }); // Add item.

  button.on('click', function (e) {
    e.preventDefault();
    var select = $('.edd-order-add-download-select'),
        spinner = $('.edd-add-download-to-purchase .spinner'),
        data = {
      action: 'edd_add_order_item',
      nonce: $('#edd_add_order_nonce').val(),
      country: $('.edd-order-address-country').val(),
      region: $('.edd-order-address-region').val(),
      download: select.val(),
      quantity: $('.edd-add-order-quantity').val(),
      editable: $('input[name="edd-order-download-is-overrideable"]').val()
    };
    spinner.css('visibility', 'visible');
    $.post(ajaxurl, data, function (response) {
      var success = response.success,
          data = response.data;

      if (!success) {
        return;
      }

      $('.orderitems .no-items').hide();
      $('.orderitems tbody').append(data.html); // @todo attach to edd-admin-add-order-download trigger in /order-amounts

      Object(_order_amounts__WEBPACK_IMPORTED_MODULE_2__["updateAmounts"])();
      Object(_index_js__WEBPACK_IMPORTED_MODULE_1__["reindex"])();
      spinner.css('visibility', 'hidden'); // Let other things happen. jQuery event for now.

      $(document).trigger('edd-admin-add-order-download', response);
    }, 'json');
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-items/index.js":
/*!*****************************************************!*\
  !*** ./assets/js/admin/orders/order-items/index.js ***!
  \*****************************************************/
/*! exports provided: reindex */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "reindex", function() { return reindex; });
/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* harmony import */ var _add_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./add.js */ "./assets/js/admin/orders/order-items/add.js");
/* harmony import */ var _remove_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./remove.js */ "./assets/js/admin/orders/order-items/remove.js");
/* harmony import */ var _refund_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./refund.js */ "./assets/js/admin/orders/order-items/refund.js");
/* harmony import */ var _utils_list_table_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./../utils/list-table.js */ "./assets/js/admin/orders/utils/list-table.js");
/* global $, ajaxurl */

/**
 * Internal dependencies
 */





/**
 * Reindex order item table rows.
 * 
 * @since 3.0
 */

var reindex = function reindex() {
  return Object(_utils_list_table_js__WEBPACK_IMPORTED_MODULE_4__["reindexRows"])($('.orderitems tbody tr:not(.no-items)'));
}; // @todo move somewhere else?

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  // Copy Download file URL.
  $(document.body).on('click', '.edd-copy-download-link', function (e) {
    e.preventDefault();
    var button = $(this),
        postData = {
      action: 'edd_get_file_download_link',
      payment_id: $('input[name="edd_payment_id"]').val(),
      download_id: button.data('download-id'),
      price_id: button.data('price-id')
    };
    $.ajax({
      type: 'POST',
      data: postData,
      url: ajaxurl,
      success: function success(link) {
        console.log(link);
        $('#edd-download-link').dialog({
          width: 400
        }).html('<textarea rows="10" cols="40" id="edd-download-link-textarea">' + link + '</textarea>');
        $('#edd-download-link-textarea').focus().select();
      }
    }).fail(function (data) {
      if (window.console && window.console.log) {
        console.log(data);
      }
    });
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-items/refund.js":
/*!******************************************************!*\
  !*** ./assets/js/admin/orders/order-items/refund.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* global $, ajaxurl */

/**
 * Internal dependencies
 */

Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  $(document.body).on('click', '.edd-refund-order', function (e) {
    e.preventDefault();
    var link = $(this),
        postData = {
      action: 'edd_generate_refund_form',
      order_id: $('input[name="edd_payment_id"]').val()
    };
    $.ajax({
      type: 'POST',
      data: postData,
      url: ajaxurl,
      success: function success(data) {
        var modal_content = '';

        if (data.success) {
          modal_content = data.html;
        } else {
          modal_content = data.message;
        }

        $('#edd-refund-order-dialog').dialog({
          position: {
            my: 'top center',
            at: 'center center-25%'
          },
          width: '75%',
          modal: true,
          resizable: false,
          draggable: false,
          open: function open(event, ui) {
            $(this).html(modal_content);
          },
          close: function close(event, ui) {
            $(this).html('');
            location.reload();
          }
        });
        return false;
      }
    }).fail(function (data) {
      $('#edd-refund-order-dialog').dialog({
        position: {
          my: 'top center',
          at: 'center center-25%'
        },
        width: '75%',
        modal: true,
        resizable: false,
        draggable: false
      }).html(data.message);
      return false;
    });
  }); // Handles including items in the refund.

  $(document.body).on('change', '#edd-refund-order-dialog tbody .check-column input[type="checkbox"]', function () {
    var parent = $(this).parent().parent(),
        all_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');

    if ($(this).is(':checked')) {
      parent.addClass('refunded');
    } else {
      parent.removeClass('refunded');
    }

    var new_subtotal = 0,
        new_tax = 0,
        new_total = 0; // Set a readonly while we recalculate, to avoid race conditions in the browser.

    all_checkboxes.prop('readonly', true);
    $('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'visible');
    $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]:checked').each(function () {
      var item_parent = $(this).parent().parent(); // Values for this item.

      var item_amount = parseFloat(item_parent.find('span[data-amount]').data('amount')),
          item_tax = parseFloat(item_parent.find('span[data-tax]').data('tax')),
          item_total = parseFloat(item_parent.find('span[data-total]').data('total')),
          item_quantity = parseInt(item_parent.find('.column-quantity').text());
      new_subtotal += item_amount;
      new_tax += item_tax;
      new_total += item_total;
    });
    new_subtotal = parseFloat(new_subtotal).toFixed(edd_vars.currency_decimals);
    new_tax = parseFloat(new_tax).toFixed(edd_vars.currency_decimals);
    new_total = parseFloat(new_total).toFixed(edd_vars.currency_decimals);
    $('#edd-refund-submit-subtotal-amount').data('refund-subtotal', new_subtotal).text(new_subtotal);
    $('#edd-refund-submit-tax-amount').data('refund-tax', new_tax).text(new_tax);
    $('#edd-refund-submit-total-amount').data('refund-total', new_total).text(new_total);

    if (new_total > 0) {
      $('#edd-submit-refund-submit').removeClass('disabled');
    } else {
      $('#edd-submit-refund-submit').addClass('disabled');
    } // Remove the readonly.


    all_checkboxes.prop('readonly', false);
    $('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'hidden');
  }); // Listen for the bulk action checkbox, since WP doesn't trigger a change on sub-items.

  $(document.body).on('change', '#edd-refund-order-dialog #cb-select-all-1', function () {
    var item_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');

    if ($(this).is(':checked')) {
      item_checkboxes.each(function () {
        $(this).prop('checked', true).trigger('change');
      });
    } else {
      item_checkboxes.each(function () {
        $(this).prop('checked', false).trigger('change');
      });
    }
  }); // Process the refund form after the button is clicked.

  $(document.body).on('click', '#edd-submit-refund-submit', function (e) {
    $('.edd-submit-refund-message').removeClass('success').removeClass('fail');
    $(this).addClass('disabled');
    $('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'visible');
    $('#edd-submit-refund-status').hide();
    var item_ids = [],
        refund_subtotal = $('#edd-refund-submit-subtotal-amount').data('refund-subtotal'),
        refund_tax = $('#edd-refund-submit-tax-amount').data('refund-tax'),
        refund_total = $('#edd-refund-submit-total-amount').data('refund-total'); // Get the Order Item IDs we're going to be refunding.

    var item_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');
    item_checkboxes.each(function () {
      if ($(this).is(':checked')) {
        var item_id = $(this).parent().parent().data('order-item');
        item_ids.push(item_id);
      }
    });
    e.preventDefault();
    var postData = {
      action: 'edd_process_refund_form',
      item_ids: item_ids,
      refund_subtotal: refund_subtotal,
      refund_tax: refund_tax,
      refund_total: refund_total,
      order_id: $('input[name="edd_payment_id"]').val(),
      nonce: $('#edd-process-refund-form #_wpnonce').val()
    };
    $.ajax({
      type: 'POST',
      data: postData,
      url: ajaxurl,
      success: function success(data) {
        var message_target = $('.edd-submit-refund-message'),
            url_target = $('.edd-submit-refund-url');

        if (data.success) {
          $('#edd-refund-order-dialog table').hide();
          $('#edd-refund-order-dialog .tablenav').hide();
          message_target.text(data.message).addClass('success');
          url_target.attr('href', data.refund_url).show();
          $('#edd-submit-refund-status').show();
        } else {
          message_target.text(data.message).addClass('fail');
          url_target.hide();
          $('#edd-submit-refund-status').show();
          $('#edd-submit-refund-submit').removeClass('disabled');
          $('#edd-submit-refund-button-wrapper .spinner').css('visibility', 'hidden');
        }
      }
    }).fail(function (data) {
      var message_target = $('.edd-submit-refund-message'),
          url_target = $('.edd-submit-refund-url'),
          json = data.responseJSON;
      message_target.text(json.message).addClass('fail');
      url_target.hide();
      $('#edd-submit-refund-status').show();
      $('#edd-submit-refund-submit').removeClass('disabled');
      $('#edd-submit-refund-button-wrapper .spinner').css('visibility', 'hidden');
      return false;
    });
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/order-items/remove.js":
/*!******************************************************!*\
  !*** ./assets/js/admin/orders/order-items/remove.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/jquery.js */ "./assets/js/utils/jquery.js");
/* harmony import */ var _index_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./index.js */ "./assets/js/admin/orders/order-items/index.js");
/* harmony import */ var _order_amounts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./../order-amounts */ "./assets/js/admin/orders/order-amounts/index.js");
/* harmony import */ var _utils_list_table_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./../utils/list-table.js */ "./assets/js/admin/orders/utils/list-table.js");
/* global $ */

/**
 * Internal dependencies
 */




Object(utils_jquery_js__WEBPACK_IMPORTED_MODULE_0__["jQueryReady"])(function () {
  $(document.body).on('click', '.orderitems .remove-item', function (e) {
    e.preventDefault();
    var button = $(this);
    var row = button.parents('tr'); // Remove row.

    Object(_utils_list_table_js__WEBPACK_IMPORTED_MODULE_3__["removeRow"])(row); // @todo attach to edd-admin-remove-order-download trigger in /order-amounts

    Object(_order_amounts__WEBPACK_IMPORTED_MODULE_2__["updateAmounts"])();
    Object(_index_js__WEBPACK_IMPORTED_MODULE_1__["reindex"])();
    return false;
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/orders/utils/list-table.js":
/*!****************************************************!*\
  !*** ./assets/js/admin/orders/utils/list-table.js ***!
  \****************************************************/
/*! exports provided: reindexRows, removeRow */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "reindexRows", function() { return reindexRows; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "removeRow", function() { return removeRow; });
/* global $ */

/**
 * Reindexes inputs a list of table rows (`<tr>`s)
 *
 * Ensures the order of items is correct when the server processes them.
 *
 * @since 3.0.0
 *
 * @param {jQuery} rows List of table rows.
 */
function reindexRows(rows) {
  var key = 0;
  $(rows).each(function () {
    $(this) // Set data attribute for reference.
    .attr('data-key', key) // Update all input names in something[0] format.
    .find('input').each(function () {
      var input = $(this);
      var name = $(this).attr('name');

      if (input.attr('name')) {
        var newName = input.attr('name').replace(/\[(\d+)\]/, "[".concat(key, "]"));
        input.attr('name', newName);
      }
    });
    key++;
  });
}
/**
 * Removes a table row.
 *
 * Shows the "no items" row if it is the only remaining row.
 *
 * @since 3.0.0
 *
 * @param {jQuery} row Table row to remove.
 */

function removeRow(row) {
  var tbody = row.parents('tbody'); // Remove row.

  row.remove(); // Show "no items" if it is the only remaining item.

  if (1 === $('tr', tbody).length) {
    $('.no-items', tbody).show();
  }
}
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/utils/chosen.js":
/*!***********************************!*\
  !*** ./assets/js/utils/chosen.js ***!
  \***********************************/
/*! exports provided: chosenVars, getChosenVars */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "chosenVars", function() { return chosenVars; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getChosenVars", function() { return getChosenVars; });
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
/**
 * Determine the variables used to initialie Chosen on an element.
 *
 * @param {Object} el select element.
 * @return {Object} Variables for Chosen.
 */

var getChosenVars = function getChosenVars(el) {
  var inputVars = chosenVars; // Ensure <select data-search-type="download"> or similar can use search always.
  // These types of fields start with no options and are updated via AJAX.

  if (el.data('search-type')) {
    delete inputVars.disable_search_threshold;
  }

  return inputVars;
};

/***/ }),

/***/ "./assets/js/utils/currency/src/index.js":
/*!***********************************************!*\
  !*** ./assets/js/utils/currency/src/index.js ***!
  \***********************************************/
/*! exports provided: Currency */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Currency", function() { return Currency; });
/* harmony import */ var _babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/objectSpread */ "./node_modules/@babel/runtime/helpers/objectSpread.js");
/* harmony import */ var _babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__);




/* global edd_vars */

/**
 * External dependencies
 */
var number_format = __webpack_require__(/*! locutus/php/strings/number_format */ "./node_modules/locutus/php/strings/number_format.js");
/**
 * Currency
 */


var Currency =
/*#__PURE__*/
function () {
  /**
   * Creates configuration for currency formatting.
   *
   * @since 3.0
   *
   * @param {Object} config Currency configuration arguments.
   * @param {String} config.currency Currency (USD, AUD, etc).
   * @param {String} config.currencySymbolPosition Currency symbol position (left or right).
   * @param {Number} config.decimalPlaces The number of decimals places to format to.
   * @param {String} config.decimalSeparator The separator between the number and decimal.
   * @param {String} config.thousandsSeparator Thousands separator.
   */
  function Currency() {
    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default()(this, Currency);

    var _edd_vars = edd_vars,
        currency = _edd_vars.currency,
        currencySymbol = _edd_vars.currency_sign,
        currencySymbolPosition = _edd_vars.currency_pos,
        precision = _edd_vars.currency_decimals,
        decimalSeparator = _edd_vars.decimal_separator,
        thousandSeparator = _edd_vars.thousands_separator;
    this.config = _babel_runtime_helpers_objectSpread__WEBPACK_IMPORTED_MODULE_0___default()({
      currency: currency,
      currencySymbol: currencySymbol,
      currencySymbolPosition: currencySymbolPosition,
      precision: precision,
      decimalSeparator: decimalSeparator,
      thousandSeparator: thousandSeparator
    }, config);
  }
  /**
   * Formats a number for display.
   *
   * @since 3.0
   * @see http://locutus.io/php/strings/number_format/
   *
   * @param {Number|String} number Number to format.
   * @returns {?String} A formatted string.
   */


  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2___default()(Currency, [{
    key: "formatNumber",
    value: function formatNumber(number) {
      if ('number' !== typeof number) {
        number = parseFloat(number);
      }

      if (isNaN(number)) {
        return '';
      }

      var _this$config = this.config,
          precision = _this$config.precision,
          decimalSeparator = _this$config.decimalSeparator,
          thousandSeparator = _this$config.thousandSeparator;
      return number_format(number, precision, decimalSeparator, thousandSeparator);
    }
    /**
     * Removes any currency formatting applied to a string
     * and returns a true number.
     *
     * @since 3.0
     *
     * @param {String} number Number to unformat.
     * @return {Number}
     */

  }, {
    key: "unformat",
    value: function unformat(number) {
      var _this$config2 = this.config,
          currencySymbol = _this$config2.currencySymbol,
          decimalSeparator = _this$config2.decimalSeparator,
          thousandSeparator = _this$config2.thousandSeparator;
      var unformatted = number // Remove symbol.
      .replace(currencySymbol, '') // Remove thousand separator.
      .replace(thousandSeparator, '') // Replace decimal separator with a decimal.
      .replace(decimalSeparator, '.');
      return parseFloat(unformatted);
    }
    /**
     * Formats a number for currency display.
     *
     * @since 3.0
     *
     * @param {Number|String} number Number to format.
     * @returns {?String} A formatted string.
     */

  }, {
    key: "formatCurrency",
    value: function formatCurrency(number) {
      var formattedNumber = this.formatNumber(number);

      if ('' === formattedNumber) {
        return formattedNumber;
      }

      var _this$config3 = this.config,
          currencySymbol = _this$config3.currencySymbol,
          currencySymbolPosition = _this$config3.currencySymbolPosition;
      var currency = '';

      switch (currencySymbolPosition) {
        case 'before':
          currency = currencySymbol + formattedNumber;
          break;

        case 'after':
          currency = formattedNumber + currencySymbol;
          break;
      }

      return currency;
    }
  }]);

  return Currency;
}();

/***/ }),

/***/ "./assets/js/utils/index.js":
/*!**********************************!*\
  !*** ./assets/js/utils/index.js ***!
  \**********************************/
/*! exports provided: chosenVars, getChosenVars, jQueryReady, Currency */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _chosen_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./chosen.js */ "./assets/js/utils/chosen.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "chosenVars", function() { return _chosen_js__WEBPACK_IMPORTED_MODULE_0__["chosenVars"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "getChosenVars", function() { return _chosen_js__WEBPACK_IMPORTED_MODULE_0__["getChosenVars"]; });

/* harmony import */ var _jquery_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./jquery.js */ "./assets/js/utils/jquery.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "jQueryReady", function() { return _jquery_js__WEBPACK_IMPORTED_MODULE_1__["jQueryReady"]; });

/* harmony import */ var _currency_src__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./currency/src */ "./assets/js/utils/currency/src/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "Currency", function() { return _currency_src__WEBPACK_IMPORTED_MODULE_2__["Currency"]; });





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

/***/ "./node_modules/@babel/runtime/helpers/classCallCheck.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/classCallCheck.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

module.exports = _classCallCheck;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/createClass.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/createClass.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

module.exports = _createClass;

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

/***/ "./node_modules/locutus/php/strings/number_format.js":
/*!***********************************************************!*\
  !*** ./node_modules/locutus/php/strings/number_format.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function number_format(number, decimals, decPoint, thousandsSep) {
  // eslint-disable-line camelcase
  //  discuss at: http://locutus.io/php/number_format/
  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // improved by: Kevin van Zonneveld (http://kvz.io)
  // improved by: davook
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Theriault (https://github.com/Theriault)
  // improved by: Kevin van Zonneveld (http://kvz.io)
  // bugfixed by: Michael White (http://getsprink.com)
  // bugfixed by: Benjamin Lupton
  // bugfixed by: Allan Jensen (http://www.winternet.no)
  // bugfixed by: Howard Yeend
  // bugfixed by: Diogo Resende
  // bugfixed by: Rival
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  //  revised by: Luke Smith (http://lucassmith.name)
  //    input by: Kheang Hok Chin (http://www.distantia.ca/)
  //    input by: Jay Klehr
  //    input by: Amir Habibi (http://www.residence-mixte.com/)
  //    input by: Amirouche
  //   example 1: number_format(1234.56)
  //   returns 1: '1,235'
  //   example 2: number_format(1234.56, 2, ',', ' ')
  //   returns 2: '1 234,56'
  //   example 3: number_format(1234.5678, 2, '.', '')
  //   returns 3: '1234.57'
  //   example 4: number_format(67, 2, ',', '.')
  //   returns 4: '67,00'
  //   example 5: number_format(1000)
  //   returns 5: '1,000'
  //   example 6: number_format(67.311, 2)
  //   returns 6: '67.31'
  //   example 7: number_format(1000.55, 1)
  //   returns 7: '1,000.6'
  //   example 8: number_format(67000, 5, ',', '.')
  //   returns 8: '67.000,00000'
  //   example 9: number_format(0.9, 0)
  //   returns 9: '1'
  //  example 10: number_format('1.20', 2)
  //  returns 10: '1.20'
  //  example 11: number_format('1.20', 4)
  //  returns 11: '1.2000'
  //  example 12: number_format('1.2000', 3)
  //  returns 12: '1.200'
  //  example 13: number_format('1 000,50', 2, '.', ' ')
  //  returns 13: '100 050.00'
  //  example 14: number_format(1e-8, 8, '.', '')
  //  returns 14: '0.00000001'

  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number;
  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
  var sep = typeof thousandsSep === 'undefined' ? ',' : thousandsSep;
  var dec = typeof decPoint === 'undefined' ? '.' : decPoint;
  var s = '';

  var toFixedFix = function toFixedFix(n, prec) {
    if (('' + n).indexOf('e') === -1) {
      return +(Math.round(n + 'e+' + prec) + 'e-' + prec);
    } else {
      var arr = ('' + n).split('e');
      var sig = '';
      if (+arr[1] + prec > 0) {
        sig = '+';
      }
      return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec);
    }
  };

  // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }

  return s.join(dec);
};
//# sourceMappingURL=number_format.js.map

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
//# sourceMappingURL=edd-admin-orders.js.map