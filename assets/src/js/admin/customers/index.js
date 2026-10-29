/**
 * Customer management screen JS
 */

import { setupEddModal } from '@easy-digital-downloads/modal';

/**
 * Post data to the admin AJAX endpoint.
 *
 * @param {Object} data Key/value pairs to POST.
 * @return {Promise<Response>}
 */
function ajaxPost( data ) {
	return fetch( globalThis.ajaxurl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
		body: new URLSearchParams( data ),
	} );
}

/**
 * Set up the "Add Email" button in the customer emails section.
 */
function setupAddEmail() {
	document.body.addEventListener( 'click', function( e ) {
		const button = e.target.closest( '#add-customer-email' );
		if ( ! button ) {
			return;
		}
		e.preventDefault();

		const wrapper = button.closest( '.customer-section' );
		const notice = wrapper?.querySelector( '.notice-wrap' );

		if ( notice ) {
			notice.innerHTML = '';
		}
		button.disabled = true;
		button.classList.add( 'updating-message' );

		ajaxPost( {
			edd_action: 'customer-add-email',
			customer_id: wrapper?.querySelector( 'input[name="customer-id"]' )?.value ?? '',
			email: wrapper?.querySelector( 'input[name="additional-email"]' )?.value ?? '',
			primary: wrapper?.querySelector( 'input[name="make-additional-primary"]' )?.checked ? '1' : '0',
			_wpnonce: wrapper?.querySelector( 'input[name="add_email_nonce"]' )?.value ?? '',
		} )
			.then( ( r ) => r.json() )
			.then( function( response ) {
				setTimeout( function() {
					if ( true === response.success ) {
						window.location.href = response.redirect;
					} else {
						button.disabled = false;
						button.classList.remove( 'updating-message' );
						if ( notice ) {
							notice.insertAdjacentHTML( 'beforeend', '<div class="notice notice-error inline"><p>' + response.message + '</p></div>' );
						}
					}
				}, 342 );
			} )
			.catch( function() {
				button.disabled = false;
				button.classList.remove( 'updating-message' );
				if ( notice ) {
					notice.insertAdjacentHTML( 'beforeend', '<div class="notice notice-error inline"><p>' + globalThis.edd_vars?.something_went_wrong + '</p></div>' );
				}
			} );
	} );
}

/**
 * Set up the "Disconnect User" link.
 */
function setupRemoveUser() {
	document.body.addEventListener( 'click', function( e ) {
		const link = e.target.closest( '#disconnect-customer' );
		if ( ! link ) {
			return;
		}
		e.preventDefault();

		// eslint-disable-next-line no-alert
		if ( ! globalThis.confirm( globalThis.edd_vars?.disconnect_customer ) ) {
			return;
		}

		ajaxPost( {
			edd_action: 'disconnect-userid',
			customer_id: document.querySelector( 'input[name="customerinfo[id]"]' )?.value ?? '',
			_wpnonce: document.querySelector( '#edit-customer-info #_wpnonce' )?.value ?? '',
		} )
			.then( function() {
				// Reload to reflect the disconnected state.
				window.location.reload();
			} )
			.catch( function() {
				// eslint-disable-next-line no-alert
				globalThis.alert( globalThis.edd_vars?.something_went_wrong );
			} );
	} );
}

/**
 * Set up the "Verify account" link, which asks first when the account is matched by email only.
 */
function setupVerifyUnclaimedUser() {
	document.body.addEventListener( 'click', function( e ) {
		const link = e.target.closest( '[data-edd-verify-unclaimed]' );
		if ( ! link ) {
			return;
		}

		// eslint-disable-next-line no-alert
		if ( ! globalThis.confirm( globalThis.edd_vars?.verify_unclaimed_user ) ) {
			e.preventDefault();
		}
	} );
}

/**
 * Set up the delete-customer confirmation checkbox, which gates the submit
 * button and the "delete records" secondary checkbox.
 */
function setupDeleteChecked() {
	const confirmCheckbox = document.getElementById( 'edd-customer-delete-confirm' );
	if ( ! confirmCheckbox ) {
		return;
	}

	confirmCheckbox.addEventListener( 'change', function() {
		const recordsInput = document.getElementById( 'edd-customer-delete-records' );
		const submitButton = document.getElementById( 'edd-delete-customer' );
		const checked = this.checked;

		if ( recordsInput ) {
			recordsInput.disabled = ! checked;
			if ( ! checked ) {
				recordsInput.checked = false;
			}
		}
		if ( submitButton ) {
			submitButton.disabled = ! checked;
		}
	} );
}

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	setupEddModal( {
		trigger: '#edit-customer',
		dialogId: 'edd-edit-customer-dialog',
	} );
	setupAddEmail();
	setupRemoveUser();
	setupVerifyUnclaimedUser();
	setupDeleteChecked();
} );
