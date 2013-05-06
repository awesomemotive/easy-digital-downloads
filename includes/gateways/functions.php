<?php
/**
 * Gateway Functions
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns a list of all available gateways.
 *
 * @since 1.0
 * @return array $gateways All the available gateways
 */
function edd_get_payment_gateways() {
	// Default, built-in gateways
	$gateways = array(
		'paypal' => array( 'admin_label' => __( 'PayPal Standard', 'edd' ), 'checkout_label' => __( 'PayPal', 'edd' ) ),
		'manual' => array( 'admin_label' => __( 'Test Payment', 'edd' ), 'checkout_label' => __( 'Test Payment', 'edd' ) ),
	);

	return apply_filters( 'edd_payment_gateways', $gateways );
}

/**
 * Returns a list of all enabled gateways.
 *
 * @since 1.0
 * @return array $gateway_list All the available gateways
*/
function edd_get_enabled_payment_gateways() {
	global $edd_options;

	$gateways = edd_get_payment_gateways();
	$enabled  = isset( $edd_options['gateways'] ) ? $edd_options['gateways'] : false;

	$gateway_list = array();

	foreach ( $gateways as $key => $gateway ) :
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) :
			$gateway_list[ $key ] = $gateway;
		endif;
	endforeach;

	return apply_filters( 'edd_enabled_payment_gateways', $gateway_list );
}

/**
 * Checks whether a specified gateway is activated.
 *
 * @since 1.0
 * @param string $gateway Name of the gateway to check for
 * @return boolean true if enabled, false otherwise
*/
function edd_is_gateway_active( $gateway ) {
	$gateways = edd_get_enabled_payment_gateways();

	if ( array_key_exists( $gateway, $gateways ) ) {
		return true;
	}

	return false;
}

/**
 * Gets the default payment gateway selected from the EDD Settings
 *
 * @since 1.5
 * @global $edd_options Array of all the EDD Options
 * @return string Gateway ID
 */
function edd_get_default_gateway() {
	global $edd_options;
	return isset( $edd_options['default_gateway'] ) && edd_is_gateway_active( $edd_options['default_gateway'] ) ? $edd_options['default_gateway'] : 'paypal';
}

/**
 * Returns the admin label for the specified gateway
 *
 * @since 1.0.8.3
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Gateway admin label
 */
function edd_get_gateway_admin_label( $gateway ) {
	$gateways = edd_get_enabled_payment_gateways();
	return isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
}

/**
 * Returns the checkout label for the specified gateway
 *
 * @since 1.0.8.5
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Checkout label for the gateway
 */
function edd_get_gateway_checkout_label( $gateway ) {
	$gateways = edd_get_enabled_payment_gateways();
	return isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;
}

/**
 * Sends all the payment data to the specified gateway
 *
 * @since 1.0
 * @param string $gateway Name of the gateway
 * @param array $payment_data All the payment data to be sent to the gateway
 * @return void
*/
function edd_send_to_gateway( $gateway, $payment_data ) {
	// $gateway must match the ID used when registering the gateway
	do_action( 'edd_gateway_' . $gateway, $payment_data );
}

/**
 * Determines if the gateway menu should be shown
 *
 * If the cart amount is zero, no option is shown and the cart uses the manual gateway
 * to emulate a no-gateway-setup for a free download
 *
 * @since 1.3.2
 * @return bool $show_gateways Whether or not to show the gateways
 */
function edd_show_gateways() {
	$gateways = edd_get_enabled_payment_gateways();
	$show_gateways = false;

	if ( count( $gateways ) > 1 && ! isset( $_GET['payment-mode'] ) ) {
		$show_gateways = true;
		if ( edd_get_cart_amount() <= 0 ) {
			$show_gateways = false;
		}
	}

	return apply_filters( 'edd_show_gateways', $show_gateways );
}

/**
 * Determines what the currently selected gateway is
 *
 * If the cart amount is zero, no option is shown and the cart uses the manual
 * gateway to emulate a no-gateway-setup for a free download
 *
 * @access public
 * @since 1.3.2
 * @return string $enabled_gateway The slug of the gateway
 */
function edd_get_chosen_gateway() {
	$gateways = edd_get_enabled_payment_gateways();

	if ( isset( $_GET['payment-mode'] ) ) {
		$enabled_gateway = urldecode( $_GET['payment-mode'] );
	} else if( count( $gateways ) >= 1 && ! isset( $_GET['payment-mode'] ) ) {
		foreach ( $gateways as $gateway_id => $gateway ):
			$enabled_gateway = $gateway_id;
			if ( edd_get_cart_amount() <= 0 ) {
				$enabled_gateway = 'manual'; // This allows a free download by filling in the info
			}
		endforeach;
	} else if ( edd_get_cart_amount() <= 0 ) {
		$enabled_gateway = 'manual';
	} else {
		$enabled_gateway = 'none';
	}

	return apply_filters( 'edd_chosen_gateway', $enabled_gateway );
}

/**
 * Record a gateway error
 *
 * A simple wrapper function for edd_record_log()
 *
 * @access public
 * @since 1.3.3
 * @param string $title Title of the log entry (default: empty)
 * @param string $message  Message to store in the log entry (default: empty)
 * @param int $parent Parent log entry (default: 0)
 * @return int ID of the new log entry
 */
function edd_record_gateway_error( $title = '', $message = '', $parent = 0 ) {
	return edd_record_log( $title, $message, $parent, 'gateway_error' );
}

/**
 * Sets an error on checkout if no gateways are enabled
 *
 * @since 1.3.4
 * @return void
 */
function edd_no_gateway_error() {
	$gateways = edd_get_enabled_payment_gateways();

	if ( empty( $gateways ) )
		edd_set_error( 'no_gateways', __( 'You must enable a payment gateway to use Easy Digital Downloads', 'edd' ) );
	else
		edd_unset_error( 'no_gateways' );
}
add_action( 'init', 'edd_no_gateway_error' );

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


/**
 * Counts the number of purchases made with a gateway
 *
 * @since 1.6
 * @return int
 */
function edd_count_sales_by_gateway( $gateway_id = 'paypal', $status = 'publish' ) {

	$ret  = 0;
	$args = array(
		'meta_key'    => '_edd_payment_gateway',
		'meta_value'  => $gateway_id,
		'nopaging'    => true,
		'post_type'   => 'edd_payment',
		'post_status' => $status,
		'fields'      => 'ids'
	);

	$payments = new WP_Query( $args );

	if( $payments )
		$ret = $payments->post_count;
	return $ret;
}