/* global Backbone */

/**
 * Model a tax rate.
 */
const TaxRate = Backbone.Model.extend( {
	defaults: {
		id: '',
		country: '',
		region: '',
		global: true,
		amount: 0,
		status: 'active',
		unsaved: false,
		selected: false,
	},

	/**
	 * Format a rate amount (adds a %)
	 *
	 * @todo This should support dynamic decimal types.
	 */
	formattedAmount: function() {
		let amount = 0;

		if ( this.get( 'amount' ) ) {
			amount = parseFloat( this.get( 'amount' ) ).toFixed( 2 );
		}

		return `${ amount }%`;
	},
} );

export default TaxRate;
