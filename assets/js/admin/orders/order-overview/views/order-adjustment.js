/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	Base,
} from './';

import {
	Currency,
} from '@easy-digital-downloads/currency';

const currency = new Currency();

/**
 * OrderAdjustment
 *
 * @since 3.0
 *
 * @class OrderAdjustment
 * @augments wp.Backbone.View
 */
export const OrderAdjustment = Base.extend( /** Lends Adjustment.prototype */ {
	/**
	 * @since 3.0
	 */
	tagName: 'tr',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-adjustment' ),

	/**
	 * @since 3.0
	 */
	events: {
		'click .delete': 'onDelete',
	},

	/**
	 * Prepares data to be used in `render` method.
	 *
	 * @since 3.0
	 *
	 * @see wp.Backbone.View
	 * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-backbone.js
	 *
	 * @return {Object} The data for this view.
	 */
	prepare() {
		const {
			state,
		} = this.options;

		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === state.get( 'hasQuantity' )
			? 2 
			: 1;

		return {
			...Base.prototype.prepare.apply( this ),

			config: {
				colspan,
			},

			totalCurrency: currency.format( this.model.getTotal() ),
		};
	},

	/**
	 * Removes the current Adjustment from Adjustments.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onDelete( e ) {
		e.preventDefault();

		const {
			state,
		} = this.options;

		state.get( 'items' )
			.updateAmounts( {
				// Remove the current Discount when finding new amounts.
				discountIds: _.reject(
					state.get( 'adjustments' ).pluck( 'id' ),
					{
						id: this.model.get( 'id' ),
					}
				),
			} )
			.done( () => {
				state.get( 'adjustments' ).remove( this.model );
			} );
	},
} );
