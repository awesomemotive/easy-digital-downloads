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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/downloads/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/components/tooltips/index.js":
/*!******************************************************!*\
  !*** ./assets/js/admin/components/tooltips/index.js ***!
  \******************************************************/
/*! exports provided: edd_attach_tooltips */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($, jQuery) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "edd_attach_tooltips", function() { return edd_attach_tooltips; });
/**
 * Attach tooltips
 *
 * @param {string} selector
 */
var edd_attach_tooltips = function edd_attach_tooltips(selector) {
  selector.tooltip({
    content: function content() {
      return $(this).prop('title');
    },
    tooltipClass: 'edd-ui-tooltip',
    position: {
      my: 'center top',
      at: 'center bottom+10',
      collision: 'flipfit'
    },
    hide: {
      duration: 200
    },
    show: {
      duration: 200
    }
  });
};
jQuery(document).ready(function ($) {
  edd_attach_tooltips($('.edd-help-tip'));
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery"), __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/downloads/bulk-edit.js":
/*!************************************************!*\
  !*** ./assets/js/admin/downloads/bulk-edit.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {jQuery(document).ready(function ($) {
  $('body').on('click', '#the-list .editinline', function () {
    var post_id = $(this).closest('tr').attr('id');
    post_id = post_id.replace('post-', '');
    var $edd_inline_data = $('#post-' + post_id);
    var regprice = $edd_inline_data.find('.column-price .downloadprice-' + post_id).val(); // If variable priced product disable editing, otherwise allow price changes

    if (regprice !== $('#post-' + post_id + '.column-price .downloadprice-' + post_id).val()) {
      $('.regprice', '#edd-download-data').val(regprice).attr('disabled', false);
    } else {
      $('.regprice', '#edd-download-data').val(edd_vars.quick_edit_warning).attr('disabled', 'disabled');
    }
  }); // Bulk edit save

  $(document.body).on('click', '#bulk_edit', function () {
    // define the bulk edit row
    var $bulk_row = $('#bulk-edit'); // get the selected post ids that are being edited

    var $post_ids = new Array();
    $bulk_row.find('#bulk-titles').children().each(function () {
      $post_ids.push($(this).attr('id').replace(/^(ttle)/i, ''));
    }); // get the stock and price values to save for all the product ID's

    var $price = $('#edd-download-data input[name="_edd_regprice"]').val();
    var data = {
      action: 'edd_save_bulk_edit',
      edd_bulk_nonce: $post_ids,
      post_ids: $post_ids,
      price: $price
    }; // save the data

    $.post(ajaxurl, data);
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/downloads/index.js":
/*!********************************************!*\
  !*** ./assets/js/admin/downloads/index.js ***!
  \********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($, jQuery) {/* harmony import */ var utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/chosen.js */ "./assets/js/utils/chosen.js");
/* harmony import */ var admin_components_tooltips__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! admin/components/tooltips */ "./assets/js/admin/components/tooltips/index.js");
/* harmony import */ var _bulk_edit_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./bulk-edit.js */ "./assets/js/admin/downloads/bulk-edit.js");
/* harmony import */ var _bulk_edit_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_bulk_edit_js__WEBPACK_IMPORTED_MODULE_2__);
/**
 * Internal dependencies.
 */



/**
 * Download Configuration Metabox
 */

