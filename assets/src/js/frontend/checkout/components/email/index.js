/**
 * Adds an event listener to the document to check if the email is already registered.
 * Since the email input may be dynamically added to the DOM, we need to listen to the document for changes.
 */
const form = document.getElementById( 'edd_purchase_form' );
if ( form ) {
	form.addEventListener( 'change', function ( event ) {
		if ( event.target.name !== 'edd_email' ) {
			return;
		}

		if ( !event.target.value ) {
			return;
		}

		checkEmail( event.target );
	} );
}

/**
 * Check if the email is already registered.
 *
 * @param {HTMLInputElement} emailField
 */
function checkEmail ( emailField ) {
	var email = emailField.value;
	if ( !email ) {
		return;
	}
	var data = new FormData();
	data.append( 'action', 'edd_check_email' );
	data.append( 'email', email );
	fetch( edd_global_vars.ajaxurl, {
		method: 'POST',
		body: data
	} ).then( function ( response ) {
		return response.json();
	} ).then( function ( response ) {
		let message = '';
		if ( !response.success ) {
			message = response.data.message;
		}

		emailField.setCustomValidity( message );
		emailField.reportValidity();
	} );
}
