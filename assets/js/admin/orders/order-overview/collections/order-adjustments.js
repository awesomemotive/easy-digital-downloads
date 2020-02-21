/* global Backbone */

/**
 * Internal dependencies
 */
import { OrderAdjustment, OrderAdjustmentDiscount } from './../models';

/**
 * Collection of `OrderAdjustment`s.
 *
 * @since 3.0
 *
 * @class Adjustments
 * @augments Backbone.Collection
 */
export const OrderAdjustments = Backbone.Collection.extend( {
	/**
	 * Initializes the `OrderAdjustments` collection.
	 *
	 * @since 3.0
	 *
	 * @constructs OrderAdjustments
	 * @augments Backbone.Collection
	 */
	initialize() {
		this.getByType = this.getByType.bind( this );
	},

	/**
	 * Determines which Model to use and instantiates it.
	 *
	 * @since 3.0
	 *
	 * @param {Object} attributes Model attributes.
	 * @param {Object} options Model options.
	 */
	model( attributes, options ) {
		let model;

		switch ( attributes.type ) {
			case 'discount':
				model = new OrderAdjustmentDiscount( attributes, options );
				break;
			default:
				model = new OrderAdjustment( attributes, options );
		}

		return model;
	},

	/**
	 * Returns a list of `OrderAdjustment`s by type.
	 *
	 * @since 3.0
	 *
	 * @param {string} type Type of adjustment to retrieve. `fee`, `credit`, or `discount`.
	 * @return {Array} List of type-specific adjustments.
	 */
	getByType( type ) {
		return this.where( {
			type,
		} );
	},
} );
