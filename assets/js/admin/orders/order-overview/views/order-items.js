/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	OrderItem
} from './';

/**
 * Items
 *
 * @since 3.0
 *
 * @class Items
 * @augments wp.Backbone.View
 */
export const OrderItems = wp.Backbone.View.extend( /** Lends Items.prototype */ {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__items',

	/**
	 * @since 3.0
	 */
	initialize() {
		this.listenTo( this.options.state.get( 'items' ), 'add', this.onAdd );
		this.listenTo( this.options.state.get( 'items' ), 'remove', this.render );
		this.listenTo( this.options.state.get( 'adjustments' ), 'remove', this.onRemoveAdjustment );
	},

	/**
	 * Renders the view.
	 *
	 * @since 3.0
	 *
	 * @return {Items} Current view.
	 */
	render() {
		const {
			state,
		} = this.options;

		// Clear existing Item views.
		this.views.remove();

		// Add Items.
		_.each( state.get( 'items' ).models, ( item ) => this.onAdd( item ) );

		return this;
	},

	/**
	 * Adds an OrderItem subview.
	 *
	 * @since 3.0
	 *
	 * @param {OrderItem} Item to add to view.
	 */
	onAdd( model ) {
		this.views.add(
			new OrderItem( {
				...this.options,
				model,
			} )
		);
	},

	/**
	 * @since 3.0
	 */
	onRemoveAdjustment() {
		const {
			state,
		} = this.options;

		state.get( 'items' )
			.updateAmounts()
			.done( () => this.render );
	},
} );
