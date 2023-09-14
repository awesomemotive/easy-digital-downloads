/* global Stripe, edd_scripts, edd_stripe_vars */

/**
 * Internal dependencies.
 */
import {
	fieldValueOrNull, consoleOutput
} from 'utils'; // eslint-disable-line @wordpress/dependency-group

import { getGlobal } from 'utils/globals.js';

import { isStripeSelectedGateway, disableForm, updateForm, enableForm } from './contexts/checkout/form.js';


let ELEMENTS_CUSTOMIZATIONS = { ...edd_stripe_vars.elementsCustomizations };

/**
 * Mounts the Payment Element to the DOM and adds event listeners to submission.
 *
 * @since 2.9.0
 *
 * @param {Elements} elementsInstance Stripe Elements instance.
 * @param {string} selector Selector to mount Element on.
 * @return {Element|undefined} Stripe Element.
 */
export function createAndMountElement() {
	window.eddStripe.elementMounted = false;

	const el = document.querySelector( getGlobal( 'elementsTarget' ) );

	if ( ! el ) {
		return undefined;
	}

	const amount = getElementsAmount();
	const styles = generateElementStyles();

	let fonts = [];
	if ( ELEMENTS_CUSTOMIZATIONS.fonts.length ) {
		ELEMENTS_CUSTOMIZATIONS.fonts.forEach( font => fonts.push( font ) );
	}

	const elementOptions = {
		mode: 'payment',
		amount: amount,
		setupFutureUsage: 'off_session',
		paymentMethodCreation: 'manual',
		currency: edd_stripe_vars.currency.toLowerCase(),
		loader: 'always',
		appearance: styles,
		fonts: fonts,
	};

	if ( ELEMENTS_CUSTOMIZATIONS.paymentMethodTypes.length ) {
		elementOptions.paymentMethodTypes = ELEMENTS_CUSTOMIZATIONS.paymentMethodTypes;
	}

	consoleOutput( 'Stripe.elements() creation options', elementOptions );

	let elements = window.eddStripe.elements( elementOptions );
	window.eddStripe.configuredElement = elements;

	const purchaseForm   = document.getElementById( 'edd_purchase_form' );
	const billingDetails = getBillingDetails( purchaseForm );

	let createOptions = {
		defaultValues: {
			billingDetails: billingDetails,
		},
		fields: ELEMENTS_CUSTOMIZATIONS.fields,
		layout: ELEMENTS_CUSTOMIZATIONS.layout,
		wallets: ELEMENTS_CUSTOMIZATIONS.wallets,
		terms: ELEMENTS_CUSTOMIZATIONS.terms,
		business: {
			name: edd_stripe_vars.store_name,
		},
	};

	consoleOutput( 'element.create() options', createOptions );

	// Create the payment element.
	let paymentElement = elements.create( 'payment', createOptions );
	window.eddStripe.paymentElement = paymentElement;

	// Mount the element to it's target.
	paymentElement.mount( getGlobal( 'elementsTarget' ) );

	// Bind our event listeners
	bindEvents();

	// Set a global so we can use it later.
	window.eddStripe.elementMounted = true;

	// Set a state for if this is at the failure limit.
	window.eddStripe.isAtFailureLimit = false;
}

/**
 * Generates and returns an object of options that can be used to change the appearance
 * of the Stripe Elements based on existing form styles.
 *
 * Styles that can be applied to the current DOM are injected to the page via
 * a <style> element.
 *
 * @since 2.9.0
 *
 * @return {Object}
 */
