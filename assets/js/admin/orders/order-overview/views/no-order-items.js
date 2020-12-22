/**
 * Internal dependencies
 */
import { Base } from './base.js';

/**
 * NoOrderItems
 *
 * @since 3.0
 *
 * @class NoOrderItems
 * @augments wp.Backbone.View
 */
export const NoOrderItems = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tr',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-no-items' ),

	/**
	 * Prepares data to be used in `render` method.
	 *
	 * @since 3.0
	 *
	 * @see wp.Backbone.View
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-backbone.js
	 *
	 * @return {Object} The data for this view.
	 */
	prepare() {
		const { model, options } = this;
		const { state } = this.options;

		// Determine column offset.
		const colspan = 4;

		return {
			...Base.prototype.prepare.apply( this, arguments ),

			config: {
				colspan,
			},
		};
	},
} );
