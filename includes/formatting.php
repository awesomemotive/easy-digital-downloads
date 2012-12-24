<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     Easy Digital Downloads
 * @subpackage  Formatting functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Sanitize Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @access      public
 * @since       1.0
 * @param       $amount string the price amount to format
 * @return      string - the newly sanitize amount
*/

function edd_sanitize_amount( $amount ) {
	global $edd_options;

	$thousands_sep = isset( $edd_options['thousands_separator'] ) ? $edd_options['thousands_separator'] : ',';
	$decimal_sep   = isset( $edd_options['decimal_separator'] )   ? $edd_options['decimal_separator'] 	 : '.';

	// sanitize the amount
	if( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {

		if( $thousands_sep == '.' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );

		// make sure we don't have more than 2 decimals
		$amount = number_format( $amount, 2 );
	}

	return apply_filters( 'edd_sanitize_amount', $amount );
}


/**
 * Format Amount
 *
 * Returns a nicely formatted amount.
 *
 * @access      public
 * @since       1.0
 * @param       $amount string the price amount to format
 * @return      string - the newly formatted amount
*/

function edd_format_amount( $amount ) {
	global $edd_options;

	$thousands_sep 	= isset( $edd_options['thousands_separator'] ) ? $edd_options['thousands_separator'] : ',';
	$decimal_sep 	= isset( $edd_options['decimal_separator'] )   ? $edd_options['decimal_separator'] 	 : '.';

	// format the amount
	if( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		$whole = substr( $amount, 0, $sep_found );
		$part = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	$decimals = apply_filters( 'edd_format_amount_decimals', 2 );

	return number_format( $amount, $decimals, $decimal_sep, $thousands_sep );
}



/**
 * Formats the currency display
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_currency_filter( $price ) {
	global $edd_options;

	$currency = isset( $edd_options['currency'] ) ? $edd_options['currency'] : 'USD';
	$position = isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before';

	if( $position == 'before' ):
		switch ( $currency ):
			case "GBP" : return '&pound;' . $price; break;
			case "USD" :
			case "AUD" :
			case "BRL" :
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