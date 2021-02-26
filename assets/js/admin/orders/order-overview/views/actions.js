/**
 * Internal dependencies
 */
import { edd_attach_tooltips as setupTooltips } from './../../../components/tooltips';
import { FormAddOrderItem } from './form-add-order-item.js';
import { FormAddOrderDiscount } from './form-add-order-discount.js';
import { FormAddOrderAdjustment } from './form-add-order-adjustment.js';

/**
 * Actions
 *
 * @since 3.0
 *
 * @class Actions
 * @augments wp.Backbone.View
 */
export const Actions = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-order-overview-actions',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-actions' ),

	/**
	 * @since 3.0
	 */
	events: {
		'click #add-item': 'onAddOrderItem',
		'click #add-discount': 'onAddOrderDiscount',
		'click #add-adjustment': 'onAddOrderAdjustment',
	},

	/**
	 * Ensures tooltips can be used after render.
	 *
	 * @since 3.0
	 *
	 * @return {Object}
	 */
	render() {
		wp.Backbone.View.prototype.render.apply( this, arguments );

		// Setup Tooltips after render.
		setupTooltips( $( '.edd-help-tip' ) );

		return this;
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

		new FormAddOrderItem( this.options ).openDialog().render();
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

		new FormAddOrderDiscount( this.options ).openDialog().render();
	},

	/**
	 * Renders the "Add Adjustment" flow.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onAddOrderAdjustment( e ) {
		e.preventDefault();

		new FormAddOrderAdjustment( this.options ).openDialog().render();
	},
} );
