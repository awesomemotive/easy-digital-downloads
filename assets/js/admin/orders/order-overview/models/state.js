/* global Backbone, _ */

/**
 * Internal dependencies
 */
import { Currency, NumberFormat } from '@easy-digital-downloads/currency';

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
			refunds: [],
			formatters: {
				currency: new Currency(),
				number: new NumberFormat(),
			},
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

			const { models: items } = this.get( 'items' );
			const { models: adjustments } = this.get( 'adjustments' );

			// Add all item subtotals.
			_.each( items, ( item ) => {
				subtotal += +item.get( 'subtotal' );
			} );

			// Add or substract all adjustment subtotals.
			_.each( adjustments, ( adjustment ) => {
				if (
					[ 'discount', 'credit' ].includes(
						adjustment.get( 'type' )
					)
				) {
					subtotal -= +adjustment.getAmount();
				} else {
					subtotal += +adjustment.getAmount();
				}
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
				return discount += +adjustment.getAmount();
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

		/**
		 * Determines if the state has a new, valid, tax rate.
		 *
		 * @since 3.0
		 *
		 * @return {bool} True if the rate has changed.
		 */
		hasNewTaxRate() {
			const hasTax = this.get( 'hasTax' );

			if ( false === hasTax ) {
				return false;
			}

			const prevHasTax = this.previous( 'hasTax' );

			return ! _.isEqual( hasTax, prevHasTax );
		},
	}
);
