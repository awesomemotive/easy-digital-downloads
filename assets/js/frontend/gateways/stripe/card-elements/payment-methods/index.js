/* global $ */

/**
 * Internal dependencies.
 */
import { forEach, getNextSiblings } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 *
 */
export function paymentMethods() {
	// Toggle only shows if using Full Address (for some reason).
	if ( getBillingFieldsToggle() ) {
		// Hide fields initially.
		toggleBillingFields( false );

		/**
		 * Binds change event to "Update billing address" toggle to show/hide address fields.
		 *
		 * @param {Event} e Change event.
		 */
		getBillingFieldsToggle().addEventListener( 'change', function( e ) {
			return toggleBillingFields( e.target.checked );
		} );
	}

	// Payment method toggles.
	const existingPaymentMethods = document.querySelectorAll( '.edd-stripe-existing-card' );

	if ( 0 !== existingPaymentMethods.length ) {
		forEach( existingPaymentMethods, function( existingPaymentMethod ) {
			/**
			 * Binds change event to credit card toggles.
			 *
			 * @param {Event} e Change event.
			 */
			return existingPaymentMethod.addEventListener( 'change', function( e ) {
				return onPaymentSourceChange( e.target );
			} );
		} );

		// Simulate change of payment method to populate current fields.
		let currentPaymentMethod = document.querySelector( '.edd-stripe-existing-card:checked' );

		if ( ! currentPaymentMethod ) {
			currentPaymentMethod = document.querySelector( '.edd-stripe-existing-card:first-of-type' );
			currentPaymentMethod.checked = true;
		}

		const paymentMethodChangeEvent = document.createEvent( 'Event' );
		paymentMethodChangeEvent.initEvent( 'change', true, false );
		currentPaymentMethod.dispatchEvent( paymentMethodChangeEvent );
	}
}

/**
 * Determines if the billing fields can be toggled.
 *
 * @return {Bool} True if the toggle exists.
 */
function getBillingFieldsToggle() {
	return document.getElementById( 'edd-stripe-update-billing-address' );
}

/**
 * Toggles billing fields visiblity.
 *
 * Assumes the toggle control is the first item in the "Billing Details" fieldset.
 *
 * @param {Bool} isVisible Billing item visibility.
 */
function toggleBillingFields( isVisible ) {
	const updateAddressWrapperEl = document.querySelector( '.edd-stripe-update-billing-address-wrapper' );

	if ( ! updateAddressWrapperEl ) {
		return;
	}

	// Find all elements after the toggle.
	const billingFieldWrappers = getNextSiblings( updateAddressWrapperEl );
	const billingAddressPreview = document.querySelector( '.edd-stripe-update-billing-address-current' );

	billingFieldWrappers.forEach( function( wrap ) {
		wrap.style.display = isVisible ? 'block' : 'none';
	} );

	// Hide address preview.
	if ( billingAddressPreview ) {
		billingAddressPreview.style.display = isVisible ? 'none' : 'block';
	}
}

/**
 * Manages UI state when the payment source changes.
 *
 * @param {HTMLElement} paymentSource Selected payment source. (Radio element with data).
 */
