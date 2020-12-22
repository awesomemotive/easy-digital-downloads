/* global wp */

/**
 * OrderRefund
 *
 * @since 3.0
 *
 * @class OrderRefund
 * @augments wp.Backbone.View
 */
export const OrderRefund = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-refund' ),

	/**
	 * @since 3.0
	 */
	tagName: 'tr',

	/**
	 * @since 3.0
	 */
	className: 'is-expanded',

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
		const { state } = options;

		const { currency } = state.get( 'formatters' );

		// Determine column offset.
		const colspan = 2;

		return {
			config: {
				colspan,
			},

			id: model.get( 'id' ),
			number: model.get( 'number' ),
			dateCreated: model.get( 'dateCreatedi18n' ),
			totalCurrency: currency.format( model.get( 'total' ) ),
		};
	},
} );
