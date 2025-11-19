/**
 * Cloudflare Turnstile Handler
 *
 * Provider-specific logic for executing Cloudflare Turnstile.
 *
 * @package EDD\Captcha
 * @since 3.6.1
 */

; ( function ( document ) {
	'use strict';

	let isExecuting = false;
	let widgetId = null;

	/**
	 * Execute Turnstile and get token.
	 *
	 * @param {Function} onSuccess Callback for successful token generation.
	 * @param {Function} onError   Callback for errors.
	 */
	function executeTurnstile( onSuccess, onError ) {
		// Check if turnstile is available.
		if ( typeof turnstile === 'undefined' ) {
			console.warn( 'Turnstile not loaded yet, skipping validation' );
			if ( onError ) {
				onError( 'Turnstile not loaded' );
			}
			return;
		}

		isExecuting = true;

		try {
			// For Turnstile, we need to render a widget or use the invisible mode.
			const input = document.getElementById( 'edd-blocks-recaptcha' );
			if ( ! input ) {
				isExecuting = false;
				if ( onError ) {
					onError( 'Input field not found' );
				}
				return;
			}

			// Create a container for Turnstile if it doesn't exist.
			let container = document.getElementById( 'edd-turnstile-container' );
			if ( ! container ) {
				container = document.createElement( 'div' );
				container.id = 'edd-turnstile-container';
				container.style.position = 'fixed';
				container.style.bottom = '0px';
				container.style.left = '-10000px';
				input.parentNode.insertBefore( container, input );
			}

			// Properly remove existing widget if present.
			if ( widgetId !== null ) {
				try {
					turnstile.remove( widgetId );
				} catch ( e ) {
					// Widget might not exist anymore, continue.
				}
				widgetId = null;
			}

			// Track if callback has been called to prevent multiple invocations.
			let callbackInvoked = false;

			widgetId = turnstile.render( container, {
				sitekey: globalThis.EDDreCAPTCHA.sitekey,
				callback: function ( token ) {
					if ( callbackInvoked ) {
						return;
					}
					callbackInvoked = true;
					input.value = token;
					isExecuting = false;
					if ( onSuccess ) {
						onSuccess( token );
					}
				},
				'error-callback': function ( error ) {
					if ( callbackInvoked ) {
						return;
					}
					callbackInvoked = true;
					console.error( 'Turnstile execution error:', error );
					isExecuting = false;
					if ( onError ) {
						onError( error );
					}
				},
			} );
		} catch ( error ) {
			console.error( 'Turnstile error:', error );
			isExecuting = false;
			if ( onError ) {
				onError( error );
			}
		}
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

			executeTurnstile( onSuccess, onError );
		},

		/**
		 * Whether this provider's tokens need periodic refresh.
		 * Turnstile tokens are valid longer, so no periodic refresh needed.
		 *
		 * @type {boolean}
		 */
		needsRefresh: false,
	};

} )( document );
