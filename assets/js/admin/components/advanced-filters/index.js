/* global jQuery */

jQuery( document ).ready( function( $ ) {
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		e.preventDefault();

		$( this ).closest( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );

	$( document ).on( 'click', function( e ) {
		if ( $( '#edd-advanced-filters' ).hasClass('open') && $( e.target ).closest( '#edd-advanced-filters' ).length == 0 ) {
			$( '#edd-advanced-filters' ).toggleClass( 'open' );
		}
	});
} );
