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
		this.views.add( new Actions( this.options ) );

		return this;
	},
} );
