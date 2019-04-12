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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/customers/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/customers/index.js":
/*!********************************************!*\
  !*** ./assets/js/admin/customers/index.js ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Customer management screen JS
 */
var EDD_Customer = {
  vars: {
    customer_card_wrap_editable: $('#edit-customer-info .editable'),
    customer_card_wrap_edit_item: $('#edit-customer-info .edit-item'),
    user_id: $('input[name="customerinfo[user_id]"]'),
    state_input: $(':input[name="customerinfo[region]"]')
  },
  init: function init() {
    this.edit_customer();
    this.add_email();
    this.user_search();
    this.remove_user();
    this.cancel_edit();
    this.change_country();
    this.delete_checked();
  },
  edit_customer: function edit_customer() {
    $(document.body).on('click', '#edit-customer', function (e) {
      e.preventDefault();
      EDD_Customer.vars.customer_card_wrap_editable.hide();
      EDD_Customer.vars.customer_card_wrap_edit_item.show().css('display', 'block');
    });
  },
  add_email: function add_email() {
    $(document.body).on('click', '#add-customer-email', function (e) {
      e.preventDefault();
      var button = $(this),
          wrapper = button.parent().parent().parent().parent(),
          customer_id = wrapper.find('input[name="customer-id"]').val(),
          email = wrapper.find('input[name="additional-email"]').val(),
          primary = wrapper.find('input[name="make-additional-primary"]').is(':checked'),
          nonce = wrapper.find('input[name="add_email_nonce"]').val(),
          postData = {
        edd_action: 'customer-add-email',
        customer_id: customer_id,
        email: email,
        primary: primary,
        _wpnonce: nonce
      };
      wrapper.parent().find('.notice-container').remove();
      wrapper.find('.spinner').css('visibility', 'visible');
      button.attr('disabled', true);
      $.post(ajaxurl, postData, function (response) {
        setTimeout(function () {
          if (true === response.success) {
            window.location.href = response.redirect;
          } else {
            button.attr('disabled', false);
            wrapper.before('<div class="notice-container"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
            wrapper.find('.spinner').css('visibility', 'hidden');
          }
        }, userInteractionInterval);
      }, 'json');
    });
  },
  user_search: function user_search() {
    // Upon selecting a user from the dropdown, we need to update the User ID
    $(document.body).on('click.eddSelectUser', '.edd_user_search_results a', function (e) {
      e.preventDefault();
      var user_id = $(this).data('userid');
      EDD_Customer.vars.user_id.val(user_id);
    });
  },
  remove_user: function remove_user() {
    $(document.body).on('click', '#disconnect-customer', function (e) {
      e.preventDefault();

      if (confirm(edd_vars.disconnect_customer)) {
        var customer_id = $('input[name="customerinfo[id]"]').val(),
            postData = {
          edd_action: 'disconnect-userid',
          customer_id: customer_id,
          _wpnonce: $('#edit-customer-info #_wpnonce').val()
        };
        $.post(ajaxurl, postData, function (response) {
          // Weird
          window.location.href = window.location.href;
        }, 'json');
      }
    });
  },
  cancel_edit: function cancel_edit() {
    $(document.body).on('click', '#edd-edit-customer-cancel', function (e) {
      e.preventDefault();
      EDD_Customer.vars.customer_card_wrap_edit_item.hide();
      EDD_Customer.vars.customer_card_wrap_editable.show();
      $('.edd_user_search_results').html('');
    });
  },
  change_country: function change_country() {
    $('select[name="customerinfo[country]"]').change(function () {
      var select = $(this),
          data = {
        action: 'edd_get_shop_states',
        country: select.val(),
        nonce: select.data('nonce'),
        field_name: 'customerinfo[region]'
      };
      $.post(ajaxurl, data, function (response) {
        console.log(response);

        if ('nostates' === response) {
          EDD_Customer.vars.state_input.replaceWith('<input type="text" name="' + data.field_name + '" value="" class="edd-edit-toggles medium-text"/>');
        } else {
          EDD_Customer.vars.state_input.replaceWith(response);
        }
      });
      return false;
    });
  },
  delete_checked: function delete_checked() {
    $('#edd-customer-delete-confirm').change(function () {
      var records_input = $('#edd-customer-delete-records');
      var submit_button = $('#edd-delete-customer');

      if ($(this).prop('checked')) {
        records_input.attr('disabled', false);
        submit_button.attr('disabled', false);
      } else {
        records_input.attr('disabled', true);
        records_input.prop('checked', false);
        submit_button.attr('disabled', true);
      }
    });
  }
};
jQuery(document).ready(function ($) {
  EDD_Customer.init();
});

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-customers.js.map