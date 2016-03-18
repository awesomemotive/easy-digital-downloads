var edd_scripts;
jQuery(document).ready(function ($) {

	// Hide unneeded elements. These are things that are required in case JS breaks or isn't present
	$('.edd-no-js').hide();
	$('a.edd-add-to-cart').addClass('edd-has-js');

	// Send Remove from Cart requests
	$('body').on('click.eddRemoveFromCart', '.edd-remove-from-cart', function (event) {
		var $this  = $(this),
			item   = $this.data('cart-item'),
			action = $this.data('action'),
			id     = $this.data('download-id'),
			data   = {
				action: action,
				cart_item: item
			};

		 $.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: edd_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {
				if (response.removed) {
					if ( parseInt( edd_scripts.position_in_cart, 10 ) === parseInt( item, 10 ) ) {
						window.location = window.location;
						return false;
					}

					// Remove the selected cart item
					$('.edd-cart').find("[data-cart-item='" + item + "']").parent().remove();

					//Reset the data-cart-item attributes to match their new values in the EDD session cart array
					var cart_item_counter = 0;
					$('.edd-cart').find("[data-cart-item]").each(function(){
						$(this).attr('data-cart-item', cart_item_counter);
						cart_item_counter = cart_item_counter + 1;
					});

					// Check to see if the purchase form(s) for this download is present on this page
					if( $( '[id^=edd_purchase_' + id + ']' ).length ) {
						$( '[id^=edd_purchase_' + id + '] .edd_go_to_checkout' ).hide();
						$( '[id^=edd_purchase_' + id + '] a.edd-add-to-cart' ).show().removeAttr('data-edd-loading');
						if ( edd_scripts.quantities_enabled == '1' ) {
							$( '[id^=edd_purchase_' + id + '] .edd_download_quantity_wrapper' ).show();
						}
					}

					$('span.edd-cart-quantity').text( response.cart_quantity );
					$('body').trigger('edd_quantity_updated', [ response.cart_quantity ]);
					if ( edd_scripts.taxes_enabled ) {
						$('.cart_item.edd_subtotal span').html( response.subtotal );
						$('.cart_item.edd_cart_tax span').html( response.tax );
					}

					$('.cart_item.edd_total span').html( response.total );

					if( response.cart_quantity == 0 ) {
						$('.cart_item.edd_subtotal,.edd-cart-number-of-items,.cart_item.edd_checkout,.cart_item.edd_cart_tax,.cart_item.edd_total').hide();
						$('.edd-cart').append('<li class="cart_item empty">' + edd_scripts.empty_cart_message + '</li>');
					}

					$('body').trigger('edd_cart_item_removed', [ response ]);
				}
			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		}).done(function (response) {

		});

		return false;
	});

	// Send Add to Cart request
	$('body').on('click.eddAddToCart', '.edd-add-to-cart', function (e) {

		e.preventDefault();

		var $this = $(this), form = $this.closest('form');

		// Disable button, preventing rapid additions to cart during ajax request
		$this.prop('disabled', true);

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

		var form           = $this.parents('form').last();
		var download       = $this.data('download-id');
		var variable_price = $this.data('variable-price');
		var price_mode     = $this.data('price-mode');
		var item_price_ids = [];
		var free_items     = true;

		if( variable_price == 'yes' ) {

			if ( form.find('.edd_price_option_' + download).is('input:hidden') ) {
				item_price_ids[0] = $('.edd_price_option_' + download, form).val();
				if ( form.find('.edd-submit').data('price') && form.find('.edd-submit').data('price') > 0 ) {
					free_items = false;
				}
			} else {
				if( ! form.find('.edd_price_option_' + download + ':checked', form).length ) {
					 // hide the spinner
					$this.removeAttr( 'data-edd-loading' );
					alert( edd_scripts.select_option );
					return;
				}

				form.find('.edd_price_option_' + download + ':checked', form).each(function( index ) {
					item_price_ids[ index ] = $(this).val();

					// If we're still only at free items, check if this one is free also
					if ( true === free_items ) {
						var item_price = $(this).data('price');
						if ( item_price && item_price > 0 ) {
							// We now have a paid item, we can't use add_to_cart
							free_items = false;
						}
					}

				});
			}

		} else {
			item_price_ids[0] = download;
			if ( $this.data('price') && $this.data('price') > 0 ) {
				free_items = false;
			}
		}

		// If we've got nothing but free items being added, change to add_to_cart
		if ( free_items ) {
			form.find('.edd_action_input').val('add_to_cart');
		}

		if( 'straight_to_gateway' == form.find('.edd_action_input').val() ) {
			form.submit();
			return true; // Submit the form
		}

		var action = $this.data('action');
		var data   = {
			action: action,
			download_id: download,
			price_ids : item_price_ids,
			post_data: $(form).serialize()
		};

		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: edd_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {
				if( edd_scripts.redirect_to_checkout == '1' && form.find( '#edd_redirect_to_checkout' ).val() == '1' ) {

					window.location = edd_scripts.checkout_page;

				} else {

					// Add the new item to the cart widget
					if ( edd_scripts.taxes_enabled === '1' ) {
						$('.cart_item.edd_subtotal').show();
						$('.cart_item.edd_cart_tax').show();
					}

					$('.cart_item.edd_total').show();
					$('.cart_item.edd_checkout').show();

					if ($('.cart_item.empty').length) {
						$(response.cart_item).insertBefore('.edd-cart-meta:first');
						$('.cart_item.empty').hide();
					} else {
						$(response.cart_item).insertBefore('.edd-cart-meta:first');
					}

					// Update the totals
					if ( edd_scripts.taxes_enabled === '1' ) {
						$('.edd-cart-meta.edd_subtotal span').html( response.subtotal );
						$('.edd-cart-meta.edd_cart_tax span').html( response.tax );
					}

					$('.edd-cart-meta.edd_total span').html( response.total );

					// Update the cart quantity
					var items_added = $( '.edd-cart-item-title', response.cart_item ).length;

					$('span.edd-cart-quantity').each(function() {
						$(this).text(response.cart_quantity);
						$('body').trigger('edd_quantity_updated', [ response.cart_quantity ]);
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
					if( $( '.edd_download_purchase_form' ).length && ( variable_price == 'no' || ! form.find('.edd_price_option_' + download).is('input:hidden') ) ) {
						var parent_form = $('.edd_download_purchase_form *[data-download-id="' + download + '"]').parents('form');
						$( 'a.edd-add-to-cart', parent_form ).hide();
						if( price_mode != 'multi' ) {
							parent_form.find('.edd_download_quantity_wrapper').slideUp();
						}
						$( '.edd_go_to_checkout', parent_form ).show().removeAttr( 'data-edd-loading' );
					}

					if( response != 'incart' ) {
						// Show the added message
						$('.edd-cart-added-alert', container).fadeIn();
						setTimeout(function () {
							$('.edd-cart-added-alert', container).fadeOut();
						}, 3000);
					}

					// Re-enable the add to cart button
					$this.prop('disabled', false);

					$('body').trigger('edd_cart_item_added', [ response ]);

				}
			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		}).done(function (response) {

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

		edd_load_gateway( payment_mode );

		return false;
	});

	// Auto load first payment gateway
	if( edd_scripts.is_checkout == '1' && $('select#edd-gateway, input.edd-gateway').length ) {
		setTimeout( function() {
			edd_load_gateway( edd_scripts.default_gateway );
		}, 200);
	}

	$(document).on('click', '#edd_purchase_form #edd_purchase_submit input[type=submit]', function(e) {

		var eddPurchaseform = document.getElementById('edd_purchase_form');

		if( typeof eddPurchaseform.checkValidity === "function" && false === eddPurchaseform.checkValidity() ) {
			return;
		}

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(edd_global_vars.purchase_loading);

		$(this).after('<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>');

		$.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout&edd_ajax=true', function(data) {
			if ( $.trim(data) == 'success' ) {
				$('.edd_errors').remove();
				$('.edd-error').hide();
				$(eddPurchaseform).submit();
			} else {
				$('#edd-purchase-button').val(complete_purchase_val);
				$('.edd-cart-ajax').remove();
				$('.edd_errors').remove();
				$('.edd-error').hide();
				$('#edd_purchase_submit').before(data);
			}
		});

	});

});

function edd_load_gateway( payment_mode ) {

	// Show the ajax loader
	jQuery('.edd-cart-ajax').show();
	jQuery('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');

	var url = edd_scripts.ajaxurl;

	if ( url.indexOf( '?' ) > 0 ) {
		url = url + '&';
	} else {
		url = url + '?';
	}

	url = url + 'payment-mode=' + payment_mode;

	jQuery.post(url, { action: 'edd_load_gateway', edd_payment_mode: payment_mode },
		function(response){
			jQuery('#edd_purchase_form_wrap').html(response);
			jQuery('.edd-no-js').hide();
		}
	);

}