function generateElementStyles() {
	// We're going to add a sample field, which should get some styles applied, capture them, then remove them.
	const sampleLabel = document.createElement( 'label' );
	sampleLabel.setAttribute( 'id', 'edds-sample-label' );
	sampleLabel.setAttribute( 'for', 'edds-sample-input' );
	sampleLabel.setAttribute( 'class', 'edd-label' );

	const sampleInput = document.createElement( 'input' );
	sampleInput.setAttribute( 'type', 'text' );
	sampleInput.setAttribute( 'name', 'edds-sample-input' );
	sampleInput.setAttribute( 'class', 'edd-input' );
	sampleInput.setAttribute( 'id', 'edds-sample-input' );
	sampleInput.setAttribute( 'readonly', 'readonly' );

	sampleLabel.appendChild( sampleInput );

	const paymentElementTarget = document.getElementById( 'edd-stripe-payment-element' );
	paymentElementTarget.parentNode.insertBefore( sampleLabel, paymentElementTarget );

	// Try to mimick existing input styles.
	const textInputEl  = document.querySelector( '#edds-sample-input' );
	const textInputElFocus = document.querySelector( '#edds-sample-input', ':focus' );
	const textInputElHover = document.querySelector( '#edds-sample-input', ':hover' );

	const inputStyles = window.getComputedStyle( textInputEl );
	const inputFocusStyles = window.getComputedStyle( textInputElFocus );
	const inputHoverStyles = window.getComputedStyle( textInputElHover );

	const theme = ELEMENTS_CUSTOMIZATIONS.theme;

	// Combine the default variables with the customized ones.
	const defaultVariables = {
		colorText: inputStyles.getPropertyValue( 'color' ),
		colorBackground: inputStyles.getPropertyValue( 'background-color' ),
		borderRadius: inputStyles.getPropertyValue( 'border-radius' ),
		colorIconTab: inputStyles.getPropertyValue( 'color' ),
	};

	const variables = {
		...defaultVariables,
		...ELEMENTS_CUSTOMIZATIONS.variables,
	};

	const inputStandardRules = {
		borderTop: inputStyles.getPropertyValue( 'border-top' ),
		borderRight: inputStyles.getPropertyValue( 'border-right' ),
		borderBottom: inputStyles.getPropertyValue( 'border-bottom' ),
		borderLeft: inputStyles.getPropertyValue( 'border-left' ),
		backgroundColor: inputStyles.getPropertyValue( 'background-color' ),
		borderRadius: inputStyles.getPropertyValue( 'border-radius' ),
		borderColor: inputStyles.getPropertyValue( 'border-color' ),
	};

	const inputHoverRules = {
		borderTop: inputHoverStyles.getPropertyValue( 'border-top' ),
		borderRight: inputHoverStyles.getPropertyValue( 'border-right' ),
		borderBottom: inputHoverStyles.getPropertyValue( 'border-bottom' ),
		borderLeft: inputHoverStyles.getPropertyValue( 'border-left' ),
		backgroundColor: inputHoverStyles.getPropertyValue( 'background-color' ),
		borderRadius: inputHoverStyles.getPropertyValue( 'border-radius' ),
		borderColor: inputHoverStyles.getPropertyValue( 'border-color' ),
	};

	const inputFocusRules = {
		borderTop: inputFocusStyles.getPropertyValue( 'border-top' ),
		borderRight: inputFocusStyles.getPropertyValue( 'border-right' ),
		borderBottom: inputFocusStyles.getPropertyValue( 'border-bottom' ),
		borderLeft: inputFocusStyles.getPropertyValue( 'border-left' ),
		backgroundColor: inputFocusStyles.getPropertyValue( 'background-color' ),
		borderRadius: inputFocusStyles.getPropertyValue( 'border-radius' ),
		borderColor: inputFocusStyles.getPropertyValue( 'border-color' ),
	};

	const inputLabelEl = document.querySelector( '#edds-sample-label' );
	const inputLabelStyles = window.getComputedStyle( inputLabelEl );

	const defaultInputRules = {
		'.Input': inputStandardRules,
		'.Input:focus': inputFocusRules,
		'.Input:hover': inputHoverRules,
		'.Label': {
			fontSize: inputLabelStyles.getPropertyValue( 'font-size' ),
			fontWeight: inputLabelStyles.getPropertyValue( 'font-weight' ),
			fontFamily: inputLabelStyles.getPropertyValue( 'font-family' ),
			color: inputLabelStyles.getPropertyValue( 'color' ),
		},
		'.CheckboxInput': {
			borderTop: inputStyles.getPropertyValue( 'border-top' ),
			borderRight: inputStyles.getPropertyValue( 'border-top' ),
			borderBottom: inputStyles.getPropertyValue( 'border-top' ),
			borderLeft: inputStyles.getPropertyValue( 'border-top' ),
			borderRadius: inputStyles.getPropertyValue( 'border-radius' ),
			backgroundColor: inputStyles.getPropertyValue( 'background-color' ),
		},
		'.CheckboxInput:hover': {
			borderTop: inputHoverStyles.getPropertyValue( 'border-top' ),
			borderRight: inputHoverStyles.getPropertyValue( 'border-top' ),
			borderBottom: inputHoverStyles.getPropertyValue( 'border-top' ),
			borderLeft: inputHoverStyles.getPropertyValue( 'border-top' ),
		},
		'.CodeInput': inputStandardRules,
		'.CodeInput:focus': inputFocusRules,
	};

	/**
	 * We try and match the tabbed interface to the gateway selector.
	 *
	 * If Stripe is the only gateway active, we can't do this, so we have to conditionally add the rules.
	 */
	const gatewaySelectorEl = document.querySelector( '.edd-gateway-option:not(.edd-gateway-option-selected)' );
	let defaultTabRules;

	if ( null !== gatewaySelectorEl && false === getGlobal( 'singleGateway' ) ) {
		const gatewaySelectorSelectedEl = document.querySelector( '.edd-gateway-option-selected' );

		const selectorStyles = window.getComputedStyle( gatewaySelectorEl );
		const selectorSelectedStyles = window.getComputedStyle( gatewaySelectorSelectedEl );

		defaultTabRules = {
			'.Tab': {
				border: selectorStyles.getPropertyValue( 'border' ),
				backgroundColor: selectorStyles.getPropertyValue( 'background-color' ),
				borderRadius: selectorStyles.getPropertyValue( 'border-radius' ),
			},
			'.Tab--selected': {
				border: selectorSelectedStyles.getPropertyValue( 'border' ),
				backgroundColor: selectorSelectedStyles.getPropertyValue( 'background-color' ),
				borderRadius: selectorSelectedStyles.getPropertyValue( 'border-radius' ),
			},
			'.Tab:hover': {
				border: selectorSelectedStyles.getPropertyValue( 'border' ),
				backgroundColor: selectorSelectedStyles.getPropertyValue( 'background-color' ),
				borderRadius: selectorSelectedStyles.getPropertyValue( 'border-radius' ),
			},
		}
	} else {
		defaultTabRules = {
			'.Tab': inputStandardRules,
			'.Tab--selected': {
				borderTop: inputFocusStyles.getPropertyValue( 'border-top' ),
				borderRight: inputFocusStyles.getPropertyValue( 'border-right' ),
				borderBottom: inputFocusStyles.getPropertyValue( 'border-bottom' ),
				borderLeft: inputFocusStyles.getPropertyValue( 'border-left' ),
				backgroundColor: '#fff',
				borderRadius: inputFocusStyles.getPropertyValue( 'border-radius' ),
				borderColor: inputFocusStyles.getPropertyValue( 'border-color' ),
			},
			'.Tab:hover': inputFocusRules,
		}
	}

	// Now remove our sample field.
	sampleLabel.remove();

	// Combine the default rules, with the user-customized rules.
	const rules = {
		...defaultInputRules,
		...defaultTabRules,
	};

	// Loop through all the customizations and merge them with the default found ones.
	Object.keys( ELEMENTS_CUSTOMIZATIONS.rules ).forEach(key => {
		let foundDefaultRules = {};
		if ( rules[key] ) {
			foundDefaultRules = rules[key];
		}

		rules[key] = {
			...foundDefaultRules,
			...ELEMENTS_CUSTOMIZATIONS.rules[key]
		};
	  });

	return {
		theme: theme,
		labels: ELEMENTS_CUSTOMIZATIONS.labels,
		variables: variables,
		rules: rules,
	};
}

