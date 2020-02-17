/* global wp, _ */

/**
 * Internal dependencies
 */
import {
	Item
} from './';

/**
 * Items
 *
 * @since 3.0
 *
 * @class Items
 * @augments wp.Backbone.View
 */
export const Items = wp.Backbone.View.extend( /** Lends Items.prototype */ {
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
		this.listenTo( this.options.config.get( 'items' ), 'add', this.onAdd );
		this.listenTo( this.options.config.get( 'items' ), 'remove', this.render );
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
			config,
		} = this.options;

		// Clear existing Item views.
		this.views.remove();

		// Add Items.
		_.each( config.get( 'items' ).models, ( item ) => this.onAdd( item ) );

		return this;
	},

	/**
	 * Adds an Item subview.
	 *
	 * @since 3.0
	 *
	 * @param {Item} Item to add to view.
	 */
	onAdd( model ) {
		this.views.add(
			new Item( {
				...this.options,
				model,
			} )
		);
	}
} );
