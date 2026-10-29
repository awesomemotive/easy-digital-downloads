<?php
/**
 * Fastlane REST Controller
 *
 * Handles PayPal Fastlane card payment processing via REST API.
 *
 * @package     EDD\REST\Controllers
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.9
 */

namespace EDD\REST\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\REST\Security;
use EDD\Gateways\PayPal\Gateway;
use EDD\Gateways\PayPal\V3\CaptureStatus;
use EDD\Gateways\PayPal\V3\ConnectAPI;
use EDD\Gateways\PayPal\V3\PurchaseUser;
use EDD\Gateways\PayPal\V3\TransactionRecorder;
use EDD\Gateways\PayPal\V3\Vault;
use EDD\Sessions\PurchaseData;

/**
 * Fastlane controller class.
 *
 * Handles Fastlane card payment processing via REST API.
 *
 * @since 3.6.9
 */
class Fastlane {

	/**
	 * Security instance.
	 *
	 * @since 3.6.9
	 * @var Security
	 */
	private $security;

	/**
	 * Constructor.
	 *
	 * @since 3.6.9
	 * @param Security $security Security instance.
	 */
	public function __construct( Security $security ) {
		$this->security = $security;
	}

	/**
	 * Process a Fastlane card payment.
	 *
	 * Receives a single-use token from the Fastlane card component,
	 * creates a pending EDD order, sends the order to PayPal with
	 * payment_source.card.single_use_token for immediate capture,
	 * and completes the EDD order.
	 *
	 * @since 3.6.9
	 * @since 3.7.2 Fails the order before contacting PayPal when its discount uses could not be claimed.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Throwable An unexpected error; the order is put back to pending unless PayPal took payment.
	 */
	public function process_payment( $request ) {
		$body = $request->get_json_params();

		$payment_token = ! empty( $body['payment_token'] ) ? sanitize_text_field( $body['payment_token'] ) : '';
		$metadata_id   = ! empty( $body['metadata_id'] ) ? sanitize_text_field( $body['metadata_id'] ) : '';

		// Merge the form data into $_POST/$_REQUEST so every hook that reads
		// $_POST — edd_checkout_error_checks, Checkout Fields Manager,
		// terms/privacy consent, reCAPTCHA, etc. — works the same way it does
		// on the standard AJAX checkout path.
		if ( ! empty( $body['form_data'] ) && is_array( $body['form_data'] ) ) {
			$_POST    = array_merge( $_POST, $body['form_data'] );
			$_REQUEST = array_merge( $_REQUEST, $_POST );
		}

		if ( empty( $payment_token ) ) {
			return new \WP_Error(
				'missing_payment_token',
				__( 'Payment token is required.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		// Validate we have a cart.
		if ( ! edd_get_cart_contents() && ! edd_cart_has_fees() ) {
			return new \WP_Error(
				'empty_cart',
				__( 'Your cart is empty.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		// Validate required checkout fields.
		$email = ! empty( $_POST['edd_email'] ) ? sanitize_email( $_POST['edd_email'] ) : '';
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_Error(
				'invalid_email',
				__( 'A valid email address is required.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		$first_name = ! empty( $_POST['edd_first'] ) ? sanitize_text_field( $_POST['edd_first'] ) : '';
		$last_name  = ! empty( $_POST['edd_last'] ) ? sanitize_text_field( $_POST['edd_last'] ) : '';

		if ( empty( $first_name ) ) {
			return new \WP_Error(
				'missing_first_name',
				__( 'First name is required.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		// Check PayPal is ready.
		if ( ! \EDD\Gateways\PayPal\ready_to_accept_payments() ) {
			return new \WP_Error(
				'gateway_not_ready',
				__( 'Payment gateway is not available. Please try again.', 'easy-digital-downloads' ),
				array( 'status' => 503 )
			);
		}

		$address = array(
			'line1'   => ! empty( $_POST['card_address'] ) ? sanitize_text_field( $_POST['card_address'] ) : '',
			'line2'   => ! empty( $_POST['card_address_2'] ) ? sanitize_text_field( $_POST['card_address_2'] ) : '',
			'city'    => ! empty( $_POST['card_city'] ) ? sanitize_text_field( $_POST['card_city'] ) : '',
			'state'   => ! empty( $_POST['card_state'] ) ? sanitize_text_field( $_POST['card_state'] ) : '',
			'country' => ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '',
			'zip'     => ! empty( $_POST['card_zip'] ) ? sanitize_text_field( $_POST['card_zip'] ) : '',
		);

		// Run the standard checkout validation hooks so extensions that gate on
		// edd_checkout_error_checks (Checkout Fields Manager required fields,
		// terms/privacy consent, reCAPTCHA) fire before order creation.
		do_action( 'edd_checkout_error_checks', array( 'gateway' => 'paypal_commerce' ), $_POST );
		$checkout_errors = edd_get_errors();
		if ( $checkout_errors ) {
			edd_clear_errors();
			return new \WP_Error( 'checkout_validation_failed', reset( $checkout_errors ), array( 'status' => 400 ) );
		}

		$resolved = PurchaseUser::resolve( $_POST, $email, $first_name, $last_name, $address );
		if ( is_wp_error( $resolved ) ) {
			return $resolved;
		}

		$purchase_data = PurchaseData::set( $resolved['valid_data'], $resolved['user'] );

		if ( empty( $purchase_data ) ) {
			return new \WP_Error(
				'order_creation_failed',
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				array( 'status' => 500 )
			);
		}

		// Create pending EDD order.
		$order_id = edd_build_order( $purchase_data );

		if ( ! $order_id ) {
			edd_debug_log( 'Fastlane: edd_build_order() failed. Data: ' . wp_json_encode( $purchase_data ), true );
			return new \WP_Error(
				'order_creation_failed',
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				array( 'status' => 500 )
			);
		}

		$refusal = \EDD\Discounts\OrderValidation::refuse_unclaimed( $order_id );
		if ( $refusal ) {
			return \EDD\Discounts\OrderValidation::get_refusal_error( $refusal );
		}

		PurchaseUser::remember_authenticated_user();

		edd_debug_log( sprintf( 'Fastlane: EDD order %d created, sending to PayPal.', $order_id ) );

		// Build PayPal order data.
		$purchase_units = \EDD\Gateways\PayPal\get_order_purchase_units( $order_id, $purchase_data, $purchase_data );

		$order_data = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => $purchase_units,
			'payment_source' => array(
				'card' => array(
					'single_use_token' => $payment_token,
				),
			),
		);

		// Include metadata ID for Fastlane fraud scoring.
		if ( ! empty( $metadata_id ) ) {
			$order_data['_metadata_id'] = $metadata_id;
		}

		// Add payer data.
		$order_data['payer'] = array(
			'email_address' => $email,
		);
		if ( ! empty( $first_name ) ) {
			$order_data['payer']['name']['given_name'] = $first_name;
		}
		if ( ! empty( $last_name ) ) {
			$order_data['payer']['name']['surname'] = $last_name;
		}

		/**
		 * Filters the Fastlane PayPal order arguments.
		 *
		 * @since 3.6.9
		 *
		 * @param array $order_data    API request arguments.
		 * @param array $purchase_data Purchase data.
		 * @param int   $order_id      ID of the EDD order.
		 */
		$order_data = apply_filters( 'edd_paypal_fastlane_order_arguments', $order_data, $purchase_data, $order_id );

		$response = \EDD\Discounts\OrderValidation::hold_capture(
			$order_id,
			function () use ( $order_id, $order_data ) {
				return $this->pay_held_order( $order_id, $order_data );
			}
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->record_payment( $order_id, $response );
	}

	/**
	 * Sends an order already moved to processing to PayPal.
	 *
	 * @since 3.7.2
	 *
	 * @param int   $order_id   The EDD order ID.
	 * @param array $order_data The PayPal order arguments.
	 * @return array|\WP_Error The PayPal order response, or an error when PayPal did not create the order.
	 */
	private function pay_held_order( $order_id, $order_data ) {
		// Send to the Connect service. With payment_source.card present, PayPal auto-captures.
		$proxy    = new ConnectAPI( Gateway::get_paypal_mode() );
		$response = $proxy->post( '/v3/paypal/orders', $order_data );

		if ( is_wp_error( $response ) || ConnectAPI::is_error( $response ) ) {
			edd_debug_log(
				sprintf(
					'Fastlane: PayPal order creation failed. Order data: %s. Response: %s',
					wp_json_encode( $order_data ),
					wp_json_encode( $response )
				),
				true
			);

			// Mark the EDD order as failed.
			edd_update_order_status( $order_id, 'failed' );

			return new \WP_Error(
				'paypal_error',
				__( 'An error occurred while processing your payment. Please try again.', 'easy-digital-downloads' ),
				array( 'status' => 502 )
			);
		}

		$paypal_order_id = isset( $response['id'] ) ? sanitize_text_field( $response['id'] ) : '';
		if ( empty( $paypal_order_id ) ) {
			edd_debug_log( sprintf( 'Fastlane: No PayPal order ID returned. Response: %s', wp_json_encode( $response ) ), true );
			edd_update_order_status( $order_id, 'failed' );

			return new \WP_Error(
				'paypal_error',
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				array( 'status' => 502 )
			);
		}

		return $response;
	}

	/**
	 * Records the payment PayPal took for an order and settles its status.
	 *
	 * @since 3.7.2
	 *
	 * @param int   $order_id The EDD order ID.
	 * @param array $response The PayPal order response.
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function record_payment( $order_id, $response ) {
		$paypal_order_id = sanitize_text_field( $response['id'] );

		// Extract transaction ID from auto-captured response.
		$transaction_id = '';
		$capture_status = '';
		$capture_reason = '';
		if ( ! empty( $response['purchase_units'][0]['payments']['captures'][0] ) ) {
			$capture        = $response['purchase_units'][0]['payments']['captures'][0];
			$transaction_id = ! empty( $capture['id'] ) ? sanitize_text_field( $capture['id'] ) : '';
			$capture_status = ! empty( $capture['status'] ) ? strtoupper( $capture['status'] ) : '';
			$capture_reason = ! empty( $capture['status_details']['reason'] )
				? sanitize_text_field( $capture['status_details']['reason'] )
				: '';
		}

		// Map the PayPal capture status to an EDD order status. PENDING
		// (with reasons like PENDING_REVIEW for fraud-flagged transactions)
		// is common in production and must route to `on_hold` so the order
		// remains pending review rather than landing the buyer on the
		// success page. Unknown statuses are treated as failures rather
		// than silent successes.
		$final_status = CaptureStatus::to_edd_status( $capture_status );
		$is_declined  = 'failed' === $final_status;

		// A declined capture gives its uses back before anything else can throw.
		if ( $is_declined ) {
			edd_update_order_status( $order_id, 'failed' );
			TransactionRecorder::record_declined( $order_id, $transaction_id );
		} else {
			TransactionRecorder::record( $order_id, $transaction_id );
		}

		// Store PayPal order ID + the source so the Payment Gateways
		// breakdown report distinguishes Fastlane from regular PayPal Card.
		edd_update_order_meta( $order_id, 'paypal_order_id', $paypal_order_id );
		edd_update_order_meta( $order_id, '_edd_paypal_payment_source', 'fastlane' );

		// Stamp + persist any vault token from the capture response before the
		// order completes, so maybe_create_vault_subscriptions can seed
		// subscription meta synchronously off the order meta this stamps. The
		// shared helper handles extraction (card source here), persistence for a
		// logged-in buyer, and the edd_paypal_v3_order_vaulted hook.
		Vault::persist_from_capture( $order_id, $transaction_id, $response );

		if ( ! empty( $final_status ) && ! $is_declined ) {
			edd_update_order_status( $order_id, $final_status );
		}

		if ( $is_declined ) {
			return new \WP_Error(
				'payment_declined',
				__( 'Your payment was declined. Please try a different payment method.', 'easy-digital-downloads' ),
				array( 'status' => 422 )
			);
		}

		// PENDING captures (typically fraud-review or e-check holds) leave
		// the order in `on_hold`. Don't clear the cart and don't redirect to
		// the receipt — the buyer needs to know the payment is being
		// reviewed and we may need them to re-attempt if PayPal ultimately
		// declines. Record the PayPal reason on the order for the merchant.
		if ( 'on_hold' === $final_status ) {
			edd_add_note(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'content'     => sprintf(
						/* translators: %s: PayPal capture status reason (e.g. PENDING_REVIEW). */
						__( 'PayPal placed this capture in a pending state (%s). The order will update automatically when PayPal completes review.', 'easy-digital-downloads' ),
						esc_html( $capture_reason ? $capture_reason : $capture_status )
					),
				)
			);

			edd_debug_log(
				sprintf(
					'Fastlane: Payment %d placed on hold. Status: %s, Reason: %s, PayPal order: %s, Transaction: %s',
					$order_id,
					$capture_status,
					$capture_reason,
					$paypal_order_id,
					$transaction_id
				)
			);

			return new \WP_Error(
				'payment_pending',
				__( 'Your payment is being reviewed by PayPal. You will receive an email once it has been processed.', 'easy-digital-downloads' ),
				array( 'status' => 202 )
			);
		}

		// A response with no capture took no payment, so fail the order and give its uses back.
		if ( '' === $final_status && '' === $transaction_id ) {
			edd_update_order_status( $order_id, 'failed' );
			edd_debug_log( sprintf( 'Fastlane: PayPal returned no capture for order %d. Marking failed.', $order_id ) );

			return new \WP_Error(
				'payment_unknown_status',
				__( 'An unexpected error occurred while processing your payment. Please try again or use a different payment method.', 'easy-digital-downloads' ),
				array( 'status' => 502 )
			);
		}

		// PayPal took the payment but reported a status we don't map, so leave the order pending for the webhook to settle.
		if ( '' === $final_status ) {
			\EDD\Discounts\OrderValidation::end_capture( $order_id, 'pending' );
			edd_debug_log(
				sprintf(
					'Fastlane: Unrecognized PayPal capture status "%s" on order %d. Leaving pending. Reason: %s',
					$capture_status,
					$order_id,
					$capture_reason
				)
			);

			return new \WP_Error(
				'payment_unknown_status',
				__( 'Your payment is being reviewed by PayPal. You will receive an email once it has been processed.', 'easy-digital-downloads' ),
				array( 'status' => 202 )
			);
		}

		edd_debug_log( sprintf( 'Fastlane: Payment %d completed. PayPal order: %s, Transaction: %s', $order_id, $paypal_order_id, $transaction_id ) );

		// Clear the cart.
		edd_empty_cart();

		PurchaseUser::maybe_log_in_after_capture( $order_id );

		$timestamp = time();
		return new \WP_REST_Response(
			array(
				'redirect_url' => edd_get_success_page_uri(),
				'order_id'     => $order_id,
				'token'        => $this->security->generate_token( $timestamp ),
				'timestamp'    => $timestamp,
			),
			200
		);
	}
}
