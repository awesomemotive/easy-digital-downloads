<?php
/**
 * PayPal Standard Gateway
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * PayPal Remove CC Form
 *
 * PayPal Standard does not need a CC form, so remove it.
 *
 * @access private
 * @since 1.0
 */
add_action( 'edd_paypal_cc_form', '__return_false' );

/**
 * Process PayPal Purchase
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @param array   $purchase_data Purchase Data
 * @return void
 */
function edd_process_paypal_purchase( $purchase_data ) {
	global $edd_options;

	// Collect payment data
	$payment_data = array(
		'price'         => $purchase_data['price'],
		'date'          => $purchase_data['date'],
		'user_email'    => $purchase_data['user_email'],
		'purchase_key'  => $purchase_data['purchase_key'],
		'currency'      => edd_get_currency(),
		'downloads'     => $purchase_data['downloads'],
		'user_info'     => $purchase_data['user_info'],
		'cart_details'  => $purchase_data['cart_details'],
		'gateway'       => 'paypal',
		'status'        => 'pending'
	);

	// Record the pending payment
	$payment = edd_insert_payment( $payment_data );

	// Check payment
	if ( ! $payment ) {
		// Record the error
		edd_record_gateway_error( __( 'Payment Error', 'edd' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'edd' ), json_encode( $payment_data ) ), $payment );
		// Problems? send back
		edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
	} else {
		// Only send to PayPal if the pending payment is created successfully
		$listener_url = add_query_arg( 'edd-listener', 'IPN', home_url( 'index.php' ) );

		// Get the success url
		$return_url = add_query_arg( array(
				'payment-confirmation' => 'paypal',
				'payment-id' => $payment

			), get_permalink( $edd_options['success_page'] ) );

		// Get the PayPal redirect uri
		$paypal_redirect = trailingslashit( edd_get_paypal_redirect() ) . '?';

		// Setup PayPal arguments
		$paypal_args = array(
			'business'      => $edd_options['paypal_email'],
			'email'         => $purchase_data['user_email'],
			'invoice'  => $purchase_data['purchase_key'],
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => edd_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $payment,
			'rm'            => '2',
			'return'        => $return_url,
			'cancel_return' => edd_get_failed_transaction_uri( '?payment-id=' . $payment ),
			'notify_url'    => $listener_url,
			'page_style'    => edd_get_paypal_page_style(),
			'cbt'   => get_bloginfo( 'name' ),
		);

		if ( ! empty( $purchase_data['user_info']['address'] ) ) {
			$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
			$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
			$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
			$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
		}

		$paypal_extra_args = array(
			'cmd'   => '_cart',
			'upload'  => '1'
		);

		$paypal_args = array_merge( $paypal_extra_args, $paypal_args );

		// Add cart items
		$i = 1;
		foreach ( $purchase_data['cart_details'] as $item ) {

			if ( edd_has_variable_prices( $item['id'] ) && edd_get_cart_item_price_id( $item ) !== false ) {

				$item['name'] .= ' - ' . edd_get_cart_item_price_name( $item );
			}

			$paypal_args['item_name_' . $i ]       = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $item['name'] ), ENT_COMPAT, 'UTF-8' ) );
			if ( edd_use_skus() ) {
				$paypal_args['item_number_' . $i ] = edd_get_download_sku( $item['id'] );
			}
			$paypal_args['quantity_' . $i ]        = $item['quantity'];
			$paypal_args['amount_' . $i ]          = $item['item_price'] - edd_sanitize_amount( $item['discount'] / $item['quantity'] );
			$i++;

		}


		// Calculate discount
		$discounted_amount = 0.00;
		if ( ! empty( $purchase_data['fees'] ) ) {
			$i = empty( $i ) ? 1 : $i;
			foreach ( $purchase_data['fees'] as $fee ) {
				if ( floatval( $fee['amount'] ) > '0' ) {
					// this is a positive fee
					$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label'] ), ENT_COMPAT, 'UTF-8' ) );
					$paypal_args['quantity_' . $i ]  = '1';
					$paypal_args['amount_' . $i ]    = $fee['amount'];
					$i++;
				} else {
					// This is a negative fee (discount)
					$discounted_amount += abs( $fee['amount'] );
				}
			}
		}

		if ( $discounted_amount > '0' ) {
			$paypal_args['discount_amount_cart'] = $discounted_amount;
		}

		// Add taxes to the cart
		if ( edd_use_taxes() ) {
			$paypal_args['tax_cart'] = round( $purchase_data['tax'], 2 );
		}

		$paypal_args = apply_filters( 'edd_paypal_redirect_args', $paypal_args, $purchase_data );

		// Build query
		$paypal_redirect .= http_build_query( $paypal_args );

		// Fix for some sites that encode the entities
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

		// Get rid of cart contents
		edd_empty_cart();

		// Redirect to PayPal
		wp_redirect( $paypal_redirect );
		exit;
	}

}
add_action( 'edd_gateway_paypal', 'edd_process_paypal_purchase' );

