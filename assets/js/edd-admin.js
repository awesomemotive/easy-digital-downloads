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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/components/chosen/index.js":
/*!****************************************************!*\
  !*** ./assets/js/admin/components/chosen/index.js ***!
  \****************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! utils/chosen.js */ "./assets/js/utils/chosen.js");
/* global _ */

/**
 * Internal dependencies.
 */

jQuery(document).ready(function ($) {
  // Globally apply to elements on the page.
  $('.edd-select-chosen').each(function () {
    var el = $(this);
    el.chosen(Object(utils_chosen_js__WEBPACK_IMPORTED_MODULE_0__["getChosenVars"])(el));
  });
  $('.edd-select-chosen .chosen-search input').each(function () {
    // Bail if placeholder already set
    if ($(this).attr('placeholder')) {
      return;
    }

    var selectElem = $(this).parent().parent().parent().prev('select.edd-select-chosen'),
        placeholder = selectElem.data('search-placeholder');

    if (placeholder) {
      $(this).attr('placeholder', placeholder);
    }
  }); // Add placeholders for Chosen input fields

  $('.chosen-choices').on('click', function () {
    var placeholder = $(this).parent().prev().data('search-placeholder');

    if (typeof placeholder === 'undefined') {
      placeholder = edd_vars.type_to_search;
    }

    $(this).children('li').children('input').attr('placeholder', placeholder);
  }); // This fixes the Chosen box being 0px wide when the thickbox is opened

  $('#post').on('click', '.edd-thickbox', function () {
    $('.edd-select-chosen', '#choose-download').css('width', '100%');
  }); // Variables for setting up the typing timer
  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

  var userInteractionInterval = 342,
      typingTimerElements = '.edd-select-chosen .chosen-search input, .edd-select-chosen .search-field input',
      typingTimer; // Replace options with search results

  $(document.body).on('keyup', typingTimerElements, _.debounce(function (e) {
    var element = $(this),
        val = element.val(),
        container = element.closest('.edd-select-chosen'),
        select = container.prev(),
        select_type = select.data('search-type'),
        no_bundles = container.hasClass('no-bundles'),
        variations = container.hasClass('variations'),
        variations_only = container.hasClass('variations-only'),
        lastKey = e.which,
        search_type = 'edd_download_search'; // String replace the chosen container IDs

    container.attr('id').replace('_chosen', ''); // Detect if we have a defined search type, otherwise default to downloads

    if (typeof select_type !== 'undefined') {
      // Don't trigger AJAX if this select has all options loaded
      if ('no_ajax' === select_type) {
        return;
      }

      search_type = 'edd_' + select_type + '_search';
    } else {
      return;
    } // Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)


    if (val.length <= 3 && 'edd_download_search' === search_type || lastKey === 16 || lastKey === 13 || lastKey === 91 || lastKey === 17 || lastKey === 37 || lastKey === 38 || lastKey === 39 || lastKey === 40) {
      container.children('.spinner').remove();
      return;
    } // Maybe append a spinner


    if (!container.children('.spinner').length) {
      container.append('<span class="spinner is-active"></span>');
    }

    $.ajax({
      type: 'GET',
      dataType: 'json',
      url: ajaxurl,
      data: {
        s: val,
        action: search_type,
        no_bundles: no_bundles,
        variations: variations,
        variations_only: variations_only
      },
      beforeSend: function beforeSend() {
        select.closest('ul.chosen-results').empty();
      },
      success: function success(data) {
        // Remove all options but those that are selected
        $('option:not(:selected)', select).remove(); // Add any option that doesn't already exist

        $.each(data, function (key, item) {
          if (!$('option[value="' + item.id + '"]', select).length) {
            select.prepend('<option value="' + item.id + '">' + item.name + '</option>');
          }
        }); // Get the text immediately before triggering an update.
        // Any sooner will cause the text to jump around.

        var val = element.val(); // Update the options

        select.trigger('chosen:updated');
        element.val(val);
      }
    }).fail(function (response) {
      if (window.console && window.console.log) {
        console.log(response);
      }
    }).done(function (response) {
      container.children('.spinner').remove();
    });
  }, userInteractionInterval));
});

/***/ }),

/***/ "./assets/js/admin/components/date-picker/index.js":
/*!*********************************************************!*\
  !*** ./assets/js/admin/components/date-picker/index.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Date picker
 *
 * This juggles a few CSS classes to avoid styling collisions with other
 * third-party plugins.
 */
jQuery(document).ready(function ($) {
  var edd_datepicker = $('input.edd_datepicker');

  if (edd_datepicker.length > 0) {
    edd_datepicker // Disable autocomplete to avoid it covering the calendar
    .attr('autocomplete', 'off') // Invoke the datepickers
    .datepicker({
      dateFormat: edd_vars.date_picker_format,
      beforeShow: function beforeShow() {
        $('#ui-datepicker-div').removeClass('ui-datepicker').addClass('edd-datepicker');
      }
    });
  }
});

/***/ }),

/***/ "./assets/js/admin/components/sortable-list/index.js":
/*!***********************************************************!*\
  !*** ./assets/js/admin/components/sortable-list/index.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Sortables
 *
 * This makes certain settings sortable, and attempts to stash the results
 * in the nearest .edd-order input value.
 */
