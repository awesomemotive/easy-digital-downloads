<?php
/**
 * Webhook Events:
 *
 * - PAYMENT.CAPTURE.REFUNDED - Merchant refunds a sale.
 * - PAYMENT.CAPTURE.REVERSED - PayPal reverses a sale.
 *
 * @package    EDD\Gateways\PayPal\Webhooks\Events
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      2.11
 */

namespace EDD\Gateways\PayPal\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;
use EDD\Gateways\PayPal\Traits\Refunds;

class Payment_Capture_Refunded extends Webhook_Event {
	use Refunds;

	/**
	 * Processes the event.
	 *
	 * @throws API_Exception
	 * @throws Authentication_Exception
	 * @throws \Exception
	 *
	 * @since 2.11
	 */
	protected function process_event() {
		// Bail if this refund transaction already exists.
		if ( $this->refund_transaction_exists() ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as refund transaction already exists.' );

			return;
		}

		$order = $this->get_order_from_refund();

		if ( 'refunded' === $order->status ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as payment status is already refunded.' );

			return;
		}

		$refunded_amount = isset( $this->event->resource->amount->value ) ? $this->event->resource->amount->value : edd_get_payment_amount( $order->id );
		$currency        = isset( $this->event->resource->amount->currency_code ) ? $this->event->resource->amount->currency_code : $order->currency;

		$this->process_refund(
			$order,
			$refunded_amount,
			$currency,
			$this->event->resource->id,
			array(
				'full'    => __( 'Full refund processed in PayPal.', 'easy-digital-downloads' ),
				'partial' => __( 'Partial refund processed in PayPal.', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Determines whether a transaction record exists for this refund.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function refund_transaction_exists() {
		if ( ! isset( $this->event->resource->id ) ) {
			throw new \Exception( 'No resource ID found.', 200 );
		}

		$transaction = edd_get_order_transaction_by( 'transaction_id', $this->event->resource->id );

		return ! empty( $transaction );
	}
}
