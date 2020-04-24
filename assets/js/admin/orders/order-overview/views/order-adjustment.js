/* global _ */

/**
 * Internal dependencies
 */
import { Base } from './base.js';
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

		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === state.get( 'hasQuantity' ) ? 2 : 1;

		let orderItem;

		if ( 'order_item' === model.get( 'object_type' ) ) {
			orderItem = _.first( state.get( 'items' ).filter( ( item ) => {
				return undefined !== item.get( 'adjustments' ).findWhere( {
					objectId: item.get( 'id' ),
				} );
			} ) );
		}

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			config: {
				colspan,
			},

			total: model.getAmount(),
			subtotal: model.getAmount(),
			orderItem: orderItem ? orderItem.toJSON() : false,
			totalCurrency: currency.format( model.getAmount() ),
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
