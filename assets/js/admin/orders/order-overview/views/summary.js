/** global wp */

/**
 * Internal dependencies
 */
import { OrderItems } from './order-items.js';
import { OrderSubtotal } from './order-subtotal.js';
import { OrderDiscountsFees } from './order-discounts-fees.js';
import { OrderTax } from './order-tax.js';
import { OrderCredits } from './order-credits.js';
import { OrderTotal } from './order-total.js';
import { OrderRefunds } from './order-refunds.js';

/**
 * Overview summary
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
		this.views.add( new OrderSubtotal( this.options ) );
		this.views.add( new OrderDiscountsFees( this.options ) );
		this.views.add( new OrderTax( this.options ) );
		this.views.add( new OrderCredits( this.options ) );
		this.views.add( new OrderTotal( this.options ) );
		this.views.add( new OrderRefunds( this.options ) );

		return this;
	},
} );
