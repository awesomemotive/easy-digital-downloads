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
		var createFunc = ( 'subscription' === eddPayPalVars.intent ) ? 'createSubscription' : 'createOrder';

		var buttonArgs = {
			onApprove: function( data, actions ) {
				console.log( 'onApprove', data );
				var formData = new FormData();
				formData.append( 'action', eddPayPalVars.approvalAction );
				formData.append( 'edd_process_paypal_nonce', nonceEl.value );

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
					console.log( 'onApprove response data', responseData );
					if ( responseData.success && responseData.data.redirect_url ) {
						window.location = responseData.data.redirect_url;
					} else {
						// Hide spinner.
						spinner.style.display = 'none';

						var errorHtml = eddPayPalVars.defaultError;
						EDD_PayPal.setErrorHtml( container, context, errorHtml );
					}
				} );
			},
			onError: function( error ) {
				// Hide spinner.
				spinner.style.display = 'none';

				error.name = '';
				EDD_PayPal.setErrorHtml( container, context, error );
			},
			onCancel: function( data ) {
				// Hide spinner.
				spinner.style.display = 'none';
			}
		};

		/*
		 * Add the `create` logic. This gets added to `createOrder` for one-time purchases
		 * or `createSubscription` for recurring.
		 */
		buttonArgs[ createFunc ] = function ( data, actions ) {
			console.log( 'create' );

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
				console.log( 'createOrder data', orderData );
				if ( orderData.data && orderData.data.paypal_order_id ) {
					// Add the nonce to the form so we can validate it later.
					if ( orderData.data.nonce ) {
						nonceEl.value = orderData.data.nonce;
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
						reject( new Error( errorHtml ) );
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
	var buyButtons = document.querySelectorAll( '.edd-paypal-checkout-buy-now' );
	for ( var i = 0; i < buyButtons.length; i++ ) {
		var element = buyButtons[ i ];
		// Skip if "Free Downloads" is enabled for this download.
		if ( element.classList.contains( 'edd-free-download' ) ) {
			return;
		}

		var wrapper = element.closest( '.edd_purchase_submit_wrapper' );
		if ( ! wrapper ) {
			return;
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
} );
