<?php
/**
 * PayPal Commerce Refunds
 *
 * @package    EDD\Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\V3\ConnectAPI;
use EDD\Orders\Order;

/**
 * Pre-flights a paypal_commerce refund submission before EDD creates the local
 * refund record.
 *
 * Hooks `wp_ajax_edd_process_refund_form` ahead of EDD's own AJAX handler so
 * we can call PayPal first and surface any gateway-side failure directly to
 * the admin modal. On success the Connect response is cached so the post-flight
 * `edd_refund_order` callback reuses it instead of double-charging the API.
 *
 * @since 3.6.9
 *
 * @return void
 */
function preflight_refund_submission() {
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	if ( ! $order_id ) {
		return;
	}

	$order = edd_get_order( $order_id );
	if ( ! $order || 'paypal_commerce' !== $order->gateway ) {
		return;
	}

	if ( empty( $_POST['data'] ) ) {
		return;
	}
	parse_str( $_POST['data'], $form_data );

	// This callback runs ahead of the one that owns authorization for this request, so it
	// establishes intent itself. Returning lets EDD's handler produce the user-facing error.
	if ( ! \EDD\Orders\Refunds\FormParser::verify_nonce( $form_data ) ) {
		return;
	}

	// Only pre-flight when the admin actually asked for the PayPal-side refund.
	if ( empty( $form_data['edd-paypal-commerce-refund'] ) ) {
		return;
	}

	$mode = ( 'live' === $order->mode ) ? API::MODE_LIVE : API::MODE_SANDBOX;

	$transaction_id = $order->get_transaction_id();
	if ( empty( $transaction_id ) ) {
		return;
	}

	// Only a Connect-onboarded store can authenticate with the proxy; anything else
	// refunds through the direct API, which needs no pre-flight.
	if ( ! V3\Onboarding::is_v3_onboarded( $mode ) ) {
		return;
	}

	// Re-validate amounts the same way EDD core does, so a partial refund here
	// matches the amount EDD will write to its own refund order after we return.
	$order_items = \EDD\Orders\Refunds\FormParser::parse_order_items( $form_data );
	$adjustments = \EDD\Orders\Refunds\FormParser::parse_adjustments( $form_data );

	try {
		$validator = new \EDD\Orders\Refunds\Validator( $order, $order_items, $adjustments );
		$validator->validate_and_calculate_totals();
	} catch ( \Exception $e ) {
		// Validation errors will be surfaced again by EDD's own AJAX handler
		// with the same details — don't intercept here.
		return;
	}

	$refund_total = abs( (float) $validator->total );
	if ( $refund_total <= 0 ) {
		return;
	}

	$refund_args = array( 'capture_id' => $transaction_id );
	if ( abs( $refund_total - abs( (float) $order->total ) ) > 0.001 ) {
		$refund_args['amount'] = array(
			'value'         => (string) edd_sanitize_amount( $refund_total ),
			'currency_code' => $order->currency,
		);
	}

	// Refund by capture ID, which identifies the payment whichever checkout created it.
	$proxy          = new ConnectAPI( $mode );
	$proxy_response = $proxy->post( '/v3/paypal/captures/' . rawurlencode( $transaction_id ) . '/refund', $refund_args );

	if ( is_wp_error( $proxy_response ) || ConnectAPI::is_error( $proxy_response ) ) {
		wp_send_json_error( resolve_refund_error_message( $proxy_response ), 422 );
	}

	// Cache the success payload so refund_transaction() reuses it instead of
	// calling PayPal a second time when the edd_refund_order action fires.
	set_transient( preflight_cache_key( $transaction_id ), $proxy_response, MINUTE_IN_SECONDS );
}
add_action( 'wp_ajax_edd_process_refund_form', __NAMESPACE__ . '\\preflight_refund_submission', 1 );

/**
 * Returns the seller-facing error message for a failed refund Connect response.
 *
 * Centralises the mapping so both the pre-flight handler and the post-flight
 * `refund_transaction()` call surface identical messaging.
 *
 * @since 3.6.9
 *
 * @param mixed $proxy_response Either a WP_Error or a decoded Connect error response.
 * @return string
 */
function resolve_refund_error_message( $proxy_response ): string {
	if ( is_wp_error( $proxy_response ) ) {
		return $proxy_response->get_error_message();
	}

	$insufficient_balance_message = __( 'Refund failed: the seller\'s PayPal balance is insufficient to process this refund. Please add funds to the PayPal account and try again.', 'easy-digital-downloads' );

	if ( 'refund_insufficient_balance' === ConnectAPI::get_error_code( $proxy_response ) ) {
		return $insufficient_balance_message;
	}

	if ( ! empty( $proxy_response['details']['details'] ) ) {
		foreach ( (array) $proxy_response['details']['details'] as $detail ) {
			$issue = is_array( $detail ) ? ( $detail['issue'] ?? '' ) : ( $detail->issue ?? '' );
			if ( in_array( $issue, array( 'INSUFFICIENT_FUNDS', 'TRANSACTION_REFUSED', 'RECEIVER_UNABLE_TO_HONOR_REFUND' ), true ) ) {
				return $insufficient_balance_message;
			}
		}
	}

	return ConnectAPI::get_error_message( $proxy_response );
}

