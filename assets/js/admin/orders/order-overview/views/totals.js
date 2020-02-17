/* global wp */

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
export const Totals = wp.Backbone.View.extend( /** Lends Totals.prototype */ {
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
		// Rerender when Items adds or removes an Item.
		this.listenTo( this.options.config.get( 'items' ), 'add remove', this.render );

		// Bind context.
		this.getSubtotal = this.getSubtotal.bind( this );
		this.getTax = this.getTax.bind( this );
		this.getTotal = this.getTotal.bind( this );
	},

	/**
	 * Prepares data to be used in `render` method.
	 *
	 * @since 3.0
	 *
	 * @see wp.Backbone.View
	 * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-backbone.js
	 *
	 * @return {Object} The data for this view.
	 */
	prepare() {
		const {
			options,

			getSubtotal,
			getTax,
			getTotal,
		} = this;

		const {
			config,
		} = options;
		
		// Determine column offset -- using cart quantities requires an extra column.
		const colspan = true === config.get( 'hasQuantity' )
			? 2 
			: 1;

		const subtotal = getSubtotal();
		const tax = getTax();
		const total = getTotal();

		return {
			config: {
				...config.toJSON(),
				colspan,
			},

			subtotal,
			tax,
			total,

			subtotalCurrency: currency.format( subtotal ),
			taxCurrency: currency.format( tax ),
			totalCurrency: currency.format( total ),
		}
	},

	/**
	 * Retrieves the Order subtotal.
	 *
	 * @since 3.0
	 *
	 * @return {number} Order subtotal.
	 */
	getSubtotal() {
		let subtotal = 0;

		// Add all item subtotals.
		_.each( this.options.config.get( 'items' ).models, ( item ) => {
			return subtotal += +item.get( 'subtotal' )
		} );

		// Add or substract all adjustment subtotals.
		_.each( this.options.config.get( 'adjustments' ).models, ( adjustment ) => {
			if ( 'discount' === adjustment.get( 'type' ) || 'credit' === adjustment.get( 'type' ) ) {
				return subtotal -= +adjustment.get( 'subtotal' )
			} else {
				return subtotal += +adjustment.get( 'subtotal' )
			}
		} );

		return subtotal;
	},

	/**
	 * Retrieves the Order tax.
	 *
	 * @since 3.0
	 *
	 * @return {number} Order tax.
	 */
	getTax() {
		let tax = 0;

		// Add all item taxes.
		_.each( this.options.config.get( 'items' ).models, ( item ) => {
			return tax += +item.get( 'tax' )
		} );

		return tax;
	},

	/**
	 * Retrieves the Order total.
	 *
	 * @since 3.0
	 *
	 * @return {number} Order total.
	 */
	getTotal() {
		return this.getSubtotal() + this.getTax();
	},
} );
