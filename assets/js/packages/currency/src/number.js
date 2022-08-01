/* global edd_vars */

/**
 * External dependencies
 */
const numberFormatter = require( 'locutus/php/strings/number_format' );

/**
 * NumberFormat
 *
 * @class NumberFormat
 */
export const NumberFormat = class NumberFormat {
	/**
	 * Creates configuration for number formatting.
	 *
	 * @todo Validate configuration.
	 * @since 3.0
	 * @param {Object} config Configuration for the number formatter.
	 * @param {number} [config.decimalPlaces=edd_vars.currency_decimals] The number of decimals places to format to.
	 * @param {string} [config.decimalSeparator=edd_vars.decimal_separator] The separator between the number and decimal.
	 * @param {string} [config.thousandsSeparator=edd_vars.thousands_separator] Thousands separator.
	 */
	constructor( config = {} ) {
		const {
			currency_decimals: precision,
			decimal_separator: decimalSeparator,
			thousands_separator: thousandSeparator,
		} = edd_vars;

		this.config = {
			precision,
			decimalSeparator,
			thousandSeparator,
			...config,
		};
	}

	/**
	 * Formats a number for display based on decimal settings.
	 *
	 * @since 3.0
	 * @see http://locutus.io/php/strings/number_format/
	 *
	 * @param {number|string} number Number to format.
	 * @return {?string} A formatted string.
	 */
	format( number ) {
		let toFormat = number;

		if ( 'number' !== typeof number ) {
			toFormat = parseFloat( number );
		}

		if ( isNaN( toFormat ) ) {
			toFormat = 0;
		}

		const { precision, decimalSeparator, thousandSeparator } = this.config;

		return numberFormatter(
			toFormat,
			precision,
			decimalSeparator,
			thousandSeparator
		);
	}

	/**
	 * Removes any non-number formatting applied to a string
	 * and returns a true number.
	 *
	 * @since 3.0
	 *
	 * @param {*} number Number to unformat.
	 * @return {number} 0 If number cannot be unformatted properly.
	 */
	unformat( number ) {
		const { decimalSeparator, thousandSeparator } = this.config;

		if ( 'string' !== typeof number ) {
			number = String( number );
		}

		const unformatted = number
			// Remove thousand separator.
			.replace( thousandSeparator, '' )

			// Replace decimal separator with a decimal.
			.replace( decimalSeparator, '.' );

		const parsed = parseFloat( unformatted );

		return isNaN( parsed ) ? 0 : parsed;
	}

	/**
	 * Converts a value to a non-negative number.
	 *
	 * @since 3.0
	 *
	 * @param {*} number Number to unformat.
	 * @return {number} A non-negative number.
	 */
	absint( number ) {
		const unformatted = this.unformat( number );

		if ( unformatted >= 0 ) {
			return unformatted;
		}

		return unformatted * -1;
	}
};
