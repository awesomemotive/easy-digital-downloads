<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     EDD
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sanitize Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since 1.0
 * @param string $amount Price amount to format
 * @return string $amount Newly sanitized amount
 */
function edd_sanitize_amount( $amount ) {
	global $edd_options;

	$thousands_sep = ! empty( $edd_options['thousands_separator'] ) ? $edd_options['thousands_separator'] : '';
	$decimal_sep   = ! empty( $edd_options['decimal_separator'] )   ? $edd_options['decimal_separator'] 	 : '.';

	// Sanitize the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if ( $thousands_sep == '.' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	return apply_filters( 'edd_sanitize_amount', $amount );
}

/**
 * Returns a nicely formatted amount.
 *
 * @since 1.0
 * @param string $amount Price amount to format
 * @return string $amount Newly formatted amount or Price Not Available
 */
function edd_format_amount( $amount ) {
	global $edd_options;

	$thousands_sep 	= ! empty( $edd_options['thousands_separator'] ) ? $edd_options['thousands_separator'] : '';
	$decimal_sep 	= ! empty( $edd_options['decimal_separator'] )   ? $edd_options['decimal_separator'] 	 : '.';

	// Format the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		$whole = substr( $amount, 0, $sep_found );
		$part = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip , from the amount (if set as the thousands separator)
	if ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ',', '', $amount );
	}

	$decimals  = apply_filters( 'edd_format_amount_decimals', 2 );
	$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	return apply_filters( 'edd_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );
}


/**
 * Formats the currency display
 *
 * @since 1.0
 * @param string $price Price
 * @return array $currency Currencies displayed correctly
 */
function edd_currency_filter( $price ) {
	global $edd_options;

	$currency = edd_get_currency();
	$position = isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before';

	if ( $position == 'before' ):
		switch ( $currency ):
			case "GBP" : return '&pound;' . $price; break;
			case "BRL" : return 'R&#36;' . $price; break;
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
				return '&#36;' . $price;
			break;
			case "JPY" : return '&yen;' . $price; break;
			default :
			    $formatted = $currency . ' ' . $price;
    		    return apply_filters( 'edd_' . strtolower( $currency ) . '_currency_filter_before', $formatted, $currency, $price );
			break;
		endswitch;
	else :
		switch ( $currency ) :
			case "GBP" : return $price . '&pound;'; break;
			case "BRL" : return $price . 'R&#36;'; break;
			case "USD" :
			case "AUD" :
			case "BRL" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
				return $price . '&#36;';
			break;
			case "JPY" : return $price . '&yen;'; break;
			default :
			    $formatted = $price . ' ' . $currency;
			    return apply_filters( 'edd_' . strtolower( $currency ) . '_currency_filter_after', $formatted, $currency, $price );
			break;
		endswitch;
	endif;
}

/**
 * Set the number of decimal places per currency
 *
 * @since 1.4.2
 * @param int $decimals Number of decimal places
 * @return int $decimals
*/
function edd_currency_decimal_filter( $decimals = 2 ) {
	global $edd_options;

	$currency = edd_get_currency();

	switch ( $currency ) {
		case 'RIAL' :
			$decimals = 0;
			break;

		case 'JPY' :
			$decimals = 0;
			break;
	}

	return $decimals;
}
add_filter( 'edd_format_amount_decimals', 'edd_currency_decimal_filter' );