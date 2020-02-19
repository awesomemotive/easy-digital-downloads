/* global Backbone */

/**
 * Internal dependencies
 */
import {
	OrderItem,
} from './../models';

/**
 * Items
 *
 * Collection for Order Items.
 *
 * @since 3.0
 *
 * @class Items
 * @augments Backbone.Collection
 */
export const Items = Backbone.Collection.extend( /** @lends Items.prototype */ {

	/**
	 * @since 3.0
	 */
	model: OrderItem,

	/**
	 * @since 3.0
	 */
	initialize( models, options ) {
		this.options = options;
	},

	/**
	 * @since 3.0
	 */
	updateAmounts( args ) {
		const {
			state,
		} = this.options;

		const defaults = {
			country: state.getTaxCountry(),
			region: state.getTaxRegion(),
			itemIds: state.get( 'items' ).pluck( 'id' ),
			discountIds: state.get( 'adjustments' ).pluck( 'id' ),
		};

		const promises = [];

		// Calculate new Item totals.
		for ( const atIndex in this.models ) {
			const item = this.at( atIndex );
			const amounts = item.getAmounts( {
				...defaults,
				...args,
			} );

			amounts.done( ( response ) => item.set( response ) );
			promises.push( amounts );
		}

		return $.when.apply( $, promises );
	},
} );
