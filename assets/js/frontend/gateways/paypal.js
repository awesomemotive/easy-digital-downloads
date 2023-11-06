/* global eddPayPalVars, edd_global_vars */

var EDD_PayPal = {
	isMounted: false,

	/**
	 * Initializes PayPal buttons and sets up some events.
	 */
	init: function() {
		if ( document.getElementById( 'edd-paypal-container' ) ) {
			this.initButtons( '#edd-paypal-container', 'checkout' );
		}

		jQuery( document.body ).on( 'edd_discount_applied', this.maybeRefreshPage );
		jQuery( document.body ).on( 'edd_discount_removed', this.maybeRefreshPage );
	},

	/**
	 * Determines whether or not the selected gateway is PayPal.
	 * @returns {boolean}
	 */
	isPayPal: function() {
		var chosenGateway = false;
		if ( jQuery('select#edd-gateway, input.edd-gateway').length ) {
			chosenGateway = jQuery("meta[name='edd-chosen-gateway']").attr('content');
		}

		if ( ! chosenGateway && edd_scripts.default_gateway ) {
			chosenGateway = edd_scripts.default_gateway;
		}

		return 'paypal_commerce' === chosenGateway;
	},

	/**
	 * Refreshes the page when adding or removing a 100% discount.
	 *
	 * @param e
	 * @param {object} data
	 */
	maybeRefreshPage: function( e, data ) {
		if ( 0 === data.total_plain && EDD_PayPal.isPayPal() ) {
			window.location.reload();
		} else if ( ! EDD_PayPal.isMounted && EDD_PayPal.isPayPal() && data.total_plain > 0 ) {
			window.location.reload();
		}
	},

	/**
	 * Sets the error HTML, depending on the context.
	 *
	 * @param {string|HTMLElement} container
	 * @param {string} context
	 * @param {string} errorHtml
	 */
	setErrorHtml: function( container, context, errorHtml ) {
		// Format errors.

		if ( 'checkout' === context && 'undefined' !== typeof edd_global_vars && edd_global_vars.checkout_error_anchor ) {
			// Checkout errors.
			var errorWrapper = document.getElementById( 'edd-paypal-errors-wrap' );
			if ( errorWrapper ) {
				errorWrapper.innerHTML = errorHtml;
			}
		} else if ( 'buy_now' === context ) {
			// Buy Now errors
			var form = container.closest( '.edd_download_purchase_form' );
			var errorWrapper = form ? form.querySelector( '.edd-paypal-checkout-buy-now-error-wrapper' ) : false;

			if ( errorWrapper ) {
				errorWrapper.innerHTML = errorHtml;
			}
		}

		jQuery( document.body ).trigger( 'edd_checkout_error', [ errorHtml ] );
	},

	/**
	 * Initializes PayPal buttons
	 *
	 * @param {string|HTMLElement} container Element to render the buttons in.
	 * @param {string} context   Context for the button. Either `checkout` or `buy_now`.
	 */
	initButtons: function( container, context ) {
		EDD_PayPal.isMounted = true;

		paypal.Buttons( EDD_PayPal.getButtonArgs( container, context ) ).render( container );

		document.dispatchEvent( new CustomEvent( 'edd_paypal_buttons_mounted' ) );
	},

	/**
	 * Retrieves the arguments used to build the PayPal button.
	 *
	 * @param {string|HTMLElement} container Element to render the buttons in.
	 * @param {string} context   Context for the button. Either `checkout` or `buy_now`.
	 */
	getButtonArgs: function ( container, context ) {
		var form = ( 'checkout' === context ) ? document.getElementById( 'edd_purchase_form' ) : container.closest( '.edd_download_purchase_form' );
		var errorWrapper = ( 'checkout' === context ) ? form.querySelector( '#edd-paypal-errors-wrap' ) : form.querySelector( '.edd-paypal-checkout-buy-now-error-wrapper' );
		var spinner = ( 'checkout' === context ) ? document.getElementById( 'edd-paypal-spinner' ) : form.querySelector( '.edd-paypal-spinner' );
		var nonceEl = form.querySelector( 'input[name="edd_process_paypal_nonce"]' );
		var tokenEl = form.querySelector( 'input[name="edd-process-paypal-token"]' );
		var createFunc = ( 'subscription' === eddPayPalVars.intent ) ? 'createSubscription' : 'createOrder';
		var requiredInputs = form.querySelectorAll( '[required]' );

		var buttonArgs = {
			onInit: function ( data, actions ) {
				actions.disable();
				if ( form.checkValidity() ) {
					actions.enable();
				}
				requiredInputs.forEach( function ( element ) {
					element.addEventListener( 'change', function ( e ) {
						if ( form.checkValidity() ) {
							actions.enable();
						} else {
							actions.disable();
						}
					} );
				} );
			},
			onClick: function ( data, actions ) {
				if ( ! form.reportValidity() ) {
					return false;
				}

				spinner.style.display = 'block';

				// Clear errors at the start of each attempt.
				if ( errorWrapper ) {
					errorWrapper.innerHTML = '';
				}

				// Submit the form via AJAX.
				return fetch( edd_scripts.ajaxurl, {
					method: 'POST',
					body: new FormData( form )
				} ).then( function ( response ) {
					return response.json();
				} ).then( function ( response ) {
					if ( ! response.success ) {
						// Error message.
						var errorHtml = eddPayPalVars.defaultError;
						if ( response.data && 'string' === typeof response.data ) {
							errorHtml = response.data;
						} else if ( 'string' === typeof response ) {
							errorHtml = response;
						}

						spinner.style.display = 'none';
						EDD_PayPal.setErrorHtml( errorWrapper, context, errorHtml );

						return false;
					}
				} );
			},
			onApprove: function( data, actions ) {
				var formData = new FormData();
				formData.append( 'action', eddPayPalVars.approvalAction );
				formData.append( 'edd_process_paypal_nonce', nonceEl.value );
				formData.append( 'token', tokenEl.getAttribute('data-token') );
				formData.append( 'timestamp', tokenEl.getAttribute('data-timestamp' ) );

				if ( data.orderID ) {
					formData.append( 'paypal_order_id', data.orderID );
				}
				if ( data.subscriptionID ) {
					formData.append( 'paypal_subscription_id', data.subscriptionID );
				}

				return fetch( edd_scripts.ajaxurl, {
					method: 'POST',
					body: formData
				} ).then( function( response ) {
					return response.json();
				} ).then( function( responseData ) {
					if ( responseData.success && responseData.data.redirect_url ) {
						window.location = responseData.data.redirect_url;
					} else {
						// Hide spinner.
						spinner.style.display = 'none';

						var errorHtml = responseData.data.message ? responseData.data.message : eddPayPalVars.defaultError;

						EDD_PayPal.setErrorHtml( container, context, errorHtml );

						// @link https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
						if ( responseData.data.retry ) {
							return actions.restart();
						}
					}
				} );
			},
			onError: function( error ) {
				// Hide spinner.
				spinner.style.display = 'none';

				EDD_PayPal.setErrorHtml( container, context, error );
			},
			onCancel: function( data ) {
				// Hide spinner.
				spinner.style.display = 'none';

				const formData = new FormData();
				formData.append( 'action', 'edd_cancel_paypal_order' );
				return fetch( edd_scripts.ajaxurl, {
					method: 'POST',
					body: formData
				} ).then( function ( response ) {
					return response.json();
				} ).then( function ( responseData ) {
					if ( responseData.success ) {
						const nonces = responseData.data.nonces;
						Object.keys( nonces ).forEach( function ( key ) {
							var gatewaySelector = document.getElementById( 'edd-gateway-' + key );
							if ( gatewaySelector ) {
								gatewaySelector.setAttribute( 'data-' + key + '-nonce', nonces[ key ] );
							}
						} );
					}
				} );
			}
		};

		/*
		 * Add style if we have any
		 *
		 * @link https://developer.paypal.com/docs/checkout/integration-features/customize-button/
		 */
		if ( eddPayPalVars.style ) {
			buttonArgs.style = eddPayPalVars.style;
		}

		/*
		 * Add the `create` logic. This gets added to `createOrder` for one-time purchases
		 * or `createSubscription` for recurring.
		 */
		buttonArgs[ createFunc ] = function ( data, actions ) {
			// Show spinner.
			spinner.style.display = 'block';

			// Clear errors at the start of each attempt.
			if ( errorWrapper ) {
				errorWrapper.innerHTML = '';
			}

			// Submit the form via AJAX.
			return fetch( edd_scripts.ajaxurl, {
				method: 'POST',
				body: new FormData( form )
			} ).then( function( response ) {
				return response.json();
			} ).then( function( orderData ) {
				if ( orderData.data && orderData.data.paypal_order_id ) {

					// Add the nonce to the form so we can validate it later.
					if ( orderData.data.nonce ) {
						nonceEl.value = orderData.data.nonce;
					}

					// Add the token to the form so we can validate it later.
					if ( orderData.data.token ) {
						jQuery(tokenEl).attr( 'data-token', orderData.data.token );
						jQuery(tokenEl).attr( 'data-timestamp', orderData.data.timestamp );
					}

					return orderData.data.paypal_order_id;
				} else {
					// Error message.
					var errorHtml = eddPayPalVars.defaultError;
					if ( orderData.data && 'string' === typeof orderData.data ) {
						errorHtml = orderData.data;
					} else if ( 'string' === typeof orderData ) {
						errorHtml = orderData;
					}

					return new Promise( function( resolve, reject ) {
						reject( errorHtml );
					} );
				}
			} );
		};

		return buttonArgs;
	}
};

