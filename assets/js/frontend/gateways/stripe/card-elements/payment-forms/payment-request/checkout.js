/* global edd_scripts, jQuery */

/**
 * Internal dependencies
 */
import { parseDataset } from './';
import { apiRequest, forEach, outputNotice, clearNotice } from 'utils';
import { handle as handleIntent } from '../../intents';
import { createPayment, completePayment } from '../../payment-forms';

let IS_PRB_GATEWAY;

/**
 * Disables the "Express Checkout" payment gateway.
 * Switches to the next in the list.
 */
function hideAndSwitchGateways() {
	IS_PRB_GATEWAY = false;

	const gatewayRadioEl = document.getElementById( 'edd-gateway-option-stripe-prb' );

	if ( ! gatewayRadioEl ) {
		return;
	}

	// Remove radio option.
	gatewayRadioEl.remove();

	// Recount available gateways and hide selector if needed.
	const gateways = document.querySelectorAll( '.edd-gateway-option' );
	const nextGateway = gateways[0];
	const nextGatewayInput = nextGateway.querySelector( 'input' );

	// Toggle radio.
	nextGatewayInput.checked = true;
	nextGateway.classList.add( 'edd-gateway-option-selected' );

	// Load gateway.
	edd_load_gateway( nextGatewayInput.value );

	// Hide wrapper.
	if ( 1 === gateways.length ) {
		document.getElementById( 'edd_payment_mode_select_wrap' ).remove();
	}
}

/**
 * Handles the click event on the Payment Request Button.
 *
 * @param {Event} event Click event.
 */
function onClick( event ) {
	const errorContainer = document.getElementById( 'edds-prb-error-wrap' );
	const {
		checkout_agree_to_terms,
		checkout_agree_to_privacy,
	} = edd_stripe_vars;

	const termsEl = document.getElementById( 'edd_agree_to_terms' );

	if ( termsEl ) {
		if ( false === termsEl.checked ) {
			event.preventDefault();

			outputNotice( {
				errorMessage: checkout_agree_to_terms,
				errorContainer,
			} );
		} else {
			clearNotice( errorContainer );
		}
	}

	const privacyEl = document.getElementById( 'edd-agree-to-privacy-policy' );

	if ( privacyEl && false === privacyEl.checked ) {
		if ( false === privacyEl.checked ) {
			event.preventDefault();

			outputNotice( {
				errorMessage: checkout_agree_to_privacy,
				errorContainer,
			} );
		} else {
			clearNotice( errorContainer );
		}
	}
}

/**
 * Handles changes to the purchase link form by updating the Payment Request object.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} checkoutForm Checkout form.
 */
async function onChange( paymentRequest, checkoutForm ) {
	try {
		// Calculate and gather price information.
		const {
			'display-items': displayItems,
			...paymentRequestData
		} = await apiRequest( 'edds_prb_ajax_get_options' );

		// Update the Payment Request with server-side data.
		paymentRequest.update( {
			displayItems,
			...paymentRequestData,
		} )
	} catch ( error ) {
		outputNotice( {
			errorMessage: '',
			errorContainer: document.getElementById( 'edds-prb-checkout' ),
			errorContainerReplace: false,
		} );
	}
}

/**
 * Handles Payment Method errors.
 *
 * @param {Object} event Payment Request event.
 * @param {Object} error Error.
 * @param {HTMLElement} purchaseLink Purchase link form.
 */
function onPaymentMethodError( event, error, checkoutForm ) {
	// Complete the Payment Request to hide the payment sheet.
	event.complete( 'success' );

	// Remove spinner.
	const spinner = checkoutForm.querySelector( '.edds-prb-spinner' );

	if ( spinner ) {
		spinner.parentNode.removeChild( spinner );
	}

	// Release loading state.
	checkoutForm.classList.remove( 'loading' );

	// Add notice.
	outputNotice( {
		errorMessage: error.message,
		errorContainer: document.getElementById( 'edds-prb-checkout' ),
		errorContainerReplace: false,
	} );
}

/**
 * Handles recieving a Payment Method from the Payment Request.
 *
 * Adds an item to the cart and processes the Checkout as if we are
 * in normal Checkout context.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} checkoutForm Checkout form.
 * @param {Object} event paymentmethod event.
 */
