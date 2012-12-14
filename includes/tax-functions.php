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


function edd_local_taxes_only() {
	global $edd_options;

	$local_only = isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local';

	return apply_filters( 'edd_local_taxes_only', $local_only );
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
		// convert to a number we can use
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

function edd_calculate_tax( $amount ) {

	$rate 	= edd_get_tax_rate();
	$tax 	= number_format( $amount * $rate, 2 ); // the tax amount

	return apply_filters( 'edd_taxed_amount', $tax, $rate );
}


/**
 * Stores the tax info in the payment meta
 *
 * @access      public
 * @since       1.3.3
 * @param 		$payment_meta array The meta data to store with the payment
 * @param 		$payment_data array The info sent from process-purchase.php
 * @return      array
*/

function edd_record_taxed_amount( $payment_meta, $payment_data ) {

	if( ! edd_use_taxes() )
		return $payment_meta;

	if( edd_local_taxes_only() && isset( $_POST['edd_tax_opt_in'] ) ) {

		// calculate local taxes
		$payment_meta['subtotal'] 	= edd_get_cart_amount( false );
		$payment_meta['tax'] 		= edd_get_cart_tax();

	} elseif( ! edd_local_taxes_only() ) {

		// calculate global taxes
		$payment_meta['subtotal'] 	= edd_get_cart_amount( false );
		$payment_meta['tax'] 		= edd_get_cart_tax();

	}

	return $payment_meta;

}
add_filter( 'edd_payment_meta', 'edd_record_taxed_amount', 10, 2 );


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

		// start at zero
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
