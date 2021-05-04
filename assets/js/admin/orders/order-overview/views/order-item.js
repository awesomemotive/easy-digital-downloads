/**
 * Internal dependencies
 */
import { Base } from './base.js';
import { CopyDownloadLink } from './copy-download-link.js';

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
		const { model, options } = this;
		const { state } = options;

		const { currency, number } = state.get( 'formatters' );

		const discountAmount = model.getDiscountAmount();
		const isAdjustingManually = model.get( '_isAdjustingManually' );

		const tax = model.getTax();

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			discount: discountAmount,
			amountCurrency: currency.format( number.absint( model.get( 'amount' ) ) ),
			subtotalCurrency: currency.format( number.absint( model.get( 'subtotal' ) ) ),
			tax,
			taxCurrency: currency.format( number.absint( tax ) ),
			total: model.getTotal(),

			config: {
				isAdjustingManually,
			},

			adjustments: model.get( 'adjustments' ).toJSON(),
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
