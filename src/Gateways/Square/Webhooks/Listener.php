<?php
/**
 * Square webhooks listener.
 *
 * @package     EDD\Gateways\Square\Webhooks
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\Helpers\Mode;
use EDD\Vendor\Square\Utils\WebhooksHelper;

/**
 * Square webhooks listener.
 *
 * @since 3.4.0
 */
class Listener implements SubscriberInterface {

	/**
	 * Endpoint namespace.
	 *
	 * @since 3.4.0
	 */
	const REST_NAMESPACE = 'edd/webhooks/v1';

	/**
	 * Endpoint route.
	 *
	 * @since 3.4.0
	 */
	const REST_ROUTE = 'square';

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_routes',
		);
	}

	/**
	 * Registers REST API routes.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function register_routes() {
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
	 * Handles the current webhook request.
	 *
	 * @since 3.4.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response
	 * @throws \Exception When invalid JSON payload is received.
	 */
	public function handle_request( \WP_REST_Request $request ) {
		$event_type = $request->get_param( 'type' );

		edd_debug_log(
			sprintf(
				'Square webhook endpoint loaded. Event: %s',
				$event_type
			)
		);

		try {
			$event_data = $request->get_json_params();
			edd_debug_log( var_export( $request->get_json_params(), true ) );

			if ( empty( $event_data ) ) {
				throw new \Exception( 'Invalid JSON payload', 400 );
			}

			// Process the webhook event.
			if ( isset( $event_data['type'] ) ) {
				$this->handle_event( $event_data );
			}

			return new \WP_REST_Response( 'Success', 200 );

		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'Square webhook - Exiting due to exception. Message: %s', $e->getMessage() ), true );

			$response_code = $e->getCode() > 0 ? $e->getCode() : 403;

			return new \WP_REST_Response( $e->getMessage(), $response_code );
		}
	}

	/**
	 * Validates the webhook request.
	 *
	 * @since 3.4.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return bool|\WP_Error
	 */
	public function validate_request( \WP_REST_Request $request ) {
		$body = $request->get_body();

		if ( empty( $body ) ) {
			edd_debug_log( 'Square webhook - Request body is empty.' );
			return new \WP_Error( 'empty_body', 'Request body is empty.' );
		}

		// Pull in the headers, so we can validate the webhook.
		$webhook_headers = getallheaders();
		$headers         = array();
		foreach ( $webhook_headers as $key => $value ) {
			$headers[ strtolower( $key ) ] = $value;
		}

		// Validate the webhook environment.
		$webhook_environment = strtolower( $headers['square-environment'] ?? '' );
		if ( empty( $webhook_environment ) ) {
			edd_debug_log( 'Square webhook - Webhook environment is empty.' );
			return new \WP_Error( 'empty_environment', 'Webhook environment is empty.' );
		}

		if ( Mode::get() !== $webhook_environment ) {
			edd_debug_log( 'Square webhook - Webhook environment does not match.' );
			return new \WP_Error( 'invalid_environment', 'Webhook environment does not match.' );
		}

		// Validate the webhook subscription ID.
		$webhook_subscription_id = $headers['square-subscription-id'] ?? '';
		if ( empty( $webhook_subscription_id ) ) {
			edd_debug_log( 'Square webhook - Webhook subscription ID is empty.' );
			return new \WP_Error( 'empty_subscription_id', 'Webhook subscription ID is empty.' );
		}

		$saved_webhook_subscription_id = Setting::get( 'webhook_subscription_id' );
		if ( $webhook_subscription_id !== $saved_webhook_subscription_id ) {
			// If we don't have a webhook subscription ID stored locally, add a local notification to the EDD admin.
			if ( empty( $saved_webhook_subscription_id ) ) {
				EDD()->notifications->maybe_add_local_notification(
					array(
						'remote_id'  => 'square_wh_01',
						'buttons'    => array(
							array(
								'type' => 'primary',
								'url'  => edd_get_admin_url(
									array(
										'page'    => 'edd-settings',
										'tab'     => 'gateways',
										'section' => 'square',
									)
								),
								'text' => __( 'Square Settings', 'easy-digital-downloads' ),
							),
						),
						'conditions' => '',
						'type'       => 'warning',
						'title'      => __( '[IMPORTANT] Square Webhooks Error', 'easy-digital-downloads' ),
						'content'    => __( 'We detected that Square attempted to send a webhook to Easy Digital Downloads, but you have not yet configured webhooks.', 'easy-digital-downloads' ),
					)
				);
			}

			edd_debug_log( 'Square webhook - Webhook subscription ID does not match.' );
			return new \WP_Error( 'invalid_subscription_id', 'Webhook subscription ID does not match.' );
		}

		// Validate the webhook signature.
		if ( ! $this->verify_signature( $body, $headers ) ) {
			edd_debug_log( 'Square webhook - Invalid webhook signature.' );
			return new \WP_Error( 'invalid_signature', 'Invalid webhook signature.' );
		}

		edd_debug_log( 'Square webhook successfully validated.' );

		return true;
	}

	/**
	 * Verifies the webhook signature.
	 *
	 * To validate the webhook notification, generate the HMAC-SHA-256 signature in your own code and compare it to
	 * the X-Square-Hmacsha256-Signature of the event notification you received.
	 *
	 * @link https://developer.squareup.com/docs/webhooks/build-with-webhooks#verify-the-webhook-notification
	 *
	 * @since 3.4.0
	 * @param string $body The request body.
	 * @param array  $headers The request headers.
	 * @return bool
	 */
	private function verify_signature( $body, $headers ) {
		$signature_key = Setting::get( 'webhook_signature_key' );

		// If we don't have the signature key stored locally, we should try and fix that.
		if ( empty( $signature_key ) ) {
			// If we don't have a signature key stored locally, add a local notification to the EDD admin.
			EDD()->notifications->maybe_add_local_notification(
				array(
					'remote_id'  => 'square_wh_02',
					'buttons'    => array(
						array(
							'type' => 'primary',
							'url'  => edd_get_admin_url(
								array(
									'page'    => 'edd-settings',
									'tab'     => 'gateways',
									'section' => 'square',
								)
							),
							'text' => __( 'Square Settings', 'easy-digital-downloads' ),
						),
					),
					'conditions' => '',
					'type'       => 'warning',
					'title'      => __( '[IMPORTANT] Square Webhooks Error', 'easy-digital-downloads' ),
					'content'    => __( 'There was an error while processing webhooks from Square. Please refresh your webhooks in Square and try again.', 'easy-digital-downloads' ),
				)
			);

			return false;
		}

		// The case of the header has been normalized, so we can use the lowercase version.
		$passed_signature = $headers['x-square-hmacsha256-signature'] ?? $headers['x-square-hmacsha256-signature'] ?? '';

		if ( empty( $passed_signature ) ) {
			return false;
		}

		// Generate the expected signature.
		$notification_url = rest_url( self::REST_NAMESPACE . '/' . self::REST_ROUTE );
		$is_valid         = WebhooksHelper::isValidWebhookEventSignature( $body, $passed_signature, $signature_key, $notification_url );

		edd_debug_log( 'Square webhook - Is valid webhook event signature: ' . $is_valid );
		return $is_valid;
	}

	/**
	 * Handles a webhook event.
	 *
	 * @since 3.4.0
	 * @param array $event The webhook event data.
	 * @return void
	 */
	private function handle_event( $event ) {
		edd_debug_log( sprintf( 'Square webhook - Processing event: %s', $event['type'] ) );

		// Convert the event to a CamelCase string, and then see if the class exists.
		// Some of their events have a _ character, we'll convert those to a full stop.
		$event_type  = str_replace( '_', '.', $event['type'] );
		$event_type  = explode( '.', $event_type );
		$event_type  = array_map( 'ucfirst', $event_type );
		$event_class = implode( '', $event_type );

		try {
			/**
			 * We typically try and use the ::class constant, but we need to be able to support
			 * events that are not registered classes, so we will manually build the class name,
			 * so we don't trigger the catch block, which allows a hook to fire for the event.
			 */
			$event_class = __NAMESPACE__ . '\\Events\\' . $event_class;

			// Check our event classes to see if it exists.
			if ( class_exists( $event_class ) && is_subclass_of( $event_class, 'EDD\Gateways\Square\Webhooks\Events\Event' ) ) {
				$webhook_event = new $event_class( $event );
				$webhook_event->process();
			}

			/**
			 * Fires after the Square event has been processed.
			 *
			 * This allows custom events to be processed, or additional processing to happen.
			 *
			 * @param string $event_type The event type.
			 * @parma array $event The Square event.
			 */
			do_action( 'edd_square_event_' . $event['type'], $event );

			edd_debug_log( 'Square webhook - Event processed successfully.' );

			wp_send_json_success(
				sprintf( 'Square webhook - Event processed successfully: %s', $event['type'] )
			);
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'Square webhook - Exiting due to exception. Message: %s', $e->getMessage() ), true );

			wp_send_json_error(
				sprintf( 'Square webhook - Event processing failed: %s', $e->getMessage() )
			);
		}
	}
}
