/* global $, _ */

/**
 * Internal dependencies.
 */
import { jQueryReady } from 'utils/jquery.js';
import { updateAmounts } from './utils.js';

jQueryReady( () => {
	const toggle = document.getElementById( 'edd-override-amounts' );

	if ( ! toggle ) {
		return;
	}

	const isOverrideableEl = document.querySelector( 'input[name="edd-order-download-is-overrideable"]' );

	/**
	 * A new download has been added.
	 */
	$( document ).on( 'edd-admin-add-order-download', function( response ) {
		// Update on change.
		_.each( document.querySelectorAll( '.overridable input' ), ( el ) => el.addEventListener( 'keyup', updateAmounts ) );

		// Update on addition.
		updateAmounts();

		// Keep toggle disabled if necesseary.
		toggle.disabled = 1 == isOverrideableEl.value;
	} );

	/**
	 * Allow edits.
	 */
	toggle.addEventListener( 'change', function() {
		// Disable the button.
		this.disabled = true;

		// Tell future download item additions to be editable.
		isOverrideableEl.value = 1;

		// Get a fresh set of inputs. Mark current inputs as editable.
		_.each( document.querySelectorAll( '.overridable input' ), ( el ) => el.readOnly = false );

		// Mark the override for saving the data.
		const input = document.createElement( 'input' );
		input.name = 'edd_add_order_override';
		input.value = true;
		input.type = 'hidden';

		document.getElementById( 'edd-add-order-form' ).appendChild( input );
	} );
} );
