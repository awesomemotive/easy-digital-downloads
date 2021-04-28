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

function sale_refunded( $event ) {
	edd_debug_log( var_export( $event, true ), true ); // @todo remove
}

add_action( 'edd_paypal_webhook_event_payment_sale_refunded', __NAMESPACE__ . '\sale_refunded' );
add_action( 'edd_paypal_webhook_event_payment_sale_reversed', __NAMESPACE__ . '\sale_refunded' );

function sale_completed( $event ) {
	edd_debug_log( var_export( $event, true ), true ); // @todo remove
}

add_action( 'edd_paypal_webhook_event_payment_sale_completed', __NAMESPACE__ . '\sale_completed' );
