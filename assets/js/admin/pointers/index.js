jQuery( document ).ready( function ( $ ) {

	function edd_open_pointer ( i ) {
		// Start by setting that there isn't a next pointer.
		let next_pointer_key = false;

		let pointer = eddPointers.pointers[ i ];

		// If the current pointer can't be opened, try the next one.
		if ( !$( pointer.target + ':visible' ).length && eddPointers.pointers[ i + 1 ] ) {
			edd_open_pointer( i + 1 );
			return;
		}

		// If there is a next pointer and it's valid, set the next pointer key.
		if ( eddPointers.pointers[ i + 1 ] && $( eddPointers.pointers[ i + 1 ].target + ':visible' ).length ) {
			next_pointer_key = i + 1;
		}

		if ( next_pointer_key ) {
			// If we have a next pointer, add a button to the pointer to move to the next pointer.
			options = $.extend( pointer.options, {
				buttons: function ( event, t ) {
					button = $( '<button id="edd-pointer-next" class="button button-secondary edd-pointer-next">' + eddPointers.next_label + '</button>' );
					button.bind( 'click.pointer', function ( event ) {
						event.preventDefault();

						// Set argument to dismiss the current pointer, so the user doesn't see it again.
						let dismiss_args = {
							pointer: pointer.pointer_id,
							action: 'dismiss-wp-pointer'
						};
						$.post( ajaxurl, dismiss_args );

						// Close this pointer.
						t.element.pointer( 'close' );

						// Now open the next pointer.
						edd_open_pointer( next_pointer_key );
					} );
					return button;
				}
			} );
		} else {
			// If this is the last pointer, show the standard 'dismiss' link.
			options = $.extend( pointer.options, {
				close: function () {
					$.post( ajaxurl, {
						pointer: pointer.pointer_id,
						action: 'dismiss-wp-pointer'
					} );
				}
			} );
		}

		// Open the requested pointer.
		$( pointer.target ).pointer( options ).pointer( 'open' );
	}

	// If we have pointers registered, open the first one.
	if ( eddPointers.pointers && eddPointers.pointers.length >= 1 ) {
		// Delay it a bit so it doesn't open immediately.
		setTimeout( function () {
			edd_open_pointer( 0 );
		}, 500 );
	}

} );
