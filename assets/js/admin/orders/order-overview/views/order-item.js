/**
 * Internal dependencies
 */
import { Base, CopyDownloadLink } from './';
import { Currency, NumberFormat } from '@easy-digital-downloads/currency';

const currency = new Currency();
const number = new NumberFormat();

/**
 * OrderItem
 *
 * @since 3.0
 *
 * @class OrderItem
 * @augments wp.Backbone.View
 */
export const OrderItem = Base.extend( {
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
		'click .copy-download-link': 'onCopyDownloadLink',
	},

	/**
	 * "Order Item" view.
	 *
	 * @since 3.0
	 *
	 * @constructs OrderItem
	 * @augments Base
	 */
	initialize() {
		// Listen for events.
		this.listenTo( this.model, 'change', this.render );
	},

	/**
	 * Prepares data to be used in `render` method.
	 *
	 * @since 3.0
	 *
	 * @see wp.Backbone.View
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-backbone.js
	 *
	 * @return {Object} The data for this view.
	 */
	prepare() {
		const { model } = this;

		const discountTotal = model.getDiscountTotal();

		return {
			...Base.prototype.prepare.apply( this ),

			discount: discountTotal,
			amountCurrency: currency.format( model.get( 'amount' ) ),
			subtotalCurrency: currency.format( model.get( 'subtotal' ) ),
			taxCurrency: currency.format( model.get( 'tax' ) ),
			discountCurrency: currency.format( discountTotal ),
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

		const { model, options } = this;
		const { state } = options;

		// Remove OrderItem.
		state.get( 'items' ).remove( model );

		// Update remaining OrderItem amounts.
		state.get( 'items' ).updateAmounts();
	},

	/**
	 * Opens a Dialog that fetches Download File URLs.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onCopyDownloadLink( e ) {
		e.preventDefault();

		const { options, model } = this;

		new CopyDownloadLink( {
			orderId: model.get( 'orderId' ),
			productId: model.get( 'productId' ),
			priceId: model.get( 'priceId' ),
		} )
			.openDialog()
			.render();
	},
} );
