<?php
/**
 * Gateway Functions
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Returns a list of all available payment modes.
 *
 * @since 3.0
 *
 * @return array $modes All the available payment modes.
 */
function edd_get_payment_modes() {
	static $modes = null;

	// Default, built-in gateways
	if ( is_null( $modes ) ) {
		$modes = array(
			'live' => array(
				'admin_label' => __( 'Live', 'easy-digital-downloads' ),
			),
			'test' => array(
				'admin_label' => __( 'Test', 'easy-digital-downloads' ),
			),
		);
	}

	return (array) apply_filters( 'edd_payment_modes', $modes );
}

/**
 * Returns a list of all available gateways.
 *
 * @since 1.0
 *
 * @return array $gateways All the available gateways.
 */
function edd_get_payment_gateways() {
	static $gateways = null;

	// Default, built-in gateways
	if ( is_null( $gateways ) ) {
		$gateways = array(
			'paypal_commerce' => array(
				'admin_label'    => __( 'PayPal', 'easy-digital-downloads' ),
				'checkout_label' => __( 'PayPal', 'easy-digital-downloads' ),
				'supports'       => array(
					'buy_now',
				),
				'icons'          => array(
					'paypal',
				),
			),
			/**
			 * PayPal Standard is available only if it was used prior to 2.11 and the store owner hasn't
			 * yet been onboarded to PayPal Commerce.
			 *
			 * @see \EDD\Gateways\PayPal\maybe_remove_paypal_standard()
			 */
			'paypal'          => array(
				'admin_label'    => __( 'PayPal Standard', 'easy-digital-downloads' ),
				'checkout_label' => __( 'PayPal', 'easy-digital-downloads' ),
				'supports'       => array(
					'buy_now',
				),
				'icons'          => array(
					'paypal',
				),
			),
			'manual'          => array(
				'admin_label'    => __( 'Store Gateway', 'easy-digital-downloads' ),
				'checkout_label' => __( 'Store Gateway', 'easy-digital-downloads' ),
			),
		);
	}

	$gateways = apply_filters( 'edd_payment_gateways', $gateways );

	// Since Stripe is added via a filter still, move to the top.
	if ( array_key_exists( 'stripe', $gateways ) ) {
		$stripe_attributes = $gateways['stripe'];
		unset( $gateways['stripe'] );

		$gateways = array_merge( array( 'stripe' => $stripe_attributes ), $gateways );
	}

	return (array) apply_filters( 'edd_payment_gateways', $gateways );
}

/**
 * Enforce the gateway order (from the sortable admin area UI).
 *
 * @since 3.0
 *
 * @param array $gateways
 * @return array
 */
