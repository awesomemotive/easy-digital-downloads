<?php
/**
 * IPN Functions
 *
 * This serves as a fallback for the webhooks in the event that the app becomes disconnected.
 *
 * @package    EDD\Gateways\PayPal
 * @copyright  Copyright (c) 2023, Easy Digital Downloads
 * @license    GPL2+
 * @since      3.2.0
 */

namespace EDD\Gateways\PayPal;

defined( 'ABSPATH' ) || exit;

use EDD\Gateways\PayPal;

/**
 * IPN class.
 *
 * @since 3.2.0
 */
class IPN {
	use Traits\Data;
	use Traits\Validate;

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

		if ( ! empty( $this->posted['txn_type'] ) ) {
			$this->txn_type = strtolower( $this->posted['txn_type'] );
		}
		if ( ! empty( $this->posted['payment_status'] ) ) {
			$this->payment_status = strtolower( $this->posted['payment_status'] );
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

		/**
		 * The is_verified() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
		 */
		if ( ! $this->is_verified() ) {
			$this->debug_log( 'verification failed, bailing.' );
			$this->terminate( 400 );
		}

		status_header( 200 );

		// Validate IPN data beyond PayPal's verification.
		try {
			$this->validate_ipn_data();
		} catch ( \Exception $e ) {
			$this->debug_log( 'IPN validation failed: ' . $e->getMessage() );
			$this->terminate( 400, 'IPN validation failed' );
		}

		if ( empty( $this->txn_type ) && empty( $this->payment_status ) ) {
			$this->debug_log( 'No txn_type or payment_status in the IPN, bailing' );
			return;
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
		if ( edd_is_test_mode() || edd_get_option( 'disable_paypal_verification', false ) ) {
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
		$api_response  = wp_remote_post( edd_get_paypal_redirect(), $remote_post_vars );
		$body          = wp_remote_retrieve_body( $api_response );
		$response_code = wp_remote_retrieve_response_code( $api_response );

		// Handle different failure scenarios.
		if ( is_wp_error( $api_response ) ) {
			$error_code    = $api_response->get_error_code();
			$error_message = $api_response->get_error_message();

			$this->debug_log(
				sprintf(
					'PayPal verification endpoint error. Code: %s, Message: %s',
					$error_code,
					$error_message
				)
			);

			// Categorize the error.
			$network_errors   = array( 'http_request_failed', 'connect_error', 'connection_timeout' );
			$is_network_error = in_array( $error_code, $network_errors, true );

			if ( $is_network_error ) {
				// Network/timeout errors - PayPal might be down.
				return $this->handle_verification_unavailable( 'network_error', $error_message );
			} else {
				// Other errors - likely invalid request.
				edd_record_gateway_error(
					__( 'IPN Error', 'easy-digital-downloads' ),
					sprintf(
						/* translators: %1$s: Error message; %2$s: IPN data */
						__( 'PayPal IPN verification failed. Error: %1$s. IPN data: %2$s', 'easy-digital-downloads' ),
						$error_message,
						wp_json_encode(
							array(
								'txn_id'   => ! empty( $this->posted['txn_id'] ) ? $this->posted['txn_id'] : '',
								'txn_type' => ! empty( $this->posted['txn_type'] ) ? $this->posted['txn_type'] : '',
							)
						)
					)
				);
				status_header( 401 );
				return false;
			}
		}

		// Check HTTP response code.
		if ( 403 === $response_code ) {
			// PayPal is actively rejecting the request.
			$this->debug_log( 'PayPal returned 403 Forbidden. Possible service disruption.' );
			return $this->handle_verification_unavailable( 'http_403', 'PayPal returned 403' );
		} elseif ( $response_code >= 500 ) {
			// PayPal server error.
			$this->debug_log( 'PayPal returned server error: ' . $response_code );
			return $this->handle_verification_unavailable( 'server_error', 'PayPal server error ' . $response_code );
		}

		// Check verification response.
		if ( 'VERIFIED' !== $body ) {
			// Check if it's INVALID or something else.
			if ( 'INVALID' === $body ) {
				// Legitimate rejection.
				edd_record_gateway_error(
					__( 'IPN Error', 'easy-digital-downloads' ),
					sprintf(
						/* translators: %s: Transaction ID */
						__( 'PayPal rejected IPN as INVALID. Transaction ID: %s', 'easy-digital-downloads' ),
						! empty( $this->posted['txn_id'] ) ? $this->posted['txn_id'] : 'unknown'
					)
				);
				$this->debug_log( 'PayPal returned INVALID. Data: ' . var_export( $body, true ) );
				status_header( 401 );
				return false;
			} else {
				// Unexpected response.
				$this->debug_log( 'Unexpected PayPal response: ' . var_export( $body, true ) );
				return $this->handle_verification_unavailable( 'unexpected_response', 'Unexpected response: ' . $body );
			}
		}

		// We've verified that the IPN Check passed, we can proceed with processing the IPN data sent to us.
		$this->debug_log( 'IPN verified successfully by PayPal' );
		return true;
	}

	/**
	 * Handles cases where PayPal's verification endpoint is unavailable.
	 *
	 * @since 3.6.3
	 * @param string $reason Reason for unavailability.
	 * @param string $details Additional details.
	 * @return bool Whether to proceed with processing.
	 */
	private function handle_verification_unavailable( $reason, $details ) {
		$this->debug_log(
			sprintf(
				'PayPal verification unavailable. Reason: %s, Details: %s',
				$reason,
				$details
			)
		);

		/**
		 * Filter the fallback strategy to use when PayPal verification is unavailable.
		 *
		 * @since 3.6.3
		 * @param string $fallback_strategy The fallback strategy to use.
		 * @return string The fallback strategy to use.
		 */
		$fallback_strategy = apply_filters( 'edd_paypal_verification_fallback', 'process_with_validation' );

		switch ( $fallback_strategy ) {
			case 'create_on_hold':
				// Create renewal order with on_hold status for manual review.
				$this->create_on_hold_renewal( $reason, $details );
				return false;

			case 'reject':
				// Reject the IPN.
				edd_record_gateway_error(
					__( 'IPN Error', 'easy-digital-downloads' ),
					sprintf(
						/* translators: %1$s: Details; %2$s: Transaction ID */
						__( 'IPN rejected due to verification unavailability: %1$s. Transaction ID: %2$s', 'easy-digital-downloads' ),
						$details,
						! empty( $this->posted['txn_id'] ) ? $this->posted['txn_id'] : 'unknown'
					)
				);
				status_header( 503 ); // Service Unavailable - PayPal will retry.
				return false;

			case 'process_with_validation':
			default:
				// Proceed with enhanced validation.
				$this->debug_log( 'Proceeding with enhanced validation due to verification unavailability' );
				edd_record_gateway_error(
					__( 'IPN Warning', 'easy-digital-downloads' ),
					sprintf(
						/* translators: %1$s: Details; %2$s: Transaction ID */
						__( 'Processing IPN without PayPal verification due to: %1$s. Enhanced validation will be performed. Transaction ID: %2$s', 'easy-digital-downloads' ),
						$details,
						! empty( $this->posted['txn_id'] ) ? $this->posted['txn_id'] : 'unknown'
					)
				);
				return true; // Will rely on validate_ipn_data().
		}
	}

	/**
	 * Creates a renewal order with on_hold status when verification is unavailable.
	 *
	 * This allows the store owner to manually review and approve the renewal if it appears legitimate.
	 *
	 * @since 3.6.3
	 * @param string $reason Reason verification is unavailable.
	 * @param string $details Additional details about the verification failure.
	 */
	private function create_on_hold_renewal( $reason, $details ) {
		// Only create on-hold orders for subscription renewals.
		$renewal_types = array( 'recurring_payment', 'recurring_payment_outstanding_payment' );
		if ( ! in_array( $this->txn_type, $renewal_types, true ) ) {
			$this->debug_log( sprintf( 'Not creating on-hold order for non-renewal transaction type: %s', $this->txn_type ) );
			$this->terminate( 503, 'Not a renewal transaction' );
		}

		// Check if EDD Recurring is available.
		if ( ! class_exists( '\\EDD_Subscription' ) ) {
			$this->debug_log( 'EDD Recurring not available, cannot create on-hold renewal' );
			$this->terminate( 503, 'EDD Recurring not available' );
		}

		// Get the subscription.
		if ( empty( $this->posted['recurring_payment_id'] ) ) {
			$this->debug_log( 'No recurring_payment_id found in IPN data' );
			$this->terminate( 503, 'No recurring_payment_id' );
		}

		$subscription = new \EDD_Subscription( $this->posted['recurring_payment_id'], true );
		if ( empty( $subscription->id ) ) {
			$this->debug_log( sprintf( 'No subscription found for recurring_payment_id: %s', $this->posted['recurring_payment_id'] ) );
			$this->terminate( 503, 'Subscription not found' );
		}

		$this->debug_log(
			sprintf(
				'Creating on-hold renewal for subscription %d. Reason: %s',
				$subscription->id,
				$reason
			)
		);

		// Use the standard process_recurring_payment method to create the renewal.
		// Pass true to skip the subscription renewal step.
		$payment_id = $this->process_recurring_payment( $subscription, true );

		if ( ! $payment_id ) {
			$this->debug_log( sprintf( 'Failed to create renewal payment for subscription %d', $subscription->id ) );
			$this->terminate( 503, 'Failed to create renewal payment' );
		}

		// Now handle the on-hold specific behavior.

		// Update the order status to on_hold (add_payment() sets it to 'edd_subscription').
		edd_update_order_status( $payment_id, 'on_hold' );

		// Add detailed note to the order.
		$note_content = sprintf(
			/* translators: 1: Verification failure reason, 2: Details, 3: Transaction ID, 4: Amount, 5: Currency */
			__( 'Renewal order created with on_hold status for manual review.%1$s%1$sPayPal verification was unavailable:%1$sReason: %2$s%1$sDetails: %3$s%1$s%1$sThis appears to be a legitimate renewal based on:%1$s- Transaction ID: %4$s%1$s- Amount matches subscription: %5$s%1$s- Currency matches: %6$s%1$s%1$sPlease verify this transaction in your PayPal account and update the order status accordingly.', 'easy-digital-downloads' ),
			"\n",
			$reason,
			$details,
			$this->transaction_id,
			edd_currency_filter( edd_format_amount( $this->amount ), $this->currency_code ),
			$this->currency_code
		);

		edd_add_note(
			array(
				'object_type' => 'order',
				'object_id'   => $payment_id,
				'content'     => $note_content,
			)
		);

		// Add note to subscription as well.
		$subscription->add_note(
			sprintf(
				/* translators: 1: Order ID, 2: Transaction ID */
				__( 'Renewal order %1$d created with on_hold status due to PayPal verification issues. Transaction ID: %2$s. Please review and approve if legitimate.', 'easy-digital-downloads' ),
				$payment_id,
				$this->transaction_id
			)
		);

		$this->debug_log(
			sprintf(
				'Successfully created on-hold renewal order %d for subscription %d. Transaction: %s',
				$payment_id,
				$subscription->id,
				$this->transaction_id
			)
		);

		// Record this in gateway errors for admin visibility.
		edd_record_gateway_error(
			__( 'IPN - Manual Review Required', 'easy-digital-downloads' ),
			sprintf(
				/* translators: %1$d: Order ID; %2$s: Reason; %3$s: Transaction ID */
				__( 'Renewal order %1$d created with on_hold status. PayPal verification unavailable (%2$s). Transaction ID: %3$s. Please review in PayPal account.', 'easy-digital-downloads' ),
				$payment_id,
				$reason,
				$this->transaction_id
			)
		);

		$this->terminate( 200, 'On-hold renewal created' );
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
					/* translators: 1: Dispute ID, 2: Dispute reason code. Example: The PayPal transaction has been disputed. Case ID: PP-R-NMW-10060094. Reason given: non_receipt. */
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
		if ( empty( $subscription->id ) ) {
			$this->debug_log( 'No subscription found for recurring_payment_id: ' . $this->posted['recurring_payment_id'] );
			return;
		}

		// Bail if this is the very first payment.
		if ( ! empty( $this->posted['payment_date'] ) && date( 'Y-n-d', strtotime( $subscription->created ) ) == date( 'Y-n-d', strtotime( $this->posted['payment_date'] ) ) ) {
			$this->debug_log( 'IPN for subscription ' . $subscription->id . ': processing stopped because this is the initial payment.' );
			return;
		}

		$parent_order = edd_get_order( $subscription->parent_payment_id );
		if ( ! $parent_order || 'paypal_commerce' !== $parent_order->gateway ) {
			$this->debug_log( 'This is not for PayPal Commerce - bailing' );
			return;
		}

		if ( empty( $subscription->id ) || $subscription->id < 1 ) {
			$this->debug_log( 'no matching subscription found detected, bailing. Data: ' . var_export( $this->posted, true ) );
			$this->terminate( 400, 'No subscription found' );
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
	 * Processes a recurring payment.
	 *
	 * @since 3.2.0
	 * @param \EDD_Subscription $subscription The subscription object.
	 * @param bool              $skip_renew Optional. Whether to skip calling $subscription->renew(). Default false.
	 * @return int|false The payment ID on success, false on failure.
	 */
	private function process_recurring_payment( $subscription, $skip_renew = false ) {
		$transaction_exists = edd_get_order_transaction_by( 'transaction_id', $this->transaction_id );
		if ( ! empty( $transaction_exists ) ) {
			$this->debug_log( 'Transaction ID ' . $this->transaction_id . ' already processed.' );
			return false;
		}

		// verify details.
		$parent_order = edd_get_order( $subscription->parent_payment_id );
		if ( ! $parent_order ) {
			$this->debug_log( 'Parent order not found for subscription ' . $subscription->id );
			return false;
		}

		// Validate currency code.
		if ( ! empty( $this->currency_code ) && ! empty( $parent_order->currency ) ) {
			if ( strtolower( $this->currency_code ) !== strtolower( $parent_order->currency ) ) {
				// the currency code is invalid.
				edd_record_gateway_error(
					__( 'Invalid Currency Code', 'easy-digital-downloads' ),
					sprintf(
						/* translators: %1$d: Subscription ID; %2$s: Expected currency; %3$s: Received currency; %4$s: Transaction ID */
						__( 'Currency mismatch for subscription %1$d. Expected: %2$s, Received: %3$s. Transaction ID: %4$s', 'easy-digital-downloads' ),
						$subscription->id,
						$parent_order->currency,
						$this->currency_code,
						$this->transaction_id
					)
				);

				$this->debug_log( 'subscription ' . $subscription->id . ': invalid currency code detected in IPN data: ' . var_export( $this->posted, true ) );

				$this->terminate( 400, 'Invalid currency code' );
			}
		}

		// Validate amount against subscription amount.
		try {
			$this->validate_subscription_amount( $subscription, $this->amount );
		} catch ( \Exception $e ) {
			$this->debug_log( 'subscription ' . $subscription->id . ': ' . $e->getMessage() );
			$this->terminate( 400, 'Amount validation failed' );
		}

		if ( 'failed' === $this->payment_status ) {
			if ( 'failing' === $subscription->status ) {
				$this->debug_log( 'Subscription ID ' . $subscription->id . ' already failing.' );
				return false;
			}

			$transaction_link = '<a href="https://www.paypal.com/activity/payment/' . $this->transaction_id . '" target="_blank">' . $this->transaction_id . '</a>';
			/* translators: %s: The transaction ID of the failed payment */
			$subscription->add_note( sprintf( __( 'Transaction ID %s failed in PayPal', 'easy-digital-downloads' ), $transaction_link ) );
			$subscription->failing();

			$this->debug_log( 'subscription ' . $subscription->id . ': payment failed in PayPal' );

			$this->terminate( 200, 'Subscription payment failed' );
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
		try {
			$payment_id = $subscription->add_payment( $subscription_payment_args );
		} catch ( \Exception $e ) {
			$this->debug_log(
				sprintf(
					'subscription %d: failed to add payment - %s',
					$subscription->id,
					$e->getMessage()
				)
			);
			return false;
		}

		if ( empty( $payment_id ) ) {
			$this->debug_log( 'subscription ' . $subscription->id . ': renewal payment creation appeared to fail.' );
			return false;
		}

		$this->debug_log( 'subscription ' . $subscription->id . ': renewal payment was recorded successfully' );
		edd_add_order_meta( $payment_id, 'renewal_handler', 'paypal_commerce_ipn', true );

		// Skip renewing if requested (used when creating on-hold renewals).
		if ( $skip_renew ) {
			return $payment_id;
		}

		// Normal renewal processing - renew the subscription.
		$this->debug_log( 'subscription ' . $subscription->id . ': preparing to renew subscription' );
		$subscription->renew( $payment_id );

		if ( 'recurring_payment_outstanding_payment' === $this->txn_type ) {
			/* translators: %s: The collected outstanding balance of the subscription */
			$subscription->add_note( sprintf( __( 'Outstanding subscription balance of %s collected successfully.', 'easy-digital-downloads' ), $this->amount ) );
		}

		$this->terminate( 200, 'Subscription payment successful' );
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

		$this->terminate( 200, 'Subscription cancelled' );
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
			$this->debug_log( 'Subscription ID ' . $subscription->id . ' already completed.' );
			return;
		}

		$subscription->complete();
		$this->debug_log( 'subscription ' . $subscription->id . ': subscription completed.' );

		$this->terminate( 200, 'Subscription completed' );
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
						/* translators: %s: Transaction ID */
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

			$this->terminate( 200, 'Reversal processed' );
		}

		$this->debug_log( 'Processing a refund for original transaction ' . $order->get_transaction_id() );

		$payment_note = sprintf(
		/* translators: 1:  Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
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
			add_filter( 'edd_is_order_refundable_by_override', '__return_true' );
			$refund_id = edd_refund_order( $order->id );
			remove_filter( 'edd_is_order_refundable_by_override', '__return_true' );
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

		$this->terminate( 200, 'Refund processed' );
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

	/**
	 * Terminate IPN processing.
	 *
	 * In production, this stops PHP execution to prevent WordPress from continuing to load.
	 * In test mode, it allows tests to continue running.
	 *
	 * @since 3.6.3
	 *
	 * @param int    $status_code HTTP status code to send. Default 200.
	 * @param string $message     Optional message to log. Default empty.
	 * @return void
	 */
	private function terminate( $status_code = 200, $message = '' ) {
		edd_die( $message, '', $status_code );
	}
}
