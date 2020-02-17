/* global Backbone */

/**
 * Internal dependencies
 */
import {
	OrderAdjustment,
	OrderAdjustmentDiscount,
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
	 * Adjustments collection.
	 *
	 * @since 3.0
	 *
	 * @constructs Adjustments
	 * @augments Backbone.Collection
	 */
	initialize() {
		this.getByType = this.getByType.bind( this );
	},

	/**
	 * @since 3.0
	 *
	 * @param {Object} attributes Model attributes.
	 * @param {Object} options Model options?
	 */
	model( attributes, options ) {
		switch ( attributes.type ) {
			case 'discount':
				return new OrderAdjustmentDiscount( attributes, options );
				break;
			default:
				return new OrderAdjustment( attributes, options);
		}
	},

	/**
	 * Retrieve all adjustments of a certain type.
	 *
	 * @since 3.0
	 *
	 * @param {string} type Type of adjustment to retrieve.
	 * @return {Array} List of type-specific adjustments.
	 */
	getByType( type ) {
		return this.where( {
			type,
		} );
	}

} );
