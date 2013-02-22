<?php
/**
 * Tax Functions
 *
 * These are functions used for checking if taxes are enabled, calculating taxes, etc.
 * Functions for retrieving tax amounts and such for individual payments are in
 * includes/payment-functions.php and includes/cart-functions.php
 *
 * @package     Easy Digital Downloads
 * @subpackage  Payment Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if taxes are enabled
 *
 * @access      public
 * @since       1.3.3
 * @return      bool
*/

function edd_use_taxes() {
	global $edd_options;

	return apply_filters( 'edd_use_taxes', isset( $edd_options['enable_taxes'] ) );
}


/**
 * Local taxes only meaning users must opt in
 *
 * @access      public
 * @since       1.3.3
 * @return      bool
*/

function edd_local_taxes_only() {
	global $edd_options;

	$local_only = isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local';

	return apply_filters( 'edd_local_taxes_only', $local_only );
}


/**
 * Checks if a customer has opted into local taxes
 *
 * @access      public
 * @since       1.4.1
 * @return      bool
*/

function edd_local_tax_opted_in() {
	return !empty( $_SESSION['wordpress_edd_local_tax_opt_in'] );
}


/**
 * Sets a customer as opted into local taxes
 *
 * @access      public
 * @since       1.4.1
 * @return      bool
*/

function edd_opt_into_local_taxes() {
	return $_SESSION['wordpress_edd_local_tax_opt_in'] = true;
}


/**
 * Sets a customer as opted out of local taxes
 *
 * @access      public
 * @since       1.4.1
 * @return      bool
*/

function edd_opt_out_local_taxes() {
	return $_SESSION['wordpress_edd_local_tax_opt_in'] = false;
}


/**
 * Show taxes on individual prices?
 *
 * @access      public
 * @since       1.4
 * @return      bool
*/

function edd_taxes_on_prices() {
	global $edd_options;
	return apply_filters( 'edd_taxes_on_prices', isset( $edd_options['taxes_on_prices'] ) );
}


/**
 * Calculate taxes before or after discounts?
 *
 * @access      public
 * @since       1.4.1
 * @return      bool
*/

function edd_taxes_after_discounts() {
	global $edd_options;
	return apply_filters( 'edd_taxes_after_discounts', isset( $edd_options['taxes_after_discounts'] ) );
}


/**
 * Get taxation rate
 *
 * @access      public
 * @since       1.3.3
 * @return      float
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
 * Calculate taxed amount
 *
 * @access      public
 * @since       1.3.3
 * @param 		$amount float The original amount to calculate a tax cost
 * @return      float
*/

function edd_calculate_tax( $amount, $sum = true ) {
	global $edd_options;

	// Not using taxes
	if ( !edd_use_taxes() ) return $amount;

	$rate = edd_get_tax_rate();
	$tax = 0;

	if ( $edd_options['prices_include_tax'] == 'yes' ) {
		$tax = $amount - ( $amount / ( $rate + 1 ) );
	}

	if ( $edd_options['prices_include_tax'] == 'no' ) {
		$tax = $amount * $rate;
	}

	if ( $sum ) {

		if ( $edd_options['prices_include_tax'] == 'yes' ) {
			$tax = $amount - $tax;
		} else {
			$tax = $amount + $tax;
		}

	}

	return apply_filters( 'edd_taxed_amount', $tax, $rate );
}

/**
 * Stores the tax info in the payment meta
 *
 * @access      public
 * @since       1.3.3
 * @param 		$year int The year to retrieve taxes for, i.e. 2012
 * @uses 		edd_currency_filter()
 * @uses 		edd_format_amount()
 * @uses 		edd_get_sales_tax_for_year()
 * @return      void
*/

function edd_sales_tax_for_year( $year = null ) {
	echo edd_currency_filter( edd_format_amount( edd_get_sales_tax_for_year( $year ) ) );
}

/**
 * Stores the tax info in the payment meta
 *
 * @access      public
 * @since       1.3.3
 * @param 		$year int The year to retrieve taxes for, i.e. 2012
 * @uses 		edd_get_payment_tax()
 * @return      float
*/

function edd_get_sales_tax_for_year( $year = null ) {

	if( empty( $year ) )
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

function edd_is_include_tax() {
	global $edd_options;

	return ( $edd_options['checkout_include_tax'] == 'yes' );
}
function edd_is_exclude_tax() {
	global $edd_options;

	return ( $edd_options['checkout_include_tax'] == 'no' );
}

function edd_is_cart_taxed() {
	return edd_use_taxes() && ( ( edd_local_tax_opted_in() && edd_local_taxes_only() ) || !edd_local_taxes_only() );
}