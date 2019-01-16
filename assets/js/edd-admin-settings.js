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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/admin/settings/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/admin/settings/index.js":
/*!*******************************************!*\
  !*** ./assets/js/admin/settings/index.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Settings screen JS
 */
var EDD_Settings = {
  init: function init() {
    this.general();
    this.misc();
    this.gateways();
    this.location();
  },
  general: function general() {
    var edd_color_picker = $('.edd-color-picker');

    if (edd_color_picker.length) {
      edd_color_picker.wpColorPicker();
    } // Settings Upload field JS


    if (typeof wp === 'undefined' || '1' !== edd_vars.new_media_ui) {
      // Old Thickbox uploader
      var edd_settings_upload_button = $('.edd_settings_upload_button');

      if (edd_settings_upload_button.length > 0) {
        window.formfield = '';
        $(document.body).on('click', edd_settings_upload_button, function (e) {
          e.preventDefault();
          window.formfield = $(this).parent().prev();
          window.tbframe_interval = setInterval(function () {
            jQuery('#TB_iframeContent').contents().find('.savesend .button').val(edd_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
          }, 2000);
          tb_show(edd_vars.add_new_download, 'media-upload.php?TB_iframe=true');
        });
        window.edd_send_to_editor = window.send_to_editor;

        window.send_to_editor = function (html) {
          if (window.formfield) {
            imgurl = $('a', '<div>' + html + '</div>').attr('href');
            window.formfield.val(imgurl);
            window.clearInterval(window.tbframe_interval);
            tb_remove();
          } else {
            window.edd_send_to_editor(html);
          }

          window.send_to_editor = window.edd_send_to_editor;
          window.formfield = '';
          window.imagefield = false;
        };
      }
    } else {
      // WP 3.5+ uploader
      var file_frame;
      window.formfield = '';
      $(document.body).on('click', '.edd_settings_upload_button', function (e) {
        e.preventDefault();
        var button = $(this);
        window.formfield = $(this).parent().prev(); // If the media frame already exists, reopen it.

        if (file_frame) {
          //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
          file_frame.open();
          return;
        } // Create the media frame.


        file_frame = wp.media.frames.file_frame = wp.media({
          title: button.data('uploader_title'),
          library: {
            type: 'image'
          },
          button: {
            text: button.data('uploader_button_text')
          },
          multiple: false
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
            window.formfield.val(attachment.url);
          });
        }); // Finally, open the modal

        file_frame.open();
      }); // WP 3.5+ uploader

      var file_frame;
      window.formfield = '';
    }
  },
  misc: function misc() {
    var downloadMethod = $('select[name="edd_settings[download_method]"]'),
        symlink = downloadMethod.parent().parent().next(); // Hide Symlink option if Download Method is set to Direct

    if (downloadMethod.val() === 'direct') {
      symlink.css('opacity', '0.4');
      symlink.find('input').prop('checked', false).prop('disabled', true);
    } // Toggle download method option


    downloadMethod.on('change', function () {
      if ($(this).val() === 'direct') {
        symlink.css('opacity', '0.4');
        symlink.find('input').prop('checked', false).prop('disabled', true);
      } else {
        symlink.find('input').prop('disabled', false);
        symlink.css('opacity', '1');
      }
    });
  },
  gateways: function gateways() {
    $('#edd-payment-gateways input[type="checkbox"]').on('change', function () {
      var gateway = $(this),
          gateway_key = gateway.data('gateway-key'),
          default_gateway = $('#edd_settings\\[default_gateway\\]'),
          option = default_gateway.find('option[value="' + gateway_key + '"]'); // Toggle enable/disable based

      option.prop('disabled', function (i, v) {
        return !v;
      }); // Maybe deselect

      if (option.prop('selected')) {
        option.prop('selected', false);
      }

      default_gateway.trigger('chosen:updated');
    });
  },
  location: function location() {
    $('select.edd_countries_filter').on('change', function () {
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
  EDD_Settings.init();
});

/***/ })

/******/ });
//# sourceMappingURL=edd-admin-settings.js.map