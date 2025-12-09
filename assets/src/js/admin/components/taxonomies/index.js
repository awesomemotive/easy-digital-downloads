/* global jQuery */

jQuery( document ).ready( function ( $ ) {
	if ( $( 'body' ).hasClass( 'taxonomy-download_category' ) || $( 'body' ).hasClass( 'taxonomy-download_tag' ) ) {
		$( '.nav-tab-wrapper, .nav-tab-wrapper + br' ).detach().insertAfter( '.wp-header-end' );
	}
} );
