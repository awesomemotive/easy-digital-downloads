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

/**
 * Webhook Handler For PayPal.
 *
 * @since 3.6.9
 */
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
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE . '/webhook-test',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle_test' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_request' ),
				'permission_callback' => array( $this, 'validate_request' ),
			)
		);
	}

	/**
	 * Handles the current request.
	 *
	 * @param \WP_REST_Request $request The REST API Request object.
	 *
	 * @since 2.11
	 * @return \WP_REST_Response
	 *
	 * @throws \Exception Upon failure, an exception is thrown.
	 */
	public function handle_request( \WP_REST_Request $request ) {
		$event_mode = self::resolve_event_mode();
		edd_debug_log(
			sprintf(
				'PayPal Commerce webhook endpoint loaded. Mode: %s; Event: %s',
				'' !== $event_mode ? $event_mode : ( edd_is_test_mode() ? 'sandbox' : 'live' ),
				$request->get_param( 'event_type' )
			)
		);

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

			$action_key = sanitize_key( strtolower( str_replace( '.', '_', $request->get_param( 'event_type' ) ) ) );

			do_action( 'edd_pre_paypal_webhook_event', $request->get_param( 'event_type' ), $request );
			do_action( 'edd_pre_paypal_webhook_event_' . $action_key, $request );

			$handler->handle();

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
		$raw_body    = file_get_contents( 'php://input' );
		$this->event = json_decode( $raw_body );

		// Check if this is a proxied (v3) webhook.
		$integration_type = isset( $_SERVER['HTTP_X_EDD_INTEGRATION_TYPE'] )
			? sanitize_text_field( $_SERVER['HTTP_X_EDD_INTEGRATION_TYPE'] )
			: '';

		if ( 'third_party' === $integration_type ) {
			return $this->validate_connect_request( $raw_body );
		}

		// v2: existing validation path.
		if ( ! PayPal\has_rest_api_connection() ) {
			return new \WP_Error( 'missing_api_credentials', 'API credentials not set.' );
		}

		try {
			Webhook_Validator::validate_from_request( $this->event );

			edd_debug_log( 'PayPal Commerce webhook successfully validated.' );

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'validation_failure', $e->getMessage() );
		}
	}

	/**
	 * Validates an incoming Connect-relayed (v3) webhook using HMAC-SHA256.
	 *
	 * @since 3.6.9
	 *
	 * @param string $raw_body Raw request body.
	 * @return true|\WP_Error
	 */
	private function validate_connect_request( $raw_body ) {
		$timestamp    = isset( $_SERVER['HTTP_X_EDD_TIMESTAMP'] ) ? sanitize_text_field( $_SERVER['HTTP_X_EDD_TIMESTAMP'] ) : '';
		$webhook_id   = isset( $_SERVER['HTTP_X_EDD_WEBHOOK_ID'] ) ? sanitize_text_field( $_SERVER['HTTP_X_EDD_WEBHOOK_ID'] ) : '';
		$received_sig = isset( $_SERVER['HTTP_X_EDD_PROXY_SIGNATURE'] ) ? sanitize_text_field( $_SERVER['HTTP_X_EDD_PROXY_SIGNATURE'] ) : '';

		if ( empty( $timestamp ) || empty( $webhook_id ) || empty( $received_sig ) ) {
			edd_debug_log( 'PayPal v3 webhook validation failed: missing HMAC headers.', true );
			return new \WP_Error( 'missing_proxy_headers', 'Missing proxy authentication headers.' );
		}

		// Validate timestamp tolerance (5 minutes).
		if ( abs( time() - (int) $timestamp ) > 300 ) {
			edd_debug_log( 'PayPal v3 webhook validation failed: stale timestamp.', true );
			return new \WP_Error( 'stale_timestamp', 'Webhook timestamp is too old.' );
		}

		$message = sprintf(
			'%s.%s.POST./wp-json/edd/webhooks/v1/paypal.%s',
			$timestamp,
			$webhook_id,
			hash( 'sha256', $raw_body )
		);

		// Validate against the key for the event's resolved mode, then the other
		// mode as a fallback — so a store with both environments connected uses
		// the right key, and an event whose mode can't be resolved (legacy
		// Connect with no mode header) still validates against either key.
		$modes = 'sandbox' === self::resolve_event_mode()
			? array( 'sandbox', 'live' )
			: array( 'live', 'sandbox' );

		foreach ( $modes as $mode ) {
			// Accept the current key, or the previous one during its grace window.
			$candidate_keys = array(
				PayPal\V3\Credentials::get_hmac_key( $mode ),
				PayPal\V3\Credentials::get_previous_hmac_key( $mode ),
			);

			foreach ( $candidate_keys as $hmac_key ) {
				if ( empty( $hmac_key ) ) {
					continue;
				}

				if ( hash_equals( hash_hmac( 'sha256', $message, $hmac_key ), $received_sig ) ) {
					edd_debug_log( sprintf( 'PayPal v3 proxy webhook successfully validated. Event mode: %s', $mode ) );
					return true;
				}
			}
		}

		edd_debug_log( 'PayPal v3 webhook validation failed: signature mismatch.', true );
		return new \WP_Error( 'invalid_signature', 'Proxy webhook signature verification failed.' );
	}

	/**
	 * Resolves the PayPal environment a Connect webhook event belongs to.
	 *
	 * Prefers the explicit `X-EDD-PayPal-Mode` header sent by the Connect service. When
	 * the header is missing (older Connect versions), falls back to matching
	 * the `X-EDD-Merchant-ID` header against the merchant IDs stored locally
	 * for sandbox vs live. Returns an empty string when neither resolves —
	 * downstream handlers should treat that as "unknown" rather than guess.
	 *
	 * Intentionally not based on `edd_is_test_mode()`: webhooks arrive in
	 * their own request context independent of the admin's UI toggle, and a
	 * live event must validate even while the admin is viewing the sandbox
	 * tab (and vice versa).
	 *
	 * @since 3.6.9
	 *
	 * @return string 'sandbox', 'live', or '' when the mode cannot be resolved.
	 */
	public static function resolve_event_mode() {
		$header_mode = isset( $_SERVER['HTTP_X_EDD_PAYPAL_MODE'] )
			? strtolower( sanitize_text_field( $_SERVER['HTTP_X_EDD_PAYPAL_MODE'] ) )
			: '';
		if ( 'sandbox' === $header_mode || 'live' === $header_mode ) {
			return $header_mode;
		}

		$merchant_id = isset( $_SERVER['HTTP_X_EDD_MERCHANT_ID'] )
			? sanitize_text_field( $_SERVER['HTTP_X_EDD_MERCHANT_ID'] )
			: '';
		if ( empty( $merchant_id ) ) {
			return '';
		}

		foreach ( array( 'live', 'sandbox' ) as $mode ) {
			if ( get_option( "edd_paypal_{$mode}_merchant_id", '' ) === $merchant_id ) {
				return $mode;
			}
		}

		return '';
	}

	/**
	 * Handles the webhook test request.
	 *
	 * @since 3.2.0
	 *
	 * @param \WP_REST_Request $request The Reqeust object.
	 */
	public function handle_test( \WP_REST_Request $request ) {
		edd_debug_log( 'PayPal Commerce webhook test endpoint loaded.' );

		return new \WP_REST_Response( array( 'message' => 'success' ), 200 );
	}
}
