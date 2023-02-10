<?php
/**
 * PayPal Standard Gateway
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

use EDD\Orders\Order;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
 * Register the PayPal Standard gateway subsection
 *
 * @since  2.6
 * @param  array $gateway_sections  Current Gateway Tab subsections
 * @return array                    Gateway subsections with PayPal Standard
 */
function edd_register_paypal_gateway_section( $gateway_sections ) {
	if ( \EDD\Gateways\PayPal\paypal_standard_enabled() ) {
		$gateway_sections['paypal'] = __( 'PayPal Standard', 'easy-digital-downloads' );
	}

	return $gateway_sections;
}
add_filter( 'edd_settings_sections_gateways', 'edd_register_paypal_gateway_section', 1, 1 );

/**
 * Registers the PayPal Standard settings for the PayPal Standard subsection
 *
 * @since  2.6
 * @param  array $gateway_settings  Gateway tab settings
 * @return array                    Gateway tab settings with the PayPal Standard settings
 */
function edd_register_paypal_gateway_settings( $gateway_settings ) {
	if ( ! \EDD\Gateways\PayPal\paypal_standard_enabled() ) {
		return $gateway_settings;
	}

	$paypal_settings = array(
		'paypal_email' => array(
			'id'   => 'paypal_email',
			'name' => __( 'PayPal Email', 'easy-digital-downloads' ),
			'desc' => __( 'Enter your PayPal account\'s email', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular',
		),
		'paypal_image_url' => array(
			'id'   => 'paypal_image_url',
			'name' => __( 'PayPal Image', 'easy-digital-downloads' ),
			'desc' => __( 'Upload an image to display on the PayPal checkout page.', 'easy-digital-downloads' ),
			'type' => 'upload',
			'size' => 'regular',
		),
	);

	$pdt_desc = sprintf(
		__( 'Enter your PayPal Identity Token in order to enable Payment Data Transfer (PDT). This allows payments to be verified without relying on the PayPal IPN. See our <a href="%s" target="_blank">documentation</a> for further information.', 'easy-digital-downloads' ),
		'https://easydigitaldownloads.com/docs/paypal-legacy-gateways-standard-express-pro-advanced/'
	);

	$paypal_settings['paypal_identify_token'] = array(
		'id'   => 'paypal_identity_token',
		'name' => __( 'PayPal Identity Token', 'easy-digital-downloads' ),
		'type' => 'text',
		'desc' => $pdt_desc,
		'size' => 'regular',
	);

	$desc  = sprintf(
		__( 'If you are unable to use Payment Data Transfer and payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases. See our <a href="%s" target="_blank">FAQ</a> for further information.', 'easy-digital-downloads' ),
		'https://easydigitaldownloads.com/docs/paypal-payments-not-marked-as-complete/'
	);

	$paypal_settings['disable_paypal_verification'] = array(
		'id'    => 'disable_paypal_verification',
		'name'  => __( 'Disable PayPal IPN Verification', 'easy-digital-downloads' ),
		'check' => __( 'Disabled', 'easy-digital-downloads' ),
		'desc'  => $desc,
		'type'  => 'checkbox_description',
	);

	$api_key_settings = array(
		'paypal_api_keys_desc' => array(
			'id'   => 'paypal_api_keys_desc',
			'name' => __( 'API Credentials', 'easy-digital-downloads' ),
			'type' => 'descriptive_text',
			'desc' => sprintf(
				__( 'API credentials are necessary to process PayPal refunds from inside WordPress. These can be obtained from <a href="%s" target="_blank">your PayPal account</a>.', 'easy-digital-downloads' ),
				'https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-an-api-signature'
			)
		),
		'paypal_live_api_username' => array(
			'id'   => 'paypal_live_api_username',
			'name' => __( 'Live API Username', 'easy-digital-downloads' ),
			'desc' => __( 'Your PayPal live API username. ', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular'
		),
		'paypal_live_api_password' => array(
			'id'   => 'paypal_live_api_password',
			'name' => __( 'Live API Password', 'easy-digital-downloads' ),
			'desc' => __( 'Your PayPal live API password.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular'
		),
		'paypal_live_api_signature' => array(
			'id'   => 'paypal_live_api_signature',
			'name' => __( 'Live API Signature', 'easy-digital-downloads' ),
			'desc' => __( 'Your PayPal live API signature.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular'
		),
		'paypal_test_api_username' => array(
			'id'   => 'paypal_test_api_username',
			'name' => __( 'Test API Username', 'easy-digital-downloads' ),
			'desc' => __( 'Your PayPal test API username.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular'
		),
		'paypal_test_api_password' => array(
			'id'   => 'paypal_test_api_password',
			'name' => __( 'Test API Password', 'easy-digital-downloads' ),
			'desc' => __( 'Your PayPal test API password.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular'
		),
		'paypal_test_api_signature' => array(
			'id'   => 'paypal_test_api_signature',
			'name' => __( 'Test API Signature', 'easy-digital-downloads' ),
			'desc' => __( 'Your PayPal test API signature.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular'
		)
	);

	$paypal_settings = array_merge( $paypal_settings, $api_key_settings );

	$paypal_settings            = apply_filters( 'edd_paypal_settings', $paypal_settings );
	$gateway_settings['paypal'] = $paypal_settings;

	return $gateway_settings;
}
add_filter( 'edd_settings_gateways', 'edd_register_paypal_gateway_settings', 1, 1 );

/**
 * Process PayPal Purchase
 *
 * @since 1.0
 * @param array   $purchase_data Purchase Data
 * @return void
 */
function edd_process_paypal_purchase( $purchase_data ) {
	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

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
		'status'        => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending'
	);

	// Record the pending payment
	$payment = edd_insert_payment( $payment_data );

	// Check payment
	if ( ! $payment ) {
		// Record the error
		edd_record_gateway_error( __( 'Payment Error', 'easy-digital-downloads' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'easy-digital-downloads' ), json_encode( $payment_data ) ), $payment );
		// Problems? send back
		edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
	} else {
		// Only send to PayPal if the pending payment is created successfully
		$listener_url = add_query_arg( 'edd-listener', 'IPN', home_url( 'index.php' ) );

		// Set the session data to recover this payment in the event of abandonment or error.
		EDD()->session->set( 'edd_resume_payment', $payment );

		// Get the success url
		$return_url = add_query_arg(
			array(
				'payment-confirmation' => 'paypal',
				'payment-id'           => urlencode( $payment ),
			),
			get_permalink( edd_get_option( 'success_page', false ) )
		);

		// Get the PayPal redirect uri
		$paypal_redirect = trailingslashit( edd_get_paypal_redirect() ) . '?';

		// Setup PayPal arguments
		$paypal_args = array(
			'business'      => edd_get_option( 'paypal_email', false ),
			'email'         => $purchase_data['user_email'],
			'first_name'    => $purchase_data['user_info']['first_name'],
			'last_name'     => $purchase_data['user_info']['last_name'],
			'invoice'       => $purchase_data['purchase_key'],
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => edd_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $payment,
			'rm'            => '2',
			'return'        => esc_url_raw( $return_url ),
			'cancel_return' => esc_url_raw( edd_get_failed_transaction_uri( '?payment-id=' . sanitize_key( $payment ) ) ),
			'notify_url'    => esc_url_raw( $listener_url ),
			'image_url'     => esc_url_raw( edd_get_paypal_image_url() ),
			'cbt'           => get_bloginfo( 'name' ),
			'bn'            => 'EasyDigitalDownloads_SP'
		);

		if ( ! empty( $purchase_data['user_info']['address'] ) ) {
			$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
			$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
			$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
			$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
		}

		$paypal_extra_args = array(
			'cmd'    => '_cart',
			'upload' => '1'
		);

		$paypal_args = array_merge( $paypal_extra_args, $paypal_args );

		// Add cart items
		$i = 1;
		$paypal_sum = 0;
		if ( is_array( $purchase_data['cart_details'] ) && ! empty( $purchase_data['cart_details'] ) ) {
			foreach ( $purchase_data['cart_details'] as $item ) {

				$item_amount = round( ( $item['subtotal'] / $item['quantity'] ) - ( $item['discount'] / $item['quantity'] ), 2 );

				if ( $item_amount <= 0 ) {
					$item_amount = 0;
				}

				$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( edd_get_cart_item_name( $item ), ENT_COMPAT, 'UTF-8' ) );
				$paypal_args['quantity_' . $i ]  = $item['quantity'];
				$paypal_args['amount_' . $i ]    = $item_amount;

				if ( edd_use_skus() ) {
					$paypal_args['item_number_' . $i ] = edd_get_download_sku( $item['id'] );
				}

				$paypal_sum += ( $item_amount * $item['quantity'] );

				$i++;

			}
		}

		// Calculate discount
		$discounted_amount = 0.00;
		if ( ! empty( $purchase_data['fees'] ) ) {
			$i = empty( $i ) ? 1 : $i;
			foreach ( $purchase_data['fees'] as $fee ) {
				if ( empty( $fee['download_id'] ) && floatval( $fee['amount'] ) > '0' ) {
					// this is a positive fee
					$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label'] ), ENT_COMPAT, 'UTF-8' ) );
					$paypal_args['quantity_' . $i ]  = '1';
					$paypal_args['amount_' . $i ]    = edd_sanitize_amount( $fee['amount'] );
					$i++;
				} else if ( empty( $fee['download_id'] ) ) {

					// This is a negative fee (discount) not assigned to a specific Download
					$discounted_amount += abs( $fee['amount'] );
				}
			}
		}

		$price_before_discount = $purchase_data['price'];
		if ( $discounted_amount > '0' ) {
			$paypal_args['discount_amount_cart'] = edd_sanitize_amount( $discounted_amount );

			/*
			 * Add the discounted amount back onto the price to get the "price before discount". We do this
			 * to avoid double applying any discounts below.
			 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6837
			 */
			$price_before_discount += $paypal_args['discount_amount_cart'];
		}

		// Check if there are any additional discounts we need to add that we haven't already accounted for.
		if( $paypal_sum > $price_before_discount ) {
			$difference = round( $paypal_sum - $price_before_discount, 2 );
			if ( ! isset( $paypal_args['discount_amount_cart'] ) ) {
				$paypal_args['discount_amount_cart'] = 0;
			}
			$paypal_args['discount_amount_cart'] += $difference;
		}

		// Add taxes to the cart
		if ( edd_use_taxes() ) {

			$paypal_args['tax_cart'] = edd_sanitize_amount( $purchase_data['tax'] );

		}

		$paypal_args = apply_filters( 'edd_paypal_redirect_args', $paypal_args, $purchase_data );

		edd_debug_log( 'PayPal arguments: ' . print_r( $paypal_args, true ) );

		// Build query
		$paypal_redirect .= http_build_query( $paypal_args );

		// Fix for some sites that encode the entities
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

		// Allow paypal as a redirect destination.
		add_filter( 'allowed_redirect_hosts', 'edd_allow_redirect_to_paypal', 10 );

		// Redirect to PayPal.
		edd_redirect( $paypal_redirect );
	}
}
add_action( 'edd_gateway_paypal', 'edd_process_paypal_purchase' );

/**
 * Add paypal.com to the list of allowed hosts that wp_safe_redirect can redirect to.
 *
 * @since 3.0
 * @param array $redirects - The list of urls that wp_safe_redirect can redirect to.
 * @return array
 */
function edd_allow_redirect_to_paypal( $redirects ) {
	$redirects[] = 'www.sandbox.paypal.com';
	$redirects[] = 'sandbox.paypal.com';
	$redirects[] = 'www.paypal.com';
	$redirects[] = 'paypal.com';
	return $redirects;
}

/**
 * Listens for a PayPal IPN requests and then sends to the processing function
 *
 * @since 1.0
 * @return void
 */
function edd_listen_for_paypal_ipn() {
	// Regular PayPal IPN
	if ( isset( $_GET['edd-listener'] ) && 'ipn' === strtolower( $_GET['edd-listener'] ) ) {

		edd_debug_log( 'PayPal IPN endpoint loaded' );

		/**
		 * This is necessary to delay execution of PayPal PDT and to avoid a race condition causing the order status
		 * updates to be triggered twice.
		 *
		 * @since 2.9.4
		 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6605
		 */
		$token = edd_get_option( 'paypal_identity_token' );
		if ( $token ) {
			sleep( 5 );
		}

		do_action( 'edd_verify_paypal_ipn' );
	}
}
add_action( 'init', 'edd_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @since 1.0
 * @return void
 */
function edd_process_paypal_ipn() {
	// Check the request method is POST
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		return;
	}

	edd_debug_log( 'edd_process_paypal_ipn() running during PayPal IPN processing' );

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
		$encoded_data .= $arg_separator . $post_data;
	} else {
		// Check if POST is empty
		if ( empty( $_POST ) ) {
			// Nothing to do
			return;
		} else {
			// Loop through each POST
			foreach ( $_POST as $key => $value ) {
				// Encode the value and append the data
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}
	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	foreach ( $encoded_data_array as $key => $value ) {

		if ( false !== strpos( $key, 'amp;' ) ) {
			$new_key = str_replace( '&amp;', '&', $key );
			$new_key = str_replace( 'amp;', '&', $new_key );

			unset( $encoded_data_array[ $key ] );
			$encoded_data_array[ $new_key ] = $value;
		}

	}

	/**
	 * PayPal Web IPN Verification
	 *
	 * Allows filtering the IPN Verification data that PayPal passes back in via IPN with PayPal Standard
	 *
	 * @since 2.8.13
	 *
	 * @param array $data      The PayPal Web Accept Data
	 */
	$encoded_data_array = apply_filters( 'edd_process_paypal_ipn_data', $encoded_data_array );

	edd_debug_log( 'encoded_data_array data array: ' . print_r( $encoded_data_array, true ) );

	if ( ! edd_get_option( 'disable_paypal_verification' ) ) {

		// Validate the IPN

		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',
				'user-agent'   => 'EDD IPN Verification/' . EDD_VERSION . '; ' . get_bloginfo( 'url' )

			),
			'sslverify'   => false,
			'body'        => $encoded_data_array
		);

		edd_debug_log( 'Attempting to verify PayPal IPN. Data sent for verification: ' . print_r( $remote_post_vars, true ) );

		// Get response
		$api_response = wp_remote_post( edd_get_paypal_redirect( true, true ), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
			edd_debug_log( 'Invalid IPN verification response. IPN data: ' . print_r( $api_response, true ) );

			return; // Something went wrong
		}

		if ( wp_remote_retrieve_body( $api_response ) !== 'VERIFIED' && edd_get_option( 'disable_paypal_verification', false ) ) {
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
			edd_debug_log( 'Invalid IPN verification response. IPN data: ' . print_r( $api_response, true ) );

			return; // Response not okay
		}

		edd_debug_log( 'IPN verified successfully' );
	}

	// Check if $post_data_array has been populated
	if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
		return;
	}

	$defaults = array(
		'txn_type'       => '',
		'payment_status' => ''
	);

	$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

	$payment_id = 0;

	if ( ! empty( $encoded_data_array[ 'parent_txn_id' ] ) ) {
		$payment_id = edd_get_purchase_id_by_transaction_id( $encoded_data_array[ 'parent_txn_id' ] );
	} elseif ( ! empty( $encoded_data_array[ 'txn_id' ] ) ) {
		$payment_id = edd_get_purchase_id_by_transaction_id( $encoded_data_array[ 'txn_id' ] );
	}

	if ( empty( $payment_id ) ) {
		$payment_id = ! empty( $encoded_data_array[ 'custom' ] ) ? absint( $encoded_data_array[ 'custom' ] ) : 0;
	}

	if ( has_action( 'edd_paypal_' . $encoded_data_array['txn_type'] ) ) {
		// Allow PayPal IPN types to be processed separately
		do_action( 'edd_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id );
	} else {
		// Fallback to web accept just in case the txn_type isn't present
		do_action( 'edd_paypal_web_accept', $encoded_data_array, $payment_id );
	}
	exit;
}
add_action( 'edd_verify_paypal_ipn', 'edd_process_paypal_ipn' );

/**
 * Process web accept (one time) payment IPNs
 *
 * @since 1.3.4
 * @param array $data       IPN Data
 * @param int   $payment_id Payment ID
 * @return void
 */
function edd_process_paypal_web_accept_and_cart( $data, $payment_id ) {

	/**
	 * PayPal Web Accept Data
	 *
	 * Allows filtering the Web Accept data that PayPal passes back in via IPN with PayPal Standard
	 *
	 * @since 2.8.13
	 *
	 * @param array $data      The PayPal Web Accept Data
	 * @param int  $payment_id The Payment ID associated with this IPN request
	 */
	$data = apply_filters( 'edd_paypal_web_accept_and_cart_data', $data, $payment_id );

	if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded' ) {
		return;
	}

	if ( empty( $payment_id ) ) {
		return;
	}

	$payment = new EDD_Payment( $payment_id );

	// Collect payment details
	$purchase_key = isset( $data['invoice'] ) ? $data['invoice'] : false;
	if ( ! $purchase_key && ! empty( $data['item_number'] ) ) {
		$purchase_key = $data['item_number'];
	}
	$paypal_amount  = $data['mc_gross'];
	$payment_status = strtolower( $data['payment_status'] );
	$currency_code  = strtolower( $data['mc_currency'] );
	$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );


	if ( $payment->gateway != 'paypal' ) {
		return; // this isn't a PayPal standard IPN
	}

	// Verify payment recipient
	if ( strcasecmp( $business_email, trim( edd_get_option( 'paypal_email', false ) ) ) != 0 ) {
		edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
		edd_debug_log( 'Invalid business email in IPN response. IPN data: ' . print_r( $data, true ) );
		edd_update_payment_status( $payment_id, 'failed' );
		edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'easy-digital-downloads' ) );
		return;
	}

	// Verify payment currency
	if ( $currency_code != strtolower( $payment->currency ) ) {

		edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
		edd_debug_log( 'Invalid currency in IPN response. IPN data: ' . print_r( $data, true ) );
		edd_update_payment_status( $payment_id, 'failed' );
		edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'easy-digital-downloads' ) );
		return;
	}

	if ( empty( $payment->email ) ) {

		// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal

		// Setup and store the customers's details
		$address = array();
		$address['line1']    = ! empty( $data['address_street']       ) ? sanitize_text_field( $data['address_street'] )       : false;
		$address['city']     = ! empty( $data['address_city']         ) ? sanitize_text_field( $data['address_city'] )         : false;
		$address['state']    = ! empty( $data['address_state']        ) ? sanitize_text_field( $data['address_state'] )        : false;
		$address['country']  = ! empty( $data['address_country_code'] ) ? sanitize_text_field( $data['address_country_code'] ) : false;
		$address['zip']      = ! empty( $data['address_zip']          ) ? sanitize_text_field( $data['address_zip'] )          : false;

		$payment->email      = sanitize_text_field( $data['payer_email'] );
		$payment->first_name = sanitize_text_field( $data['first_name'] );
		$payment->last_name  = sanitize_text_field( $data['last_name'] );
		$payment->address    = $address;

		if ( empty( $payment->customer_id ) ) {

			$customer = new EDD_Customer( $payment->email );
			if ( ! $customer || $customer->id < 1 ) {

				$customer->create( array(
					'email'   => $payment->email,
					'name'    => $payment->first_name . ' ' . $payment->last_name,
					'user_id' => $payment->user_id
				) );

			}

			$payment->customer_id = $customer->id;
		}

		$payment->save();

	}

	if( empty( $customer ) ) {

		$customer = new EDD_Customer( $payment->customer_id );

	}

	// Record the payer email on the EDD_Customer record if it is different than the email entered on checkout
	if( ! empty( $data['payer_email'] ) && ! in_array( strtolower( $data['payer_email'] ), array_map( 'strtolower', $customer->emails ) ) ) {

		$customer->add_email( strtolower( $data['payer_email'] ) );

	}

	if ( $payment_status == 'refunded' || $payment_status == 'reversed' ) {

		// Process a refund
		edd_process_paypal_refund( $data, $payment_id );

	} else {

		if ( edd_get_payment_status( $payment_id ) == 'complete' ) {
			return; // Only complete payments once
		}

		// Retrieve the total purchase amount (before PayPal)
		$payment_amount = edd_get_payment_amount( $payment_id );

		if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
			// The prices don't match
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
			edd_debug_log( 'Invalid payment amount in IPN response. IPN data: ' . printf( $data, true ) );
			edd_update_payment_status( $payment_id, 'failed' );
			edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'easy-digital-downloads' ) );
			return;
		}
		if ( $purchase_key != edd_get_payment_key( $payment_id ) ) {
			// Purchase keys don't match
			edd_debug_log( 'Invalid purchase key in IPN response. IPN data: ' . printf( $data, true ) );
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid purchase key in IPN response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $data ) ), $payment_id );
			edd_update_payment_status( $payment_id, 'failed' );
			edd_insert_payment_note( $payment_id, __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'easy-digital-downloads' ) );
			return;
		}

		if ( 'completed' == $payment_status || edd_is_test_mode() ) {

			edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'easy-digital-downloads' ) , $data['txn_id'] ) );
			edd_set_payment_transaction_id( $payment_id, $data['txn_id'], number_format( (float) $paypal_amount, 2 ) );
			edd_update_payment_status( $payment_id, 'complete' );

		} else if ( 'pending' == $payment_status && isset( $data['pending_reason'] ) ) {

			// Look for possible pending reasons, such as an echeck

			$note = '';

			switch( strtolower( $data['pending_reason'] ) ) {

				case 'echeck' :

					$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days', 'easy-digital-downloads' );
					$payment->status = 'processing';
					$payment->save();
					break;

				case 'address' :

					$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal', 'easy-digital-downloads' );

					break;

				case 'intl' :

					$note = __( 'Payment must be accepted manually through PayPal due to international account regulations', 'easy-digital-downloads' );

					break;

				case 'multi-currency' :

					$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal', 'easy-digital-downloads' );

					break;

				case 'paymentreview' :
				case 'regulatory_review' :

					$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'easy-digital-downloads' );

					break;

				case 'unilateral' :

					$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'easy-digital-downloads' );

					break;

				case 'upgrade' :

					$note = __( 'PayPal account must be upgraded before this payment can be accepted', 'easy-digital-downloads' );

					break;

				case 'verify' :

					$note = __( 'PayPal account is not verified. Verify account in order to accept this payment', 'easy-digital-downloads' );

					break;

				case 'other' :

					$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance', 'easy-digital-downloads' );

					break;

			}

			if ( ! empty( $note ) ) {

				edd_debug_log( 'Payment not marked as completed because: ' . $note );
				edd_insert_payment_note( $payment_id, $note );

			}

		}
	}
}
add_action( 'edd_paypal_web_accept', 'edd_process_paypal_web_accept_and_cart', 10, 2 );

