<?php
/**
 * Webhook Event: PAYMENT.CAPTURE.DENIED
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks\Events
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal\Webhooks\Events;

class Payment_Capture_Denied extends Webhook_Event {

	/**
	 * Processes the webhook event
	 *
	 * @since 2.11
	 *
	 * @throws \Exception
	 */
	protected function process_event() {
		$order = $this->get_order_from_capture();

		edd_update_order_status( $order->id, 'failed' );

		edd_add_note( array(
			'object_type' => 'order',
			'object_id'   => $order->id,
			'content'     => __( 'PayPal transaction denied.', 'easy-digital-downloads' ),
		) );
	}
}
