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
 * @param string $button
 *
 * @since 2.11
 * @return string
 */
function override_purchase_button( $button ) {
	if ( 'paypal_commerce' === edd_get_chosen_gateway() && edd_get_cart_total() ) {
		ob_start();
		if ( ready_to_accept_payments() ) {
			wp_nonce_field( 'edd_process_paypal', 'edd_process_paypal_nonce' );
			$timestamp = time();
			?>
			<input type="hidden" name="edd-process-paypal-token" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>" />
			<div id="edd-paypal-errors-wrap"></div>
			<div id="edd-paypal-container"></div>
			<div id="edd-paypal-spinner" style="display: none;">
				<span class="edd-loading-ajax edd-loading"></span>
			</div>
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
 * @return void
 */
function send_ajax_errors( $user, $valid_data, $posted ) {
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
 * @param array $purchase_data
 *
 * @since 2.11
 * @return void
 */
function create_order( $purchase_data ) {

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

		wp_send_json_error( edd_build_errors_html( array(
			'paypal-error' => $error_message
		) ) );
	}

	try {
		// Create pending payment in EDD.
		$payment_args = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => edd_get_currency(),
			'downloads'    => $purchase_data['downloads'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info'    => $purchase_data['user_info'],
			'status'       => 'pending',
			'gateway'      => 'paypal_commerce'
		);

		$payment_id = edd_insert_payment( $payment_args );

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

		$order_data = array(
			'intent'               => 'CAPTURE',
			'purchase_units'       => get_order_purchase_units( $payment_id, $purchase_data, $payment_args ),
			'application_context'  => array(
				//'locale'              => get_locale(), // PayPal doesn't like this. Might be able to replace `_` with `-`
				'shipping_preference' => 'NO_SHIPPING',
				'user_action'         => 'PAY_NOW',
				'return_url'          => edd_get_checkout_uri(),
				'cancel_url'          => edd_get_failed_transaction_uri( '?payment-id=' . urlencode( $payment_id ) )
			),
			'payment_instructions' => array(
				'disbursement_mode' => 'INSTANT'
			)
		);

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
			$api      = new API();
			$response = $api->make_request( 'v2/checkout/orders', $order_data );

			if ( ! isset( $response->id ) && _is_item_total_mismatch( $response ) ) {

				edd_record_gateway_error(
					__( 'PayPal Gateway Warning', 'easy-digital-downloads' ),
					sprintf(
						/* Translators: %s - Original order data sent to PayPal. */
						__( 'PayPal could not complete the transaction with the itemized breakdown. Original order data sent: %s', 'easy-digital-downloads' ),
						json_encode( $order_data )
					),
					$payment_id
				);

				// Try again without the item breakdown. That way if we have an error in our totals the whole API request won't fail.
				$order_data['purchase_units'] = array(
					get_order_purchase_units_without_breakdown( $payment_id, $purchase_data, $payment_args )
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

			edd_update_payment_meta( $payment_id, 'paypal_order_id', sanitize_text_field( $response->id ) );

			/*
			 * Send successfully created order ID back.
			 * We also send back a new nonce, for verification in the next step: `capture_order()`.
			 * If the user was just logged into a new account, the previously sent nonce may have
			 * become invalid.
			 */
			$timestamp = time();
			wp_send_json_success( array(
				'paypal_order_id' => $response->id,
				'edd_order_id'    => $payment_id,
				'nonce'           => wp_create_nonce( 'edd_process_paypal' ),
				'timestamp'       => $timestamp,
				'token'           =>  \EDD\Utils\Tokenizer::tokenize( $timestamp ),
			) );
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

		wp_send_json_error( edd_build_errors_html( array(
			'paypal-error' => $e->getMessage()
		) ) );
	}
}

add_action( 'edd_gateway_paypal_commerce', __NAMESPACE__ . '\create_order', 9 );

/**
 * Captures the order in PayPal
 *
 * @since 2.11
 */
function capture_order() {
	edd_debug_log( 'PayPal - capture_order()' );
	try {

		$token     = isset( $_POST['token'] )     ? sanitize_text_field( $_POST['token'] )     : '';
		$timestamp = isset( $_POST['timestamp'] ) ? sanitize_text_field( $_POST['timestamp'] ) : '';

		if ( ! empty( $timestamp ) && ! empty( $token ) ) {
			if ( !\EDD\Utils\Tokenizer::is_token_valid( $token, $timestamp ) ) {
				throw new Gateway_Exception(
					__('A validation error occurred. Please try again.', 'easy-digital-downloads'),
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

		if ( empty( $_POST['paypal_order_id'] ) ) {
			throw new Gateway_Exception(
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				400,
				'Missing PayPal order ID during capture.'
			);
		}

		try {
			$api      = new API();
			$response = $api->make_request( 'v2/checkout/orders/' . urlencode( $_POST['paypal_order_id'] ) . '/capture' );

			edd_debug_log( sprintf( '-- PayPal Response code: %d; order ID: %s', $api->last_response_code, esc_html( $_POST['paypal_order_id'] ) ) );

			if ( ! in_array( $api->last_response_code, array( 200, 201 ) ) ) {
				$message = ! empty( $response->message ) ? $response->message : __( 'Failed to process payment. Please try again.', 'easy-digital-downloads' );

				/*
				 * If capture failed due to funding source, we want to send a `restart` back to PayPal.
				 * @link https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
				 */
				if ( ! empty( $response->details ) && is_array( $response->details ) ) {
					foreach ( $response->details as $detail ) {
						if ( isset( $detail->issue ) && 'INSTRUMENT_DECLINED' === $detail->issue ) {
							$message = __( 'Unable to complete your order with your chosen payment method. Please choose a new funding source.', 'easy-digital-downloads' );
							$retry = true;
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

			$payment = $transaction_id = false;
			if ( isset( $response->purchase_units ) && is_array( $response->purchase_units ) ) {
				foreach ( $response->purchase_units as $purchase_unit ) {
					if ( ! empty( $purchase_unit->reference_id ) ) {
						$payment        = edd_get_payment_by( 'key', $purchase_unit->reference_id );
						$transaction_id = isset( $purchase_unit->payments->captures[0]->id ) ? $purchase_unit->payments->captures[0]->id : false;

						if ( ! empty( $payment ) && isset( $purchase_unit->payments->captures[0]->status ) ) {
							if ( 'COMPLETED' === strtoupper( $purchase_unit->payments->captures[0]->status ) ) {
								$payment->status = 'complete';
							} elseif( 'DECLINED' === strtoupper( $purchase_unit->payments->captures[0]->status ) ) {
								$payment->status = 'failed';
							}
						}
						break;
					}
				}
			}

			if ( ! empty( $payment ) ) {
				/**
				 * Buy Now Button
				 *
				 * Fill in missing data when using "Buy Now". This bypasses checkout so not all information
				 * was collected prior to payment. Instead, we pull it from the PayPal info.
				 */
				if ( empty( $payment->email ) ) {
					if ( ! empty( $response->payer->email_address ) ) {
						$payment->email = sanitize_text_field( $response->payer->email_address );
					}
					if ( empty( $payment->first_name ) && ! empty( $response->payer->name->given_name ) ) {
						$payment->first_name = sanitize_text_field( $response->payer->name->given_name );
					}
					if ( empty( $payment->last_name ) && ! empty( $response->payer->name->surname ) ) {
						$payment->last_name = sanitize_text_field( $response->payer->name->surname );
					}

					if ( empty( $payment->customer_id ) && ! empty( $payment->email ) ) {
						$customer = new \EDD_Customer( $payment->email );

						if ( $customer->id < 1 ) {
							$customer->create( array(
								'email'   => $payment->email,
								'name'    => trim( sprintf( '%s %s', $payment->first_name, $payment->last_name ) ),
								'user_id' => $payment->user_id
							) );
						}
					}
				}

				if ( ! empty( $transaction_id ) ) {
					$payment->transaction_id = sanitize_text_field( $transaction_id );

					edd_insert_payment_note( $payment->ID, sprintf(
					/* Translators: %s - transaction ID */
						__( 'PayPal Transaction ID: %s', 'easy-digital-downloads' ),
						esc_html( $transaction_id )
					) );
				}

				$payment->save();

				if ( 'failed' === $payment->status ) {
					$retry = true;
					throw new Gateway_Exception(
						__( 'Your payment was declined. Please try a new payment method.', 'easy-digital-downloads' ),
						400,
						sprintf( 'Order capture failure. PayPal response: %s', json_encode( $response ) )
					);
				}
			}

			wp_send_json_success( array( 'redirect_url' => edd_get_success_page_uri() ) );
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

		wp_send_json_error( array(
			'message' => edd_build_errors_html( array(
				'paypal_capture_failure' => $e->getMessage()
			) ),
			'retry'   => isset( $retry ) ? $retry : false
		) );
	}
}

add_action( 'wp_ajax_nopriv_edd_capture_paypal_order', __NAMESPACE__ . '\capture_order' );
add_action( 'wp_ajax_edd_capture_paypal_order', __NAMESPACE__ . '\capture_order' );

/**
 * Gets a fresh set of gateway options when a PayPal order is cancelled.
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
