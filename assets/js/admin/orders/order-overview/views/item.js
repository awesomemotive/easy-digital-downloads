/* global wp */

/**
 * Internal dependencies
 */
import { Currency } from '@easy-digital-downloads/currency';

const currency = new Currency();

/**
 * Item
 *
 * @since 3.0
 *
 * @class Item
 * @augments wp.Backbone.View
 */
export const Item = wp.Backbone.View.extend( /** Lends Item.prototype */ {
	/**
	 * @since 3.0
	 */
	tagName: 'tr',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-item' ),

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

		return {
			...model.toJSON(),

			config: {
				...config.toJSON(),
			},

			amountCurrency: currency.format( this.model.get( 'amount' ) ),
			subtotalCurrency: currency.format( this.model.get( 'subtotal' ) ),
			taxCurrency: currency.format( this.model.get( 'tax' ) ),
			discountCurrency: currency.format( this.model.get( 'discount' ) ),
		};
	},

	/**
	 * Removes the current Item from Items.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onDelete( e ) {
		e.preventDefault();

		this.options.config.get( 'items' ).remove( this.model );
	},
} );
