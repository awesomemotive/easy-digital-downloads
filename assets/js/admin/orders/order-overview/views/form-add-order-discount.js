/* global _ */

/**
 * External dependencies
 */
import uuid from 'uuid-random';

/**
 * Internal dependencies
 */
import { Dialog, Base } from './';
import { OrderAdjustmentDiscount } from './../models';

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

		// Assign Collection from State.
		this.collection = state.get( 'adjustments' );

		// Create a fresh `OrderAdjustmentDiscount` to be added.
		this.model = new OrderAdjustmentDiscount( {
			id: uuid(),
			_selected: false,

			state,
		} );

		// Listen for events.
		this.listenTo( this.model, 'change', this.render );
		this.listenTo( this.collection, 'add', this.closeDialog );
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

		const _isDuplicate = undefined !== state.get( 'adjustments' ).findWhere( {
			id: model.get( 'id' ),
		} );

		return {
			...Base.prototype.prepare.apply( this ),

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
		const {
			preventDefault,
			target: { selectedIndex, options },
		} = e;

		const { model } = this;

		preventDefault();

		const discount = options[ selectedIndex ];
		const adjustment = discount.dataset;

		if ( '' === discount.value ) {
			return model.set( OrderAdjustmentDiscount.prototype.defaults );
		}

		// Deselect all other items.
		// @todo Find a better way to manage selection.
		_.each( this.collection.models, ( d ) =>
			d.set(
				{
					_selected: false,
				},
				{
					silent: true,
				}
			)
		);

		// Update Order Adjustment.
		model.set( {
			typeId: parseInt( discount.value ),
			description: adjustment.code,

			_selected: true,
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

		this.collection.add( this.model );
	},
} );
