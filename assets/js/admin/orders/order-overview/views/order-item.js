/* global wp */

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
 * OrderItem
 *
 * @since 3.0
 *
 * @class OrderItem
 * @augments wp.Backbone.View
 */
export const OrderItem = Base.extend( /** Lends OrderItem.prototype */ {
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
	 * @since 3.0
	 */
	initialize() {
		this.listenTo( this.model, 'change', this.render );
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

		return {
			...Base.prototype.prepare.apply( this ),

			amountCurrency: currency.format( model.get( 'amount' ) ),
			subtotalCurrency: currency.format( model.get( 'subtotal' ) ),
			taxCurrency: currency.format( model.get( 'tax' ) ),
			discountCurrency: currency.format( model.get( 'discount' ) ),
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

		this.options.state.get( 'items' ).remove( this.model );
	},
} );
