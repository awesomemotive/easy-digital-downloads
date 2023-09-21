<?php
/**
 * IPN Functions
 *
 * This serves as a fallback for the webhooks in the event that the app becomes disconnected.
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2023, Easy Digital Downloads
 * @license    GPL2+
 * @since      3.2.0
 */

namespace EDD\Gateways\PayPal;

defined( 'ABSPATH' ) || exit;

use EDD\Gateways\PayPal;

class IPN {

	/**
	 * The posted data from PayPal.
	 *
	 * @var array
	 */
	private $posted;

	/**
	 * The amount of the transaction.
	 *
	 * @var float
	 */
	private $amount = 0;

	/**
	 * The transaction type.
	 *
	 * @var string
	 */
	private $txn_type = '';

	/**
	 * The payment status.
	 *
	 * @var string
	 */
	private $payment_status = '';

	/**
	 * The currency code.
	 *
	 * @var string
	 */
	private $currency_code = '';

	/**
	 * The transaction ID.
	 *
	 * @var string
	 */
	private $transaction_id = '';

	/**
	 * Constructor
	 *
	 * @since 3.2.0
	 *
	 * @param array $posted The posted data from PayPal.
	 */
	public function __construct( $posted = array() ) {
		$this->posted = $posted;
		$this->listen();
	}

	/**
	 * Listens for an IPN call from PayPal
	 *
	 * This is intended to be a 'backup' listener, for if the webhook is no longer connected for a specific PayPal object.
	 * Events that are handled here:
	 * - new_case
	 * - recurring_payment
	 * - recurring_payment_outstanding_payment
	 * - recurring_payment_profile_cancel
	 * - recurring_payment_suspended
	 * - recurring_payment_suspended_due_to_max_failed_payment
	 * - recurring_payment_failed
	 * - recurring_payment_expired
	 *
	 * @since 3.1.0.3
	 * @since 3.2.0 Moved to a class to allow for better organization.
	 */
	public function listen() {
		if ( empty( $_GET['edd-listener'] ) || 'eppe' !== $_GET['edd-listener'] ) {
			return;
		}

		// If PayPal is not connected, we don't need to run here.
		if ( ! PayPal\has_rest_api_connection() ) {
			return;
		}

		// If the transaction type is ignored, we don't need to run here.
		$ignored_txn_types = array( 'recurring_payment_profile_created' );
		if ( isset( $this->posted['txn_type'] ) && in_array( $this->posted['txn_type'], $ignored_txn_types, true ) ) {
			$this->debug_log( 'Transaction Type ' . $this->posted['txn_type'] . ' is ignored by the PayPal Commerce IPN.' );
			return;
		}

		$this->debug_log( 'IPN Backup Loaded' );

		/**
		 * The is_verified() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
		 */
		if ( ! $this->is_verified() ) {
			$this->debug_log( 'verification failed, bailing.' );
			status_header( 400 );
			die( 'invalid IPN' );
		}

		status_header( 200 );

		if ( ! empty( $this->posted['txn_type'] ) ) {
			$this->txn_type = strtolower( $this->posted['txn_type'] );
		}
		if ( ! empty( $this->posted['payment_status'] ) ) {
			$this->payment_status = strtolower( $this->posted['payment_status'] );
		}

		if ( empty( $this->txn_type ) && empty( $this->payment_status ) ) {
			$this->debug_log( 'No txn_type or payment_status in the IPN, bailing' );
			return;
		}

		$this->amount = $this->get_amount();
		if ( ! empty( $this->posted['mc_currency'] ) ) {
			$this->currency_code = $this->posted['mc_currency'];
		} elseif ( ! empty( $this->posted['currency_code'] ) ) {
			$this->currency_code = $this->posted['currency_code'];
		}
		if ( ! empty( $this->posted['txn_id'] ) ) {
			$this->transaction_id = $this->posted['txn_id'];
		}

		if ( 'new_case' === $this->txn_type && ! empty( $this->transaction_id ) ) {
			$this->log_dispute();
			return;
		}

		// Process webhooks from recurring first, as that is where most of the missing actions will come from.
		$this->maybe_handle_recurring();

		$this->maybe_handle_refunds();
	}

