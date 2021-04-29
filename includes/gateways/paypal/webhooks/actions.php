<?php
/**
 * Webhook Actions
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\PayPal\Webhooks;

use EDD\PayPal\Webhooks\Events\Payment_Capture_Completed;
use EDD\PayPal\Webhooks\Events\Payment_Capture_Refunded;

/**
 * Process refund events.
 *
 * @param object $event
 *
 * @since 2.11
 * @return void
 */
function capture_refunded( $event ) {
	$event_handler = new Payment_Capture_Refunded( $event );
	$event_handler->handle();
}

add_action( 'edd_paypal_webhook_event_payment_capture_refunded', __NAMESPACE__ . '\capture_refunded' );
add_action( 'edd_paypal_webhook_event_payment_capture_reversed', __NAMESPACE__ . '\capture_refunded' );

/**
 * Process capture completed events.
 *
 * @param object $event
 *
 * @since 2.11
 * @return void
 */
function capture_completed( $event ) {
	$event_handler = new Payment_Capture_Completed( $event );
	$event_handler->handle();
}

add_action( 'edd_paypal_webhook_event_payment_capture_completed', __NAMESPACE__ . '\capture_completed' );
