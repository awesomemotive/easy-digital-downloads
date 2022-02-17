/* global Backbone, _, $ */

/**
 * Internal dependencies
 */
import { OrderAdjustments } from './../collections/order-adjustments.js';

/**
 * OrderItem
 *
 * @since 3.0
 *
 * @class OrderItem
 * @augments Backbone.Model
 */
export const OrderItem = Backbone.Model.extend( {
	/**
	 * @since 3.0
	 *
	 * @typedef {Object} OrderItem
	 */
	defaults: {
		id: 0,
		orderId: 0,
		productId: 0,
		productName: '',
		priceId: null,
		cartIndex: 0,
		type: 'download',
		status: '',
		statusLabel: '',
		quantity: 1,
		amount: 0,
		subtotal: 0,
		discount: 0,
		tax: 0,
		total: 0,
		dateCreated: '',
		dateModified: '',
		uuid: '',

		// Track manually set amounts.
		amountManual: 0,
		taxManual: 0,
		subtotalManual: 0,

		// Track if the amounts have been adjusted manually on addition.
		_isAdjustingManually: false,

		// Track `OrderItem`-level adjustments.
		//
		// The handling of Adjustments in the API is currently somewhat
		// fragmented with certain extensions creating Adjustments at the
		// `Order` level, some at a duplicate `OrderItem` level, and some both.
		adjustments: new OrderAdjustments(),
	},

	/**
	 * Returns the `OrderItem` subtotal amount.
	 *
	 * @since 3.0.0
	 *
	 * @param {bool} includeTax If taxes should be included when retrieving the subtotal.
	 *                          This is needed in some scenarios with inclusive taxes.
	 * @return {number} Subtotal amount.
	 */
	getSubtotal( includeTax = false ) {
		const state = this.get( 'state' );
		const subtotal = this.get( 'subtotal' );

		// Use stored value if the record has already been created.
		if ( false === state.get( 'isAdding' ) ) {
			return subtotal;
		}

		// Calculate subtotal.
		if ( true === state.hasInclusiveTax() && false === includeTax ) {
			return subtotal - this.getTax();
		}

		return subtotal;
	},

	/**
	 * Returns the Discount amount.
	 *
	 * If an Order is being added the amount is calculated based
	 * on the total of `OrderItem`-level Adjustments that are
	 * currently applied.
	 *
	 * If an Order has already been added use the amount stored
	 * directly in the database.
	 *
	 * @since 3.0
	 *
	 * @return {number} Discount amount.
	 */
	getDiscountAmount() {
		let amount = 0;

		const discounts = this.get( 'adjustments' ).getByType( 'discount' );

		if ( 0 === discounts.length ) {
			return this.get( 'discount' );
		}

		discounts.forEach( ( discount ) => {
			amount += +discount.get( 'subtotal' );
		} );

		return amount;
	},

	/**
	 * Retrieves the rounded Tax for the order item.
	 *
	 * Rounded to match storefront checkout.
	 *
	 * @since 3.0.0
	 *
	 * @return {number} Total amount.
	 */
	getTax() {
		const state = this.get( 'state' );
		const tax = this.get( 'tax' );

		// Use stored value if the record has already been created.
		if ( false === state.get( 'isAdding' ) ) {
			return tax;
		}

		// Calculate tax.
		const { number } = state.get( 'formatters' );

		return number.unformat( number.format( tax ) );
	},

	/**
	 * Retrieves the Total for the order item.
	 *
	 * @since 3.0.0
	 *
	 * @return {number} Total amount.
	 */
	getTotal() {
		const state = this.get( 'state' );

		// Use stored value if the record has already been created.
		if ( false === state.get( 'isAdding' ) ) {
			return this.get( 'total' );
		}

		// Calculate total.
		if ( true === state.hasInclusiveTax() ) {
			return this.get( 'subtotal' ) - this.getDiscountAmount();
		}

		return ( this.get( 'subtotal' ) - this.getDiscountAmount() ) + this.getTax();
	},

	/**
	 * Retrieves amounts for the `OrderItem` based on other `OrderItem`s and `OrderAdjustment`s.
	 *
	 * @since 3.0
	 *
	 * @param {Object} args Arguments to pass as data in the XHR request.
	 * @param {string} args.country Country code to determine tax rate.
	 * @param {string} args.region Region to determine tax rate.
	 * @param {Array} args.products List of current products added to the order.
	 * @param {Array} args.discountIds List of `OrderAdjustmentDiscount`s to calculate amounts against.
	 * @return {$.promise} A jQuery promise that represents the request.
	 */
	getAmounts( {
		country = '',
		region = '',
		products = [],
		discountIds = [],
	} ) {
		const {
			nonces: { edd_admin_order_get_item_amounts: nonce },
		} = window.eddAdminOrderOverview;

		const { productId, priceId, quantity, amount, tax, subtotal } = _.clone(
			this.attributes
		);

		return wp.ajax.send( 'edd-admin-order-get-item-amounts', {
			data: {
				nonce,
				productId,
				priceId,
				quantity,
				amount,
				tax,
				subtotal,
				country,
				region,
				products: _.uniq( [
					...products,
					{
						id: productId,
						quantity,
						options: {
							price_id: priceId,
						},
					},
				], function( { id, options: { price_id } } ) {
					return `${ id }_${ price_id }`
				} ),
				discounts: _.uniq( discountIds ),
			},
		} );
	},

	/**
	 * Bulk sets amounts.
	 *
	 * Only adjusts the Discount amount if adjusting manually.
	 *
	 * @since 3.0
	 *
	 * @param {Object} amounts Amounts to set.
	 * @param {number} amounts.amount `OrderItem` unit price.
	 * @param {number} amounts.discount `OrderItem` discount amount.
	 * @param {number} amounts.tax `OrderItem` tax amount.
	 * @param {number} amounts.subtotal `OrderItem` subtotal amount.
	 * @param {number} amounts.total `OrderItem` total amount.
	 */
	setAmounts( {
		amount = 0,
		discount = 0,
		tax = 0,
		subtotal = 0,
		total = 0,
	} ) {
		if ( true === this.get( '_isAdjustingManually' ) ) {
			this.set( {
				discount,
			} );
		} else {
			this.set( {
				amount,
				discount,
				tax,
				subtotal,
				total,
			} );
		}
	},
} );
