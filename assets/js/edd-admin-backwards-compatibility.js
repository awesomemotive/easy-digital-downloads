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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/backwards-compatibility.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/backwards-compatibility.js":
/*!****************************************************!*\
  !*** ./assets/js/admin/backwards-compatibility.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/* global edd_backcompat_vars */

/**
 * Developer Notice: The contents of this JavaScript file are not to be relied on in any future versions of EDD
 * These exist as a backwards compatibility measure for https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2704
 */
jQuery(document).ready(function ($) {
  // Adjust location of setting labels for settings in the new containers created below (back compat)
  $(document.body).find('.edd-custom-price-option-sections .edd-legacy-setting-label').each(function () {
    $(this).prependTo($(this).nextAll('span:not(:has(>.edd-legacy-setting-label))').first());
  }); // Build HTML containers for existing price option settings (back compat)

  $(document.body).find('.edd-custom-price-option-sections').each(function () {
    $(this).find('[class*="purchase_limit"]').wrapAll('<div class="edd-purchase-limit-price-option-settings-legacy edd-custom-price-option-section"></div>');
    $(this).find('[class*="shipping"]').wrapAll('<div class="edd-simple-shipping-price-option-settings-legacy edd-custom-price-option-section" style="display: none;"></div>');
    $(this).find('[class*="sl-"]').wrapAll('<div class="edd-sl-price-option-settings-legacy edd-custom-price-option-section"></div>');
    $(this).find('[class*="edd-recurring-"]').wrapAll('<div class="edd-recurring-price-option-settings-legacy edd-custom-price-option-section"></div>');
  }); // only display Simple Shipping/Software Licensing sections if enabled (back compat)

  $(document.body).find('#edd_enable_shipping', '#edd_license_enabled').each(function () {
    var variable_pricing = $('#edd_variable_pricing').is(':checked');
    var ss_checked = $('#edd_enable_shipping').is(':checked');
    var ss_section = $('.edd-simple-shipping-price-option-settings-legacy');
    var sl_checked = $('#edd_license_enabled').is(':checked');
    var sl_section = $('.edd-sl-price-option-settings-legacy');

    if (variable_pricing) {
      if (ss_checked) {
        ss_section.show();
      } else {
        ss_section.hide();
      }

      if (sl_checked) {
        sl_section.show();
      } else {
        sl_section.hide();
      }
    }
  });
  $('#edd_enable_shipping').on('change', function () {
    var enabled = $(this).is(':checked');
    var section = $('.edd-simple-shipping-price-option-settings-legacy');

    if (enabled) {
      section.show();
    } else {
      section.hide();
    }
  });
  $('#edd_license_enabled').on('change', function () {
    var enabled = $(this).is(':checked');
    var section = $('.edd-sl-price-option-settings-legacy');

    if (enabled) {
      section.show();
    } else {
      section.hide();
    }
  }); // Create section titles for newly created HTML containers (back compat)

  $(document.body).find('.edd-purchase-limit-price-option-settings-legacy').each(function () {
    $(this).prepend('<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.purchase_limit_settings + '</span>');
  });
  $(document.body).find('.edd-simple-shipping-price-option-settings-legacy').each(function () {
    $(this).prepend('<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.simple_shipping_settings + '</span>');
  });
  $(document.body).find('.edd-sl-price-option-settings-legacy').each(function () {
    $(this).prepend('<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.software_licensing_settings + '</span>');
  });
  $(document.body).find('.edd-recurring-price-option-settings-legacy').each(function () {
    $(this).prepend('<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.recurring_payments_settings + '</span>');
  });
});

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-backwards-compatibility.js.map