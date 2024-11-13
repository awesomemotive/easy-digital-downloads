<?php
/**
 * Stripe Connect class.
 *
 * @package EDD\Gateways\Stripe\Admin
 */

namespace EDD\Gateways\Stripe\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Connect class.
 *
 * @since 3.3.4
 */
class Connect {

	/**
	 * Check webhooks.
	 *
	 * @since 3.3.4
	 */
	public static function check_webhooks() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}

		$endpoints = self::get_endpoints();
		if ( is_string( $endpoints ) ) {
			return $endpoints;
		}

		$key = self::check_endpoints( $endpoints );

		if ( edd_is_dev_environment() ) {
			if ( false !== $key ) {
				return __( 'Webhooks are configured correctly, but are not typically functional in local/development environments.', 'easy-digital-downloads' );
			} else {
				return sprintf(
					/* translators: 1: Webhooks setup link, 2: Closing anchor tag */
					__( 'Webhooks are not typically functional in local/development environments, but you can %1$smanually create the webhooks%2$s for testing. This will happen automatically on production sites.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( self::get_webhooks_setup_link() ) . '">',
					'</a>'
				);
			}
		}

		// Webhooks not found.
		if ( false === $key ) {
			if ( ! self::is_listener_ssl() ) {
				return sprintf(
					/* translators: 1: Webhooks setup link, 2: Closing anchor tag */
					__( 'Webhooks cannot be automatically set up because your site is not using HTTPS. %1$sManually add webhooks%2$s.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( self::get_workbench_url() ) . '">',
					'</a>'
				);
			}

			return sprintf(
				/* translators: 1: Webhooks setup link, 2: Closing anchor tag, 3: Manual setup link */
				__( 'Webhooks not found. %1$sAutomatically set up webhooks%2$s or %3$sadd them to your account manually%2$s.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( self::get_webhooks_setup_link() ) . '">',
				'</a>',
				'<a href="' . esc_url( self::get_workbench_url() ) . '">'
			);
		}

		return __( 'Webhooks are configured correctly.', 'easy-digital-downloads' );
	}

	/**
	 * Set up webhooks.
	 *
	 * @since 3.3.4
	 * @param array $data Data.
	 */
	public static function create_webhooks( $data ) {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		if ( empty( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd-create-stripe-webhooks' ) ) {
			return;
		}

		$message = 'stripe_webhooks_error';
		// Only create webhooks in production mode if the listener is SSL.
		if ( ! edd_is_test_mode() && ! self::is_listener_ssl() ) {
			self::redirect( 'stripe_webhooks_error_ssl' );
		}

		try {
			$webhooks = edds_api_request(
				'WebhookEndpoint',
				'create',
				array(
					'url'            => self::get_listener_url(),
					'enabled_events' => self::get_event_endpoints(),
					'api_version'    => EDD_STRIPE_API_VERSION,
				)
			);

			$message = 'stripe_webhooks_created';
		} catch ( \EDD\Vendor\Stripe\ApiErrorException $e ) {
			// Do nothing.
		}

		self::redirect( $message );
	}

	/**
	 * Get the webhooks setup link to manually set up webhooks.
	 *
	 * @since 3.3.4
	 * @return string
	 */
	private static function get_workbench_url() {
		return add_query_arg(
			array(
				'events' => urlencode( implode( ',', self::get_event_endpoints() ) ),
			),
			'https://dashboard.stripe.com/webhooks/create'
		);
	}

	/**
	 * Get the list of event endpoints.
	 *
	 * @since 3.3.4
	 * @return array
	 */
	private static function get_event_endpoints() {
		/**
		 * Filter the list of Stripe webhook endpoints.
		 *
		 * @since 3.3.4
		 * @param array $endpoints The list of endpoints.
		 */
		return apply_filters(
			'edd_stripe_webhook_endpoints',
			array(
				'charge.refunded',
				'charge.succeeded',
				'charge.dispute.created',
				'customer.subscription.created',
				'customer.subscription.deleted',
				'customer.subscription.updated',
				'invoice.payment_failed',
				'invoice.payment_succeeded',
				'mandate.updated',
				'payment_intent.amount_capturable_updated',
				'payment_intent.canceled',
				'payment_intent.created',
				'payment_intent.partially_funded',
				'payment_intent.payment_failed',
				'payment_intent.processing',
				'payment_intent.requires_action',
				'payment_intent.succeeded',
				'payment_method.attached',
				'payment_method.automatically_updated',
				'payment_method.detached',
				'payment_method.updated',
				'radar.early_fraud_warning.created',
				'review.closed',
				'review.opened',
				'setup_intent.canceled',
				'setup_intent.created',
				'setup_intent.requires_action',
				'setup_intent.setup_failed',
				'setup_intent.succeeded',
			)
		);
	}

	/**
	 * Gets the listener URL.
	 *
	 * @since 3.3.4
	 * @param bool $include_index Whether to include the index.php file.
	 * @param bool $include_trailing_slash Whether to include the trailing slash.
	 * @return string
	 */
	private static function get_listener_url( $include_index = true, $include_trailing_slash = true ) {
		$home_url = $include_index
			? home_url( 'index.php' )
			: home_url();

		if ( ! $include_index && $include_trailing_slash ) {
			$home_url = trailingslashit( $home_url );
		}

		return add_query_arg(
			array(
				'edd-listener' => 'stripe',
			),
			$home_url
		);
	}

	/**
	 * Get the webhooks setup link.
	 *
	 * @since 3.3.4
	 * @return string
	 */
	private static function get_webhooks_setup_link() {
		return wp_nonce_url(
			edd_get_admin_url(
				array(
					'page'       => 'edd-settings',
					'tab'        => 'gateways',
					'section'    => 'edd-stripe',
					'edd-action' => 'create_stripe_webhooks',
				)
			),
			'edd-create-stripe-webhooks'
		);
	}

	/**
	 * Check if the listener is SSL.
	 *
	 * @since 3.3.5
	 * @return bool
	 */
	private static function is_listener_ssl() {
		return 'https' === wp_parse_url( self::get_listener_url(), PHP_URL_SCHEME );
	}

	/**
	 * Redirect to the Stripe settings page.
	 *
	 * @since 3.3.5
	 * @param string $message Message.
	 */
	private static function redirect( string $message ) {
		edd_redirect(
			edd_get_admin_url(
				array(
					'page'        => 'edd-settings',
					'tab'         => 'gateways',
					'section'     => 'edd-stripe',
					'edd-message' => $message,
				)
			)
		);
	}

	/**
	 * Get the list of webhook endpoints.
	 *
	 * @since 3.3.5
	 * @return array|string
	 */
	private static function get_endpoints() {
		try {
			$webhooks = edds_api_request(
				'WebhookEndpoint',
				'all',
				array(
					'limit' => 100,
				)
			);
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}

		return $webhooks['data'];
	}

	/**
	 * Check if the webhook endpoint exists.
	 *
	 * @since 3.3.5
	 * @param array $endpoints Endpoints.
	 * @return int|bool
	 */
	private static function check_endpoints( $endpoints ) {
		$urls = wp_list_pluck( $endpoints, 'url' );

		// Ensure we're only working with 'enabled' webhook endpoints.
		foreach ( $urls as $key => $url ) {
			if ( 'enabled' !== $endpoints[ $key ]->status ) {
				unset( $urls[ $key ] );
			}
		}

		$all_listeners = array(
			self::get_listener_url(), // /index.php?edd-listener=stripe
			self::get_listener_url( false, false ), // ?edd-listener=stripe
			self::get_listener_url( false, true ), // /?edd-listener=stripe
		);

		foreach ( $all_listeners as $listener ) {
			$key = array_search( $listener, $urls, true );

			if ( false !== $key ) {
				$webhook_id = $endpoints[ $key ]->id;
				break;
			}
		}

		if ( false === $key ) {
			return false;
		}

		$update_data = array();

		// Ensure that even if we did find a webhook that the URL is correct.
		if ( self::get_listener_url() !== $urls[ $key ] ) {
			$update_data['url'] = self::get_listener_url();
		}

		// Check to see if we have any of our required events missing.
		$missing_events_count = 0;
		foreach ( self::get_event_endpoints() as $event ) {
			if ( ! in_array( $event, $endpoints[ $key ]->enabled_events, true ) ) {
				++$missing_events_count;
			}
		}

		if ( $missing_events_count > 0 ) {
			$update_data['enabled_events'] = self::get_event_endpoints();
		}

		// If we have data to update, do so.
		if ( ! empty( $update_data ) ) {
			self::update_webhook( $webhook_id, $update_data );
		}

		// Now lets ensure we never have any duplicates and disable the ones we find.
		self::disable_duplicate_webhooks( $webhook_id );

		return $key;
	}

	/**
	 * Update a webhook.
	 *
	 * @since 3.3.5
	 * @param string $webhook_id Webhook ID.
	 * @param array  $data Data.
	 * @return bool
	 */
	private static function update_webhook( $webhook_id, $data ) {
		try {
			edds_api_request(
				'WebhookEndpoint',
				'update',
				$webhook_id,
				$data
			);
		} catch ( \EDD\Vendor\Stripe\ApiErrorException $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Disable duplicate webhooks.
	 *
	 * It is possible that we end up with multiple webhooks due to people adding them manually, so we'll
	 * ensure that we only have one webhook that matches our listener URL. Searching for both the
	 * listener URL with and without index.php, keeping the one that matches our $valid_webhook_id.
	 *
	 * @since 3.3.5
	 * @param string $valid_webhook_id Webhook ID that we want to keep.
	 */
	private static function disable_duplicate_webhooks( $valid_webhook_id ) {
		$endpoints = self::get_endpoints();

		$listener_urls = array(
			self::get_listener_url(), // /index.php?edd-listener=stripe
			self::get_listener_url( false, false ), // ?edd-listener=stripe
			self::get_listener_url( false, true ), // /?edd-listener=stripe
		);

		foreach ( $endpoints as $endpoint ) {
			// Skip the webhook that we want to keep.
			if ( $endpoint->id === $valid_webhook_id ) {
				continue;
			}

			if ( in_array( $endpoint->url, $listener_urls, true ) ) {
				self::update_webhook(
					$endpoint->id,
					array( 'disabled' => true )
				);
			}
		}
	}
}
