/* global eddSquare */

/**
 * Square Checkout JavaScript
 *
 * Handles Square Web Payments SDK integration for EDD checkout
 */
import * as process from './process';
import { apiRequest } from '../../../utils/api-request';

export function init() {
	window.eddSquare.squareData = typeof eddSquare !== 'undefined' ? eddSquare : null;

	if ( typeof Square === 'undefined' ) {
		return;
	}

	if ( ! window.eddSquare.squareData ) {
		return;
	}

	// Setup some validation states for the card fields.
	window.eddSquare.cardValidation = {
		cardNumber: false,
		expirationDate: false,
		postalCode: false,
		cvv: false,
	}

	// Setup the submit button.
	const submitButton = document.getElementById( 'edd-purchase-button' );
	if ( submitButton ) {
		window.eddSquare.submitButtonOriginalText = submitButton.value;
	}

	const hasSingleGateway = document.querySelector('input[name="edd-gateway"]');
	if ( hasSingleGateway && 'square' === hasSingleGateway.value ) {
		window.eddSquare.singleGateway = true;

		initializeSquarePaymentsAndCard();
	} else {
		window.eddSquare.singleGateway = false;

		$( document.body ).on( 'edd_gateway_loaded', ( e, gateway ) => {
			if ( 'square' !== gateway ) {
				if ( window.eddSquare.card ) {
					window.eddSquare.card.detach();
				}
				return;
			}

			initializeSquarePaymentsAndCard();
		} );
	}
}

async function initializeSquarePaymentsAndCard() {
	let cardElement = document.getElementById( 'edd-square-card-element' );

	if ( ! cardElement ) {
		updatePurchaseButton( 'disabled' );
		return;
	}

	updatePurchaseButton( 'updating' );

	let success = false;

	if ( window.eddSquare.card ) {
		window.eddSquare.card.attach( cardElement );
		success = true;
	} else {
		window.eddSquare.payments = null;
		window.eddSquare.card = null;
		success = await initializeSquarePayments();
	}

	if (success) {
		// We don't want to enable the button yet, until we have valid card information.
		updatePurchaseButton( 'disabled' );
		bindCardFieldValidation();
	} else {
		updatePurchaseButton( 'disabled' );
	}
}

function updatePurchaseButton( state ) {
	const purchaseButton = document.getElementById( 'edd-purchase-button' );

	if ( purchaseButton ) {
		if ( 'disabled' === state ) {
			unbindSquareSubmitHandler
			purchaseButton.setAttribute( 'data-edd-button-state', state );
			purchaseButton.setAttribute( 'disabled', 'disabled' );
			purchaseButton.setAttribute( 'readonly', 'readonly' );
			purchaseButton.value = ( window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.completePurchase ) || 'Complete Purchase';
		} else if ( 'processing' === state ) {
			unbindSquareSubmitHandler
			purchaseButton.setAttribute( 'data-edd-button-state', state );
			purchaseButton.setAttribute( 'disabled', 'disabled' );
			purchaseButton.setAttribute( 'readonly', 'readonly' );
			purchaseButton.value = ( window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.processing ) || 'Processing...';
		} else if ( 'updating' === state ) {
			unbindSquareSubmitHandler
			purchaseButton.setAttribute( 'data-edd-button-state', state );
			purchaseButton.setAttribute( 'disabled', 'disabled' );
			purchaseButton.setAttribute( 'readonly', 'readonly' );
			purchaseButton.value = window.eddSquare.submitButtonOriginalText || ((window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.completePurchase) || 'Complete Purchase');
		} else if ( 'enabled' === state ) {
			bindSquareSubmitHandler(); // Bind specific submit handler now that card is ready
			purchaseButton.removeAttribute( 'data-edd-button-state' );
			purchaseButton.removeAttribute( 'disabled' );
			purchaseButton.removeAttribute( 'readonly' );
			purchaseButton.value = window.eddSquare.submitButtonOriginalText || ((window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.completePurchase) || 'Complete Purchase');
		}
	}
}

