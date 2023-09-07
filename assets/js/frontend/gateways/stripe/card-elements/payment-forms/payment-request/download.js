/* global edd_stripe_vars, jQuery */

/**
 * Internal dependencies
 */
import { parseDataset } from './';
import { apiRequest, forEach, outputNotice } from 'utils';
import { handle as handleIntent } from '../../intents';
import { createPayment, completePayment } from '../../payment-forms';

/**
 * Finds the Download ID, Price ID, and quantity values for single Download.
 *
 * @param {HTMLElement} purchaseLink Purchase link form.
 * @return {Object}
 */
function getDownloadData( purchaseLink ) {
	let downloadId, priceId = false, quantity = 1;

	// Download ID.
	const downloadIdEl = purchaseLink.querySelector( '[name="download_id"]' );
	downloadId = parseFloat( downloadIdEl.value );

	// Price ID.
	const priceIdEl = purchaseLink.querySelector(
		`.edd_price_option_${downloadId}:checked`
	);

	if ( priceIdEl ) {
		priceId = parseFloat( priceIdEl.value );
	}

	// Quantity.
	const quantityEl = purchaseLink.querySelector(
		'input[name="edd_download_quantity"]'
	);

	if ( quantityEl ) {
		quantity = parseFloat( quantityEl.value );
	}

	return {
		downloadId,
		priceId,
		quantity,
	};
}

/**
 * Handles changes to the purchase link form by updating the Payment Request object.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} purchaseLink Purchase link form.
 */
async function onChange( paymentRequest, purchaseLink ) {
	const { downloadId, priceId, quantity } = getDownloadData( purchaseLink );

	try {
		// Calculate and gather price information.
		const {
			'display-items': displayItems,
			...paymentRequestData
		} = await apiRequest( 'edds_prb_ajax_get_options', {
			downloadId,
			priceId,
			quantity,
		} )

		// Update the Payment Request with server-side data.
		paymentRequest.update( {
			displayItems,
			...paymentRequestData,
		} )
	} catch ( error ) {
		outputNotice( {
			errorMessage: '',
			errorContainer: purchaseLink,
			errorContainerReplace: false,
		} );
	}
}

/**
 * Updates the Payment Request amount when the "Custom Amount" input changes.
 *
 * @param {HTMLElement} addToCartEl Add to cart button.
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} purchaseLink Purchase link form.
 */
async function onChangeCustomPrice( addToCartEl, paymentRequest, purchaseLink ) {
	const { price } = addToCartEl.dataset;
	const { downloadId, priceId, quantity } = getDownloadData( purchaseLink );

	try {
		// Calculate and gather price information.
		const {
			'display-items': displayItems,
			...paymentRequestData
		} = await apiRequest( 'edds_prb_ajax_get_options', {
			downloadId,
			priceId,
			quantity,
		} )

		// Find the "Custom Amount" price.
		const { is_zero_decimal: isZeroDecimal } = edd_stripe_vars;
		let amount = parseFloat( price );

		if ( 'false' === isZeroDecimal ) {
			amount = Math.round( amount * 100 );
		}

		// Update the Payment Request with the returned server-side data.
		// Force update the `amount` in all `displayItems` and `total`.
		//
		// "Custom Prices" does not support quantities and Payment Requests
		// do not support taxes so the same amount applies across the board.
		paymentRequest.update( {
			displayItems: displayItems.map( ( { label } ) => ( {
				label,
				amount,
			} ) ),
			...paymentRequestData,
			total: {
				label: paymentRequestData.total.label,
				amount,
			},
		} )
	} catch ( error ) {
		outputNotice( {
			errorMessage: '',
			errorContainer: purchaseLink,
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
function onPaymentMethodError( event, error, purchaseLink ) {
	// Complete the Payment Request to hide the payment sheet.
	event.complete( 'success' );

	// Release loading state.
	purchaseLink.classList.remove( 'loading' );

	outputNotice( {
		errorMessage: error.message,
		errorContainer: purchaseLink,
		errorContainerReplace: false,
	} );

	// Item is in the cart at this point, so change the Purchase button to Checkout.
	//
	// Using jQuery which will preserve the previously set display value in order
	// to provide better theme compatibility.
	jQuery( 'a.edd-add-to-cart', purchaseLink ).hide();
	jQuery( '.edd_download_quantity_wrapper', purchaseLink ).hide();
	jQuery( '.edd_price_options', purchaseLink ).hide();
	jQuery( '.edd_go_to_checkout', purchaseLink )
		.show().removeAttr( 'data-edd-loading' );
}

/**
 * Handles recieving a Payment Method from the Payment Request.
 *
 * Adds an item to the cart and processes the Checkout as if we are
 * in normal Checkout context.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} purchaseLink Purchase link form.
 * @param {Object} event paymentmethod event.
 */
async function onPaymentMethod( paymentRequest, purchaseLink, event ) {
	try {
		// Retrieve the latest data (price ID, quantity, etc).
		const { downloadId, priceId, quantity } = getDownloadData( purchaseLink );

		// Retrieve information from the PRB event.
		const { paymentMethod, payerEmail, payerName } = event;

		const tokenInput = jQuery( '#edd-process-stripe-token-' + downloadId );

		// Start the processing.
		//
		// Adds the single Download to the cart and then shims $_POST
		// data to align with the standard Checkout context.
		//
		// This calls `_edds_process_purchase_form()` server-side which
		// creates and returns a PaymentIntent -- just like the first step
		// of a true Checkout.
		const {
			intent,
			intent: {
				client_secret: clientSecret,
				object: intentType,
			}
		} = await apiRequest( 'edds_prb_ajax_process_checkout', {
			email: payerEmail,
			name: payerName,
			paymentMethod,
			downloadId,
			priceId,
			quantity,
			context: 'download',
			post_data: jQuery( purchaseLink ).serialize(),
			timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
			token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		} );

		// Complete the Payment Request to hide the payment sheet.
		event.complete( 'success' );

		// Loading state. Block interaction.
		purchaseLink.classList.add( 'loading' );

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
							onPaymentMethodError( event, error, purchaseLink );
						}
					} );
			} )
			.catch( ( error ) => {
				onPaymentMethodError( event, error, purchaseLink );
			} );

	// Something went wrong, output a notice.
	} catch ( error ) {
		onPaymentMethodError( event, error, purchaseLink );
	}
}

