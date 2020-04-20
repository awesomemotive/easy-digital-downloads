/* global jQuery */

jQuery( document ).ready( function( $ ) {
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		e.preventDefault();

		$( this ).closest( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );
} );
