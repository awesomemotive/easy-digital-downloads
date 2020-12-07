jQuery( document ).ready( function ( $ ) {
	$( '.edd_countries_filter' ).on( 'change', function () {
		const select = $( this ),
			data = {
				action: 'edd_get_shop_states',
				country: select.val(),
				nonce: select.data( 'nonce' ),
				field_name: 'edd_regions_filter',
			};

		$.post( ajaxurl, data, function ( response ) {
			$( 'select.edd_regions_filter' ).find( 'option:gt(0)' ).remove();

			if ( 'nostates' !== response ) {
				$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd_regions_filter' );
			}

			$( 'select.edd_regions_filter' ).trigger( 'chosen:updated' );
		} );

		return false;
	} );
} );
