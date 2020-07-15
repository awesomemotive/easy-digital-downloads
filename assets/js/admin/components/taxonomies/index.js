/* global jQuery */

jQuery( document ).ready( function ( $ ) {
	if ( $( 'body' ).hasClass( 'taxonomy-download_category' ) || $( 'body' ).hasClass( 'taxonomy-download_tag' ) ) {
		$( 'h1.wp-heading-inline' ).detach().prependTo( '.edd-tab-wrap' );
	}
} );
