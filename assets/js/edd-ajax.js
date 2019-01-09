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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/frontend/edd-ajax.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/frontend/checkout/index.js":
/*!**********************************************!*\
  !*** ./assets/js/frontend/checkout/index.js ***!
  \**********************************************/
/*! exports provided: recalculate_taxes */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "recalculate_taxes", function() { return recalculate_taxes; });
/* harmony import */ var core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.function.name */ "./node_modules/core-js/modules/es6.function.name.js");
/* harmony import */ var core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_function_name__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es6.array.find */ "./node_modules/core-js/modules/es6.array.find.js");
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils.js */ "./assets/js/frontend/checkout/utils.js");



/**
 * Internal dependencies.
 */

/* global edd_global_vars */

var ajax_tax_count = 0;
/**
 * Recalulate taxes.
 *
 * @param {string} state State to calculate taxes for.
 * @return {Promise}
 */

function recalculate_taxes(state) {
  if ('1' != edd_global_vars.taxes_enabled) return; // Taxes not enabled

  var $edd_cc_address = jQuery('#edd_cc_address');
  var billing_country = $edd_cc_address.find('#billing_country').val(),
      card_address = $edd_cc_address.find('#card_address').val(),
      card_address_2 = $edd_cc_address.find('#card_address_2').val(),
      card_city = $edd_cc_address.find('#card_city').val(),
      card_state = $edd_cc_address.find('#card_state').val(),
      card_zip = $edd_cc_address.find('#card_zip').val();

  if (!state) {
    state = card_state;
  }

  var postData = {
    action: 'edd_recalculate_taxes',
    card_address: card_address,
    card_address_2: card_address_2,
    card_city: card_city,
    card_zip: card_zip,
    state: state,
    billing_country: billing_country,
    nonce: jQuery('#edd-checkout-address-fields-nonce').val()
  };
  jQuery('#edd_purchase_submit [type=submit]').after('<span class="edd-loading-ajax edd-recalculate-taxes-loading edd-loading"></span>');
  var current_ajax_count = ++ajax_tax_count;
  return jQuery.ajax({
    type: "POST",
    data: postData,
    dataType: "json",
    url: edd_global_vars.ajaxurl,
    xhrFields: {
      withCredentials: true
    },
    success: function success(tax_response) {
      // Only update tax info if this response is the most recent ajax call.
      // Avoids bug with form autocomplete firing multiple ajax calls at the same time and not
      // being able to predict the call response order.
      if (current_ajax_count === ajax_tax_count) {
        jQuery('#edd_checkout_cart_form').replaceWith(tax_response.html);
        jQuery('.edd_cart_amount').html(tax_response.total);

        var _tax_data = new Object();

        _tax_data.postdata = postData;
        _tax_data.response = tax_response;
        jQuery('body').trigger('edd_taxes_recalculated', [_tax_data]);
      }

      jQuery('.edd-recalculate-taxes-loading').remove();
    }
  }).fail(function (data) {
    if (window.console && window.console.log) {
      console.log(data);

      if (current_ajax_count === ajax_tax_count) {
        jQuery('body').trigger('edd_taxes_recalculated', [tax_data]);
      }
    }
  });
} // Backwards compatibility. Assign function to global namespace.

window.recalculate_taxes = recalculate_taxes;

