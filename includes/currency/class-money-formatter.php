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
	 * @var float Typed amount.
	 */
	public $typed_amount;

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
		$this->original_amount = $amount;
		$this->amount          = $amount;
		$this->typed_amount    = $amount;
		$this->currency        = $currency;
	}

	/**
	 * Un-formats an amount.
	 * This ensures the amount is put into a state where we can perform mathematical
	 * operations on it --- that means using `.` as the decimal separator and no
	 * thousands separator.
	 *
	 * @return float|int
	 */
	private function unformat() {
		$amount = $this->original_amount;

		$sep_found = strpos( $amount, $this->currency->decimal_separator );
		if ( ',' === $this->currency->decimal_separator && false !== $sep_found ) {
			$whole  = substr( $amount, 0, $sep_found );
			$part   = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
			$amount = $whole . '.' . $part;
		}

		// Strip "," and " " from the amount (if set as the thousands separator).
		foreach ( array( ',', ' ' ) as $thousands_separator ) {
			if ( $thousands_separator === $this->currency->thousands_separator && false !== strpos( $amount, $this->currency->thousands_separator ) ) {
				$amount = str_replace( $thousands_separator, '', $amount );
			}
		}

		return $amount;
	}

	/**
	 * Returns the number of decimals ot use for the formatted amount.
	 *
	 * Based on the currency code used when instantiating the class, determines how many
	 * decimal points the value should have once formatted.
	 *
	 * @param bool  $decimals If we should include decimals or not in the formatted amount.
	 * @param float $amount   The amount to format.
	 *
	 * @return int  The number of decimals places to use when formatting the amount.
	 */
	private function get_decimals( $decimals, $amount ) {
		/**
		 * Filter number of decimals to use for formatted amount
		 *
		 * @since unknown
		 *
		 * @param int        $number        Default 2. Number of decimals.
		 * @param int|string $amount        Amount being formatted.
		 * @param string     $currency_code Currency code being formatted.
		 */
		return apply_filters( 'edd_format_amount_decimals', $decimals ? $this->currency->number_decimals : 0, $amount, $this->currency->code );
	}

	/**
	 * Formats the amount for display.
	 * Does not apply the currency code.
	 *
	 * @since 3.0
	 *
	 * @param bool $decimals If we should include decimal places or not when formatting.
	 *
	 * @return Money_Formatter
	 */
	public function format_for_display( $decimals = true ) {
		$amount = $this->unformat();

		if ( empty( $amount ) ) {
			$amount = 0;
		}

		$decimals = $this->get_decimals( $decimals, $amount );

		// Format amount using decimals and separators (also rounds up or down).
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
	 * Formats the amount for typed data returns.
	 * Does not apply the currency code and returns a foat instead of a string.
	 *
	 * @since 3.0
	 *
	 * @param bool $decimals If we should include decimal places or not when formatting.
	 *
	 * @return Money_Formatter
	 */
	public function format_for_typed( $decimals = true ) {
		$amount = $this->unformat();

		if ( empty( $amount ) ) {
			$amount = 0;
		}

		$decimals = $this->get_decimals( $decimals, $amount );

		/**
		 * Since we want to return a float value here, intentionally only supply a decimal separator.
		 *
		 * The separators here are hard coded intentionally as we're looking to get truncated, raw format of float
		 * which requires '.' for decimal separators and no thousands separator.
		 *
		 * This is also intentionally not filtered for the time being.
		 */
		$formatted = floatval( number_format( (float) $amount, $decimals, '.', '' ) );

		// Set the amount to $this->amount.
		$this->typed_amount = $formatted;

		return $this;
	}

	/**
	 * Applies the currency prefix/suffix to the amount.
	 *
	 * @since 3.0
	 * @return string
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

		return $formatted;
	}

}