function edd_order_gateways( $gateways = array() ) {

	// Get the order option
	$order = edd_get_option( 'gateways_order', '' );

	// If order is set, enforce it
	if ( ! empty( $order ) ) {
		$order    = array_flip( explode( ',', $order ) );
		$order    = array_intersect_key( $order, $gateways );
		$gateways = array_merge( $order, $gateways );
	}

	// Return ordered gateways
	return $gateways;
}
add_filter( 'edd_payment_gateways', 'edd_order_gateways', 99 );
add_filter( 'edd_enabled_payment_gateways_before_sort', 'edd_order_gateways', 99 );

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
		if ( isset( $enabled[ $key ] ) && 1 === (int) $enabled[ $key ] ) {
			$gateway_list[ $key ] = $gateway;
		}
	}

	/**
	 * Filter the enabled payment gateways before the default is bumped to the
	 * front of the array.
	 *
	 * @since 3.0
	 *
	 * @param array $gateway_list List of enabled payment gateways
	 * @return array Array of sorted gateways
	 */
	$gateway_list = apply_filters( 'edd_enabled_payment_gateways_before_sort', $gateway_list );

	// Reorder our gateways so the default is first
	if ( true === $sort ) {
		$default_gateway_id = edd_get_default_gateway();

		// Only put default on top if it's active
		if ( edd_is_gateway_active( $default_gateway_id ) ) {
			$default_gateway = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
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
 *
 * @param string $gateway Name of the gateway to check for.
 * @return boolean true if enabled, false otherwise.
 */
function edd_is_gateway_active( $gateway ) {
	$gateways = edd_get_enabled_payment_gateways();
	$retval   = array_key_exists( $gateway, $gateways );

	return apply_filters( 'edd_is_gateway_active', $retval, $gateway, $gateways );
}

/**
 * Gets the default payment gateway selected from the EDD Settings.
 *
 * @since 1.5
 *
 * @return string $default Default gateway ID.
 */
function edd_get_default_gateway() {
	$default = edd_get_option( 'default_gateway', 'paypal' );

	// Use the first enabled one
	if ( ! edd_is_gateway_active( $default ) ) {
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
 *
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Gateway admin label
 */
function edd_get_gateway_admin_label( $gateway ) {
	$gateways = edd_get_payment_gateways();

	$label = isset( $gateways[ $gateway ] )
		? $gateways[ $gateway ]['admin_label']
		: ucwords( $gateway );

	return apply_filters( 'edd_gateway_admin_label', $label, $gateway );
}

/**
 * Returns the checkout label for the specified gateway.
 *
 * @since 1.0.8.5
 *
 * @param string $gateway Name of the gateway to retrieve a label for.
 * @return string Checkout label for the gateway.
 */
function edd_get_gateway_checkout_label( $gateway ) {
	$gateways = edd_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;

	return apply_filters( 'edd_gateway_checkout_label', $label, $gateway );
}

/**
 * Returns the options a gateway supports.
 *
 * @since 1.8
 *
 * @param string $gateway ID of the gateway to retrieve a label for.
 * @return array Options the gateway supports.
 */
function edd_get_gateway_supports( $gateway ) {
	$gateways = edd_get_enabled_payment_gateways();
	$supports = isset( $gateways[ $gateway ]['supports'] ) ? $gateways[ $gateway ]['supports'] : array();

	return apply_filters( 'edd_gateway_supports', $supports, $gateway );
}

/**
 * Checks if a gateway supports buy now.
 *
 * @since 1.8
 *
 * @param string $gateway ID of the gateway to retrieve a label for.
 * @return bool True if the gateway supports buy now, false otherwise.
 */
function edd_gateway_supports_buy_now( $gateway ) {
	$supports = edd_get_gateway_supports( $gateway );
	$ret      = in_array( 'buy_now', $supports, true );

	return apply_filters( 'edd_gateway_supports_buy_now', $ret, $gateway );
}

/**
 * Checks if an enabled gateway supports buy now.
 *
 * @since 1.8
 *
 * @return bool True if the shop supports buy now, false otherwise.
 */
function edd_shop_supports_buy_now() {
	$gateways = edd_get_enabled_payment_gateways();
	$ret      = false;

	if ( ! edd_use_taxes() && $gateways && 1 === count( $gateways ) ) {
		foreach ( $gateways as $gateway_id => $gateway ) {
			if ( edd_gateway_supports_buy_now( $gateway_id ) ) {
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
 *
 * @return mixed|void
 */
function edd_build_straight_to_gateway_data( $download_id = 0, $options = array(), $quantity = 1 ) {
	$price_options = array();

	if ( empty( $options ) || ! edd_has_variable_prices( $download_id ) ) {
		$price = edd_get_download_price( $download_id );
	} else {

		if ( is_array( $options['price_id'] ) ) {
			$price_id = $options['price_id'][0];
		} else {
			$price_id = $options['price_id'];
		}

		$prices = edd_get_variable_prices( $download_id );

		// Make sure a valid price ID was supplied
		if ( ! isset( $prices[ $price_id ] ) ) {
			wp_die( __( 'The requested price ID does not exist.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 404 ) );
		}

		$price_options = array(
			'price_id' => $price_id,
			'amount'   => $prices[ $price_id ]['amount'],
		);
		$price         = $prices[ $price_id ]['amount'];
	}

	// Set up Downloads array
	$downloads = array(
		array(
			'id'      => $download_id,
			'options' => $price_options,
		),
	);

	// Set up Cart Details array
	$cart_details = array(
		array(
			'name'        => get_the_title( $download_id ),
			'id'          => $download_id,
			'item_number' => array(
				'id'      => $download_id,
				'options' => $price_options,
			),
			'tax'         => 0,
			'discount'    => 0,
			'item_price'  => $price,
			'subtotal'    => ( $price * $quantity ),
			'price'       => ( $price * $quantity ),
			'quantity'    => $quantity,
		),
	);

	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
	}

	// Setup user information
	$user_info = array(
		'id'         => is_user_logged_in() ? get_current_user_id() : -1,
		'email'      => is_user_logged_in() ? $current_user->user_email : '',
		'first_name' => is_user_logged_in() ? $current_user->user_firstname : '',
		'last_name'  => is_user_logged_in() ? $current_user->user_lastname : '',
		'discount'   => 'none',
		'address'    => array(),
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
		'gateway'      => \EDD\Gateways\PayPal\paypal_standard_enabled() ? 'paypal' : 'paypal_commerce',
		'buy_now'      => true,
		'card_info'    => array(),
	);

	return apply_filters( 'edd_straight_to_gateway_purchase_data', $purchase_data );
}

/**
 * Sends all the payment data to the specified gateway
 *
 * @since 1.0
 *
 * @param string $gateway     Name of the gateway.
 * @param array  $payment_data All the payment data to be sent to the gateway.
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
 *
 * @return bool $show_gateways Whether or not to show the gateways
 */
function edd_show_gateways() {
	$gateways      = edd_get_enabled_payment_gateways();
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
 * @since 1.3.2
 * @return string $chosen_gateway The slug of the gateway
 */
function edd_get_chosen_gateway() {

	// Use the default gateway by default
	$retval = edd_get_default_gateway();

	// Get the chosen gateway
	$chosen = isset( $_REQUEST['payment-mode'] )
		? $_REQUEST['payment-mode']
		: false;

	// Sanitize the gateway
	if ( false !== $chosen ) {
		$chosen = preg_replace( '/[^a-zA-Z0-9-_]+/', '', $chosen );
		$chosen = urldecode( $chosen );

		// Set return value if gateway is active
		if ( ! empty( $chosen ) && edd_is_gateway_active( $chosen ) ) {
			$retval = $chosen;
		}
	}

	// Override to manual if no price
	if ( edd_get_cart_subtotal() <= 0 ) {
		$retval = 'manual';
	}

	return apply_filters( 'edd_chosen_gateway', $retval, $chosen );
}

/**
 * Record a gateway error
 *
 * A simple wrapper function for edd_record_log()
 *
 * @since 1.3.3
 *
 * @param string $title   Title of the log entry (default: empty)
 * @param string $message Message to store in the log entry (default: empty)
 * @param int    $parent  Parent log entry (default: 0)
 *
 * @return int ID of the new log entry.
 */
function edd_record_gateway_error( $title = '', $message = '', $parent = 0 ) {
	return edd_record_log( $title, $message, $parent, 'gateway_error' );
}

/**
 * Counts the number of orders made with a specific gateway.
 *
 * @since 1.6
 * @since 3.0 Use edd_count_orders().
 *
 * @param string $gateway_label Gateway label.
 * @param string $status        Order status.
 *
 * @return int Number of orders placed based on the gateway.
 */
function edd_count_sales_by_gateway( $gateway_label = 'paypal', $status = 'complete' ) {
	return edd_count_orders(
		array(
			'gateway' => $gateway_label,
			'status'  => $status,
		)
	);
}

/**
 * Determines if a gateway is setup.
 *
 * @since 3.1.2
 *
 * @param string $gateway The gateway to check.
 * @param bool   $ignore_registration Whether or not to ignore if the gateway is registered.
 * @return bool True if the gateway is setup, false otherwise.
 */
function edd_is_gateway_setup( $gateway = '', $ignore_registration = false ) {
	// Return false if no gateway is passed.
	if ( empty( $gateway ) ) {
		return false;
	}

	$gateways = edd_get_payment_gateways();

	// If the gateway is not registered, return false.
	if ( ! array_key_exists( $gateway, $gateways ) && ! $ignore_registration ) {
		return false;
	}

	// Some core gateways, we can just determine here, otherwise we'll use the default case to run the filter.
	switch ( $gateway ) {
		case 'stripe':
			$api_key = edd_is_test_mode()
			? edd_get_option( 'test_publishable_key' )
			: edd_get_option( 'live_publishable_key' );

			$is_setup = ! empty( $api_key );
			break;

		case 'paypal_commerce':
			$is_setup = EDD\Gateways\PayPal\ready_to_accept_payments();
			break;

		case 'amazon':
			$amazon_settings = array(
				'amazon_seller_id',
				'amazon_client_id',
				'amazon_mws_access_key',
				'amazon_mws_secret_key',
			);

			$is_setup = true;
			foreach ( $amazon_settings as $key ) {
				if ( empty( edd_get_option( $key, '' ) ) ) {
					$is_setup = false;
					break;
				}
			}
			break;

		default:
			/**
			 * Run a filter to determine if a gateway is setup.
			 *
			 * This defaults to 'true' so that gateways that do not have a setup check to
			 * continue to work.
			 *
			 * This hook would fire on the gateway slug, prefixed with `edd_is_gateway_setup_`.
			 * Example: edd_is_gateway_setup_paypal_express
			 *
			 * @since 3.1.2
			 *
			 * @param bool $is_setup Whether or not the gateway is setup.
			 */
			$is_setup = apply_filters( 'edd_is_gateway_setup_' . $gateway, true );
			break;
	}

	return $is_setup;
}

/**
 * Gets the URL to the gateway settings page.
 *
 * @since 3.1.2
 *
 * @param string $gateway The gateway to get the settings URL for.
 *
 * @return string The URL to the gateway settings page.
 */
function edd_get_gateway_settings_url( $gateway = '' ) {
	// Return false if no gateway is passed.
	if ( empty( $gateway ) ) {
		return '';
	}

	$gateways = edd_get_payment_gateways();

	// If the gateway is not registered, return false.
	if ( ! array_key_exists( $gateway, $gateways ) ) {
		return '';
	}

	// Some core gateways, we can just determine here, otherwise we'll use the default case to run the filter.
	switch ( $gateway ) {
		case 'stripe':
			$gateway_settings_url = edd_get_admin_url(
				array(
					'page'    => 'edd-settings',
					'tab'     => 'gateways',
					'section' => 'edd-stripe',
				)
			);
			break;

		case 'paypal_commerce':
			$gateway_settings_url = EDD\Gateways\PayPal\Admin\get_settings_url();
			break;

		case 'amazon':
			$gateway_settings_url = edd_get_admin_url(
				array(
					'page'    => 'edd-settings',
					'tab'     => 'gateways',
					'section' => 'amazon',
				)
			);
			break;

		default:
			/**
			 * Run a filter to assign a settings URL for the gateway.
			 *
			 * This defaults to an empty string so that gateways that do not have
			 * a setup check to continue to work.
			 *
			 * This hook would fire on the gateway slug, prefixed with `edd_gateway_settings_url_`.
			 * Example: edd_gateway_settings_url_paypal_express
			 *
			 * @since 3.1.2
			 *
			 * @param string $gateway_settings_url The URL to the gateway settings.
			 */
			$gateway_settings_url = apply_filters( 'edd_gateway_settings_url_' . $gateway, '' );
			break;
	}

	return $gateway_settings_url;
}

/**
 * Checks whether the current cart setup is supported. This is intended for subscription orders.
 * If the cart contains multiple products and one of them is a subscription, we need to check
 * if the gateway supports a mixed cart.
 *
 * @since 3.2.7
 * @return bool
 */
function edd_gateway_supports_cart_contents( string $gateway ) {

	// If the cart only contains a single item, the cart is supported.
	if ( count( edd_get_cart_contents() ) === 1 ) {
		return true;
	}

	// If Recurring isn't active, or if the cart doesn't contain a subscription, the cart is supported.
	if ( ! function_exists( 'edd_recurring' ) || ! edd_recurring()->cart_contains_recurring() ) {
		return true;
	}

	// If the cart is mixed and the gateway supports it, the cart is supported.
	// Historically, mixed carts also support multiple subscriptions.
	return in_array( 'mixed_cart', edd_get_gateway_supports( $gateway ), true );
}