var EDD_Download_Configuration = {
  init: function init() {
    this.add();
    this.move();
    this.remove();
    this.type();
    this.prices();
    this.files();
    this.updatePrices();
    this.showAdvanced();
  },
  clone_repeatable: function clone_repeatable(row) {
    // Retrieve the highest current key
    var key = 1;
    var highest = 1;
    row.parent().find('.edd_repeatable_row').each(function () {
      var current = $(this).data('key');

      if (parseInt(current) > highest) {
        highest = current;
      }
    });
    key = highest += 1;
    var clone = row.clone();
    clone.removeClass('edd_add_blank');
    clone.attr('data-key', key);
    clone.find('input, select, textarea').val('').each(function () {
      var elem = $(this),
          name = elem.attr('name'),
          id = elem.attr('id');

      if (name) {
        name = name.replace(/\[(\d+)\]/, '[' + parseInt(key) + ']');
        elem.attr('name', name);
      }

      elem.attr('data-key', key);

      if (typeof id !== 'undefined') {
        id = id.replace(/(\d+)/, parseInt(key));
        elem.attr('id', id);
      }
    });
    /** manually update any select box values */

    clone.find('select').each(function () {
      $(this).val(row.find('select[name="' + $(this).attr('name') + '"]').val());
    });
    /** manually uncheck any checkboxes */

    clone.find('input[type="checkbox"]').each(function () {
      // Make sure checkboxes are unchecked when cloned
      var checked = $(this).is(':checked');

      if (checked) {
        $(this).prop('checked', false);
      } // reset the value attribute to 1 in order to properly save the new checked state


      $(this).val(1);
    });
    clone.find('span.edd_price_id').each(function () {
      $(this).text(parseInt(key));
    });
    clone.find('input.edd_repeatable_index').each(function () {
      $(this).val(parseInt($(this).data('key')));
    });
    clone.find('span.edd_file_id').each(function () {
      $(this).text(parseInt(key));
    });
    clone.find('.edd_repeatable_default_input').each(function () {
      $(this).val(parseInt(key)).removeAttr('checked');
    });
    clone.find('.edd_repeatable_condition_field').each(function () {
      $(this).find('option:eq(0)').prop('selected', 'selected');
    }); // Remove Chosen elements

    clone.find('.search-choice').remove();
    clone.find('.chosen-container').remove();
    Object(admin_components_tooltips__WEBPACK_IMPORTED_MODULE_1__["edd_attach_tooltips"])(clone.find('.edd-help-tip'));
    return clone;
  },
  add: function add() {
    $(document.body).on('click', '.edd_add_repeatable', function (e) {
      e.preventDefault();
      var button = $(this),
          row = button.parent().prev().children('.edd_repeatable_row:last-child'),
          clone = EDD_Download_Configuration.clone_repeatable(row);
      clone.insertAfter(row).find('input, textarea, select').filter(':visible').eq(0).focus(); // Setup chosen fields again if they exist

      clone.find('.edd-select-chosen').each(function () {
        var el = $(this);
        el.chosen(Object(utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__["getChosenVars"])(el));
      });
      clone.find('.edd-select-chosen').css('width', '100%');
      clone.find('.edd-select-chosen .chosen-search input').attr('placeholder', edd_vars.search_placeholder);
    });
  },
  move: function move() {
    $('.edd_repeatable_table .edd-repeatables-wrap').sortable({
      axis: 'y',
      handle: '.edd-draghandle-anchor',
      items: '.edd_repeatable_row',
      cursor: 'move',
      tolerance: 'pointer',
      containment: 'parent',
      distance: 2,
      opacity: 0.7,
      scroll: true,
      update: function update() {
        var count = 0;
        $(this).find('.edd_repeatable_row').each(function () {
          $(this).find('input.edd_repeatable_index').each(function () {
            $(this).val(count);
          });
          count++;
        });
      },
      start: function start(e, ui) {
        ui.placeholder.height(ui.item.height() - 2);
      }
    });
  },
  remove: function remove() {
    $(document.body).on('click', '.edd-remove-row, .edd_remove_repeatable', function (e) {
      e.preventDefault();
      var row = $(this).parents('.edd_repeatable_row'),
          count = row.parent().find('.edd_repeatable_row').length,
          type = $(this).data('type'),
          repeatable = 'div.edd_repeatable_' + type + 's',
          focusElement,
          focusable,
          firstFocusable; // Set focus on next element if removing the first row. Otherwise set focus on previous element.

      if ($(this).is('.ui-sortable .edd_repeatable_row:first-child .edd-remove-row, .ui-sortable .edd_repeatable_row:first-child .edd_remove_repeatable')) {
        focusElement = row.next('.edd_repeatable_row');
      } else {
        focusElement = row.prev('.edd_repeatable_row');
      }

      focusable = focusElement.find('select, input, textarea, button').filter(':visible');
      firstFocusable = focusable.eq(0);

      if (type === 'price') {
        var price_row_id = row.data('key');
        /** remove from price condition */

        $('.edd_repeatable_condition_field option[value="' + price_row_id + '"]').remove();
      }

      if (count > 1) {
        $('input, select', row).val('');
        row.fadeOut('fast').remove();
        firstFocusable.focus();
      } else {
        switch (type) {
          case 'price':
            alert(edd_vars.one_price_min);
            break;

          case 'file':
            $('input, select', row).val('');
            break;

          default:
            alert(edd_vars.one_field_min);
            break;
        }
      }
      /* re-index after deleting */


      $(repeatable).each(function (rowIndex) {
        $(this).find('input, select').each(function () {
          var name = $(this).attr('name');
          name = name.replace(/\[(\d+)\]/, '[' + rowIndex + ']');
          $(this).attr('name', name).attr('id', name);
        });
      });
    });
  },
  type: function type() {
    $(document.body).on('change', '#_edd_product_type', function (e) {
      var edd_products = $('#edd_products'),
          edd_download_files = $('#edd_download_files'),
          edd_download_limit_wrap = $('#edd_download_limit_wrap');

      if ('bundle' === $(this).val()) {
        edd_products.show();
        edd_download_files.hide();
        edd_download_limit_wrap.hide();
      } else {
        edd_products.hide();
        edd_download_files.show();
        edd_download_limit_wrap.show();
      }
    });
  },
  prices: function prices() {
    $(document.body).on('change', '#edd_variable_pricing', function (e) {
      var checked = $(this).is(':checked'),
          single = $('#edd_regular_price_field'),
          variable = $('#edd_variable_price_fields, .edd_repeatable_table .pricing'),
          bundleRow = $('.edd-bundled-product-row, .edd-repeatable-row-standard-fields');

      if (checked) {
        single.hide();
        variable.show();
        bundleRow.addClass('has-variable-pricing');
      } else {
        single.show();
        variable.hide();
        bundleRow.removeClass('has-variable-pricing');
      }
    });
  },
  files: function files() {
    var file_frame;
    window.formfield = '';
    $(document.body).on('click', '.edd_upload_file_button', function (e) {
      e.preventDefault();
      var button = $(this);
      window.formfield = button.closest('.edd_repeatable_upload_wrapper'); // If the media frame already exists, reopen it.

      if (file_frame) {
        file_frame.open();
        return;
      } // Create the media frame.


      file_frame = wp.media.frames.file_frame = wp.media({
        title: button.data('uploader-title'),
        library: {
          type: 'image'
        },
        button: {
          text: button.data('uploader-button-text')
        },
        multiple: $(this).data('multiple') === '0' ? false : true // Set to true to allow multiple files to be selected

      });
      file_frame.on('menu:render:default', function (view) {
        // Store our views in an object.
        var views = {}; // Unset default menu items

        view.unset('library-separator');
        view.unset('gallery');
        view.unset('featured-image');
        view.unset('embed'); // Initialize the views in our view object.

        view.set(views);
      }); // When an image is selected, run a callback.

      file_frame.on('select', function () {
        var selection = file_frame.state().get('selection');
        selection.each(function (attachment, index) {
          attachment = attachment.toJSON();
          var selectedSize = 'image' === attachment.type ? $('.attachment-display-settings .size option:selected').val() : false,
              selectedURL = attachment.url,
              selectedName = attachment.title.length > 0 ? attachment.title : attachment.filename;

          if (selectedSize && typeof attachment.sizes[selectedSize] !== 'undefined') {
            selectedURL = attachment.sizes[selectedSize].url;
          }

          if ('image' === attachment.type) {
            if (selectedSize && typeof attachment.sizes[selectedSize] !== 'undefined') {
              selectedName = selectedName + '-' + attachment.sizes[selectedSize].width + 'x' + attachment.sizes[selectedSize].height;
            } else {
              selectedName = selectedName + '-' + attachment.width + 'x' + attachment.height;
            }
          }

          if (0 === index) {
            // place first attachment in field
            window.formfield.find('.edd_repeatable_attachment_id_field').val(attachment.id);
            window.formfield.find('.edd_repeatable_thumbnail_size_field').val(selectedSize);
            window.formfield.find('.edd_repeatable_upload_field').val(selectedURL);
            window.formfield.find('.edd_repeatable_name_field').val(selectedName);
          } else {
            // Create a new row for all additional attachments
            var row = window.formfield,
                clone = EDD_Download_Configuration.clone_repeatable(row);
            clone.find('.edd_repeatable_attachment_id_field').val(attachment.id);
            clone.find('.edd_repeatable_thumbnail_size_field').val(selectedSize);
            clone.find('.edd_repeatable_upload_field').val(selectedURL);
            clone.find('.edd_repeatable_name_field').val(selectedName);
            clone.insertAfter(row);
          }
        });
      }); // Finally, open the modal

      file_frame.open();
    }); // @todo Break this out and remove jQuery.

    $('.edd_repeatable_upload_field').on('focus', function () {
      var input = $(this);
      input.data('originalFile', input.val());
    }).on('change', function () {
      var input = $(this);
      var originalFile = input.data('originalFile');

      if (originalFile !== input.val()) {
        input.closest('.edd-repeatable-row-standard-fields').find('.edd_repeatable_attachment_id_field').val(0);
      }
    });
    var file_frame;
    window.formfield = '';
  },
  updatePrices: function updatePrices() {
    $('#edd_price_fields').on('keyup', '.edd_variable_prices_name', function () {
      var key = $(this).parents('.edd_repeatable_row').data('key'),
          name = $(this).val(),
          field_option = $('.edd_repeatable_condition_field option[value=' + key + ']');

      if (field_option.length > 0) {
        field_option.text(name);
      } else {
        $('.edd_repeatable_condition_field').append($('<option></option>').attr('value', key).text(name));
      }
    });
  },
  showAdvanced: function showAdvanced() {
    // Toggle display of entire custom settings section for a price option
    $(document.body).on('click', '.toggle-custom-price-option-section', function (e) {
      e.preventDefault();
      var toggle = $(this),
          show = toggle.html() === edd_vars.show_advanced_settings ? true : false;

      if (show) {
        toggle.html(edd_vars.hide_advanced_settings);
      } else {
        toggle.html(edd_vars.show_advanced_settings);
      }

      var header = toggle.parents('.edd-repeatable-row-header');
      header.siblings('.edd-custom-price-option-sections-wrap').slideToggle();
      var first_input;

      if (show) {
        first_input = $(':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings('.edd-custom-price-option-sections-wrap'));
      } else {
        first_input = $(':input:not(input[type=button],input[type=submit],button):visible:first', header.siblings('.edd-repeatable-row-standard-fields'));
      }

      first_input.focus();
    });
  }
};
jQuery(document).ready(function ($) {
  EDD_Download_Configuration.init();
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery"), __webpack_require__(/*! jquery */ "jquery")))

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

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-downloads.js.map