window.EDD_Checkout = function ($) {
  'use strict';

  var $body, $form, $edd_cart_amount, before_discount, $checkout_form_wrap;

  function init() {
    $body = $(document.body);
    $form = $("#edd_purchase_form");
    $edd_cart_amount = $('.edd_cart_amount');
    before_discount = $edd_cart_amount.text();
    $checkout_form_wrap = $('#edd_checkout_form_wrap');
    $body.on('edd_gateway_loaded', function (e) {
      edd_format_card_number($form);
    });
    $body.on('keyup change', '.edd-do-validate .card-number', function () {
      edd_validate_card($(this));
    });
    $body.on('blur change', '.card-name', function () {
      var name_field = $(this);
      name_field.validateCreditCard(function (result) {
        if (result.card_type != null) {
          name_field.removeClass('valid').addClass('error');
          $('#edd-purchase-button').attr('disabled', 'disabled');
        } else {
          name_field.removeClass('error').addClass('valid');
          $('#edd-purchase-button').removeAttr('disabled');
        }
      });
    }); // Make sure a gateway is selected

    $body.on('submit', '#edd_payment_mode', function () {
      var gateway = $('#edd-gateway option:selected').val();

      if (gateway == 0) {
        alert(edd_global_vars.no_gateway);
        return false;
      }
    }); // Add a class to the currently selected gateway on click

    $body.on('click', '#edd_payment_mode_select input', function () {
      $('#edd_payment_mode_select label.edd-gateway-option-selected').removeClass('edd-gateway-option-selected');
      $('#edd_payment_mode_select input:checked').parent().addClass('edd-gateway-option-selected');
    }); // Validate and apply a discount

    $checkout_form_wrap.on('click', '.edd-apply-discount', apply_discount); // Prevent the checkout form from submitting when hitting Enter in the discount field

    $checkout_form_wrap.on('keypress', '#edd-discount', function (event) {
      if (event.keyCode == '13') {
        return false;
      }
    }); // Apply the discount when hitting Enter in the discount field instead

    $checkout_form_wrap.on('keyup', '#edd-discount', function (event) {
      if (event.keyCode == '13') {
        $checkout_form_wrap.find('.edd-apply-discount').trigger('click');
      }
    }); // Remove a discount

    $body.on('click', '.edd_discount_remove', remove_discount); // When discount link is clicked, hide the link, then show the discount input and set focus.

    $body.on('click', '.edd_discount_link', function (e) {
      e.preventDefault();
      $('.edd_discount_link').parent().hide();
      $('#edd-discount-code-wrap').show().find('#edd-discount').focus();
    }); // Hide / show discount fields for browsers without javascript enabled

    $body.find('#edd-discount-code-wrap').hide();
    $body.find('#edd_show_discount').show(); // Update the checkout when item quantities are updated

    $body.on('change', '.edd-item-quantity', update_item_quantities);
    $body.on('click', '.edd-amazon-logout #Logout', function (e) {
      e.preventDefault();
      amazon.Login.logout();
      window.location = edd_amazon.checkoutUri;
    });
  }

  function edd_validate_card(field) {
    var card_field = field;
    card_field.validateCreditCard(function (result) {
      var $card_type = $('.card-type');

      if (result.card_type == null) {
        $card_type.removeClass().addClass('off card-type');
        card_field.removeClass('valid');
        card_field.addClass('error');
      } else {
        $card_type.removeClass('off');
        $card_type.html(Object(_utils_js__WEBPACK_IMPORTED_MODULE_2__["getCreditCardIcon"])(result.card_type.name));
        $card_type.addClass(result.card_type.name);

        if (result.length_valid && result.luhn_valid) {
          card_field.addClass('valid');
          card_field.removeClass('error');
        } else {
          card_field.removeClass('valid');
          card_field.addClass('error');
        }
      }
    });
  }

  function edd_format_card_number(form) {
    var card_number = form.find('.card-number'),
        card_cvc = form.find('.card-cvc'),
        card_expiry = form.find('.card-expiry');

    if (card_number.length && 'function' === typeof card_number.payment) {
      card_number.payment('formatCardNumber');
      card_cvc.payment('formatCardCVC');
      card_expiry.payment('formatCardExpiry');
    }
  }

  function apply_discount(event) {
    event.preventDefault();
    var $this = $(this),
        discount_code = $('#edd-discount').val(),
        edd_discount_loader = $('#edd-discount-loader');

    if (discount_code == '' || discount_code == edd_global_vars.enter_discount) {
      return false;
    }

    var postData = {
      action: 'edd_apply_discount',
      code: discount_code,
      form: $('#edd_purchase_form').serialize()
    };
    $('#edd-discount-error-wrap').html('').hide();
    edd_discount_loader.show();
    $.ajax({
      type: "POST",
      data: postData,
      dataType: "json",
      url: edd_global_vars.ajaxurl,
      xhrFields: {
        withCredentials: true
      },
      success: function success(discount_response) {
        if (discount_response) {
          if (discount_response.msg == 'valid') {
            $('.edd_cart_discount').html(discount_response.html);
            $('.edd_cart_discount_row').show();
            $('.edd_cart_amount').each(function () {
              // Format discounted amount for display.
              $(this).text(discount_response.total); // Set data attribute to new (unformatted) discounted amount.'

              $(this).data('total', discount_response.total_plain);
            });
            $('#edd-discount', $checkout_form_wrap).val('');
            recalculate_taxes();
            var inputs = $('#edd_cc_fields .edd-input, #edd_cc_fields .edd-select,#edd_cc_address .edd-input, #edd_cc_address .edd-select,#edd_payment_mode_select .edd-input, #edd_payment_mode_select .edd-select');

            if ('0.00' == discount_response.total_plain) {
              $('#edd_cc_fields,#edd_cc_address,#edd_payment_mode_select').slideUp();
              inputs.removeAttr('required');
              $('input[name="edd-gateway"]').val('manual');
            } else {
              if (!inputs.is('.card-address-2')) {
                inputs.attr('required', 'required');
              }

              $('#edd_cc_fields,#edd_cc_address').slideDown();
            }

            $body.trigger('edd_discount_applied', [discount_response]);
          } else {
            $('#edd-discount-error-wrap').html('<span class="edd_error">' + discount_response.msg + '</span>');
            $('#edd-discount-error-wrap').show();
            $body.trigger('edd_discount_invalid', [discount_response]);
          }
        } else {
          if (window.console && window.console.log) {
            console.log(discount_response);
          }

          $body.trigger('edd_discount_failed', [discount_response]);
        }

        edd_discount_loader.hide();
      }
    }).fail(function (data) {
      if (window.console && window.console.log) {
        console.log(data);
      }
    });
    return false;
  }

  function remove_discount(event) {
    var $this = $(this),
        postData = {
      action: 'edd_remove_discount',
      code: $this.data('code')
    };
    $.ajax({
      type: "POST",
      data: postData,
      dataType: "json",
      url: edd_global_vars.ajaxurl,
      xhrFields: {
        withCredentials: true
      },
      success: function success(discount_response) {
        var zero = '0' + edd_global_vars.decimal_separator + '00';
        $('.edd_cart_amount').each(function () {
          if (edd_global_vars.currency_sign + zero == $(this).text() || zero + edd_global_vars.currency_sign == $(this).text()) {
            // We're removing a 100% discount code so we need to force the payment gateway to reload
            window.location.reload();
          } // Format discounted amount for display.


          $(this).text(discount_response.total); // Set data attribute to new (unformatted) discounted amount.'

          $(this).data('total', discount_response.total_plain);
        });
        $('.edd_cart_discount').html(discount_response.html);

        if (!discount_response.discounts) {
          $('.edd_cart_discount_row').hide();
        }

        recalculate_taxes();
        $('#edd_cc_fields,#edd_cc_address').slideDown();
        $body.trigger('edd_discount_removed', [discount_response]);
      }
    }).fail(function (data) {
      if (window.console && window.console.log) {
        console.log(data);
      }
    });
    return false;
  }

  function update_item_quantities(event) {
    var $this = $(this),
        quantity = $this.val(),
        key = $this.data('key'),
        download_id = $this.closest('.edd_cart_item').data('download-id'),
        options = $this.parent().find('input[name="edd-cart-download-' + key + '-options"]').val();
    var edd_cc_address = $('#edd_cc_address');
    var billing_country = edd_cc_address.find('#billing_country').val(),
        card_state = edd_cc_address.find('#card_state').val();
    var postData = {
      action: 'edd_update_quantity',
      quantity: quantity,
      download_id: download_id,
      options: options,
      billing_country: billing_country,
      card_state: card_state
    }; //edd_discount_loader.show();

    $.ajax({
      type: "POST",
      data: postData,
      dataType: "json",
      url: edd_global_vars.ajaxurl,
      xhrFields: {
        withCredentials: true
      },
      success: function success(response) {
        $('.edd_cart_subtotal_amount').each(function () {
          $(this).text(response.subtotal);
        });
        $('.edd_cart_tax_amount').each(function () {
          $(this).text(response.taxes);
        });
        $('.edd_cart_amount').each(function () {
          $(this).text(response.total);
          $body.trigger('edd_quantity_updated', [response]);
        });
      }
    }).fail(function (data) {
      if (window.console && window.console.log) {
        console.log(data);
      }
    });
    return false;
  } // Expose some functions or variables to window.EDD_Checkout object


  return {
    init: init,
    recalculate_taxes: recalculate_taxes
  };
}(window.jQuery); // init on document.ready


