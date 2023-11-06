/* global EDDPassManager, ajaxurl */

; ( function ( document, $ ) {
	'use strict';

	$( '.edd-pass-handler__control' ).on( 'click', '.edd-pass-handler__action', function ( e ) {
		e.preventDefault();

		var $btn = $( this ),
			action = $btn.attr( 'data-action' ),
			ajaxAction = '',
			text = $btn.text();

		if ( $btn.attr( 'disabled' ) ) {
			return;
		}

		switch ( action ) {
			case 'verify':
				ajaxAction = 'edd_verify_pass';
				$btn.text( EDDPassManager.verifying );
				break;

			case 'activate':
				ajaxAction = 'edd_activate_pass';
				$btn.text( EDDPassManager.activating );
				break;

			case 'deactivate':
				ajaxAction = 'edd_deactivate_pass';
				$btn.text( EDDPassManager.deactivating );
				break;

			default:
				return;
		}

		$( '.edd-pass-handler__control + .notice' ).remove();
		$( '.edd-pass-handler__control + p' ).remove();
		$btn.removeClass( 'button-primary' ).attr( 'disabled', true ).addClass( 'updating-message' );

		if ( 'verify' === action ) {
			$( 'body' ).addClass( 'edd-pass-handler__verifying' );
			$( '.edd-pass-handler__control' ).after( '<div class="edd-pass-handler__verifying-wrap"><p class="edd-pass-handler__loading">' + EDDPassManager.verify_loader + '</p></div>' );
		}

		var data = {
			action: ajaxAction,
			token: $btn.attr( 'data-token' ),
			timestamp: $btn.attr( 'data-timestamp' ),
			nonce: $btn.attr( 'data-nonce' ),
			license: $( '#edd_pass_key' ).val(),
		};

		$.post( ajaxurl, data )
			.done( function ( res ) {
				if ( res.success ) {
					$( '.edd-pass-handler__actions' ).replaceWith( res.data.actions );
					if ( res.data.message ) {
						$( '.edd-pass-handler__control' ).after( res.data.message );
					}
					if ( data.license.length && 'deactivate' === action ) {
						$( '#edd_pass_key' ).attr( 'readonly', false );
					} else if ( 'activate' === action || 'verify' === action ) {
						$( '#edd_pass_key' ).attr( 'readonly', true );
						if ( res.data.url && res.data.url.length ) {
							setTimeout( function () {
								window.location.href = res.data.url;
							}, 1500 );
							return;
						}
						if ( 'activate' === action ) {
							$( '#edd-admin-notice-inactivepro, .edd-pass-handler__description' ).slideUp();
							$( '#edd-flyout-button' ).removeClass( 'has-alert' );
							$( '.edd-flyout-item-license' ).remove();
							$( '.edd-flyout-item-activate' ).remove();
						}
					}
				} else {
					$btn.text( text );
					$( '.edd-pass-handler__control' ).after( '<div class="notice inline-notice notice-warning edd-pass-handler__notice">' + res.data.message + '</div>' );
					if ( 'verify' === action ) {
						$( 'body' ).removeClass( 'edd-pass-handler__verifying' );
						$( '.edd-pass-handler__verifying-wrap' ).remove();
					}
				}
				$btn.attr( 'disabled', false ).removeClass( 'updating-message' );
			} );
	} );

	$( '.edd-pass-handler__control' ).on( 'click', '.edd-pass-handler__delete', function ( e ) {
		e.preventDefault();

		var $btn = $( this ),
			ajaxAction = 'edd_delete_pass';

		var data = {
			action: ajaxAction,
			token: $btn.attr( 'data-token' ),
			timestamp: $btn.attr( 'data-timestamp' ),
			nonce: $btn.attr( 'data-nonce' ),
			license: $( '#edd_pass_key' ).val(),
		};

		if ( !data.license ) {
			return;
		}

		$( '.edd-pass-handler__control + .notice' ).remove();
		$( '.edd-pass-handler__control + p' ).remove();
		$btn.attr( 'disabled', true ).addClass( 'updating-message' );
		$( '#edd_pass_key' ).val( '' );

		$.post( ajaxurl, data )
			.done( function ( res ) {
				if ( res.success ) {
					$( '.edd-pass-handler__control' ).after( res.data.message );
					$btn.hide();
				} else {
					$( '.edd-pass-handler__control' ).after( '<div class="notice inline-notice notice-warning">' + res.data.message + '</div>' );
				}
				$btn.attr( 'disabled', false ).removeClass( 'updating-message' );
			} );
	} );
} )( document, jQuery );
