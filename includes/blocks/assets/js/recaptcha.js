; ( function ( document, $ ) {
	'use strict';

	let isExecuting = false;

	/**
	 * Executes reCAPTCHA and updates the hidden input with the token.
	 *
	 * @param {Function} onSuccess Optional callback for successful token generation.
	 * @param {Function} onError   Optional callback for errors.
	 */
	function executeRecaptcha( onSuccess, onError ) {
		if ( !EDDreCAPTCHA.sitekey ) {
			return;
		}

		// Prevent duplicate executions
		if ( isExecuting ) {
			console.warn( 'reCAPTCHA execution already in progress' );
			return;
		}

		// Check if grecaptcha is available and ready
		if ( typeof grecaptcha === 'undefined' || typeof grecaptcha.ready !== 'function' ) {
			console.warn( 'reCAPTCHA not loaded yet, skipping validation' );
			if ( onError ) {
				onError( 'reCAPTCHA not loaded' );
			}
			return;
		}

		isExecuting = true;

		grecaptcha.ready( function () {
			try {
				grecaptcha
					.execute( EDDreCAPTCHA.sitekey, {
						action: EDDreCAPTCHA.action,
					} )
					.then( ( token ) => {
						const input = document.getElementById( 'edd-blocks-recaptcha' );
						if ( input ) {
							input.value = token;
						}
						isExecuting = false;
						if ( onSuccess ) {
							onSuccess( token );
						}
					} )
					.catch( ( error ) => {
						console.error( 'reCAPTCHA execution error:', error );
						isExecuting = false;
						if ( onError ) {
							onError( error );
						}
					} );
			} catch ( error ) {
				console.error( 'reCAPTCHA error:', error );
				isExecuting = false;
				if ( onError ) {
					onError( error );
				}
			}
		} );
	}

	/**
	 * Initialize checkout-specific functionality.
	 * Refreshes reCAPTCHA token on a timer for checkout forms.
	 */
	function initializeCheckout() {
		let tokenRefresh;

		function clearTokenRefresh() {
			if ( tokenRefresh ) {
				clearInterval( tokenRefresh );
				tokenRefresh = null;
			}
		}

		// Start token refresh when gateway loads
		$( document.body ).on( 'edd_gateway_loaded', function ( e, gateway ) {
			clearTokenRefresh();
			tokenRefresh = setInterval( function () {
				executeRecaptcha( null, function ( error ) {
					// On checkout, show critical error if reCAPTCHA fails
					const form = document.getElementById( 'edd_purchase_form' );
					if ( form ) {
						form.innerHTML = '<div class="edd_errors edd-alert edd-alert-error">' + EDDreCAPTCHA.checkoutFailure + '</div>';
					}
				} );
			}, 1000 * 110 );
			executeRecaptcha();
		} );

		// Clear interval on form submission
		$( document.body ).on( 'submit', '#edd_purchase_form', clearTokenRefresh );

		// Clear interval when checkout is unloaded
		$( document.body ).on( 'edd_checkout_unloaded', clearTokenRefresh );
	}

	/**
	 * Initialize form validation-specific functionality.
	 * Handles reCAPTCHA validation on form submission.
	 */
	function initializeFormValidation() {
		const reCAPTCHAinput = document.querySelector( 'input#edd-blocks-recaptcha' );
		if ( !reCAPTCHAinput ) {
			return;
		}

		// Get form action and submit button
		EDDreCAPTCHA.action = document.querySelector( 'input[name="edd_action"]' ).value;
		EDDreCAPTCHA.submit = document.querySelector( 'input[name="edd_submit"]' ).value;
		const submitButton = document.querySelector( '#' + EDDreCAPTCHA.submit );

		reCAPTCHAinput.addEventListener( 'invalid', function () {
			executeRecaptcha(
				function ( token ) {
					// Validate token via AJAX
					$.ajax( {
						type: 'POST',
						data: {
							action: 'edd_recaptcha_validate',
							token: token,
						},
						url: EDDreCAPTCHA.ajaxurl,
						success: function ( response ) {
							if ( response.success ) {
								reCAPTCHAinput.value = token;
								submitButton.click();
							} else {
								reCAPTCHAinput.value = '';
								var errorNode = document.createElement( 'div' );
								errorNode.classList.add( 'edd_errors', 'edd-alert', 'edd-alert-error', response.data.error );
								errorNode.innerHTML = '<p class="edd_error"><strong>' + EDDreCAPTCHA.error + '</strong>: ' + response.data.message + '</p>';
								submitButton.closest( 'form' ).before( errorNode );
							}
						},
					} ).fail( function ( response ) {
						reCAPTCHAinput.value = '';
						console.error( 'reCAPTCHA AJAX error:', response );
					} );
				},
				function ( error ) {
					// Show error to user
					reCAPTCHAinput.value = '';
					var errorNode = document.createElement( 'div' );
					errorNode.classList.add( 'edd_errors', 'edd-alert', 'edd-alert-error', 'invalid_recaptcha_bad' );
					errorNode.innerHTML = '<p class="edd_error"><strong>' + EDDreCAPTCHA.error + '</strong>: ' + EDDreCAPTCHA.error_message + '</p>';
					submitButton.closest( 'form' ).after( errorNode );
				}
			);
		} );
	}

	// Initialize based on context
	if ( EDDreCAPTCHA.context === 'checkout' ) {
		initializeCheckout();
	} else {
		$( document ).ready( function () {
			initializeFormValidation();
		} );
	}

} )( document, jQuery );
