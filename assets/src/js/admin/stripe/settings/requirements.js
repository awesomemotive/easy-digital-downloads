/* global wp */

/**
 * Hides "Save Changes" button if showing the special settings placeholder.
 */
wp.domReady( () => {
	const containerEl = document.querySelector( '.edds-requirements-not-met' );

	if ( ! containerEl ) {
		return;
	}

	// Hide "Save Changes" button.
	document.querySelector( '.edd-settings-wrap .submit' ).style.display = 'none';
} );

/**
 * Moves "Payment Gateways" notice under Stripe.
 * Disables/unchecks the checkbox.
 */
wp.domReady( () => {
	const noticeEl = document.getElementById( 'edds-payment-gateways-stripe-unmet-requirements' );

	if ( ! noticeEl ) {
		return;
	}

	const stripeLabel = document.querySelector( 'label[for="edd_settings[gateways][stripe]"]' );
	stripeLabel.parentNode.insertBefore( noticeEl, stripeLabel.nextSibling );

	const stripeCheck = document.getElementById( 'edd_settings[gateways][stripe]' );
	stripeCheck.disabled = true;
	stripeCheck.checked = false;

	noticeEl.insertBefore( stripeCheck, noticeEl.querySelector( 'p' ) );
	noticeEl.insertBefore( stripeLabel, noticeEl.querySelector( 'p' ) );
} );
