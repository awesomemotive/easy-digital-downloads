/**
 * Order subtotal
 *
 * @since 3.0
 *
 * @class Subtotal
 * @augments wp.Backbone.View
 */
export const OrderSubtotal = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__subtotal',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-subtotal' ),

	/**
	 * Order subtotal view.
	 *
	 * @since 3.0
	 *
	 * @constructs OrderSubtotal
	 * @augments wp.Backbone.View
	 */
	initialize() {
		const { state } = this.options;

		// Listen for events.
		this.listenTo( state.get( 'items' ), 'add remove change', this.render );
		this.listenTo( state.get( 'adjustments' ), 'add remove', this.render );
	},

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
		const { state } = this.options;
		const { currency, number } = state.get( 'formatters' );
		const colspan = true === state.get( 'hasQuantity' ) ? 2 : 1;

		const subtotal = state.getSubtotal();

		return {
			state: state.toJSON(),
			config: {
				colspan,
			},

			subtotal,
			subtotalCurrency: currency.format( number.absint( subtotal ) ),
		};
	},
} );
