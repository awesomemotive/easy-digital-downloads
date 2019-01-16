/**
 * Internal dependencies.
 */
import { jQueryReady } from 'js/utils/jquery.js';

/**
 * DOM ready.
 */
jQueryReady( () => {
	const products = $( '#products' );

	if ( ! products ) {
		return;
	}

	/**
	 * Show/hide conditions based on input value.
	 */
	products.change( function() {
		$( '#edd-discount-product-conditions' ).toggle( products.val() );
	} );
} );
