/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	Adjustment
} from './';

/**
 * Adjustments
 *
 * @since 3.0
 *
 * @class Adjustments
 * @augments wp.Backbone.View
 */
export const Adjustments = wp.Backbone.View.extend( /** Lends Adjustments.prototype */ {
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
		this.listenTo( this.options.config.get( 'adjustments' ), 'add', this.onAdd );
		this.listenTo( this.options.config.get( 'adjustments' ), 'remove', this.render );
	},

	/**
	 * Renders the view.
	 *
	 * @since 3.0
	 *
	 * @return {Adjustments} Current view.
	 */
	render() {
		const {
			config,
		} = this.options;

		// Clear existing Adjustment views.
		this.views.remove();

		// Add Adjustments.
		_.each( config.get( 'adjustments' ).models, ( item ) => this.onAdd( item ) );

		return this;
	},

	/**
	 * Adds an Adjustment subview.
	 *
	 * @since 3.0
	 *
	 * @param {Adjustment} Adjustment to add to view.
	 */
	onAdd( model ) {
		this.views.add(
			new Adjustment( {
				...this.options,
				model,
			} )
		);
	}
} );
