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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/tools/export/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/tools/export/index.js":
/*!***********************************************!*\
  !*** ./assets/js/admin/tools/export/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Export screen JS
 */
var EDD_Export = {
  init: function init() {
    this.submit();
  },
  submit: function submit() {
    var self = this;
    $(document.body).on('submit', '.edd-export-form', function (e) {
      e.preventDefault();
      var form = $(this),
          submitButton = form.find('input[type="submit"]').first();

      if (submitButton.hasClass('button-disabled') || submitButton.is(':disabled')) {
        return;
      }

      var data = form.serialize();
      submitButton.addClass('button-disabled');
      form.find('.notice-wrap').remove();
      form.append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="edd-progress"><div></div></div></div>'); // start the process

      self.process_step(1, data, self);
    });
  },
  process_step: function process_step(step, data, self) {
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        form: data,
        action: 'edd_do_ajax_export',
        step: step
      },
      dataType: "json",
      success: function success(response) {
        if ('done' === response.step || response.error || response.success) {
          // We need to get the actual in progress form, not all forms on the page
          var export_form = $('.edd-export-form').find('.edd-progress').parent().parent();
          var notice_wrap = export_form.find('.notice-wrap');
          export_form.find('.button-disabled').removeClass('button-disabled');

          if (response.error) {
            var error_message = response.message;
            notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');
          } else if (response.success) {
            var success_message = response.message;
            notice_wrap.html('<div id="edd-batch-success" class="updated notice"><p>' + success_message + '</p></div>');
          } else {
            notice_wrap.remove();
            window.location = response.url;
          }
        } else {
          $('.edd-progress div').animate({
            width: response.percentage + '%'
          }, 50, function () {// Animation complete.
          });
          self.process_step(parseInt(response.step), data, self);
        }
      }
    }).fail(function (response) {
      if (window.console && window.console.log) {
        console.log(response);
      }
    });
  }
};
jQuery(document).ready(function ($) {
  EDD_Export.init();
});

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-tools-export.js.map