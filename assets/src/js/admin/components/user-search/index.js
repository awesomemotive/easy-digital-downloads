jQuery( function ( $ ) {
	// AJAX user search
	$( '.edd-ajax-user-search' )

		// Search
		.on( 'keyup focus', function () {
			let user_search = $( this ).val(),
				exclude = '';

			if ( $( this ).data( 'exclude' ) ) {
				exclude = $( this ).data( 'exclude' );
			}

			$( '.edd_user_search_wrap' ).addClass( 'loading' );

			const data = {
				action: 'edd_search_users',
				user_name: user_search,
				exclude: exclude,
			};

			$.ajax( {
				type: 'POST',
				data: data,
				dataType: 'json',
				url: ajaxurl,

				success: function( search_response ) {
					$( '.edd_user_search_wrap' ).removeClass( 'loading' );
					$( '.edd_user_search_results' ).removeClass( 'hidden' );
					$( '.edd_user_search_results span' ).html( '' );
					if ( search_response.results ) {
						$( search_response.results ).appendTo( '.edd_user_search_results span' );
					}
				},
			} );
		} )

		// Hide
		.on( 'blur', function () {
			if ( edd_user_search_mouse_down ) {
				edd_user_search_mouse_down = false;
			} else {
				$( this ).removeClass( 'loading' );
				$( '.edd_user_search_results' ).addClass( 'hidden' );
			}
		} );

	$( document.body ).on( 'click.eddSelectUser', '.edd_user_search_results span a', function( e ) {
		e.preventDefault();
		const login = $( this ).data( 'login' );
		$( '.edd-ajax-user-search' ).val( login );
		$( '.edd_user_search_results' ).addClass( 'hidden' );
		$( '.edd_user_search_results span' ).html( '' );
	} );

	$( document.body ).on( 'click.eddCancelUserSearch', '.edd_user_search_results a.edd-ajax-user-cancel', function( e ) {
		e.preventDefault();
		$( '.edd-ajax-user-search' ).val( '' );
		$( '.edd_user_search_results' ).addClass( 'hidden' );
		$( '.edd_user_search_results span' ).html( '' );
	} );

	// Cancel user-search.blur when picking a user
	var edd_user_search_mouse_down = false;
	$( '.edd_user_search_results' ).on( 'mousedown', function () {
		edd_user_search_mouse_down = true;
	} );
} );
