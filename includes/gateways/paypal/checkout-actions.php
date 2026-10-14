<?php
/**
 * PayPal Commerce Checkout Actions
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal;

use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;
use EDD\Gateways\PayPal\Exceptions\Gateway_Exception;
use EDD\Gateways\PayPal\V3\CaptureStatus;
use EDD\Gateways\PayPal\V3\ConnectAPI;
use EDD\Gateways\PayPal\V3\PurchaseUser;
use EDD\Gateways\PayPal\V3\TransactionRecorder;
use EDD\Gateways\PayPal\V3\Vault;

/**
 * Removes the credit card form for PayPal Commerce
 *
 * @access private
 * @since  2.11
 */
add_action( 'edd_paypal_commerce_cc_form', '__return_false' );

/**
 * Replaces the "Submit" button with a PayPal smart button.
 *
 * @param string $button The original button HTML.
 *
 * @since 2.11
 * @return string
 */
function override_purchase_button( $button ) {
	if ( 'paypal_commerce' === edd_get_chosen_gateway() && edd_get_cart_total() ) {

		// PayPal can't process multiple subscriptions or a mixed cart; show the error instead of loading the buttons.
		if ( ! edd_gateway_supports_cart_contents( 'paypal_commerce' ) ) {
			$error_message = ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_is_mixed() )
				? __( 'Subscriptions and non-subscriptions may not be purchased at the same time. Please purchase each separately.', 'easy-digital-downloads' )
				: __( 'Subscriptions must be purchased individually. Please update your cart to only contain a single subscription.', 'easy-digital-downloads' );
			ob_start();
			?>
			<div class="edd_errors edd-alert edd-alert-info">
				<p class="edd_error" id="edd_error_edd-paypal-incompatible-cart">
					<?php echo esc_html( $error_message ); ?>
				</p>
			</div>
			<?php
			return ob_get_clean();
		}

		ob_start();
		if ( ready_to_accept_payments() ) {
			wp_nonce_field( 'edd_process_paypal', 'edd_process_paypal_nonce' );
			$timestamp = time();
			?>
			<input type="hidden" name="edd-process-paypal-token" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>" />
			<div id="edd-paypal-errors-wrap"></div>
			<?php
			$mode         = \EDD\Gateways\PayPal\Gateway::get_paypal_mode();
			$has_fastlane = get_option( "edd_paypal_{$mode}_vaulting_available", false ) && \EDD\Gateways\PayPal\PaymentMethods::is_enabled( 'fastlane', true );

			if ( $has_fastlane ) :
				?>
			<div id="edd-fastlane-container">
				<div id="edd-fastlane-card-container"></div>
				<div id="edd-fastlane-watermark"></div>
				<div id="edd-fastlane-spinner" style="display: none;">
					<span class="edd-loading-ajax edd-loading"></span>
				</div>
				<button id="edd-fastlane-submit" type="button" class="edd-submit button <?php echo esc_attr( edd_get_button_color_class() ); ?>" style="display: none;">
					<?php echo esc_html( edd_get_checkout_button_purchase_label() ); ?>
				</button>
			</div>
			<div id="edd-fastlane-divider" class="edd-fastlane-divider" style="display: none;">
				<span><?php esc_html_e( 'Or Pay with', 'easy-digital-downloads' ); ?></span>
			</div>
			<?php endif; ?>
			<div id="edd-paypal-applepay-container" class="edd-paypal-wallet-container" style="display: none;"></div>
			<div id="edd-paypal-googlepay-container" class="edd-paypal-wallet-container" style="display: none;"></div>
			<div id="edd-paypal-container"></div>
			<div id="edd-paypal-messages"></div>
			<div id="edd-paypal-spinner" style="display: none;">
				<span class="edd-loading-ajax edd-loading"></span>
			</div>
			<?php if ( \EDD\Gateways\PayPal\PaymentMethods::is_enabled( 'unbranded_card', \EDD\Gateways\PayPal\PaymentMethods::default_state( 'unbranded_card' ) ) ) : ?>
			<div id="edd-paypal-unbranded-card-divider" class="edd-paypal-card-divider" style="display: none;">
				<span><?php echo esc_html_x( 'Or', 'Divider between PayPal buttons and the card fields', 'easy-digital-downloads' ); ?></span>
			</div>
			<div id="edd-paypal-unbranded-card-container" class="edd-paypal-unbranded-card" style="display: none;">
				<div id="edd-paypal-card-number" class="edd-paypal-card-field"></div>
				<div class="edd-paypal-card-row">
					<div id="edd-paypal-card-expiry" class="edd-paypal-card-field"></div>
					<div id="edd-paypal-card-cvv" class="edd-paypal-card-field"></div>
				</div>
				<button id="edd-paypal-unbranded-card-submit" type="button" class="edd-submit button <?php echo esc_attr( edd_get_button_color_class() ); ?>">
					<?php echo esc_html( edd_get_checkout_button_purchase_label() ); ?>
				</button>
				<div id="edd-paypal-unbranded-card-spinner" style="display: none;">
					<span class="edd-loading-ajax edd-loading"></span>
				</div>
			</div>
			<?php endif; ?>
			<?php
			/**
			 * Triggers right below the button container.
			 *
			 * @since 2.11
			 */
			do_action( 'edd_paypal_after_button_container' );
		} else {
			$error_message = current_user_can( 'manage_options' )
				? __( 'Please connect your PayPal account in the gateway settings.', 'easy-digital-downloads' )
				: __( 'Unexpected authentication error. Please contact a site administrator.', 'easy-digital-downloads' );
			?>
			<div class="edd_errors edd-alert edd-alert-error">
				<p class="edd_error">
					<?php echo esc_html( $error_message ); ?>
				</p>
			</div>
			<?php
		}

		return ob_get_clean();
	}

	return $button;
}

