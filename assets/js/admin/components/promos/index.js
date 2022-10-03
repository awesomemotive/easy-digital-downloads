/* global ajaxurl */

jQuery( document ).ready( function( $ ) {
	/**
	 * Dismiss notices
	 */
	$( '.edd-promo-notice' ).each( function() {
		const notice = $( this );

		notice.on( 'click', '.edd-promo-notice-dismiss', function( e ) {
			// Only prevent default behavior for buttons, not links.
			if ( ! $( this ).attr( 'href' ) ) {
				e.preventDefault();
			}

			$.ajax( {
				type: 'POST',
				data: {
					action: 'edd_dismiss_promo_notice',
					notice_id: notice.data( 'id' ),
					nonce: notice.data( 'nonce' ),
					lifespan: notice.data( 'lifespan' )
				},
				url: ajaxurl,
				success: function( response ) {
					notice.slideUp();
				}
			} );
		} );
	} );
} );
