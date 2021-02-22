/**
 * External dependencies
 */
import uuid from 'uuid-random';

/**
 * Internal dependencies
 */
import { Base } from './base.js';
import { Dialog } from './dialog.js';
import { OrderAdjustment } from './../models/order-adjustment.js';
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
			'change #object_type': 'onChangeObjectType',
			'change [name="type"]': 'onChangeType',
			'keyup #amount': 'onChangeAmount',
			'keyup #description': 'onChangeDescription',

			'submit form': 'onAdd',
		} );

		const { state } = this.options;

		// Create a model `OrderAdjustment` to be added.
		this.model = new OrderAdjustment( {
			id: uuid(),
			objectId: uuid(),
			typeId: uuid(),
			objectType: 'order',
			type: 'fee',
			amountManual: '',

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

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			// Pass existing OrderItems so we can apply a fee at OrderItem level.
			orderItems: state.get( 'items' ).models.map( ( item ) => ( {
				id: item.get( 'id' ),
				productName: item.get( 'productName' ),
			} ) ),
		};
	},

	/**
	 * Updates the `OrderAdjustment` when the Object Type changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event
	 */
	onChangeObjectType( e ) {
		const {
			target: { options, selectedIndex },
		} = e;

		const selected = options[ selectedIndex ];

		const objectType = selected.value;
		let objectId = this.model.get( 'objectId' );

		// Apply to a specific `OrderItem`.
		if ( 'order_item' === objectType ) {
			objectId = selected.dataset.orderItemId;

			this.model.set( {
				objectId,
				objectType,
			} );

			// Apply to the whole order.
		} else {
			this.model.set( {
				objectType,
				objectId,
			} );
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
		const { target } = e;

		e.preventDefault();

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

		const { model, options } = this;
		const { state } = options;

		const adjustments = state.get( 'adjustments' );
		const items = state.get( 'items' );

		// Add at `OrderItem` level if necessary.
		if ( 'order_item' === model.get( 'objectType' ) ) {
			const orderItem = items.findWhere( {
				id: model.get( 'objectId' ),
			} );

			orderItem.get( 'adjustments' ).add( model );
			// Adding to the Collection doesn't bubble up a change event.
			orderItem.trigger( 'change' );
			model.set( 'objectType', 'order_item' );
		} else {

			// Add to `Order` level.
			model.set( 'objectType', 'order' );
		}

		adjustments.add( model );

		// Stop listening to the model in this view.
		this.stopListening( model );
	},
} );
