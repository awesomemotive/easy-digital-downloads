jQuery( document ).ready( function ( $ ) {
	// If the main PayPal localized variables aren't defined, we can't do anything more here.
	if ( typeof eddPayPalConnectVars === 'undefined' ) {
		return;
	}

	// Clear errors.
	let errorContainer = document.getElementById( 'edd-paypal-commerce-errors' );
	if ( errorContainer && errorContainer.length ) {
		while (errorContainer.firstChild) {
			errorContainer.removeChild(errorContainer.firstChild);
		}
		errorContainer.classList.remove( 'notice notice-error' );
	}

	if ( ! eddPayPalConnectVars.isConnected ) {
		// If the edd-paypal-commerce-link element is on the page, load the Partner Onboarding script.
		let connectButton = document.getElementById( 'edd-paypal-commerce-link' );
		if ( connectButton ) {

			// Load the Partner Onboarding script.
			let paypalScriptTag = document.createElement('script');
			paypalScriptTag.id  = 'edd-paypal-commerce-onboarding';
			paypalScriptTag.src = 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
			document.body.appendChild( paypalScriptTag) ;

			setTimeout(
				() => {
					if ('undefined' !== window.PAYPAL.apps.Signup) {
						window.PAYPAL.apps.Signup.render();
					}
				},
				1000
			);
		}

		window.eddPayPalOnboardingCallback = function eddPayPalOnboardingCallback( authCode, shareId ) {
			let connectButton = document.getElementById( 'edd-paypal-commerce-link' );
			let errorContainer = document.getElementById( 'edd-paypal-commerce-errors' );

			jQuery.post( ajaxurl, {
				action: 'edd_paypal_commerce_get_access_token',
				auth_code: authCode,
				share_id: shareId,
				_ajax_nonce: connectButton.dataset.nonce,
			} ).done( function() {
				connectButton.classList.add( 'disabled', 'updating-message' );
				connectButton.disabled = true;
			} ).fail( function( response ) {
				errorContainer.innerHTML = '<p>' + response.data + '</p>';
				errorContainer.classList.add( 'notice', 'notice-error' );

				let getHelpButton = document.getElementById( 'edd-paypal-commerce-get-help' );
				getHelpButton.classList.remove( 'edd-hidden' );
				getHelpButton.classList.add( 'button', 'button-secondary' );
			} );
		}
	} else {
		// If we are connected we can register the rest of the events and functions.
		let reconnectButton = document.getElementById( 'edd-paypal-commerce-reconnect' );
		if ( reconnectButton ) {
			reconnectButton.addEventListener( 'click', function( e ) {
				e.preventDefault();

				// Clear errors.
				let errorContainer = $( '#edd-paypal-commerce-errors' );
				errorContainer.empty().removeClass( 'notice notice-error' );

				reconnectButton.classList.add( 'updating-message' );
				reconnectButton.disabled = true;

				$.post( ajaxurl, {
					action: 'edd_paypal_commerce_reconnect',
					_ajax_nonce: reconnectButton.dataset.nonce,
				} ).done( function() {} ).fail( function( response ) {
					console.log( 'Reconnect failure', response.data );
					reconnectButton.classList.remove( 'updating-message' );
					reconnectButton.disabled = false;

					// Set errors.
					errorContainer.html( '<p>' + response.data + '</p>' ).addClass( 'notice notice-error' );
					return;
				} );
			} );
		}

		/**
		 * Checks the PayPal connection & webhook status.
		 */
		function eddPayPalGetAccountStatus() {
			let accountInfoEl = document.getElementById( 'edd-paypal-commerce-connect-wrap' );
			if ( accountInfoEl ) {
				$.post( ajaxurl, {
					action: 'edd_paypal_commerce_get_account_info',
					_ajax_nonce: accountInfoEl.getAttribute( 'data-nonce' )
				}, function( response ) {
					let newHtml = '<p>' + eddPayPalConnectVars.defaultError + '</p>';

					if ( response.success ) {
						newHtml = response.data.account_status;

						if ( response.data.actions && response.data.actions.length ) {
							newHtml += '<p class="edd-paypal-connect-actions">' + response.data.actions.join( ' ' ) + '</p>';
						}

						if ( response.data.disconnect_links && response.data.disconnect_links.length ) {
							var disconnect_link_wrppper = document.getElementById('edd-paypal-disconnect');
							disconnect_link_wrppper.innerHTML = response.data.disconnect_links.join( ' ' );
						}
					} else if ( response.data && response.data.message ) {
						newHtml = response.data.message;
					}

					accountInfoEl.innerHTML = newHtml;

					// Remove old status messages.
					accountInfoEl.classList.remove( 'notice-success', 'notice-warning', 'notice-error', 'loading' );

					// Add new one.
					var newClass = response.success && response.data.status ? 'notice-' + response.data.status : 'notice-error';
					accountInfoEl.classList.add( newClass );

					let getHelpButton = document.getElementById( 'edd-paypal-commerce-get-help' );
					if ( 'success' === response.data.status ) {
						getHelpButton.classList.add( 'edd-hidden' );
						getHelpButton.classList.remove( 'button', 'button-secondary' );
					} else {
						getHelpButton.classList.remove( 'edd-hidden' );
						getHelpButton.classList.add( 'button', 'button-secondary' );
					}

					// If we are now connected and verified, we can bind all the action buttons.
					eddPayPalBindActions();
				} );
			}
		}
		eddPayPalGetAccountStatus();

		function eddPayPalBindActions() {
			let actionButtons = document.querySelectorAll( '.edd-paypal-connect-action' );
			if ( actionButtons && actionButtons.length ) {
				// Loop over all the actionButtons and add a click event listener to each.
				actionButtons.forEach( function( button ) {
					button.addEventListener( 'click', function( e ) {
						e.preventDefault();

						let button = e.target;
						button.disabled = true;
						button.classList.add( 'updating-message' );

						// Clear errors.
						let errorWrap =document.getElementById( 'edd-paypal-commerce-connect-wrap' );
						if ( errorWrap && errorWrap.length ) {
							errorWrap.remove();
						}

						$.post( ajaxurl, {
							action: button.dataset.action,
							_ajax_nonce: button.dataset.nonce,
						} ).done( function() {
							// Refresh account status.
							eddPayPalGetAccountStatus();
						} ).fail( function( response ) {
							console.log( 'Failure', response.data );
							button.disabled = false;
							button.classList.remove( 'updating-message' );

							// Set errors.
							errorWrap.html( '<p>' + response.data + '</p>' ).addClass( 'edd-paypal-actions-error-wrap' );
						} );
					} );
				} );
			}
		}
	}
} );
