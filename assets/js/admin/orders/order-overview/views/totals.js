/**
 * Order totals
 *
 * @since 3.0
 *
 * @class Totals
 * @augments wp.Backbone.View
 */
export const Totals = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__totals',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-totals' ),

	/**
	 * @since 3.0
	 */
	events: {
		'click #notice-tax-change .notice-dismiss': 'onDismissTaxRateChange',
		'click #notice-tax-change .update-amounts': 'onUpdateAmounts',
	},

	/**
	 * Order totals view.
	 *
	 * @since 3.0
	 *
	 * @constructs Totals
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

		// Determine column offset.
		const colspan = 2;

		const subtotal = state.getSubtotal();
		const tax = state.getTax();
		const total = state.getTotal();
		const discount = state.getDiscount();
		const hasNewTaxRate = state.hasNewTaxRate();
		const hasManualAdjustment = undefined !== state.get( 'items' ).findWhere( {
			_isAdjustingManually: true,
		} );

		return {
			state: {
				...state.toJSON(),
				hasNewTaxRate,
				hasManualAdjustment,
			},
			config: {
				colspan,
			},

			subtotal,
			tax,
			total,
			discount,

			subtotalCurrency: currency.format( number.absint( subtotal ) ),
			discountCurrency: currency.format( number.absint( discount ) ),
			taxCurrency: currency.format( number.absint( tax ) ),
			totalCurrency: currency.format( number.absint( total ) ),
		};
	},

	/**
	 * Dismisses Tax Rate change notice.
	 *
	 * @since 3.0
	 */
	onDismissTaxRateChange() {
		const { state } = this.options;
		// Reset amount
		state.set( 'hasTax', state.get( 'hasTax' ) );

		// Manually trigger change because new and previous attributes
		// are the same so Backbone will not.
		state.trigger( 'change:hasTax' );
	},

	/**
	 * Updates amounts for existing Order Items.
	 *
	 * @since 3.0
	 */
	onUpdateAmounts( e ) {
		e.preventDefault();

		const { state } = this.options;

		state.get( 'items' )
			.updateAmounts()
			.done( ( response ) => {
				this.onDismissTaxRateChange();
			} );
	},
} );
