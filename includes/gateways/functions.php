<?php
/**
 * Gateway Functions
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
		'paypal' => array(
			'admin_label'    => __( 'PayPal Standard', 'easy-digital-downloads' ),
			'checkout_label' => __( 'PayPal', 'easy-digital-downloads' ),
			'supports'       => array( 'buy_now' )
		),
		'manual' => array(
			'admin_label'    => __( 'Test Payment', 'easy-digital-downloads' ),
			'checkout_label' => __( 'Test Payment', 'easy-digital-downloads' )
		),
	);

	return apply_filters( 'edd_payment_gateways', $gateways );
}

/**
 * Returns a list of all enabled gateways.
 *
 * @since 1.0
 * @param  bool $sort If true, the default gateway will be first
 * @return array $gateway_list All the available gateways
*/
function edd_get_enabled_payment_gateways( $sort = false ) {
	$gateways = edd_get_payment_gateways();
	$enabled  = (array) edd_get_option( 'gateways', false );

	$gateway_list = array();

	foreach ( $gateways as $key => $gateway ) {
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
			$gateway_list[ $key ] = $gateway;
		}
	}

	if ( true === $sort ) {
		// Reorder our gateways so the default is first
		$default_gateway_id = edd_get_default_gateway();

		if( edd_is_gateway_active( $default_gateway_id ) ) {

			$default_gateway    = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
			unset( $gateway_list[ $default_gateway_id ] );

			$gateway_list = array_merge( $default_gateway, $gateway_list );

		}

	}

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
	$ret = array_key_exists( $gateway, $gateways );
	return apply_filters( 'edd_is_gateway_active', $ret, $gateway, $gateways );
}

/**
 * Gets the default payment gateway selected from the EDD Settings
 *
 * @since 1.5
 * @return string Gateway ID
 */
function edd_get_default_gateway() {
	$default = edd_get_option( 'default_gateway', 'paypal' );

	if( ! edd_is_gateway_active( $default ) ) {
		$gateways = edd_get_enabled_payment_gateways();
		$gateways = array_keys( $gateways );
		$default  = reset( $gateways );
	}

	return apply_filters( 'edd_default_gateway', $default );
}

/**
 * Returns the admin label for the specified gateway
 *
 * @since 1.0.8.3
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Gateway admin label
 */
function edd_get_gateway_admin_label( $gateway ) {
	$gateways = edd_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
	$payment  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;

	if( $gateway == 'manual' && $payment ) {
		if( edd_get_payment_amount( $payment ) == 0 ) {
			$label = __( 'Free Purchase', 'easy-digital-downloads' );
		}
	}

	return apply_filters( 'edd_gateway_admin_label', $label, $gateway );
}

/**
 * Returns the checkout label for the specified gateway
 *
 * @since 1.0.8.5
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Checkout label for the gateway
 */
function edd_get_gateway_checkout_label( $gateway ) {
	$gateways = edd_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;

	if( $gateway == 'manual' ) {
		$label = __( 'Free Purchase', 'easy-digital-downloads' );
	}

	return apply_filters( 'edd_gateway_checkout_label', $label, $gateway );
}

/**
 * Returns the options a gateway supports
 *
 * @since 1.8
 * @param string $gateway ID of the gateway to retrieve a label for
 * @return array Options the gateway supports
 */
function edd_get_gateway_supports( $gateway ) {
	$gateways = edd_get_enabled_payment_gateways();
	$supports = isset( $gateways[ $gateway ]['supports'] ) ? $gateways[ $gateway ]['supports'] : array();
	return apply_filters( 'edd_gateway_supports', $supports, $gateway );
}

/**
 * Checks if a gateway supports buy now
 *
 * @since 1.8
 * @param string $gateway ID of the gateway to retrieve a label for
 * @return bool
 */
function edd_gateway_supports_buy_now( $gateway ) {
	$supports = edd_get_gateway_supports( $gateway );
	$ret = in_array( 'buy_now', $supports );
	return apply_filters( 'edd_gateway_supports_buy_now', $ret, $gateway );
}

/**
 * Checks if an enabled gateway supports buy now
 *
 * @since 1.8
 * @return bool
 */
