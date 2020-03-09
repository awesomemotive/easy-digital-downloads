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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/reports/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/reports/charts/index.js":
/*!*************************************************!*\
  !*** ./assets/js/admin/reports/charts/index.js ***!
  \*************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _line_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./line.js */ "./assets/js/admin/reports/charts/line.js");
/* harmony import */ var _pie_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pie.js */ "./assets/js/admin/reports/charts/pie.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils.js */ "./assets/js/admin/reports/charts/utils.js");
/* global eddAdminReportsCharts */

/**
 * Internal dependencies.
 */


 // Access existing global `edd` variable, or create a new object.

window.edd = window.edd || {};
/**
 * Render a chart based on config.
 *
 * This function is attached to the `edd` property attached to the `window`.
 *
 * @param {Object} config Chart config.
 */

window.edd.renderChart = function (config) {
  var isPie = Object(_utils_js__WEBPACK_IMPORTED_MODULE_2__["isPieChart"])(config);

  if (Object(_utils_js__WEBPACK_IMPORTED_MODULE_2__["isPieChart"])(config)) {
    Object(_pie_js__WEBPACK_IMPORTED_MODULE_1__["render"])(config);
  } else {
    Object(_line_js__WEBPACK_IMPORTED_MODULE_0__["render"])(config);
  }
};

/***/ }),

/***/ "./assets/js/admin/reports/charts/line.js":
/*!************************************************!*\
  !*** ./assets/js/admin/reports/charts/line.js ***!
  \************************************************/
/*! exports provided: render, tooltipConfig */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "tooltipConfig", function() { return tooltipConfig; });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils */ "./assets/js/admin/reports/charts/utils.js");


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/* global Chart */

/**
 * Internal dependencies.
 */

/**
 * Render a line chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */

var render = function render(config) {
  var dates = config.dates,
      options = config.options,
      data = config.data,
      target = config.target;
  Chart.defaults.global.pointHitDetectionRadius = 5; // Convert dataset x-axis values to moment() objects.

  _.each(data.datasets, function (dataset) {
    _.each(dataset.data, function (pair, index) {
      // Moment.js accepts a date object so we'll turn the timestamp into a date object here.
      var date = new Date(parseInt(pair.x)); // Offset the moment.js so it is set to match the WordPress timezone, which is n dates.utc_offset

      pair.x = moment(date).utcOffset(parseInt(dates.utc_offset)).format('LLL');
    });
  }); // Set min and max moment() values for the x-axis.
  // @todo Not sure this is the correct way to be setting this?


  _.each(options.scales.xAxes, function (xaxis) {
    if (!dates.day_by_day) {
      xaxis.time.unit = 'month';
    }

    xaxis.time.min = moment(dates.start.date);
    xaxis.time.max = moment(dates.end.date);
  }); // Config tooltips.


  config.options.tooltips = tooltipConfig(config); // Render

  return new Chart(document.getElementById(target), config);
};
/**
 * Get custom tooltip config for line charts.
 *
 * @param {Object} config Global chart config.
 * @return {Object}
 */

var tooltipConfig = function tooltipConfig(config) {
  return _objectSpread({}, _utils__WEBPACK_IMPORTED_MODULE_1__["toolTipBaseConfig"], {
    callbacks: {
      /**
       * Generate a label.
       *
       * @param {Object} t
       * @param {Object} d
       */
      label: function label(t, d) {
        var datasets = config.options.datasets;
        var datasetConfig = datasets[Object.keys(datasets)[t.datasetIndex]];
        var label = Object(_utils__WEBPACK_IMPORTED_MODULE_1__["getLabelWithTypeCondition"])(t.yLabel, datasetConfig);
        return "".concat(d.datasets[t.datasetIndex].label, ": ").concat(label);
      }
    }
  });
};

/***/ }),

/***/ "./assets/js/admin/reports/charts/pie.js":
/*!***********************************************!*\
  !*** ./assets/js/admin/reports/charts/pie.js ***!
  \***********************************************/
/*! exports provided: render, tooltipConfig */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "tooltipConfig", function() { return tooltipConfig; });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils */ "./assets/js/admin/reports/charts/utils.js");


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/* global Chart */

/**
 * Internal dependencies.
 */

/**
 * Render a line chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */

var render = function render(config) {
  var target = config.target;
  Chart.defaults.global.pointHitDetectionRadius = 5; // Config tooltips.

  config.options.tooltips = tooltipConfig(config); // Render

  return new Chart(document.getElementById(target), config);
};
/**
 * Get custom tooltip config for pie charts.
 *
 * @param {Object} config Global chart config.
 * @return {Object}
 */

