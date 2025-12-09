/* global $, edd_stripe_vars */

/**
 * Generates a notice element.
 *
 * @param {string} message The notice text.
 * @param {string} type The type of notice. error or success. Default error.
 * @return {Element} HTML element containing errors.
 */
export function generateNotice( message, type = 'error' ) {
	const notice = document.createElement( 'p' );
	notice.classList.add( 'edd-alert' );
	notice.classList.add( 'edd-stripe-alert' );
	notice.style.clear = 'both';

	if ( 'error' === type ) {
		notice.classList.add( 'edd-alert-error' );
	} else {
		notice.classList.add( 'edd-alert-success' );
	}

	notice.innerHTML = message || edd_stripe_vars.generic_error;

	return notice;
}

/**
 * Outputs a notice.
 *
 *
 * @param {object} args Output arguments.
 * @param {string} args.errorType The type of notice. error or success
 * @param {string} args.errorMessasge The notice text.
 * @param {HTMLElement} args.errorContainer HTML element containing errors.
 * @param {bool} args.errorContainerReplace If true Appends the notice before
 *                                          the container.
 */
export function outputNotice( {
	errorType,
	errorMessage,
	errorContainer,
	errorContainerReplace = true,
} ) {
	const $errorContainer = $( errorContainer );
	const notice = generateNotice( errorMessage, errorType );

	if ( true === errorContainerReplace ) {
		$errorContainer.html( notice );
	} else {
		$errorContainer.before( notice );
	}
}

/**
 * Clears a notice.
 *
 * @param {HTMLElement} errorContainer HTML element containing errors.
 */
export function clearNotice( errorContainer ) {
	$( errorContainer ).html( '' );
}
