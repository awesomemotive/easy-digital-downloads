/* global jQuery */

jQuery( document ).ready( function( $ ) {
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		e.preventDefault();

		$( this ).closest( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );

	$( document ).on( 'click', function( e ) {
		var advancedFiltersWrapper = $( '#edd-advanced-filters' );

		if ( ! advancedFiltersWrapper.hasClass( 'open' ) ) {
			return;
		}

		if ( ! advancedFiltersWrapper.is( e.target ) && ! advancedFiltersWrapper.has( e.target ).length ) {
			$( '#edd-advanced-filters' ).toggleClass( 'open' );
		}
	});
} );
