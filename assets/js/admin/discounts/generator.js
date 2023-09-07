jQuery( ( $ ) => {

	let successTimeout;

	/**
	 * When an .edd-toggle is changed in #edd-generator-characters wrapper, if there is only
	 * one checked, make it readonly.
	 */
	$( '#edd-generator-characters .edd-toggle' ).on( 'change', function() {
		const checked = $( '#edd-generator-characters .edd-toggle input:checked' ),
		allInputs = $( '#edd-generator-characters .edd-toggle input' );

		if ( 1 === checked.length ) {
			checked.each( function() {
				$( this ).attr( 'readonly', true );
				$( this ).attr( 'disabled', true );
			} );
		} else {
			allInputs.each( function() {
				$( this ).attr( 'readonly', false );
				$( this ).attr( 'disabled', false );
			} );
		}
	} );

	// When 'Generate' button is clicked, generate a new discount code.
	$( '#edd-generate-code' ).on( 'click', function() {

		const hasLetters = $( '#generator-letters' ).is( ':checked' );
		const hasNumbers = $( '#generator-numbers' ).is( ':checked' );

		if ( ! hasLetters && ! hasNumbers ) {
			showPopupError( edd_vars.no_letters_or_numbers );
			return;
		}

		if ( successTimeout ) {
			clearTimeout( successTimeout );
			$( this ).removeClass( 'updated-message' );
		}

		hidePopupError();

		$( this ).prop( 'disabled', true ).addClass( 'updating-message' );

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'edd_admin_generate_discount_code',
				'edd-discount-nonce': $( '[name="edd-discount-nonce"]' ).val(),
				prefix: $( '#generator-prefix' ).val(),
				limit: $( '#generator-length' ).val(),
				letters: hasLetters,
				numbers: hasNumbers,
			},
			success: ( response ) => {
				if ( response.success ) {
					$( '#edd-code' ).val( response.data.code );
					$( this ).addClass( 'updated-message' );

					successTimeout = setTimeout( () => {
						$( this ).removeClass( 'updated-message' );
					}, 1000);
				} else {
					showPopupError( response.data.message );
				}
			},
			error: ( error ) => {
				showPopupError( error.responseJSON ?? error.responseText );
			},
			complete: () => {
				$( this ).prop( 'disabled', false ).removeClass( 'updating-message' );
			}
		});
	});

	$( '.edd-popup-trigger.disabled' ).on( 'mouseover', function() {
		$( this ).parent().next( '.edd-code-generator-popup' ).show();
	} );

	/**
	 * This prevents hovering over a child element from triggering the mouseout, and hiding the popup prematurely.
	 */
	$( '.edd-popup-trigger.disabled' ).on( 'mouseout', function() {
		return;
	} );

	$( document )
		.on( 'focus', ':input', function() {
			if ( isPopupBoundary( $( this ) ) ) {
				return;
			}
			hidePopup( $( '.edd-code-generator-popup' ) );
		})
		.on( 'click touchstart', function(e) {
			const target = $( e.target ).closest( '.edd-popup-trigger' ).length ? $( '.edd-popup-trigger' )  : $( e.target ) ;

			if ( target.is( '.edd-popup-trigger' ) ) {
				target.parent().next( '.edd-code-generator-popup' ).toggle();
				return;
			}

			if ( ! isPopupBoundary( target ) ) {
				hidePopup( $( '.edd-code-generator-popup' ) );
			}
		})
		.keyup( function( e ) {
			if ( e.keyCode === 27 ) {
				hidePopup( $( '.edd-code-generator-popup' ) );
			}
		});
});

const hidePopup = () => {
	const popup = $( '.edd-code-generator-popup' );

	popup.hide();
	hidePopupError( popup );
};

const isPopupBoundary = ( target ) => {
	return !! target.closest( '.edd-code-generator-popup' ).length;
};

const showPopupError = ( message ) => {
	const errorElement = $( '<div/>' ).addClass( 'notice notice-error' ).append( $( '<p/>' ).text( message ) );
	errorElement.insertBefore( '#edd-generate-code' );
}

const hidePopupError = ( popupElement = false ) => {
	if ( ! popupElement ) {
		popupElement = $( '.edd-code-generator-popup:not(.hidden)' );
	}

	popupElement.find('.notice').remove();
	popupElement.css( 'margin-top', 0 );
};
