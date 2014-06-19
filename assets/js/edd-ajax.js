
/*
 * Main ajax request
 */
function edd_ajax(postData, success, fail){

	if (!postData) return false;

	if (typeof postData.action == 'undefined') return false; // we need to have action

	var url;

	// check for url
	if (typeof postData.url != 'undefined') {
		url = postData.url;
		delete postData.url
	} else {
		if (typeof edd_scripts != 'undefined')
			url = edd_scripts.ajaxurl;
		else if (typeof edd_global_vars != 'undefined')
			url = edd_global_vars.ajaxurl;
		else
			return false; // if no ajax url present abort the request
	}

	jQuery.ajax({
		type: "POST",
		cache: false,
		data: postData,
		dataType: "json",
		url: url,
		success: function(response){
			if (typeof success == 'function') success.call(this, response);
		}
	}).fail(function(data){
		if (typeof fail == 'function') fail.call(this, data);
	});
}


/*
 * Check if value is empty
 */
function edd_is_empty_value(val){
	return typeof val == 'undefined' || jQuery.trim(val) == '';
}


/*
 * Add cart item
 */
function edd_add_to_cart(download_id, price_ids, success, fail){

	if (edd_is_empty_value(download_id)) return false;

	var postData = {
		action: 'edd_add_to_cart',
		download_id: download_id,
		price_ids: jQuery.isArray(price_ids) ? price_ids : [price_ids] // if single id sent wrap it into array
	};

	edd_ajax(postData, success, fail);
}


/*
 * Remove cart item
 */
function edd_remove_from_cart(item, success, fail){

	if (edd_is_empty_value(item)) return false;

	var postData = {
		action: 'edd_remove_from_cart',
		cart_item: item
	};

	edd_ajax(postData, success, fail);
}


/*
 * Update quantity
 */
function edd_update_quantity(download_id, quantity, success, fail){

	if (edd_is_empty_value(download_id)) return false;

	var postData = {
		action: 'edd_update_quantity',
		quantity: quantity,
		download_id: download_id
	};

	edd_ajax(postData, success, fail);
}


/*
 * Recalculate taxes
 */
function edd_recalculate_taxes(state, billing_country, success, fail){

	if (edd_global_vars.taxes_enabled != '1') return false; // Taxes not enabled

	if (edd_is_empty_value(state)) return false;

	var postData = {
		action: 'edd_recalculate_taxes',
		state: state,
		billing_country: billing_country
	};

	edd_ajax(postData, success, fail);
}


/*
 * Apply discount
 */
function edd_apply_discount(discount_code, success, fail){

	if (edd_is_empty_value(discount_code)) return false;

	var postData = {
		action: 'edd_apply_discount',
		code: discount_code
	};

	edd_ajax(postData, success, fail);
}


/*
 * Remove discount
 */
function edd_remove_discount(discount_code, success, fail){

	if (edd_is_empty_value(discount_code)) return false;

	var postData = {
		action: 'edd_remove_discount',
		code: discount_code
	};

	edd_ajax(postData, success, fail);
}


/*
 * Load gateway
 */
function edd_load_gateway(payment_mode, success, fail) {

	if (edd_is_empty_value(payment_mode)) return false;

	var postData = {
		action: 'edd_load_gateway',
		edd_payment_mode: payment_mode,
		url: edd_scripts.ajaxurl + '?payment-mode=' + payment_mode
	};

	edd_ajax(postData, success, fail);
}



/*
 * Attach events
 */
