/* global wp */

( () => {
	const widget = document.getElementById( 'edd_dashboard_sales' );

	if ( ! widget ) {
		return;
	}

	/**
	 * Populate widget area with latest store data.
	 */
	wp.ajax.send( {
		type: 'GET',
		data: {
			action: 'edd_load_dashboard_widget',
		},
		success: function( response ) {
			widget.querySelector( '.inside' ).innerHTML = response;
		},
	} );
} ) ();
