/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	Dialog,
} from './';
import {
	OrderAdjustmentDiscount,
} from './../models';

/**
 * "Add Discount" view
 *
 * @since 3.0
 *
 * @class FormAddOrderDiscount
 * @augments wp.Backbone.View
 */
export const FormAddOrderDiscount = Dialog.extend( /** Lends FormAddItem.prototype */ {
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
		// Assign collection from State.
		this.collection = this.options.state.get( 'adjustments' );

		Dialog.prototype.initialize.apply( this, arguments );

		// Create a fresh OrderAdjustmentDiscount to be added.
		this.discount = new OrderAdjustmentDiscount();

		// Rerender when a Discount has changed.
		this.listenTo( this.discount, 'change', this.render );
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
			discount,
		} = this;

		return {
			...discount.toJSON(),
		};
	},

	/**
	 * @since 3.0
	 */
	render() {
		wp.Backbone.View.prototype.render.apply( this, arguments );

		// Reselect Discount.
		// @todo Use a separate view/model to manage this?
		if ( 0 !== this.discount.get( 'typeId' ) ) {
			this.$el
				.find( `#discount option[value="${ this.discount.get( 'typeId' ) }"]` )
				.prop( 'selected', true )

			this.$el
				.find( '#discount' )
				.focus();
		}

		return this;
	},

	/**
	 * Updates the OrderDiscounts's discount on change.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChangeDiscount( e ) {
		const {
			preventDefault,
			target: {
				selectedIndex,
				options,
			},
		} = e;

		preventDefault();

		const discount = options[ selectedIndex ];
		const adjustment = discount.dataset;

		if ( '' === discount.value ) {
			return this.discount.set( OrderAdjustmentDiscount.prototype.defaults );
		}

		// Update Order Adjustment.
		this.discount.set( {
			// Set ID so it is unique
			// @todo Investigate why idAttribute is not working on the model.
			id: parseInt( discount.value ),
			typeId: parseInt( discount.value ),
			description: adjustment.code,
		} );

		// Update Adjustment with known information.
		this.discount.get( 'adjustment' ).set( {
			...adjustment,
			productRequirements: _.without( adjustment.productRequirements.split( ',' ), '' ),
			productExclusions: _.without( adjustment.productExclusions.split( ',' ), '' ),
		} );
	},

	/**
	 * @since 3.0
	 *
	 * @param {Object} e Submit event.
	 */
	onAdd( e ) {
		e.preventDefault();

		this.collection.add( this.discount );
	}
} );
