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
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "edd_attach_tooltips", function() { return edd_attach_tooltips; });
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

/***/ }),

/***/ "./assets/js/admin/downloads/bulk-edit.js":
/*!************************************************!*\
  !*** ./assets/js/admin/downloads/bulk-edit.js ***!
  \************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.array.find */ "./node_modules/core-js/modules/es6.array.find.js");
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es6.regexp.replace */ "./node_modules/core-js/modules/es6.regexp.replace.js");
/* harmony import */ var core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_1__);


jQuery(document).ready(function ($) {
  $('body').on('click', '#the-list .editinline', function () {
    var post_id = $(this).closest('tr').attr('id');
    post_id = post_id.replace("post-", "");
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

/***/ }),

/***/ "./assets/js/admin/downloads/index.js":
/*!********************************************!*\
  !*** ./assets/js/admin/downloads/index.js ***!
  \********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es6.regexp.replace */ "./node_modules/core-js/modules/es6.regexp.replace.js");
/* harmony import */ var core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_regexp_replace__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es6.array.find */ "./node_modules/core-js/modules/es6.array.find.js");
/* harmony import */ var core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_array_find__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var js_utils_chosen_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/utils/chosen.js */ "./assets/js/utils/chosen.js");
/* harmony import */ var js_admin_components_tooltips__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! js/admin/components/tooltips */ "./assets/js/admin/components/tooltips/index.js");
/* harmony import */ var _bulk_edit_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./bulk-edit.js */ "./assets/js/admin/downloads/bulk-edit.js");



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
    Object(js_admin_components_tooltips__WEBPACK_IMPORTED_MODULE_3__["edd_attach_tooltips"])(clone.find('.edd-help-tip'));
    return clone;
  },
  add: function add() {
    $(document.body).on('click', '.edd_add_repeatable', function (e) {
      e.preventDefault();
      var button = $(this),
          row = button.parent().prev().children('.edd_repeatable_row:last-child'),
          clone = EDD_Download_Configuration.clone_repeatable(row);
      clone.insertAfter(row).find('input, textarea, select').filter(':visible').eq(0).focus(); // Setup chosen fields again if they exist

      clone.find('.edd-select-chosen').chosen(js_utils_chosen_js__WEBPACK_IMPORTED_MODULE_2__["chosenVars"]);
      clone.find('.edd-select-chosen').css('width', '100%');
      clone.find('.edd-select-chosen .chosen-search input').attr('placeholder', edd_vars.search_placeholder);
    });
  },
  move: function move() {
    $(".edd_repeatable_table .edd-repeatables-wrap").sortable({
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

          if (selectedSize && typeof attachment.sizes[selectedSize] !== "undefined") {
            selectedURL = attachment.sizes[selectedSize].url;
          }

          if ('image' === attachment.type) {
            if (selectedSize && typeof attachment.sizes[selectedSize] !== "undefined") {
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
  }
}; // Toggle display of entire custom settings section for a price option

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
    first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.edd-custom-price-option-sections-wrap'));
  } else {
    first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.edd-repeatable-row-standard-fields'));
  }

  first_input.focus();
});
jQuery(document).ready(function ($) {
  EDD_Download_Configuration.init();
});

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

/***/ "./node_modules/core-js/modules/_fix-re-wks.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/modules/_fix-re-wks.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var hide = __webpack_require__(/*! ./_hide */ "./node_modules/core-js/modules/_hide.js");
var redefine = __webpack_require__(/*! ./_redefine */ "./node_modules/core-js/modules/_redefine.js");
var fails = __webpack_require__(/*! ./_fails */ "./node_modules/core-js/modules/_fails.js");
var defined = __webpack_require__(/*! ./_defined */ "./node_modules/core-js/modules/_defined.js");
var wks = __webpack_require__(/*! ./_wks */ "./node_modules/core-js/modules/_wks.js");

module.exports = function (KEY, length, exec) {
  var SYMBOL = wks(KEY);
  var fns = exec(defined, SYMBOL, ''[KEY]);
  var strfn = fns[0];
  var rxfn = fns[1];
  if (fails(function () {
    var O = {};
    O[SYMBOL] = function () { return 7; };
    return ''[KEY](O) != 7;
  })) {
    redefine(String.prototype, KEY, strfn);
    hide(RegExp.prototype, SYMBOL, length == 2
      // 21.2.5.8 RegExp.prototype[@@replace](string, replaceValue)
      // 21.2.5.11 RegExp.prototype[@@split](string, limit)
      ? function (string, arg) { return rxfn.call(string, this, arg); }
      // 21.2.5.6 RegExp.prototype[@@match](string)
      // 21.2.5.9 RegExp.prototype[@@search](string)
      : function (string) { return rxfn.call(string, this); }
    );
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

/***/ "./node_modules/core-js/modules/es6.regexp.replace.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/modules/es6.regexp.replace.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// @@replace logic
__webpack_require__(/*! ./_fix-re-wks */ "./node_modules/core-js/modules/_fix-re-wks.js")('replace', 2, function (defined, REPLACE, $replace) {
  // 21.1.3.14 String.prototype.replace(searchValue, replaceValue)
  return [function replace(searchValue, replaceValue) {
    'use strict';
    var O = defined(this);
    var fn = searchValue == undefined ? undefined : searchValue[REPLACE];
    return fn !== undefined
      ? fn.call(searchValue, O, replaceValue)
      : $replace.call(String(O), searchValue, replaceValue);
  }, $replace];
});


/***/ })

/******/ });
//# sourceMappingURL=edd-admin-downloads.js.map