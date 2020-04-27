/* global _ */

/**
 * Internal dependencies
 */
import { OrderItem } from './order-item.js';
import { NoOrderItems } from './no-order-items.js';

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
		this.listenTo( items, 'add', this.render );
		this.listenTo( items, 'remove', this.remove );
	},

	/**
	 * Renders initial view.
	 *
	 * @since 3.0
	 */
	render() {
		const { state } = this.options;
		const items = state.get( 'items' );

		this.views.remove();

		// Nothing available.
		if ( 0 === items.length ) {
			this.views.set(
				new NoOrderItems( {
					...this.options,
				} )
			);
			// Render each item.
		} else {
			_.each( items.models, ( model ) => this.add( model ) );
		}
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

			if ( viewModel.get( 'id' ) === model.id ) {
				subview = view;
			}
		} );

		// Remove subview if found.
		if ( null !== subview ) {
			subview.remove();
		}

		// Last item was removed, show "No items".
		if ( 0 === this.views.get().length ) {
			this.views.set(
				new NoOrderItems( {
					...this.options,
				} )
			);
		}
	},
} );
