<?php

/**
 * Checks if taxes are enabled
 *
 * @access      public
 * @since       1.3.3
 * @return      bool
*/

function edd_use_taxes() {
	global $edd_options;

	return isset( $edd_options['enable_taxes'] );
}


function edd_local_taxes_only() {
	global $edd_options;

	return isset( $edd_options['tax_condition'] ) && $edd_options['tax_condition'] == 'local';
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
	$tax 	= $amount * $rate; // the tax amount

	return apply_filters( 'edd_taxed_amount', $tax, $rate );
}
