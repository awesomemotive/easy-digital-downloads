jQuery( document ).ready( function( $ ) {
	if ( $( '#edd_dashboard_sales' ).length ) {
		$.ajax( {
			type: 'GET',
			data: {
				action: 'edd_load_dashboard_widget',
			},
			url: ajaxurl,
			success: function( response ) {
				$( '#edd_dashboard_sales .edd-loading' ).html( response );
			},
		} );
	}
} );
