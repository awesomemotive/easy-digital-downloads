jQuery( document ).ready( function ( $ ) {
	$( 'body' ).on( 'edd_cart_item_added edd_cart_item_removed', function ( e, response ) {
		if ( $( '.wp-block-edd-cart' ).length && response.block_cart.length ) {
			if ( $( '.edd-blocks__cart-mini' ).length ) {
				$( '.edd-blocks-cart__mini-quantity' ).empty().append( response.quantity_formatted );
				$( '.edd-blocks-cart__mini-total' ).empty().append( response.total );
			}
			if ( $( '.edd-blocks__cart-full' ).length ) {
				$( '.edd-blocks__cart-full .edd-blocks-form__cart' ).replaceWith( response.block_cart );
			}
		}
	} );
} );
