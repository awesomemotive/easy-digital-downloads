<?php
/**
 * Manages the webhooks for the Square gateway.
 *
 * @package     EDD\Gateways\Square\Webhooks
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\Helpers\Api;
use EDD\Vendor\Square\Models\WebhookSubscription;
use EDD\Vendor\Square\Models\UpdateWebhookSubscriptionRequest;
use EDD\Vendor\Square\Models\CreateWebhookSubscriptionRequest;

/**
 * Manages the webhooks for the Square gateway.
 *
 * @since 3.4.0
 */
class Manager {

	/**
	 * The Square events that we support and listen for.
	 *
	 * @since 3.4.0
	 * @var array
	 */
	protected static $events = array(
		'customer.created',
		'customer.deleted',
		'customer.updated',
		'dispute.created',
		'dispute.state.updated',
		'invoice.created',
		'invoice.published',
		'invoice.updated',
		'invoice.payment_made',
		'invoice.scheduled_charge_failed',
		'invoice.canceled',
		'invoice.refunded',
		'invoice.deleted',
		'location.created',
		'location.updated',
		'oauth.authorization.revoked',
		'order.created',
		'order.fulfillment.updated',
		'order.updated',
		'payment.created',
		'payment.updated',
		'refund.created',
		'refund.updated',
		'subscription.created',
		'subscription.updated',
	);

	/**
	 * Get the webhook subscription ID that is stored locally.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public static function get_subscription_id(): string {
		return Setting::get( 'webhook_subscription_id' );
	}

	/**
	 * Get the webhook signature key that is stored locally.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public static function get_signature_key(): string {
		return Setting::get( 'webhook_signature_key' );
	}

	/**
	 * Get webhook notification URL for this site.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public static function get_notification_url(): string {
		return rest_url( 'edd/webhooks/v1/square' );
	}

	/**
	 * Retrieve the webhook subscription.
	 *
	 * @since 3.4.0
	 * @param string $token The token.
	 * @param string $subscription_id The subscription ID.
	 *
	 * @return array
	 */
	public static function get( string $token, string $subscription_id ): array {
		if ( empty( $token ) || ! is_string( $token ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to retrieve webhooks.', 'easy-digital-downloads' ),
			);
		}

		if ( empty( $subscription_id ) || ! is_string( $subscription_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to retrieve webhooks.', 'easy-digital-downloads' ),
			);
		}

		$webhook_subscription = Api::client( true, $token )->getWebhookSubscriptionsApi()->retrieveWebhookSubscription( $subscription_id );

		if ( ! $webhook_subscription->isSuccess() ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to retrieve webhooks.', 'easy-digital-downloads' ),
			);
		}

		return array(
			'success'      => true,
			'subscription' => $webhook_subscription->getResult()->getSubscription(),
		);
	}

	/**
	 * Get the webhook subscriptions.
	 *
	 * @since 3.4.0
	 * @param string $token The token.
	 *
	 * @return array
	 */
	public static function list( string $token ): array {
		if ( empty( $token ) || ! is_string( $token ) ) {
			return array();
		}

		$webhook_subscriptions = Api::client( true, $token )->getWebhookSubscriptionsApi()->listWebhookSubscriptions();

		if ( ! $webhook_subscriptions->isSuccess() ) {
			return array();
		}

		return ! empty( $webhook_subscriptions->getResult()->getSubscriptions() ) ?
			$webhook_subscriptions->getResult()->getSubscriptions() :
			array();
	}

	/**
	 * Update the webhook subscription.
	 *
	 * @since 3.4.0
	 * @param string              $webhook_subscription_id The webhook subscription ID.
	 * @param WebhookSubscription $subscription The subscription.
	 * @param string              $token The token.
	 *
	 * @return bool
	 */
	public static function update( string $webhook_subscription_id, WebhookSubscription $subscription, string $token ): bool {
		if ( empty( $webhook_subscription_id ) || ! is_int( $webhook_subscription_id ) ) {
			return false;
		}

		if ( empty( $subscription ) || ! is_object( $subscription ) ) {
			return false;
		}

		if ( empty( $token ) || ! is_string( $token ) ) {
			return false;
		}

		$update_request = new UpdateWebhookSubscriptionRequest();
		$update_request->setSubscription( $subscription );

		$response = Api::client( true, $token )->getWebhookSubscriptionsApi()->updateWebhookSubscription( $webhook_subscription_id, $update_request );

		return $response->isSuccess();
	}

	/**
	 * Create a new webhook subscription.
	 *
	 * @since 3.4.0
	 * @param string $token The token.
	 *
	 * @return array
	 */
	public static function create( string $token ): array {
		if ( empty( $token ) || ! is_string( $token ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create webhooks.', 'easy-digital-downloads' ),
			);
		}

		$subscription = new WebhookSubscription();
		$subscription->setEventTypes( self::get_default_events() );
		$subscription->setEnabled( true );
		$subscription->setNotificationUrl( self::get_notification_url() );
		$subscription->setName( 'EDD Square Webhook Subscription' );

		$create_request = new CreateWebhookSubscriptionRequest( $subscription );
		$create_request->setIdempotencyKey( Api::get_idempotency_key( 'webhook_create_' ) );

		$response = Api::client( true, $token )->getWebhookSubscriptionsApi()->createWebhookSubscription( $create_request );

		if ( ! $response->isSuccess() ) {
			return array(
				'success' => false,
				'message' => self::parse_error_code( $response->getErrors()[0]->getCode() ),
			);
		}

		return array(
			'success' => true,
			'subscription' => $response->getResult()->getSubscription(),
		);
	}

	/**
	 * Check the subscription events.
	 *
	 * @since 3.4.0
	 * @param array $subscription_events The subscription events.
	 *
	 * @return array
	 */
	public static function check_events( $subscription_events = array() ): array {
		if ( empty( $subscription_events ) ) {
			return array();
		}

		$subscription_events = array_map( 'strtolower', $subscription_events );
		$missing_events      = array();

		foreach ( $subscription_events as $event ) {
			if ( ! in_array( $event, self::$events, true ) ) {
				$missing_events[] = $event;
			}
		}

		return $missing_events;
	}

	/**
	 * Check the notification URL of a webhook subscription against the notification URL for this site.
	 *
	 * @since 3.4.0
	 * @param string $notification_url The notification URL.
	 *
	 * @return bool
	 */
	public static function check_notification_url( $notification_url ): bool {
		if ( empty( $notification_url ) || ! is_string( $notification_url ) ) {
			return false;
		}

		return strtolower( $notification_url ) === strtolower( self::get_notification_url() );
	}

	/**
	 * Get the default events for the EDD Square integration.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public static function get_default_events(): array {
		return self::$events;
	}

	/**
	 * Parse the error code from the response.
	 *
	 * @since 3.4.0
	 * @param string $error_code The error code.
	 *
	 * @return string
	 */
	private static function parse_error_code( $error_code ): string {
		$error_messages = array(
			'UNREACHABLE_URL' => __( 'Unable to register webhooks.Webhooks require the URL to be reachable from the Square servers to be registered.', 'easy-digital-downloads' ),
		);

		if ( isset( $error_messages[ $error_code ] ) ) {
			return $error_messages[ $error_code ];
		}

		return __( 'Failed to update webhooks.', 'easy-digital-downloads' );
	}
}
