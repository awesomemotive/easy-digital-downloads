<?php
/**
 * Webhook Functions
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal\Webhooks;

use EDD\Gateways\PayPal\API;
use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;

/**
 * Returns the webhook URL.
 *
 * @since 2.11
 * @return string
 */
function get_webhook_url() {
	return rest_url( Webhook_Handler::REST_NAMESPACE . '/' . Webhook_Handler::REST_ROUTE );
}

/**
 * Returns the ID of the webhook.
 *
 * @param string $mode API mode. Either `sandbox` or `live`. If omitted, current store mode is used.
 *
 * @since 2.11
 * @return string|false
 */
function get_webhook_id( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	return get_option( sanitize_key( 'edd_paypal_commerce_webhook_id_' . $mode ) );
}

/**
 * Returns the list of webhook events that EDD requires.
 *
 * @link  https://developer.paypal.com/docs/api-basics/notifications/webhooks/event-names/#sales
 *
 * @todo  Would be nice to use the EDD 3.0 registry for this at some point.
 *
 * @param string $mode Store mode. Either `sandbox` or `live`.
 *
 * @since 2.11
 * @return array
 */
function get_webhook_events( $mode = '' ) {
	$events = array(
		'PAYMENT.CAPTURE.COMPLETED' => '\\EDD\\Gateways\\PayPal\\Webhooks\\Events\\Payment_Capture_Completed',
		'PAYMENT.CAPTURE.DENIED'    => '\\EDD\\Gateways\\PayPal\\Webhooks\\Events\\Payment_Capture_Denied',
		'PAYMENT.CAPTURE.REFUNDED'  => '\\EDD\\Gateways\\PayPal\\Webhooks\\Events\\Payment_Capture_Refunded',
		'PAYMENT.CAPTURE.REVERSED'  => '\\EDD\\Gateways\\PayPal\\Webhooks\\Events\\Payment_Capture_Refunded',
		'CUSTOMER.DISPUTE.CREATED'  => '\\EDD\\Gateways\\PayPal\\Webhooks\\Events\\Customer_Dispute_Created',
	);

	/**
	 * Filters the webhook events.
	 *
	 * @param array  $events Array of events that PayPal will send webhooks for.
	 * @param string $mode   Mode the webhook is being created in. Either `sandbox` or `live`.
	 *
	 * @since 2.11
	 */
	return (array) apply_filters( 'edd_paypal_webhook_events', $events, $mode );
}

/**
 * Creates a webhook.
 *
 * @param string $mode Store mode. Either `sandbox` or `live`. If omitted, current store mode is used.
 *
 * @return true
 * @throws API_Exception
 * @throws Authentication_Exception
 */
function create_webhook( $mode = '' ) {
	if ( ! is_ssl() ) {
		throw new API_Exception( __( 'An SSL certificate is required to create a PayPal webhook.', 'easy-digital-downloads' ) );
	}

	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	$webhook_url = get_webhook_url();

	$api = new API( $mode );

	// First, list webhooks in case it's already added.
	try {
		$response = $api->make_request( 'v1/notifications/webhooks', array(), array(), 'GET' );
		if ( ! empty( $response->webhooks ) && is_array( $response->webhooks ) ) {
			foreach ( $response->webhooks as $webhook ) {
				if ( ! empty( $webook->id ) && ! empty( $webhook->url ) && $webhook_url === $webhook->url ) {
					update_option( sanitize_key( 'edd_paypal_commerce_webhook_id_' . $mode ), sanitize_text_field( $webhook->id ) );

					return true;
				}
			}
		}
	} catch ( \Exception $e ) {
		// Continue to webhook creation.
	}

	$event_types = array();

	foreach ( array_keys( get_webhook_events( $mode ) ) as $event ) {
		$event_types[] = array( 'name' => $event );
	}

	$response = $api->make_request(
		'v1/notifications/webhooks',
		array(
			'url'         => $webhook_url,
			'event_types' => $event_types,
		)
	);

	if ( 201 !== $api->last_response_code ) {
		throw new API_Exception(
			sprintf(
				/* Translators: %d - HTTP response code; %s - Full response from the API. */
				__( 'Invalid response code %1$d while creating webhook. Response: %2$s', 'easy-digital-downloads' ),
				$api->last_response_code,
				json_encode( $response )
			)
		);
	}

	if ( empty( $response->id ) ) {
		throw new API_Exception( __( 'Unexpected response from PayPal.', 'easy-digital-downloads' ) );
	}

	update_option( sanitize_key( 'edd_paypal_commerce_webhook_id_' . $mode ), sanitize_text_field( $response->id ) );

	return true;
}

