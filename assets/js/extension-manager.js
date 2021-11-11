/* global EDDExtensionManager */

; ( function ( document, $ ) {
	'use strict';

	$( '.edd-extension-manager__action' ).on( 'click', function ( e ) {
		e.preventDefault();

		var $btn = $( this ),
			action = $btn.attr( 'data-action' ),
			plugin = $btn.attr( 'data-plugin' ),
			ajaxAction = '';

		if ( $btn.attr( 'disabled' ) ) {
			return;
		}

		switch ( action ) {
			case 'activate':
				ajaxAction = 'edd_activate_extension';
				$btn.text( EDDExtensionManager.activating );
				break;

			case 'install':
				ajaxAction = 'edd_install_extension';
				$btn.text( EDDExtensionManager.installing );
				break;

			default:
				return;
		}

		$btn.removeClass( 'button-primary' ).attr( 'disabled', true ).addClass( 'updating-message' );

		var data = {
			action: ajaxAction,
			nonce: EDDExtensionManager.extension_manager_nonce,
			plugin: plugin,
			type: $btn.attr( 'data-type' ),
			pass: $btn.attr( 'data-pass' ),
			id: $btn.attr( 'data-id' ),
			product: $btn.attr( 'data-product' ),
		};

		$.post( ajaxurl, data )
			.done( function ( res ) {
				console.log( res );
				var thisStep = $btn.closest( '.edd-extension-manager__step' );
				if ( res.success ) {
					var nextStep = thisStep.next();
					if ( nextStep.length ) {
						thisStep.fadeOut();
						nextStep.fadeIn();
					}
				} else {
					thisStep.fadeOut();
					var message = res.data.message;
					if ( res.data[ 0 ][ 'message' ] ) {
						message = res.data[ 0 ][ 'message' ];
					}
					thisStep.after( '<p>' + message + '</p>' );
				}
			} );
	} );
} )( document, jQuery );
