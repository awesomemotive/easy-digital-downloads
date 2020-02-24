/* global Backbone, $, _ */

/**
 * Internal dependencies
 */
import { OrderItem } from './../models';
import { NumberFormat } from '@easy-digital-downloads/currency';

const number = new NumberFormat();

/**
 * Collection of `OrderItem`s.
 *
 * @since 3.0
 *
 * @class OrderItems
 * @augments Backbone.Collection
 */
export const OrderItems = Backbone.Collection.extend( {
	/**
	 * @since 3.0
	 *
	 * @type {OrderItem}
	 */
	model: OrderItem,

	/**
	 * Ensures `OrderItems` has access to the current state through a similar
	 * interface as Views. BackBone.Collection does not automatically set
	 * passed options as a property.
	 *
	 * @since 3.0
	 *
	 * @param {null|Array} models List of Models.
	 * @param {Object} options Collection options.
	 */
	preinitialize( models, options ) {
		this.options = options;
	},

	/**
	 * Updates the amounts for all current `OrderItem`s.
	 *
	 * @since 3.0
	 *
	 * @param {Object} args Configuration to calculate individual `OrderItem` amounts against.
	 * @param {string} args.country Country code to determine tax rate.
	 * @param {string} args.region Region to determine tax rate.
	 * @param {Array} args.productIds List of current products added to the Order.
	 * @param {Array} args.discountIds List of `OrderAdjustmentDiscount`s to calculate amounts against.
	 * @return {$.promise} A jQuery promise representing zero or more requests.
	 */
	updateAmounts( args ) {
		const { options } = this;
		const { state } = options;

		const items = state.get( 'items' );
		const adjustments = state.get( 'adjustments' );

		const defaults = {
			country: state.getTaxCountry(),
			region: state.getTaxRegion(),
			productIds: items.pluck( 'productId' ),
			discountIds: adjustments.pluck( 'typeId' ),
		};

		// Keep track of all jQuery Promises.
		const promises = [];

		// Find each `OrderItem`'s amounts.
		_.each( items.models, ( item ) => {
			const getItemAmounts = item.getAmounts( {
				...defaults,
				...args,
			} );

			getItemAmounts
				// Update individual `OrderItem`s and `OrderAdjustment`s with new amounts.
				.done( ( response ) => item.setAmounts( response ) )
				// Track how much of each Discount is applied to an `OrderItem`.
				// There is not currently API support for `OrderItem`-level `OrderAdjustment`s.
				.done( ( { _discounts } ) => item.set( {
					_discounts,
				} ) );

			// Track jQuery Promise.
			promises.push( getItemAmounts );
		} );

		// Return list of jQuery Promises.
		return $.when.apply( $, promises );
	},
} );
