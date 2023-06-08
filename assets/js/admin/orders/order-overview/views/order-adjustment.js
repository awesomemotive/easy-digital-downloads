/* global _ */

/**
 * Internal dependencies
 */
import { Base } from './base.js';

/**
 * OrderAdjustment
 *
 * @since 3.0
 *
 * @class OrderAdjustment
 * @augments wp.Backbone.View
 */
export const OrderAdjustment = Base.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tr',

	/**
	 * @since 3.0
	 */
	className: 'is-expanded',

	/**
	 * @since 3.0
	 */
	events: {
		'click .delete': 'onDelete',
	},

	initialize() {
		Base.prototype.initialize.apply( this );

		// Set template depending on type.
		switch ( this.model.get( 'type' ) ) {
			case 'credit':
			case 'fee':
				this.template = wp.template( 'edd-admin-order-adjustment' );
				break;
			default:
				this.template = wp.template(
					'edd-admin-order-adjustment-discount'
				);
		}

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
		const { state } = this.options;

		const { currency, number } = state.get( 'formatters' );

		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === state.get( 'hasQuantity' ) ? 2 : 1;

		let orderItem;

		if ( 'order_item' === model.get( 'objectType' ) ) {
			orderItem = _.first( state.get( 'items' ).filter( ( item ) => {
				return undefined !== item.get( 'adjustments' ).findWhere( {
					objectId: model.get( 'objectId' ),
				} );
			} ) );
		}

		const subtotal = model.getAmount();
		const total = model.getTotal();

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			config: {
				colspan,
			},

			total,
			subtotal,
			orderItem: orderItem ? orderItem.toJSON() : false,
			totalCurrency: currency.format( total ),
			subtotalCurrency: currency.format( subtotal )
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

		const { state } = this.options;

		// Remove `OrderAdjustment`.
		state.get( 'adjustments' ).remove( this.model );

		// Update `OrderItem` amounts.
		state.get( 'items' ).updateAmounts();
	},
} );