/**
 * Listens for changes to the "Add to Cart" button.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} purchaseLink Purchase link form.
 */
function observeAddToCartChanges( paymentRequest, purchaseLink ) {
	const addToCartEl = purchaseLink.querySelector( '.edd-add-to-cart' );

	if ( ! addToCartEl ) {
		return;
	}

	const observer = new MutationObserver( ( mutations ) => {
		mutations.forEach( ( { type, attributeName, target } ) => {
			if ( type !== 'attributes' ) {
				return;
			}

			// Update the Payment Request if the price has changed.
			// Used for "Custom Prices" extension.
			if ( 'data-price' === attributeName ) {
				onChangeCustomPrice( target, paymentRequest, purchaseLink );
			}
		} );
	} );

	observer.observe( addToCartEl, {
		attributes: true,
	} );
}

/**
 * Binds purchase link form events.
 *
 * @param {PaymentRequest} paymentRequest Payment Request object.
 * @param {HTMLElement} purchaseLink Purchase link form.
 */
function bindEvents( paymentRequest, purchaseLink ) {
	// Price option change.
	const priceOptionsEls = purchaseLink.querySelectorAll( '.edd_price_options input[type="radio"]' );

	forEach( priceOptionsEls, ( priceOption ) => {
		priceOption.addEventListener( 'change', () => onChange( paymentRequest, purchaseLink ) );
	} );

	// Quantity change.
	const quantityEl = purchaseLink.querySelector( 'input[name="edd_download_quantity"]' );

	if ( quantityEl ) {
		quantityEl.addEventListener( 'change', () => onChange( paymentRequest, purchaseLink ) );
	}

	// Changes to "Add to Cart" button.
	observeAddToCartChanges( paymentRequest, purchaseLink );
}

/**
 * Mounts Payment Request buttons (if possible).
 *
 * @param {HTMLElement} element Payment Request button mount wrapper.
 */
function mount( element ) {
	const { eddStripe } = window;

	try {
		// Gather initial data.
		const { 'display-items': displayItems, ...data } = parseDataset( element.dataset );

		// Find the purchase link form.
		const purchaseLink = element.closest(
			'.edd_download_purchase_form'
		);

		// Create a Payment Request object.
		const paymentRequest = eddStripe.paymentRequest( {
			// Requested to prompt full address information collection for Apple Pay.
			//
			// Collected email address is used to create/update Easy Digital Downloads Customer.
			//
			// @link https://stripe.com/docs/js/payment_request/create#stripe_payment_request-options-requestPayerName
			requestPayerEmail: true,
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
					return;
				}

				// Hide wrapper if using Apple Pay but in Test Mode.
				// The verification for Connected accounts in Test Mode is not reliable.
				if ( true === result.applePay && 'true' === edd_stripe_vars.isTestMode ) {
					return;
				}

				// Mount.
				wrapper.style.display = 'block';
				purchaseLink.classList.add( 'edd-prb--is-active' );
				prButton.mount( `#${ element.id } .edds-prb__button` );

				// Bind variable pricing/quantity events.
				bindEvents( paymentRequest, purchaseLink );
			} );

		// Handle a PaymentMethod when available.
		paymentRequest.on( 'paymentmethod', ( event ) => {
			onPaymentMethod( paymentRequest, purchaseLink, event );
		} );
	} catch ( error ) {
		outputNotice( {
			errorMessage: error.message,
			errorContainer: purchaseLink,
			errorContainerReplace: false,
		} );
	}
};

/**
 * Sets up Payment Request functionality for single purchase links.
 */
export function setup() {
	forEach( document.querySelectorAll( '.edds-prb.edds-prb--download' ), mount );
}
