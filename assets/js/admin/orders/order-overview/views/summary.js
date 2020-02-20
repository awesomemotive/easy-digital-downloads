/** global wp */

/**
 * Internal dependencies
 */
import { OrderItems, OrderAdjustments, Totals } from './';

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
export const Summary = wp.Backbone.View.extend(
	/** Lends Summary.prototype */ {
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

			return this;
		},
	}
);
