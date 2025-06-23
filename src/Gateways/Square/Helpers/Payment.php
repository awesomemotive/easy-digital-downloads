<?php
/**
 * Payment helper for the Square integration.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\CustomerDetails;
use EDD\Vendor\Square\Models\CreatePaymentRequest;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\RefundPaymentRequest;
use EDD\Vendor\Square\Models\OrderState;
use EDD\Vendor\Square\Models\Order as SquareOrder;
use EDD\Vendor\Square\Models\Payment as SquarePayment;
use EDD\Gateways\Square\ApplicationFee;

/**
 * Payment helper for the Square integration.
 *
 * @since 3.4.0
 */
class Payment {
	/**
	 * Build the payment request.
	 *
	 * @since 3.4.0
	 * @param array $purchase_data The purchase data.
	 * @param array $args The arguments. Additional data for the payment.
	 *
	 * @return CreatePaymentRequest
	 * @throws \Exception If there is an error building the payment request.
	 */
	public static function build_payment_request( $purchase_data, $args, $order ) {
		$amount = new Money();
		$amount->setAmount( $order->getTotalMoney()->getAmount() );
		$amount->setCurrency( $order->getTotalMoney()->getCurrency() );

		$customer_details = new CustomerDetails();
		$customer_details->setCustomerInitiated( true );

		$request = new CreatePaymentRequest(
			$purchase_data['source_id'],
			Api::get_idempotency_key( 'square_create_payment_' )
		);

		$request->setAmountMoney( $amount );

		$application_fee = new ApplicationFee();
		if ( $application_fee->has_application_fee() ) {
			$app_fee_money = new Money();
			$app_fee_money->setAmount( $application_fee->get_application_fee_amount( $order->getTotalMoney()->getAmount() ) );
			$app_fee_money->setCurrency( $order->getTotalMoney()->getCurrency() );

			$request->setAppFeeMoney( $app_fee_money );
		}

		$request->setAutocomplete( true );
		$request->setCustomerId( $order->getCustomerId() );
		$request->setLocationId( $order->getLocationId() );
		$request->setOrderId( $order->getId() );
		$request->setLocationId( $order->getLocationId() );
		$request->setCustomerDetails( $customer_details );

		// If there is a billing address provided, and it is a valid address object, set it.
		if (
			! empty( $args['billing']['address'] ) &&
			$args['billing']['address'] instanceof Address
		) {
			$request->setBillingAddress( $args['billing']['address'] );
		}

		if ( ! empty( $args['buyer_email'] ) ) {
			$request->setBuyerEmailAddress( $args['buyer_email'] );
		}

		if ( ! empty( self::get_note( $purchase_data ) ) ) {
			$request->setNote( self::get_note( $purchase_data ) );
		}

		// If the payment details have a phone number, set it.
		if ( ! empty( $purchase_data['user_info']['phone'] ) ) {
			$request->setBuyerPhoneNumber( $purchase_data['user_info']['phone'] );
		}

		/**
		 * Filter a create payment request object.
		 *
		 * @since 3.4.0
		 *
		 * @param CreatePaymentRequest $request Create payment request object.
		 * @param array                $purchase_data Purchase data.
		 * @param array                $args          Payment arguments.
		 * @param SquareOrder          $order         The order.
		 */
		return apply_filters( 'edd_square_api_prepare_create_payment_request', $request, $purchase_data, $args, $order );
	}

	/**
	 * Create a payment.
	 *
	 * @since 3.4.0
	 * @param CreatePaymentRequest $request The request.
	 * @param SquareOrder          $order The order.
	 *
	 * @return SquarePayment
	 * @throws \Exception Throws an exception if there is an error.
	 */
	public static function create_payment( $request, $order ) {
		$response = Api::client()->getPaymentsApi()->createPayment( $request );

		if ( ! $response->isSuccess() ) {
			foreach ( $response->getErrors() as $error ) {
				edd_debug_log( 'Square error: ' . $error->getDetail() );
			}

			// Pull the first error message to display to the user.
			$error_code    = $response->getErrors()[0]->getCode();
			$error_message = Errors::get_error_message( $error_code );

			// Update the order object, as it has changed since the last request.
			$order = Order::get_order( $order->getId() );

			// Before we throw the exception, we need to cancel the order so that it can be recreated.
			Order::update_order(
				$order,
				array(
					'version'    => $order->getVersion(),
					'locationId' => $order->getLocationId(),
					'state'      => OrderState::CANCELED,
				)
			);

			throw new \Exception(
				sprintf(
					/* translators: 1: The error message (which has already been translated), 2: A reference code for the error, to help with customer support */
					esc_html__( 'There was an error processing your payment - %1$s Reference: %2$s', 'easy-digital-downloads' ),
					$error_message,
					'SQ1005'
				)
			);
		}

		return $response->getResult()->getPayment();
	}

