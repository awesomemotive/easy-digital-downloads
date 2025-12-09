// Listen for any changes to the email editor and set a flag to warn the user if they try to leave the page without saving.
document.addEventListener( 'DOMContentLoaded', function () {
	var inputs = document.querySelectorAll( 'input, textarea' );
	for ( var i = 0; i < inputs.length; i++ ) {
		inputs[ i ].addEventListener( 'change', function () {
			window.onbeforeunload = function () {
				return true;
			};
		} );
	}
	// Remove the warning if the user saves the email.
	document.getElementById( 'submit' ).addEventListener( 'click', function () {
		window.onbeforeunload = null;
	} );
} );
