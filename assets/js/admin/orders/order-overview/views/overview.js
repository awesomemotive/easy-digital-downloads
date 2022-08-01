/** global wp */

/**
 * Internal dependencies
 */
import { Summary } from './summary.js';
import { Actions } from './actions.js';

/**
 * Overview
 *
 * @since 3.0
 *
 * @class Overview
 * @augments wp.Backbone.View
 */
export const Overview = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-order-overview',

	/**
	 * @since 3.0
	 */
	events: {
		'click .toggle-row': 'onToggleRow',
	},

	/**
	 * Renders the view.
	 *
	 * @since 3.0
	 *
	 * @return {Overview} Current view.
	 */
	render() {
		// Add "Summary".
		//
		// Contains `OrderItems`, `OrderAdjustments`, and `Totals` subviews.
		this.views.add( new Summary( this.options ) );

		// "Actions".
		if ( document.getElementById( 'edd-order-overview-actions' ) ) {
			this.views.add( new Actions( this.options ) );
		}

		return this;
	},

	/**
	 * Toggles a row's other columns.
	 *
	 * Core does not support the dynamically added items.
	 *
	 * @since 3.0
	 *
	 * @see https://github.com/WordPress/WordPress/blob/001ffe81fbec4438a9f594f330e18103d21fbcd7/wp-admin/js/common.js#L908
	 *
	 * @param {Object} e Click event.
	 */
	onToggleRow( e ) {
		e.preventDefault();
		$( e.target ).closest( 'tr' ).toggleClass( 'is-expanded' );
	},
} );
