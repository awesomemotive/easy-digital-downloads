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

namespace EDD\PayPal\Webhooks\Events;

class Payment_Capture_Denied extends Webhook_Event {

	/**
	 * Processes the webhook event
	 *
	 * @since 2.11
	 *
	 * @throws \Exception
	 */
	protected function process_event() {
		$payment = $this->get_payment_from_capture();

		edd_update_payment_status( $payment->ID, 'failed' );

		$payment->add_note( __( 'PayPal transaction denied.', 'easy-digital-downloads' ) );
	}
}
