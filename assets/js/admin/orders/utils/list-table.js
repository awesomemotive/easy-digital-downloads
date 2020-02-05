/* global $ */

/**
 * Reindexes inputs a list of table rows (`<tr>`s)
 *
 * Ensures the order of items is correct when the server processes them.
 *
 * @since 3.0.0
 *
 * @param {jQuery} rows List of table rows.
 */
export function reindexRows( rows ) {
	let key = 0;

	$( rows ).each( function() {
		$( this )
			// Set data attribute for reference.
			.attr( 'data-key', key )
			// Update all input names in something[0] format.
			.find( 'input' ).each( function() {
				const input = $( this );
				let name = $( this ).attr( 'name' );

				if ( input.attr( 'name' ) ) {
					const newName = input
						.attr( 'name' )
						.replace( /\[(\d+)\]/, `[${ key }]` )

					input.attr( 'name', newName );
				}
			} );

		key++;
	} );
}

/**
 * Removes a table row.
 *
 * Shows the "no items" row if it is the only remaining row.
 *
 * @since 3.0.0
 *
 * @param {jQuery} row Table row to remove.
 */
export function removeRow( row ) {
	const tbody = row.parents( 'tbody' );

	// Remove row.
	row.remove();

	// Show "no items" if it is the only remaining item.
	if ( 1 === $( 'tr', tbody ).length ) {
		$( '.no-items', tbody ).show();
	}
}
