/**
 * Internal dependencies.
 */
import EDD_Add_Order from './index.js';

const toggle = document.querySelector( '.edd-override' );

/**
 * A new download has been added.
 */
$( document ).on( 'edd-admin-add-order-download', function() {
	toggle.disabled = false;

	// Update on change.
	document.querySelectorAll( '.overridable input' ).forEach( ( el ) => el.addEventListener( 'keyup', EDD_Add_Order.update_totals ) );

	// Update on addition.
	return EDD_Add_Order.update_totals();
} );

/**
 * Allow edits.
 */
toggle.addEventListener( 'click', function() {
	// Tell future download item additions to be editable.
	document.querySelector( 'input[name="edd-order-download-is-overrideable"]' ).value = 1;

	// Get a fresh set of inputs. Mark current inputs as editable.
	document.querySelectorAll( '.overridable input' ).forEach( ( el ) => el.readOnly = false );
} );
