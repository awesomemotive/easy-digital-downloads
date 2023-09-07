/* global $, edd_stripe_vars */

/**
 * Internal dependencies.
 */
import {
	generateNotice,
	fieldValueOrNull,
	forEach
} from 'utils'; // eslint-disable-line @wordpress/dependency-group

const DEFAULT_ELEMENTS = {
	'card': '#edd-stripe-card-element',
}

const DEFAULT_SPLIT_ELEMENTS = {
	'cardNumber': '#edd-stripe-card-element',
	'cardExpiry': '#edd-stripe-card-exp-element',
	'cardCvc': '#edd-stripe-card-cvc-element',
}

let ELEMENTS_OPTIONS = { ...edd_stripe_vars.elementsOptions };

/**
 * Mounts Elements based on payment form configuration.
 *
 * Assigns a `cardElement` object to the global `eddStripe` object
 * that can be used to collect card data for tokenization.
 *
 * Integrations (such as Recurring) should pass a configuration of Element
 * types and specific HTML IDs to mount based on settings and form markup
 * to avoid attempting to mount to the same `HTMLElement`.
 *
 * @since 2.8.0
 *
 * @param {Object} elementsInstance Stripe Elements instance.
 * @return {Element} The last Stripe Element to be mounted.
 */
export function createPaymentForm( elementsInstance, elements ) {
	let mountedEl;

	if ( ! elements ) {
		elements = ( 'true' === edd_stripe_vars.elementsSplitFields )
			? DEFAULT_SPLIT_ELEMENTS
			: DEFAULT_ELEMENTS;
	}

	forEach( elements, ( selector, element ) => {
		mountedEl = createAndMountElement( elementsInstance, selector, element );
	} );

	// Make at least one Element available globally.
	window.eddStripe.cardElement = mountedEl;

	return mountedEl;
}

/**
 * Generates and returns an object of styles that can be used to change the appearance
 * of the Stripe Elements iFrame based on existing form styles.
 *
 * Styles that can be applied to the current DOM are injected to the page via
 * a <style> element.
 *
 * @link https://stripe.com/docs/stripe-js/reference#the-elements-object
 *
 * @since 2.8.0
 *
 * @return {Object}
 */
function generateElementStyles() {
	// Try to mimick existing input styles.
	const cardNameEl = document.querySelector( '.card-name.edd-input' );

	if ( ! cardNameEl ) {
		return null;
	}

	const inputStyles = window.getComputedStyle( cardNameEl );

	// Inject inline CSS instead of applying to the Element so it can be overwritten.
	if ( ! document.getElementById( 'edds-stripe-element-styles' ) ) {
		const styleTag = document.createElement( 'style' );

		styleTag.innerHTML = `
			.edd-stripe-card-element.StripeElement,
			.edd-stripe-card-exp-element.StripeElement,
			.edd-stripe-card-cvc-element.StripeElement {
				background-color: ${ inputStyles.getPropertyValue( 'background-color' ) };

				${
					[ 'top', 'right', 'bottom', 'left' ]
						.map( ( dir ) => (
							`border-${ dir }-color: ${ inputStyles.getPropertyValue( `border-${ dir }-color` ) };
							 border-${ dir }-width: ${ inputStyles.getPropertyValue( `border-${ dir }-width` ) };
							 border-${ dir }-style: ${ inputStyles.getPropertyValue( `border-${ dir }-style` ) };
							 padding-${ dir }: ${ inputStyles.getPropertyValue( `padding-${ dir }` ) };`
						) )
						.join( '' )
				}
				${
					[ 'top-right', 'bottom-right', 'bottom-left', 'top-left' ]
						.map( ( dir ) => (
							`border-${ dir }-radius: ${ inputStyles.getPropertyValue( 'border-top-right-radius' ) };`
						) )
						.join( '' )
				}
			}`
				// Remove whitespace.
				.replace( /\s/g, '' );

		styleTag.id = 'edds-stripe-element-styles';

		document.body.appendChild( styleTag );
	}

	return {
		base: {
			color: inputStyles.getPropertyValue( 'color' ),
			fontFamily: inputStyles.getPropertyValue( 'font-family' ),
			fontSize: inputStyles.getPropertyValue( 'font-size' ),
			fontWeight: inputStyles.getPropertyValue( 'font-weight' ),
			fontSmoothing: inputStyles.getPropertyValue( '-webkit-font-smoothing' ),
		},
	};
}

/**
 * Mounts an Elements Card to the DOM and adds event listeners to submission.
 *
 * @link https://stripe.com/docs/stripe-js/reference#the-elements-object
 *
 * @since 2.8.0
 *
 * @param {Elements} elementsInstance Stripe Elements instance.
 * @param {string} selector Selector to mount Element on.
 * @return {Element|undefined} Stripe Element.
 */
