/* global ajaxurl */

jQuery( document ).ready( function( $ ) {
	$( '.edd-dashboard-widget' ).each( function() {
		const widget = $( this ).data( 'eddDashboardWidget' );

		$.ajax( {
			type: 'GET',
			data: {
				action: `edd_load_dashboard_${ widget}_widget`,
			},
			url: ajaxurl,
			success: ( response ) => {
				$( this ).replaceWith( response );
			},
		} );
	} );
} );
