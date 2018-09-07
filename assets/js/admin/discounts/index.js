/**
 * Discount add / edit screen JS
 */
const EDD_Discount = {

	init: function() {
		this.product_requirements();
	},

	product_requirements: function() {
		$( '#edd-products' ).change( function() {
			const product_conditions = $( '#edd-discount-product-conditions' );

			if ( $( this ).val() ) {
				product_conditions.show();
			} else {
				product_conditions.hide();
			}
		} );
	},
};

export default EDD_Discount;