function onPaymentSourceChange( paymentSource ) {
	const isNew = 'new' === paymentSource.value;
	const newCardForm = document.querySelector( '.edd-stripe-new-card' );
	const billingAddressToggle = document.querySelector( '.edd-stripe-update-billing-address-wrapper' );

	// Toggle card details field.
	newCardForm.style.display = isNew ? 'block' : 'none';

	if ( billingAddressToggle ) {
		billingAddressToggle.style.display = isNew ? 'none' : 'block';
	}

	// @todo don't be lazy.
	$( '.edd-stripe-card-radio-item' ).removeClass( 'selected' );
	$( paymentSource ).closest( '.edd-stripe-card-radio-item' ).addClass( 'selected' );

	const addressFieldMap = {
		card_address: 'address_line1',
		card_address_2: 'address_line2',
		card_city: 'address_city',
		card_state: 'address_state',
		card_zip: 'address_zip',
		billing_country: 'address_country',
	};

	// New card is being used, show fields and reset them.
	if ( isNew ) {
		// Reset all fields.
		for ( const addressEl in addressFieldMap ) {
			if ( ! addressFieldMap.hasOwnProperty( addressEl ) ) {
				return;
			}

			const addressField = document.getElementById( addressEl );

			if ( addressField ) {
				addressField.value = '';
				addressField.selected = '';
			}
		}

		// Recalculate taxes.
		if ( window.EDD_Checkout.recalculate_taxes ) {
			window.EDD_Checkout.recalculate_taxes();
		}

		// Show billing fields.
		toggleBillingFields( true );

		// Existing card is being used.
		// Ensure the billing fields are hidden, and update their values with saved information.
	} else {
		const addressString = [];
		const billingDetailsEl = document.getElementById( paymentSource.id + '-billing-details' );

		if ( ! billingDetailsEl ) {
			return;
		}

		// Hide billing fields.
		toggleBillingFields( false );

		// Uncheck "Update billing address"
		if ( getBillingFieldsToggle() ) {
			getBillingFieldsToggle().checked = false;
		}

		// Update billing address fields with saved card values.
		const billingDetails = billingDetailsEl.dataset;

		for ( const addressEl in addressFieldMap ) {
			if ( ! addressFieldMap.hasOwnProperty( addressEl ) ) {
				continue;
			}

			const addressField = document.getElementById( addressEl );

			if ( ! addressField ) {
				continue;
			}

			const value = billingDetails[ addressFieldMap[ addressEl ] ];

			// Set field value.
			addressField.value = value;

			// Generate an address string from values.
			if ( '' !== value ) {
				addressString.push( value );
			}

			// This field is required but does not have a saved value, show all fields.
			if ( addressField.required && '' === value ) {
				// @todo DRY up some of this DOM usage.
				toggleBillingFields( true );

				if ( getBillingFieldsToggle() ) {
					getBillingFieldsToggle().checked = true;
				}

				if ( billingAddressToggle ) {
					billingAddressToggle.style.display = 'none';
				}
			}

			// Trigger change event when the Country field is updated.
			if ( 'billing_country' === addressEl ) {
				const changeEvent = document.createEvent( 'Event' );
				changeEvent.initEvent( 'change', true, true );
				addressField.dispatchEvent( changeEvent );
			}
		}

		/**
		 * Monitor AJAX requests for address changes.
		 *
		 * Wait for the "State" field to be updated based on the "Country" field's
		 * change event. Once there is an AJAX response fill the "State" field with the
		 * saved card's State data and recalculate taxes.
		 *
		 * @since 2.7
		 *
		 * @param {Object} event
		 * @param {Object} xhr
		 * @param {Object} options
		 */
		$( document ).ajaxSuccess( function( event, xhr, options ) {
			if ( ! options || ! options.data || ! xhr ) {
				return;
			}

			if (
				options.data.includes( 'action=edd_get_shop_states' ) &&
				options.data.includes( 'field_name=card_state' ) &&
				( xhr.responseText && xhr.responseText.includes( 'card_state' ) )
			) {
				const stateField = document.getElementById( 'card_state' );

				if ( stateField ) {
					stateField.value = billingDetails.address_state;

					// Recalculate taxes.
					if ( window.EDD_Checkout.recalculate_taxes ) {
						window.EDD_Checkout.recalculate_taxes( stateField.value );
					}
				}
			}
		} );

		// Update address string summary.
		const billingAddressPreview = document.querySelector( '.edd-stripe-update-billing-address-current' );

		if ( billingAddressPreview ) {
			billingAddressPreview.innerText = addressString.join( ', ' );

			const { brand, last4 } = billingDetails;

			document.querySelector( '.edd-stripe-update-billing-address-brand' ).innerHTML = brand;
			document.querySelector( '.edd-stripe-update-billing-address-last4' ).innerHTML = last4;
		}
	}
}