/**
 * Initialize on checkout.
 */
jQuery( document.body ).on( 'edd_gateway_loaded', function( e, gateway ) {
	if ( 'paypal_commerce' !== gateway ) {
		return;
	}

	EDD_PayPal.init();
} );

/**
 * Initialize Buy Now buttons.
 */
jQuery( document ).ready( function( $ ) {
	EDDPayPalBuyNowbuttons();
} );

export function EDDPayPalBuyNowbuttons() {
	var buyButtons = document.querySelectorAll( '.edd-paypal-checkout-buy-now' );
	for ( var i = 0; i < buyButtons.length; i++ ) {
		var element = buyButtons[ i ];
		// Skip if "Free Downloads" is enabled for this download.
		if ( element.classList.contains( 'edd-free-download' ) ) {
			continue;
		}

		var wrapper = element.closest( '.edd_purchase_submit_wrapper' );
		if ( ! wrapper ) {
			continue;
		}

		// Find the closest input with a class of edd_action_input and get it's value.
		var edd_input_action = element.closest( 'form' ).querySelector( '.edd_action_input' ).value;
		if ( 'add_to_cart' === edd_input_action ) {
			continue;
		}

		// Clear contents of the wrapper.
		wrapper.innerHTML = '';

		// Add error container after the wrapper.
		var errorNode = document.createElement( 'div' );
		errorNode.classList.add( 'edd-paypal-checkout-buy-now-error-wrapper' );
		wrapper.before( errorNode );

		// Add spinner container.
		var spinnerWrap = document.createElement( 'span' );
		spinnerWrap.classList.add( 'edd-paypal-spinner', 'edd-loading-ajax', 'edd-loading' );
		spinnerWrap.style.display = 'none';
		wrapper.after( spinnerWrap );

		// Initialize button.
		EDD_PayPal.initButtons( wrapper, 'buy_now' );
	}
}
