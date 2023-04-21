jQuery( document ).ready( function ( $ ) {
	function edd_open_pointer ( i ) {
		pointer = eddPointers.pointers[ i ];
		options = $.extend( pointer.options, {
			close: function () {
				$.post( ajaxurl, {
					pointer: pointer.pointer_id,
					action: 'dismiss-wp-pointer'
				} );
			}
		} );

		$( pointer.target ).pointer( options ).pointer( 'open' );
	}
	for ( var i in eddPointers.pointers ) {
		edd_open_pointer( i );
	}
} );
