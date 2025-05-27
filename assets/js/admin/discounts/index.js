/**
 * Internal dependencies.
 */
import './generator';

; ( function ( document, $ ) {
	'use strict';

	const products = $( '#edd_products' );
	const categories = $( '#edd_categories' );
	if ( ! products && ! categories ) {
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
} )( document, jQuery );
