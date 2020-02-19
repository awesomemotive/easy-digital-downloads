/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	OrderAdjustment,
} from './';

/**
 * OrderAdjustments
 *
 * @since 3.0
 *
 * @class Adjustments
 * @augments wp.Backbone.View
 */
export const OrderAdjustments = wp.Backbone.View.extend( /** Lends Adjustments.prototype */ {
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
		const {
			state,
		} = this.options;

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
		const {
			state,
		} = this.options;

		// Clear existing OrderAdjustment views.
		this.views.remove();

		// Add OrderAdjustments.
		_.each( state.get( 'adjustments' ).models, ( item ) => this.onAdd( item ) );

		return this;
	},

	/**
	 * Adds an OrderAdjustment subview.
	 *
	 * @since 3.0
	 *
	 * @param {OrderAdjustment} OrderAdjustment to add to view.
	 */
	onAdd( model ) {
		// Keep state context available.
		// @todo Find a better way?
		model.set( 'options', this.options );

		const {
			state,
		} = this.options;

		state.get( 'items' )
			.updateAmounts()
			.done( () => {
				console.log('adding view' );
				this.views.add(
					new OrderAdjustment( {
						...this.options,
						model,
					} )
				);
			} );
	}
} );
