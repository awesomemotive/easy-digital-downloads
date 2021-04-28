/**
 * Order total
 *
 * @since 3.0
 *
 * @class OrderTotal
 * @augments wp.Backbone.View
 */
export const OrderTotal = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__total',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-total' ),

	/**
	 * Order tax view.
	 *
	 * @since 3.0
	 *
	 * @constructs OrderTax
	 * @augments wp.Backbone.View
	 */
	initialize() {
		const { state } = this.options;

		// Listen for events.
		this.listenTo( state, 'change:hasTax', this.render );
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

		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === state.get( 'hasQuantity' ) ? 2 : 1;

		const total = state.getTotal();
		const discount = state.getDiscount();
		const hasManualAdjustment = undefined !== state.get( 'items' ).findWhere( {
			_isAdjustingManually: true,
		} );

		return {
			state: {
				...state.toJSON(),
				hasManualAdjustment,
			},
			config: {
				colspan,
			},

			total,
			discount,

			discountCurrency: currency.format( number.absint( discount ) ),
			totalCurrency: currency.format( number.absint( total ) ),
		};
	},
} );
