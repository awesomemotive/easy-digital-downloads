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

		const { productId, priceId, quantity, amount, subtotal } = _.clone(
			this.attributes
		);

		return wp.ajax.send( 'edd-admin-order-get-item-amounts', {
			data: {
				nonce,
				productId,
				priceId,
				quantity,
				amount,
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
