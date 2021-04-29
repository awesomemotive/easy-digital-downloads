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
 * @param \WP_REST_Request $request
 *
 * @since 2.11
 * @return void
 * @throws \Exception
 */
function capture_refunded( $request ) {
	$event_handler = new Payment_Capture_Refunded( $request );
	$event_handler->handle();
}

add_action( 'edd_paypal_webhook_event_payment_capture_refunded', __NAMESPACE__ . '\capture_refunded' );
add_action( 'edd_paypal_webhook_event_payment_capture_reversed', __NAMESPACE__ . '\capture_refunded' );

/**
 * Process capture completed events.
 *
 * @param \WP_REST_Request $request
 *
 * @since 2.11
 * @return void
 * @throws \Exception
 */
function capture_completed( $request ) {
	$event_handler = new Payment_Capture_Completed( $request );
	$event_handler->handle();
}

add_action( 'edd_paypal_webhook_event_payment_capture_completed', __NAMESPACE__ . '\capture_completed' );
