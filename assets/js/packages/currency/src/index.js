/* global edd_vars */

/**
 * Internal dependencies
 */
import { NumberFormat } from './number.js';

// Make Number directly accessible from package.
export { NumberFormat } from './number.js';

/**
 * Currency
 *
 * @class Currency
 */
export const Currency = class Currency {
	/**
	 * Creates configuration for currency formatting.
	 *
	 * @todo Validate configuration.
	 *
	 * @since 3.0
	 *
	 * @param {Object} config Currency configuration arguments.
	 * @param {string} [config.currency=edd_vars.currency] Currency (USD, AUD, etc).
	 * @param {string} [config.currencySymbol=edd_vars.currency_sign] Currency symbol ($, €, etc).
	 * @param {string} [config.currencySymbolPosition=edd_vars.currency_pos] Currency symbol position (left or right).
	 * @param {number} [config.decimalPlaces=edd_vars.currency_decimals] The number of decimals places to format to.
	 * @param {string} [config.decimalSeparator=edd_vars.decimal_separator] The separator between the number and decimal.
	 * @param {string} [config.thousandsSeparator=edd_vars.thousands_separator] Thousands separator.
	 */
	constructor( config = {} ) {
		const {
			currency,
			currency_sign: currencySymbol,
			currency_pos: currencySymbolPosition,
			currency_decimals: precision,
			decimal_separator: decimalSeparator,
			thousands_separator: thousandSeparator,
		} = edd_vars;

		this.config = {
			currency,
			currencySymbol,
			currencySymbolPosition,
			precision,
			decimalSeparator,
			thousandSeparator,
			...config,
		};

		this.number = new NumberFormat( this.config );
	}

	/**
	 * Formats a number for currency display.
	 *
	 * @since 3.0
	 *
	 * @param {number} number Number to format.
	 * @return {?string} A formatted string.
	 */
	format( number, absint = true ) {
		const { currencySymbol, currencySymbolPosition } = this.config;

		const isNegative = number < 0;
		let formattedNumber = this.number.format( number );
		let currency = '';

		if( isNegative ) {
			formattedNumber = this.number.format( number * -1 );
		}

		switch ( currencySymbolPosition ) {
			case 'before':
				currency = currencySymbol + formattedNumber;
				break;
			case 'after':
				currency = formattedNumber + currencySymbol;
				break;
		}

		// Place negative symbol before currency symbol if needed.
		if ( true === isNegative && false === absint ) {
			currency = `-${ currency }`;
		}

		return currency;
	}

	/**
	 * Removes formatting from a currency string.
	 *
	 * @since 3.0
	 *
	 * @param {string} currency String containing currency formatting.
	 * @return {number} Unformatted number.
	 */
	unformat( currency ) {
		const { currencySymbol } = this.config;

		// Remove any existing currency symbol.
		const number = currency.replace( currencySymbol, '' );

		return this.number.unformat( number );
	}
};
