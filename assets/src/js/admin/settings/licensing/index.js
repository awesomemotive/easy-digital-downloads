/* global EDDLicenseHandler, ajaxurl */

; ( function ( document, $ ) {
	'use strict';

	$( 'p.submit' ).remove();

	$( '.edd-license__control' ).on( 'click', '.edd-license__action', function ( e ) {
		e.preventDefault();

		var button = $( this ),
			action = button.attr( 'data-action' ),
			ajaxAction = '',
			text = button.text(),
			control = $( this ).closest( '.edd-license__control' ),
			input = control.find( 'input[type="password"]' ),
			customAPI = control.find( 'input[name="apiurl"]' );

		if ( button.attr( 'disabled' ) ) {
			return;
		}

		switch ( action ) {
			case 'activate':
				ajaxAction = 'edd_activate_extension_license';
				button.text( EDDLicenseHandler.activating );
				break;

			case 'deactivate':
				ajaxAction = 'edd_deactivate_extension_license';
				button.text( EDDLicenseHandler.deactivating );
				break;

			default:
				return;
		}

		button.removeClass( 'button-primary' ).attr( 'disabled', true ).addClass( 'updating-message' );

		var data = {
			action: ajaxAction,
			token: button.attr( 'data-token' ),
			timestamp: button.attr( 'data-timestamp' ),
			nonce: button.attr( 'data-nonce' ),
			license: input.val(),
			item_id: input.attr( 'data-item' ),
			item_name: input.attr( 'data-name' ),
			key: input.attr( 'data-key' ),
			api: customAPI.val(),
		};

		$.post( ajaxurl, data )
			.done( function ( res ) {
				console.log( res );
				button.text( text );
				let licenseData = control.next( '.edd-license-data' );
				if ( res.success ) {
					if ( res.data.actions ) {
						control.find( '.edd-licensing__actions' ).replaceWith( res.data.actions );
					}
					if ( res.data.message ) {
						if ( 'deactivate' === action ) {
							licenseData.find( 'p' ).replaceWith( res.data.message );
						} else {
							licenseData.replaceWith( res.data.message );
						}
					}
					if ( data.license.length && 'activate' === action ) {
						input.attr( 'readonly', true );
					} else {
						input.attr( 'readonly', false );
					}
				} else {
					licenseData.find( 'p' ).replaceWith( res.data.message );
				}
				button.attr( 'disabled', false ).removeClass( 'updating-message' );
			} );
	} );

	$( '.edd-license__control' ).on( 'click', '.edd-license__delete', function ( e ) {
		e.preventDefault();

		var button = $( this ),
			ajaxAction = 'edd_delete_extension_license',
			control = $( this ).closest( '.edd-license__control' ),
			input = control.find( 'input' );

		var data = {
			action: ajaxAction,
			token: button.attr( 'data-token' ),
			timestamp: button.attr( 'data-timestamp' ),
			nonce: button.attr( 'data-nonce' ),
			license: input.val(),
			item_name: input.attr( 'data-name' ),
		};

		button.attr( 'disabled', true ).addClass( 'updating-message' );
		input.val( '' );

		$.post( ajaxurl, data )
			.done( function ( res ) {
				if ( res.success ) {
					button.hide();
				}
				control.next( '.edd-license-data' ).find( 'p' ).replaceWith( res.data.message );
				button.attr( 'disabled', false ).removeClass( 'updating-message' );
			} );
	} );
} )( document, jQuery );
