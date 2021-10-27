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

		$btn.attr( 'disabled', true );

		var data = {
			action: ajaxAction,
			nonce: EDDExtensionManager.extension_manager_nonce,
			plugin: plugin,
			type: $btn.attr( 'data-type' ),
		};

		$.post( ajaxurl, data )
			.done( function ( res ) {
				console.log( res );
				if ( res.success ) {
					$btn.html( res.data.msg );

					var thisStep = $btn.closest( '.edd-extension-manager__step' ),
						nextStep = thisStep.next();

					if ( nextStep.length ) {
						thisStep.fadeOut();
						nextStep.fadeIn();
					}
				}
			} )
			.always( function () {
			} );
	} );
} )( document, jQuery );
