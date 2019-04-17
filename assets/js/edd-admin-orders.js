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
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/chosen.js */ "./assets/js/utils/chosen.js");
/* harmony import */ var _override_amounts_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./override-amounts.js */ "./assets/js/admin/orders/override-amounts.js");
/**
 * Internal dependencies.
 */


jQuery(document).ready(function ($) {
  // Toggle advanced filters on Orders page.
  $('.edd-advanced-filters-button').on('click', function (e) {
    // Prevnt submit action
    e.preventDefault();
    $('#edd-advanced-filters').toggleClass('open');
  });
});
var edd_admin_globals = {};
/**
 * Add order
 */

var EDD_Add_Order = {
  init: function init() {
    this.add_order_item();
    this.add_adjustment();
    this.remove();
    this.fetch_addresses();
    this.select_address();
    this.recalculate_total();
    this.validate();
  },
  add_order_item: function add_order_item() {
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
        $('.orderitems tbody').append(data.html);
        EDD_Add_Order.update_totals();
        EDD_Add_Order.reindex();
        spinner.css('visibility', 'hidden'); // Let other things happen. jQuery event for now.

        $(document).trigger('edd-admin-add-order-download', response);
      }, 'json');
    });
  },
  add_adjustment: function add_adjustment() {
    // Toggle form.
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
            amount: $('.edd-add-order-credit-amount').val()
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
        EDD_Add_Order.update_totals();
        EDD_Add_Order.reindex();
        spinner.css('visibility', 'hidden');
      }, 'json');
    });
  },
  remove: function remove() {
    $(document.body).on('click', '.orderitems .remove-item, .orderadjustments .remove-item', function (e) {
      e.preventDefault();
      var $this = $(this),
          tbody = $this.parents('tbody');
      $this.parents('tr').remove();

      if (1 === $('tr', tbody).length) {
        $('.no-items', tbody).show();
      }

      EDD_Add_Order.update_totals();
      EDD_Add_Order.reindex();
      return false;
    });
  },
  fetch_addresses: function fetch_addresses() {
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


        edd_admin_globals.customer_address_ajax_result = data;

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
  },
  select_address: function select_address() {
    $(document.body).on('change', '.customer-address-select-wrap .add-order-customer-address-select', function () {
      var $this = $(this),
          val = $this.val(),
          select = $('#edd-add-order-form select#edd_order_address_country'),
          address = edd_admin_globals.customer_address_ajax_result.addresses[val];
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
    });
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
      }).done(function (response) {
        EDD_Add_Order.recalculate_taxes();
      });
      return false;
    });
    $('.edd-order-address-region').on('change', function () {
      EDD_Add_Order.recalculate_taxes();
    });
  },
  reindex: function reindex() {
    var key = 0;
    $('.orderitems tbody tr:not(.no-items), .orderadjustments tbody tr:not(.no-items)').each(function () {
      $(this).attr('data-key', key);
      $(this).find('input').each(function () {
        var name = $(this).attr('name');

        if (name) {
          name = name.replace(/\[(\d+)\]/, '[' + parseInt(key) + ']');
          $(this).attr('name', name);
        }
      });
      key++;
    });
  },
  recalculate_taxes: function recalculate_taxes() {
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
          var amount = parseFloat($('.amount .value', this).text());
          var quantity = $('.quantity .value', this).length > 0 ? parseFloat($('.column-quantity .value', this).text()) : 1;
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
          $('.tax .value', this).text(tax.toLocaleString(storeCurrency, {
            style: 'decimal',
            currency: storeCurrency,
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces
          }));
          $('.total .value', this).text(total.toLocaleString(storeCurrency, {
            style: 'decimal',
            currency: storeCurrency,
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces
          }));
        });
      }
    }, 'json').done(function () {
      $('#publishing-action .spinner').css('visibility', 'hidden');
      EDD_Add_Order.update_totals();
    });
  },
  recalculate_total: function recalculate_total() {
    $('#edd-add-order').on('click', '#edd-order-recalc-total', function () {
      EDD_Add_Order.update_totals();
    });
  },
  update_totals: function update_totals() {
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
      item_amount = parseFloat(row.find('.amount input').val());

      if (row.find('.quantity').length) {
        item_quantity = parseFloat(row.find('.quantity input').val());
      }

      subtotal += item_amount * item_quantity;

      if (row.find('.tax').length) {
        item_tax = parseFloat(row.find('.tax input').val());

        if (!isNaN(item_tax) && !edd_vars.taxes_included) {
          item_amount += item_tax;
          tax += item_tax;
        }
      }

      item_total = item_amount * item_quantity;
      total += item_total;
    });
    $('.orderadjustments tbody tr:not(.no-items)').each(function () {
      var row = $(this),
          type,
          amount = 0;
      type = row.data('adjustment');

      switch (type) {
        case 'credit':
          amount = parseFloat(row.find('input.credit-amount', row).val());
          adjustments += amount;
          total -= amount;
          break;

        case 'discount':
          amount = parseFloat(row.find('input.discount-amount', row).val());

          if ('percent' === row.find('input.discount-type').val()) {
            $('.orderitems tbody tr:not(.no-items)').each(function () {
              var item_amount = $(this).find('.amount .value').text(),
                  quantity = 1;

              if ($(this).find('.quantity').length) {
                quantity = parseFloat($(this).find('.quantity').text());
              }

              item_amount *= quantity;
              var reduction = parseFloat(item_amount / 100 * amount);

              if ($(this).find('.tax').length) {
                var item_tax = parseFloat($(this).find('.tax .value').text()),
                    item_tax_reduction = parseFloat(item_tax / 100 * amount);
                tax -= item_tax_reduction;
                total -= item_tax_reduction;
              }

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

    $(' .edd-order-subtotal .value').html(subtotal.toFixed(edd_vars.currency_decimals));
    $(' .edd-order-discounts .value').html(discounts.toFixed(edd_vars.currency_decimals));
    $(' .edd-order-adjustments .value').html(adjustments.toFixed(edd_vars.currency_decimals));
    $(' .edd-order-taxes .value').html(tax.toFixed(edd_vars.currency_decimals));
    $(' .edd-order-total .value ').html(total.toFixed(edd_vars.currency_decimals));
  },
  validate: function validate() {
    $('#edd-add-order-form').on('submit', function () {
      $('#publishing-action .spinner').css('visibility', 'visible');

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

      if ($('.notice').is(':visible')) {
        $('#publishing-action .spinner').css('visibility', 'hidden');
        return false;
      }
    });
  }
};
jQuery(document).ready(function ($) {
  EDD_Add_Order.init();
});
/* harmony default export */ __webpack_exports__["default"] = (EDD_Add_Order);

/***/ }),

/***/ "./assets/js/admin/orders/override-amounts.js":
/*!****************************************************!*\
  !*** ./assets/js/admin/orders/override-amounts.js ***!
  \****************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _index_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./index.js */ "./assets/js/admin/orders/index.js");
/* global _ */

/**
 * Internal dependencies.
 */


(function () {
  var toggle = document.querySelector('.edd-override');

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
      return el.addEventListener('keyup', _index_js__WEBPACK_IMPORTED_MODULE_0__["default"].update_totals);
    }); // Update on addition.


    _index_js__WEBPACK_IMPORTED_MODULE_0__["default"].update_totals(); // Keep toggle disabled if necesseary.

    toggle.disabled = 1 == isOverrideableEl.value;
  });
  /**
   * Allow edits.
   */

  toggle.addEventListener('click', function () {
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
})();

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

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-orders.js.map