function bindCardFieldValidation() {
	if ( window.eddSquare.card ) {
		// Listen for general focus being removed.
		window.eddSquare.card.addEventListener(
			'focusClassRemoved',
			async (cardInputEvent) => {
				window.eddSquare.cardValidation[cardInputEvent.detail.field] = cardInputEvent.detail.currentState.isCompletelyValid;
				checkCardValidation();
			}
		);

		window.eddSquare.card.addEventListener(
			'cardBrandChanged',
			async (cardInputEvent) => {
				window.eddSquare.cardValidation[cardInputEvent.detail.field] = cardInputEvent.detail.currentState.isCompletelyValid;
				checkCardValidation();
			}
		);

		// Listen for any error classes being added.
		window.eddSquare.card.addEventListener(
			'errorClassAdded',
			async (cardInputEvent) => {
				window.eddSquare.cardValidation[cardInputEvent.detail.field] = false;
				checkCardValidation();
			}
		);

		// Listen specifically for the postal code field, as it's the last one.
		window.eddSquare.card.addEventListener(
			'postalCodeChanged',
			async (cardInputEvent) => {
				window.eddSquare.cardValidation.postalCode = cardInputEvent.detail.currentState.isCompletelyValid;
				checkCardValidation();
			}
		);
	}
}

function checkCardValidation() {
	let $state = 'disabled';
	if ( Object.values( window.eddSquare.cardValidation ).every( (field) => field ) ) {
		$state = 'enabled';
	}

	updatePurchaseButton( $state );
}

async function initializeSquarePayments() {
	if ( ! window.eddSquare.squareData ) {
		return false;
	}

	try {
		window.eddSquare.payments = Square.payments(window.eddSquare.squareData.client_id, window.eddSquare.squareData.location_id);
		window.eddSquare.card     = await window.eddSquare.payments.card();

		// Attach the card element.
		await window.eddSquare.card.attach('#edd-square-card-element'); // Square SDK uses a CSS selector string.

		return true;
	} catch (error) {
		if ( error ) {
			console.error( 'Error details:', { type: error.constructor.name, message: error.message, code: error.code, details: error.details } );
		}
		showError(error.message || (window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.genericError) || 'Failed to initialize payment form.');
		return false;
	}
}

function bindSquareSubmitHandler() {
	const purchaseButton = document.getElementById( 'edd-purchase-button' );
	const events = [ 'click', 'keydown' ];
	events.forEach( ( event ) => {
		purchaseButton.addEventListener( event, processPayment );
	} );

	enableSubmitButton();
}

function unbindSquareSubmitHandler() {
	const purchaseButton = document.getElementById( 'edd-purchase-button' );
	const events = [ 'click', 'keydown' ];
	events.forEach( ( event ) => {
		purchaseButton.removeEventListener( event, processPayment );
	} );

	disableSubmitButton();
}

function isSquareGatewaySelected() {
	// Check the input name edd-gateway.
	const gateway = document.querySelector( 'input[name="edd-gateway"]' );
	if ( gateway ) {
		return 'square' === gateway.value;
	}

	return false;
}

