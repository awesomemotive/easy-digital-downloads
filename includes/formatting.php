<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     EDD
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Sanitize a numeric value.
 *
 * Use this function to "unformat" a previously formatted numeric value.
 *
 * (Most commonly, this is when accepting input from a form field where the
 * value is likely to derived from the site or user preferences.)
 *
 * @since 1.0
 *
 * @param mixed $amount Default 0. Numeric amount to sanitize.
 *
 * @return string $amount Newly sanitized amount.
 */
function edd_sanitize_amount( $amount = 0 ) {

	// Get separators
	$decimal_sep   = edd_get_option( 'decimal_separator',   '.' );
	$thousands_sep = edd_get_option( 'thousands_separator', ',' );

	// Look for separators in amount
	$found_decimal   = strpos( $amount, $decimal_sep   );
	$found_thousands = strpos( $amount, $thousands_sep );

	// Amount contains comma as decimal separator
	if ( ( $decimal_sep === ',' ) && ( false !== $found_decimal ) ) {

		// Amount contains period or space as thousands separator
		if ( in_array( $thousands_sep, array( '.', ' ' ), true ) && ( false !== $found_thousands ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );

		// Amount contains period
		} elseif ( empty( $thousands_sep ) && ( false !== strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );

	// Amount contains comma as thousands separator
	} elseif ( ( $thousands_sep === ',' ) && ( false !== $found_thousands ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	// Remove anything that's not a number, period, or negative sign.
	$amount = preg_replace( '/[^0-9\.\-]/', '', $amount );

	// Check if negative.
	$negative_exponent = ( $amount < 0 )
		? -1
		: 1;

	// Cast the amount to an absolute value.
	$amount = '' === $amount ? 0 : abs( (float) $amount );

	/**
	 * Filter number of decimals to use for sanitized amount
	 *
	 * @since unknown
	 *
	 * @param int        $number Default 2. Number of decimals.
	 * @param int|string $amount Amount being sanitized.
	 */
	$decimals = apply_filters( 'edd_sanitize_amount_decimals', 2, $amount );

	// Flip back to negative
	$sanitized = $amount * $negative_exponent;

	// Format amount using decimals and a period for the decimal separator
	// (no thousands separator; also rounds up or down)
	$sanitized = number_format( (float) $sanitized, $decimals, '.', '' );

	/**
	 * Filter the sanitized amount before returning
	 *
	 * @since unknown
	 *
	 * @param mixed  $sanitized     Sanitized amount.
	 * @param mixed  $amount        Original amount.
	 * @param int    $decimals      Default 2. Number of decimals.
	 * @param string $decimal_sep   Default '.'. Decimal separator.
	 * @param string $thousands_sep Default ','. Thousands separator.
	 */
	return apply_filters( 'edd_sanitize_amount', $sanitized, $amount, $decimals, $decimal_sep, $thousands_sep );
}

/**
 * Format a numeric value.
 *
 * Uses the decimal & thousands separator settings, and the number of decimals,
 * to format any numeric value.
 *
 * (Most commonly, this is used to apply site or user preferences to a numeric
 * value for output to the page.)
 *
 * @since 1.0
 * @since 3.0 Added `$currency` parameter.
 *
 * @param mixed  $amount   Default 0. Numeric amount to format.
 * @param string $decimals Default true. Whether or not to use decimals. Useful when set to false for non-currency numbers.
 * @param string $currency Currency code to format the amount for. This determines how many decimals are used.
 *                         If omitted, site-wide currency is used.
 * @param string $context  Defines the context in which we are formatting the data (formatted), for display or for data useage like API (typed).
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function edd_format_amount( $amount = 0, $decimals = true, $currency = '', $context = 'display' ) {
	if ( empty( $currency ) ) {
		$currency = edd_get_currency();
	}

	$formatter = new \EDD\Currency\Money_Formatter( $amount, new \EDD\Currency\Currency( $currency ) );

	switch ( $context ) {
		case 'typed':
			$return_value = $formatter->format_for_typed( $decimals )->typed_amount;
			break;
		case 'display':
		default:
			$return_value = $formatter->format_for_display( $decimals )->amount;
			break;
	}

	return $return_value;
}

/**
 * Formats the currency display
 *
 * @since 1.0
 *
 * @param string $price    Price. This should already be formatted.
 * @param string $currency Currency code. When this function is used on an order's amount, the order's currency
 *                         should always be provided here. If omitted, the store currency is used instead.
 *                         But to ensure immutability with orders, the currency should always be explicitly provided
 *                         if known and tied to an existing order.
 *
 * @return string $currency Currencies displayed correctly
 */
function edd_currency_filter( $price = '', $currency = '' ) {

	// Fallback to default currency
	if ( empty( $currency ) ) {
		$currency = edd_get_currency();
	}

	$currency = new \EDD\Currency\Currency( $currency );
	if ( '' === $price ) {
		return $currency->symbol;
	}

	$formatter = new \EDD\Currency\Money_Formatter( $price, $currency );

	return $formatter->apply_symbol();
}

/**
 * Set the number of decimal places per currency
 *
 * @since 1.4.2
 * @since 3.0 Updated to allow currency to be passed in.
 *
 * @param int    $decimals Number of decimal places.
 * @param string $currency Currency.
 *
 * @return int $decimals Number of decimal places for currency.
*/
function edd_currency_decimal_filter( $decimals = 2, $currency = '' ) {
	$currency = empty( $currency )
		? edd_get_currency()
		: $currency;

	switch ( $currency ) {
		case 'RIAL' :
		case 'JPY' :
		case 'TWD' :
		case 'HUF' :
			$decimals = 0;
			break;
	}

	return apply_filters( 'edd_currency_decimal_count', $decimals, $currency );
}
add_filter( 'edd_sanitize_amount_decimals', 'edd_currency_decimal_filter' );
add_filter( 'edd_format_amount_decimals',   'edd_currency_decimal_filter', 10, 2 );

/**
 * Sanitizes a string key for EDD Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes,
 * underscores, stops, colons and slashes are allowed.
 *
 * This differs from `sanitize_key()` in that it allows uppercase letters,
 * stops, colons, and slashes.
 *
 * @since  2.5.8
 * @param  string $key String key
 * @return string Sanitized key
 */
function edd_sanitize_key( $key = '' ) {
	$raw_key = $key;
	$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	/**
	 * Filter a sanitized key string.
	 *
	 * @since 2.5.8
	 * @param string $key     Sanitized key.
	 * @param string $raw_key The key prior to sanitization.
	 */
	return apply_filters( 'edd_sanitize_key', $key, $raw_key );
}

/**
 * Never let a numeric value be less than zero.
 *
 * Adapted from bbPress.
 *
 * @since 3.0
 *
 * @param int $number Default 0.
 * @return int.
 */
function edd_number_not_negative( $number = 0 ) {

	// Protect against formatted strings
	if ( is_string( $number ) ) {
		$number = strip_tags( $number );                    // No HTML
		$number = preg_replace( '/[^0-9-]/', '', $number ); // No number-format
		// Protect against objects, arrays, scalars, etc...
	} elseif ( ! is_numeric( $number ) ) {
		$number = 0;
	}

	// Make the number an integer
	$casted_number = is_float( $number )
		? floatval( $number )
		: intval( $number );

	$max_value = is_float( $number )
		? 0.00
		: 0;

	// Pick the maximum value, never less than zero
	$not_less_than_zero = max( $max_value, $casted_number );

	// Filter & return
	return (int) apply_filters( 'edd_number_not_negative', $not_less_than_zero, $casted_number, $number );
}

/**
 * Return array of allowed HTML tags.
 *
 * Used with wp_kses() to filter unsafe HTML out of settings and notes.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_allowed_tags() {
	return (array) apply_filters( 'edd_allowed_html_tags', array(
		'p'      => array(
			'class' => array(),
			'id'    => array(),
		),
		'span'   => array(
			'class' => array(),
			'id'    => array(),
		),
		'a' => array(
			'href'   => array(),
			'target' => array(),
			'title'  => array(),
			'class'  => array(),
			'id'     => array(),
		),
		'code'   => array(),
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
		'img'    => array(
			'src'   => array(),
			'title' => array(),
			'alt'   => array(),
			'id'    => array(),
		),
		'div'    => array(
			'class' => array(),
			'id'    => array(),
		),
		'ul'     => array(
			'class' => array(),
			'id'    => array(),
		),
		'li'     => array(
			'class' => array(),
			'id'    => array(),
		),
	) );
}

/**
 * Return a translatable and display ready string for an address type.
 *
 * @since 3.0
 * @param string $address_type The type of address to get the display label for.
 *
 * @return string              The translatable string for the display type, in lowercase.
 */
function edd_get_address_type_label( $address_type = 'billing' ) {

	// Core default address types and their labels.
	$address_type_labels = array(
		'billing' => __( 'Billing', 'easy-digital-downloads' ),
	);

	/**
	 * Physical address type labels.
	 *
	 * A key/value array of billing types found in the 'type' column of the customer address table, and their translatable
	 * strings for output.
	 *
	 * @since 3.0
	 * @param array $address_type_labels
	 *     Array of the address type labels, in key/value form. The key should match the database entry for the
	 *         wp_edd_customer_addresses table in the 'type' column. The value of each array entry should be a translatable
	 *         string for output in the UI.
	 */
	$address_type_labels = apply_filters( 'edd_address_type_labels', $address_type_labels );

	// Fallback to just applying an upper case to any words not in the filter.
	return array_key_exists( $address_type, $address_type_labels ) ?
		$address_type_labels[ $address_type ] :
		$address_type;

}