jQuery(document).ready(function ($) {
  var edd_sortables = $('ul.edd-sortable-list');

  if (edd_sortables.length > 0) {
    edd_sortables.sortable({
      axis: 'y',
      items: 'li',
      cursor: 'move',
      tolerance: 'pointer',
      containment: 'parent',
      distance: 2,
      opacity: 0.7,
      scroll: true,

      /**
       * When sorting stops, assign the value to the previous input.
       * This input should be a hidden text field
       */
      stop: function stop() {
        var keys = $.map($(this).children('li'), function (el) {
          return $(el).data('key');
        });
        $(this).prev('input.edd-order').val(keys);
      }
    });
  }
});

/***/ }),

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

/***/ "./assets/js/admin/components/user-search/index.js":
/*!*********************************************************!*\
  !*** ./assets/js/admin/components/user-search/index.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

jQuery(document).ready(function ($) {
  // AJAX user search
  $('.edd-ajax-user-search') // Search
  .keyup(function () {
    var user_search = $(this).val(),
        exclude = '';

    if ($(this).data('exclude')) {
      exclude = $(this).data('exclude');
    }

    $('.edd_user_search_wrap').addClass('loading');
    var data = {
      action: 'edd_search_users',
      user_name: user_search,
      exclude: exclude
    };
    $.ajax({
      type: 'POST',
      data: data,
      dataType: 'json',
      url: ajaxurl,
      success: function success(search_response) {
        $('.edd_user_search_wrap').removeClass('loading');
        $('.edd_user_search_results').removeClass('hidden');
        $('.edd_user_search_results span').html('');

        if (search_response.results) {
          $(search_response.results).appendTo('.edd_user_search_results span');
        }
      }
    });
  }) // Hide
  .blur(function () {
    if (edd_user_search_mouse_down) {
      edd_user_search_mouse_down = false;
    } else {
      $(this).removeClass('loading');
      $('.edd_user_search_results').addClass('hidden');
    }
  }) // Show
  .focus(function () {
    $(this).keyup();
  });
  $(document.body).on('click.eddSelectUser', '.edd_user_search_results span a', function (e) {
    e.preventDefault();
    var login = $(this).data('login');
    $('.edd-ajax-user-search').val(login);
    $('.edd_user_search_results').addClass('hidden');
    $('.edd_user_search_results span').html('');
  });
  $(document.body).on('click.eddCancelUserSearch', '.edd_user_search_results a.edd-ajax-user-cancel', function (e) {
    e.preventDefault();
    $('.edd-ajax-user-search').val('');
    $('.edd_user_search_results').addClass('hidden');
    $('.edd_user_search_results span').html('');
  }); // Cancel user-search.blur when picking a user

  var edd_user_search_mouse_down = false;
  $('.edd_user_search_results').mousedown(function () {
    edd_user_search_mouse_down = true;
  });
});

/***/ }),

/***/ "./assets/js/admin/components/vertical-sections/index.js":
/*!***************************************************************!*\
  !*** ./assets/js/admin/components/vertical-sections/index.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

jQuery(document).ready(function ($) {
  // Hides the section content.
  $('.edd-vertical-sections.use-js .section-content').hide(); // Shows the first section's content.

  $('.edd-vertical-sections.use-js .section-content:first-child').show(); // Makes the 'aria-selected' attribute true for the first section nav item.

  $('.edd-vertical-sections.use-js .section-nav :first-child').attr('aria-selected', 'true'); // Copies the current section item title to the box header.

  $('.which-section').text($('.section-nav :first-child a').text()); // When a section nav item is clicked.

  $('.edd-vertical-sections.use-js .section-nav li a').on('click', function (j) {
    // Prevent the default browser action when a link is clicked.
    j.preventDefault(); // Get the `href` attribute of the item.

    var them = $(this),
        href = them.attr('href'),
        rents = them.parents('.edd-vertical-sections'); // Hide all section content.

    rents.find('.section-content').hide(); // Find the section content that matches the section nav item and show it.

    rents.find(href).show(); // Set the `aria-selected` attribute to false for all section nav items.

    rents.find('.section-title').attr('aria-selected', 'false'); // Set the `aria-selected` attribute to true for this section nav item.

    them.parent().attr('aria-selected', 'true'); // Maybe re-Chosen

    rents.find('div.chosen-container').css('width', '100%'); // Copy the current section item title to the box header.

    $('.which-section').text(them.text());
  }); // click()
});

/***/ }),

/***/ "./assets/js/admin/index.js":
/*!**********************************!*\
  !*** ./assets/js/admin/index.js ***!
  \**********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_date_picker__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/date-picker */ "./assets/js/admin/components/date-picker/index.js");
/* harmony import */ var _components_date_picker__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_components_date_picker__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_chosen__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/chosen */ "./assets/js/admin/components/chosen/index.js");
/* harmony import */ var _components_tooltips__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/tooltips */ "./assets/js/admin/components/tooltips/index.js");
/* harmony import */ var _components_vertical_sections__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/vertical-sections */ "./assets/js/admin/components/vertical-sections/index.js");
/* harmony import */ var _components_vertical_sections__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_components_vertical_sections__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _components_sortable_list__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/sortable-list */ "./assets/js/admin/components/sortable-list/index.js");
/* harmony import */ var _components_sortable_list__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_components_sortable_list__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _components_user_search__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/user-search */ "./assets/js/admin/components/user-search/index.js");
/* harmony import */ var _components_user_search__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_components_user_search__WEBPACK_IMPORTED_MODULE_5__);
/**
 * Internal dependencies.
 */







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
//# sourceMappingURL=edd-admin.js.map