function bindEvents() {
	/**
	 * Since EDD core still uses the jQuery event system, we need to use jQuery for these events.
	 *
	 * A jQuery .trigger() does not get caught by addEventListener, so we need to use the jQuery .on() method.
	 */
	let $window = jQuery( window );
	let $document = jQuery( document );

	// Cart quantities have changed.
	$window.on( 'edd_quantity_updated', () => onAmountChange( 'quantity updated' ) );

	// Discounts have changed.
	$document.on( 'edd_discount_applied', () => onAmountChange( 'discount applied' ) );
	$document.on( 'edd_discount_removed', () => onAmountChange( 'discount removed' ) );

	// When taxes are applied/changed.
	$window.on( 'edd_taxes_recalculated', () => onAmountChange( 'taxes recalcluated' ) );

	/**
	 * The rest of these can use vanilla JS and addEventListener.
	 */

	// Disable the Enter key from submitting the form to allow us to not have to hijack the 'click'.
	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Enter' === event.key && paymentElementExists() ) {
			event.preventDefault();
		}
	} );

	const eddPurchaseForm = document.getElementById( 'edd_purchase_form' );
	const requiredInputs = eddPurchaseForm.querySelectorAll( '[required]' );
	const addressInputs = document.querySelectorAll( '#edd_cc_address .edd-input:not([required]), #edd_cc_address .edd-select:not([required])' );

	const targetedInputs = [ ...requiredInputs, ...addressInputs ];

	// Loop through the targtedInputs
	targetedInputs.forEach( ( input ) => {
		// And add an event listener to run the udpateElementBillingDetails function.
		input.addEventListener( 'change', () => {
			updateElementBillingDetails();
		} );
	} );

	// Now also listen for events triggered by the elements object to possibly disable the purchase button.
	window.eddStripe.paymentElement.on( 'change', (event) => {
		if ( !isStripeSelectedGateway() ) {
			return;
		}

		consoleOutput( 'paymentElement change event', event );
		updateForm();

		// If the element is complete and the form is valid, enable the form.
		if ( true === event.complete ) {
			window.eddStripe.elementComplete = true;
			enableForm();
		} else {
			window.eddStripe.elementComplete = false;
			disableForm();
		}
	} );
}

