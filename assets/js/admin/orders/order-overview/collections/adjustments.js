/* global Backbone */

/**
 * Internal dependencies
 */
import {
	Adjustment,
} from './../models';

/**
 * Adjustments
 *
 * Collection for Order Adjustments.
 *
 * @since 3.0
 *
 * @class Adjustments
 * @augments Backbone.Collection
 */
export const Adjustments = Backbone.Collection.extend( /** @lends Adjustments.prototype */ {

	/**
	 * @since 3.0
	 */
	model: Adjustment,

} );
