/* global _ */

/**
 * Internal dependencies
 */
import { OrderItem } from './';

/**
 * OrderItems
 *
 * @since 3.0
 *
 * @class OrderItems
 * @augments wp.Backbone.View
 */
export const OrderItems = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__items',

	/**
	 * "Order Items" view.
	 *
	 * @since 3.0
	 *
	 * @constructs OrderItem
	 * @augments Base
	 */
	initialize() {
		const { state } = this.options;

		const items = state.get( 'items' );
		const adjustments = state.get( 'adjustments' );

		// Listen for events.
		this.listenTo( items, 'add', this.add );
		this.listenTo( items, 'remove', this.remove );

		this.listenTo( adjustments, 'add remove', this.onChangeAdjustments );
	},

	/**
	 * Adds an `OrderItem` subview.
	 *
	 * @since 3.0
	 *
	 * @param {OrderItem} model OrderItem 
	 */
	add( model ) {
		this.views.add(
			new OrderItem( {
				...this.options,
				model,
			} )
		);
	},

	/**
	 * Removes an `OrderItem` subview.
	 *
	 * @since 3.0
	 *
	 * @param {OrderItem} model OrderItem 
	 */
	remove( model ) {
		let subview = null;

		// Find the subview containing the model.
		this.views.get().forEach( ( view ) => {
			const { model: viewModel } = view;

			if ( parseInt( viewModel.get( 'id' ) ) === parseInt( model.id ) ) {
				subview = view;
			}
		} );

		// Remove subview if found.
		if ( null !== subview ) {
			subview.remove();
		}
	},

	/**
	 * @since 3.0
	 */
	onChangeAdjustments() {
		const { state } = this.options;

		state
			.get( 'items' )
			.updateAmounts()
			.done( () => this.render );
	},
} );
