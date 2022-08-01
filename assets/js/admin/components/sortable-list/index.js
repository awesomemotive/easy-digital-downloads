/**
 * Sortables
 *
 * This makes certain settings sortable, and attempts to stash the results
 * in the nearest .edd-order input value.
 */
jQuery( document ).ready( function( $ ) {
	const edd_sortables = $( 'ul.edd-sortable-list' );

	if ( edd_sortables.length > 0 ) {
		edd_sortables.sortable( {
			axis: 'y',
			items: 'li',
			cursor: 'move',
			tolerance: 'pointer',
			containment: 'parent',
			distance: 2,
			opacity: 0.7,
			scroll: true,

			/**
			 * When sorting stops, assign the value to the previous input.
			 * This input should be a hidden text field
			 */
			stop: function() {
				const keys = $.map( $( this ).children( 'li' ), function( el ) {
					 return $( el ).data( 'key' );
				} );

				$( this ).prev( 'input.edd-order' ).val( keys );
			},
		} );
	}
} );
