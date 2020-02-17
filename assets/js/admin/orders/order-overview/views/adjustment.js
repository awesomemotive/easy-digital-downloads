/* global wp */

/**
 * Internal dependencies
 */
import { Currency } from '@easy-digital-downloads/currency';

const currency = new Currency();

/**
 * Adjustment
 *
 * @since 3.0
 *
 * @class Adjustment
 * @augments wp.Backbone.View
 */
export const Adjustment = wp.Backbone.View.extend( /** Lends Adjustment.prototype */ {
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
			config,
		} = options;

		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === config.get( 'hasQuantity' )
			? 2 
			: 1;

		return {
			...model.toJSON(),

			config: {
				...config.toJSON(),
				colspan,
			},

			totalCurrency: currency.format( this.model.get( 'total' ) ),
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

		this.options.config.get( 'adjustments' ).remove( this.model );
	},
} );
