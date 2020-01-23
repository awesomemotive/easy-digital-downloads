/* global edd_vars */

/**
 * External dependencies
 */
const number_format = require( 'locutus/php/strings/number_format' );

/**
 * Currency
 */
export const Currency = class Currency {

	/**
	 * Creates configuration for currency formatting.
	 *
	 * @since 3.0
	 *
	 * @param {Object} config Currency configuration arguments.
	 * @param {String} config.currency Currency (USD, AUD, etc).
	 * @param {String} config.currencySymbolPosition Currency symbol position (left or right).
	 * @param {Number} config.decimalPlaces The number of decimals places to format to.
	 * @param {String} config.decimalSeparator The separator between the number and decimal.
	 * @param {String} config.thousandsSeparator Thousands separator.
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
		}
	}

	/**
	 * Formats a number for display.
	 *
	 * @since 3.0
	 * @see http://locutus.io/php/strings/number_format/
	 *
	 * @param {Number|String} number Number to format.
	 * @returns {?String} A formatted string.
	 */
	formatNumber( number ) {
		if ( 'number' !== typeof number ) {
			number = parseFloat( number );
		}

		if ( isNaN( number ) ) {
			return '';
		}

		const {
			precision,
			decimalSeparator,
			thousandSeparator
		} = this.config;

		return number_format(
			number,
			precision,
			decimalSeparator,
			thousandSeparator
		);
	}

	/**
	 * Removes any currency formatting applied to a string
	 * and returns a true number.
	 *
	 * @since 3.0
	 *
	 * @param {String} number Number to unformat.
	 * @return {Number}
	 */
	unformat( number ) {
		const {
			currencySymbol,
			decimalSeparator,
			thousandSeparator
		} = this.config;

		const unformatted = number
			// Remove symbol.
			.replace( currencySymbol, '' )

			// Remove thousand separator.
			.replace( thousandSeparator, '' )

			// Replace decimal separator with a decimal.
			.replace( decimalSeparator, '.' );

		return parseFloat( unformatted );
	}

	/**
	 * Formats a number for currency display.
	 *
	 * @since 3.0
	 *
	 * @param {Number|String} number Number to format.
	 * @returns {?String} A formatted string.
	 */
	formatCurrency( number ) {
		const formattedNumber = this.formatNumber( number );

		if ( '' === formattedNumber ) {
			return formattedNumber;
		}

		const {
			currencySymbol,
			currencySymbolPosition,
		} = this.config;

		let currency = '';

		switch ( currencySymbolPosition ) {
			case 'before':
				currency = currencySymbol + formattedNumber;
				break;
			case 'after':
				currency = formattedNumber + currencySymbol;
				break;
		}

		return currency;
	}

}
