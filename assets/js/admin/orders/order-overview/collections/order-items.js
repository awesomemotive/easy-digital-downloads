/* global Backbone */

/**
 * Internal dependencies
 */
import {
	OrderItem,
} from './../models';

/**
 * Items
 *
 * Collection for Order Items.
 *
 * @since 3.0
 *
 * @class Items
 * @augments Backbone.Collection
 */
export const Items = Backbone.Collection.extend( /** @lends Items.prototype */ {

	/**
	 * @since 3.0
	 */
	model: OrderItem,

} );