function createAndMountElement( elementsInstance, selector, element ) {
	const el = document.querySelector( selector );

	if ( ! el ) {
		return undefined;
	}

	ELEMENTS_OPTIONS.style = jQuery.extend(
		true,
		{},
		generateElementStyles(),
		ELEMENTS_OPTIONS.style
	);

	// Remove hidePostalCode if not using a combined `card` Element.
	if ( 'cardNumber' === element && ELEMENTS_OPTIONS.hasOwnProperty( 'hidePostalCode' ) ) {
		delete ELEMENTS_OPTIONS.hidePostalCode;
	}

	// Remove unused parameter from options.
	delete ELEMENTS_OPTIONS.i18n;

	const stripeElement = elementsInstance
		.create( element, ELEMENTS_OPTIONS );

	stripeElement
		.addEventListener( 'change', ( event ) => {
			handleElementError( event, el );
			handleCardBrandIcon( event );
		} )
		.mount( el );

	return stripeElement;
}

/**
 * Mounts an Elements Card to the DOM and adds event listeners to submission.
 *
 * @since 2.7.0
 * @since 2.8.0 Deprecated
 *
 * @deprecated Use createPaymentForm() to mount specific elements.
 *
 * @param {Elements} elementsInstance Stripe Elements instance.
 * @param {string} toMount Selector to mount Element on.
 * @return {Element} Stripe Element.
 */
export function mountCardElement( elementsInstance, toMount = '#edd-stripe-card-element' ) {
	const mountedEl = createPaymentForm( elementsInstance, {
		'card': toMount,
	} );

	// Hide split card details fields because any integration that is using this
	// directly has not properly implemented split fields.
	const splitFields = document.getElementById( 'edd-card-details-wrap' );

	if ( splitFields ) {
		splitFields.style.display = 'none';
	}

	return mountedEl;
}

/**
 * Handles error output for Elements Card.
 *
 * @param {Event} event Change event on the Card Element.
 * @param {HTMLElement} el HTMLElement the Stripe Element is being mounted on.
 */
function handleElementError( event, el ) {
	const newCardContainer = el.closest( '.edd-stripe-new-card' );
	const errorsContainer  = newCardContainer.querySelector( '#edd-stripe-card-errors' );

	// Only show one error at once.
	errorsContainer.innerHTML = '';

	if ( event.error ) {
		const { code, message } = event.error;
		const { elementsOptions: { i18n: { errorMessages } } } = window.edd_stripe_vars;

		const localizedMessage = errorMessages[ code ] ? errorMessages[ code ] : message;

		errorsContainer.appendChild( generateNotice( localizedMessage ) );
	}
}

/**
 * Updates card brand icon if using a split form.
 *
 * @since 2.8.0
 *
 * @param {Event} event Change event on the Card Element.
 */
function handleCardBrandIcon( event ) {
	const {
		brand,
		elementType,
	} = event;

	if ( 'cardNumber' !== event.elementType ) {
		return;
	}

	const cardTypeEl = document.querySelector( '.card-type' );

	if ( 'unknown' === brand ) {
		cardTypeEl.className = 'card-type';
	} else {
		cardTypeEl.classList.add( brand );
	}
}

/**
 * Retrieves (or creates) a PaymentMethod.
 *
 * @param {HTMLElement} billingDetailsForm Form to find data from.
 * @return {Object} PaymentMethod ID and if it previously existed.
 */
export function getPaymentMethod( billingDetailsForm, cardElement ) {
	const selectedPaymentMethod = $( 'input[name="edd_stripe_existing_card"]:checked' );

	// An existing PaymentMethod is selected.
	if ( selectedPaymentMethod.length > 0 && 'new' !== selectedPaymentMethod.val() ) {
		return Promise.resolve( {
			id: selectedPaymentMethod.val(),
			exists: true,
		} );
	}

	// Create a PaymentMethod using the Element data.
	return window.eddStripe
		.createPaymentMethod(
			'card',
			cardElement,
			{
				billing_details: getBillingDetails( billingDetailsForm ),
			}
		)
		.then( function( result ) {
			if ( result.error ) {
				throw result.error;
			}

			return {
				id: result.paymentMethod.id,
				exists: false,
			};
		} );
}

/**
 * Retrieves billing details from the Billing Details sections of a form.
 *
 * @param {HTMLElement} form Form to find data from.
 * @return {Object} Billing details
 */
export function getBillingDetails( form ) {
	return {
		// @todo add Phone
		// @todo add Email
		name: fieldValueOrNull( form.querySelector( '.card-name' ) ),
		address: {
			line1: fieldValueOrNull( form.querySelector( '.card-address' ) ),
			line2: fieldValueOrNull( form.querySelector( '.card-address-2' ) ),
			city: fieldValueOrNull( form.querySelector( '.card-city' ) ),
			state: fieldValueOrNull( form.querySelector( '.card_state' ) ),
			postal_code: fieldValueOrNull( form.querySelector( '.card-zip' ) ),
			country: fieldValueOrNull( form.querySelector( '#billing_country' ) ),
		},
	};
}