var tooltipConfig = function tooltipConfig(config) {
  return _objectSpread({}, _utils__WEBPACK_IMPORTED_MODULE_1__["toolTipBaseConfig"], {
    callbacks: {
      /**
       * Generate a label.
       *
       * @param {Object} t
       * @param {Object} d
       */
      label: function label(t, d) {
        var dataset = d.datasets[t.datasetIndex];
        var total = dataset.data.reduce(function (previousValue, currentValue, currentIndex, array) {
          return previousValue + currentValue;
        });
        var currentValue = dataset.data[t.index];
        var precentage = Math.floor(currentValue / total * 100 + 0.5);
        return "".concat(d.labels[t.index], ": ").concat(currentValue, " (").concat(precentage, "%)");
      }
    }
  });
};

/***/ }),

/***/ "./assets/js/admin/reports/charts/utils.js":
/*!*************************************************!*\
  !*** ./assets/js/admin/reports/charts/utils.js ***!
  \*************************************************/
/*! exports provided: isPieChart, getLabelWithTypeCondition, toolTipBaseConfig */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isPieChart", function() { return isPieChart; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getLabelWithTypeCondition", function() { return getLabelWithTypeCondition; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "toolTipBaseConfig", function() { return toolTipBaseConfig; });
/* global edd_vars */

/**
 * Determine if a pie graph.
 *
 * @todo maybe pass from data?
 *
 * @param {Object} config Global chart config.
 * @return {Bool}
 */
var isPieChart = function isPieChart(config) {
  var type = config.type;
  return type === 'pie' || type === 'doughnut';
};
/**
 * Determine if a chart's dataset has a special conditional type.
 *
 * Currently just checks for currency.
 *
 * @param {string} label Current label.
 * @param {Object} config Global chart config.
 */

var getLabelWithTypeCondition = function getLabelWithTypeCondition(label, datasetConfig) {
  var _edd_vars = edd_vars,
      currency_sign = _edd_vars.currency_sign,
      currency_pos = _edd_vars.currency_pos;
  var newLabel = label;
  var type = datasetConfig.type;

  if ('currency' === type) {
    var amount = label.toFixed(2);

    if ('before' === currency_pos) {
      newLabel = currency_sign + amount;
    } else {
      newLabel = amount + currency_sign;
    }
  }

  return newLabel;
};
/**
 * Shared tooltip configuration.
 */

var toolTipBaseConfig = {
  enabled: false,
  mode: 'index',
  position: 'nearest',

  /**
   * Output a a custom tooltip.
   *
   * @param {Object} tooltip Tooltip data.
   */
  custom: function custom(tooltip) {
    // Tooltip element.
    var tooltipEl = document.getElementById('edd-chartjs-tooltip');

    if (!tooltipEl) {
      tooltipEl = document.createElement('div');
      tooltipEl.id = 'edd-chartjs-tooltip';
      tooltipEl.innerHTML = '<table></table>';

      this._chart.canvas.parentNode.appendChild(tooltipEl);
    } // Hide if no tooltip.


    if (tooltip.opacity === 0) {
      tooltipEl.style.opacity = 0;
      return;
    } // Set caret position.


    tooltipEl.classList.remove('above', 'below', 'no-transform');

    if (tooltip.yAlign) {
      tooltipEl.classList.add(tooltip.yAlign);
    } else {
      tooltipEl.classList.add('no-transform');
    }

    function getBody(bodyItem) {
      return bodyItem.lines;
    } // Set Text


    if (tooltip.body) {
      var titleLines = tooltip.title || [];
      var bodyLines = tooltip.body.map(getBody);
      var innerHtml = '<thead>';
      titleLines.forEach(function (title) {
        innerHtml += '<tr><th>' + title + '</th></tr>';
      });
      innerHtml += '</thead><tbody>';
      bodyLines.forEach(function (body, i) {
        var colors = tooltip.labelColors[i];
        var borderColor = colors.borderColor,
            backgroundColor = colors.backgroundColor; // Super dirty check to use the legend's color.

        var fill = borderColor;

        if (fill === 'rgb(230, 230, 230)' || fill === '#fff') {
          fill = backgroundColor;
        }

        var style = ["background: ".concat(fill), "border-color: ".concat(fill), 'border-width: 2px'];
        var span = '<span class="edd-chartjs-tooltip-key" style="' + style.join(';') + '"></span>';
        innerHtml += '<tr><td>' + span + body + '</td></tr>';
      });
      innerHtml += '</tbody>';
      var tableRoot = tooltipEl.querySelector('table');
      tableRoot.innerHTML = innerHtml;
    }

    var positionY = this._chart.canvas.offsetTop;
    var positionX = this._chart.canvas.offsetLeft; // Display, position, and set styles for font

    tooltipEl.style.opacity = 1;
    tooltipEl.style.left = positionX + tooltip.caretX + 'px';
    tooltipEl.style.top = positionY + tooltip.caretY + 'px';
    tooltipEl.style.fontFamily = tooltip._bodyFontFamily;
    tooltipEl.style.fontSize = tooltip.bodyFontSize + 'px';
    tooltipEl.style.fontStyle = tooltip._bodyFontStyle;
    tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';
  }
};

