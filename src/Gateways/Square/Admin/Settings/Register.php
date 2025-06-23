<?php
/**
 * Square settings handler.
 *
 * @package     EDD\Gateways\Square\Admin\Settings
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Admin\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;
use EDD\Gateways\Square\Gateway;
use EDD\Gateways\Square\Webhooks\Manager;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Vendor\Square\Models\WebhookSubscription;

/**
 * Square settings handler.
 *
 * @since 3.4.0
 */
class Register implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_settings_sections_gateways'         => 'register_settings_section',
			'edd_settings_gateways'                  => 'register_settings',
			'admin_enqueue_scripts'                  => 'enqueue_admin_assets',
			'wp_ajax_edd_square_initiate_connection' => 'handle_ajax_initiate_connection',
			'admin_notices'                          => 'admin_notices',
			'edd_gateway_settings_url_square'        => 'get_settings_url',
			'edd_promo_notices'                      => 'register_webhook_modal',
			'wp_ajax_edd_square_register_webhooks'   => 'handle_ajax_register_webhooks',
			'edd_flyout_docs_link'                   => 'update_docs_link',
		);
	}

	/**
	 * Register the Square settings section.
	 *
	 * @since 3.4.0
	 * @param array $sections The settings sections.
	 * @return array
	 */
	public function register_settings_section( $sections ) {
		if ( ! Gateway::is_store_country_supported() ) {
			return $sections;
		}

		$sections['square'] = __( 'Square', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * Register the Square settings.
	 *
	 * @since 3.4.0
	 * @param array $settings The settings array.
	 * @return array
	 */
	public function register_settings( $settings ) {
		$square_settings    = new Render();
		$settings['square'] = $square_settings->get();

		return $settings;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 3.4.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		Render::enqueue_admin_assets( $hook );
	}

	/**
	 * Handle AJAX initiate connection request.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function handle_ajax_initiate_connection() {
		Render::ajax_initiate_connection();
	}

	/**
	 * Display admin notices.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function admin_notices() {
		Render::admin_notices();
	}

	/**
	 * Get the settings URL.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public function get_settings_url() {
		return edd_get_admin_url(
			array(
				'page'    => 'edd-settings',
				'tab'     => 'gateways',
				'section' => 'square',
			)
		);
	}

	/**
	 * Register the webhook modal notice.
	 *
	 * @since 3.4.0
	 * @param array $notices The notices to be registered.
	 * @return array
	 */
	public function register_webhook_modal( $notices ) {
		$notices[] = '\\EDD\\Gateways\\Square\\Admin\\Settings\\Webhooks\\Modal';
		return $notices;
	}

	/**
	 * Handle AJAX register webhooks request.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function handle_ajax_register_webhooks() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), 403 );
		}

		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'edd_square_register_webhooks' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'easy-digital-downloads' ), 403 );
		}

		$token = filter_input( INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $token ) {
			wp_send_json_error( __( 'Missing token.', 'easy-digital-downloads' ), 400 );
		}

		if ( Manager::get_subscription_id() ) {
			$webhook_subscription = Manager::get( $token, Manager::get_subscription_id() );

			if ( $webhook_subscription['success'] && $webhook_subscription['subscription'] instanceof WebhookSubscription ) {
				$subscription_object = $webhook_subscription['subscription'];
				$existing_events     = $subscription_object->getEventTypes();
				$missing_events      = Manager::check_events( $existing_events );
				$is_enabled          = $subscription_object->getEnabled();

				$update_subscription = ! empty( $missing_events ) || ! $is_enabled ?
					new WebhookSubscription() :
					false;

				if ( $update_subscription ) {
					$update_subscription->setEventTypes( array_merge( $existing_events, $missing_events ) );
					$update_subscription->setEnabled( true );

					$updated = Manager::update( $subscription_object->getId(), $update_subscription, $token );

					if ( ! $updated['success'] ) {
						wp_send_json_error( $updated['message'], 400 );
					}
				}

				// We'll just always make sure that the webhook signature key is set.
				Setting::set( 'webhook_signature_key', $subscription_object->getSignatureKey() );

				wp_send_json_success( __( 'Webhooks successfully updated.', 'easy-digital-downloads' ) );
			}

			// The stored webhook subscription doesn't exist, so delete it from our settings.
			Setting::delete( 'webhook_subscription_id' );
			Setting::delete( 'webhook_signature_key' );
		}

		// Now see if there is already a webhook registered for this site.
		$webhook_subscriptions = Manager::list( $token );

		if ( ! empty( $webhook_subscriptions ) ) {
			foreach ( $webhook_subscriptions as $webhook_subscription ) {
				if ( Manager::check_notification_url( $webhook_subscription->getNotificationUrl() ) ) {
					// If the webhook subscription has the same notification URL, we can use it.
					$existing_events = $webhook_subscription->getEventTypes();
					$missing_events  = Manager::check_events( $existing_events );
					$is_enabled      = $webhook_subscription->getEnabled();

					$update_subscription = ! empty( $missing_events ) || ! $is_enabled ?
						new WebhookSubscription() :
						false;

					if ( $update_subscription ) {
						$update_subscription->setEventTypes( array_merge( $existing_events, $missing_events ) );
						$update_subscription->setEnabled( true );

						$updated = Manager::update( $webhook_subscription->getId(), $update_subscription, $token );

						if ( ! $updated['success'] ) {
							wp_send_json_error( $updated['message'], 400 );
						}

						Setting::set( 'webhook_subscription_id', $webhook_subscription->getId() );
						Setting::set( 'webhook_signature_key', $webhook_subscription->getSignatureKey() );

						wp_send_json_success( __( 'Webhooks successfully updated.', 'easy-digital-downloads' ) );
						break;
					}

					// Everything was already in place, so just update the ID and signature key.
					Setting::set( 'webhook_subscription_id', $webhook_subscription->getId() );
					Setting::set( 'webhook_signature_key', $webhook_subscription->getSignatureKey() );

					wp_send_json_success( __( 'Webhooks successfully updated.', 'easy-digital-downloads' ) );
					break;
				}
			}
		}

		$created = Manager::create( $token );

		if ( false === $created['success'] ) {
			wp_send_json_error( $created['message'], 400 );
		}

		Setting::set( 'webhook_subscription_id', $created['subscription']->getId() );
		Setting::set( 'webhook_signature_key', $created['subscription']->getSignatureKey() );

		wp_send_json_success( __( 'Webhooks successfully registered.', 'easy-digital-downloads' ) );
	}

	/**
	 * Update the docs link.
	 *
	 * @since 3.4.0
	 * @param string $link The link.
	 * @return string
	 */
	public function update_docs_link( $link ) {
		if (
			'edd-settings' === filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS ) &&
			'gateways' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS ) &&
			'square' === filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS )
		) {
			return 'https://easydigitaldownloads.com/docs/setting-up-square-payments/';
		}

		return $link;
	}
}
