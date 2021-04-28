<?php
/**
 * Webhook Handler
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\PayPal\Webhooks;

use EDD\PayPal;

class Webhook_Handler {
	/**
	 * Webhook payload
	 *
	 * @var object
	 * @since 2.11
	 */
	private $event;

	/**
	 * Handles the current request.
	 */
	public function handle_request() {
		edd_debug_log( sprintf(
			'PayPal Commerce webhook endpoint loaded. Mode: %s',
			( edd_is_test_mode() ? 'sandbox' : 'live' )
		) );

		try {
			$this->validate_request();
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'PayPal Commerce webhook validation exception: %s', $e->getMessage() ) );

			wp_send_json_error( $e->getMessage(), 403 );
			die();
		}

		$action_key = sanitize_key( strtolower( str_replace( '.', '_', $this->event->event_type ) ) );

		try {
			/**
			 * Triggers once the webhook has been verified.
			 * $action_key is a formatted version of the event type:
			 *      - All lowercase
			 *      - Full stops `.` replaced with underscores `_`
			 *
			 * If you hook into this action then throw an exception in your callback function if you want
			 * the webhook to fail. Failed webhooks will be retried.
			 *
			 * @param object $event
			 *
			 * @since 2.11
			 */
			do_action( 'edd_paypal_webhook_event_' . $action_key, $this->event );

			wp_send_json_success( 'Success', 200 );
		} catch ( \Exception $e ) {
			$response_code = $e->getCode() > 0 ? $e->getCode() : 500;

			wp_send_json_error( $e->getMessage(), $response_code );
		}

		die();
	}

	/**
	 * Listens for webhook events.
	 *
	 * @since 2.11
	 */
	public static function listen() {
		if ( empty( $_GET['edd-listener'] ) || 'paypal_commerce' !== $_GET['edd-listener'] ) {
			return;
		}

		$handler = new Webhook_Handler();
		$handler->handle_request();
	}

	private function validate_request() {
		if ( ! PayPal\has_rest_api_connection() ) {
			throw new \Exception( 'API credentials not set.' );
		}

		$this->event = json_decode( file_get_contents( 'php://input' ) );

		Webhook_Validator::validate_from_request( $this->event );
	}
}

add_action( 'init', array( '\\EDD\\PayPal\\Webhooks\\Webhook_Handler', 'listen' ) );