/**
 * Handles changes to the purchase link form by updating the Payment Request object.
 *
 * @param {paymentElement} paymentElement Payment Element object.
 * @param {HTMLElement} checkoutForm Checkout form.
 */
async function onAmountChange( reason ) {
	if ( !paymentElementExists() ) {
		return;
	}
	updateForm();

	let amount = getElementsAmount();

	consoleOutput( reason, amount );

	enableForm();

	// Update the Payment Request with server-side data.
	window.eddStripe.configuredElement.update( {
		amount: amount,
	} );
}

/**
 * Retrieves the amount to charge from the DOM.
 *
 * @returns {number} Amount to charge.
 */
function getElementsAmount() {
	let totalElement, amount;

	totalElement = document.querySelector('.edd_cart_total .edd_cart_amount' );
	if ( totalElement ) {
		amount = totalElement.getAttribute( 'data-total' );
	} else {
		amount = 0;
	}

	amount = parseFloat( amount );

	if ( 'false' === edd_stripe_vars.is_zero_decimal ) {
		amount = Math.round( amount * 100 );
	}

	return amount;
}

/**
 * Updates the Payment Element with the current billing details.
 */
async function updateElementBillingDetails() {
	if ( !isStripeSelectedGateway() ) {
		return;
	}

	let purchaseForm = document.getElementById( 'edd_purchase_form' ),
		billingDetails = getBillingDetails( purchaseForm ),
		updateArgs = {
			defaultValues: {
				billingDetails: billingDetails,
			},
		};

	consoleOutput( 'updateElementBillingDetails: updateArgs', updateArgs );

	updateElement( updateArgs );
}

/**
 * Updates the Payment Element with the provided data.
 *
 * @param {object} data
 */
function updateElement( data ) {
	window.eddStripe.paymentElement.update( data );
}

/**
 * Retrieves billing details from the Billing Details sections of a form.
 *
 * @param {HTMLElement} form Form to find data from.
 * @return {Object} Billing details
 */
export function getBillingDetails( form ) {
	// Email address could either be in edd_email or edd-email, depending on core or CFM.
	let email = fieldValueOrNull( form.querySelector( '#edd-email' ) );

	if ( null === email ) {
		email = fieldValueOrNull( form.querySelector( '#edd_email' ) );
	}

	return {
		email: email,
		name: fieldValueOrNull( form.querySelector( '#card_name') ),
		phone: fieldValueOrNull( form.querySelector( '.edd-phone' ) ),
		address: {
			line1: fieldValueOrNull( form.querySelector( '#card_address' ) ),
			line2: fieldValueOrNull( form.querySelector( '#card_address_2' ) ),
			city: fieldValueOrNull( form.querySelector( '#card_city' ) ),
			state: fieldValueOrNull( form.querySelector( '#card_state' ) ),
			postal_code: fieldValueOrNull( form.querySelector( '#card_zip' ) ),
			country: fieldValueOrNull( form.querySelector( '#billing_country' ) ),
		},
	};
}

/**
 * Checks if the Payment Element exists.
 * @returns {boolean} True if the payment element exists.
 */
function paymentElementExists () {
	return Boolean( document.getElementById( 'edd-stripe-payment-element' ) );
}
