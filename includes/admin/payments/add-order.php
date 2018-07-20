<?php
/**
 * Add Order Page.
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Bail if incorrect view.
if ( ! isset( $_GET['view'] ) || 'add-order' !== $_GET['view'] ) { // WPCS: input var ok, CSRF ok.
	wp_die( esc_html__( 'Something went wrong.', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}

// Determine float accuracy for the steps and rounding.
$decimals = edd_currency_decimal_filter();
if ( empty( $decimals ) ) {
	$step = 1;
} else {
	$i    = 1;
	$step = '0.';
	while ( $i < $decimals ) {
		$step .= '0';
		$i++;
	}
	$step .= '1';
	$step  = (float) $step;
}

$hours   = edd_get_hour_values();
$minutes = edd_get_minute_values();

$countries = array_filter( edd_get_country_list() );
$regions   = array_filter( edd_get_shop_states( edd_get_shop_country() ) );

// Setup gateway list.
$known_gateways = edd_get_payment_gateways();

$gateways = array();

foreach ( $known_gateways as $id => $data ) {
	$gateways[ $id ] = esc_html( $data['admin_label'] );
}

// Output
?>
