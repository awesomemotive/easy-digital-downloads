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

namespace EDD\Gateways\PayPal\Webhooks;

use EDD\Gateways\PayPal;

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
		register_rest_route( self::REST_NAMESPACE, self::REST_ROUTE . '/webhook-test', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'handle_test' ),
			'permission_callback' => '__return_true',
		) );

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
			$request->get_param( 'event_type' )
		) );

		edd_debug_log( sprintf( 'Payload: %s', json_encode( $this->event ) ) ); // @todo remove

		try {
			// We need to match this event to one of our handlers.
			$events = get_webhook_events();
			if ( ! array_key_exists( $request->get_param( 'event_type' ), $events ) ) {
				throw new \Exception( sprintf( 'Event not registered. Event: %s', esc_html( $request->get_param( 'event_type' ) ) ), 200 );
			}

			$class_name = $events[ $request->get_param( 'event_type' ) ];

			if ( ! class_exists( $class_name ) ) {
				throw new \Exception( sprintf( 'Class %s doesn\'t exist for event type.', $class_name ), 500 );
			}

			/**
			 * Initialize the handler for this event.
			 *
			 * @var PayPal\Webhooks\Events\Webhook_Event $handler
			 */
			$handler = new $class_name( $request );

			if ( ! method_exists( $handler, 'handle' ) ) {
				throw new \Exception( sprintf( 'handle() method doesn\'t exist in class %s.', $class_name ), 500 );
			}

			edd_debug_log( sprintf( 'PayPal Commerce Webhook - Passing to handler %s', esc_html( $class_name ) ) );

			$handler->handle();

			$action_key = sanitize_key( strtolower( str_replace( '.', '_', $request->get_param( 'event_type' ) ) ) );
			/**
			 * Triggers once the handler has run successfully.
			 * $action_key is a formatted version of the event type:
			 *      - All lowercase
			 *      - Full stops `.` replaced with underscores `_`
			 *
			 * Note: This action hook exists so you can execute custom code *after* a handler has run.
			 * If you're registering a custom event, please build a custom handler by extending
			 * the `Webhook_Event` class and not via this hook.
			 *
			 * @param \WP_REST_Request $event
			 *
			 * @since 2.11
			 */
			do_action( 'edd_paypal_webhook_event_' . $action_key, $request );

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

		try {
			Webhook_Validator::validate_from_request( $this->event );

			edd_debug_log( 'PayPal Commerce webhook successfully validated.' );

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'validation_failure', $e->getMessage() );
		}
	}

	/**
	 * Handles the webhook test request.
	 *
	 * @since 3.2.0
	 *
	 * @param \WP_REST_Request $request
	 */
	public function handle_test( \WP_REST_Request $request ) {
		edd_debug_log( 'PayPal Commerce webhook test endpoint loaded.' );

		return new \WP_REST_Response( array( 'message' => 'success' ), 200 );
	}
}
