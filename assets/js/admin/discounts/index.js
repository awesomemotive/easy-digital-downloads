/**
 * Internal dependencies.
 */
import { jQueryReady } from 'utils/jquery.js';

/**
 * DOM ready.
 */
jQueryReady( () => {
	const products = $( '#edd_products' );

	if ( ! products ) {
		return;
	}

	/**
	 * Show/hide conditions based on input value.
	 */
	products.change( function() {
			$( '#edd-discount-product-conditions' ).show( products.val() );
	} );
} );
