/* global wp, $ */

/**
 * Internal dependencies
 */
import {
	FormAddItem,
} from './';

/**
 * Overview actions
 *
 * @since 3.0
 *
 * @class Actions
 * @augments wp.Backbone.View
 */
export const Actions = wp.Backbone.View.extend( /** Lends Actions.prototype */ {
	/**
	 * @since 3.0
	 */
	el: '#edd-order-overview-actions',

	/**
	 * @since 3.0
	 */
	events: {
		'click #add-item': 'addItem',
	},

	/**
	 * Renders the "Add Item" flow.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	addItem( e ) {
		e.preventDefault();

		new FormAddItem( this.options )
			.openDialog()
			.render();
	},

} );
