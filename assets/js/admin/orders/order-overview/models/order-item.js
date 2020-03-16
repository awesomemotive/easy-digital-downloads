/* global Backbone, _, $ */

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
		priceId: 0,
		cartIndex: 0,
		type: 'download',
		status: '',
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

		// Track how much of each Discount is applied to an `OrderItem`.
		// There is not currently API support for `OrderItem`-level `OrderAdjustment`s.
		_discounts: [],

		// Track if the amounts have been adjusted manually on addition.
		_isAdjustingManually: false,
	},

	/**
	 * Returns the total Discount amount.
	 *
	 * @todo Clear up how/when a Discount amount is dynmically calculated
	 * vs. using the saved value.
	 *
	 * It could use `state.get( 'isAdding' )`, but that's not great either.
	 *
	 * @since 3.0
	 *
	 * @return {number}
	 */
	getDiscountTotal() {
		const _discounts = this.get( '_discounts' );

		// If there are no internally tracked Discounts use
		// the initial value.
		//
		// This ensues a value is available when viewing an existing Order.
		if ( 0 === _discounts.length ) {
			return this.get( 'discount' );
		}

		return _.reduce(
			_discounts,
			( total, _discount ) => {
				return total + _discount.amount;
			},
			0
		);
	},

	/**
	 * Retrieves amounts for the `OrderItem` based on other `OrderItem`s and `OrderAdjustment`s.
	 *
	 * @since 3.0
	 *
	 * @param {Object} args Arguments to pass as data in the XHR request.
	 * @param {string} args.country Country code to determine tax rate.
	 * @param {string} args.region Region to determine tax rate.
	 * @param {Array} args.productIds List of current products added to the order.
	 * @param {Array} args.discountIds List of `OrderAdjustmentDiscount`s to calculate amounts against.
	 * @return {$.promise} A jQuery promise that represents the request.
	 */
	getAmounts( {
		country = '',
		region = '',
		productIds = [],
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
				productIds: _.uniq( [ productId, ...productIds ] ),
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
