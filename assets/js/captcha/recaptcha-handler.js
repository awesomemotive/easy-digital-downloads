/**
 * reCAPTCHA v3 Handler
 *
 * Provider-specific logic for executing Google reCAPTCHA v3.
 *
 * @package EDD\Captcha
 * @since 3.6.1
 */

; ( function ( document ) {
	'use strict';

	let isExecuting = false;

	/**
	 * Execute reCAPTCHA and get token.
	 *
	 * @param {Function} onSuccess Callback for successful token generation.
	 * @param {Function} onError   Callback for errors.
	 */
	function executeRecaptcha( onSuccess, onError ) {
		// Check if grecaptcha is available and ready.
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
					.execute( globalThis.EDDreCAPTCHA.sitekey, {
						action: globalThis.EDDreCAPTCHA.action,
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

	// Define the standard handler interface.
	globalThis.EDDCaptchaHandler = {
		/**
		 * Execute the CAPTCHA.
		 *
		 * @param {Function} onSuccess Callback for successful execution.
		 * @param {Function} onError   Callback for errors.
		 */
		execute: function ( onSuccess, onError ) {
			if ( ! globalThis.EDDreCAPTCHA.sitekey ) {
				return;
			}

			// Prevent duplicate executions.
			if ( isExecuting ) {
				console.warn( 'CAPTCHA execution already in progress' );
				return;
			}

			executeRecaptcha( onSuccess, onError );
		},

		/**
		 * Whether this provider's tokens need periodic refresh.
		 * reCAPTCHA v3 tokens expire after 2 minutes.
		 *
		 * @type {boolean}
		 */
		needsRefresh: true,
	};

} )( document );
