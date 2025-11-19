/**
 * Easy Digital Downloads CAPTCHA Core
 *
 * Provider-agnostic CAPTCHA integration for checkout and form validation.
 *
 * @package EDD\Captcha
 * @since 3.6.1
 */

; ( function ( document, $ ) {
	'use strict';

	/**
	 * Executes CAPTCHA and updates the hidden input with the token.
	 *
	 * Delegates to the provider-specific handler defined by globalThis.EDDCaptchaHandler.
	 *
	 * @param {Function} onSuccess Optional callback for successful token generation.
	 * @param {Function} onError   Optional callback for errors.
	 */
	function executeCaptcha( onSuccess, onError ) {
		if ( ! globalThis.EDDreCAPTCHA.sitekey ) {
			return;
		}

		// Check if handler is available.
		if ( typeof globalThis.EDDCaptchaHandler === 'undefined' || typeof globalThis.EDDCaptchaHandler.execute !== 'function' ) {
			console.error( 'CAPTCHA handler not loaded' );
			if ( onError ) {
				onError( 'Handler not loaded' );
			}
			return;
		}

		// Delegate to the provider-specific handler.
		globalThis.EDDCaptchaHandler.execute( onSuccess, onError );
	}

	/**
	 * Initialize checkout-specific functionality.
	 * Refreshes CAPTCHA token on a timer if the provider requires it.
	 */
	function initializeCheckout() {
		let tokenRefresh;

		function clearTokenRefresh() {
			if ( tokenRefresh ) {
				clearInterval( tokenRefresh );
				tokenRefresh = null;
			}
		}

		// Start token refresh when gateway loads.
		$( document.body ).on( 'edd_gateway_loaded', function ( e, gateway ) {
			clearTokenRefresh();

			// Only set up token refresh if the provider needs it.
			// (e.g., reCAPTCHA v3 tokens expire after 2 minutes)
			if ( globalThis.EDDCaptchaHandler && globalThis.EDDCaptchaHandler.needsRefresh ) {
				tokenRefresh = setInterval( function () {
					executeCaptcha( null, function ( error ) {
						// On checkout, show critical error if CAPTCHA fails.
						const form = document.getElementById( 'edd_purchase_form' );
						if ( form ) {
							form.innerHTML = '<div class="edd_errors edd-alert edd-alert-error">' + globalThis.EDDreCAPTCHA.checkoutFailure + '</div>';
						}
					} );
				}, 1000 * 110 ); // Refresh every 110 seconds (before 2-minute expiry).
			}

			executeCaptcha();
		} );

		// Clear interval on form submission.
		$( document.body ).on( 'submit', '#edd_purchase_form', clearTokenRefresh );

		// Clear interval when checkout is unloaded.
		$( document.body ).on( 'edd_checkout_unloaded', clearTokenRefresh );
	}

	/**
	 * Initialize form validation-specific functionality.
	 * Handles CAPTCHA validation on form submission.
	 */
	function initializeFormValidation() {
		const captchaInput = document.querySelector( 'input#edd-blocks-recaptcha' );
		if ( ! captchaInput ) {
			return;
		}

		// Get form action and submit button.
		globalThis.EDDreCAPTCHA.action = document.querySelector( 'input[name="edd_action"]' ).value;
		globalThis.EDDreCAPTCHA.submit = document.querySelector( 'input[name="edd_submit"]' ).value;
		const submitButton = document.querySelector( '#' + globalThis.EDDreCAPTCHA.submit );
		let isProcessing = false;

		/**
		 * Remove existing CAPTCHA error messages.
		 */
		function removeExistingErrors() {
			const existingErrors = document.querySelectorAll( '.edd_errors.invalid_captcha_bad, .edd_errors.invalid_captcha' );
			existingErrors.forEach( function ( error ) {
				error.remove();
			} );
		}

		captchaInput.addEventListener( 'invalid', function ( e ) {
			e.preventDefault();

			// Prevent multiple simultaneous executions.
			if ( isProcessing ) {
				return;
			}

			isProcessing = true;

			// Clear any existing error messages.
			removeExistingErrors();

			executeCaptcha(
				function ( token ) {
					// Validate token via AJAX.
					$.ajax( {
						type: 'POST',
						data: {
							action: 'edd_captcha_validate',
							token: token,
						},
						url: globalThis.EDDreCAPTCHA.ajaxurl,
						success: function ( response ) {
							isProcessing = false;
							if ( response.success ) {
								captchaInput.value = token;
								submitButton.click();
							} else {
								captchaInput.value = '';
								removeExistingErrors();
								var errorNode = document.createElement( 'div' );
								errorNode.classList.add( 'edd_errors', 'edd-alert', 'edd-alert-error', 'invalid_captcha', response.data.error );
								errorNode.innerHTML = '<p class="edd_error"><strong>' + globalThis.EDDreCAPTCHA.error + '</strong>: ' + response.data.message + '</p>';
								submitButton.closest( 'form' ).after( errorNode );
							}
						},
					} ).fail( function ( response ) {
						isProcessing = false;
						captchaInput.value = '';
						console.error( 'CAPTCHA AJAX error:', response );
					} );
				},
				function ( error ) {
					isProcessing = false;
					// Show error to user.
					captchaInput.value = '';
					removeExistingErrors();
					var errorNode = document.createElement( 'div' );
					errorNode.classList.add( 'edd_errors', 'edd-alert', 'edd-alert-error', 'invalid_captcha_bad' );
					errorNode.innerHTML = '<p class="edd_error"><strong>' + globalThis.EDDreCAPTCHA.error + '</strong>: ' + globalThis.EDDreCAPTCHA.error_message + '</p>';
					submitButton.closest( 'form' ).after( errorNode );
				}
			);
		} );
	}

	// Initialize based on context.
	if ( globalThis.EDDreCAPTCHA.context === 'checkout' ) {
		initializeCheckout();
	} else {
		$( document ).ready( function () {
			initializeFormValidation();
		} );
	}

} )( document, jQuery );
