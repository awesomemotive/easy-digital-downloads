/**
 * External dependencies
 */
import uuid from 'uuid-random';

/**
 * Internal dependencies
 */
import { Base } from './base.js';
import { Dialog } from './dialog.js';
import { OrderItem } from './../models/order-item.js';
import { NumberFormat } from '@easy-digital-downloads/currency';

const number = new NumberFormat();

/**
 * "Add Item" view
 *
 * @since 3.0
 *
 * @class FormAddOrderItem
 * @augments Dialog
 */
export const FormAddOrderItem = Dialog.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-admin-order-add-item-dialog',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-form-add-order-item' ),

	/**
	 * "Add Item" view.
	 *
	 * @since 3.0
	 *
	 * @constructs FormAddOrderItem
	 * @augments Base
	 */
	initialize() {
		Dialog.prototype.initialize.apply( this, arguments );

		// Delegate additional events.
		this.addEvents( {
			'change #download': 'onChangeDownload',
			'change #quantity': 'onChangeQuantity',
			'change #auto-calculate': 'onAutoCalculateToggle',

			'keyup #amount': 'onChangeAmount',
			'keyup #tax': 'onChangeTax',
			'keyup #subtotal': 'onChangeSubtotal',

			'click #set-address': 'onSetAddress',

			'submit form': 'onAdd',
		} );

		const { state } = this.options;
		const id = uuid();

		// Create a fresh `OrderItem` to be added.
		this.model = new OrderItem( {
			id,
			orderId: id,
			_isAdjustingManually: false,

			state,
		} );

		// Listen for events.
		this.listenTo( this.model, 'change', this.render );
		this.listenTo( state, 'change:hasTax', this.render );
		this.listenTo( state.get( 'items' ), 'add', this.closeDialog );
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

		const quantity = model.get( 'quantity' );

		let amount = number.format( model.get( 'amount' ) * quantity );
		let tax = number.format( model.get( 'tax' ) * quantity );
		let subtotal = number.format( model.get( 'subtotal' ) * quantity );

		if ( true === model.get( '_isAdjustingManually' ) ) {
			amount = model.get( 'amountManual' );
			tax = model.get( 'taxManual' );
			subtotal = model.get( 'subtotalManual' );
		}

		const _isDuplicate = state.get( 'items' ).has( model );
		const _isAdjustingManually = model.get( '_isAdjustingManually' );

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			amountManual: amount,
			taxManual: tax,
			subtotalManual: subtotal,

			_isAdjustingManually,
			_isDuplicate,
		};
	},

	/**
	 * Updates the OrderItem when the Download changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event for Download selector.
	 */
	onChangeDownload( e ) {
		const {
			target: { options, selectedIndex },
		} = e;

		const { state } = this.options;

		// Find the selected Download.
		const selected = options[ selectedIndex ];

		// Set ID and Price ID.
		let productId = selected.value;
		let priceId = 0;

		const parts = productId.split( '_' );

		productId = parseInt( parts[ 0 ] );

		if ( parts[ 1 ] ) {
			priceId = parseInt( parts[ 1 ] );
		}

		// Update basic attributes.
		this.model.set( {
			productId,
			priceId,
			productName: selected.text,
		} );

		// Update amount attributes.
		this.model
			.getAmounts( {
				country: state.getTaxCountry(),
				region: state.getTaxRegion(),
				items: state.get( 'items' ),
				adjustments: state.get( 'adjustments' ),
			} )
			.then( ( response ) => {
				const { amount, tax, subtotal, total } = response;

				this.model.set( {
					amount,
					tax,
					subtotal,
					total,

					amountManual: number.format( amount ),
					taxManual: number.format( tax ),
					subtotalManual: number.format( subtotal ),
				} );
			} );
	},

	/**
	 * Updates the `OrderItem`'s when the Quantity changes.
	 *
	 * @since 3.0
	 * @todo Validate.
	 *
	 * @param {Object} e Change event.
	 */
	onChangeQuantity( e ) {
		this.model.set( 'quantity', parseInt( e.target.value ) );
	},

	/**
	 * Updates the `OrderItem`'s when the manually managed Amount changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChangeAmount( e ) {
		this.model.set( 'amountManual', e.target.value );
	},

	/**
	 * Updates the `OrderItem`'s when the manually managed Tax changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChangeTax( e ) {
		this.model.set( 'taxManual', e.target.value );
	},

	/**
	 * Updates the `OrderItem`'s when the manually managed Subtotal changes.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChangeSubtotal( e ) {
		this.model.set( 'subtotalManual', e.target.value );
	},

	/**
	 * Toggles manual amount adjustments.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onAutoCalculateToggle( e ) {
		e.preventDefault();

		this.model.set( {
			_isAdjustingManually: ! e.target.checked,
		} );
	},

	/**
	 * Closes dialog and opens "Order Details - Address" section.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onSetAddress( e ) {
		e.preventDefault();

		this.closeDialog();

		const button = $( '[href="#edd_general_address"]' );

		if ( ! button ) {
			return;
		}

		button.trigger( 'click' );

		$( '#edd_order_address_country' ).focus();
	},

	/**
	 * Adds an `OrderItem` to `OrderItems`.
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

		// Use manual amounts if adjusting manually.
		if ( true === model.get( '_isAdjustingManually' ) ) {
			model.set( {
				amount: number.unformat( model.get( 'amountManual' ) ),
				tax: number.unformat( model.get( 'taxManual' ) ),
				subtotal: number.unformat( model.get( 'subtotalManual' ) ),
			} );

			// Duplicate base amounts by the quantity set.
		} else {
			const quantity = model.get( 'quantity' );

			model.set( {
				tax: model.get( 'tax' ) * quantity,
				subtotal: model.get( 'subtotal' ) * quantity,
			} );
		}

		const items = state.get( 'items' );

		// Add to collection but do not alert.
		items.add( model, {
			silent: true,
		} );

		// Update all amounts with new item and alert when done.
		items
			.updateAmounts()
			.done( () => items.trigger( 'add', model ) )
			.done( () => state.set( 'isFetching', false ) );
	},
} );