/***/ }),

/***/ "./assets/js/admin/reports/formatting.js":
/*!***********************************************!*\
  !*** ./assets/js/admin/reports/formatting.js ***!
  \***********************************************/
/*! exports provided: eddLabelFormatter, eddLegendFormatterSales, eddLegendFormatterEarnings */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "eddLabelFormatter", function() { return eddLabelFormatter; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "eddLegendFormatterSales", function() { return eddLegendFormatterSales; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "eddLegendFormatterEarnings", function() { return eddLegendFormatterEarnings; });
var eddLabelFormatter = function eddLabelFormatter(label, series) {
  return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
};
var eddLegendFormatterSales = function eddLegendFormatterSales(label, series) {
  var slug = label.toLowerCase().replace(/\s/g, '-'),
      color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>',
      value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + eddFormatNumber(series.data[0][1]) + ')</div>',
      item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';
  jQuery('#edd-pie-legend-' + series.edd_vars.id).append(item);
  return item;
};
var eddLegendFormatterEarnings = function eddLegendFormatterEarnings(label, series) {
  var slug = label.toLowerCase().replace(/\s/g, '-'),
      color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>',
      value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + eddFormatCurrency(series.data[0][1]) + ')</div>',
      item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';
  jQuery('#edd-pie-legend-' + series.edd_vars.id).append(item);
  return item;
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./assets/js/admin/reports/index.js":
/*!******************************************!*\
  !*** ./assets/js/admin/reports/index.js ***!
  \******************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function($, jQuery) {/* harmony import */ var _formatting_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./formatting.js */ "./assets/js/admin/reports/formatting.js");
/* harmony import */ var _charts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./charts */ "./assets/js/admin/reports/charts/index.js");
/* global pagenow, postboxes */

/**
 * Internal dependencies.
 */

 // Enable reports meta box toggle states.

if (typeof postboxes !== 'undefined' && /edd-reports/.test(pagenow)) {
  postboxes.add_postbox_toggles(pagenow);
}
/**
 * Reports / Exports screen JS
 */


var EDD_Reports = {
  init: function init() {
    this.meta_boxes();
    this.date_options();
    this.customers_export();
    this.filters();
  },
  meta_boxes: function meta_boxes() {
    $('.edd-reports-wrapper .postbox .handlediv').remove();
    $('.edd-reports-wrapper .postbox').removeClass('closed'); // Use a timeout to ensure this happens after core binding

    setTimeout(function () {
      $('.edd-reports-wrapper .postbox .hndle').unbind('click.postboxes');
    }, 1);
  },
  date_options: function date_options() {
    // Show hide extended date options
    $('select.edd-graphs-date-options').on('change', function (event) {
      var select = $(this),
          date_range_options = select.parent().siblings('.edd-date-range-options');

      if ('other' === select.val()) {
        date_range_options.removeClass('screen-reader-text');
      } else {
        date_range_options.addClass('screen-reader-text');
      }
    });
  },
  customers_export: function customers_export() {
    // Show / hide Download option when exporting customers
    $('#edd_customer_export_download').change(function () {
      var $this = $(this),
          download_id = $('option:selected', $this).val(),
          customer_export_option = $('#edd_customer_export_option');

      if ('0' === $this.val()) {
        customer_export_option.show();
      } else {
        customer_export_option.hide();
      } // On Download Select, Check if Variable Prices Exist


      if (parseInt(download_id) !== 0) {
        var data = {
          action: 'edd_check_for_download_price_variations',
          download_id: download_id,
          all_prices: true
        };
        var price_options_select = $('.edd_price_options_select');
        $.post(ajaxurl, data, function (response) {
          price_options_select.remove();
          $('#edd_customer_export_download_chosen').after(response);
        });
      } else {
        price_options_select.remove();
      }
    });
  },
  filters: function filters() {
    $('.edd_countries_filter').on('change', function () {
      var select = $(this),
          data = {
        action: 'edd_get_shop_states',
        country: select.val(),
        nonce: select.data('nonce'),
        field_name: 'edd_regions_filter'
      };
      $.post(ajaxurl, data, function (response) {
        $('select.edd_regions_filter').find('option:gt(0)').remove();

        if ('nostates' !== response) {
          $(response).find('option:gt(0)').appendTo('select.edd_regions_filter');
        }

        $('select.edd_regions_filter').trigger('chosen:updated');
      });
      return false;
    });
  }
};
jQuery(document).ready(function ($) {
  EDD_Reports.init();
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery"), __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/defineProperty.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/defineProperty.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

module.exports = _defineProperty;

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
//# sourceMappingURL=edd-admin-reports.js.map