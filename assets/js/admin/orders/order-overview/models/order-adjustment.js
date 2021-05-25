/* global Backbone */

/**
 * OrderAdjustment
 *
 * @since 3.0
 *
 * @class OrderAdjustment
 * @augments Backbone.Model
 */
export const OrderAdjustment = Backbone.Model.extend( {
	/**
	 * @since 3.0
	 *
	 * @typedef {Object} OrderAdjustment
	 */
	defaults: {
		id: 0,
		objectId: 0,
		objectType: '',
		typeId: 0,
		type: '',
		description: '',
		subtotal: 0,
		tax: 0,
		total: 0,
		dateCreated: '',
		dateModified: '',
		uuid: '',
	},

	/**
	 * Returns the `OrderAdjustment` amount.
	 *
	 * Separate from subtotal or total calculation so `OrderAdjustmentDiscount`
	 * can be calculated independently.
	 *
	 * @see OrderAdjustmentDiscount.prototype.getAmount()
	 *
	 * @since 3.0
	 */
	getAmount() {
		return this.get( 'subtotal' );
	},

	/**
	 * Retrieves the `OrderAdjustment` tax.
	 *
	 * @since 3.0.0
	 *
	 * @return {number} Total amount.
	 */
	getTax() {
		return this.get( 'tax' );
	},

	/**
	 * Returns the `OrderAdjustment` total.
	 *
	 * @since 3.0
	 */
	getTotal() {
		const state = this.get( 'state' );

		if ( true === state.hasInclusiveTax() ) {
			return this.get( 'subtotal' );
		}

		return this.get( 'subtotal' ) + this.get( 'tax' );
	},

	/**
	 * Recalculates the tax amount based on the current tax rate.
	 *
	 * @since 3.0.0
	 */
	updateTax() {
		const state = this.get( 'state' );
		const hasTax = state.get( 'hasTax' );

		if (
			'none' === hasTax ||
			'' === hasTax.country ||
			'' === hasTax.rate
		) {
			return;
		}

		const { number } = state.get( 'formatters' );
		const taxRate = hasTax.rate / 100;
		const adjustments = state.get( 'adjustments' ).getByType( 'fee' );

		adjustments.forEach( ( adjustment ) => {
			if ( false === adjustment.get( 'isTaxable' ) ) {
				return;
			}

			const taxableAmount = adjustment.getAmount();
			const taxAmount = number.unformat( taxableAmount * taxRate );

			adjustment.set( 'tax', taxAmount );
		} );
	}
} );
