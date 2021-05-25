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
			// Use stored value if the record has already been created.
			if ( false === this.get( 'isAdding' ) ) {
				return this.get( 'order' ).subtotal;
			}

			const { models: items } = this.get( 'items' );

			return items.reduce(
				( amount, item ) => {
					return amount += +item.get( 'subtotal' );
				},
				0
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
			const adjustments = this.get( 'adjustments' ).getByType( 'fee' );

			return [ ...items, ...adjustments ].reduce(
				( amount, item ) => {
					return amount += +item.getTax();
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

			// Calculate all adjustments that affect the total.
			const { models: adjustments } = this.get( 'adjustments' );

			const adjustedSubtotal = adjustments.reduce(
				( amount, adjustment ) => {
					if (
						[ 'discount', 'credit' ].includes(
							adjustment.get( 'type' )
						)
					) {
						return amount -= +adjustment.getAmount();
					} else {
						return amount += +adjustment.get( 'subtotal' );
					}
				},
				this.getSubtotal()
			);

			if ( true === this.hasInclusiveTax() ) {
				return adjustedSubtotal;
			}

			return adjustedSubtotal + this.getTax();
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

		/**
		 * Determines if the state has prices entered inclusive of tax.
		 *
		 * @since 3.0
		 *
		 * @returns {bool} True if prices are entered inclusive of tax.
		 */
		hasInclusiveTax() {
			return this.get( 'hasTax' ).inclusive;
		}
	}
);
