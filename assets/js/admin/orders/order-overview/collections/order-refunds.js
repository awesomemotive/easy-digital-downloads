/* global Backbone */

/**
 * Internal dependencies
 */
import { OrderRefund } from './../models/order-refund.js';

/**
 * Collection of `OrderRefund`s.
 *
 * @since 3.0
 *
 * @class OrderRefunds
 * @augments Backbone.Collection
 */
export const OrderRefunds = Backbone.Collection.extend( {
	/**
	 * @since 3.0
	 */
	model: OrderRefund,
} );
