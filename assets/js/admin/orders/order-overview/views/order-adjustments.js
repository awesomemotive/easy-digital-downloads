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
		this.listenTo( this.options.state.get( 'adjustments' ), 'add', this.onAdd );
		this.listenTo( this.options.state.get( 'adjustments' ), 'remove', this.render );
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
		this.views.add(
			new OrderAdjustment( {
				...this.options,
				model,
			} )
		);
	}
} );
