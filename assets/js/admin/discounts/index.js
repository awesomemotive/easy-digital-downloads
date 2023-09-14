/**
 * Internal dependencies.
 */
import { jQueryReady } from 'utils/jquery.js';
import './generator';

/**
 * DOM ready.
 */
jQueryReady( () => {
	const products = $( '#edd_products' );
	const categories = $( '#edd_categories' );
	if ( !products && !categories ) {
		return;
	}

	/**
	 * Show/hide conditions based on input value.
	 */
	products.on( 'change', function () {
		$( '#edd-discount-product-conditions' ).toggle( !!products.val().length );
	} );

	categories.on( 'change', function () {
		$( '#edd-discount-category-conditions' ).toggle( !!categories.val().length );
	} );
} );