var edd_scripts;
jQuery(document).ready(function ($) {

	// Hide unneeded elements. These are things that are required in case JS breaks or isn't present
	$('.edd-no-js').hide();
	$('a.edd-add-to-cart').addClass('edd-has-js');


	function load_gateway( payment_mode ) {
		// Show the ajax loader
		jQuery('.edd-cart-ajax').show();
		jQuery('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');

		edd_load_gateway(payment_mode, function(response){
			jQuery('#edd_purchase_form_wrap').html(response);
			jQuery('.edd-no-js').hide();
		});
	}


	// Send Remove from Cart requests
	$('body').on('click.eddRemoveFromCart', '.edd-remove-from-cart', function (e) {
		e.preventDefault();

		var $this  = $(this),
			item   = $this.data('cart-item'),
			action = $this.data('action'),
			id	 = $this.data('download-id');

		edd_remove_from_cart(item, function (response) { // success
			if (response.removed) {
				if ( parseInt( edd_scripts.position_in_cart, 10 ) === parseInt( item, 10 ) ) {
					window.location = window.location;
					return false;
				}

				// Remove the selected cart item
				$('.edd-cart').find("[data-cart-item='" + item + "']").parent().remove();

				// Check to see if the purchase form for this download is present on this page
				if( $( '#edd_purchase_' + id ).length ) {
					$( '#edd_purchase_' + id + ' .edd_go_to_checkout' ).hide();
					$( '#edd_purchase_' + id + ' a.edd-add-to-cart' ).show().removeAttr('data-edd-loading');
				}

				$('span.edd-cart-quantity').each(function() {
					var quantity = parseInt( $(this).text(), 10 ) - 1;
					if( quantity < 1 ) {
						quantity = 0;
					}
					$(this).text( quantity );
					$('body').trigger('edd_quantity_updated', [ quantity ]);
				});

				$('.cart_item.edd_subtotal span').html( response.subtotal );

				if(!$('.edd-cart-item').length) {
					$('.cart_item.edd_subtotal,.edd-cart-number-of-items,.cart_item.edd_checkout').hide();
					$('.edd-cart').append('<li class="cart_item empty">' + edd_scripts.empty_cart_message + '</li>');
				}

				$('body').trigger('edd_cart_item_removed', [ response ]);
			}
		}, function (response) { // fail
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

		return false;
	});


	// Send Add to Cart request
	$('body').on('click.eddAddToCart', '.edd-add-to-cart', function (e) {

		e.preventDefault();

		var $this = $(this), form = $this.closest('form');

		if( 'straight_to_gateway' == form.find('.edd_action_input').val() ) {
			form.submit();
			return true; // Submit the form
		}

		var $spinner = $this.find('.edd-loading');
		var container = $this.closest('div');

		var spinnerWidth  = $spinner.width(),
			spinnerHeight = $spinner.height();

		// Show the spinner
		$this.attr('data-edd-loading', '');

		$spinner.css({
			'margin-left': spinnerWidth / -2,
			'margin-top' : spinnerHeight / -2
		});

		var form		   = $this.parents('form').last();
		var download	   = $this.data('download-id');
		var variable_price = $this.data('variable-price');
		var price_mode	 = $this.data('price-mode');
		var item_price_ids = [];

		if( variable_price == 'yes' ) {

			if( ! $('.edd_price_option_' + download + ':checked', form).length ) {
				 // hide the spinner
				$this.removeAttr( 'data-edd-loading' );
				alert( edd_scripts.select_option );
				return;
			}

			$('.edd_price_option_' + download + ':checked', form).each(function( index ) {
				item_price_ids[ index ] = $(this).val();
			});

		} else {
			item_price_ids[0] = download;
		}

		edd_add_to_cart(download, item_price_ids, function (response) { // success

			if( edd_scripts.redirect_to_checkout == '1' ) {

				window.location = edd_scripts.checkout_page;

			} else {

				// Add the new item to the cart widget
				if ($('.cart_item.empty').length) {
					$(response.cart_item).insertBefore('.cart_item.edd_subtotal');
					$('.cart_item.edd_checkout,.cart_item.edd_subtotal').show();
					$('.cart_item.empty').remove();
				} else {
					$(response.cart_item).insertBefore('.cart_item.edd_subtotal');
				}

				 $('.cart_item.edd_subtotal span').html( response.subtotal );

				// Update the cart quantity
				$('span.edd-cart-quantity').each(function() {
					var quantity = parseInt($(this).text(), 10) + 1;
					$(this).text(quantity);
					$('body').trigger('edd_quantity_updated', [ quantity ]);
				});

				// Show the "number of items in cart" message
				if ( $('.edd-cart-number-of-items').css('display') == 'none') {
					$('.edd-cart-number-of-items').show('slow');
				}

				if( variable_price == 'no' || price_mode != 'multi' ) {
					// Switch purchase to checkout if a single price item or variable priced with radio buttons
					$('a.edd-add-to-cart', container).toggle();
					$('.edd_go_to_checkout', container).css('display', 'inline-block');
				}

				if ( price_mode == 'multi' ) {
					// remove spinner for multi
					$this.removeAttr( 'data-edd-loading' );
				}

				// Update all buttons for same download
				if( $( '.edd_download_purchase_form' ).length ) {
					var parent_form = $('.edd_download_purchase_form *[data-download-id="' + download + '"]').parents('form');
					$( 'a.edd-add-to-cart', parent_form ).hide();
					$( '.edd_go_to_checkout', parent_form ).show().removeAttr( 'data-edd-loading' );
			   	}

				if( response != 'incart' ) {
					// Show the added message
					$('.edd-cart-added-alert', container).fadeIn();
					setTimeout(function () {
						$('.edd-cart-added-alert', container).fadeOut();
					}, 3000);
				}

				$('body').trigger('edd_cart_item_added', [ response ]);

			}
		}, function (response) { // fail
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

		return false;
	});


	// Show the login form on the checkout page
	$('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function () {
		var $this = $(this),
			data = {
				action: $this.data('action')
			};
		// Show the ajax loader
		$('.edd-cart-ajax').show();

		$.post(edd_scripts.ajaxurl, data, function (checkout_response) {
			$('#edd_checkout_login_register').html(edd_scripts.loading);
			$('#edd_checkout_login_register').html(checkout_response);
			// Hide the ajax loader
			$('.edd-cart-ajax').hide();
		});
		return false;
	});


	// Process the login form via ajax
	$(document).on('click', '#edd_purchase_form #edd_login_fields input[type=submit]', function(e) {

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(edd_global_vars.purchase_loading);

		$(this).after('<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>');

		var data = {
			action : 'edd_process_checkout_login',
			edd_ajax : 1,
			edd_user_login : $('#edd_login_fields #edd_user_login').val(),
			edd_user_pass : $('#edd_login_fields #edd_user_pass').val()
		};

		$.post(edd_global_vars.ajaxurl, data, function(data) {

			if ( $.trim(data) == 'success' ) {
				$('.edd_errors').remove();
				window.location = edd_scripts.checkout_page;
			} else {
				$('#edd_login_fields input[type=submit]').val(complete_purchase_val);
				$('.edd-cart-ajax').remove();
				$('.edd_errors').remove();
				$('#edd-user-login-submit').before(data);
			}
		});

	});


	// Load the fields for the selected payment method
   $('select#edd-gateway, input.edd-gateway').change( function (e) {

		var payment_mode = $('#edd-gateway option:selected, input.edd-gateway:checked').val();

		if( payment_mode == '0' )
			return false;

		load_gateway( payment_mode );

		return false;
	});


	// Auto load first payment gateway
	if( edd_scripts.is_checkout == '1' && $('select#edd-gateway, input.edd-gateway').length ) {
		setTimeout( function() {
			load_gateway( edd_scripts.default_gateway );
		}, 200);
	}


	$(document).on('click', '#edd_purchase_form #edd_purchase_submit input[type=submit]', function(e) {

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(edd_global_vars.purchase_loading);

		$(this).after('<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>');

		$.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout&edd_ajax=true', function(data) {
			if ( $.trim(data) == 'success' ) {
				$('.edd_errors').remove();
				$('#edd_purchase_form').submit();
			} else {
				$('#edd_purchase_form #edd-purchase-button').val(complete_purchase_val);
				$('.edd-cart-ajax').remove();
				$('.edd_errors').remove();
				$('#edd_purchase_submit').before(data);
			}
		});

	});

});