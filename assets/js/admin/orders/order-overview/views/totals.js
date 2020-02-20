/**
 * Internal dependencies
 */
import { Currency } from '@easy-digital-downloads/currency';

const currency = new Currency();

/**
 * Order totals
 *
 * @since 3.0
 *
 * @class Totals
 * @augments wp.Backbone.View
 */
export const Totals = wp.Backbone.View.extend(
	/** Lends Totals.prototype */ {
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
			this.listenTo(
				state.get( 'items' ),
				'add remove change',
				this.render
			);
			this.listenTo(
				state.get( 'adjustments' ),
				'add remove',
				this.render
			);
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

			// Determine column offset -- using cart quantities requires an extra column.
			const colspan = true === state.get( 'hasQuantity' ) ? 2 : 1;

			const subtotal = state.getSubtotal();
			const tax = state.getTax();
			const total = state.getTotal();

			return {
				state: {
					...state.toJSON(),
				},
				config: {
					colspan,
				},

				subtotal,
				tax,
				total,

				subtotalCurrency: currency.format( subtotal ),
				taxCurrency: currency.format( tax ),
				totalCurrency: currency.format( total ),
			};
		},
	}
);
