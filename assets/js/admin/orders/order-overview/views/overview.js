/** global wp */

/**
 * Internal dependencies
 */
import {
	Summary,
	Actions,
} from './';

/**
 * Overview
 *
 * @since 3.0
 *
 * @class Overview
 * @augments wp.Backbone.View
 */
export const Overview = wp.Backbone.View.extend( /** Lends Overview.prototype */ {
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
		this.views.add(
			new Summary( this.options )
		);

		this.views.add(
			new Actions( this.options )
		);

		return this;
	},
} );