/**
 * Process PayPal IPN Refunds
 *
 * @since 1.3.4
 * @param array   $data IPN Data
 * @return void
 */
function edd_process_paypal_refund( $data, $payment_id = 0 ) {

	/**
	 * PayPal Process Refund Data
	 *
	 * Allows filtering the Refund data that PayPal passes back in via IPN with PayPal Standard
	 *
	 * @since 2.8.13
	 *
	 * @param array $data      The PayPal Refund data
	 * @param int  $payment_id The Payment ID associated with this IPN request
	 */
	$data = apply_filters( 'edd_process_paypal_refund_data', $data, $payment_id );

	// Collect payment details
	if ( empty( $payment_id ) ) {
		return;
	}

	if ( get_post_status( $payment_id ) == 'refunded' ) {
		return; // Only refund payments once
	}

	$payment_amount = edd_get_payment_amount( $payment_id );
	$refund_amount  = $data['mc_gross'] * -1;

	if ( number_format( (float) $refund_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {

		edd_insert_payment_note( $payment_id, sprintf( __( 'Partial PayPal refund processed: %s', 'easy-digital-downloads' ), $data['parent_txn_id'] ) );
		return; // This is a partial refund

	}

	edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Payment #%s Refunded for reason: %s', 'easy-digital-downloads' ), $data['parent_txn_id'], $data['reason_code'] ) );
	edd_insert_payment_note( $payment_id, sprintf( __( 'PayPal Refund Transaction ID: %s', 'easy-digital-downloads' ), $data['txn_id'] ) );
	edd_update_payment_status( $payment_id, 'refunded' );
}

/**
 * Get PayPal Redirect
 *
 * @since 1.0.8.2
 * @param bool    $ssl_check Is SSL?
 * @param bool    $ipn       Is this an IPN verification check?
 * @return string
 */
function edd_get_paypal_redirect( $ssl_check = false, $ipn = false ) {

	$protocol = 'http://';
	if ( is_ssl() || ! $ssl_check ) {
		$protocol = 'https://';
	}

	// Check the current payment mode
	if ( edd_is_test_mode() ) {

		// Test mode

		if ( $ipn ) {

			$paypal_uri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

		} else {

			$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';

		}

	} else {

		// Live mode

		if ( $ipn ) {

			$paypal_uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';

		} else {

			$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';

		}

	}

	return apply_filters( 'edd_paypal_uri', $paypal_uri, $ssl_check, $ipn );
}

/**
 * Get the image for the PayPal purchase page.
 *
 * @since 2.8
 * @return string
 */
function edd_get_paypal_image_url() {
	$image_url = trim( edd_get_option( 'paypal_image_url', '' ) );
	return apply_filters( 'edd_paypal_image_url', $image_url );
}

/**
 * Shows "Purchase Processing" message for PayPal payments are still pending on site return.
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

	edd_empty_cart();

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if ( ! $payment_id ) {
		$session    = edd_get_purchase_session();
		$payment_id = edd_get_purchase_id_by_key( $session['purchase_key'] );
	}

	$payment = new EDD_Payment( $payment_id );

	if ( $payment->ID > 0 && 'pending' == $payment->status  ) {

		// Payment is still pending so show processing indicator to fix the Race Condition, issue #
		ob_start();

		edd_get_template_part( 'payment', 'processing' );

		$content = ob_get_clean();

	}

	return $content;

}
add_filter( 'edd_payment_confirm_paypal', 'edd_paypal_success_page_content' );

/**
 * Mark payment as complete on return from PayPal if a PayPal Identity Token is present.
 *
 * See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6197
 *
 * @since 2.8.13
 * @return void
 */
function edd_paypal_process_pdt_on_return() {

	if ( ! isset( $_GET['payment-id'] ) || ! isset( $_GET['tx'] ) ) {
		return;
	}

	$token = edd_get_option( 'paypal_identity_token' );

	if ( ! edd_is_success_page() || ! $token || ! edd_is_gateway_active( 'paypal' ) ) {
		return;
	}

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if ( empty( $payment_id ) ) {
		return;
	}

	$purchase_session = edd_get_purchase_session();
	$payment          = new EDD_Payment( $payment_id );

	// If there is no purchase session, don't try and fire PDT.
	if ( empty( $purchase_session ) ) {
		return;
	}

	// Do not fire a PDT verification if the purchase session does not match the payment-id PDT is asking to verify.
	if ( ! empty( $purchase_session['purchase_key'] ) && $payment->key !== $purchase_session['purchase_key'] ) {
		return;
	}

	if ( $token && ! empty( $_GET['tx'] ) && $payment->ID > 0 ) {

		// An identity token has been provided in settings so let's immediately verify the purchase

		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',
				'user-agent'   => 'EDD PDT Verification/' . EDD_VERSION . '; ' . get_bloginfo( 'url' )

			),
			'sslverify'   => false,
			'body'        => array(
				'tx'  => sanitize_text_field( $_GET['tx'] ),
				'at'  => $token,
				'cmd' => '_notify-synch',
			)
		);

		// Sanitize the data for debug logging.
		$debug_args               = $remote_post_vars;
		$debug_args['body']['at'] = str_pad( substr( $debug_args['body']['at'], -6 ), strlen( $debug_args['body']['at'] ), '*', STR_PAD_LEFT );
		edd_debug_log( 'Attempting to verify PayPal payment with PDT. Args: ' . print_r( $debug_args, true ) );

		edd_debug_log( 'Sending PDT Verification request to ' . edd_get_paypal_redirect() );

		$request = wp_remote_post( edd_get_paypal_redirect(), $remote_post_vars );

		if ( ! is_wp_error( $request ) ) {

			$body = wp_remote_retrieve_body( $request );

			// parse the data
			$lines = explode( "\n", trim( $body ) );
			$data  = array();
			if ( strcmp ( $lines[0], "SUCCESS" ) == 0 ) {

				for ( $i = 1; $i < count( $lines ); $i++ ) {
					$parsed_line = explode( "=", $lines[ $i ],2 );
					$data[ urldecode( $parsed_line[0] ) ] = urldecode( $parsed_line[1] );
				}

				if ( isset( $data['mc_gross'] ) ) {

					$total = $data['mc_gross'];

				} else if ( isset( $data['payment_gross'] ) ) {

					$total = $data['payment_gross'];

				} else if ( isset( $_REQUEST['amt'] ) ) {

					$total = $_REQUEST['amt'];

				} else {

					$total = null;

				}

				if ( is_null( $total ) ) {

					edd_debug_log( 'Attempt to verify PayPal payment with PDT failed due to payment total missing' );
					$payment->add_note( __( 'Payment could not be verified while validating PayPal PDT. Missing payment total fields.', 'easy-digital-downloads' ) );
					$payment->status = 'pending';

				} elseif ( (float) $total < (float) $payment->total ) {

					/**
					 * Here we account for payments that are less than the expected results only. There are times that
					 * PayPal will sometimes round and have $0.01 more than the amount. The goal here is to protect store owners
					 * from getting paid less than expected.
					 */
					edd_debug_log( 'Attempt to verify PayPal payment with PDT failed due to payment total discrepancy' );
					$payment->add_note( sprintf( __( 'Payment failed while validating PayPal PDT. Amount expected: %f. Amount Received: %f', 'easy-digital-downloads' ), $payment->total, $data['payment_gross'] ) );
					$payment->status = 'failed';

				} else {

					// Verify the status
					switch( strtolower( $data['payment_status'] ) ) {

						case 'completed':
							$payment->status = 'complete';
							break;

						case 'failed':
							$payment->status = 'failed';
							break;

						default:
							$payment->status = 'pending';
							break;

					}

				}

				$payment->transaction_id = sanitize_text_field( $_GET['tx'] );
				$payment->save();

			} elseif ( strcmp ( $lines[0], "FAIL" ) == 0 ) {

				edd_debug_log( 'Attempt to verify PayPal payment with PDT failed due to PDT failure response: ' . print_r( $body, true ) );
				$payment->add_note( __( 'Payment failed while validating PayPal PDT.', 'easy-digital-downloads' ) );
				$payment->status = 'failed';
				$payment->save();

			} else {

				edd_debug_log( 'Attempt to verify PayPal payment with PDT met with an unexpected result: ' . print_r( $body, true ) );
				$payment->add_note( __( 'PayPal PDT encountered an unexpected result, payment set to pending', 'easy-digital-downloads' ) );
				$payment->status = 'pending';
				$payment->save();

			}

		} else {

			edd_debug_log( 'Attempt to verify PayPal payment with PDT failed. Request return: ' . print_r( $request, true ) );

		}
	}

}
add_action( 'template_redirect', 'edd_paypal_process_pdt_on_return' );

