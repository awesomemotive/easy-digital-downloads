jQuery( document ).ready( function ( $ ) {
	$( '.edd-blocks__checkout-forms button' ).on( 'click', function ( e ) {
		e.preventDefault();
		var button = $( this ),
			form_id = $( this ).data( 'attr' ),
			current = $( '.edd-blocks__checkout-forms' ).find( 'button:disabled' );
		$( '.edd-checkout-block__personal-info' ).empty().append( '<span class=\"edd-loading-ajax edd-loading\"></span>' );
		$.ajax( {
			type: 'GET',
			data: {
				action: 'edd_blocks_swap_personal_info',
				form_id: form_id,
			},
			url: edd_global_vars.ajaxurl,
			success: function ( response ) {
				button.prop( 'disabled', true );
				current.prop( 'disabled', false );
				$( '.edd-checkout-block__personal-info' ).empty();
				$( '.edd-checkout-block__personal-info' ).html( response.data );
			},
		} );
	} );
} );
