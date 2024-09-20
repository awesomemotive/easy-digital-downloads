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
		if ( edd_is_dev_environment() ) {
			return sprintf(
				/* translators: 1: Webhooks setup link, 2: Closing anchor tag */
				__( 'Webhooks are not available in local/development environments, but you can %1$stest webhook creation%2$s. This will happen automatically on production sites.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( self::get_webhooks_setup_link() ) . '">',
				'</a>'
			);
		}
		try {
			$webhooks = edds_api_request(
				'WebhookEndpoint',
				'all',
				array(
					'limit' => 100,
				)
			);
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
		}

		if ( ! empty( $error ) ) {
			return $error;
		}
		$data = $webhooks['data'];
		$urls = wp_list_pluck( $data, 'url' );
		$key  = array_search( self::get_listener_url(), $urls, true );
		if ( false === $key ) {
			return sprintf(
				/* translators: 1: Webhooks setup link, 2: Closing anchor tag, 3: Manual setup link */
				__( 'Webhooks not found. %1$sAutomatically set up webhooks%2$s or %3$sadd them to your account manually%2$s.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( self::get_webhooks_setup_link() ) . '">',
				'</a>',
				'<a href="' . esc_url( self::get_workbench_url() ) . '">'
			);
		}
		$events = $data[ $key ]->enabled_events;
		$diff   = array_diff( self::get_event_endpoints(), $events );
		if ( ! empty( $diff ) ) {
			return sprintf(
				/* translators: 1: Webhooks setup link, 2: Closing anchor tag, 3: Manual setup link */
				__( 'Some webhooks are missing. %1$sAutomatically set up webhooks%2$s or %3$sadd them to your account manually%2$s.', 'easy-digital-downloads' ),
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
		} catch ( \EDD\Vendors\Stripe\ApiErrorException $e ) {
			$error = $e->getMessage();
		}

		$message = 'stripe_webhooks_created';
		if ( ! empty( $error ) ) {
			$message = 'stripe_webhooks_error';
		}

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
	 * @return string
	 */
	private static function get_listener_url() {
		return add_query_arg(
			array(
				'edd-listener' => 'stripe',
			),
			home_url( 'index.php' )
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
}
