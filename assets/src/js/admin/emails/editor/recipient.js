; ( function ( document, $ ) {
	'use strict';

	const recipient = $( '.edd-email__recipient' );
	if ( recipient.length ) {
		const custom = $( '.edd-email__recipient--custom' ),
			admin = $( '.edd-email__recipient--admin' );
		recipient.on( 'change', function ( e ) {
			if ( 'default' === e.target.value ) {
				custom.hide();
				admin.show();
			} else if ( 'custom' === e.target.value ) {
				custom.show();
				admin.hide();
			} else {
				custom.hide();
				admin.hide();
			}
		} );
	}
} )( document, jQuery );
