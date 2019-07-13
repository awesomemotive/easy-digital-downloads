var edd_scripts;

/**
 * Run event after the DOM is ready.
 *
 * Emulates jQuery.ready().
 * 
 * @see https://vanillajstoolkit.com/helpers/ready/
 * @param {function} fn - The function we want to run.
 * @returns {void}
 */
function eddDocIsReady( fn ) {

	// Sanity check.
	if ( 'function' !== typeof fn ) {
		return;
	}

	// If document is already loaded, run method.
	if ( 'complete' === document.readyState ) {
		return fn();
	}

	// Otherwise, wait until document is loaded.
	document.addEventListener( 'DOMContentLoaded', fn, false );
}

function eddSlideUp( target, duration ) {
	duration = duration || 400;
	target.style.transitionProperty = 'height, margin, padding';
	target.style.transitionDuration = duration + 'ms';
	target.style.boxSizing = 'border-box';
	target.style.height = target.offsetHeight + 'px';
	target.offsetHeight;
	target.style.overflow = 'hidden';
	target.style.height = 0;
	target.style.paddingTop = 0;
	target.style.paddingBottom = 0;
	target.style.marginTop = 0;
	target.style.marginBottom = 0;
	window.setTimeout( function() {
		target.style.display = 'none';
		target.style.removeProperty('height');
		target.style.removeProperty('padding-top');
		target.style.removeProperty('padding-bottom');
		target.style.removeProperty('margin-top');
		target.style.removeProperty('margin-bottom');
		target.style.removeProperty('overflow');
		target.style.removeProperty('transition-duration');
		target.style.removeProperty('transition-property');
	}, duration);
}

