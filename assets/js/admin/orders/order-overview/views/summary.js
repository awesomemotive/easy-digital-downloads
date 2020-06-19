/** global wp */

/**
 * Internal dependencies
 */
import { OrderItems } from './order-items.js';
import { OrderAdjustments } from './order-adjustments.js';
import { Refunds } from './order-refunds.js';
import { Totals } from './totals.js';

/**
 * Overview summary
 *
 * Contains Order Items and Item totals.
 *
 * @since 3.0
 *
 * @class Summary
 * @augments wp.Backbone.view
 */
export const Summary = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-order-overview-summary',

	/**
	 * Renders the view.
	 *
	 * @since 3.0
	 *
	 * @return {Summary} Current view.
	 */
	render() {
		this.views.add( new OrderItems( this.options ) );
		this.views.add( new OrderAdjustments( this.options ) );
		this.views.add( new Totals( this.options ) );
		this.views.add( new Refunds( this.options ) );

		return this;
	},
} );
