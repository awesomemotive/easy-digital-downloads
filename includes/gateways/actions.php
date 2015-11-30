<?php
/**
 * Gateway Actions
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes gateway select on checkout. Only for users without ajax / javascript
 *
 * @since 1.7
 *
 * @param $data
 */
function edd_process_gateway_select( $data ) {
	if( isset( $_POST['gateway_submit'] ) ) {
		wp_redirect( add_query_arg( 'payment-mode', $_POST['payment-mode'] ) ); exit;
	}
}
add_action( 'edd_gateway_select', 'edd_process_gateway_select' );

/**
 * Loads a payment gateway via AJAX
 *
 * @since 1.3.4
 * @return void
 */
function edd_load_ajax_gateway() {
	if ( isset( $_POST['edd_payment_mode'] ) ) {
		do_action( 'edd_purchase_form' );
		exit();
	}
}
add_action( 'wp_ajax_edd_load_gateway', 'edd_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_edd_load_gateway', 'edd_load_ajax_gateway' );