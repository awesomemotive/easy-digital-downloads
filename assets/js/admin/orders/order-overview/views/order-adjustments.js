/* global _ */

/**
 * Internal dependencies
 */
import { Base } from './base.js';
import { OrderAdjustment } from './order-adjustment.js';

/**
 * OrderAdjustments
 *
 * @since 3.0
 *
 * @class Adjustments
 * @augments wp.Backbone.View
 */
export const OrderAdjustments = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__adjustments',

	/**
	 * @since 3.0
	 */
	initialize() {
		const { state } = this.options;

		const items = state.get( 'items' );
		const adjustments = state.get( 'adjustments' );

		// Listen for events.
		this.listenTo( adjustments, 'add', this.render );
		this.listenTo( adjustments, 'remove', this.remove );
	},

	/**
	 * Render the collection.
	 *
	 * @since 3.0
	 */
	render() {
		const { state } = this.options;
		const adjustments = state.get( 'adjustments' );

		// Remove existing `OrderAdjustment`s.
		this.views.remove();

		_.each( adjustments.models, ( model ) => this.add( model ) );
	},

	/**
	 * Adds an `OrderAdjustment` subview.
	 *
	 * @since 3.0
	 *
	 * @param {OrderAdjustment} model OrderAdjustment to add to view.
	 */
	add( model ) {
		this.views.add(
			new OrderAdjustment( {
				...this.options,
				model,
			} )
		);
	},

	/**
	 * Removes an `OrderAdjustment` subview.
	 *
	 * @since 3.0
	 *
	 * @param {OrderAdjustment} model OrderAdjustment to remove from view.
	 */
	remove( model ) {
		let subview = null;

		// Find the Subview containing the model.
		this.views.get().forEach( ( view ) => {
			const { model: viewModel } = view;

			if ( viewModel.get( 'id' ) === model.id ) {
				subview = view;
			}
		} );

		// Remove Subview if found.
		if ( null !== subview ) {
			subview.remove();
		}
	},
} );