function edd_shop_supports_buy_now() {
	$gateways = edd_get_enabled_payment_gateways();
	$ret      = false;

	if( ! edd_use_taxes()  && $gateways && 1 === count( $gateways ) ) {
		foreach( $gateways as $gateway_id => $gateway ) {
			if( edd_gateway_supports_buy_now( $gateway_id ) ) {
				$ret = true;
				break;
			}
		}
	}

	return apply_filters( 'edd_shop_supports_buy_now', $ret );
}

/**
 * Build the purchase data for a straight-to-gateway purchase button
 *
 * @since 1.7
 *
 * @param int   $download_id
 * @param array $options
 * @param int   $quantity
 * @return mixed|void
 */
function edd_build_straight_to_gateway_data( $download_id = 0, $options = array(), $quantity = 1 ) {

	$price_options = array();

	if( empty( $options ) || ! edd_has_variable_prices( $download_id ) ) {
		$price = edd_get_download_price( $download_id );
	} else {

		if( is_array( $options['price_id'] ) ) {
			$price_id = $options['price_id'][0];
		} else {
			$price_id = $options['price_id'];
		}

		$prices = edd_get_variable_prices( $download_id );

		// Make sure a valid price ID was supplied
		if( ! isset( $prices[ $price_id ] ) ) {
			wp_die( __( 'The requested price ID does not exist.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 404 ) );
		}

		$price_options = array(
			'price_id' => $price_id,
			'amount'   => $prices[ $price_id ]['amount']
		);
		$price  = $prices[ $price_id ]['amount'];
	}

	// Set up Downloads array
	$downloads = array(
		array(
			'id'      => $download_id,
			'options' => $price_options
		)
	);

	// Set up Cart Details array
	$cart_details = array(
		array(
			'name'        => get_the_title( $download_id ),
			'id'          => $download_id,
			'item_number' => array(
				'id'      => $download_id,
				'options' => $price_options
			),
			'tax'         => 0,
			'discount'    => 0,
			'item_price'  => $price,
			'subtotal'    => ( $price * $quantity ),
			'price'       => ( $price * $quantity ),
			'quantity'    => $quantity,
		)
	);

	if( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
	}


	// Setup user information
	$user_info = array(
		'id'         => is_user_logged_in() ? get_current_user_id()         : -1,
		'email'      => is_user_logged_in() ? $current_user->user_email     : '',
		'first_name' => is_user_logged_in() ? $current_user->user_firstname : '',
		'last_name'  => is_user_logged_in() ? $current_user->user_lastname  : '',
		'discount'   => 'none',
		'address'    => array()
	);

	// Setup purchase information
	$purchase_data = array(
		'downloads'    => $downloads,
		'fees'         => edd_get_cart_fees(),
		'subtotal'     => $price * $quantity,
		'discount'     => 0,
		'tax'          => 0,
		'price'        => $price * $quantity,
		'purchase_key' => strtolower( md5( uniqid() ) ),
		'user_email'   => $user_info['email'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'user_info'    => $user_info,
		'post_data'    => array(),
		'cart_details' => $cart_details,
		'gateway'      => 'paypal',
		'buy_now'      => true,
		'card_info'    => array()
	);

	return apply_filters( 'edd_straight_to_gateway_purchase_data', $purchase_data );

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

	$payment_data['gateway_nonce'] = wp_create_nonce( 'edd-gateway' );

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

	if ( count( $gateways ) > 1 ) {
		$show_gateways = true;
		if ( edd_get_cart_total() <= 0 ) {
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
 * @return string $chosen_gateway The slug of the gateway
 */
function edd_get_chosen_gateway() {
	$gateways = edd_get_enabled_payment_gateways();
	$chosen   = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;

	if ( false !== $chosen ) {
		$chosen = preg_replace('/[^a-zA-Z0-9-_]+/', '', $chosen );
	}

	if ( ! empty ( $chosen ) ) {

		$chosen_gateway = urldecode( $chosen );

		if( ! edd_is_gateway_active( $chosen_gateway ) ) {
			$chosen_gateway = edd_get_default_gateway();
		}

	} else {
		$chosen_gateway = edd_get_default_gateway();
	}

	if ( edd_get_cart_subtotal() <= 0 ) {
		$chosen_gateway = 'manual';
	}

	return apply_filters( 'edd_chosen_gateway', $chosen_gateway );
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
 * Counts the number of purchases made with a gateway
 *
 * @since 1.6
 *
 * @param string $gateway_id
 * @param string $status
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