	/**
	 * Verifies the IPN data with PayPal.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function is_verified() {
		$encoded_data_array = $this->get_encoded_data_array();
		if ( ! $encoded_data_array ) {
			return false;
		}

		// In certain cases, we will bypass the verification process.
		if ( edd_is_test_mode() || edd_get_option( 'disable_paypal_verification' ) || isset( $this->posted['verification_override'] ) ) {
			return true;
		}

		$this->debug_log( 'preparing to verify IPN data' );

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
			$this->debug_log( 'verification failed. Data: ' . var_export( $body, true ) );
			status_header( 401 );
			return false; // Something went wrong.
		}

		if ( 'VERIFIED' !== $body ) {
			/* Translators: %s - IPN Verification response */
			edd_record_gateway_error( __( 'IPN Error', 'easy-digital-downloads' ), sprintf( __( 'Invalid PayPal Commerce/Express IPN verification response. IPN data: %s', 'easy-digital-downloads' ), json_encode( $api_response ) ) );
			$this->debug_log( 'verification failed. Data: ' . var_export( $body, true ) );
			status_header( 401 );
			return false; // Response not okay.
		}

		// We've verified that the IPN Check passed, we can proceed with processing the IPN data sent to us.
		return true;
	}

	/**
	 * Gets the encoded data array.
	 *
	 * @since 3.2.0
	 * @return array|bool
	 */
	private function get_encoded_data_array() {

		nocache_headers();

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
			if ( empty( $this->posted ) ) {
				// Nothing to do.
				$this->debug_log( 'post data not detected, bailing' );
				return false;
			}

			// Loop through each POST.
			foreach ( $this->posted as $key => $value ) {

				// Encode the value and append the data.
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}

		// Convert collected post data to an array.
		parse_str( $encoded_data, $encoded_data_array );

		return $encoded_data_array;
	}

	/**
	 * Note: Amounts get more properly sanitized on insert.
	 *
	 * @see EDD_Subscription::add_payment()
	 * @since 3.2.0
	 * @return float
	 */
	private function get_amount() {
		if ( isset( $this->posted['amount'] ) ) {
			return (float) $this->posted['amount'];
		}
		if ( isset( $this->posted['mc_gross'] ) ) {
			return (float) $this->posted['mc_gross'];
		}

		return 0;
	}

	/**
	 * Logs a dispute.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function log_dispute() {
		$this->debug_log( 'IPN for dispute ' . $this->transaction_id );
		$order_id = edd_get_order_id_from_transaction_id( $this->transaction_id );
		if ( ! $order_id ) {
			return;
		}
		$order = edd_get_order( $order_id );
		if ( ! $order || 'on_hold' === $order->status ) {
			return;
		}
		$dispute_id = ! empty( $this->posted['case_id'] ) ? $this->posted['case_id'] : false;
		$reason     = ! empty( $this->posted['reason_code'] ) ? $this->posted['reason_code'] : false;
		if ( $dispute_id ) {
			edd_record_order_dispute( $order_id, $dispute_id, $reason );
		}
		edd_add_note(
			array(
				'object_type' => 'order',
				'object_id'   => $order_id,
				'content'     => sprintf(
					/* Translators: 1. Dispute ID; 2. Dispute reason code. Example: The PayPal transaction has been disputed. Case ID: PP-R-NMW-10060094. Reason given: non_receipt. */
					__( 'The PayPal transaction has been disputed (IPN). Case ID: %1$s. Reason given: %2$s.', 'easy-digital-downloads' ),
					$dispute_id,
					! empty( $reason ) ? $reason : __( 'unknown', 'easy-digital-downloads' )
				),
			)
		);
	}

	/**
	 * Handles recurring payments.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function maybe_handle_recurring() {
		if ( ! class_exists( '\\EDD_Subscription' ) ) {
			return;
		}
		if ( empty( $this->posted['recurring_payment_id'] ) ) {
			return;
		}
		// Allow the posted data to be modified.
		$this->posted = apply_filters( 'edd_recurring_ipn_post', $this->posted );

		$subscription = new \EDD_Subscription( $this->posted['recurring_payment_id'], true );

		// Bail if this is the very first payment.
		if ( ! empty( $this->posted['payment_date'] ) && date( 'Y-n-d', strtotime( $subscription->created ) ) == date( 'Y-n-d', strtotime( $this->posted['payment_date'] ) ) ) {
			$this->debug_log( 'IPN for subscription ' . $subscription->id . ': processing stopped because this is the initial payment.' );
			return;
		}

		$parent_order = edd_get_order( $subscription->parent_payment_id );
		if ( 'paypal_commerce' !== $parent_order->gateway ) {
			$this->debug_log( 'This is not for PayPal Commerce - bailing' );
			return;
		}

		if ( empty( $subscription->id ) || $subscription->id < 1 ) {
			$this->debug_log( 'no matching subscription found detected, bailing. Data: ' . var_export( $this->posted, true ) );
			die( 'No subscription found' );
		}

		$this->debug_log( 'Processing ' . $this->txn_type . ' IPN for subscription ' . $subscription->id );

		// Subscriptions.
		switch ( $this->txn_type ) :

			case 'recurring_payment':
			case 'recurring_payment_outstanding_payment':
				$this->process_recurring_payment( $subscription );
				break;

			case 'recurring_payment_profile_cancel':
			case 'recurring_payment_suspended':
			case 'recurring_payment_suspended_due_to_max_failed_payment':
				$this->cancel( $subscription );
				break;

			case 'recurring_payment_failed':
				if ( 'failing' === $subscription->status ) {
					$this->debug_log( 'Subscription ID ' . $subscription->id . ' already failing.' );
					return;
				}

				$subscription->failing();
				$this->debug_log( 'subscription ' . $subscription->id . ': subscription failing.' );
				do_action( 'edd_recurring_payment_failed', $subscription );

				break;

			case 'recurring_payment_expired':
				$this->complete( $subscription );
				break;

		endswitch;
	}

	/**
	 * Gets the order ID from the transaction ID.
	 *
	 * @since 3.2.0
	 * @return int
	 */
	private function get_order_id() {
		return ! empty( $this->posted['parent_txn_id'] ) ?
			edd_get_order_id_from_transaction_id( $this->posted['parent_txn_id'] ) :
			0;
	}

	/**
	 * Processes a recurring payment.
	 *
	 * @since 3.2.0
	 * @param \EDD_Subscription $subscription The subscription object.
	 * @return void
	 */
	private function process_recurring_payment( $subscription ) {
		$transaction_exists = edd_get_order_transaction_by( 'transaction_id', $this->transaction_id );
		if ( ! empty( $transaction_exists ) ) {
			$this->debug_log( 'Transaction ID ' . $this->transaction_id . ' arlready processed.' );
			return;
		}

		// verify details.
		if ( ! empty( $parent_order->currency ) && strtolower( $this->currency_code ) !== strtolower( $parent_order->currency ) ) {

			// the currency code is invalid
			// @TODO: Does this need a parent_id for better error organization?
			/* Translators: %s - The payment data sent via the IPN */
			edd_record_gateway_error( __( 'Invalid Currency Code', 'easy-digital-downloads' ), sprintf( __( 'The currency code in an IPN request did not match the site currency code. Payment data: %s', 'easy-digital-downloads' ), json_encode( $this->posted ) ) );

			$this->debug_log( 'subscription ' . $subscription->id . ': invalid currency code detected in IPN data: ' . var_export( $this->posted, true ) );

			die( 'invalid currency code' );
		}

		if ( 'failed' === $this->payment_status ) {
			if ( 'failing' === $subscription->status ) {
				$this->debug_log( 'Subscription ID ' . $subscription->id . ' arlready failing.' );
				return;
			}

			$transaction_link = '<a href="https://www.paypal.com/activity/payment/' . $this->transaction_id . '" target="_blank">' . $this->transaction_id . '</a>';
			/* Translators: %s - The transaction ID of the failed payment */
			$subscription->add_note( sprintf( __( 'Transaction ID %s failed in PayPal', 'easy-digital-downloads' ), $transaction_link ) );
			$subscription->failing();

			$this->debug_log( 'subscription ' . $subscription->id . ': payment failed in PayPal' );

			die( 'Subscription payment failed' );
		}

		$this->debug_log( 'subscription ' . $subscription->id . ': preparing to insert renewal payment' );

		// Build the array for adding a subscription order.
		$subscription_payment_args = array(
			'amount'         => $this->amount,
			'transaction_id' => $this->transaction_id,
		);

		$payment_date = $this->get_payment_date();
		if ( $payment_date ) {
			$subscription_payment_args['date'] = $payment_date;
		}

		// when a user makes a recurring payment.
		$payment_id = $subscription->add_payment( $subscription_payment_args );

		if ( ! empty( $payment_id ) ) {
			$this->debug_log( 'subscription ' . $subscription->id . ': renewal payment was recorded successfully, preparing to renew subscription' );
			$subscription->renew( $payment_id );

			if ( 'recurring_payment_outstanding_payment' === $this->txn_type ) {
				/* Translators: %s - The collected outstanding balance of the subscription */
				$subscription->add_note( sprintf( __( 'Outstanding subscription balance of %s collected successfully.', 'easy-digital-downloads' ), $this->amount ) );
			}
		} else {
			$this->debug_log( 'subscription ' . $subscription->id . ': renewal payment creation appeared to fail.' );
		}

		die( 'Subscription payment successful' );
	}

	/**
	 * Cancels a subscription.
	 *
	 * @since 3.2.0
	 * @param \EDD_Subscription $subscription The subscription object.
	 * @return void
	 */
	private function cancel( $subscription ) {
		if ( 'cancelled' === $subscription->status ) {
			$this->debug_log( 'Subscription ID ' . $subscription->id . ' already cancelled.' );
			return;
		}

		$subscription->cancel();
		$this->debug_log( 'subscription ' . $subscription->id . ': subscription cancelled.' );

		die( 'Subscription cancelled' );
	}

	/**
	 * Completes a subscription.
	 *
	 * @since 3.2.0
	 * @param \EDD_Subscription $subscription The subscription object.
	 * @return void
	 */
	private function complete( $subscription ) {
		if ( 'completed' === $subscription->status ) {
			$this->debug_log( 'Subscription ID ' . $subscription->id . ' arlready completed.' );
			return;
		}

		$subscription->complete();
		$this->debug_log( 'subscription ' . $subscription->id . ': subscription completed.' );

		die( 'Subscription completed' );
	}

	/**
	 * Handles refunds.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function maybe_handle_refunds() {
		// First, if this isn't a refund or reversal, we don't need to process anything.
		$statuses_to_process = array( 'refunded', 'reversed' );
		if ( ! in_array( $this->payment_status, $statuses_to_process, true ) ) {
			$this->debug_log( 'Payment Status was not a status we need to process: ' . $this->payment_status );
			return;
		}

		$order_id = $this->get_order_id();
		/**
		 * This section of the IPN should only affect processing refunds or returns on orders made previous, not new
		 * orders, so we can just look for the parent_txn_id, and if it's not here, bail as this is a new order that
		 * should be handeled with webhooks.
		 */
		if ( empty( $order_id ) ) {
			$this->debug_log( 'IPN Track ID ' . $this->posted['ipn_track_id'] . ' does not need to be processed as it does not belong to an existing record.' );
			return;
		}

		$order = edd_get_order( $order_id );
		if ( 'paypal_commerce' !== $order->gateway ) {
			$this->debug_log( 'Order ' . $order_id . ' was not with PayPal Commerce' );
			return;
		}

		if ( 'refunded' === $order->status ) {
			$this->debug_log( 'Order ' . $order_id . ' is already refunded' );
		}

		$transaction_exists = edd_get_order_transaction_by( 'transaction_id', $this->transaction_id );
		if ( ! empty( $transaction_exists ) ) {
			$this->debug_log( 'Refund transaction for ' . $this->transaction_id . ' already exists' );
			return;
		}

		$order_amount    = $order->total;
		$refunded_amount = ! empty( $this->amount ) ? $this->amount : $order_amount;
		$currency        = ! empty( $this->currency_code ) ? $this->currency_code : $order->currency;

		if ( 'reversed' === $this->payment_status ) {
			$this->debug_log( 'Processing a reversal for original transaction ' . $order->get_transaction_id() );
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => sprintf(
						/* Translators: %s - Transaction ID */
						__( 'Reversal processed in PayPal (IPN). Transaction ID: %s', 'easy-digital-downloads' ),
						$this->transaction_id
					),
				)
			);
			// The order may already be on hold. If not, we need to record the dispute.
			if ( 'on_hold' !== $order->status && empty( edd_get_order_hold_reason( $order->id ) ) ) {
				$reason = ! empty( $this->posted['reason_code'] ) ? $this->posted['reason_code'] : '';
				edd_record_order_dispute( $order->id, '', $reason );
			}

			die( 'Reversal processed' );
		}

		$this->debug_log( 'Processing a refund for original transaction ' . $order->get_transaction_id() );

		$payment_note = sprintf(
		/* Translators: %1$s - Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
			esc_html__( 'Amount: %1$s; Payment transaction ID: %2$s; Refund transaction ID: %3$s', 'easy-digital-downloads' ),
			edd_currency_filter( edd_format_amount( $refunded_amount ), $currency ),
			esc_html( $order->get_transaction_id() ),
			esc_html( $this->transaction_id )
		);

		// Partial refund.
		if ( (float) $refunded_amount < (float) $order_amount ) {
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => __( 'Partial refund processed in PayPal (IPN).', 'easy-digital-downloads' ) . ' ' . $payment_note,
				)
			);
			edd_update_order_status( $order->id, 'partially_refunded' );
		} else {
			// Full refund.
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => __( 'Full refund processed in PayPal (IPN).', 'easy-digital-downloads' ) . ' ' . $payment_note,
				)
			);
			$refund_id = edd_refund_order( $order->id );
			if ( $refund_id ) {
				edd_add_order_transaction(
					array(
						'object_type'    => 'order',
						'object_id'      => $refund_id,
						'transaction_id' => $this->transaction_id,
						'gateway'        => 'paypal_commerce',
						'total'          => $refunded_amount,
						'status'         => 'complete',
						'currency'       => $currency,
					)
				);
			} else {
				edd_update_order_status( $order->id, 'refunded' );
			}
		}

		die( 'Refund processed' );
	}

	/**
	 * Gets the payment date from the IPN data.
	 *
	 * @since 3.2.0
	 * @return false|string
	 */
	private function get_payment_date() {
		if ( empty( $this->posted['payment_date'] ) ) {
			return false;
		}
		// Create a DateTime object of the payment_date, so we can adjust as needed.
		$subscription_payment_date = new \DateTime( $this->posted['payment_date'] );

		// To make sure we don't inadvertently fail, make sure the date was parsed correctly before working with it.
		if ( ! $subscription_payment_date instanceof \DateTime ) {
			return false;
		}

		/**
		 * Convert to GMT, as that is what EDD 3.0 expects the times to be in.
		 */
		$subscription_payment_date->setTimezone( new \DateTimeZone( 'GMT' ) );

		// Now add the date into the arguments for creating the renewal payment.
		return $subscription_payment_date->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Helper method to prefix any calls to edd_debug_log
	 *
	 * @since 3.1.0.3
	 * @uses edd_debug_log
	 *
	 * @param string $message The message to send to the debug logging.
	 */
	private function debug_log( $message ) {
		edd_debug_log( 'PayPal Commerce IPN: ' . $message );
	}
}
