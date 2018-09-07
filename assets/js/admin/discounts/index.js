/**
 * Discount add / edit screen JS
 */
var EDD_Discount = {

	init : function() {
		this.product_requirements();
	},

	product_requirements : function() {
		$('#edd-products').change(function() {
			var product_conditions = $( '#edd-discount-product-conditions' );

			if ( $( this ).val() ) {
				product_conditions.show();
			} else {
				product_conditions.hide();
			}
		});
	}
};

export default EDD_Discount;
