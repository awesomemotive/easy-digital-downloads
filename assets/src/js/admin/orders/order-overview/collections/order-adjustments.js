/* global Backbone */

/**
 * Internal dependencies
 */
import { OrderAdjustment } from './../models/order-adjustment.js';
import { OrderAdjustmentDiscount } from './../models/order-adjustment-discount.js';

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
	 * @since 3.0
	 */
	comparator: 'type',

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
	 * Defines the model's attribute that defines it's ID.
	 *
	 * Uses the `OrderAdjustment`'s Type ID.
	 *
	 * @since 3.0
	 *
	 * @param {Object} attributes Model attributes.
	 * @return {number}
	 */
	modelId( attributes ) {
		return `${ attributes.type }-${ attributes.typeId }-${ attributes.description }`;
	},

	/**
	 * Determines if `OrderAdjustments` contains a specific `OrderAdjustment`.
	 *
	 * @since 3.0
	 *
	 * @param {OrderAdjustment} model Model to look for.
	 * @return {bool} True if the Collection contains the Model.
	 */
	has( model ) {
		return (
			undefined !==
			this.findWhere( {
				typeId: model.get( 'typeId' ),
			} )
		);
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
