/* global EDDExtensionManager */

; ( function ( document, $ ) {
	'use strict';

	$( '.edd-extension-manager' ).on( 'click', function ( e ) {
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

			case 'goto-url':
				window.location.href = $btn.attr( 'data-url' );
				return;

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

		$.post( EDDExtensionManager.ajaxurl, data )
			.done( function ( res ) {
				console.log( res );
				if ( res.success ) {
					$btn.html( res.data.msg );
				}
				// app.stepInstallDone( res, $btn, action );
			} )
			.always( function () {
				$btn.attr( 'disabled', false );
			} );
	} );
} )( document, jQuery );
