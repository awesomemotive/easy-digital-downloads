/* global Backbone, _ */

/**
 * Internal dependencies
 */
import { Currency, NumberFormat } from '@easydigitaldownloads/currency';

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
			const { models: items } = this.get( 'items' );

			// Use stored value if the record has already been created.
			if ( false === this.get( 'isAdding' ) ) {
				// Use the `discount` column for each OrderItem. OrderAdjustments do
				// not store the per-item discount amount.
				//
				// Used when viewing a Refund to find the actual amount refunded per item.
				//
				// A $50.00 OrderItem that has a $12.50 discount applied by splitting two
				// flat rate discounts of $5 and $10 would incorrectly try to apply $15.00
				// discount if the attached OrderAdjustments were summed.
				return items.reduce(
					( amount, item ) => {
						return amount += ( item.get( 'subtotal' ) - item.get( 'discount' ) );
					},
					0
				);
			}

			// Add all subtotals.
			const subtotal = items.reduce(
				( amount, item ) => {
					return amount += +item.get( 'subtotal' );
				},
				0
			);

			const { models: adjustments } = this.get( 'adjustments' );

			// Add or substract all adjustment subtotals.
			return adjustments.reduce(
				( amount, adjustment ) => {
					if (
						[ 'discount', 'credit' ].includes(
							adjustment.get( 'type' )
						)
					) {
						return amount -= +adjustment.getAmount();
					} else {
						return amount += +adjustment.getAmount();
					}
				},
				subtotal
			);
		},

		/**
		 * Retrieves the Order discount.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order discount.
		 */
		getDiscount() {
			// Use stored value if the record has already been created.
			if ( false === this.get( 'isAdding' ) ) {
				return this.get( 'order' ).discount;
			}

			const adjustments = this.get( 'adjustments' ).getByType( 'discount' );

			return adjustments.reduce(
				( amount, adjustment ) => {
					return amount += +adjustment.getAmount();
				},
				0
			);
		},

		/**
		 * Retrieves the Order tax.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order tax.
		 */
		getTax() {
			// Use stored value if the record has already been created.
			if ( false === this.get( 'isAdding' ) ) {
				return this.get( 'order' ).tax;
			}

			const items = this.get( 'items' ).models;

			return items.reduce(
				( amount, item ) => {
					return amount += +item.get( 'tax' );
				},
				0
			);
		},

		/**
		 * Retrieves the Order total.
		 *
		 * @since 3.0
		 *
		 * @return {number} Order total.
		 */
		getTotal() {
			// Use stored value if the record has already been created.
			if ( false === this.get( 'isAdding' ) ) {
				return this.get( 'order' ).total;
			}

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
