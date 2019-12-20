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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/tools/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/tools/index.js":
/*!****************************************!*\
  !*** ./assets/js/admin/tools/index.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($, jQuery) {/**
 * Tools screen JS
 */
var EDD_Tools = {
  init: function init() {
    this.revoke_api_key();
    this.regenerate_api_key();
    this.create_api_key();
    this.recount_stats();
  },
  revoke_api_key: function revoke_api_key() {
    $(document.body).on('click', '.edd-revoke-api-key', function (e) {
      return confirm(edd_vars.revoke_api_key);
    });
  },
  regenerate_api_key: function regenerate_api_key() {
    $(document.body).on('click', '.edd-regenerate-api-key', function (e) {
      return confirm(edd_vars.regenerate_api_key);
    });
  },
  create_api_key: function create_api_key() {
    $(document.body).on('submit', '#api-key-generate-form', function (e) {
      var input = $('input[type="text"][name="user_id"]');
      input.css('border-color', '#ddd');
      var user_id = input.val();

      if (user_id.length < 1 || user_id === 0) {
        input.css('border-color', '#ff0000');
        return false;
      }
    });
  },
  recount_stats: function recount_stats() {
    $(document.body).on('change', '#recount-stats-type', function () {
      var export_form = $('#edd-tools-recount-form'),
          selected_type = $('option:selected', this).data('type'),
          submit_button = $('#recount-stats-submit'),
          products = $('#tools-product-dropdown'); // Reset the form

      export_form.find('.notice-wrap').remove();
      submit_button.removeClass('button-disabled').attr('disabled', false);
      products.hide();
      $('.edd-recount-stats-descriptions span').hide();

      if ('recount-download' === selected_type) {
        products.show();
        products.find('.edd-select-chosen').css('width', 'auto');
      } else if ('reset-stats' === selected_type) {
        export_form.append('<div class="notice-wrap"></div>');
        var notice_wrap = export_form.find('.notice-wrap');
        notice_wrap.html('<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + edd_vars.reset_stats_warn + '</label></p></div>');
        $('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
      } else {
        products.hide();
        products.val(0);
      }

      $('#' + selected_type).show();
    });
    $(document.body).on('change', '#confirm-reset', function () {
      var checked = $(this).is(':checked');

      if (checked) {
        $('#recount-stats-submit').removeClass('button-disabled').removeAttr('disabled');
      } else {
        $('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
      }
    });
    $('#edd-tools-recount-form').submit(function (e) {
      var selection = $('#recount-stats-type').val(),
          export_form = $(this),
          selected_type = $('option:selected', this).data('type');

      if ('reset-stats' === selected_type) {
        var is_confirmed = $('#confirm-reset').is(':checked');

        if (is_confirmed) {
          return true;
        }

        has_errors = true;
      }

      export_form.find('.notice-wrap').remove();
      export_form.append('<div class="notice-wrap"></div>');
      var notice_wrap = export_form.find('.notice-wrap'),
          has_errors = false;

      if (null === selection || 0 === selection) {
        // Needs to pick a method edd_vars.batch_export_no_class
        notice_wrap.html('<div class="updated error"><p>' + edd_vars.batch_export_no_class + '</p></div>');
        has_errors = true;
      }

      if ('recount-download' === selected_type) {
        var selected_download = $('select[name="download_id"]').val();

        if (selected_download === 0) {
          // Needs to pick download edd_vars.batch_export_no_reqs
          notice_wrap.html('<div class="updated error"><p>' + edd_vars.batch_export_no_reqs + '</p></div>');
          has_errors = true;
        }
      }

      if (has_errors) {
        export_form.find('.button-disabled').removeClass('button-disabled');
        return false;
      }
    });
  }
};
jQuery(document).ready(function ($) {
  EDD_Tools.init();
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery"), __webpack_require__(/*! jquery */ "jquery")))

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
//# sourceMappingURL=edd-admin-tools.js.map