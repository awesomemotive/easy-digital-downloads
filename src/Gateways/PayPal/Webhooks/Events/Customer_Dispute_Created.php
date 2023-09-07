<?php
/**
 * Webhook Event: CUSTOMER.DISPUTE.CREATED
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks\Events
 * @copyright  Copyright (c) 2023, Sandhills Development, LLC
 * @license    GPL2+
 * @since      3.2.0
 */

namespace EDD\Gateways\PayPal\Webhooks\Events;

defined( 'ABSPATH' ) || exit;

class Customer_Dispute_Created extends Webhook_Event {

	/**
	 * Processes the webhook event
	 *
	 * @since 3.2.0
	 *
	 * @throws \Exception
	 */
	protected function process_event() {
		if ( empty( $this->event->resource_type ) || 'dispute' !== $this->event->resource_type ) {
			throw new \Exception( 'Missing or invalid resource_type.', 200 );
		}

		if ( ! isset( $this->event->resource->disputed_transactions ) || ! is_array( $this->event->resource->disputed_transactions ) ) {
			throw new \Exception( 'Missing or invalid disputed_transactions.', 200 );
		}

		$dispute_id = $this->event->resource->dispute_id;
		$messages   = $this->event->resource->messages;
		$message    = ! empty( $messages ) ? reset( $messages ) : false;
		foreach ( $this->event->resource->disputed_transactions as $disputed_transaction ) {
			if ( ! isset( $disputed_transaction->seller_transaction_id ) ) {
				continue;
			}

			$order_id = edd_get_order_id_from_transaction_id( $disputed_transaction->seller_transaction_id );
			if ( ! $order_id ) {
				continue;
			}
			$order = edd_get_order( $order_id );
			if ( 'on_hold' === $order->status ) {
				continue;
			}
			$reasons = array_unique( wp_list_pluck( $disputed_transaction->items, 'reason' ) );
			edd_record_order_dispute( $order_id, $dispute_id, $reasons );
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order_id,
					'content'     => sprintf(
						/* Translators: 1. Dispute ID; 2. Dispute reason code. Example: The PayPal transaction has been disputed. Case ID: PP-R-NMW-10060094. Reason given: non_receipt. */
						__( 'The PayPal transaction has been disputed. Case ID: %1$s. Reason given: %2$s.', 'easy-digital-downloads' ),
						$dispute_id,
						implode( ', ', $reasons )
					),
				)
			);

			if ( ! empty( $message ) ) {
				edd_add_note(
					array(
						'object_type' => 'order',
						'object_id'   => $order_id,
						'content'     => sprintf(
							/* Translators: dispute message added by the customer */
							__( 'PayPal Dispute Message: %s', 'easy-digital-downloads' ),
							$message->content
						),
					)
				);
			}
		}
	}
}