/**
 * Syncs the webhook with expected data. This replaces the webhook URL and event types with
 * what EDD expects them to be. This can be used when the events need to be updated in
 * the event that some are missing.
 *
 * @param string $mode Either `sandbox` or `live` mode. If omitted, current store mode is used.
 *
 * @since 2.11
 * @return true
 * @throws API_Exception
 * @throws Authentication_Exception
 */
function sync_webhook( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	// If the webhook sync failed during a cron event and we are syncing again, delete the option to clear the admin notice.
	delete_option( 'edd_paypal_webhook_sync_failed' );

	$webhook_id = get_webhook_id( $mode );
	if ( empty( $webhook_id ) ) {
		throw new \Exception( esc_html__( 'Webhook not configured.', 'easy-digital-downloads' ) );
	}

	$event_types = array();
	foreach ( array_keys( get_webhook_events( $mode ) ) as $event ) {
		$event_types[] = array( 'name' => $event );
	}

	$new_data = array(
		array(
			'op'    => 'replace',
			'path'  => '/url',
			'value' => get_webhook_url(),
		),
		array(
			'op'    => 'replace',
			'path'  => '/event_types',
			'value' => $event_types,
		),
	);

	$api      = new API( $mode );
	$response = $api->make_request( 'v1/notifications/webhooks/' . urlencode( $webhook_id ), $new_data, array(), 'PATCH' );
	if ( 400 === $api->last_response_code && isset( $response->name ) && 'WEBHOOK_PATCH_REQUEST_NO_CHANGE' === $response->name ) {
		return true;
	}

	if ( 200 !== $api->last_response_code ) {
		throw new API_Exception(
			sprintf(
				/* Translators: %d - HTTP response code; %s - Full response from the API. */
				__( 'Invalid response code %1$d while syncing webhook. Response: %2$s', 'easy-digital-downloads' ),
				$api->last_response_code,
				json_encode( $response )
			)
		);
	}

	return true;
}

/**
 * Syncs the webhook on a cron event.
 *
 * @since 3.2.1
 * @return void
 */
function sync_webhook_on_cron() {
	try {
		sync_webhook();
	} catch ( \Exception $e ) {
		add_option( 'edd_paypal_webhook_sync_failed', time(), '', false );
	}
}
add_action( 'edd_paypal_commerce_sync_webhooks', __NAMESPACE__ . '\\sync_webhook_on_cron' );

/**
 * Retrieves information about the webhook EDD created.
 *
 * @param string $mode Mode to get the webhook in. If omitted, current store mode is used.
 *
 * @return object|false Webhook object on success, false if there is no webhook set up.
 * @throws API_Exception
 * @throws Authentication_Exception
 */
function get_webhook_details( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	$webhook_id = get_option( sanitize_key( 'edd_paypal_commerce_webhook_id_' . $mode ) );

	// Bail if webhook was never set.
	if ( ! $webhook_id ) {
		return false;
	}

	$api      = new API( $mode );
	$response = $api->make_request( 'v1/notifications/webhooks/' . urlencode( $webhook_id ), array(), array(), 'GET' );
	if ( 200 !== $api->last_response_code ) {
		if ( 404 === $api->last_response_code ) {
			throw new API_Exception(
				__( 'Your store is currently not receiving webhook notifications, create the webhooks to reconnect.', 'easy-digital-downloads' )
			);
		} else {
			throw new API_Exception(
				sprintf(
					/* Translators: %d - HTTP response code. */
					__( 'Invalid response code %d while retrieving webhook details.', 'easy-digital-downloads' ),
					$api->last_response_code
				)
			);
		}
	}

	if ( empty( $response->id ) ) {
		throw new API_Exception( __( 'Unexpected response from PayPal when retrieving webhook details.', 'easy-digital-downloads' ) );
	}

	return $response;
}

/**
 * Deletes the webhook.
 *
 * @since 2.11
 *
 * @param string $mode
 *
 * @throws API_Exception
 * @throws Authentication_Exception
 */
function delete_webhook( $mode = '' ) {
	if ( empty( $mode ) ) {
		$mode = edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE;
	}

	$webhook_name = sanitize_key( 'edd_paypal_commerce_webhook_id_' . $mode );
	$webhook_id   = get_option( $webhook_name );

	// Bail if webhook was never set.
	if ( ! $webhook_id ) {
		return;
	}

	$api = new API( $mode );

	$api->make_request( 'v1/notifications/webhooks/' . urlencode( $webhook_id ), array(), array(), 'DELETE' );

	if ( 204 !== $api->last_response_code ) {
		throw new API_Exception(
			sprintf(
				/* Translators: %d - HTTP response code. */
				__( 'Invalid response code %d while deleting webhook.', 'easy-digital-downloads' ),
				$api->last_response_code
			)
		);
	}
}

add_action(
	'rest_api_init',
	function () {
		$handler = new Webhook_Handler();
		$handler->register_routes();
	}
);
