/* global EDDAdminEmails */

; ( function ( document, $ ) {
	'use strict';

	$( '.edd-email-manager__action' ).on( 'click', function ( e ) {
		e.preventDefault();

		const $btn = $( this ),
			action = $btn.attr( 'data-action' );

		let removeClass = '',
			addClass = '',
			replaceAction = '',
			replaceStatus = '';

		if ( $btn.attr( 'disabled' ) ) {
			return;
		}

		switch ( action ) {
			case 'enable':
				addClass = 'edd-button-toggle--active';
				replaceAction = 'disable';
				replaceStatus = 'inactive';
				break;

			case 'disable':
				removeClass = 'edd-button-toggle--active';
				replaceAction = 'enable';
				replaceStatus = 'active';
				break;

			default:
				return;
		}

		$btn.attr( 'disabled', true ).addClass( 'edd-updating' );

		const data = {
			action: 'edd_update_email_status',
			nonce: EDDAdminEmails.nonce,
			email_id: $btn.attr( 'data-id' ),
			status: $btn.attr( 'data-status' ),
			button: action,
		};

		$.post( EDDAdminEmails.ajaxurl, data )
			.done( function ( res ) {
				if ( EDDAdminEmails.debug ) {
					console.log( res );
				}
				$btn.attr( 'disabled', false ).removeClass( 'edd-updating' );
				if ( res.success ) {
					$btn.removeClass( removeClass ).addClass( addClass );
					$btn.attr( 'data-action', replaceAction );
					$btn.attr( 'data-status', replaceStatus );
				}
			} );
	} );
} )( document, jQuery );
