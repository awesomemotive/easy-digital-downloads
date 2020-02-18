/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	Dialog,
} from './';
import {
	OrderAdjustment,
} from './../models';

/**
 * "Add Adjustment" view
 *
 * @since 3.0
 *
 * @class FormAddOrderAdjustment
 * @augments wp.Backbone.View
 */
export const FormAddOrderAdjustment = Dialog.extend( /** Lends FormAddItem.prototype */ {
	/**
	 * @since 3.0
	 */
	el: '#edd-admin-order-add-adjustment-dialog',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-form-add-order-adjustment' ),

	/**
	 * @since 3.0
	 */
	events: {
		'submit form': 'onAdd',
	},

	/**
	 * "Add Discount" view.
	 *
	 * @since 3.0
	 *
	 * @constructs FormAddOrderAdjustment
	 * @augments wp.Backbone.View
	 */
	initialize() {
		// Assign collection from State.
		this.collection = this.options.state.get( 'adjustments' );

		Dialog.prototype.initialize.apply( this, arguments );

		// Create a fresh OrderAdjustment to be added.
		this.adjustment = new OrderAdjustment();
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
			adjustment,
		} = this;

		return {
			...adjustment.toJSON(),
		};
	},

	/**
	 * @since 3.0
	 *
	 * @param {Object} e Submit event.
	 */
	onAdd( e ) {
		e.preventDefault();

		this.collection.add( this.adjustment );
	}
} );
