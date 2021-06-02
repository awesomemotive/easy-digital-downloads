/**
 * Order tax
 *
 * @since 3.0
 *
 * @class OrderTax
 * @augments wp.Backbone.View
 */
export const OrderTax = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__tax',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-tax' ),

	/**
	 * @since 3.0
	 */
	events: {
		'click #notice-tax-change .notice-dismiss': 'onDismissTaxRateChange',
		'click #notice-tax-change .update-amounts': 'onUpdateAmounts',
	},

	/**
	 * Order total view.
	 *
	 * @since 3.0
	 *
	 * @constructs OrderTotal
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

		const tax = state.getTax();
		const hasNewTaxRate = state.hasNewTaxRate();

		const taxableItems = [
			...state.get( 'items' ).models,
			...state.get( 'adjustments' ).getByType( 'fee' ),
		];

		return {
			state: {
				...state.toJSON(),
				hasNewTaxRate,
			},
			config: {
				colspan,
			},

			tax,
			taxCurrency: currency.format( tax ),

			hasTaxableItems: taxableItems.length > 0,
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

		// Manually recalculate taxed fees.
		state.get( 'adjustments' ).getByType( 'fee' ).forEach(
			( fee ) => {
				fee.updateTax();
			}
		);

		// Request updated tax amounts for orders from the server.
		state.get( 'items' )
			.updateAmounts()
			.done( () => {
				this.onDismissTaxRateChange();
			} );
	},
} );
