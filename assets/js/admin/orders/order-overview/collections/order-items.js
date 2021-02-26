/* global Backbone, $, _ */

/**
 * External dependencies
 */
import uuid from 'uuid-random';

/**
 * Internal dependencies
 */
import { OrderAdjustments } from './../collections/order-adjustments.js';
import { OrderAdjustmentDiscount } from './../models/order-adjustment-discount.js';
import { OrderItem } from './../models/order-item.js';
import { NumberFormat } from '@easydigitaldownloads/currency';

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
	 * Determines if `OrderItems` contains a specific `OrderItem`.
	 *
	 * Uses the `OrderItem`s Product ID and Price ID to create a unique
	 * value to check against.
	 *
	 * @since 3.0
	 *
	 * @param {OrderItem} model Model to look for.
	 * @return {bool} True if the Collection contains the Model.
	 */
	has( model ) {
		const duplicates = this.filter( ( item ) => {
			const itemId =
				item.get( 'productId' ) + '_' + item.get( 'priceId' );
			const modelId =
				model.get( 'productId' ) + '_' + model.get( 'priceId' );

			return itemId === modelId;
		} );

		return duplicates.length > 0;
	},

	/**
	 * Updates the amounts for all current `OrderItem`s.
	 *
	 * @since 3.0
	 *
	 * @return {$.promise} A jQuery promise representing zero or more requests.
	 */
	updateAmounts() {
		const { options } = this;
		const { state } = options;

		const items = state.get( 'items' );
		const adjustments = state.get( 'adjustments' );

		const args = {
			country: state.getTaxCountry(),
			region: state.getTaxRegion(),
			products: items.map( ( item ) => ( {
				id: item.get( 'productId' ),
				quantity: item.get( 'quantity' ),
				options: {
					price_id: item.get( 'priceId' ),
				}
			} ) ),
			discountIds: adjustments.pluck( 'typeId' ),
		};

		// Keep track of all jQuery Promises.
		const promises = [];

		// Find each `OrderItem`'s amounts.
		items.models.forEach( ( item ) => {
			const getItemAmounts = item.getAmounts( args );

			getItemAmounts
				// Update `OrderItem`-level Adjustments.
				.done( ( { adjustments } ) => {
					// Map returned Discounts to `OrderAdjustmentDiscount`.
					const orderItemDiscounts = adjustments.map( ( adjustment ) => {
						return new OrderAdjustmentDiscount( {
							...adjustment,
							id: uuid(),
							objectId: item.get( 'id' ),
						} );
					} );

					// Gather existing `fee` and `credit` `OrderItem`-level Adjustments.
					const orderItemAdjustments = item.get( 'adjustments' ).filter( ( adjustment ) => {
						return [ 'fee', 'credit' ].includes( adjustment.type );
					} );

					// Reset `OrderAdjustments` collection with new data.
					item.set( 'adjustments', new OrderAdjustments( [
						...orderItemDiscounts,
						...orderItemAdjustments,
					] ) );
				} )
				// Update individual `OrderItem`s and `OrderAdjustment`s with new amounts.
				.done( ( response ) => item.setAmounts( response ) );

			// Track jQuery Promise.
			promises.push( getItemAmounts );
		} );

		return $.when.apply( $, promises );
	},
} );