eddDocIsReady( function() {
	var $ = $ || jQuery;

	// Hide unneeded elements. These are things that are required in case JS breaks or isn't present
	document.querySelectorAll( '.edd-no-js' ).forEach( function( el ) {
		el.style.display = 'none';
	});
	document.querySelectorAll( 'a.edd-add-to-cart' ).forEach( function( el ) {
		el.classList.add( 'edd-has-js' );
	});

	// Send Remove from Cart requests
	$(document.body).on('click.eddRemoveFromCart', '.edd-remove-from-cart', function (event) {
		var $this  = $(this),
			item   = $this.data('cart-item'),
			action = $this.data('action'),
			id     = $this.data('download-id'),
			nonce  = $this.data('nonce'),
			data   = {
				action: action,
				cart_item: item,
				nonce: nonce
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

					if ( ( parseInt( edd_scripts.position_in_cart, 10 ) === parseInt( item, 10 ) ) || edd_scripts.has_purchase_links ) {
						window.location = window.location;
						return false;
					}

					// Remove the selected cart item.
					document.querySelectorAll( '.edd-cart' ).forEach( function( el ) {
						var parentEl = el.querySelectorAll( "[data-cart-item='" + item + "']" ).parentNode;
						parentEl.parentNode.removeChild( parentEl );
					});

					//Reset the data-cart-item attributes to match their new values in the EDD session cart array
					document.querySelectorAll( '.edd-cart' ).forEach( function( el ) {
						var cart_item_counter = 0;
						el.querySelectorAll( '[data-cart-item]' ).forEach( function( childEl ) {
							childEl.setAttribute( 'data-cart-item', cart_item_counter );
							cart_item_counter = cart_item_counter + 1;
						});
					});

					// Check to see if the purchase form(s) for this download is present on this page
					if ( document.querySelectorAll( '[id^=edd_purchase_' + id + ']' ).length ) {
						document.querySelectorAll( '[id^=edd_purchase_' + id + '] .edd_go_to_checkout' ).forEach( function( el ) {
							el.style.display = 'none';
						});
						document.querySelectorAll( '[id^=edd_purchase_' + id + '] a.edd-add-to-cart' ).forEach( function( el ) {
							el.style.display = 'block';
							el.removeAttribute( 'data-edd-loading' );
						});
						if ( edd_scripts.quantities_enabled == '1' ) {
							document.querySelectorAll( '[id^=edd_purchase_' + id + '] .edd_download_quantity_wrapper' ).forEach( function( el ) {
								el.style.display = 'block';
							});
						}
					}

					document.querySelectorAll( 'span.edd-cart-quantity' ).textContent( response.cart_quantity );
					$(document.body).trigger('edd_quantity_updated', [ response.cart_quantity ]);
					if ( edd_scripts.taxes_enabled ) {
						document.querySelectorAll( '.cart_item.edd_subtotal span' ).innerHTML( response.subtotal );
						document.querySelectorAll( '.cart_item.edd_cart_tax span' ).innerHTML( response.tax );
					}

					document.querySelectorAll( '.cart_item.edd_total span' ).innerHTML( response.total );

					if( response.cart_quantity == 0 ) {
						document.querySelectorAll('.cart_item.edd_subtotal,.edd-cart-number-of-items,.cart_item.edd_checkout,.cart_item.edd_cart_tax,.cart_item.edd_total').forEach( function( el ) {
							el.style.display = 'none';
						});
						document.querySelectorAll( '.edd-cart' ).forEach( function( el ) {
							var newLi = document.createElement( 'li' );
							if ( el.parentNode ) {
								el.parentNode.classList.add('cart-empty')
								el.parentNode.classList.remove( 'cart-not-empty' );
							}

							newLi.classList.add( 'cart_item' );
							newLi.classList.add( 'empty' );
							newLi.innerHTML = edd_scripts.empty_cart_message;
							el.appendChild( newLi );
						});
					}

					$(document.body).trigger('edd_cart_item_removed', [ response ]);
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
	$(document.body).on('click.eddAddToCart', '.edd-add-to-cart', function (e) {

		e.preventDefault();

		var $this = $(this), form = $this.closest('form');

		// Disable button, preventing rapid additions to cart during ajax request
		$this.prop('disabled', true);

		var $spinner = $this.find('.edd-loading');
		var container = $this.closest('div');

		// Show the spinner
		$this.attr('data-edd-loading', '');

		var form           = $this.parents('form').last();
		var download       = $this.data('download-id');
		var variable_price = $this.data('variable-price');
		var price_mode     = $this.data('price-mode');
		var nonce          = $this.data('nonce');
		var item_price_ids = [];
		var free_items     = true;

		if( variable_price == 'yes' ) {

			if ( form.find('.edd_price_option_' + download + '[type="hidden"]').length > 0 ) {
				item_price_ids[0] = $('.edd_price_option_' + download, form).val();
				if ( form.find('.edd-submit').data('price') && form.find('.edd-submit').data('price') > 0 ) {
					free_items = false;
				}
			} else {
				if( ! form.find('.edd_price_option_' + download + ':checked', form).length ) {
					 // hide the spinner
					$this.removeAttr( 'data-edd-loading' );
					alert( edd_scripts.select_option );
					e.stopPropagation();
					$this.prop('disabled', false);
					return false;
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
			post_data: $(form).serialize(),
			nonce: nonce,
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
				var store_redirect = edd_scripts.redirect_to_checkout == '1';
				var item_redirect  = form.find( '#edd_redirect_to_checkout' ).val() == '1';

				if( ( store_redirect && item_redirect ) || ( ! store_redirect && item_redirect ) ) {

					window.location = edd_scripts.checkout_page;

				} else {

					// Add the new item to the cart widget
					if ( edd_scripts.taxes_enabled === '1' ) {
						document.querySelectorAll( '.cart_item.edd_subtotal,.cart_item.edd_cart_tax' ).forEach( function( el ) {
							el.style.display = 'block';
						});
					}

					document.querySelectorAll( '.cart_item.edd_total,.cart_item.edd_checkout' ).forEach( function( el ) {
						el.style.display = 'block';
					});

					document.querySelectorAll( '.cart_item.empty' ).forEach( function( el ) {
						el.style.display = 'none';
					});

					document.querySelectorAll( '.widget_edd_cart_widget .edd-cart' ).forEach( function( el ) {
						var target = el.querySelector( '.edd-cart-meta' );
						document.querySelectorAll( response.cart_item ).forEach( function( cartEl ) {
							cartEl.insertBefore( target, cartEl.firstChild );
						});

						if ( el.parentNode ) {
							el.parentNode.classList.add( 'cart-not-empty' );
							el.parentNode.classList.remove( 'cart-empty' );
						}

					});

					// Update the totals
					if ( edd_scripts.taxes_enabled === '1' ) {
						document.querySelectorAll( '.edd-cart-meta.edd_subtotal span' ).forEach( function( el ) {
							el.innerHTML = response.subtotal;
						});
						document.querySelectorAll( '.edd-cart-meta.edd_cart_tax span' ).forEach( function( el ) {
							el.innerHTML = response.tax;
						});
					}

					document.querySelectorAll('.edd-cart-meta.edd_total span').forEach( function( el ) {
						el.innerHTML = response.total;
					});

					// Update the cart quantity
					var items_added = $( '.edd-cart-item-title', response.cart_item ).length;

					$('span.edd-cart-quantity').each(function() {
						$(this).text(response.cart_quantity);
						$(document.body).trigger('edd_quantity_updated', [ response.cart_quantity ]);
					});

					// Show the "number of items in cart" message
					if ( $('.edd-cart-number-of-items').css('display') == 'none') {
						document.querySelectorAll( '.edd-cart-number-of-items' ).forEach( function( el ) {
							el.style.display = 'block';
						});
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
						document.querySelectorAll( '.edd_download_purchase_form *[data-download-id="' + download + '"]' ).forEach( function( el ) {
							var parent_form = el.closest( 'form' );
							parent_form.querySelectorAll( 'a.edd-add-to-cart' ).style.display = 'none';
							if ( 'multi' !== price_mode ) {
								parent_form.querySelectorAll( '.edd_download_quantity_wrapper' ).forEach( function( qtyWrapper ) {
									eddSlideUp( qtyWrapper );
								});
							}
							parent_form.querySelectorAll( '.edd_go_to_checkout' ).forEach( function( el ) {
								el.style.display = 'block';
								el.removeAttribute( 'data-edd-loading' );
							});
						});
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

					$(document.body).trigger('edd_cart_item_added', [ response ]);

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
				action: $this.data('action'),
				nonce: $this.data('nonce'),
			};

		// Show the ajax loader
		document.querySelectorAll( '.edd-cart-ajax' ).forEach( function( el ) {
			el.style.display = 'block';
		});

		$.post(edd_scripts.ajaxurl, data, function (checkout_response) {
			document.querySelectorAll( '#edd_checkout_login_register' ).forEach( function( el ) {
				el.innerHTML = edd_scripts.loading;
			});
			document.querySelectorAll( '#edd_checkout_login_register' ).forEach( function( el ) {
				el.innerHTML = checkout_response;
			});

			// Hide the ajax loader
			document.querySelectorAll( '.edd-cart-ajax' ).forEach( function( el ) {
				el.style.display = 'none';
			});
		});
		return false;
	});

	// Process the login form via ajax
	$(document).on('click', '#edd_purchase_form #edd_login_fields input[type=submit]', function(e) {

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(edd_global_vars.purchase_loading);

		$(this).after('<span class="edd-loading-ajax edd-loading"></span>');

		var data = {
			action : 'edd_process_checkout_login',
			edd_ajax : 1,
			edd_user_login : $('#edd_login_fields #edd_user_login').val(),
			edd_user_pass : $('#edd_login_fields #edd_user_pass').val(),
			edd_login_nonce : $('#edd_login_nonce').val(),
		};

		$.post(edd_global_vars.ajaxurl, data, function(data) {

			if ( $.trim(data) == 'success' ) {
				$('.edd_errors').remove();
				window.location = edd_scripts.checkout_page;
			} else {
				$('#edd_login_fields input[type=submit]').val(complete_purchase_val);
				$('.edd-loading-ajax').remove();
				$('.edd_errors').remove();
				$('#edd-user-login-submit').before(data);
			}
		});

	});

	// Load the fields for the selected payment method
	$('select#edd-gateway, input.edd-gateway').change( function (e) {

		var payment_mode = $('#edd-gateway option:selected, input.edd-gateway:checked').val();

		if( payment_mode == '0' ) {
			return false;
		}

		edd_load_gateway( payment_mode );

		return false;
	});

	// Auto load first payment gateway
	if( edd_scripts.is_checkout == '1' ) {

		var chosen_gateway = false;
		var ajax_needed    = false;

		if ( $('select#edd-gateway, input.edd-gateway').length ) {
			chosen_gateway = $("meta[name='edd-chosen-gateway']").attr('content');
			ajax_needed    = true;
		}

		if( ! chosen_gateway ) {
			chosen_gateway = edd_scripts.default_gateway;
		}

		if ( ajax_needed ) {

			// If we need to ajax in a gateway form, send the requests for the POST.
			setTimeout( function() {
				edd_load_gateway( chosen_gateway );
			}, 200);

		} else {

			// The form is already on page, just trigger that the gateway is loaded so further action can be taken.
			$('body').trigger('edd_gateway_loaded', [ chosen_gateway ]);

		}
	}

	// Process checkout
	$(document).on('click', '#edd_purchase_form #edd_purchase_submit [type=submit]', function(e) {

		var eddPurchaseform = document.getElementById('edd_purchase_form');

		if( typeof eddPurchaseform.checkValidity === "function" && false === eddPurchaseform.checkValidity() ) {
			return;
		}

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(edd_global_vars.purchase_loading);

		$(this).prop( 'disabled', true );

		$(this).after('<span class="edd-loading-ajax edd-loading"></span>');

		$.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout&edd_ajax=true', function(data) {
			if ( $.trim(data) == 'success' ) {
				$('.edd_errors').remove();
				document.querySelectorAll( '.edd-error' ).forEach( function( el ) {
					el.style.display = 'none';
				});
				$(eddPurchaseform).submit();
			} else {
				$('#edd-purchase-button').val(complete_purchase_val);
				$('.edd-loading-ajax').remove();
				$('.edd_errors').remove();
				document.querySelectorAll( '.edd-error' ).forEach( function( el ) {
					el.style.display = 'none';
				});
				$( edd_global_vars.checkout_error_anchor ).before(data);
				$('#edd-purchase-button').prop( 'disabled', false );

				$(document.body).trigger( 'edd_checkout_error', [ data ] );
			}
		});

	});

	// Update state field
	$(document.body).on('change', '#edd_cc_address input.card_state, #edd_cc_address select, #edd_address_country', update_state_field);

	function update_state_field() {

		var $this = $(this);
		var $form;
		var is_checkout = typeof edd_global_vars !== 'undefined';
		var field_name  = 'card_state';
		if ( $(this).attr('id') == 'edd_address_country' ) {
			field_name = 'edd_address_state';
		}

		var state_inputs = document.getElementById(field_name );

		// If the country is being changed, and there is a state field being shown...
		if( 'card_state' != $this.attr('id') && null != state_inputs ) {
			var nonce = $(this).data('nonce');

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action: 'edd_get_shop_states',
				country: $this.val(),
				field_name: field_name,
				nonce: nonce,
			};

			$.ajax({
				type: "POST",
				data: postData,
				url: edd_scripts.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					if ( is_checkout ) {
						$form = $("#edd_purchase_form");
					} else {
						$form = $this.closest("form");
					}

					var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="edd_address_state"], select[name="edd_address_state"]';

					if( 'nostates' == $.trim(response) ) {
						var text_field = '<input type="text" id=' + field_name + ' name="card_state" class="card-state edd-input required" value=""/>';
						$form.find(state_inputs).replaceWith( text_field );
					} else {
						$form.find(state_inputs).replaceWith( response );
					}

					if ( is_checkout ) {
						$(document.body).trigger('edd_cart_billing_address_updated', [ response ]);
					}

				}
			}).fail(function (data) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			}).done(function (data) {
				if ( is_checkout ) {
					recalculate_taxes();
				}
			});
		} else {
			if ( is_checkout ) {
				recalculate_taxes();
			}
		}

		return false;
	}

	// If is_checkout, recalculate sales tax on postalCode change.
	$(document.body).on('change', '#edd_cc_address input[name=card_zip]', function () {
		if (typeof edd_global_vars !== 'undefined') {
			recalculate_taxes();
		}
	});
});

// Load a payment gateway
function edd_load_gateway( payment_mode ) {

	// Show the ajax loader
	document.querySelectorAll( '.edd-cart-ajax' ).forEach( function( el ) {
		el.style.display = 'block';
	});
	document.querySelectorAll( '#edd_purchase_form_wrap' ).forEach( function( el ) {
		var loadingSpan = document.createElement( 'span' );
		loadingSpan.classList.add( 'edd-loading-ajax' );
		loadingSpan.classList.add( 'edd-loading' );

		el.innerHTML = loadingSpan;
	});

	var nonce = jQuery('#edd-gateway-' + payment_mode).data(payment_mode+'-nonce');
	var url   = edd_scripts.ajaxurl;

	if ( url.indexOf( '?' ) > 0 ) {
		url = url + '&';
	} else {
		url = url + '?';
	}

	url = url + 'payment-mode=' + payment_mode;

	jQuery.post(url, { action: 'edd_load_gateway', edd_payment_mode: payment_mode, nonce: nonce },
		function(response){
			document.querySelectorAll( '#edd_purchase_form_wrap' ).forEach( function( el ) {
				el.innerHTML = response;
			});
			document.querySelectorAll( '.edd-no-js' ).forEach( function( el ) {
				el.style.display = 'none';
			});
			jQuery('body').trigger('edd_gateway_loaded', [ payment_mode ]);
		}
	);

}
