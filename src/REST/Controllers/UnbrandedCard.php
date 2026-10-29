<?php
/**
 * Unbranded Card (Advanced Card Processing) REST Controller
 *
 * Handles on-checkout card payments via PayPal's Card Fields component
 * (Advanced Card Processing / "unbranded"). Unlike the branded card button
 * (which opens the PayPal minibrowser) the card fields render directly on the
 * checkout page. Two endpoints mirror the SDK's createOrder / onApprove
 * lifecycle:
 *
 *   - create:  builds the EDD order + a PayPal order requesting 3-D Secure
 *              only when the card/region requires it (SCA_WHEN_REQUIRED); the
 *              Card Fields SDK then collects the card and runs any challenge
 *              client-side.
 *   - capture: after the SDK returns, inspects the 3DS result and declines only
 *              when a required challenge was attempted and failed, then captures
 *              and finalizes.
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
 * UnbrandedCard controller class.
 *
 * @since 3.6.9
 */
class UnbrandedCard {

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
	 * Creates the EDD order and a PayPal order for the Card Fields component.
	 *
	 * The PayPal order is created with 3-D Secure forced on the card source.
	 * No card data is sent here — the Card Fields SDK attaches the card and
	 * runs the 3DS challenge after this returns the order ID.
	 *
	 * @since 3.6.9
	 * @since 3.7.2 Fails the order before contacting PayPal when its discount uses could not be claimed.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create( $request ) {
		$body = $request->get_json_params();

		// Merge the form data into $_POST/$_REQUEST so every hook that reads
		// $_POST — edd_checkout_error_checks, Checkout Fields Manager,
		// terms/privacy consent, reCAPTCHA, etc. — works the same way it does
		// on the standard AJAX checkout path.
		if ( ! empty( $body['form_data'] ) && is_array( $body['form_data'] ) ) {
			$_POST    = array_merge( $_POST, $body['form_data'] );
			$_REQUEST = array_merge( $_REQUEST, $_POST );
		}

		// Validate we have a cart.
		if ( ! edd_get_cart_contents() && ! edd_cart_has_fees() ) {
			return new \WP_Error( 'empty_cart', __( 'Your cart is empty.', 'easy-digital-downloads' ), array( 'status' => 400 ) );
		}

		$email = ! empty( $_POST['edd_email'] ) ? sanitize_email( $_POST['edd_email'] ) : '';
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', __( 'A valid email address is required.', 'easy-digital-downloads' ), array( 'status' => 400 ) );
		}

		$first_name = ! empty( $_POST['edd_first'] ) ? sanitize_text_field( $_POST['edd_first'] ) : '';
		$last_name  = ! empty( $_POST['edd_last'] ) ? sanitize_text_field( $_POST['edd_last'] ) : '';

		if ( empty( $first_name ) ) {
			return new \WP_Error( 'missing_first_name', __( 'First name is required.', 'easy-digital-downloads' ), array( 'status' => 400 ) );
		}

		if ( ! \EDD\Gateways\PayPal\ready_to_accept_payments() ) {
			return new \WP_Error( 'gateway_not_ready', __( 'Payment gateway is not available. Please try again.', 'easy-digital-downloads' ), array( 'status' => 503 ) );
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
			return new \WP_Error( 'order_creation_failed', __( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ), array( 'status' => 500 ) );
		}

		$order_id = edd_build_order( $purchase_data );
		if ( ! $order_id ) {
			edd_debug_log( 'Unbranded card: edd_build_order() failed. Data: ' . wp_json_encode( $purchase_data ), true );
			return new \WP_Error( 'order_creation_failed', __( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ), array( 'status' => 500 ) );
		}
		$refusal = \EDD\Discounts\OrderValidation::refuse_unclaimed( $order_id );
		if ( $refusal ) {
			return \EDD\Discounts\OrderValidation::get_refusal_error( $refusal );
		}

		PurchaseUser::remember_authenticated_user();

		$purchase_units = \EDD\Gateways\PayPal\get_order_purchase_units( $order_id, $purchase_data, $purchase_data );

