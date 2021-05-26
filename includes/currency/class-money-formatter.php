<?php
/**
 * Money Formatter
 *
 * Formats an amount of money in various ways, according to the provided currency.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.0
 */

namespace EDD\Currency;

class Money_Formatter {

	/**
	 * @var int|float Current working amount.
	 */
	public $amount;

	/**
	 * @var int|float Original, unmodified amount passed in via constructor.
	 */
	private $original_amount;

	/**
	 * @var Currency
	 */
	private $currency;

	/**
	 * Money_Formatter constructor.
	 *
	 * @param          $amount
	 * @param Currency $currency
	 */
	public function __construct( $amount, Currency $currency ) {
		$this->amount   = $this->original_amount = $amount;
		$this->currency = $currency;
	}

	/**
	 * Formats the amount for display.
	 * Does not apply the currency code.
	 *
	 * @since 3.0
	 * @return Money_Formatter
	 */
	public function format_for_display( $decimals = true ) {
		$amount = $this->amount;

		if ( ',' === $this->currency->decimal_separator && false !== ( $sep_found = strpos( $amount, $this->currency->decimal_separator ) ) ) {
			$whole  = substr( $amount, 0, $sep_found );
			$part   = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
			$amount = $whole . '.' . $part;
		}

		// Strip "," from the amount (if set as the thousands separator).
		if ( ',' === $this->currency->thousands_separator && false !== strpos( $amount, $this->currency->thousands_separator ) ) {
			$amount = str_replace( ',', '', $amount );
		}

		// Strip " " from the amount (if set as the thousands separator).
		if ( ' ' === $this->currency->thousands_separator && false !== strpos( $amount, $this->currency->thousands_separator ) ) {
			$amount = str_replace( ' ', '', $amount );
		}

		if ( empty( $amount ) ) {
			$amount = 0;
		}

		/**
		 * Filter number of decimals to use for formatted amount
		 *
		 * @since unknown
		 *
		 * @param int        $number        Default 2. Number of decimals.
		 * @param int|string $amount        Amount being formatted.
		 * @param string     $currency_code Currency code being formatted.
		 */
		$decimals = apply_filters( 'edd_format_amount_decimals', $decimals ? $this->currency->number_decimals : 0, $amount, $this->currency->code );

		// Format amount using decimals and separators (also rounds up or down)
		$formatted = number_format( (float) $amount, $decimals, $this->currency->decimal_separator, $this->currency->thousands_separator );

		/**
		 * Filter the formatted amount before returning
		 *
		 * @since unknown
		 *
		 * @param mixed  $formatted           Formatted amount.
		 * @param mixed  $amount              Original amount.
		 * @param int    $decimals            Default 2. Number of decimals.
		 * @param string $decimal_separator   Default '.'. Decimal separator.
		 * @param string $thousands_separator Default ','. Thousands separator.
		 * @param string $currency_code       Currency used for formatting.
		 */
		$this->amount = apply_filters( 'edd_format_amount', $formatted, $amount, $decimals, $this->currency->decimal_separator, $this->currency->thousands_separator, $this->currency->code );

		return $this;
	}

	/**
	 * Applies the currency prefix/suffix to the amount.
	 *
	 * @since 3.0
	 * @return Money_Formatter
	 */
	public function apply_symbol() {
		$amount      = $this->amount;
		$is_negative = is_numeric( $this->amount ) && $this->amount < 0;

		// Remove "-" from start.
		if ( $is_negative ) {
			$amount = substr( $amount, 1 );
		}

		$formatted = '';
		if ( ! empty( $this->currency->prefix ) ) {
			$formatted .= $this->currency->prefix;
		}

		$formatted .= $amount;

		if ( ! empty( $this->currency->suffix ) ) {
			$formatted .= $this->currency->suffix;
		}

		if ( ! empty( $this->currency->prefix ) ) {
			/**
			 * Filters the output with a prefix.
			 *
			 * @param string $formatted
			 * @param string $currency_code
			 * @param string $amount
			 */
			$formatted = apply_filters( 'edd_' . strtolower( $this->currency->code ) . '_currency_filter_before', $formatted, $this->currency->code, $amount );
		}

		if ( ! empty( $this->currency->suffix ) ) {
			/**
			 * Filters the output with a suffix.
			 *
			 * @param string $formatted
			 * @param string $currency_code
			 * @param string $amount
			 */
			$formatted = apply_filters( 'edd_' . strtolower( $this->currency->code ) . '_currency_filter_after', $formatted, $this->currency->code, $amount );
		}

		// Add the "-" sign back to the start of the string.
		if ( $is_negative ) {
			$formatted = '-' . $formatted;
		}

		return $this;
	}

}
