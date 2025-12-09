const reset = document.getElementById( 'edd-email-reset' );
if ( reset ) {
	reset.addEventListener( 'click', e => {
		e.preventDefault();

		// disable the button and add updating-message class
		reset.classList.remove( 'button-primary' );
		reset.classList.add( 'updating-message' );
		reset.disabled = true;

		// do an ajax call to reset the email settings
		const data = {
			action: 'edd_reset_email',
			nonce: EDDAdminEmails.nonce,
			email_id: reset.dataset.email
		};
		fetch( EDDAdminEmails.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams( data )
		} )
			.then( response => response.json() )
			.then( response => {
				if ( response.success ) {
					const editor = tinymce.get( 'edd-email-content' ),
						textArea = document.getElementById( 'edd-email-content' );
					if ( editor ) {
						editor.setContent( response.data.content );
					}
					textArea.value = response.data.content;
					document.querySelector( '.edd-promo-notice-dismiss' ).click();
					reset.classList.remove( 'updating-message' );
					reset.classList.add( 'button-primary', 'updated-message' );
				}
			} )
			.catch( error => {
				console.error( error );
			} );
	} );

	// Wait for the promo notice to be dismissed to re-enable the button.
	document.addEventListener( 'edd_promo_notice_dismiss', e => {
		reset.classList.remove( 'updated-message' );
		reset.disabled = false;
	} );
}