		// Build the order requesting 3-D Secure only when required. No card data
		// is sent — the Card Fields SDK supplies it and runs any challenge after
		// we return the order ID. SCA_WHEN_REQUIRED lets PayPal/the issuer decide
		// whether a challenge is needed (e.g. EU SCA), so non-SCA cards check out
		// without friction; the capture step then declines only a failed challenge.
		$order_data = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => $purchase_units,
			'payment_source' => array(
				'card' => array(
					'attributes' => array(
						'verification' => array(
							'method' => 'SCA_WHEN_REQUIRED',
						),
					),
				),
			),
		);

		/**
		 * Filters the PayPal order arguments for an unbranded (Advanced Card
		 * Processing) create-order. edd-recurring hooks this to attach its
		 * card-source vault attributes for subscription carts.
		 *
		 * @since 3.6.9
		 *
		 * @param array $order_data    PayPal order API arguments.
		 * @param array $purchase_data EDD purchase data.
		 * @param int   $order_id      EDD order ID.
		 */
		$order_data = apply_filters( 'edd_paypal_unbranded_card_order_arguments', $order_data, $purchase_data, $order_id );

		// Re-assert the verification method. The recurring vault filter assigns
		// the card `attributes` block wholesale, which would otherwise drop the
		// verification method on subscription carts.
		if ( empty( $order_data['payment_source']['card'] ) || ! is_array( $order_data['payment_source']['card'] ) ) {
			$order_data['payment_source']['card'] = array();
		}
		if ( empty( $order_data['payment_source']['card']['attributes'] ) || ! is_array( $order_data['payment_source']['card']['attributes'] ) ) {
			$order_data['payment_source']['card']['attributes'] = array();
		}
		$order_data['payment_source']['card']['attributes']['verification'] = array( 'method' => 'SCA_WHEN_REQUIRED' );

		$proxy    = new ConnectAPI( Gateway::get_paypal_mode() );
		$response = $proxy->post( '/v3/paypal/orders', $order_data );

		if ( is_wp_error( $response ) || ConnectAPI::is_error( $response ) ) {
			edd_debug_log(
				sprintf(
					'Unbranded card: PayPal order creation failed. Order data: %s. Response: %s',
					wp_json_encode( $order_data ),
					wp_json_encode( $response )
				),
				true
			);

			edd_update_order_status( $order_id, 'failed' );

			return new \WP_Error( 'paypal_error', __( 'An error occurred while processing your payment. Please try again.', 'easy-digital-downloads' ), array( 'status' => 502 ) );
		}

		$paypal_order_id = isset( $response['id'] ) ? sanitize_text_field( $response['id'] ) : '';
		if ( empty( $paypal_order_id ) ) {
			edd_debug_log( sprintf( 'Unbranded card: no PayPal order ID returned. Response: %s', wp_json_encode( $response ) ), true );
			edd_update_order_status( $order_id, 'failed' );

			return new \WP_Error( 'paypal_error', __( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ), array( 'status' => 502 ) );
		}

		edd_update_order_meta( $order_id, 'paypal_order_id', $paypal_order_id );
		edd_update_order_meta( $order_id, '_edd_paypal_payment_source', 'unbranded_card' );

		return new \WP_REST_Response(
			array(
				'id'           => $paypal_order_id,
				'edd_order_id' => $order_id,
			),
			200
		);
	}

	/**
	 * Inspects the 3-D Secure result, then captures the order.
	 *
	 * Returns true only when a required 3-D Secure challenge was attempted and failed; every other result proceeds.
	 *
	 * @since 3.6.9
	 * @since 3.7.2 Redeems the order's discounts before capture.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Throwable An unexpected error; the order is put back to pending unless PayPal captured it.
	 */
	public function capture( $request ) {
		$body            = $request->get_json_params();
		$paypal_order_id = ! empty( $body['paypal_order_id'] ) ? sanitize_text_field( $body['paypal_order_id'] ) : '';
		$order_id        = ! empty( $body['edd_order_id'] ) ? absint( $body['edd_order_id'] ) : 0;

		if ( empty( $paypal_order_id ) || empty( $order_id ) ) {
			return new \WP_Error( 'missing_order', __( 'Missing order reference.', 'easy-digital-downloads' ), array( 'status' => 400 ) );
		}

		// Confirm the EDD order exists, is still pending, and matches the
		// PayPal order id we stamped in create() — prevents a tampered request
		// from finalizing an unrelated order.
		$order = edd_get_order( $order_id );
		if ( ! $order || 'pending' !== $order->status ) {
			return new \WP_Error( 'invalid_order', __( 'This order can no longer be processed.', 'easy-digital-downloads' ), array( 'status' => 409 ) );
		}
		if ( edd_get_order_meta( $order_id, 'paypal_order_id', true ) !== $paypal_order_id ) {
			return new \WP_Error( 'order_mismatch', __( 'Order reference mismatch.', 'easy-digital-downloads' ), array( 'status' => 409 ) );
		}
		$response = \EDD\Discounts\OrderValidation::hold_capture(
			$order_id,
			function () use ( $order, $order_id, $paypal_order_id ) {
				// Redeemed only once processing, where the pending-order sweeps can't give back the use claimed here.
				$unredeemed = \EDD\Discounts\OrderValidation::redeem_discounts( $order );
				if ( $unredeemed ) {
					$refusal = \EDD\Discounts\OrderValidation::refuse( $order_id, $unredeemed );
					return \EDD\Discounts\OrderValidation::get_refusal_error( $refusal );
				}

				return $this->capture_held_order( $order_id, $paypal_order_id );
			}
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->finalize_capture( $order_id, $response );
	}

	/**
	 * Checks 3-D Secure and captures an order already moved to processing.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id        The EDD order ID.
	 * @param string $paypal_order_id The PayPal order ID.
	 * @return array|\WP_Error The PayPal capture response, or an error when nothing was captured.
	 */
	private function capture_held_order( $order_id, $paypal_order_id ) {
		$proxy = new ConnectAPI( Gateway::get_paypal_mode() );

		// Verify 3-D Secure liability BEFORE capturing. Reading the order after
		// the SDK's 3DS challenge exposes the authentication result; capturing
		// first and refunding on no-shift would needlessly move money.
		$order_status = $proxy->get( '/v3/paypal/orders/' . rawurlencode( $paypal_order_id ) );
		if ( is_wp_error( $order_status ) || ConnectAPI::is_error( $order_status ) ) {
			edd_debug_log( sprintf( 'Unbranded card: failed to read order %s for 3DS check. Response: %s', $paypal_order_id, wp_json_encode( $order_status ) ), true );
			edd_update_order_status( $order_id, 'failed' );
			return new \WP_Error( 'paypal_error', __( 'An error occurred while verifying your payment. Please try again.', 'easy-digital-downloads' ), array( 'status' => 502 ) );
		}

		if ( $this->challenge_failed( $order_status ) ) {
			edd_debug_log(
				sprintf(
					'Unbranded card: 3DS challenge failed for order %s (EDD #%d). Auth result: %s',
					$paypal_order_id,
					$order_id,
					wp_json_encode( $this->get_authentication_result( $order_status ) )
				)
			);
			edd_update_order_status( $order_id, 'failed' );

			return new \WP_Error(
				'card_authentication_failed',
				__( 'We could not verify your card with your bank. Please try a different card or payment method.', 'easy-digital-downloads' ),
				array( 'status' => 422 )
			);
		}

		// Capture unless a required 3DS challenge failed.
		$response = $proxy->post( '/v3/paypal/orders/' . rawurlencode( $paypal_order_id ) . '/capture', array() );

		if ( is_wp_error( $response ) || ConnectAPI::is_error( $response ) ) {
			edd_debug_log( sprintf( 'Unbranded card: capture failed for order %s. Response: %s', $paypal_order_id, wp_json_encode( $response ) ), true );
			edd_update_order_status( $order_id, 'failed' );
			return new \WP_Error( 'paypal_error', __( 'An error occurred while processing your payment. Please try again.', 'easy-digital-downloads' ), array( 'status' => 502 ) );
		}

		return $response;
	}

	/**
	 * Returns true only when a required 3-D Secure challenge was attempted and failed; every other result proceeds.
	 *
	 * @since 3.6.9
	 *
	 * @param array $order_response PayPal order response.
	 * @return bool True when the order should be declined for a failed challenge.
	 */
	private function challenge_failed( $order_response ) {
		$auth = $this->get_authentication_result( $order_response );

		// No 3DS data means no challenge was required — nothing to fail.
		if ( empty( $auth ) ) {
			return false;
		}

		// Liability sits with the issuer; the challenge passed or may have.
		$liability_shift = isset( $auth['liability_shift'] ) ? strtoupper( (string) $auth['liability_shift'] ) : '';
		if ( in_array( $liability_shift, array( 'YES', 'POSSIBLE' ), true ) ) {
			return false;
		}

		$three_d_secure = ! empty( $auth['three_d_secure'] ) ? (array) $auth['three_d_secure'] : array();
		$enrollment     = isset( $three_d_secure['enrollment_status'] ) ? strtoupper( (string) $three_d_secure['enrollment_status'] ) : '';
		$authentication = isset( $three_d_secure['authentication_status'] ) ? strtoupper( (string) $three_d_secure['authentication_status'] ) : '';

		// The card was enrolled (a challenge was required) but the buyer did not
		// clear it — failed or rejected. This is the only decline case.
		return 'Y' === $enrollment && in_array( $authentication, array( 'N', 'R' ), true );
	}

	/**
	 * Extracts the card authentication_result block from a PayPal order response.
	 *
	 * @since 3.6.9
	 *
	 * @param array $order_response PayPal order response.
	 * @return array
	 */
	private function get_authentication_result( $order_response ) {
		if ( ! empty( $order_response['payment_source']['card']['attributes']['authentication_result'] ) ) {
			return (array) $order_response['payment_source']['card']['attributes']['authentication_result'];
		}
		if ( ! empty( $order_response['payment_source']['card']['authentication_result'] ) ) {
			return (array) $order_response['payment_source']['card']['authentication_result'];
		}

		return array();
	}

	/**
	 * Finalizes the EDD order from a successful capture response.
	 *
	 * Mirrors the Fastlane controller's capture handling: maps the PayPal
	 * capture status to an EDD status, records the transaction, stamps and
	 * persists any vault token, and returns the redirect.
	 *
	 * @since 3.6.9
	 * @since 3.7.2 Fails a declined order before recording it, and leaves an unknown status with a capture pending.
	 *
	 * @param int   $order_id EDD order ID.
	 * @param array $response PayPal capture response.
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function finalize_capture( $order_id, $response ) {
		$transaction_id = '';
		$capture_status = '';
		$capture_reason = '';
		if ( ! empty( $response['purchase_units'][0]['payments']['captures'][0] ) ) {
			$capture        = $response['purchase_units'][0]['payments']['captures'][0];
			$transaction_id = ! empty( $capture['id'] ) ? sanitize_text_field( $capture['id'] ) : '';
			$capture_status = ! empty( $capture['status'] ) ? strtoupper( $capture['status'] ) : '';
			$capture_reason = ! empty( $capture['status_details']['reason'] ) ? sanitize_text_field( $capture['status_details']['reason'] ) : '';
		}

		$final_status = CaptureStatus::to_edd_status( $capture_status );
		$is_declined  = 'failed' === $final_status;

		// A declined capture gives its uses back before anything else can throw.
		if ( $is_declined ) {
			edd_update_order_status( $order_id, 'failed' );
			TransactionRecorder::record_declined( $order_id, $transaction_id );
		} else {
			TransactionRecorder::record( $order_id, $transaction_id );
		}

		// Stamp + persist any vault token before edd_update_order_status fires
		// edd_complete_purchase, so recurring can seed subscription meta
		// synchronously. The shared helper handles extraction, persistence for a
		// logged-in buyer, and the edd_paypal_v3_order_vaulted hook.
		Vault::persist_from_capture( $order_id, $transaction_id, $response );

		if ( ! empty( $final_status ) && ! $is_declined ) {
			edd_update_order_status( $order_id, $final_status );
		}

		if ( $is_declined ) {
			return new \WP_Error( 'payment_declined', __( 'Your payment was declined. Please try a different payment method.', 'easy-digital-downloads' ), array( 'status' => 422 ) );
		}

		if ( 'on_hold' === $final_status ) {
			edd_add_note(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
					'content'     => sprintf(
						/* translators: %s: PayPal capture status reason. */
						__( 'PayPal placed this capture in a pending state (%s). The order will update automatically when PayPal completes review.', 'easy-digital-downloads' ),
						esc_html( $capture_reason ? $capture_reason : $capture_status )
					),
				)
			);

			return new \WP_Error( 'payment_pending', __( 'Your payment is being reviewed by PayPal. You will receive an email once it has been processed.', 'easy-digital-downloads' ), array( 'status' => 202 ) );
		}

		// A response with no capture took no payment, so fail the order and give its uses back.
		if ( '' === $final_status && '' === $transaction_id ) {
			edd_update_order_status( $order_id, 'failed' );
			edd_debug_log( sprintf( 'Unbranded card: PayPal returned no capture for order %d. Marking failed.', $order_id ) );
			return new \WP_Error( 'payment_unknown_status', __( 'An unexpected error occurred while processing your payment. Please try again or use a different payment method.', 'easy-digital-downloads' ), array( 'status' => 502 ) );
		}

		// PayPal took the payment but reported a status we don't map, so leave the order pending for the webhook to settle.
		if ( '' === $final_status ) {
			\EDD\Discounts\OrderValidation::end_capture( $order_id, 'pending' );
			edd_debug_log( sprintf( 'Unbranded card: unrecognized capture status "%s" on order %d. Leaving pending.', $capture_status, $order_id ) );
			return new \WP_Error( 'payment_unknown_status', __( 'Your payment is being reviewed by PayPal. You will receive an email once it has been processed.', 'easy-digital-downloads' ), array( 'status' => 202 ) );
		}

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
