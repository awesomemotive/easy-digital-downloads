<?php
/**
 * PayPal Commerce Refunds
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

/**
 * Adds a "Refund in PayPal" checkbox when switching the payment's status to "Refunded".
 *
 * @param int $payment_id
 *
 * @since 2.11
 * @return void
 */
function add_refund_javascript( $payment_id ) {
	$payment = edd_get_payment( $payment_id );

	if ( ! $payment || 'paypal_commerce' !== $payment->gateway ) {
		return;
	}

	$mode = ( 'live' === $payment->mode ) ? API::MODE_LIVE : API::MODE_SANDBOX;

	try {
		$api = new API( $mode );
	} catch ( Exceptions\Authentication_Exception $e ) {
		// If we don't have credentials.
		return;
	}

	$label = __( 'Refund Transaction in PayPal', 'easy-digital-downloads' );
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function ( $ ) {
			$( 'select[name=edd-payment-status]' ).change( function () {
				if ( 'refunded' === $( this ).val() ) {
					$( this ).parent().parent().append( '<input type="checkbox" id="edd-paypal-commerce-refund" name="edd-paypal-commerce-refund" value="1" style="margin-top:0">' );
					$( this ).parent().parent().append( '<label for="edd-paypal-commerce-refund"><?php echo esc_html( $label ); ?></label>' );
				} else {
					$( '#edd-paypal-commerce-refund' ).remove();
					$( 'label[for="edd-paypal-commerce-refund"]' ).remove();
				}
			} );
		} );
	</script>
	<?php
}

add_action( 'edd_view_order_details_before', __NAMESPACE__ . '\add_refund_javascript', 100 );

/**
 * Refunds the transaction in PayPal, if the option was selected.
 *
 * @param \EDD_Payment $payment The payment being refunded.
 *
 * @since 2.11
 * @return void
 */
function maybe_refund_transaction( \EDD_Payment $payment ) {
	if ( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
		return;
	}

	if ( 'paypal_commerce' !== $payment->gateway || empty( $_POST['edd-paypal-commerce-refund'] ) ) {
		return;
	}

	// Payment status should be coming from "publish" or "revoked".
	// @todo In 3.0 use `edd_get_refundable_order_statuses()`
	if ( ! in_array( $payment->old_status, array( 'publish', 'complete', 'revoked', 'edd_subscription' ) ) ) {
		return;
	}

	// If the payment has already been refunded, bail.
	if ( $payment->get_meta( '_edd_paypal_refunded', true ) ) {
		return;
	}

	// Process the refund.
	try {
		refund_transaction( $payment );
	} catch ( \Exception $e ) {
		edd_insert_payment_note( $payment->ID, sprintf(
		/* Translators: %s - The error message */
			__( 'Failed to refund transaction in PayPal. Error Message: %s', 'easy-digital-downloads' ),
			$e->getMessage()
		) );
	}
}

add_action( 'edd_pre_refund_payment', __NAMESPACE__ . '\maybe_refund_transaction', 999 );

/**
 * Refunds a transaction in PayPal.
 *
 * @link  https://developer.paypal.com/docs/api/payments/v2/#captures_refund
 *
 * @param \EDD_Payment $payment
 *
 * @since 2.11
 * @throws Authentication_Exception
 * @throws API_Exception
 * @throws \Exception
 */
function refund_transaction( \EDD_Payment $payment ) {
	$transaction_id = $payment->transaction_id;

	if ( empty( $transaction_id ) ) {
		throw new \Exception( __( 'Missing transaction ID.', 'easy-digital-downloads' ) );
	}

	$mode = ( 'live' === $payment->mode ) ? API::MODE_LIVE : API::MODE_SANDBOX;

	$api = new API( $mode );

	$response = $api->make_request( 'v2/payments/captures/' . urlencode( $transaction_id ) . '/refund' );

	if ( 201 !== $api->last_response_code ) {
		throw new API_Exception( sprintf(
		/* Translators: %d - The HTTP response code; %s - Full API response from PayPal */
			__( 'Unexpected response code: %d. Response: %s', 'easy-digital-downloads' ),
			$api->last_response_code,
			json_encode( $response )
		), $api->last_response_code );
	}

	if ( empty( $response->status ) || 'COMPLETED' !== strtoupper( $response->status ) ) {
		throw new API_Exception( sprintf(
		/* Translators: %s - API response from PayPal */
			__( 'Missing or unexpected refund status. Response: %s', 'easy-digital-downloads' ),
			json_encode( $response )
		) );
	}

	// At this point we can assume it was successful.
	$payment->update_meta( '_edd_paypal_refunded', true );

	if ( ! empty( $response->id ) ) {
		$payment->add_note( sprintf(
		/* Translators: %s - ID of the refund in PayPal */
			__( 'Successfully refunded in PayPal. Refund transaction ID: %s', 'easy-digital-downloads' ),
			esc_html( $response->id )
		) );
	}

	/**
	 * Triggers after a successful refund.
	 *
	 * @param \EDD_Payment $payment
	 */
	do_action( 'edd_paypal_refund_purchase', $payment );
}
