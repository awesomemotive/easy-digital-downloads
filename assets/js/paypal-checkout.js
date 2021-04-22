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
	isPayPal() {
		let chosenGateway = false;
		if ( jQuery('select#edd-gateway, input.edd-gateway').length ) {
			chosenGateway = jQuery("meta[name='edd-chosen-gateway']").attr('content');
		}

		if ( ! chosenGateway && edd_scripts.default_gateway ) {
			chosenGateway = edd_scripts.default_gateway;
		}

		return 'paypal' === chosenGateway;
	},

	/**
	 * Refreshes the page when adding or removing a 100% discount.
	 *
	 * @param e
	 * @param total
	 */
	maybeRefreshPage( e, { total_plain: total } ) {
		if ( 0 === total && EDD_PayPal.isPayPal() ) {
			window.location.reload();
		} else if ( ! EDD_PayPal.isMounted && EDD_PayPal.isPayPal() && total > 0 ) {
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
	setErrorHtml( container, context, errorHtml ) {
		if ( 'checkout' === context && 'undefined' !== typeof edd_global_vars && edd_global_vars.checkout_error_anchor ) {
			// Checkout errors.
			jQuery( edd_global_vars.checkout_error_anchor ).before( errorHtml );
		} else if ( 'buy_now' === context ) {
			// Buy Now errors
			const form = container.closest( '.edd_download_purchase_form' );
			const errorWrapper = form ? form.querySelector( '.edd-paypal-checkout-buy-now-error-wrapper' ) : false;

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
	 *
	 * @todo fetch/Promise polyfill needed
	 */
	initButtons( container, context ) {
		EDD_PayPal.isMounted = true;

		paypal.Buttons( {
			createOrder: function ( data, actions ) {
				console.log( 'createOrder' );

				// Clear errors at the start of each attempt.
				const form = ( 'checkout' === context ) ? document.getElementById( 'edd_purchase_form' ) : container.closest( '.edd_download_purchase_form' );
				const errorWrapper = form.querySelector( '.edd-paypal-checkout-buy-now-error-wrapper' );
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
						return orderData.data.paypal_order_id;
					} else {
						const errorHtml = orderData.data.error_message ? orderData.data.error_message : orderData.data;

						return new Promise( function( resolve, reject ) {
							reject( new Error( errorHtml ) );
						} );
					}
				} );
			},
			onApprove: function( data, actions ) {
				// @todo nonce?
				console.log( 'onApprove' );
				const formData = new FormData();
				formData.append( 'action', 'edd_capture_paypal_order' );
				formData.append( 'paypal_order_id', data.orderID );

				return fetch( edd_scripts.ajaxurl, {
					method: 'POST',
					body: formData
				} ).then( function( response ) {
					return response.json();
				} ).then( function( responseData ) {
					console.log( 'onApprove response data', responseData );
					if ( responseData.success && responseData.data ) {
						window.location = responseData.data;
					} else {
						const errorHtml = 'Generic error'; // @todo markup, il8n
						EDD_PayPal.setErrorHtml( container, context, errorHtml );
					}
				} );
			},
			onError: function( error ) {
				console.log( 'PayPal gateway error', error );
				const errorHtml = error.err ? error.err : 'Generic error'; // @todo markup, il8n
				EDD_PayPal.setErrorHtml( container, context, error );
			}
		} ).render( container );
	}
};

/**
 * Initialize on checkout.
 */
jQuery( document.body ).on( 'edd_gateway_loaded', function( e, gateway ) {
	if ( 'paypal' !== gateway ) {
		return;
	}

	EDD_PayPal.init();
} );

/**
 * Initialize Buy Now buttons.
 */
jQuery( document ).ready( function( $ ) {
	const buyButtons = document.querySelectorAll( '.edd-paypal-checkout-buy-now' );
	for ( let i = 0; i < buyButtons.length; i++ ) {
		const element = buyButtons[ i ];
		// Skip if "Free Downloads" is enabled for this download.
		if ( element.classList.contains( 'edd-free-download' ) ) {
			return;
		}

		const wrapper = element.closest( '.edd_purchase_submit_wrapper' );
		if ( ! wrapper ) {
			return;
		}

		// Clear contents of the wrapper.
		wrapper.innerHTML = '';

		// Add error container after the wrapper.
		const errorNode = document.createElement( 'div' );
		errorNode.classList.add( 'edd-paypal-checkout-buy-now-error-wrapper' );
		wrapper.before( errorNode );

		// Initialize button.
		EDD_PayPal.initButtons( wrapper, 'buy_now' );
	}
} );
