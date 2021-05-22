<?php
/**
 * Currency
 *
 * Used for formatting currency for display.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.0
 */

namespace EDD\Utils;


class Currency {

	/**
	 * @var float
	 */
	private $amount;

	/**
	 * @var string
	 */
	private $currency;

	/**
	 * Currency constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		$this->amount   = isset( $args['amount'] ) ? $args['amount'] : 0.00;
		$this->currency = isset( $args['currency'] ) ? $args['currency'] : edd_get_currency();
	}

	public static function display( $amount, $currency ) {
		return Currency::apply_symbol( Currency::format( $amount, $currency ), $currency );
	}

	/**
	 * Formats a currency for display.
	 *
	 * @since 3.0
	 *
	 * @param int|float $amount   Amount.
	 * @param string    $currency Currency code.
	 * @param bool      $decimals Whether or not to use decimals. If `true`, numbers are formatted to 2 decimal
	 *                            places. If `false`, numbers are formatted to 0 decimals.
	 *
	 * @return float|int
	 */
	public static function format( $amount, $currency, $decimals = true ) {
		// Get separators
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );
		$thousands_sep = edd_get_option( 'thousands_separator', ',' );

		// Format the amount
		if ( $decimal_sep === ',' && false !== ( $sep_found = strpos( $amount, $decimal_sep ) ) ) {
			$whole  = substr( $amount, 0, $sep_found );
			$part   = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
			$amount = $whole . '.' . $part;
		}

		// Strip , from the amount (if set as the thousands separator)
		if ( $thousands_sep === ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( ',', '', $amount );
		}

		// Strip ' ' from the amount (if set as the thousands separator)
		if ( $thousands_sep === ' ' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
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
		 * @param int        $number   Default 2. Number of decimals.
		 * @param int|string $amount   Amount being formatted.
		 * @param string     $currency Currency being formatted.
		 */
		$decimals = apply_filters( 'edd_format_amount_decimals', $decimals ? 2 : 0, $amount, $currency );

		// Format amount using decimals and separators (also rounds up or down)
		$formatted = number_format( (float) $amount, $decimals, $decimal_sep, $thousands_sep );

		/**
		 * Filter the formatted amount before returning
		 *
		 * @since unknown
		 *
		 * @param mixed  $formatted     Formatted amount.
		 * @param mixed  $amount        Original amount.
		 * @param int    $decimals      Default 2. Number of decimals.
		 * @param string $decimal_sep   Default '.'. Decimal separator.
		 * @param string $thousands_sep Default ','. Thousands separator.
		 * @param string $currency      Currency used for formatting.
		 */
		return apply_filters( 'edd_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep, $currency );
	}

	/**
	 * Returns the symbol for the provided currency.
	 *
	 * @since 3.0
	 *
	 * @param string $currency
	 *
	 * @return string
	 */
	public static function symbol( $currency ) {
		switch ( $currency ) :
			case "GBP" :
				$symbol = '&pound;';
				break;
			case "BRL" :
				$symbol = 'R&#36;';
				break;
			case "EUR" :
				$symbol = '&euro;';
				break;
			case "USD" :
			case "AUD" :
			case "NZD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
				$symbol = '&#36;';
				break;
			case "JPY" :
				$symbol = '&yen;';
				break;
			case "AOA" :
				$symbol = 'Kz';
				break;
			default :
				$symbol = $currency;
				break;
		endswitch;

		/**
		 * Filters the currency symbol.
		 *
		 * @since unknown
		 *
		 * @param string $symbol
		 * @param string $currency
		 */
		return apply_filters( 'edd_currency_symbol', $symbol, $currency );
	}

	/**
	 * Applies the currency symbol before or after the amount.
	 *
	 * @since 3.0
	 *
	 * @param int|float|string $amount
	 * @param string           $currency
	 *
	 * @return string
	 */
	public static function apply_symbol( $amount, $currency ) {
		$position = edd_get_option( 'currency_position', 'before' );
		$negative = is_numeric( $amount ) && $amount < 0;

		// Remove proceeding "-" -
		if ( true === $negative ) {
			$amount = substr( $amount, 1 );
		}

		$symbol = self::symbol( $currency );

		if ( 'before' === $position ) {
			switch ( $currency ):
				case 'GBP' :
				case 'BRL' :
				case 'EUR' :
				case 'USD' :
				case 'AUD' :
				case 'CAD' :
				case 'HKD' :
				case 'MXN' :
				case 'NZD' :
				case 'SGD' :
				case 'JPY' :
					$formatted = $symbol . $amount;
					break;
				default :
					$formatted = $currency . ' ' . $amount;
					break;
			endswitch;
			$formatted = apply_filters( 'edd_' . strtolower( $currency ) . '_currency_filter_before', $formatted, $currency, $amount );
		} else {
			switch ( $currency ) :
				case 'GBP' :
				case 'BRL' :
				case 'EUR' :
				case 'USD' :
				case 'AUD' :
				case 'CAD' :
				case 'HKD' :
				case 'MXN' :
				case 'SGD' :
				case 'JPY' :
					$formatted = $amount . $symbol;
					break;
				default :
					$formatted = $amount . ' ' . $currency;
					break;
			endswitch;
			$formatted = apply_filters( 'edd_' . strtolower( $currency ) . '_currency_filter_after', $formatted, $currency, $amount );
		}

		// Prepend the mins sign before the currency sign
		if ( true === $negative ) {
			$formatted = '-' . $formatted;
		}

		return $formatted;
	}

}
