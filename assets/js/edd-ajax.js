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
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "recalculate_taxes", function() { return recalculate_taxes; });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.js */ "./assets/js/frontend/checkout/utils.js");
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
  if ('1' != edd_global_vars.taxes_enabled) {
    return;
  } // Taxes not enabled


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
    type: 'POST',
    data: postData,
    dataType: 'json',
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
    $form = $('#edd_purchase_form');
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
        $card_type.html(Object(_utils_js__WEBPACK_IMPORTED_MODULE_0__["getCreditCardIcon"])(result.card_type.name));
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
      type: 'POST',
      data: postData,
      dataType: 'json',
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
      type: 'POST',
      data: postData,
      dataType: 'json',
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
      type: 'POST',
      data: postData,
      dataType: 'json',
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
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

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
 * @param {string} type Credit card type.
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
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony import */ var _checkout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./checkout */ "./assets/js/frontend/checkout/index.js");
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
      type: 'POST',
      data: data,
      dataType: 'json',
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
            $(this).find('[data-cart-item]').each(function () {
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
      type: 'POST',
      data: data,
      dataType: 'json',
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

    if (typeof eddPurchaseform.checkValidity === 'function' && false === eddPurchaseform.checkValidity()) {
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

    var state_inputs = document.getElementById(field_name);

    if ('card_state' !== $this.attr('id') && null != state_inputs) {
      var nonce = $(this).data('nonce'); // If the country field has changed, we need to update the state/province field

      var postData = {
        action: 'edd_get_shop_states',
        country: $this.val(),
        field_name: field_name,
        nonce: nonce
      };
      $.ajax({
        type: 'POST',
        data: postData,
        url: edd_scripts.ajaxurl,
        xhrFields: {
          withCredentials: true
        },
        success: function success(response) {
          if (is_checkout) {
            $form = $('#edd_purchase_form');
          } else {
            $form = $this.closest('form');
          }

          var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="edd_address_state"], select[name="edd_address_state"]';

          if ('nostates' === $.trim(response)) {
            var text_field = '<input type="text" id="' + field_name + '" name="card_state" class="card-state edd-input required" value=""/>';
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
          Object(_checkout__WEBPACK_IMPORTED_MODULE_0__["recalculate_taxes"])();
        }
      });
    } else if (is_checkout) {
      Object(_checkout__WEBPACK_IMPORTED_MODULE_0__["recalculate_taxes"])();
    }

    return false;
  } // Backwards compatibility. Assign function to global namespace.


  window.update_state_field = update_state_field; // If is_checkout, recalculate sales tax on postalCode change.

  $(document.body).on('change', '#edd_cc_address input[name=card_zip]', function () {
    if (typeof edd_global_vars !== 'undefined') {
      Object(_checkout__WEBPACK_IMPORTED_MODULE_0__["recalculate_taxes"])();
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
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

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
//# sourceMappingURL=edd-ajax.js.map