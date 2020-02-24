/**
 * External dependencies
 */
import uuid from 'uuid-random';

/**
 * Internal dependencies
 */
import { Base, Dialog } from './';
import { OrderAdjustment } from './../models';
import { NumberFormat } from '@easy-digital-downloads/currency';

const number = new NumberFormat();

/**
 * FormAddOrderAdjustment
 *
 * @since 3.0
 *
 * @class FormAddOrderAdjustment
 * @augments Dialog
 */
export const FormAddOrderAdjustment = Dialog.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-admin-order-add-adjustment-dialog',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-form-add-order-adjustment' ),

	/**
	 * "Add Adjustment" view.
	 *
	 * @since 3.0
	 *
	 * @constructs FormAddOrderAdjustment
	 * @augments Dialog
	 */
	initialize() {
		Dialog.prototype.initialize.apply( this, arguments );

		// Delegate additional events.
		this.addEvents( {
			'change [name="type"]': 'onChangeType',
			'keyup #amount': 'onChangeAmount',
			'keyup #description': 'onChangeDescription',

			'submit form': 'onAdd',
		} );

		const { state } = this.options;

		// Assign Collection from State.
		this.collection = state.get( 'adjustments' );

		// Create a model `OrderAdjustment` to be added.
		this.model = new OrderAdjustment( {
			id: uuid(),
			objectType: 'order',
			type: 'fee',
			amountManual: '',

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

		return {
			...Base.prototype.prepare.apply( this ),

			// Pass existing OrderItems so we can apply a fee at OrderItem level.
			orderItems: state.get( 'items' ).models.map( ( item ) => ( {
				id: item.get( 'id' ),
				productName: item.get( 'productName' ),
			} ) ),
		}
	},

	/**
	 * Updates the `OrderAdjustment` when the Type changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event
	 */
	onChangeType( e ) {
		this.model.set( 'type', e.target.value );
	},

	/**
	 * Updates the `OrderAdjustment` when the Description changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event
	 */
	onChangeDescription( e ) {
		this.model.set( 'description', e.target.value );
	},

	/**
	 * Updates the `OrderAdjustment` when the Amount changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event
	 */
	onChangeAmount( e ) {
		const { preventDefault, target } = e;

		preventDefault();

		const amountManual = target.value;
		const amountNumber = number.unformat( amountManual );

		this.model.set( {
			amountManual,
			subtotal: amountNumber,
			total: amountNumber,
		} );
	},

	/**
	 * Adds an `OrderAdjustment` to `OrderAdjustments`.
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
