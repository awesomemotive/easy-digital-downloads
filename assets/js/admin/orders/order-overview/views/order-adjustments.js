/* global _ */

/**
 * Internal dependencies
 */
import { OrderAdjustment } from './';

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

		this.listenTo( adjustments, 'add', this.onAdd );
		this.listenTo( adjustments, 'remove', this.render );
		this.listenTo( items, 'add remove', this.render );
	},

	/**
	 * Renders the view.
	 *
	 * @since 3.0
	 *
	 * @return {OrderAdjustments} Current view.
	 */
	render() {
		const { state } = this.options;

		// Clear existing OrderAdjustment views.
		this.views.remove();

		// Add OrderAdjustments.
		_.each( state.get( 'adjustments' ).models, ( item ) =>
			this.onAdd( item )
		);

		return this;
	},

	/**
	 * Adds an OrderAdjustment subview.
	 *
	 * @todo This is messy/hacky.
	 * The `OrderItem` amount updates should be called within
	 * the `OrderItems` view.
	 *
	 * @since 3.0
	 *
	 * @param {OrderAdjustment} model OrderAdjustment to add to view.
	 */
	onAdd( model ) {
		// Keep state context available.
		model.set( 'options', this.options );

		const { state } = this.options;

		// Do not recalculate amounts when viewing an order.
		if ( false === state.get( 'isAdding' ) ) {
			this.views.add(
				new OrderAdjustment( {
					...this.options,
					model,
				} )
			);

			return;
		}

		state
			.get( 'items' )
			.updateAmounts()
			.done( () => {
				this.views.add(
					new OrderAdjustment( {
						...this.options,
						model,
					} )
				);
			} );
	},
} );
