// add a JS listener to name="edd_email" input field (text field) to trigger an ajax call to check if the email is already registered
document.addEventListener( 'DOMContentLoaded', function () {
	var emailField = document.querySelector( 'input[name="edd_email"]' );
	if ( !emailField ) {
		return;
	}
	emailField.addEventListener( 'change', function () {
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
			if ( ! response.success ) {
				message = response.data.message;
			}

			emailField.setCustomValidity( message );
			emailField.reportValidity();
		} );
	} );
} );
