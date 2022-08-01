/* global _ */

/**
 * Internal dependencies
 */
import { OrderAdjustment } from './order-adjustment.js';

/**
 * OrderAdjustments
 *
 * @since 3.0
 *
 * @class OrderAdjustments
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
		this.listenTo( state, 'change:hasTax', this.render );
		this.listenTo( items, 'change', this.render );
		this.listenTo( adjustments, 'add', this.render );
		this.listenTo( adjustments, 'remove', this.remove );
	},

	/**
	 * Renders initial view.
	 *
	 * @since 3.0
	 */
	render() {
		const adjustments = this.getAdjustments();

		this.views.remove();

		_.each( adjustments, ( adjustment ) => this.add( adjustment ) );
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
		const views = this.views.get();

		if ( ! views ) {
			return;
		}

		// Find the Subview containing the model.
		views.forEach( ( view ) => {
			const { model: viewModel } = view;

			if ( viewModel.id === model.id ) {
				subview = view;
			}
		} );

		// Remove Subview if found.
		if ( null !== subview ) {
			subview.remove();
		}
	},
} );
