/* global Backbone, _ */

/**
 * State
 *
 * Leverages `Backbone.Model` and subsequently `Backbone.Events`
 * to easily track changes to top level state changes.
 *
 * @since 3.0
 *
 * @class State
 * @augments Backbone.Model
 */
export const State = Backbone.Model.extend(
	/** Lends State.prototype */ {
		/**
		 * @since 3.0
		 *
		 * @typedef {Object} State
		 */
		defaults: {
			isAdding: false,
			isFetching: false,
			hasQuantity: false,
			hasTax: false,
			items: [],
			adjustments: [],
		},

		/**
		 * Returns the current tax rate's country code.
		 *
		 * @since 3.0
		 *
		 * @return {string} Tax rate country code.
		 */
		getTaxCountry() {
			return false !== this.get( 'hasTax' )
				? this.get( 'hasTax' ).country
				: '';
		},

		/**
		 * Returns the current tax rate's region.
		 *
		 * @since 3.0
		 *
		 * @return {string} Tax rate region.
		 */
		getTaxRegion() {
			return false !== this.get( 'hasTax' )
				? this.get( 'hasTax' ).region
				: '';
		},

		/**
		 * Retrieves the Order subtotal.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order subtotal.
		 */
		getSubtotal() {
			let subtotal = 0;

			const items = this.get( 'items' );
			const adjustments = this.get( 'adjustments' );

			// Add all item subtotals.
			_.each( items.models, ( item ) => {
				return ( subtotal += +item.get( 'subtotal' ) );
			} );

			// Add or substract all adjustment subtotals.
			_.each( adjustments.models, ( adjustment ) => {
				if (
					[ 'discount', 'credit' ].includes(
						adjustment.get( 'type' )
					)
				) {
					return ( subtotal -= +adjustment.getTotal() );
				}

				return ( subtotal += +adjustment.getTotal() );
			} );

			return subtotal;
		},

		/**
		 * Retrieves the Order discount.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order discount.
		 */
		getDiscount() {
			let discount = 0;
			const adjustments = this.get( 'adjustments' ).getByType( 'discount' );

			adjustments.forEach( ( adjustment ) => {
				return discount += +adjustment.getTotal();
			} );

			return discount;
		},

		/**
		 * Retrieves the Order tax.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order tax.
		 */
		getTax() {
			let tax = 0;

			// Add all item taxes.
			_.each( this.get( 'items' ).models, ( item ) => {
				return ( tax += +item.get( 'tax' ) );
			} );

			return tax;
		},

		/**
		 * Retrieves the Order total.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order total.
		 */
		getTotal() {
			return this.getSubtotal() + this.getTax();
		},
	}
);