	/**
	 * Refund a payment.
	 *
	 * @since 3.4.0
	 * @param \EDD\Orders\Order $order The order object.
	 * @param \EDD\Orders\Order $refund The refund object.
	 *
	 * @return void
	 * @throws \Exception Throws an exception if there is an error.
	 */
	public static function refund_payment( $order, $refund ) {
		edd_debug_log(
			sprintf(
				'Processing Square refund for order #%d',
				$order->id
			)
		);

		$charge_id = edd_get_payment_transaction_id( $order->id );

		// Bail if no charge ID was found.
		if ( empty( $charge_id ) ) {
			edd_debug_log( sprintf( 'Exiting refund of order #%d. No Square charge found.', $order->id ) );

			return;
		}

		if ( $refund instanceof Order && $order instanceof Order && abs( $refund->total ) !== abs( $order->total ) ) {
			$amount = abs( $refund->total );

			edd_debug_log(
				sprintf(
					'Processing partial Square refund for order #%d. Refund amount: %s; Amount sent to Square: %s',
					$order->id,
					edd_currency_filter( $refund->total, $refund->currency ),
					$amount
				)
			);
		} else {
			$amount = abs( $refund->total );
			edd_debug_log( sprintf( 'Processing full Square refund for order #%d.', $order->id ) );
		}

		if ( ! Currency::is_zero_decimal_currency( $refund->currency ) ) {
			$amount = round( $amount * 100, 0 );
		}

		$amount_money = new Money();
		$amount_money->setAmount( $amount );
		$amount_money->setCurrency( $refund->currency );

		$refund_request = new RefundPaymentRequest(
			Api::get_idempotency_key( 'square_refund_payment_' ),
			$amount_money
		);
		$refund_request->setPaymentId( $charge_id );
		$refund_request->setReason( 'Refund' );

		$response = Api::client()->getRefundsApi()->refundPayment( $refund_request );
		if ( ! $response->isSuccess() ) {
			foreach ( $response->getErrors() as $error ) {
				edd_debug_log( 'Square error: ' . $error->getDetail() );
				throw new \Exception( esc_html__( 'Error 1103: There was an error processing the refund. Please try again.', 'easy-digital-downloads' ) );
			}
		}

		$refund_response = $response->getResult()->getRefund();

		$amount_refunded = (float) $refund_response->getAmountMoney()->getAmount();
		if ( ! Currency::is_zero_decimal_currency( $refund_response->getAmountMoney()->getCurrency() ) ) {
			$amount_refunded = round( $amount_refunded / 100, edd_currency_decimal_filter( 2, strtoupper( $refund_response->getAmountMoney()->getCurrency() ) ) );
		}

		$order_note = sprintf(
			/* translators: %1$s the amount refunded; %2$s Square Refund ID */
			__( '%1$s refunded in Square. Refund ID %2$s', 'easy-digital-downloads' ),
			edd_currency_filter( $amount_refunded, strtoupper( $refund_response->getAmountMoney()->getCurrency() ) ),
			$refund_response->getId()
		);

		edd_insert_payment_note( $order->id, $order_note );

		if ( $refund instanceof Order ) {
			edd_add_order_transaction(
				array(
					'object_id'      => $refund->id,
					'object_type'    => 'order',
					'transaction_id' => sanitize_text_field( $refund_response->getId() ),
					'gateway'        => 'square',
					'status'         => 'complete',
					'total'          => edd_negate_amount( $amount_refunded ),
				)
			);

			edd_add_note(
				array(
					'object_id'   => $refund->id,
					'object_type' => 'order',
					'user_id'     => is_admin() ? get_current_user_id() : 0,
					'content'     => $order_note,
				)
			);
		}
	}

	/**
	 * Get the payment description.
	 *
	 * @since 3.4.0
	 *
	 * @return string The payment description.
	 */
	private static function get_note( $purchase_data ) {
		$purchase_summary = '';

		if ( is_array( $purchase_data['cart_details'] ) && ! empty( $purchase_data['cart_details'] ) ) {
			foreach ( $purchase_data['cart_details'] as $item ) {
				$purchase_summary .= $item['name'];
				$price_id          = isset( $item['item_number']['options']['price_id'] )
					? absint( $item['item_number']['options']['price_id'] ?? 0 )
					: false;

				if ( false !== $price_id ) {
					$purchase_summary .= ' - ' . edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
				}

				$purchase_summary .= ', ';
			}

			$purchase_summary = rtrim( $purchase_summary, ', ' );
		}

		// Square has a maximum of 500 characters in the charge description.
		$purchase_summary = substr( $purchase_summary, 0, 500 );

		return html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' );
	}
}