window.jQuery(document).ready(EDD_Checkout.init);

/***/ }),

/***/ "./assets/js/frontend/checkout/utils.js":
/*!**********************************************!*\
  !*** ./assets/js/frontend/checkout/utils.js ***!
  \**********************************************/
/*! exports provided: getCreditCardIcon */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getCreditCardIcon", function() { return getCreditCardIcon; });
/**
 * Generate markup for a credit card icon based on a passed type.
 *
 * @param {String} type Credit card type.
 * @return HTML markup.
 */
var getCreditCardIcon = function getCreditCardIcon(type) {
  var width;
  var name = type;

  switch (type) {
    case 'amex':
      name = 'americanexpress';
      width = 32;
      break;

    default:
      width = 50;
      break;
  }

  return "\n    <svg\n      width=".concat(width, "\n      height=", 32, "\n      class=\"payment-icon icon-").concat(name, "\"\n      role=\"img\"\n    >\n      <use\n        href=\"#icon-").concat(name, "\"\n        xlink:href=\"#icon-").concat(name, "\">\n      </use>\n    </svg>");
};

/***/ }),

/***/ "./assets/js/frontend/edd-ajax.js":
/*!****************************************!*\
  !*** ./assets/js/frontend/edd-ajax.js ***!
  \****************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.array.find */ "./node_modules/core-js/modules/es6.array.find.js");
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _checkout__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./checkout */ "./assets/js/frontend/checkout/index.js");