add_filter( 'edd_checkout_button_purchase', __NAMESPACE__ . '\override_purchase_button', 10000 );

/**
 * Sends checkout error messages via AJAX.
 *
 * This overrides the normal error behaviour in `edd_process_purchase_form()` because we *always*
 * want to send errors back via JSON.
 *
 * @param array $user       User data.
 * @param array $valid_data Validated form data.
 * @param array $posted     Raw $_POST data.
 *
 * @since 2.11
 * @since 3.7.1 Leaves a REST request's response to its own controller.
 * @return void
 */
function send_ajax_errors( $user, $valid_data, $posted ) {
	// A REST controller builds its own response, so ending the request here would replace it.
	if ( \EDD\Utils\Request::is_request( 'rest' ) ) {
		return;
	}

	if ( empty( $valid_data['gateway'] ) || 'paypal_commerce' !== $valid_data['gateway'] ) {
		return;
	}

	$errors = edd_get_errors();
	if ( $errors ) {
		edd_clear_errors();

		wp_send_json_error( edd_build_errors_html( $errors ) );
	}
}

add_action( 'edd_checkout_user_error_checks', __NAMESPACE__ . '\send_ajax_errors', 99999, 3 );

/**
 * Creates a new order in PayPal and EDD.
 *
 * @since 2.11
 * @since 3.7.2 Fails the order before contacting PayPal when its discount uses could not be claimed.
 * @param array $purchase_data Purchase data.
 * @throws Gateway_Exception If an error occurs.
 * @return void
 */
