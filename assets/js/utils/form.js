/**
 * Internal dependencies.
 */
/**
 * External dependencies
 */
import { forEach } from 'utils';

/**
 * Checks is a form passes HTML5 validation.
 *
 * @param {HTMLElement} form Form to trigger validation on.
 * @return {Bool} If the form has valid inputs.
 */
export function hasValidInputs( form ) {
	let plainInputsValid = true;

	forEach( form.querySelectorAll( 'input' ), function( input ) {
		if ( input.checkValidity && ! input.checkValidity() ) {
			plainInputsValid = false;
		}
	} );

	return plainInputsValid;
}

/**
 * Triggers HTML5 browser validation.
 *
 * @param {HTMLElement} form Form to trigger validation on.
 */
export function triggerBrowserValidation( form ) {
	const submit = document.createElement( 'input' );
	submit.type = 'submit';
	submit.style.display = 'none';

	form.appendChild( submit );
	submit.click();
	submit.remove();
}

/**
 * Returns an input's value, or null.
 *
 * @param {HTMLElement} field Field to retrieve value from.
 * @return {null|string} Value if the field has a value.
 */
export function fieldValueOrNull( field ) {
	if ( ! field ) {
		return null;
	}

	if ( '' === field.value ) {
		return null;
	}

	return field.value;
}
