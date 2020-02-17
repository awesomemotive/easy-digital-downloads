/* global wp, $ */

/**
 * Internal dependencies
 */
import {
	FormAddOrderItem,
	FormAddOrderDiscount,
} from './';

/**
 * Overview actions
 *
 * @since 3.0
 *
 * @class Actions
 * @augments wp.Backbone.View
 */
export const Actions = wp.Backbone.View.extend( /** Lends Actions.prototype */ {
	/**
	 * @since 3.0
	 */
	el: '#edd-order-overview-actions',

	/**
	 * @since 3.0
	 */
	events: {
		'click #add-item': 'onAddOrderItem',
		'click #add-discount': 'onAddOrderDiscount',
	},

	/**
	 * Renders the "Add Item" flow.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onAddOrderItem( e ) {
		e.preventDefault();

		new FormAddOrderItem( this.options )
			.openDialog()
			.render();
	},

	/**
	 * Renders the "Add Discount" flow.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onAddOrderDiscount( e ) {
		e.preventDefault();

		new FormAddOrderDiscount( this.options )
			.openDialog()
			.render();
	},

} );
