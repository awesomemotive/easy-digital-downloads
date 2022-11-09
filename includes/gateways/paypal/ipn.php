<?php
/**
 * IPN Functions
 *
 * This serves as a fallback for the webhooks in the event that the app becomes disconnected.
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal\IPN;

// listens for a IPN request and then processes the order information
function listen_for_ipn() {
	if ( empty( $_GET['edd-listener'] ) || ( $this->id !== $_GET['edd-listener'] && 'eppe' !== $_GET['edd-listener'] ) ) {
		return;
	}

	ipn_debug_log( 'IPN Backup Loaded' );

	nocache_headers();

	$verified = false;

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
			ipn_debug_log( 'post data not detected, bailing' );
			return;

		} else {

			// Loop through each POST
			foreach ( $_POST as $key => $value ) {

				// Encode the value and append the data
				$encoded_data .= $arg_separator."$key=" . urlencode( $value );

			}

		}

	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	// We're always going to validate the IPN here...
	if ( ! edd_is_test_mode() ) {

		ipn_debug_log( 'preparing to verify IPN data' );

		// Validate the IPN
		$remote_post_vars      = array(
			'method'           => 'POST',
			'timeout'          => 45,
			'redirection'      => 5,
			'httpversion'      => '1.1',
			'blocking'         => true,
			'headers'          => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'body'             => $encoded_data_array
		);

		// Get response
		$api_response = wp_remote_post( edd_get_paypal_redirect(), $remote_post_vars );
		$body         = wp_remote_retrieve_body( $api_response );

		if ( is_wp_error( $api_response ) ) {
			edd_record_gateway_error( __( 'IPN Error', 'edd-recurring' ), sprintf( __( 'Invalid PayPal Express IPN verification response. IPN data: %s', 'edd-recurring' ), json_encode( $api_response ) ) );
			ipn_debug_log( 'verification failed. Data: ' . var_export( $body, true ) );
			status_header( 401 );
			return; // Something went wrong
		}

		if ( $body !== 'VERIFIED' ) {
			status_header( 401 );
			edd_record_gateway_error( __( 'IPN Error', 'edd-recurring' ), sprintf( __( 'Invalid PayPal Express IPN verification response. IPN data: %s', 'edd-recurring' ), json_encode( $api_response ) ) );
			ipn_debug_log( 'verification failed. Data: ' . var_export( $body, true ) );
			return; // Response not okay
		}

		// We've verified that the IPN Check passed, we can proceed with processing the IPN data sent to us.
		$verified = true;

	}

	/**
	 * The processIpn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
	 */
	if ( ( $verified || edd_get_option( 'disable_paypal_verification' ) ) || isset( $_POST['verification_override'] ) || edd_is_test_mode() ) {

		status_header( 200 );

		$posted = apply_filters( 'edd_recurring_ipn_post', $_POST ); // allow $_POST to be modified

		/**
		 * Note: Amounts get more properly sanitized on insert.
		 * @see EDD_Subscription::add_payment()
		 */
		if( isset( $posted['amount'] ) ) {
			$amount = (float) $posted['amount'];
		} elseif( isset( $posted['mc_gross'] ) ) {
			$amount = (float) $posted['mc_gross'];
		} else {
			$amount = 0;
		}

		$txn_type        = isset( $posted['txn_type'] ) ? $posted['txn_type'] : '';
		$currency_code   = isset( $posted['mc_currency'] ) ? $posted['mc_currency'] : $posted['currency_code'];
		$transaction_id  = isset( $posted['txn_id'] ) ? $posted['txn_id'] : '';

		if ( empty( $txn_type ) ) {
			ipn_debug_log( 'No txn_type, bailing' );
			return;
		}


		// Process for Subscriptions
		if ( class_exists( 'EDD_Recurring' ) ) {
			if ( ! isset( $posted['recurring_payment_id'] ) ) {
				ipn_debug_log( 'no recurring billing found, move along.' );
			}

			$subscription = new \EDD_Subscription( $posted['recurring_payment_id'], true );

			$parent_payment = edd_get_payment( $subscription->parent_payment_id );
			if ( $parent_payment->gateway !== 'paypal_commerce' ) {
				ipn_debug_log( 'This is not for PayPal Commerce - bailing' );
				return;
			}

			if ( empty( $subscription->id ) || $subscription->id < 1 )  {
				ipn_debug_log( 'no matching subscription found detected, bailing. Data: ' . var_export( $posted, true ) );
				die( 'No subscription found' );
			}

			ipn_debug_log( 'Processing ' . $txn_type . ' IPN for subscription ' . $subscription->id );


			// Subscriptions
			switch ( $txn_type ) :

				case "recurring_payment_profile_created" :

					$subscription->update( array( 'status' => 'active' ) );
					if( ! empty( $posted['initial_payment_txn_id'] ) ) {
						edd_set_payment_transaction_id( $subscription->parent_payment_id, $posted['initial_payment_txn_id'] );
					}

					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription marked as active' );

					die( 'subscription marked as active' );

					break;

				case "recurring_payment" :
				case "recurring_payment_outstanding_payment" :

					$sub_currency = edd_get_payment_currency_code( $subscription->parent_payment_id );

					// verify details
					if( ! empty( $sub_currency ) && strtolower( $currency_code ) != strtolower( $sub_currency ) ) {

						// the currency code is invalid
						// @TODO: Does this need a parent_id for better error organization?
						edd_record_gateway_error( __( 'Invalid Currency Code', 'edd-recurring' ), sprintf( __( 'The currency code in an IPN request did not match the site currency code. Payment data: %s', 'edd-recurring' ), json_encode( $payment_data ) ) );

						ipn_debug_log( 'subscription ' . $subscription->id . ': invalid currency code detected in IPN data: ' . var_export( $posted, true ) );

						die( 'invalid currency code' );

					}

					if( 'failed' === strtolower( $posted['payment_status'] ) ) {

						$transaction_link = '<a href="https://www.paypal.com/activity/payment/' . $transaction_id . '" target="_blank">' . $transaction_id . '</a>';
						$subscription->add_note( sprintf( __( 'Transaction ID %s failed in PayPal', 'edd-recurring' ), $transaction_link ) );
						$subscription->failing();

						ipn_debug_log( 'subscription ' . $subscription->id . ': payment failed in PayPal' );

						die( 'Subscription payment failed' );

					}

					// Bail if this is the very first payment
					if( date( 'Y-n-d', strtotime( $subscription->created ) ) == date( 'Y-n-d', strtotime( $posted['payment_date'] ) ) ) {

						edd_set_payment_transaction_id( $subscription->parent_payment_id, $transaction_id );

						ipn_debug_log( 'subscription ' . $subscription->id . ': processing stopped because this is the initial payment' );

						return;
					}

					ipn_debug_log( 'subscription ' . $subscription->id . ': preparing to insert renewal payment' );

					// when a user makes a recurring payment
					$payment_id = $subscription->add_payment( array(
						'amount'         => $amount,
						'transaction_id' => $transaction_id
					) );

					if ( ! empty( $payment_id ) ) {

						ipn_debug_log( 'subscription ' . $subscription->id . ': renewal payment was recorded successfully, preparing to renew subscription' );
						$subscription->renew( $payment_id );

						if( 'recurring_payment_outstanding_payment' === $txn_type ) {
							$subscription->add_note( sprintf( __( 'Outstanding subscription balance of %s collected successfully.', 'edd-recurring' ), $amount ) );
						}

					} else {
						ipn_debug_log( 'subscription ' . $subscription->id . ': renewal payment creation appeared to fail.' );
					}

					die( 'Subscription payment successful' );

					break;

				case "recurring_payment_profile_cancel" :
				case "recurring_payment_suspended" :
				case "recurring_payment_suspended_due_to_max_failed_payment" :

					$subscription->cancel();
					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription cancelled.' );


					die( 'Subscription cancelled' );

					break;

				case "recurring_payment_failed" :

					$subscription->failing();
					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription failing.' );
					do_action( 'edd_recurring_payment_failed', $subscription );

					break;

				case "recurring_payment_expired" :

					$subscription->complete();
					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription completed.' );

					die( 'Subscription completed' );
					break;

				default :

					die( 'Paypal Commerce IPN Endpoint' );
					break;

			endswitch;
		}

	} else {
		ipn_debug_log( 'verification failed, bailing.' );
		status_header( 400 );
		die( 'invalid IPN' );

	}
}
add_action( 'init', __NAMESPACE__ . '\listen_for_ipn' );

function ipn_debug_log( $message ) {
	ipn_debug_log( 'PayPal Commerce IPN: ' . $message );
}