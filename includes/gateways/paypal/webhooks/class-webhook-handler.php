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
	 * Endpoint namespace.
	 *
	 * @since 2.11
	 */
	const REST_NAMESPACE = 'edd/webhooks/v1';

	/**
	 * Endpoint route.
	 *
	 * @since 2.11
	 */
	const REST_ROUTE = 'paypal';

	/**
	 * Webhook payload
	 *
	 * @var object
	 * @since 2.11
	 */
	private $event;

	/**
	 * Registers REST API routes.
	 *
	 * @since 2.11
	 * @return void
	 */
	public function register_routes() {
		register_rest_route( self::REST_NAMESPACE, self::REST_ROUTE, array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'handle_request' ),
			'permission_callback' => array( $this, 'validate_request' )
		) );
	}

	/**
	 * Handles the current request.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @since 2.11
	 * @return \WP_REST_Response
	 */
	public function handle_request( \WP_REST_Request $request ) {
		edd_debug_log( sprintf(
			'PayPal Commerce webhook endpoint loaded. Mode: %s; Event: %s',
			( edd_is_test_mode() ? 'sandbox' : 'live' ),
			$this->event->event_type
		) );

		edd_debug_log( sprintf( 'Payload: %s; Request data: %s', json_encode( $this->event ), json_encode($request->get_body_params()) ) ); // @todo remove

		$action_key = sanitize_key( strtolower( str_replace( '.', '_', $this->event->event_type ) ) );

		try {
			/**
			 * Triggers once the webhook has been verified.
			 * $action_key is a formatted version of the event type:
			 *      - All lowercase
			 *      - Full stops `.` replaced with underscores `_`
			 *
			 * If you hook into this action then throw an exception in your callback function if you want
			 * the webhook to fail. Set the exception code to your desired HTTP response code.
			 * Failed webhooks will be retried.
			 *
			 * @param object $event
			 *
			 * @since 2.11
			 */
			do_action( 'edd_paypal_webhook_event_' . $action_key, $this->event );

			return new \WP_REST_Response( 'Success', 200 );
		} catch ( PayPal\Exceptions\Authentication_Exception $e ) {
			// Failure with PayPal credentials.
			edd_debug_log( sprintf( 'PayPal Commerce Webhook - Exiting due to authentication exception. Message: %s', $e->getMessage() ), true );

			return new \WP_REST_Response( $e->getMessage(), 403 );
		} catch ( PayPal\Exceptions\API_Exception $e ) {
			// Failure with a PayPal API request.
			edd_debug_log( sprintf( 'PayPal Commerce Webhook - Failure due to an API exception. Message: %s', $e->getMessage() ) );

			return new \WP_REST_Response( $e->getMessage(), 500 );
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'PayPal Commerce - Exiting webhook due to an exception. Message: %s', $e->getMessage() ), true );

			$response_code = $e->getCode() > 0 ? $e->getCode() : 500;

			return new \WP_REST_Response( $e->getMessage(), $response_code );
		}
	}

	/**
	 * Validates the webhook
	 *
	 * @since 2.11
	 * @return bool|\WP_Error
	 */
	public function validate_request() {
		if ( ! PayPal\has_rest_api_connection() ) {
			return new \WP_Error( 'missing_api_credentials', 'API credentials not set.' );
		}

		$this->event = json_decode( file_get_contents( 'php://input' ) );

		if ( isset( $this->event->event_type ) ) {
			edd_debug_log( sprintf( 'PayPal Commerce webhook event type: %s', $this->event->event_type ) );
		}

		try {
			Webhook_Validator::validate_from_request( $this->event );

			edd_debug_log( 'PayPal Commerce webhook successfully validated. Passing to handler.' );

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'validation_failure', $e->getMessage() );
		}
	}
}

add_action( 'rest_api_init', function () {
	$handler = new Webhook_Handler();
	$handler->register_routes();
} );