/**
 * Listens for a PayPal IPN requests and then sends to the processing function
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_listen_for_paypal_ipn() {
	global $edd_options;

	// Regular PayPal IPN
	if ( isset( $_GET['edd-listener'] ) && $_GET['edd-listener'] == 'IPN' ) {
		do_action( 'edd_verify_paypal_ipn' );
	}
}
add_action( 'init', 'edd_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_process_paypal_ipn() {
	global $edd_options;

	// Check the request method is POST
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		return;
	}

	// Set initial post data to empty string
	$post_data = '';

	// Fallback just in case post_max_size is lower than needed
	if ( ini_get( 'allow_url_fopen' ) ) {
		$post_data = file_get_contents( 'php://input' );
	} else {
		// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
		ini_set( 'post_max_size', '12M' );
	}
	// Start the encoded data collection with notification command
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator
	$arg_separator = edd_get_php_arg_separator_output();

	// Verify there is a post_data
	if ( $post_data || strlen( $post_data ) > 0 ) {
		// Append the data
		$encoded_data .= $arg_separator.$post_data;
	} else {
		// Check if POST is empty
		if ( empty( $_POST ) ) {
			// Nothing to do
			return;
		} else {
			// Loop trough each POST
			foreach ( $_POST as $key => $value ) {
				// Encode the value and append the data
				$encoded_data .= $arg_separator."$key=" . urlencode( $value );
			}
		}
	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	// Get the PayPal redirect uri
	$paypal_redirect = edd_get_paypal_redirect( true );

	if ( ! edd_get_option( 'disable_paypal_verification' ) ) {

		// Validate the IPN

		$remote_post_vars      = array(
			'method'           => 'POST',
			'timeout'          => 45,
			'redirection'      => 5,
			'httpversion'      => '1.0',
			'blocking'         => true,
			'headers'          => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'sslverify'        => false,
			'body'             => $encoded_data_array
		);

		// Get response
		$api_response = wp_remote_post( edd_get_paypal_redirect(), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'edd' ), json_encode( $api_response ) ) );
			return; // Something went wrong
		}

		if ( $api_response['body'] !== 'VERIFIED' && !isset( $edd_options['disable_paypal_verification'] ) ) {
			edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'edd' ), json_encode( $api_response ) ) );
			return; // Response not okay
		}

	}

	// Check if $post_data_array has been populated
	if ( ! is_array( $encoded_data_array ) && !empty( $encoded_data_array ) )
		return;

	if ( has_action( 'edd_paypal_' . $encoded_data_array['txn_type'] ) ) {
		// Allow PayPal IPN types to be processed separately
		do_action( 'edd_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array );
	} else {
		// Fallback to web accept just in case the txn_type isn't present
		do_action( 'edd_paypal_web_accept', $encoded_data_array );
	}
	exit;
}
add_action( 'edd_verify_paypal_ipn', 'edd_process_paypal_ipn' );

/**
 * Process web accept (one time) payment IPNs
 *
 * @since 1.3.4
 * @global $edd_options Array of all the EDD Options
 * @param array   $data IPN Data
 * @return void
 */
