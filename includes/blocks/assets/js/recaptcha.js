jQuery( document ).ready( function ( $ ) {
	if ( !EDDreCAPTCHA.sitekey ) {
		return;
	}
	var reCAPTCHAinput = document.querySelector( 'input#edd-blocks-recaptcha' );
	if ( !reCAPTCHAinput ) {
		return;
	}
	EDDreCAPTCHA.action = document.querySelector( 'input[name="edd_action"]' ).value;
	EDDreCAPTCHA.submit = document.querySelector( 'input[name="edd_submit"]' ).value;
	reCAPTCHAinput.addEventListener( 'invalid', function () {
		grecaptcha.execute( EDDreCAPTCHA.sitekey, { action: EDDreCAPTCHA.action } ).then( function ( token ) {
			$.ajax( {
				type: 'POST',
				data: {
					action: 'edd_recaptcha_validate',
					token: token,
					ip: document.querySelector( '[name="edd_blocks_ip"]' ).value,
				},
				url: EDDreCAPTCHA.ajaxurl,
				success: function ( response ) {
					var submitButton = document.querySelector( '#' + EDDreCAPTCHA.submit );
					if ( response.success ) {
						reCAPTCHAinput.value = token;
						submitButton.click();
					} else {
						reCAPTCHAinput.value = '';
						var errorNode = document.createElement( 'div' );
						errorNode.classList.add( 'edd_errors', 'edd-alert', 'edd-alert-error', response.data.error );
						errorNode.innerHTML = '<p class="edd_error"><strong>' + EDDreCAPTCHA.error + '</strong>: ' + response.data.message + '</p>';
						submitButton.closest( 'form' ).before( errorNode );
					}
				},
			} ).fail( function ( response ) {
				reCAPTCHAinput.value = '';
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		} );
	} );
} );