/**
 * Given a Payment ID, extract the transaction ID
 *
 * @since 2.1
 * @since 3.0 Updated to use EDD_Note class.
 *
 * @param string $payment_id Payment ID.
 * @return string Transaction ID.
 */
function edd_paypal_get_payment_transaction_id( $payment_id ) {
	$transaction_id = '';
	$notes = edd_get_payment_notes( $payment_id );

	foreach ( $notes as $note ) {
		if ( preg_match( '/^PayPal Transaction ID: ([^\s]+)/', $note->content, $match ) ) {
			$transaction_id = $match[1];
			continue;
		}
	}

	return apply_filters( 'edd_paypal_set_payment_transaction_id', $transaction_id, $payment_id );
}
add_filter( 'edd_get_payment_transaction_id-paypal', 'edd_paypal_get_payment_transaction_id', 10, 1 );

/**
 * Given a transaction ID, generate a link to the PayPal transaction ID details
 *
 * @since  2.2
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 * @return string                 A link to the PayPal transaction details
 */
function edd_paypal_link_transaction_id( $transaction_id, $payment_id ) {

	$payment         = new EDD_Payment( $payment_id );
	$sandbox         = 'test' === $payment->mode ? 'sandbox.' : '';
	$paypal_base_url = 'https://' . $sandbox . 'paypal.com/activity/payment/';
	$transaction_url = '<a href="' . esc_url( $paypal_base_url . $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';

	return apply_filters( 'edd_paypal_link_payment_details_transaction_id', $transaction_url );

}
add_filter( 'edd_payment_details_transaction_id-paypal', 'edd_paypal_link_transaction_id', 10, 2 );

/**
 * Shows a checkbox to automatically refund payments in PayPal.
 *
 * @param Order $order
 *
 * @since 3.0
 * @return void
 */
function edd_paypal_refund_checkbox( Order $order ) {
	if ( 'paypal' !== $order->gateway ) {
		return;
	}

	// If our credentials are not set, return early.
	$key       = $order->mode;
	$username  = edd_get_option( 'paypal_' . $key . '_api_username' );
	$password  = edd_get_option( 'paypal_' . $key . '_api_password' );
	$signature = edd_get_option( 'paypal_' . $key . '_api_signature' );

	if ( empty( $username ) || empty( $password ) || empty( $signature ) ) {
		return;
	}
	?>
	<div class="edd-form-group edd-paypal-refund-transaction">
		<div class="edd-form-group__control">
			<input type="checkbox" id="edd-paypal-refund" name="edd-paypal-refund" class="edd-form-group__input" value="1">
			<label for="edd-paypal-refund" class="edd-form-group__label">
				<?php esc_html_e( 'Refund transaction in PayPal', 'easy-digital-downloads' ); ?>
			</label>
		</div>
	</div>
	<?php
}
add_action( 'edd_after_submit_refund_table', 'edd_paypal_refund_checkbox' );

/**
 * If selected, refunds a transaction in PayPal when creating a new refund record.
 *
 * @param int $order_id ID of the order we're processing a refund for.
 * @param int $refund_id ID of the newly created refund record.
 * @param bool $all_refunded Whether or not this was a full refund.
 *
 * @since 3.0
 */
function edd_paypal_maybe_refund_transaction( $order_id, $refund_id, $all_refunded ) {
	if ( ! current_user_can( 'edit_shop_payments', $order_id ) ) {
		return;
	}

	if ( empty( $_POST['data'] ) ) {
		return;
	}

	$order = edd_get_order( $order_id );
	if ( empty( $order->gateway ) || 'paypal' !== $order->gateway ) {
		return;
	}

	// Get our data out of the serialized string.
	parse_str( $_POST['data'], $form_data );

	if ( empty( $form_data['edd-paypal-refund'] ) ) {
		edd_add_note( array(
			'object_id'   => $order_id,
			'object_type' => 'order',
			'user_id'     => is_admin() ? get_current_user_id() : 0,
			'content'     => __( 'Transaction not refunded in PayPal, as checkbox was not selected.', 'easy-digital-downloads' )
		) );

		return;
	}

	$refund = edd_get_order( $refund_id );
	if ( empty( $refund->total ) ) {
		return;
	}

	edd_refund_paypal_purchase( $order, $refund );
}
add_action( 'edd_refund_order', 'edd_paypal_maybe_refund_transaction', 10, 3 );

/**
 * Refunds a purchase made via PayPal.
 *
 * @since  2.6.0
 *
 * @param EDD_Payment|Order|int $payment_id_or_object The ID or object of the order to refund.
 * @param Order|null            $refund_object        Optional. The refund object associated with this
 *                                                    transaction refund. If provided, then the refund
 *                                                    amount is used as the transaction refund amount (used for
 *                                                    partial refunds), and an EDD transaction record will be
 *                                                    inserted.
 *
 * @return void
 */
function edd_refund_paypal_purchase( $payment_id_or_object, $refund_object = null ) {
	/*
	 * Internally we want to work with an Order object, but we also need
	 * an EDD_Payment object for backwards compatibility in the hooks.
	 */
	$order = $payment = false;
	if ( $payment_id_or_object instanceof Order ) {
		$order   = $payment_id_or_object;
		$payment = edd_get_payment( $order->id );
	} elseif ( $payment_id_or_object instanceof EDD_Payment ) {
		$payment = $payment_id_or_object;
		$order   = edd_get_order( $payment_id_or_object->ID );
	} elseif ( is_numeric( $payment_id_or_object ) ) {
		$order   = edd_get_order( $payment_id_or_object );
		$payment = edd_get_payment( $payment_id_or_object );
	}

	if ( empty( $order ) || ! $order instanceof Order ) {
		return;
	}

	// Set PayPal API key credentials.
	$credentials = array(
		'api_endpoint'  => 'test' == $order->mode ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp',
		'api_username'  => edd_get_option( 'paypal_' . $order->mode . '_api_username' ),
		'api_password'  => edd_get_option( 'paypal_' . $order->mode . '_api_password' ),
		'api_signature' => edd_get_option( 'paypal_' . $order->mode . '_api_signature' )
	);

	$credentials = apply_filters( 'edd_paypal_refund_api_credentials', $credentials, $payment );

	$body = array(
		'USER'          => $credentials['api_username'],
		'PWD'           => $credentials['api_password'],
		'SIGNATURE'     => $credentials['api_signature'],
		'VERSION'       => '124',
		'METHOD'        => 'RefundTransaction',
		'TRANSACTIONID' => $order->get_transaction_id(),
		'REFUNDTYPE'    => 'Full'
	);

	// If a refund object is supplied, let's check if this should be a partial refund instead.
	if ( $refund_object instanceof Order && abs( $refund_object->total ) !== abs( $order->total ) ) {
		$body['REFUNDTYPE'] = 'Partial';
		$body['AMT']        = abs( $refund_object->total );

		/* Translators: %d - order ID number; %s - formatted refund amount */
		edd_debug_log( sprintf( 'Processing partial PayPal refund for order #%d. Amount: %s.', $order->id, edd_currency_filter( $refund_object->total, $refund_object->currency ) ) );
	} else {
		/* Translators: %d - order ID number */
		edd_debug_log( sprintf( 'Processing full PayPal refund for order #%d.', $order->id ) );
	}

	$body = apply_filters( 'edd_paypal_refund_body_args', $body, $payment );

	// Prepare the headers of the refund request.
	$headers = array(
		'Content-Type'  => 'application/x-www-form-urlencoded',
		'Cache-Control' => 'no-cache'
	);

	$headers = apply_filters( 'edd_paypal_refund_header_args', $headers, $payment );

	// Prepare args of the refund request.
	$args = array(
		'body' 	      => $body,
		'headers'     => $headers,
		'httpversion' => '1.1'
	);

	$args = apply_filters( 'edd_paypal_refund_request_args', $args, $payment );

	$error_msg = '';
	$request   = wp_remote_post( $credentials['api_endpoint'], $args );

	if ( is_wp_error( $request ) ) {

		$success   = false;
		$error_msg = $request->get_error_message();

	} else {

		$body = wp_remote_retrieve_body( $request );
		if ( is_string( $body ) ) {
			wp_parse_str( $body, $body );
		}

		if ( isset( $body['ACK'] ) && 'success' === strtolower( $body['ACK'] ) ) {
			$success = true;
		} else {
			$success = false;
			if ( isset( $body['L_LONGMESSAGE0'] ) ) {
				$error_msg = $body['L_LONGMESSAGE0'];
			} else {
				$error_msg = __( 'PayPal refund failed for unknown reason.', 'easy-digital-downloads' );
			}
		}

	}

	if ( $success ) {

		edd_update_order_meta( $order->id, '_edd_paypal_refunded', true );

		// Add a note to the original order, and, if provided, the new refund object.
		if ( isset( $body['GROSSREFUNDAMT'] ) ) {
			/* Translators: %1$s - amount refunded; %2$s - transaction ID. */
			$note_message = sprintf( __( '%1$s refunded in PayPal. Transaction ID: %2$s', 'easy-digital-downloads' ), edd_currency_filter( edd_format_amount( $body['GROSSREFUNDAMT'] ) ), esc_html( $body['REFUNDTRANSACTIONID'] ) );
		} else {
			/* Translators: %s - transaction ID. */
			$note_message = sprintf( __( 'PayPal refund transaction ID: %s', 'easy-digital-downloads' ), esc_html( $body['REFUNDTRANSACTIONID'] ) );
		}
		$note_object_ids = array( $order->id );
		if ( $refund_object instanceof Order ) {
			$note_object_ids[] = $refund_object->id;
		}
		foreach ( $note_object_ids as $note_object_id ) {
			edd_add_note( array(
				'object_id'   => $note_object_id,
				'object_type' => 'order',
				'user_id'     => is_admin() ? get_current_user_id() : 0,
				'content'     => $note_message
			) );
		}

		// Add a negative transaction.
		if ( $refund_object instanceof Order && isset( $body['REFUNDTRANSACTIONID'] ) && isset( $body['GROSSREFUNDAMT'] ) ) {
			edd_add_order_transaction( array(
				'object_id'      => $refund_object->id,
				'object_type'    => 'order',
				'transaction_id' => sanitize_text_field( $body['REFUNDTRANSACTIONID'] ),
				'gateway'        => 'paypal',
				'status'         => 'complete',
				'total'          => edd_negate_amount( $body['GROSSREFUNDAMT'] )
			) );
		}

	} else {
		edd_add_note( array(
			'object_id'   => $order->id,
			'object_type' => 'order',
			'user_id'     => is_admin() ? get_current_user_id() : 0,
			'content'     => sprintf( __( 'PayPal refund failed: %s', 'easy-digital-downloads' ), $error_msg )
		) );
	}

	// Run hook letting people know the payment has been refunded successfully.
	do_action( 'edd_paypal_refund_purchase', $payment );
}