async function onPaymentMethod( paymentRequest, checkoutForm, event ) {
	try {
		// Retrieve information from the PRB event.
		const { paymentMethod, payerEmail, payerName } = event;

		// Loading state. Block interaction.
		checkoutForm.classList.add( 'loading' );

		// Create and append a spinner.
		const spinner = document.createElement( 'span' );
		[ 'edd-loading-ajax', 'edd-loading', 'edds-prb-spinner' ].forEach(
			( className ) => spinner.classList.add( className )
		);
		checkoutForm.appendChild( spinner );

		const data = {
			email: payerEmail,
			name: payerName,
			paymentMethod,
			context: 'checkout',
		};

		const tokenInput = $( '#edd-process-stripe-token' );

		// Start the processing.
		//
		// Shims $_POST data to align with the standard Checkout context.
		//
		// This calls `_edds_process_purchase_form()` server-side which
		// creates and returns a PaymentIntent -- just like the first step
		// of a true Checkout.
		const {
			intent,
			intent: {
				client_secret: clientSecret,
				object: intentType,
			},
			nonce: refreshedNonce,
		} = await apiRequest( 'edds_prb_ajax_process_checkout', {
			name: payerName,
			paymentMethod,
			form_data: $( '#edd_purchase_form' ).serialize(),
			timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
			token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		} );

		// Update existing nonce value in DOM form data in case data is retrieved
		// again directly from the DOM.
		$( '#edd-process-checkout-nonce' ).val( refreshedNonce );

		// Complete the Payment Request to hide the payment sheet.
		event.complete( 'success' );

		// Confirm the card (SCA, etc).
		const confirmFunc = 'setup_intent' === intentType
			? 'confirmCardSetup'
			: 'confirmCardPayment';

		eddStripe[ confirmFunc ](
			clientSecret,
			{
				payment_method: paymentMethod.id
			},
			{
				handleActions: false,
			}
		)
			.then( ( { error } ) => {
				// Something went wrong. Alert the Payment Request.
				if ( error ) {
					throw error;
				}

				// Confirm again after the Payment Request dialog has been hidden.
				// For cards that do not require further checks this will throw a 400
				// error (in the Stripe API) and a log console error but not throw
				// an actual Exception. This can be ignored.
				//
				// https://github.com/stripe/stripe-payments-demo/issues/133#issuecomment-632593669
				eddStripe[ confirmFunc ]( clientSecret )
					.then( async ( { error } ) => {
						try {
							if ( error ) {
								throw error;
							}

							// Create an EDD Payment.
							const { intent: updatedIntent, nonce } = await createPayment( intent );

							// Complete the EDD Payment with the updated PaymentIntent.
							await completePayment( updatedIntent, nonce );

							// Redirect on completion.
							window.location.replace( edd_stripe_vars.successPageUri );

							// Something went wrong, output a notice.
						} catch ( error ) {
							onPaymentMethodError( event, error, checkoutForm );
						}
					} );
			} )
			.catch( ( error ) => {
				onPaymentMethodError( event, error, checkoutForm );
			} );

	// Something went wrong, output a notice.
	} catch ( error ) {
		onPaymentMethodError( event, error, checkoutForm );
	}
}

/**
 * Determines if a full page reload is needed when applying a discount.
 *
 * A 100% discount switches to the "manual" gateway, bypassing the Stripe,
 * however we are still bound to the Payment Request button and a standard
 * Purchase button is not present in the DOM to switch back to.add-new-card
 *
 * @param {Event} e edd_discount_applied event.
 * @param {Object} response Discount application response.
 * @param {int} response.total_plain Cart total after discount.
 */
function onApplyDiscount( e, { total_plain: total } ) {
	if ( true === IS_PRB_GATEWAY && 0 === total ) {
		window.location.reload();
	}
}

/**
 * Binds purchase link form events.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} checkoutForm Checkout form.
 */
function bindEvents( paymentRequest, checkoutForm ) {
	const $body = jQuery( document.body );

	// Cart quantities have changed.
	$body.on( 'edd_quantity_updated', () => onChange( paymentRequest, checkoutForm ) );

	// Discounts have changed.
	$body.on( 'edd_discount_applied', () => onChange( paymentRequest, checkoutForm ) );
	$body.on( 'edd_discount_removed', () => onChange( paymentRequest, checkoutForm ) );

	// Handle a PaymentMethod when available.
	paymentRequest.on( 'paymentmethod', ( event ) => {
		onPaymentMethod( paymentRequest, checkoutForm, event );
	} );

	// Handle 100% discounts that require a full gateway refresh.
	$body.on( 'edd_discount_applied', onApplyDiscount );
}

