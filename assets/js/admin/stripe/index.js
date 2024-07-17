/* global $, edd_stripe_admin */

/**
 * Internal dependencies.
 */
// import './../../../css/src/admin.scss';
import './settings/index.js';

let testModeCheckbox;
let testModeToggleNotice;

$( document ).ready( function() {
	testModeCheckbox = document.getElementById( 'edd_settings[test_mode]' );
	if ( testModeCheckbox ) {
		testModeToggleNotice = document.getElementById( 'edd_settings[stripe_connect_test_mode_toggle_notice]' );
		EDD_Stripe_Connect_Scripts.init();
	}

	// Toggle API keys.
	$( '.edds-api-key-toggle button' ).on( 'click', function( event ) {
		event.preventDefault();

		$( '.edds-api-key-toggle, .edds-api-key-row' )
			.toggleClass( 'edd-hidden' );
	} );

	const elementsModeToggle = $( '.stripe-elements-mode select' );
	if ( elementsModeToggle ) {

		// Listen to the elements mode toggle.
		elementsModeToggle.on( 'change', function() {
			$( '.card-elements-feature' ).toggleClass( 'edd-hidden' );
			$( '.payment-elements-feature' ).toggleClass( 'edd-hidden' );
		} );
	}

	/**
	 * Handle showing/hiding the Statement Descriptor Prefix field based on the toggle.
	 */
	const statementDescriptorSummaryToggle = document.getElementById( 'edd_settings[stripe_include_purchase_summary_in_statement_descriptor]' );
	if ( statementDescriptorSummaryToggle ) {
		statementDescriptorSummaryToggle.addEventListener( 'change', function() {
			$( '.statement-descriptor-prefix' ).toggleClass( 'edd-hidden' );
		} );
	}
} );

const EDD_Stripe_Connect_Scripts = {

	init() {
		this.listeners();
	},

	listeners() {
		const self = this;

		testModeCheckbox.addEventListener( 'change', function() {
			// Don't run these events if Stripe is not enabled.
			if ( ! edd_stripe_admin.stripe_enabled ) {
				return;
			}

			if ( this.checked ) {
				if ( 'false' === edd_stripe_admin.test_key_exists ) {
					self.showNotice( testModeToggleNotice, 'warning' );
					self.addHiddenMarker();
				} else {
					self.hideNotice( testModeToggleNotice );
					const hiddenMarker = document.getElementById( 'edd-test-mode-toggled' );
					if ( hiddenMarker ) {
						hiddenMarker.parentNode.removeChild( hiddenMarker );
					}
				}
			}

			if ( ! this.checked ) {
				if ( 'false' === edd_stripe_admin.live_key_exists ) {
					self.showNotice( testModeToggleNotice, 'warning' );
					self.addHiddenMarker();
				} else {
					self.hideNotice( testModeToggleNotice );
					const hiddenMarker = document.getElementById( 'edd-test-mode-toggled' );
					if ( hiddenMarker ) {
						hiddenMarker.parentNode.removeChild( hiddenMarker );
					}
				}
			}
		} );
	},

	addHiddenMarker() {
		const submit = document.getElementById( 'submit' );

		if ( ! submit ) {
			return;
		}

		submit.parentNode.insertAdjacentHTML( 'beforeend', '<input type="hidden" class="edd-hidden" id="edd-test-mode-toggled" name="edd-test-mode-toggled" />' );
	},

	showNotice( element = false, type = 'error' ) {
		if ( ! element ) {
			return;
		}

		if ( typeof element !== 'object' ) {
			return;
		}

		element.className = 'notice inline notice-' + type;
	},

	hideNotice( element = false ) {
		if ( ! element ) {
			return;
		}

		if ( typeof element !== 'object' ) {
			return;
		}

		element.className = 'edd-hidden';
	},
};
