<?php
/**
 * Tax Functions
 *
 * These are functions used for checking if taxes are enabled, calculating taxes, etc.
 * Functions for retrieving tax amounts and such for individual payments are in
 * includes/payment-functions.php and includes/cart-functions.php
 *
 * @package     EDD
 * @subpackage  Functions/Taxes
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if taxes are enabled by using the option set from the EDD Settings.
 * The value returned can be filtered.
 *
 * @since 1.3.3
 * @global $edd_options
 * @return bool Whether or not taxes are enabled
 */
function edd_use_taxes() {
	global $edd_options;

	return apply_filters( 'edd_use_taxes', isset( $edd_options['enable_taxes'] ) );
}

/**
 * Check if only local taxes are enabled meaning users must opt in by using the
 * option set from the EDD Settings.
 *
 * @since 1.3.3
 * @global $edd_options
 * @return bool $local_only
 */
function edd_local_taxes_only() {
	global $edd_options;

	$local_only = isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local';

	return apply_filters( 'edd_local_taxes_only', $local_only );
}

/**
 * Checks if a customer has opted into local taxes
 *
 * @since 1.4.1
 * @uses EDD_Session::get()
 * @return bool
 */
function edd_local_tax_opted_in() {
	$opted_in = EDD()->session->get( 'edd_local_tax_opt_in' );
	return ! empty( $opted_in );
}

/**
 * Sets a customer as opted into local taxes
 *
 * @since 1.4.1
 * @uses EDD_Session::get()
 * @return bool
*/
function edd_opt_into_local_taxes() {
	EDD()->session->set( 'edd_local_tax_opt_in', '1' );
}

/**
 * Sets a customer as opted out of local taxes
 *
 * @since 1.4.1
 * @uses EDD_Session::get()
 * @return bool
 */
function edd_opt_out_local_taxes() {
	EDD()->session->set( 'edd_local_tax_opt_in', null);
}

/**
 * Show taxes on individual prices?
 *
 * @since 1.4
 * @global $edd_options
 * @return bool Whether or not to show taxes on prices
 */
function edd_taxes_on_prices() {
	global $edd_options;
	return apply_filters( 'edd_taxes_on_prices', isset( $edd_options['taxes_on_prices'] ) );
}

/**
 * Checks if the user has enabled the option to calculate taxes after discounts
 * have been entered
 *
 * @since 1.4.1
 * @global $edd_options
 * @return bool Whether or not taxes are calculated after discount
 */
function edd_taxes_after_discounts() {
	global $edd_options;
	return apply_filters( 'edd_taxes_after_discounts', isset( $edd_options['taxes_after_discounts'] ) );
}

/**
 * Get taxation rate
 *
 * @since 1.3.3
 * @global $edd_options
 * @return float $trate Taxation rate
 */
function edd_get_tax_rate() {
	global $edd_options;

	$rate = isset( $edd_options['tax_rate'] ) ? (float) $edd_options['tax_rate'] : 0;

	if( $rate > 1 ) {
		// Convert to a number we can use
		$rate = $rate / 100;
	}
	return apply_filters( 'edd_tax_rate', $rate );
}

/**
 * Calculate the taxed amount
 *
 * @since 1.3.3
 * @param $amount float The original amount to calculate a tax cost
 * @return float $tax Taxed amount
 */
function edd_calculate_tax( $amount, $sum = true ) {
	global $edd_options;

	// Not using taxes
	if ( !edd_use_taxes() ) return $amount;

	$rate = edd_get_tax_rate();
	$tax = 0;
	$prices_include_tax = isset( $edd_options['prices_include_tax'] ) ? $edd_options['prices_include_tax'] : 'no';

	if ( $prices_include_tax == 'yes' ) {
		$tax = $amount - ( $amount / ( $rate + 1 ) );
	}

	if ( $prices_include_tax == 'no' ) {
		$tax = $amount * $rate;
	}

	if ( $sum ) {

		if ( $prices_include_tax == 'yes' ) {
			$tax = $amount - $tax;
		} else {
			$tax = $amount + $tax;
		}

	}

	$tax = round( $tax, 2 );
	return apply_filters( 'edd_taxed_amount', $tax, $rate );
}

/**
 * Stores the tax info in the payment meta
 *
 * @since 1.3.3
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses edd_get_sales_tax_for_year()
 * @return void
*/
function edd_sales_tax_for_year( $year = null ) {
	echo edd_currency_filter( edd_format_amount( edd_get_sales_tax_for_year( $year ) ) );
}

/**
 * Gets the sales tax for the current year
 *
 * @since 1.3.3
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses edd_get_payment_tax()
 * @return float $tax Sales tax
 */
function edd_get_sales_tax_for_year( $year = null ) {
	if ( empty( $year ) )
		return 0;

	// Start at zero
	$tax = 0;

	$args = array(
		'post_type' 		=> 'edd_payment',
		'posts_per_page' 	=> -1,
		'year' 				=> $year,
		'meta_key' 			=> '_edd_payment_mode',
		'meta_value' 		=> edd_is_test_mode() ? 'test' : 'live',
		'fields'			=> 'ids'
	);

	$payments = get_posts( $args );

	if( $payments ) :

		foreach( $payments as $payment ) :
			$tax += edd_get_payment_tax( $payment );
		endforeach;

	endif;

	return apply_filters( 'edd_get_sales_tax_for_year', $tax, $year );
}


/**
 * Checks whether the user has enabled display of taxes on the checkout
 *
 * @since 1.5
 * @global $edd_options
 * @return bool $include_tax
 */
function edd_prices_show_tax_on_checkout() {
	global $edd_options;

	$include_tax = isset( $edd_options['checkout_include_tax'] ) ? $edd_options['checkout_include_tax'] : 'no';

	return ( $include_tax == 'yes' );
}

/**
 * Check if the individual product prices include tax
 *
 * @since 1.5
 * @global $edd_options
 * @return bool $include_tax
*/
function edd_prices_include_tax() {
	global $edd_options;

	$include_tax = isset( $edd_options['prices_include_tax'] ) ? $edd_options['prices_include_tax'] : 'no';

	return ( $include_tax == 'yes' );
}

/**
 * Is the cart taxed?
 *
 * @since 1.5
 * @return bool
 */
function edd_is_cart_taxed() {
	return edd_use_taxes() && ( ( edd_local_tax_opted_in() && edd_local_taxes_only() ) || ! edd_local_taxes_only() );
}