function create_order( $purchase_data ) {

	if ( ! edd_doing_ajax() ) {
		edd_redirect( edd_get_checkout_uri() );
	}

	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_debug_log( 'PayPal - create_order()' );

	if ( ! ready_to_accept_payments() ) {
		edd_record_gateway_error(
			__( 'PayPal Gateway Error', 'easy-digital-downloads' ),
			__( 'Account not ready to accept payments.', 'easy-digital-downloads' )
		);

		$error_message = current_user_can( 'manage_options' )
			? __( 'Please connect your PayPal account in the gateway settings.', 'easy-digital-downloads' )
			: __( 'Unexpected authentication error. Please contact a site administrator.', 'easy-digital-downloads' );

		wp_send_json_error(
			edd_build_errors_html(
				array(
					'paypal-error' => $error_message,
				)
			)
		);
	}

	try {
		// Create pending payment in EDD.
		$payment_args = wp_parse_args(
			$purchase_data,
			array(
				'currency' => edd_get_currency(),
				'status'   => 'pending',
				'gateway'  => 'paypal_commerce',
			)
		);

		$payment_id = edd_build_order( $payment_args );

		if ( ! $payment_id ) {
			throw new Gateway_Exception(
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				500,
				sprintf(
					'Payment creation failed before sending buyer to PayPal. Payment data: %s',
					json_encode( $payment_args )
				)
			);
		}

		$refusal = \EDD\Discounts\OrderValidation::refuse_unclaimed( $payment_id );
		if ( $refusal ) {
			// Shown inline without a reload, so it must not block the buyer's next attempt.
			edd_unset_error( 'edd-discount-error' );
			throw new Gateway_Exception(
				$refusal,
				409,
				'Order discount could not be claimed before sending buyer to PayPal.'
			);
		}

		// Stamp the wallet hint passed by the Apple Pay / Google Pay JS buttons
		// so the receipt label is correct even when PayPal's capture response
		// only includes the underlying card details (PayPal IWT requirement:
		// the thank-you page must show Apple Pay / Google Pay as the payment
		// method, not the generic "PayPal" fallback).
		$wallet_hint = isset( $_POST['edd_paypal_wallet_type'] ) ? sanitize_key( wp_unslash( $_POST['edd_paypal_wallet_type'] ) ) : '';
		if ( in_array( $wallet_hint, array( 'apple_pay', 'google_pay' ), true ) ) {
			edd_update_order_meta( $payment_id, '_edd_paypal_payment_source', $wallet_hint );
		}

		$order_data = array(
			'intent'              => 'CAPTURE',
			'purchase_units'      => get_order_purchase_units( $payment_id, $purchase_data, $payment_args ),
			'application_context' => array(
				// 'locale'              => get_locale(), // PayPal doesn't like this. Might be able to replace `_` with `-`
				'brand_name'          => BrandName::get(),
				'shipping_preference' => 'NO_SHIPPING',
				'user_action'         => 'PAY_NOW',
				'return_url'          => edd_get_checkout_uri(),
				'cancel_url'          => edd_get_failed_transaction_uri( '?payment-id=' . urlencode( $payment_id ) ),
			),
		);

		// Disbursement mode must live inside purchase_units[*].payment_instruction (singular); a top-level payment_instructions (plural) key is silently discarded by PayPal's Orders schema.

		// Add payer data if we have it. We won't have it when using Buy Now buttons.
		if ( ! empty( $purchase_data['user_email'] ) ) {
			$order_data['payer']['email_address'] = $purchase_data['user_email'];
		}
		if ( ! empty( $purchase_data['user_info']['first_name'] ) ) {
			$order_data['payer']['name']['given_name'] = $purchase_data['user_info']['first_name'];
		}
		if ( ! empty( $purchase_data['user_info']['last_name'] ) ) {
			$order_data['payer']['name']['surname'] = $purchase_data['user_info']['last_name'];
		}

		/**
		 * Filters the arguments sent to PayPal.
		 *
		 * @param array $order_data    API request arguments.
		 * @param array $purchase_data Purchase data.
		 * @param int   $payment_id    ID of the EDD payment.
		 *
		 * @since 2.11
		 */
		$order_data = apply_filters( 'edd_paypal_order_arguments', $order_data, $purchase_data, $payment_id );

		try {
			$mode = \EDD\Gateways\PayPal\Gateway::get_paypal_mode();
			if ( 'v3' === CommerceVersion::get_version() ) {
				if ( ! isset( $order_data['payment_source']['paypal']['experience_context'] ) ) {
					$order_data['payment_source']['paypal']['experience_context'] = array();
				}

				// Copy return/cancel URLs into experience_context if not already present
				// (vault flows already set these). PayPal rejects orders that have URLs in
				// both application_context and experience_context with INCOMPATIBLE_PARAMETER_VALUE,
				// so the URLs must live in exactly one place — experience_context wins.
				if ( empty( $order_data['payment_source']['paypal']['experience_context']['return_url'] ) && ! empty( $order_data['application_context']['return_url'] ) ) {
					$order_data['payment_source']['paypal']['experience_context']['return_url'] = $order_data['application_context']['return_url'];
				}
				if ( empty( $order_data['payment_source']['paypal']['experience_context']['cancel_url'] ) && ! empty( $order_data['application_context']['cancel_url'] ) ) {
					$order_data['payment_source']['paypal']['experience_context']['cancel_url'] = $order_data['application_context']['cancel_url'];
				}
				unset( $order_data['application_context']['return_url'] );
				unset( $order_data['application_context']['cancel_url'] );

				$proxy    = new ConnectAPI( $mode );
				$response = $proxy->post( '/v3/paypal/orders', $order_data );

				if ( is_wp_error( $response ) || ConnectAPI::is_error( $response ) ) {
					$message = is_wp_error( $response ) ? $response->get_error_message() : ConnectAPI::get_error_message( $response );
					edd_debug_log(
						sprintf(
							'PayPal v3 order creation failed. Order data: %s. Proxy response: %s',
							wp_json_encode( $order_data ),
							wp_json_encode( $response )
						),
						true
					);
					throw new Gateway_Exception(
						__( 'An error occurred while communicating with PayPal. Please try again.', 'easy-digital-downloads' ),
						500,
						sprintf( 'Proxy order creation failed: %s', $message )
					);
				}

				$order_id = isset( $response['id'] ) ? $response['id'] : '';
				if ( empty( $order_id ) ) {
					throw new Gateway_Exception(
						__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
						500,
						sprintf( 'Proxy returned no order ID: %s', wp_json_encode( $response ) )
					);
				}

				edd_debug_log( sprintf( '-- PayPal v3 proxy order created. PayPal order ID: %s; EDD order ID: %d', esc_html( $order_id ), $payment_id ) );
				edd_update_order_meta( $payment_id, 'paypal_order_id', sanitize_text_field( $order_id ) );

				$timestamp = time();
				wp_send_json_success(
					array(
						'paypal_order_id' => $order_id,
						'edd_order_id'    => $payment_id,
						'total'           => isset( $order_data['purchase_units'][0]['amount']['value'] ) ? $order_data['purchase_units'][0]['amount']['value'] : '',
						'currency'        => isset( $order_data['purchase_units'][0]['amount']['currency_code'] ) ? $order_data['purchase_units'][0]['amount']['currency_code'] : '',
						'nonce'           => wp_create_nonce( 'edd_process_paypal' ),
						'timestamp'       => $timestamp,
						'token'           => \EDD\Utils\Tokenizer::tokenize( $timestamp ),
					)
				);
			}

			$api      = new API();
			$response = $api->make_request( 'v2/checkout/orders', $order_data );

			if ( ! isset( $response->id ) && _is_item_total_mismatch( $response ) ) {

				edd_record_gateway_error(
					__( 'PayPal Gateway Warning', 'easy-digital-downloads' ),
					sprintf(
						/* translators: %s: Original order data sent to PayPal. */
						__( 'PayPal could not complete the transaction with the itemized breakdown. Original order data sent: %s', 'easy-digital-downloads' ),
						json_encode( $order_data )
					),
					$payment_id
				);

				// Try again without the item breakdown. That way if we have an error in our totals the whole API request won't fail.
				$order_data['purchase_units'] = array(
					get_order_purchase_units_without_breakdown( $payment_id, $purchase_data, $payment_args ),
				);

				// Re-apply the filter.
				$order_data = apply_filters( 'edd_paypal_order_arguments', $order_data, $purchase_data, $payment_id );

				$response = $api->make_request( 'v2/checkout/orders', $order_data );
			}

			if ( ! isset( $response->id ) ) {
				throw new Gateway_Exception(
					__( 'An error occurred while communicating with PayPal. Please try again.', 'easy-digital-downloads' ),
					$api->last_response_code,
					sprintf(
						'Unexpected response when creating order: %s',
						json_encode( $response )
					)
				);
			}

			edd_debug_log( sprintf( '-- Successful PayPal response. PayPal order ID: %s; EDD order ID: %d', esc_html( $response->id ), $payment_id ) );

			edd_update_order_meta( $payment_id, 'paypal_order_id', sanitize_text_field( $response->id ) );

			/*
			 * Send successfully created order ID back.
			 * We also send back a new nonce, for verification in the next step: `capture_order()`.
			 * If the user was just logged into a new account, the previously sent nonce may have
			 * become invalid.
			 */
			$timestamp = time();
			wp_send_json_success(
				array(
					'paypal_order_id' => $response->id,
					'edd_order_id'    => $payment_id,
					'total'           => isset( $order_data['purchase_units'][0]['amount']['value'] ) ? $order_data['purchase_units'][0]['amount']['value'] : '',
					'currency'        => isset( $order_data['purchase_units'][0]['amount']['currency_code'] ) ? $order_data['purchase_units'][0]['amount']['currency_code'] : '',
					'nonce'           => wp_create_nonce( 'edd_process_paypal' ),
					'timestamp'       => $timestamp,
					'token'           => \EDD\Utils\Tokenizer::tokenize( $timestamp ),
				)
			);
		} catch ( Authentication_Exception $e ) {
			throw new Gateway_Exception( __( 'An authentication error occurred. Please try again.', 'easy-digital-downloads' ), $e->getCode(), $e->getMessage() );
		} catch ( API_Exception $e ) {
			throw new Gateway_Exception( __( 'An error occurred while communicating with PayPal. Please try again.', 'easy-digital-downloads' ), $e->getCode(), $e->getMessage() );
		}
	} catch ( Gateway_Exception $e ) {
		if ( ! isset( $payment_id ) ) {
			$payment_id = 0;
		}

		$e->record_gateway_error( $payment_id );

		wp_send_json_error(
			edd_build_errors_html(
				array(
					'paypal-error' => $e->getMessage(),
				)
			)
		);
	}
}