/**
 * Returns the transient key used to cache a pre-flighted refund Connect response.
 *
 * @since 3.6.9
 *
 * @param string $transaction_id Capture ID being refunded.
 * @return string
 */
function preflight_cache_key( string $transaction_id ): string {
	return 'edd_paypal_refund_preflight_' . md5( $transaction_id );
}

/**
 * Shows a checkbox to automatically refund payments in PayPal.
 *
 * @param Order $order
 *
 * @since 3.0
 */
add_action( 'edd_after_submit_refund_table', function( Order $order ) {
	if ( 'paypal_commerce' !== $order->gateway ) {
		return;
	}

	$mode           = ( 'live' === $order->mode ) ? API::MODE_LIVE : API::MODE_SANDBOX;
	$transaction_id = $order->get_transaction_id();

	if ( empty( $transaction_id ) ) {
		return;
	}

	if ( ! V3\Onboarding::is_v3_onboarded( $mode ) ) {
		try {
			new API( $mode );
		} catch ( Exceptions\Authentication_Exception $e ) {
			// Neither Connect nor direct API credentials — nothing can process the refund.
			return;
		}
	}
	?>
	<div class="edd-form-group edd-paypal-refund-transaction">
		<div class="edd-form-group__control">
			<input
				type="checkbox"
				id="edd-paypal-commerce-refund"
				name="edd-paypal-commerce-refund"
				class="edd-form-group__input"
				value="1"
				<?php echo esc_attr( 'on_hold' === $order->status ? 'disabled' : '' ); ?>
			>
			<label for="edd-paypal-commerce-refund" class="edd-form-group__label">
				<?php esc_html_e( 'Refund transaction in PayPal', 'easy-digital-downloads' ); ?>
			</label>
		</div>
		<?php if ( 'on_hold' === $order->status ) : ?>
			<p class="edd-form-group__help description">
				<?php esc_html_e( 'This order is currently on hold. You can create the refund transaction in EDD; PayPal may have already issued a refund.', 'easy-digital-downloads' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
} );

/**
 * If selected, refunds a transaction in PayPal when creating a new refund record.
 *
 * @param int $order_id ID of the order we're processing a refund for.
 * @param int $refund_id ID of the newly created refund record.
 * @param bool $all_refunded Whether or not this was a full refund.
 *
 * @since 3.0
 */
add_action( 'edd_refund_order', function( $order_id, $refund_id, $all_refunded ) {
	if ( ! current_user_can( 'edit_shop_payments', $order_id ) ) {
		return;
	}

	if ( empty( $_POST['data'] ) ) {
		return;
	}

	$order = edd_get_order( $order_id );
	if ( empty( $order->gateway ) || 'paypal_commerce' !== $order->gateway ) {
		return;
	}

	// Get our data out of the serialized string.
	parse_str( $_POST['data'], $form_data );

	if ( empty( $form_data['edd-paypal-commerce-refund'] ) ) {
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

	try {
		refund_transaction( $order, $refund );
	} catch ( \Exception $e ) {
		edd_debug_log( sprintf(
			'Failure when processing refund #%d. Message: %s',
			$refund->id,
			$e->getMessage()
		), true );

		edd_add_note( array(
			'object_id'   => $order->id,
			'object_type' => 'order',
			'user_id'     => is_admin() ? get_current_user_id() : 0,
			'content'     => sprintf(
				/* translators: 1: Refund ID, 2: Error message */
				__( 'Failure when processing PayPal refund #%1$d: %2$s', 'easy-digital-downloads' ),
				$refund->id,
				$e->getMessage()
			)
		) );
	}
}, 10, 3 );

/**
 * Refunds a transaction in PayPal.
 *
 * @link  https://developer.paypal.com/docs/api/payments/v2/#captures_refund
 *
 * @param Order|\EDD_Payment $payment_or_order Order or payment object.
 * @param Order|null         $refund_object   Refund object.
 *
 * @since 2.11
 * @throws API_Exception If the request fails.
 * @throws \Exception    If the transaction ID is missing.
 */
function refund_transaction( $payment_or_order, ?Order $refund_object = null ) {
	$order = false;
	if ( $payment_or_order instanceof Order ) {
		$order = $payment_or_order;
	} elseif ( $payment_or_order instanceof \EDD_Payment ) {
		$order = edd_get_order( $payment_or_order->ID );
	}

	if ( empty( $order ) || ! $order instanceof Order ) {
		return;
	}

	$transaction_id = $order->get_transaction_id();

	if ( empty( $transaction_id ) ) {
		throw new \Exception( __( 'Missing transaction ID.', 'easy-digital-downloads' ) );
	}

	$mode = ( 'live' === $order->mode ) ? API::MODE_LIVE : API::MODE_SANDBOX;

	if ( V3\Onboarding::is_v3_onboarded( $mode ) ) {
		// Connect-onboarded store — route through the proxy.
		$proxy       = new ConnectAPI( $mode );
		$refund_args = array( 'capture_id' => $transaction_id );
		if ( $refund_object instanceof Order ) {
			$refund_args['invoice_id'] = (string) $refund_object->id;
		}
		if ( $refund_object instanceof Order && abs( $refund_object->total ) !== abs( $order->total ) ) {
			$refund_args['amount'] = array(
				'value'         => (string) abs( $refund_object->total ),
				'currency_code' => $refund_object->currency,
			);
		}

		// Reuse the pre-flight response when present so we don't double-call PayPal.
		// The pre-flight handler has already validated the gateway-side refund
		// succeeded; the cached payload still drives the downstream side effects
		// (negative transaction, notes, _edd_paypal_refunded meta).
		$preflight_key = preflight_cache_key( $transaction_id );
		$preflighted   = get_transient( $preflight_key );

		if ( false !== $preflighted ) {
			$proxy_response = $preflighted;
			delete_transient( $preflight_key );
		} else {
			// Refund by capture ID, which identifies the payment whichever checkout created it.
			$proxy_response = $proxy->post( '/v3/paypal/captures/' . rawurlencode( $transaction_id ) . '/refund', $refund_args );
		}

		if ( is_wp_error( $proxy_response ) || ConnectAPI::is_error( $proxy_response ) ) {
			throw new API_Exception( resolve_refund_error_message( $proxy_response ), 500 );
		}

		$response             = json_decode( wp_json_encode( $proxy_response ) );
		$refund_response_code = 201;
	} else {
		// v2 order on a v2 store — direct PayPal API call.
		$api         = new API( $mode );
		$refund_args = $refund_object instanceof Order ? array( 'invoice_id' => $refund_object->id ) : array();
		if ( $refund_object instanceof Order && abs( $refund_object->total ) !== abs( $order->total ) ) {
			$refund_args['amount'] = array(
				'value'         => abs( $refund_object->total ),
				'currency_code' => $refund_object->currency,
			);
		}
		$response = $api->make_request(
			'v2/payments/captures/' . urlencode( $transaction_id ) . '/refund',
			$refund_args,
			array(
				'Prefer' => 'return=representation',
			)
		);
		$refund_response_code = $api->last_response_code;
	}

	if ( 201 !== $refund_response_code ) {
		throw new API_Exception( sprintf(
			/* translators: 1: Response code, 2: Response message */
			__( 'Unexpected response code: %1$d. Response: %2$s', 'easy-digital-downloads' ),
			$refund_response_code,
			json_encode( $response )
		), $refund_response_code );
	}

	if ( empty( $response->status ) || 'COMPLETED' !== strtoupper( $response->status ) ) {
		throw new API_Exception( sprintf(
		/* translators: %s: API response from PayPal */
			__( 'Missing or unexpected refund status. Response: %s', 'easy-digital-downloads' ),
			json_encode( $response )
		) );
	}

	// At this point we can assume it was successful.
	edd_update_order_meta( $order->id, '_edd_paypal_refunded', true );

	if ( ! empty( $response->id ) ) {
		// A full refund sends no amount, so only a returned or sent amount confirms what PayPal refunded.
		$transaction_amount = null;
		$confirmed_amount   = null;
		if ( isset( $response->amount->value ) ) {
			$transaction_amount = $response->amount->value;
			$confirmed_amount   = $response->amount->value;
		} elseif ( $refund_object instanceof Order ) {
			$transaction_amount = abs( $refund_object->total );
			$confirmed_amount   = $refund_args['amount']['value'] ?? null;
		}

		// Add a note to the original order, and, if provided, the new refund object.
		if ( ! is_null( $confirmed_amount ) ) {
			$note_message = sprintf(
				/* translators: 1: amount refunded; 2: transaction ID. */
				__( '%1$s refunded in PayPal. Refund transaction ID: %2$s', 'easy-digital-downloads' ),
				edd_currency_filter( edd_format_amount( $confirmed_amount ), $order->currency ),
				esc_html( $response->id )
			);
		} else {
			$note_message = sprintf(
				/* translators: %s: ID of the refund in PayPal */
				__( 'Successfully refunded in PayPal. Refund transaction ID: %s', 'easy-digital-downloads' ),
				esc_html( $response->id )
			);
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
		if ( $refund_object instanceof Order ) {
			edd_add_order_transaction( array(
				'object_id'      => $refund_object->id,
				'object_type'    => 'order',
				'transaction_id' => sanitize_text_field( $response->id ),
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => edd_negate_amount( $transaction_amount ),
			) );
		}
	}

	/**
	 * Triggers after a successful refund.
	 *
	 * @param Order $order
	 * @since 3.6.7
	 */
	do_action( 'edd_paypal_commerce_refund_purchase', $order );

	if ( has_action( 'edd_paypal_refund_purchase' ) ) {
		_edd_deprecated_hook( 'edd_paypal_refund_purchase', '3.6.7', 'edd_paypal_commerce_refund_purchase' );
		do_action( 'edd_paypal_refund_purchase', edd_get_payment( $order->id ) );
	}
}
