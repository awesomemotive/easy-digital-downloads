/**
 * Internal dependencies.
 */
import { jQueryReady } from '@easydigitaldownloads/utils';

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
		$( '#edd-discount-product-conditions' ).toggle( null !== products.val() );
	} );
} );
