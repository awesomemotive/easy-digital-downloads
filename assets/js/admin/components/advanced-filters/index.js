/* global jQuery */

jQuery( document ).ready( function( $ ) {

	// when the 'More' button is clicked.
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		e.preventDefault();

		edd_toggle_advanced_order_filters();
	} );

	// If a click event is triggered.
	$( document ).on( 'click', function( e ) {
		edd_maybe_toggle_advanced_order_filters( e.target );
	});

	// If the Escape key is pressed.
	$( document ).on( 'keydown', function( event ) {
		const key = event.key;
		if ( key === "Escape" ) {
			edd_maybe_toggle_advanced_order_filters();
		}
	});
} );

/**
 * Given a target, determine if we should toggle the advanced orders filter overlay.
 *
 * Determines if the target is the advanced order filters wrappr or an element within it,
 * or in the event of a keypress (target = false), toggles the 'open' class.
 *
 * @param {event|boolean} target The target requested the possible toggle.
 * @returns void
 */
function edd_maybe_toggle_advanced_order_filters( target = false) {
	var advancedFiltersWrapper = $( '#edd-advanced-filters' );

	if ( ! advancedFiltersWrapper.hasClass( 'open' ) ) {
		return false;
	}

	if ( false === target || ( ! advancedFiltersWrapper.is( target ) && ! advancedFiltersWrapper.has( target ).length ) ) {
		edd_toggle_advanced_order_filters();
	}
}

/**
 * Toggles the 'open' class on the advanced order filters overlay.
 */
function edd_toggle_advanced_order_filters() {
	$( '#edd-advanced-filters' ).toggleClass( 'open' );
}
