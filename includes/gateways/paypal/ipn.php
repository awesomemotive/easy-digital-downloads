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

use EDD\Gateways\PayPal;
/**
 * Listens for an IPN call from PayPal
 *
 * This is intended to be a 'backup' listener, for if the webhook is no longer connected for a specific PayPal object.
 *
 * @since 3.1.0.3
 */
function listen_for_ipn() {
	if ( empty( $_GET['edd-listener'] ) || 'eppe' !== $_GET['edd-listener'] ) {
		return;
	}

	// If PayPal is not connected, we don't need to run here.
	if ( ! PayPal\has_rest_api_connection() ) {
		return;
	}

	ipn_debug_log( 'IPN Backup Loaded' );

	// Moving this up in the load order so we can check some things before even getting to verification.
	$posted            = $_POST;
	$ignored_txn_types = array( 'recurring_payment_profile_created' );

	if ( isset( $posted['txn_type'] ) && in_array( $posted['txn_type'], $ignored_txn_types ) ) {
		ipn_debug_log( 'Transaction Type ' . $posted['txn_type'] . ' is ignored by the PayPal Commerce IPN.' );
		return;
	}

	nocache_headers();

	$verified = false;

	// Set initial post data to empty string.
	$post_data = '';

	// Fallback just in case post_max_size is lower than needed.
	if ( ini_get( 'allow_url_fopen' ) ) {
		$post_data = file_get_contents( 'php://input' );
	} else {
		// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough.
		ini_set( 'post_max_size', '12M' );
	}

	// Start the encoded data collection with notification command.
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator.
	$arg_separator = edd_get_php_arg_separator_output();

	// Verify there is a post_data.
	if ( $post_data || strlen( $post_data ) > 0 ) {

		// Append the data.
		$encoded_data .= $arg_separator . $post_data;

	} else {

		// Check if POST is empty.
		if ( empty( $_POST ) ) {

			// Nothing to do.
			ipn_debug_log( 'post data not detected, bailing' );
			return;

		} else {

			// Loop through each POST.
			foreach ( $_POST as $key => $value ) {

				// Encode the value and append the data.
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );

			}

		}

	}

	// Convert collected post data to an array.
	parse_str( $encoded_data, $encoded_data_array );

	// We're always going to validate the IPN here...
	if ( ! edd_is_test_mode() ) {

		ipn_debug_log( 'preparing to verify IPN data' );

		// Validate the IPN.
		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $encoded_data_array,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'user-agent'  => 'Easy Digital Downloads/' . EDD_VERSION . '; ' . get_bloginfo( 'name' ),
		);

		// Get response.
		$api_response = wp_remote_post( edd_get_paypal_redirect(), $remote_post_vars );
		$body         = wp_remote_retrieve_body( $api_response );

		if ( is_wp_error( $api_response ) ) {
			/* Translators: %s - IPN Verification response */
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid PayPal Commerce/Express IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
			ipn_debug_log( 'verification failed. Data: ' . var_export( $body, true ) );
			status_header( 401 );
			return; // Something went wrong.
		}

		if ( 'VERIFIED' !== $body ) {
			/* Translators: %s - IPN Verification response */
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid PayPal Commerce/Express IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
			ipn_debug_log( 'verification failed. Data: ' . var_export( $body, true ) );
			status_header( 401 );
			return; // Response not okay.
		}

		// We've verified that the IPN Check passed, we can proceed with processing the IPN data sent to us.
		$verified = true;

	}

	/**
	 * The processIpn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
	 */
	if ( ( $verified || edd_get_option( 'disable_paypal_verification' ) ) || isset( $_POST['verification_override'] ) || edd_is_test_mode() ) {

		status_header( 200 );

		/**
		 * Note: Amounts get more properly sanitized on insert.
		 *
		 * @see EDD_Subscription::add_payment()
		 */
		if ( isset( $posted['amount'] ) ) {
			$amount = (float) $posted['amount'];
		} elseif ( isset( $posted['mc_gross'] ) ) {
			$amount = (float) $posted['mc_gross'];
		} else {
			$amount = 0;
		}

		$txn_type       = isset( $posted['txn_type'] ) ? strtolower( $posted['txn_type'] ) : '';
		$payment_status = isset( $posted['payment_status'] ) ? strtolower( $posted['payment_status'] ) : '';
		$currency_code  = isset( $posted['mc_currency'] ) ? $posted['mc_currency'] : $posted['currency_code'];
		$transaction_id = isset( $posted['txn_id'] ) ? $posted['txn_id'] : '';

		if ( empty( $txn_type ) && empty( $payment_status ) ) {
			ipn_debug_log( 'No txn_type or payment_status in the IPN, bailing' );
			return;
		}

		// Process webhooks from recurring first, as that is where most of the missing actions will come from.
		if ( class_exists( 'EDD_Recurring' ) && isset( $posted['recurring_payment_id'] ) ) {
			$posted = apply_filters( 'edd_recurring_ipn_post', $_POST ); // allow $_POST to be modified.

			$subscription = new \EDD_Subscription( $posted['recurring_payment_id'], true );


			// Bail if this is the very first payment.
			if ( ! empty( $posted['payment_date'] ) && date( 'Y-n-d', strtotime( $subscription->created ) ) == date( 'Y-n-d', strtotime( $posted['payment_date'] ) ) ) {
				ipn_debug_log( 'IPN for subscription ' . $subscription->id . ': processing stopped because this is the initial payment.' );
				return;
			}

			$parent_payment = edd_get_payment( $subscription->parent_payment_id );
			if ( 'paypal_commerce' !== $parent_payment->gateway ) {
				ipn_debug_log( 'This is not for PayPal Commerce - bailing' );
				return;
			}

			if ( empty( $subscription->id ) || $subscription->id < 1 ) {
				ipn_debug_log( 'no matching subscription found detected, bailing. Data: ' . var_export( $posted, true ) );
				die( 'No subscription found' );
			}

			ipn_debug_log( 'Processing ' . $txn_type . ' IPN for subscription ' . $subscription->id );

			// Subscriptions.
			switch ( $txn_type ) :

				case 'recurring_payment':
				case 'recurring_payment_outstanding_payment':
					$transaction_exists = edd_get_order_transaction_by( 'transaction_id', $transaction_id );
					if ( ! empty( $transaction_exists ) ) {
						ipn_debug_log( 'Transaction ID ' . $transaction_id . ' arlready processed.' );
						return;
					}

					$sub_currency = edd_get_payment_currency_code( $subscription->parent_payment_id );

					// verify details.
					if ( ! empty( $sub_currency ) && strtolower( $currency_code ) != strtolower( $sub_currency ) ) {

						// the currency code is invalid
						// @TODO: Does this need a parent_id for better error organization?
						/* Translators: %s - The payment data sent via the IPN */
						edd_record_gateway_error( __( 'Invalid Currency Code', 'easy-digital-downloads' ), sprintf( __( 'The currency code in an IPN request did not match the site currency code. Payment data: %s', 'easy-digital-downloads' ), json_encode( $posted ) ) );

						ipn_debug_log( 'subscription ' . $subscription->id . ': invalid currency code detected in IPN data: ' . var_export( $posted, true ) );

						die( 'invalid currency code' );

					}

					if ( 'failed' === $payment_status ) {
						if ( 'failing' === $subscription->status ) {
							ipn_debug_log( 'Subscription ID ' . $subscription->id . ' arlready failing.' );
							return;
						}

						$transaction_link = '<a href="https://www.paypal.com/activity/payment/' . $transaction_id . '" target="_blank">' . $transaction_id . '</a>';
						/* Translators: %s - The transaction ID of the failed payment */
						$subscription->add_note( sprintf( __( 'Transaction ID %s failed in PayPal', 'easy-digital-downloads' ), $transaction_link ) );
						$subscription->failing();

						ipn_debug_log( 'subscription ' . $subscription->id . ': payment failed in PayPal' );

						die( 'Subscription payment failed' );

					}

					ipn_debug_log( 'subscription ' . $subscription->id . ': preparing to insert renewal payment' );

					// Build the array for adding a subscription order.
					$subscription_payment_args = array(
						'amount'         => $amount,
						'transaction_id' => $transaction_id,
					);

					// Create a DateTime object of the payment_date, so we can adjust as needed.
					$subscription_payment_date = new \DateTime( $posted['payment_date'] );

					// To make sure we don't inadverntatly fail, make sure the date was parsed correctly before working with it.
					if ( $subscription_payment_date instanceof \DateTime ) {
						/**
						 * Convert to GMT, as that is what EDD 3.0 expects the times to be in.
						 */
						$subscription_payment_date->setTimezone( new \DateTimeZone( 'GMT' ) );

						// Now add the date into the arguments for creating the renewal payment.
						$subscription_payment_args['date'] = $subscription_payment_date->format( 'Y-m-d H:i:s' );
					}

					// when a user makes a recurring payment.
					$payment_id = $subscription->add_payment( $subscription_payment_args );

					if ( ! empty( $payment_id ) ) {
						ipn_debug_log( 'subscription ' . $subscription->id . ': renewal payment was recorded successfully, preparing to renew subscription' );
						$subscription->renew( $payment_id );

						if ( 'recurring_payment_outstanding_payment' === $txn_type ) {
							/* Translators: %s - The collected outstanding balance of the subscription */
							$subscription->add_note( sprintf( __( 'Outstanding subscription balance of %s collected successfully.', 'easy-digital-downloads' ), $amount ) );
						}
					} else {
						ipn_debug_log( 'subscription ' . $subscription->id . ': renewal payment creation appeared to fail.' );
					}

					die( 'Subscription payment successful' );

					break;

				case 'recurring_payment_profile_cancel':
				case 'recurring_payment_suspended':
				case 'recurring_payment_suspended_due_to_max_failed_payment':
					if ( 'cancelled' === $subscription->status ) {
						ipn_debug_log( 'Subscription ID ' . $subscription->id . ' arlready cancelled.' );
						return;
					}

					$subscription->cancel();
					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription cancelled.' );


					die( 'Subscription cancelled' );

					break;

				case 'recurring_payment_failed':
					if ( 'failing' === $subscription->status ) {
						ipn_debug_log( 'Subscription ID ' . $subscription->id . ' arlready failing.' );
						return;
					}

					$subscription->failing();
					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription failing.' );
					do_action( 'edd_recurring_payment_failed', $subscription );

					break;

				case 'recurring_payment_expired':
					if ( 'completed' === $subscription->status ) {
						ipn_debug_log( 'Subscription ID ' . $subscription->id . ' arlready completed.' );
						return;
					}

					$subscription->complete();
					ipn_debug_log( 'subscription ' . $subscription->id . ': subscription completed.' );

					die( 'Subscription completed' );
					break;

			endswitch;
		}

		// We've processed recurring, now let's handle non-recurring IPNs.

		// First, if this isn't a refund or reversal, we don't need to process anything.
		$statuses_to_process = array( 'refunded', 'reversed' );
		if ( ! in_array( $payment_status, $statuses_to_process, true ) ) {
			ipn_debug_log( 'Payment Status was not a status we need to process: ' . $payment_status );
			return;
		}

		$order_id = 0;

		if ( ! empty( $posted['parent_txn_id'] ) ) {
			$order_id = edd_get_order_id_from_transaction_id( $posted['parent_txn_id'] );
		}

		/**
		 * This section of the IPN should only affect processing refunds or returns on orders made previous, not new
		 * orders, so we can just look for the parent_txn_id, and if it's not here, bail as this is a new order that
		 * should be handeled with webhooks.
		 */
		if ( empty( $order_id ) ) {
			ipn_debug_log( 'IPN Track ID ' . $posted['ipn_track_id'] . ' does not need to be processed as it does not belong to an existing record.' );
			return;
		}

		$order = edd_get_order( $order_id );
		if ( 'paypal_commerce' !== $order->gateway ) {
			ipn_debug_log( 'Order ' . $order_id . ' was not with PayPal Commerce' );
			return;
		}

		if ( 'refunded' === $order->status ) {
			ipn_debug_log( 'Order ' . $order_id . ' is already refunded' );
		}

		$transaction_exists = edd_get_order_transaction_by( 'transaction_id', $transaction_id );
		if ( ! empty( $transaction_exists ) ) {
			ipn_debug_log( 'Refund transaction for ' . $transaction_id . ' already exists' );
			return;
		}

		$order_amount    = edd_get_payment_amount( $order->id );
		$refunded_amount = ! empty( $amount ) ? $amount : $order_amount;
		$currency        = ! empty( $currency_code ) ? $currency_code : $order->currency;

		ipn_debug_log( 'Processing a refund for original transaction ' . $order->get_transaction_id() );

		/* Translators: %1$s - Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
		$payment_note = sprintf(
			esc_html__( 'Amount: %1$s; Payment transaction ID: %2$s; Refund transaction ID: %3$s', 'easy-digital-downloads' ),
			edd_currency_filter( edd_format_amount( $refunded_amount ), $currency ),
			esc_html( $order->get_transaction_id() ),
			esc_html( $transaction_id )
		);

		// Partial refund.
		if ( (float) $refunded_amount < (float) $order_amount ) {
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => __( 'Partial refund processed in PayPal.', 'easy-digital-downloads' ) . ' ' . $payment_note,
				)
			);
			edd_update_order_status( $order->id, 'partially_refunded' );
		} else {
			// Full refund.
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => __( 'Full refund processed in PayPal.', 'easy-digital-downloads' ) . ' ' . $payment_note,
				)
			);
			edd_update_order_status( $order->id, 'refunded' );
		}

		die( 'Refund processed' );
	} else {
		ipn_debug_log( 'verification failed, bailing.' );
		status_header( 400 );
		die( 'invalid IPN' );

	}
}
add_action( 'init', __NAMESPACE__ . '\listen_for_ipn' );

/**
 * Helper method to prefix any calls to edd_debug_log
 *
 * @since 3.1.0.3
 * @uses edd_debug_log
 *
 * @param string $message The message to send to the debug logging.
 */
function ipn_debug_log( $message ) {
	edd_debug_log( 'PayPal Commerce IPN: ' . $message );
}