function edd_process_paypal_web_accept_and_cart( $data ) {
	global $edd_options;

	if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' )
		return;

	// Collect payment details
	$payment_id     = $data['custom'];
	$purchase_key   = isset( $data['invoice'] ) ? $data['invoice'] : $data['item_number'];
	$paypal_amount  = $data['mc_gross'];
	$payment_status = strtolower( $data['payment_status'] );
	$currency_code  = strtolower( $data['mc_currency'] );
	$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );

	if ( edd_get_payment_gateway( $payment_id ) != 'paypal' ) {
		return; // this isn't a PayPal standard IPN
	}

	// Verify payment recipient
	if ( strcasecmp( $business_email, trim( $edd_options['paypal_email'] ) ) != 0 ) {

		edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'edd' ), json_encode( $data ) ), $payment_id );
		edd_update_payment_status( $payment_id, 'failed' );
		edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'edd' ) );
		return;
	}

	// Verify payment currency
	if ( $currency_code != strtolower( edd_get_currency() ) ) {

		edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'edd' ), json_encode( $data ) ), $payment_id );
		edd_update_payment_status( $payment_id, 'failed' );
		edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'edd' ) );
		return;
	}

	if ( ! edd_get_payment_user_email( $payment_id ) ) {

		// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal

		// No email associated with purchase, so store from PayPal
		update_post_meta( $payment_id, '_edd_payment_user_email', $data['payer_email'] );

		// Setup and store the customers's details
		$address = array();
		$address['line1']   = ! empty( $data['address_street']       ) ? $data['address_street']       : false;
		$address['city']    = ! empty( $data['address_city']         ) ? $data['address_city']         : false;
		$address['state']   = ! empty( $data['address_state']        ) ? $data['address_state']        : false;
		$address['country'] = ! empty( $data['address_country_code'] ) ? $data['address_country_code'] : false;
		$address['zip']     = ! empty( $data['address_zip']          ) ? $data['address_zip']          : false;

		$user_info = array(
			'id'         => '-1',
			'email'      => $data['payer_email'],
			'first_name' => $data['first_name'],
			'last_name'  => $data['last_name'],
			'discount'   => '',
			'address'    => $address
		);

		$payment_meta = get_post_meta( $payment_id, '_edd_payment_meta', true );
		$payment_meta['user_info'] = $user_info;
		update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
	}

	if ( $payment_status == 'refunded' ) {

		// Process a refund
		edd_process_paypal_refund( $data );

	} else {

		if ( get_post_status( $payment_id ) == 'publish' ) {
			return; // Only complete payments once
		}

		// Retrieve the total purchase amount (before PayPal)
		$payment_amount = edd_get_payment_amount( $payment_id );

		if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
			// The prices don't match
			edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'edd' ), json_encode( $data ) ), $payment_id );
			edd_update_payment_status( $payment_id, 'failed' );
			edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'edd' ) );
			return;
		}
		if ( $purchase_key != edd_get_payment_key( $payment_id ) ) {
			// Purchase keys don't match
			edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid purchase key in IPN response. IPN data: %s', 'edd' ), json_encode( $data ) ), $payment_id );
			edd_update_payment_status( $payment_id, 'failed' );
			edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'edd' ) );
			return;
		}

		if ( $payment_status == 'completed' || edd_is_test_mode() ) {
			edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'edd' ) , $data['txn_id'] ) );
			edd_update_payment_status( $payment_id, 'publish' );
		}
	}
}
add_action( 'edd_paypal_web_accept', 'edd_process_paypal_web_accept_and_cart' );

/**
 * Process PayPal IPN Refunds
 *
 * @since 1.3.4
 * @global $edd_options Array of all the EDD Options
 * @param array   $data IPN Data
 * @return void
 */
function edd_process_paypal_refund( $data ) {
	global $edd_options;

	// Collect payment details
	$payment_id = intval( $data['custom'] );

	if ( get_post_status( $payment_id ) == 'refunded' ) {
		return; // Only refund payments once
	}

	edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Payment #%s Refunded', 'edd' ) , $data['parent_txn_id'] ) );
	edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Refund Transaction ID: %s', 'edd' ) , $data['txn_id'] ) );
	edd_update_payment_status( $payment_id, 'refunded' );
}

/**
 * Get PayPal Redirect
 *
 * @since 1.0.8.2
 * @global $edd_options Array of all the EDD Options
 * @param bool    $ssl_check Is SSL?
 * @return string
 */
function edd_get_paypal_redirect( $ssl_check = false ) {
	global $edd_options;

	if ( is_ssl() || ! $ssl_check ) {
		$protocal = 'https://';
	} else {
		$protocal = 'http://';
	}

	// Check the current payment mode
	if ( edd_is_test_mode() ) {
		// Test mode
		$paypal_uri = $protocal . 'www.sandbox.paypal.com/cgi-bin/webscr';
	} else {
		// Live mode
		$paypal_uri = $protocal . 'www.paypal.com/cgi-bin/webscr';
	}

	return apply_filters( 'edd_paypal_uri', $paypal_uri );
}

/**
 * Set the Page Style for PayPal Purchase page
 *
 * @since 1.4.1
 * @global $edd_options Array of all the EDD Options
 * @return string
 */
function edd_get_paypal_page_style() {
	global $edd_options;

	$page_style = 'PayPal';

	if ( isset( $edd_options['paypal_page_style'] ) )
		$page_style = trim( $edd_options['paypal_page_style'] );

	return apply_filters( 'edd_paypal_page_style', $page_style );
}

/**
 * Shows "Purchase Processing" message for PayPal payments are still pending on site return
 *
 * This helps address the Race Condition, as detailed in issue #1839
 *
 * @since 1.9
 * @return string
 */
function edd_paypal_success_page_content( $content ) {

	if ( ! isset( $_GET['payment-id'] ) && ! edd_get_purchase_session() ) {
		return $content;
	}

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if ( ! $payment_id ) {
		$session    = edd_get_purchase_session();
		$payment_id = edd_get_purchase_id_by_key( $session['purchase_key'] );
	}

	$payment = get_post( $payment_id );

	if ( $payment && 'pending' == $payment->post_status ) {

		// Payment is still pending so show processing indicator to fix the Race Condition, issue #
		ob_start();

		edd_get_template_part( 'payment', 'processing' );

		$content = ob_get_clean();

	}

	return $content;

}
add_filter( 'edd_payment_confirm_paypal', 'edd_paypal_success_page_content' );