/**
 * Mounts Payment Request buttons (if possible).
 *
 * @param {HTMLElement} element Payment Request button mount wrapper.
 */
function mount( element ) {
	const { eddStripe } = window;

	const checkoutForm = document.getElementById( 'edd_checkout_form_wrap' );

	try {
		// Gather initial data.
		const { 'display-items': displayItems, ...data } = parseDataset( element.dataset );

		// Create a Payment Request object.
		const paymentRequest = eddStripe.paymentRequest( {
			// Only requested to prompt full address information collection for Apple Pay.
			//
			// On-page name fields are used to update the Easy Digital Downloads Customer.
			// The Payment Request's Payment Method populate the Customer's Billing Details.
			//
			// @link https://stripe.com/docs/js/payment_request/create#stripe_payment_request-options-requestPayerName
			requestPayerName: true,
			displayItems,
			...data,
		} );

		// Create a Payment Request button.
		const elements = eddStripe.elements();
		const prButton = elements.create( 'paymentRequestButton', {
			paymentRequest: paymentRequest,
		} );

		const wrapper = document.querySelector( `#${ element.id }` );

		// Check the availability of the Payment Request API.
		paymentRequest.canMakePayment()
			// Attempt to mount.
			.then( function( result ) {
				// Hide wrapper if nothing can be mounted.
				if ( ! result ) {
					return hideAndSwitchGateways();
				}

				// Hide wrapper if using Apple Pay but in Test Mode.
				// The verification for Connected accounts in Test Mode is not reliable.
				if ( true === result.applePay && 'true' === edd_stripe_vars.isTestMode ) {
					return hideAndSwitchGateways();
				}

				// Mount.
				wrapper.style.display = 'block';
				checkoutForm.classList.add( 'edd-prb--is-active' );
				prButton.mount( `#${ element.id } .edds-prb__button` );

				// Bind variable pricing/quantity events.
				bindEvents( paymentRequest, checkoutForm );

				// Handle "Terms of Service" and "Privacy Policy" client validation.
				prButton.on( 'click', onClick );
			} );
	} catch ( error ) {
		outputNotice( {
			errorMessage: error.message,
			errorContainer: document.querySelector( '#edds-prb-checkout' ),
			errorContainerReplace: false,
		} );
	}
};

/**
 * Performs an initial check for Payment Request support.
 *
 * Used when Stripe is not the default gateway (and therefore Express Checkout is not
 * loaded by default) to do a "background" check of support while a different initial
 * gateway is loaded.
 *
 * @link https://github.com/easydigitaldownloads/edd-stripe/issues/652
 */
function paymentRequestPrecheck() {
	const {
		eddStripe: stripe,
		edd_stripe_vars: config
	} = window;

	if ( ! config || ! stripe ) {
		return;
	}

	const { currency, country, checkoutHasPaymentRequest } = config;

	if ( 'false' === checkoutHasPaymentRequest ) {
		return;
	}

	stripe.paymentRequest( {
		country,
		currency: currency.toLowerCase(),
		total: {
			label: 'Easy Digital Downloads',
			amount: 100,
		}
	} )
		.canMakePayment()
		.then( ( result ) => {
			if ( null === result ) {
				hideAndSwitchGateways();
			}

			const checkoutForm = document.getElementById( 'edd_checkout_form_wrap' );
			checkoutForm.classList.add( 'edd-prb--is-active' );
		} );
}

/**
 * Sets up Payment Request functionality for single purchase links.
 */
export function setup() {
	if ( '1' !== edd_scripts.is_checkout ) {
		return;
	}

	/**
	 * Mounts PRB when the gateway has loaded.
	 *
	 * @param {Event} e Gateway loaded event.
	 * @param {string} gateway Gateway ID.
	 */
	jQuery( document.body ).on( 'edd_gateway_loaded', ( e, gateway ) => {
		if ( 'stripe-prb' !== gateway ) {
			IS_PRB_GATEWAY = false;

			// Always check for Payment Request support if Stripe is active.
			paymentRequestPrecheck();

			return;
		}

		const prbEl = document.querySelector( '.edds-prb.edds-prb--checkout' );

		if ( ! prbEl ) {
			return;
		}

		IS_PRB_GATEWAY = true;

		mount( prbEl );
	} );
}
