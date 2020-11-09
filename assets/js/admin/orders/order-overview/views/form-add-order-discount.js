/* global _ */

/**
 * External dependencies
 */
import uuid from 'uuid-random';

/**
 * Internal dependencies
 */
import { Dialog } from './dialog.js';
import { Base } from './base.js';
import { OrderAdjustmentDiscount } from './../models/order-adjustment-discount.js';

/**
 * "Add Discount" view
 *
 * @since 3.0
 *
 * @class FormAddOrderDiscount
 * @augments wp.Backbone.View
 */
export const FormAddOrderDiscount = Dialog.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-admin-order-add-discount-dialog',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-form-add-order-discount' ),

	/**
	 * @since 3.0
	 */
	events: {
		'submit form': 'onAdd',

		'change #discount': 'onChangeDiscount',
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
		Dialog.prototype.initialize.apply( this, arguments );

		const { state } = this.options;

		// Create a fresh `OrderAdjustmentDiscount` to be added.
		this.model = new OrderAdjustmentDiscount( {
			id: uuid(),
			typeId: uuid(),

			state,
		} );

		// Listen for events.
		this.listenTo( this.model, 'change', this.render );
		this.listenTo( state.get( 'adjustments' ), 'add', this.closeDialog );
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

		const _isDuplicate = state.get( 'adjustments' ).has( model );

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			_isDuplicate,
		};
	},

	/**
	 * Updates the `OrderDiscounts` when the Discount changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChangeDiscount( e ) {
		const { target: { selectedIndex, options } } = e;
		const { model } = this;

		e.preventDefault();

		const discount = options[ selectedIndex ];
		const adjustment = discount.dataset;

		if ( '' === discount.value ) {
			return model.set( OrderAdjustmentDiscount.prototype.defaults );
		}

		model.set( {
			typeId: parseInt( discount.value ),
			description: adjustment.code,
		} );
	},

	/**
	 * Adds an `OrderAdjustmentDiscount` to `OrderAdjustments`.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Submit event.
	 */
	onAdd( e ) {
		e.preventDefault();

		const { model, options } = this;
		const { state } = options;

		state.set( 'isFetching', true );

		const items = state.get( 'items' );
		const adjustments = state.get( 'adjustments' );

		// Add to collection but do not alert.
		adjustments.add( model, {
			silent: true,
		} );

		// Update all amounts with new item and alert when done.
		items
			.updateAmounts()
			.done( () => {
				// Stop listening to the model in this view.
				this.stopListening( model );

				// Alert of succesful addition.
				adjustments.trigger( 'add', model );

				// Clear fetching.
				state.set( 'isFetching', false ) ;
			} );
	},
} );