/* global edd_scripts, edd_global_vars */

/**
 * Internal dependencies.
 */

jQuery(document).ready(function ($) {
  // Hide unneeded elements. These are things that are required in case JS breaks or isn't present
  $('.edd-no-js').hide();
  $('a.edd-add-to-cart').addClass('edd-has-js'); // Send Remove from Cart requests

  $(document.body).on('click.eddRemoveFromCart', '.edd-remove-from-cart', function (event) {
    var $this = $(this),
        item = $this.data('cart-item'),
        action = $this.data('action'),
        id = $this.data('download-id'),
        nonce = $this.data('nonce'),
        data = {
      action: action,
      cart_item: item,
      nonce: nonce
    };
    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: edd_scripts.ajaxurl,
      xhrFields: {
        withCredentials: true
      },
      success: function success(response) {
        if (response.removed) {
          if (parseInt(edd_scripts.position_in_cart, 10) === parseInt(item, 10) || edd_scripts.has_purchase_links) {
            window.location = window.location;
            return false;
          } // Remove the selected cart item


          $('.edd-cart').each(function () {
            $(this).find("[data-cart-item='" + item + "']").parent().remove();
          }); //Reset the data-cart-item attributes to match their new values in the EDD session cart array

          $('.edd-cart').each(function () {
            var cart_item_counter = 0;
            $(this).find("[data-cart-item]").each(function () {
              $(this).attr('data-cart-item', cart_item_counter);
              cart_item_counter = cart_item_counter + 1;
            });
          }); // Check to see if the purchase form(s) for this download is present on this page

          if ($('[id^=edd_purchase_' + id + ']').length) {
            $('[id^=edd_purchase_' + id + '] .edd_go_to_checkout').hide();
            $('[id^=edd_purchase_' + id + '] a.edd-add-to-cart').show().removeAttr('data-edd-loading');

            if (edd_scripts.quantities_enabled === '1') {
              $('[id^=edd_purchase_' + id + '] .edd_download_quantity_wrapper').show();
            }
          }

          $('span.edd-cart-quantity').text(response.cart_quantity);
          $(document.body).trigger('edd_quantity_updated', [response.cart_quantity]);

          if (edd_scripts.taxes_enabled) {
            $('.cart_item.edd_subtotal span').html(response.subtotal);
            $('.cart_item.edd_cart_tax span').html(response.tax);
          }

          $('.cart_item.edd_total span').html(response.total);

          if (response.cart_quantity === 0) {
            $('.cart_item.edd_subtotal,.edd-cart-number-of-items,.cart_item.edd_checkout,.cart_item.edd_cart_tax,.cart_item.edd_total').hide();
            $('.edd-cart').each(function () {
              var cart_wrapper = $(this).parent();

              if (cart_wrapper) {
                cart_wrapper.addClass('cart-empty');
                cart_wrapper.removeClass('cart-not-empty');
              }

              $(this).append('<li class="cart_item empty">' + edd_scripts.empty_cart_message + '</li>');
            });
          }

          $(document.body).trigger('edd_cart_item_removed', [response]);
        }
      }
    }).fail(function (response) {
      if (window.console && window.console.log) {
        console.log(response);
      }
    }).done(function (response) {});
    return false;
  }); // Send Add to Cart request

  $(document.body).on('click.eddAddToCart', '.edd-add-to-cart', function (e) {
    e.preventDefault();
    var $this = $(this),
        form = $this.closest('form'); // Disable button, preventing rapid additions to cart during ajax request

    $this.prop('disabled', true);
    var $spinner = $this.find('.edd-loading');
    var container = $this.closest('div'); // Show the spinner

    $this.attr('data-edd-loading', '');
    var form = $this.parents('form').last();
    var download = $this.data('download-id');
    var variable_price = $this.data('variable-price');
    var price_mode = $this.data('price-mode');
    var nonce = $this.data('nonce');
    var item_price_ids = [];
    var free_items = true;

    if (variable_price === 'yes') {
      if (form.find('.edd_price_option_' + download + '[type="hidden"]').length > 0) {
        item_price_ids[0] = $('.edd_price_option_' + download, form).val();

        if (form.find('.edd-submit').data('price') && form.find('.edd-submit').data('price') > 0) {
          free_items = false;
        }
      } else {
        if (!form.find('.edd_price_option_' + download + ':checked', form).length) {
          // hide the spinner
          $this.removeAttr('data-edd-loading');
          alert(edd_scripts.select_option);
          e.stopPropagation();
          $this.prop('disabled', false);
          return false;
        }

        form.find('.edd_price_option_' + download + ':checked', form).each(function (index) {
          item_price_ids[index] = $(this).val(); // If we're still only at free items, check if this one is free also

          if (true === free_items) {
            var item_price = $(this).data('price');

            if (item_price && item_price > 0) {
              // We now have a paid item, we can't use add_to_cart
              free_items = false;
            }
          }
        });
      }
    } else {
      item_price_ids[0] = download;

      if ($this.data('price') && $this.data('price') > 0) {
        free_items = false;
      }
    } // If we've got nothing but free items being added, change to add_to_cart


    if (free_items) {
      form.find('.edd_action_input').val('add_to_cart');
    }

    if ('straight_to_gateway' === form.find('.edd_action_input').val()) {
      form.submit();
      return true; // Submit the form
    }

    var action = $this.data('action');
    var data = {
      action: action,
      download_id: download,
      price_ids: item_price_ids,
      post_data: $(form).serialize(),
      nonce: nonce
    };
    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: edd_scripts.ajaxurl,
      xhrFields: {
        withCredentials: true
      },
      success: function success(response) {
        var store_redirect = edd_scripts.redirect_to_checkout === '1';
        var item_redirect = form.find('#edd_redirect_to_checkout').val() === '1';

        if (store_redirect && item_redirect || !store_redirect && item_redirect) {
          window.location = edd_scripts.checkout_page;
        } else {
          // Add the new item to the cart widget
          if (edd_scripts.taxes_enabled === '1') {
            $('.cart_item.edd_subtotal').show();
            $('.cart_item.edd_cart_tax').show();
          }

          $('.cart_item.edd_total').show();
          $('.cart_item.edd_checkout').show();

          if ($('.cart_item.empty').length) {
            $('.cart_item.empty').hide();
          }

          $('.widget_edd_cart_widget .edd-cart').each(function (cart) {
            var target = $(this).find('.edd-cart-meta:first');
            $(response.cart_item).insertBefore(target);
            var cart_wrapper = $(this).parent();

            if (cart_wrapper) {
              cart_wrapper.addClass('cart-not-empty');
              cart_wrapper.removeClass('cart-empty');
            }
          }); // Update the totals

          if (edd_scripts.taxes_enabled === '1') {
            $('.edd-cart-meta.edd_subtotal span').html(response.subtotal);
            $('.edd-cart-meta.edd_cart_tax span').html(response.tax);
          }

          $('.edd-cart-meta.edd_total span').html(response.total); // Update the cart quantity

          var items_added = $('.edd-cart-item-title', response.cart_item).length;
          $('span.edd-cart-quantity').each(function () {
            $(this).text(response.cart_quantity);
            $(document.body).trigger('edd_quantity_updated', [response.cart_quantity]);
          }); // Show the "number of items in cart" message

          if ($('.edd-cart-number-of-items').css('display') === 'none') {
            $('.edd-cart-number-of-items').show('slow');
          }

          if (variable_price === 'no' || price_mode !== 'multi') {
            // Switch purchase to checkout if a single price item or variable priced with radio buttons
            $('a.edd-add-to-cart', container).toggle();
            $('.edd_go_to_checkout', container).css('display', 'inline-block');
          }

          if (price_mode === 'multi') {
            // remove spinner for multi
            $this.removeAttr('data-edd-loading');
          } // Update all buttons for same download


          if ($('.edd_download_purchase_form').length && (variable_price === 'no' || !form.find('.edd_price_option_' + download).is('input:hidden'))) {
            var parent_form = $('.edd_download_purchase_form *[data-download-id="' + download + '"]').parents('form');
            $('a.edd-add-to-cart', parent_form).hide();

            if (price_mode !== 'multi') {
              parent_form.find('.edd_download_quantity_wrapper').slideUp();
            }

            $('.edd_go_to_checkout', parent_form).show().removeAttr('data-edd-loading');
          }

          if (response !== 'incart') {
            // Show the added message
            $('.edd-cart-added-alert', container).fadeIn();
            setTimeout(function () {
              $('.edd-cart-added-alert', container).fadeOut();
            }, 3000);
          } // Re-enable the add to cart button


          $this.prop('disabled', false);
          $(document.body).trigger('edd_cart_item_added', [response]);
        }
      }
    }).fail(function (response) {
      if (window.console && window.console.log) {
        console.log(response);
      }
    }).done(function (response) {});
    return false;
  }); // Show the login form on the checkout page

  $('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function () {
    var $this = $(this),
        data = {
      action: $this.data('action'),
      nonce: $this.data('nonce')
    }; // Show the ajax loader

    $('.edd-cart-ajax').show();
    $.post(edd_scripts.ajaxurl, data, function (checkout_response) {
      $('#edd_checkout_login_register').html(edd_scripts.loading);
      $('#edd_checkout_login_register').html(checkout_response); // Hide the ajax loader

      $('.edd-cart-ajax').hide();
    });
    return false;
  }); // Process the login form via ajax

  $(document).on('click', '#edd_purchase_form #edd_login_fields input[type=submit]', function (e) {
    e.preventDefault();
    var complete_purchase_val = $(this).val();
    $(this).val(edd_global_vars.purchase_loading);
    $(this).after('<span class="edd-loading-ajax edd-loading"></span>');
    var data = {
      action: 'edd_process_checkout_login',
      edd_ajax: 1,
      edd_user_login: $('#edd_login_fields #edd_user_login').val(),
      edd_user_pass: $('#edd_login_fields #edd_user_pass').val(),
      edd_login_nonce: $('#edd_login_nonce').val()
    };
    $.post(edd_global_vars.ajaxurl, data, function (data) {
      if ($.trim(data) === 'success') {
        $('.edd_errors').remove();
        window.location = edd_scripts.checkout_page;
      } else {
        $('#edd_login_fields input[type=submit]').val(complete_purchase_val);
        $('.edd-loading-ajax').remove();
        $('.edd_errors').remove();
        $('#edd-user-login-submit').before(data);
      }
    });
  }); // Load the fields for the selected payment method

  $('select#edd-gateway, input.edd-gateway').change(function (e) {
    var payment_mode = $('#edd-gateway option:selected, input.edd-gateway:checked').val();

    if (payment_mode === '0') {
      return false;
    }

    edd_load_gateway(payment_mode);
    return false;
  }); // Auto load first payment gateway

  if (edd_scripts.is_checkout === '1') {
    var chosen_gateway = false;
    var ajax_needed = false;

    if ($('select#edd-gateway, input.edd-gateway').length) {
      chosen_gateway = $("meta[name='edd-chosen-gateway']").attr('content');
      ajax_needed = true;
    }

    if (!chosen_gateway) {
      chosen_gateway = edd_scripts.default_gateway;
    }

    if (ajax_needed) {
      // If we need to ajax in a gateway form, send the requests for the POST.
      setTimeout(function () {
        edd_load_gateway(chosen_gateway);
      }, 200);
    } else {
      // The form is already on page, just trigger that the gateway is loaded so further action can be taken.
      $('body').trigger('edd_gateway_loaded', [chosen_gateway]);
    }
  } // Process checkout


  $(document).on('click', '#edd_purchase_form #edd_purchase_submit [type=submit]', function (e) {
    var eddPurchaseform = document.getElementById('edd_purchase_form');

    if (typeof eddPurchaseform.checkValidity === "function" && false === eddPurchaseform.checkValidity()) {
      return;
    }

    e.preventDefault();
    var complete_purchase_val = $(this).val();
    $(this).val(edd_global_vars.purchase_loading);
    $(this).prop('disabled', true);
    $(this).after('<span class="edd-loading-ajax edd-loading"></span>');
    $.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout&edd_ajax=true', function (data) {
      if ($.trim(data) === 'success') {
        $('.edd_errors').remove();
        $('.edd-error').hide();
        $(eddPurchaseform).submit();
      } else {
        $('#edd-purchase-button').val(complete_purchase_val);
        $('.edd-loading-ajax').remove();
        $('.edd_errors').remove();
        $('.edd-error').hide();
        $(edd_global_vars.checkout_error_anchor).before(data);
        $('#edd-purchase-button').prop('disabled', false);
        $(document.body).trigger('edd_checkout_error', [data]);
      }
    });
  }); // Update state field

  $(document.body).on('change', '#edd_cc_address input.card_state, #edd_cc_address select, #edd_address_country', update_state_field);

  function update_state_field() {
    var $this = $(this);
    var $form;
    var is_checkout = typeof edd_global_vars !== 'undefined';
    var field_name = 'card_state';

    if ($(this).attr('id') === 'edd_address_country') {
      field_name = 'edd_address_state';
    }

    if ('card_state' !== $this.attr('id')) {
      var nonce = $(this).data('nonce'); // If the country field has changed, we need to update the state/province field

      var postData = {
        action: 'edd_get_shop_states',
        country: $this.val(),
        field_name: field_name,
        nonce: nonce
      };
      $.ajax({
        type: "POST",
        data: postData,
        url: edd_scripts.ajaxurl,
        xhrFields: {
          withCredentials: true
        },
        success: function success(response) {
          if (is_checkout) {
            $form = $("#edd_purchase_form");
          } else {
            $form = $this.closest("form");
          }

          var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="edd_address_state"], select[name="edd_address_state"]';

          if ('nostates' === $.trim(response)) {
            var text_field = '<input type="text" name="card_state" class="card-state edd-input required" value=""/>';
            $form.find(state_inputs).replaceWith(text_field);
          } else {
            $form.find(state_inputs).replaceWith(response);
          }

          if (is_checkout) {
            $(document.body).trigger('edd_cart_billing_address_updated', [response]);
          }
        }
      }).fail(function (data) {
        if (window.console && window.console.log) {
          console.log(data);
        }
      }).done(function (data) {
        if (is_checkout) {
          Object(_checkout__WEBPACK_IMPORTED_MODULE_1__["recalculate_taxes"])();
        }
      });
    } else if (is_checkout) {
      Object(_checkout__WEBPACK_IMPORTED_MODULE_1__["recalculate_taxes"])();
    }

    return false;
  } // Backwards compatibility. Assign function to global namespace.


  window.update_state_field = update_state_field; // If is_checkout, recalculate sales tax on postalCode change.

  $(document.body).on('change', '#edd_cc_address input[name=card_zip]', function () {
    if (typeof edd_global_vars !== 'undefined') {
      Object(_checkout__WEBPACK_IMPORTED_MODULE_1__["recalculate_taxes"])();
    }
  });
}); // Load a payment gateway

function edd_load_gateway(payment_mode) {
  // Show the ajax loader
  jQuery('.edd-cart-ajax').show();
  jQuery('#edd_purchase_form_wrap').html('<span class="edd-loading-ajax edd-loading"></span>');
  var nonce = jQuery('#edd-gateway-' + payment_mode).data(payment_mode + '-nonce');
  var url = edd_scripts.ajaxurl;

  if (url.indexOf('?') > 0) {
    url = url + '&';
  } else {
    url = url + '?';
  }

  url = url + 'payment-mode=' + payment_mode;
  jQuery.post(url, {
    action: 'edd_load_gateway',
    edd_payment_mode: payment_mode,
    nonce: nonce
  }, function (response) {
    jQuery('#edd_purchase_form_wrap').html(response);
    jQuery('.edd-no-js').hide();
    jQuery('body').trigger('edd_gateway_loaded', [payment_mode]);
  });
} // Backwards compatibility. Assign function to global namespace.


window.edd_load_gateway = edd_load_gateway;

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
//# sourceMappingURL=edd-ajax.js.map