async function processPayment(event) { // event is passed by addEventListener
	// This handler is only active if Square is selected and initialized.
	if (!isSquareGatewaySelected()) {
		return; // Allow default form submission
	}

	event.preventDefault(); // Prevent default form submission

	unbindSquareSubmitHandler();

	clearErrors();

	const form = document.getElementById('edd_purchase_form'),
		submitButton = document.getElementById('edd-purchase-button'),
		tokenInput = $( '#edd-process-square-token' );


	if ( ! window.eddSquare.card ) {
		showError( ( window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.cardError ) || 'Card payment not initialized' );
		bindSquareSubmitHandler();
		return;
	}

	updatePurchaseButton( 'processing' );

	const loadingIndicator = document.getElementById('edd-square-loading'); // Assuming this ID
	if (loadingIndicator) loadingIndicator.style.display = 'block';
	clearErrors();

	try {

		let formData = new FormData(form),
			formObject = {};

		for (let [key, value] of formData) {
			formObject[key] = value;
		}

		const token = await process.tokenizeCard( getVerificationDetails( formObject ) );

		if ( ! token ) {
			showError( 'Failed to process card payment.' );
			bindSquareSubmitHandler();
			return;
		}

		// 1. Validate the checkout form and create the order record.
		let {
			square_payment_status
		} = await apiRequest( 'edd_square_process_checkout_form', {
			form_data: formObject,
			source_id: token,
			token: tokenInput.data( 'token' ),
			timestamp: tokenInput.data( 'timestamp' ),
		} );

		if ( square_payment_status !== 'COMPLETED' ) {
			showError( 'Payment failed. Please try again.' );
			bindSquareSubmitHandler(); // Re-bind the submit handler.
			return;
		}

		// 3. Optionally create the card and subscription records.

		// 4. Redirect to the success page.
		window.location.href = eddSquare.success_page_uri;
	} catch (e) {
		console.log( e );
		bindSquareSubmitHandler();
		showError(e.message || ((window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.genericError) || 'An error occurred.'));
		submitButton.disabled = false;
		submitButton.value = window.eddSquare.submitButtonOriginalText || ((window.eddSquare.squareData.strings && window.eddSquare.squareData.strings.completePurchase) || 'Complete Purchase');
		if (loadingIndicator) loadingIndicator.style.display = 'none';
	}
}

function showError(message) {
	const errorElementWrapper = document.getElementById('edd-square-card-errors'),
		errorMessageWrapper = document.getElementById( 'edd-square-card-error-message' );

	console.log( errorElementWrapper, errorMessageWrapper );
	if (errorElementWrapper && errorMessageWrapper) {
		errorMessageWrapper.textContent = message;
		errorElementWrapper.style.display = 'block';
		if (errorElementWrapper.offsetParent !== null) {
			errorElementWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}
}

function clearErrors() {
	const errorElementWrapper = document.getElementById('edd-square-card-errors'),
		errorMessageWrapper = document.getElementById( 'edd-square-card-error-message' );

	if (errorElementWrapper && errorMessageWrapper) {
		errorMessageWrapper.textContent = '';
		errorElementWrapper.style.display = 'none';
	}
}

function disableSubmitButton() {
	const submitButton = document.getElementById('edd-purchase-button');
	if (submitButton) {
		submitButton.disabled = true;
	}
}

function enableSubmitButton() {
	const submitButton = document.getElementById('edd-purchase-button');
	if (submitButton) {
		submitButton.disabled = false;
	}
}

/**
 *
 * @param Object formData
 * @returns Object
 */
function getVerificationDetails( formData ) {

	const totalElement = document.querySelector( '.edd_cart_total .edd_cart_amount' );
	let total = '0.00';

	if ( totalElement ) {
		total = totalElement.getAttribute( 'data-total' ) || total;
		total = parseFloat( total ).toFixed( 2 );
	}

	// Map the billing contact information.
	const billingContact = {};

	// Map the address inputs to the Square expected keys.
	const fieldsMapping = {
		edd_last: 'familyName',
		edd_first: 'givenName',
		card_city: 'city',
		card_state: 'state',
		card_zip: 'postalCode',
		edd_email: 'email',
		edd_phone: 'phone',
		billing_country: 'countryCode',
	};

	// Loop over the fields mapping and set teh values in the billingContact object.
	for ( const [ key, value ] of Object.entries( fieldsMapping ) ) {
		if ( formData[key] && formData[key].length > 0 ) {
			billingContact[value] = formData[key];
		}
	}

	const addressLines = [];
	if ( formData.card_address ) {
		addressLines.push( formData.card_address );
	}
	if ( formData.card_address_2 ) {
		addressLines.push( formData.card_address_2 );
	}

	// Now if the addressLines array is not empty, add it to the billingContact object.
	if ( addressLines.length > 0 ) {
		billingContact.addressLines = addressLines;
	}

	return {
		amount: total,
		currencyCode: window.eddSquare.currency,
		intent: 'CHARGE',
		customerInitiated: true,
		sellerKeyedIn: false,
		billingContact: billingContact,
	};
}