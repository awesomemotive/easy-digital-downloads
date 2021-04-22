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

namespace EDD\PayPal;

use EDD\PayPal\Exceptions\API_Exception;
use EDD\PayPal\Exceptions\Authentication_Exception;
use EDD\PayPal\Exceptions\Gateway_Exception;

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
	if ( 'paypal_commerce' === edd_get_chosen_gateway() && has_rest_api_connection() && edd_get_cart_total() ) {
		$button = wp_nonce_field( 'edd_process_paypal', 'edd_process_paypal_nonce', true, false ) . '<div id="edd-paypal-container"></div>';
	}

	return $button;
}

add_filter( 'edd_checkout_button_purchase', __NAMESPACE__ . '\override_purchase_button', 10000 );

/**
 * Creates a new order in PayPal and EDD.
 *
 * @param array $purchase_data
 *
 * @since 2.11
 * @return void
 */
function create_order( $purchase_data ) {
	/**
	 * If PayPal Standard is still enabled, bail and let that handle it.
	 *
	 * @see edd_process_paypal_purchase()
	 */
	if ( paypal_standard_enabled() ) {
		return;
	}

	edd_debug_log( 'PayPal - create_order()' );

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
					__( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'easy-digital-downloads' ),
					json_encode( $payment_args )
				)
			);
		}

		$order_data = array(
			'intent'              => 'CAPTURE',
			'purchase_units'      => array(
				array(
					// @todo We could put the breakdown here (tax, discount, etc.)
					'reference_id' => $payment_args['purchase_key'],
					'amount'       => array(
						'currency_code' => edd_get_currency(),
						'value'         => (string) $purchase_data['price']
					),
					'custom_id'    => $payment_id
				)
			),
			'application_context' => array(
				//'locale'              => get_locale(), // PayPal doesn't like this. Might be able to replace `_` with `-`
				'shipping_preference' => 'NO_SHIPPING',
				'user_action'         => 'PAY_NOW',
				'return_url'          => edd_get_checkout_uri(),
				'cancel_url'          => edd_get_failed_transaction_uri( '?payment-id=' . urlencode( $payment_id ) )
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

			if ( ! isset( $response->id ) ) {
				throw new Gateway_Exception(
					__( 'An error occurred while communicating with PayPal. Please try again.', 'easy-digital-downloads' ),
					$api->last_response_code,
					json_encode( $response )
				);
			}

			edd_debug_log( sprintf( '-- Successful PayPal response. PayPal order ID: %d; EDD order ID: %d', esc_html( $response->id ), $payment_id ) );

			// Send successfully created order ID back.
			wp_send_json_success( array(
				'paypal_order_id' => $response->id,
				'edd_order_id'    => $payment_id
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

		edd_set_error( 'paypal-error', $e->getMessage() );
		ob_start();
		edd_print_errors();
		$error_html = ob_get_clean();

		wp_send_json_error( array(
			'error_message' => $e->getMessage(),
			'error_html'    => $error_html
		) );
	}
}

add_action( 'edd_gateway_paypal_commerce', __NAMESPACE__ . '\create_order', 9 );

function capture_order() {
	// @todo nonce
	edd_debug_log( 'PayPal - capture_order()' );
	try {
		if ( empty( $_POST['paypal_order_id'] ) ) {
			throw new Gateway_Exception(
				__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				400,
				__( 'Missing PayPal order ID during capture.', 'easy-digital-downloads' )
			);
		}

		try {
			$api      = new API();
			$response = $api->make_request( 'v2/checkout/orders/' . urlencode( $_POST['paypal_order_id'] ) . '/capture' );

			edd_debug_log( sprintf( '-- PayPal Response code: %d; order ID: %s', $api->last_response_code, esc_html( $_POST['paypal_order_id'] ) ) );

			$payment = $transaction_id = false;
			if ( isset( $response->purchase_units ) && is_array( $response->purchase_units ) ) {
				foreach ( $response->purchase_units as $purchase_unit ) {
					if ( ! empty( $purchase_unit->reference_id ) ) {
						$payment        = edd_get_payment_by( 'key', $purchase_unit->reference_id );
						$transaction_id = isset( $purchase_unit->payments->captures[0]->id ) ? $purchase_unit->payments->captures[0]->id : false;
						break;
					}
				}
			}

			if ( ! empty( $payment ) ) {
				if ( ! empty( $response->status ) && 'COMPLETED' === strtoupper( $response->status ) ) {
					$payment->status = 'complete';
				}

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
			}

			wp_send_json_success( edd_get_success_page_uri() );
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

		wp_send_json_error( $e->getMessage() );
	}
}

add_action( 'wp_ajax_nopriv_edd_capture_paypal_order', __NAMESPACE__ . '\capture_order' );
add_action( 'wp_ajax_edd_capture_paypal_order', __NAMESPACE__ . '\capture_order' );
