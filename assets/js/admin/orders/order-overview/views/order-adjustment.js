/* global wp */

/**
 * Internal dependencies
 */
import { Currency } from '@easy-digital-downloads/currency';

const currency = new Currency();

/**
 * OrderAdjustment
 *
 * @since 3.0
 *
 * @class OrderAdjustment
 * @augments wp.Backbone.View
 */
export const OrderAdjustment = wp.Backbone.View.extend( /** Lends Adjustment.prototype */ {
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
			options,
			model,
		} = this;

		const {
			state,
		} = options;

		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === state.get( 'hasQuantity' )
			? 2 
			: 1;

		return {
			...model.toJSON(),

			state: {
				...state.toJSON(),
			},
			config: {
				colspan,
			},

			totalCurrency: currency.format( model.getTotal() ),
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

		this.options.state.get( 'adjustments' ).remove( this.model );
	},
} );
