jQuery( document ).ready( function( $ ) {
	$( 'body' ).on( 'click', '#the-list .editinline', function() {
		let post_id = $( this ).closest( 'tr' ).attr( 'id' );

		post_id = post_id.replace( 'post-', '' );

		const $edd_inline_data = $( '#post-' + post_id );

		const regprice = $edd_inline_data.find( '.column-price .downloadprice-' + post_id ).val();

		// If variable priced product disable editing, otherwise allow price changes
		if ( regprice !== $( '#post-' + post_id + '.column-price .downloadprice-' + post_id ).val() ) {
			$( '.regprice', '#edd-download-data' ).val( regprice ).attr( 'disabled', false );
		} else {
			$( '.regprice', '#edd-download-data' ).val( edd_vars.quick_edit_warning ).attr( 'disabled', 'disabled' );
		}
	} );
} );