add_action( 'edd_gateway_paypal_commerce', __NAMESPACE__ . '\create_order', 9 );

/**
 * Captures the order in PayPal
 *
 * @since 2.11
 * @since 3.7.2 Redeems the order's discounts before capture and retries a declined order.
 * @throws Gateway_Exception If an error occurs.
 * @throws \Throwable An unexpected error; the order is put back unless PayPal captured it.
 */
function capture_order() {
	edd_debug_log( 'PayPal - capture_order()' );
	try {

		$token     = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
		$timestamp = isset( $_POST['timestamp'] ) ? sanitize_text_field( $_POST['timestamp'] ) : '';

		if ( ! empty( $timestamp ) && ! empty( $token ) ) {
			if ( ! \EDD\Utils\Tokenizer::is_token_valid( $token, $timestamp ) ) {
				throw new Gateway_Exception(
					__( 'A validation error occurred. Please try again.', 'easy-digital-downloads' ),
					403,
					'Token validation failed.'
				);
			}
		} elseif ( empty( $token ) && ! empty( $_POST['edd_process_paypal_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['edd_process_paypal_nonce'], 'edd_process_paypal' ) ) {
				throw new Gateway_Exception(
					__( 'A validation error occurred. Please try again.', 'easy-digital-downloads' ),
					403,
					'Nonce validation failed.'
				);
			}
		} else {
			throw new Gateway_Exception(
				__( 'A validation error occurred. Please try again.', 'easy-digital-downloads' ),
				400,
				'Missing validation fields.'
			);
		}

		if ( empty( $_POST['paypal_order_id'] ) || '' === sanitize_text_field( $_POST['paypal_order_id'] ) ) {
			throw new Gateway_Exception(
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				400,
				'Missing PayPal order ID during capture.'
			);
		}

		$capture_order = get_order_for_capture( sanitize_text_field( $_POST['paypal_order_id'] ) );
		if ( ! $capture_order ) {
			throw new Gateway_Exception(
				__( 'This order can no longer be processed.', 'easy-digital-downloads' ),
				409,
				'Capture requested for a PayPal order with no matching EDD order.'
			);
		}

		// A declined capture fails the order, and PayPal then retries it with another payment method from the same checkout.
		$restore_status   = 'failed' === $capture_order->status ? 'failed' : 'pending';
		$is_session_order = absint( EDD()->session->get( 'edd_resume_payment' ) ) === absint( $capture_order->id );
		if ( 'failed' === $capture_order->status && $is_session_order ) {
			// A request that loses the reopen to a double submit is refused by the status check below or by start_capture().
			\EDD\Discounts\OrderValidation::reopen_failed( $capture_order->id );
			$capture_order = edd_get_order( $capture_order->id );
		}

		if ( 'pending' !== $capture_order->status ) {
			throw new Gateway_Exception(
				__( 'This order can no longer be processed.', 'easy-digital-downloads' ),
				409,
				sprintf( 'Capture requested for an order with the status %s.', $capture_order->status )
			);
		}

		if ( ! \EDD\Discounts\OrderValidation::start_capture( $capture_order->id ) ) {
			throw new Gateway_Exception(
				__( 'This order can no longer be processed.', 'easy-digital-downloads' ),
				409,
				'Capture requested for an order that is no longer pending.'
			);
		}
		$capturing_order_id = $capture_order->id;

		// Redeemed only once processing, where the pending-order sweeps can't give back the use claimed here.
		$unredeemed = \EDD\Discounts\OrderValidation::redeem_discounts( $capture_order );
		if ( $unredeemed ) {
			$payment_id = $capture_order->id;
			$refusal    = \EDD\Discounts\OrderValidation::refuse( $payment_id, $unredeemed );
			$refresh    = true;
			throw new Gateway_Exception(
				$refusal,
				409,
				'Order discount could not be redeemed before capture.'
			);
		}

		try {
			$mode = \EDD\Gateways\PayPal\Gateway::get_paypal_mode();
			if ( 'v3' === CommerceVersion::get_version() ) {
				$proxy        = new ConnectAPI( $mode );
				$proxy_result = $proxy->post( '/v3/paypal/orders/' . rawurlencode( sanitize_text_field( $_POST['paypal_order_id'] ) ) . '/capture', array() );

				if ( is_wp_error( $proxy_result ) || ConnectAPI::is_error( $proxy_result ) ) {
					$message = is_wp_error( $proxy_result ) ? $proxy_result->get_error_message() : ConnectAPI::get_error_message( $proxy_result );
					throw new Gateway_Exception( __( 'An error occurred while communicating with PayPal. Please try again.', 'easy-digital-downloads' ), 500, sprintf( 'Proxy capture failed: %s', $message ) );
				}

				// Normalize Connect array response to stdClass for reuse of the existing payment-update logic below.
				$response           = json_decode( wp_json_encode( $proxy_result ) );
				$last_response_code = 200;
			} else {
				$api                = new API();
				$response           = $api->make_request( 'v2/checkout/orders/' . urlencode( $_POST['paypal_order_id'] ) . '/capture' );
				$last_response_code = $api->last_response_code;
			}

			edd_debug_log( sprintf( '-- PayPal Response code: %d; order ID: %s', $last_response_code, esc_html( $_POST['paypal_order_id'] ) ) );

			if ( ! in_array( $last_response_code, array( 200, 201 ), true ) ) {
				$message = ! empty( $response->message ) ? $response->message : __( 'Failed to process payment. Please try again.', 'easy-digital-downloads' );

				/*
				 * If capture failed due to funding source, we want to send a `restart` back to PayPal.
				 * @link https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
				 */
				if ( ! empty( $response->details ) && is_array( $response->details ) ) {
					foreach ( $response->details as $detail ) {
						if ( isset( $detail->issue ) && 'INSTRUMENT_DECLINED' === $detail->issue ) {
							$message = __( 'Unable to complete your order with your chosen payment method. Please choose a new funding source.', 'easy-digital-downloads' );
							$retry   = true;
							break;
						}
					}
				}

				throw new Gateway_Exception(
					$message,
					400,
					sprintf( 'Order capture failure. PayPal response: %s', json_encode( $response ) )
				);
			}

			$order          = false;
			$capture        = false;
			$transaction_id = false;
			$new_status     = '';
			$capture_reason = '';
			if ( isset( $response->purchase_units ) && is_array( $response->purchase_units ) ) {
				foreach ( $response->purchase_units as $purchase_unit ) {
					if ( ! empty( $purchase_unit->reference_id ) ) {
						$order   = edd_get_order_by( 'payment_key', $purchase_unit->reference_id );
						$capture = isset( $purchase_unit->payments->captures[0] ) ? $purchase_unit->payments->captures[0] : false;
						break;
					}
				}

				// The PayPal order was looked up by its ID, so its capture belongs to the order being captured.
				if ( empty( $order ) && ! empty( $capturing_order_id ) ) {
					$order   = edd_get_order( $capturing_order_id );
					$capture = isset( $response->purchase_units[0]->payments->captures[0] ) ? $response->purchase_units[0]->payments->captures[0] : false;
				}
			}

			if ( ! empty( $order ) && ! empty( $capture ) ) {
				$transaction_id = isset( $capture->id ) ? $capture->id : false;
				if ( isset( $capture->status ) ) {
					// Shared capture-status table: PENDING (fraud review /
					// e-check holds) routes to on_hold, and an unknown
					// status stays empty so the order status isn't changed
					// rather than silently completing.
					$new_status = CaptureStatus::to_edd_status( strtoupper( $capture->status ) );
				}
				$capture_reason = ! empty( $capture->status_details->reason )
					? sanitize_text_field( $capture->status_details->reason )
					: '';
			}

			// PayPal has taken the payment, so an error from here on must not put the order back.
			$is_captured = ! empty( $transaction_id ) && 'failed' !== $new_status;

			if ( ! empty( $order ) ) {
				// A declined capture gives its uses back before anything else can throw.
				if ( 'failed' === $new_status ) {
					edd_update_order_status( $order->id, 'failed' );
					TransactionRecorder::record_declined( $order->id, $transaction_id );
				} else {
					// Recorded first, so the webhook can match the payment even if a later write throws.
					TransactionRecorder::record( $order->id, $transaction_id );
				}

				// Stamp the PayPal reason on the order so the
				// merchant has the context PayPal returned (e.g.
				// `PENDING_REVIEW`, `BUYER_COMPLAINT`).
				if ( 'on_hold' === $new_status && ! empty( $capture_reason ) ) {
					edd_add_note(
						array(
							'object_id'   => $order->id,
							'object_type' => 'order',
							'content'     => sprintf(
								/* translators: %s: PayPal capture status reason (e.g. PENDING_REVIEW). */
								__( 'PayPal placed this capture in a pending state (%s). The order will update automatically when PayPal completes review.', 'easy-digital-downloads' ),
								esc_html( $capture_reason )
							),
						)
					);
				}

				/**
				 * Buy Now Button
				 *
				 * Fill in missing data when using "Buy Now". This bypasses checkout so not all information
				 * was collected prior to payment. Instead, we pull it from the PayPal info.
				 */
				if ( empty( $order->email ) ) {
					$order_updates = array();
					$first_name    = '';
					$last_name     = '';

					if ( ! empty( $response->payer->email_address ) ) {
						$order_updates['email'] = sanitize_text_field( $response->payer->email_address );
					}
					if ( ! empty( $response->payer->name->given_name ) ) {
						$first_name = sanitize_text_field( $response->payer->name->given_name );
					}
					if ( ! empty( $response->payer->name->surname ) ) {
						$last_name = sanitize_text_field( $response->payer->name->surname );
					}

					if ( empty( $order->customer_id ) && ! empty( $order_updates['email'] ) ) {
						$customer = edd_get_customer_by( 'email', $order_updates['email'] );

						if ( ! $customer ) {
							$customer_id = edd_add_customer(
								array(
									'email'   => $order_updates['email'],
									'name'    => trim( sprintf( '%s %s', $first_name, $last_name ) ),
									'user_id' => $order->user_id,
								)
							);
						} else {
							$customer_id = $customer->id;
						}

						if ( ! empty( $customer_id ) ) {
							$order_updates['customer_id'] = $customer_id;
						}
					}

					if ( ! empty( $order_updates ) ) {
						edd_update_order( $order->id, $order_updates );
					}

					// Refresh the order to pick up the writes above. We intentionally
					// do NOT touch the order_addresses table here — PayPal payer data
					// only gives us a buyer's first/last name, and address rows are
					// meant to hold full addresses. The customer's name is already
					// captured via edd_add_customer above.
					$order = edd_get_order( $order->id );
				}

				// Store the specific payment source for receipt display.
				// Wallets are checked before `card` because PayPal sometimes
				// includes both keys for wallet payments (the wallet identifies
				// the funding source; `card` holds the underlying tokenized
				// card details). Without wallet-first ordering, Apple Pay /
				// Google Pay orders would mis-render as "Card" on the receipt.
				//
				// We also skip overriding when the order was already stamped
				// with a wallet hint at create time (from the Apple/Google
				// Pay JS buttons) — the JS knows the wallet authoritatively,
				// and some PayPal capture responses don't echo the wallet
				// info back in payment_source.
				if ( ! empty( $response->payment_source ) ) {
					$existing       = edd_get_order_meta( $order->id, '_edd_paypal_payment_source', true );
					$payment_source = 'paypal';
					if ( isset( $response->payment_source->venmo ) ) {
						$payment_source = 'venmo';
					} elseif ( isset( $response->payment_source->apple_pay ) ) {
						$payment_source = 'apple_pay';
					} elseif ( isset( $response->payment_source->google_pay ) ) {
						$payment_source = 'google_pay';
					} elseif ( isset( $response->payment_source->card ) ) {
						$payment_source = 'card';
					}

					if ( empty( $existing ) || ! in_array( $existing, array( 'apple_pay', 'google_pay' ), true ) ) {
						edd_update_order_meta( $order->id, '_edd_paypal_payment_source', $payment_source );
					}

					// Store the PayPal account email when the wallet was used, so the
					// receipt can show the buyer's PayPal email alongside the funding
					// source label (PayPal IWT requirement).
					$payer_email = '';
					if ( ! empty( $response->payment_source->paypal->email_address ) ) {
						$payer_email = $response->payment_source->paypal->email_address;
					} elseif ( 'paypal' === $payment_source && ! empty( $response->payer->email_address ) ) {
						$payer_email = $response->payer->email_address;
					}
					if ( ! empty( $payer_email ) && is_email( $payer_email ) ) {
						edd_update_order_meta( $order->id, '_edd_paypal_payer_email', sanitize_email( $payer_email ) );
					}
				}

				// Stamp + persist any vault token synchronously, before the
				// status update below, via the shared helper. We do this on every
				// capture (not just an opt-in path) because subscription orders
				// force vault=true upstream and need the token recorded
				// synchronously. Relying on the async PAYMENT.CAPTURE.COMPLETED
				// webhook stranded any sub whose webhook failed to deliver. The
				// response is an object here, so normalize it to the array shape
				// the helper reads.
				Vault::persist_from_capture( $order->id, $transaction_id, json_decode( wp_json_encode( $response ), true ) );

				if ( ! empty( $new_status ) && 'failed' !== $new_status ) {
					edd_update_order_status( $order->id, $new_status );
				}

				if ( 'failed' === $new_status ) {
					$retry = true;
					throw new Gateway_Exception(
						__( 'Your payment was declined. Please try a new payment method.', 'easy-digital-downloads' ),
						400,
						sprintf( 'Order capture failure. PayPal response: %s', json_encode( $response ) )
					);
				}
			}

			// An unrecognized capture status leaves the order pending for the webhook to settle.
			if ( isset( $capturing_order_id ) ) {
				\EDD\Discounts\OrderValidation::end_capture( $capturing_order_id, 'pending' );
			}

			if ( ! empty( $order ) ) {
				PurchaseUser::maybe_log_in_after_capture( $order->id );
			}

			wp_send_json_success( array( 'redirect_url' => edd_get_success_page_uri() ) );
		} catch ( Authentication_Exception $e ) {
			throw new Gateway_Exception( __( 'An authentication error occurred. Please try again.', 'easy-digital-downloads' ), $e->getCode(), $e->getMessage() );
		} catch ( API_Exception $e ) {
			throw new Gateway_Exception( __( 'An error occurred while communicating with PayPal. Please try again.', 'easy-digital-downloads' ), $e->getCode(), $e->getMessage() );
		}
	} catch ( Gateway_Exception $e ) {
		if ( isset( $capturing_order_id ) && empty( $is_captured ) ) {
			\EDD\Discounts\OrderValidation::end_capture( $capturing_order_id, $restore_status );
		}

		if ( ! isset( $payment_id ) ) {
			$payment_id = 0;
		}

		$e->record_gateway_error( $payment_id );

		wp_send_json_error(
			array(
				'message' => edd_build_errors_html(
					array(
						'paypal_capture_failure' => $e->getMessage(),
					)
				),
				'retry'   => isset( $retry ) ? $retry : false,
				'refresh' => isset( $refresh ) ? $refresh : false,
			)
		);
	} catch ( \Throwable $e ) {
		if ( isset( $capturing_order_id ) && empty( $is_captured ) ) {
			\EDD\Discounts\OrderValidation::end_capture( $capturing_order_id, $restore_status );
		}

		throw $e;
	}
}

add_action( 'wp_ajax_nopriv_edd_capture_paypal_order', __NAMESPACE__ . '\capture_order' );
add_action( 'wp_ajax_edd_capture_paypal_order', __NAMESPACE__ . '\capture_order' );

/**
 * Finds the order a PayPal capture request is for.
 *
 * Checks the order this checkout session built first, so the meta lookup only runs without one.
 *
 * @since 3.7.2
 *
 * @param string $paypal_order_id The PayPal order ID.
 * @return \EDD\Orders\Order|false
 */
function get_order_for_capture( $paypal_order_id ) {
	$session_order_id = absint( EDD()->session->get( 'edd_resume_payment' ) );
	if ( $session_order_id && edd_get_order_meta( $session_order_id, 'paypal_order_id', true ) === $paypal_order_id ) {
		return edd_get_order( $session_order_id );
	}

	$orders = edd_get_orders(
		array(
			'number'     => 1,
			'meta_query' => array(
				array(
					'key'   => 'paypal_order_id',
					'value' => $paypal_order_id,
				),
			),
		)
	);

	return ! empty( $orders ) ? $orders[0] : false;
}

/**
 * Gets a fresh set of gateway options when a PayPal order is cancelled.
 *
 * @link https://github.com/awesomemotive/easy-digital-downloads/issues/8883
 *
 * @since 2.11.3
 * @return void
 */
function cancel_order() {
	$nonces   = array();
	$gateways = edd_get_enabled_payment_gateways( true );
	foreach ( $gateways as $gateway_id => $gateway ) {
		$nonces[ $gateway_id ] = wp_create_nonce( 'edd-gateway-selected-' . esc_attr( $gateway_id ) );
	}

	wp_send_json_success(
		array(
			'nonces' => $nonces,
		)
	);
}
add_action( 'wp_ajax_nopriv_edd_cancel_paypal_order', __NAMESPACE__ . '\cancel_order' );
add_action( 'wp_ajax_edd_cancel_paypal_order', __NAMESPACE__ . '\cancel_order' );
