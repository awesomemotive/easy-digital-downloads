/* global Backbone, $ */

/**
 * Internal dependencies
 */
import {
	OrderItem,
} from './../models';

/**
 * Collection of `OrderItem`s.
 *
 * @since 3.0
 *
 * @class OrderItems
 * @augments Backbone.Collection
 */
export const OrderItems = Backbone.Collection.extend( /** @lends Items.prototype */ {

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
	 * @param {Array} args.itemIds List of current `OrderItems`s.
	 * @param {Array} args.discountIds List of `OrderAdjustmentDiscount`s to calculate amounts against.
	 * @return {$.promise} A jQuery promise representing zero or more requests.
	 */
	updateAmounts( args ) {
		const {
			options,
			models,
		} = this;

		const {
			state,
		} = options;

		const items = state.get( 'items' );
		const adjustments = state.get( 'adjustments' );

		const defaults = {
			country: state.getTaxCountry(),
			region: state.getTaxRegion(),
			itemIds: items.pluck( 'id' ),
			discountIds: adjustments.pluck( 'id' ),
		};

		// Keep track of all jQuery Promises.
		const promises = [];

		// Update each `Item`'s amounts.
		_.each( items.models, ( item ) => {
			const amounts = item.getAmounts( {
				...defaults,
				...args,
			} );

			// Update individual `Item` with new amounts.
			amounts.done( ( response ) => item.set( response ) );

			// Track jQuery Promise.
			promises.push( amounts );
		} );

		return $.when.apply( $, promises );
	},
} );
