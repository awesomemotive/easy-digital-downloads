<?php
/**
 * Toggle Setting Dispatcher
 *
 * Routes AJAX toggle requests to the appropriate handler based on allowed settings.
 *
 * @package     EDD\Admin\Settings\Ajax
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.1
 */

namespace EDD\Admin\Settings\Ajax;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Toggle Setting Dispatcher
 *
 * Provides centralized routing for AJAX toggle requests to multiple handlers.
 * Each handler is registered and provides its own list of allowed settings to toggle.
 *
 * @since 3.6.1
 */
class Toggle implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.1
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'wp_ajax_edd_toggle_ajax_setting' => 'dispatch',
		);
	}

	/**
	 * Dispatch the toggle request to the appropriate handler.
	 *
	 * @since 3.6.1
	 * @return void
	 */
	public function dispatch(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorized', 'easy-digital-downloads' ),
				),
				403
			);
		}

		$setting = isset( $_POST['setting'] ) ? sanitize_key( wp_unslash( $_POST['setting'] ) ) : '';
		if ( empty( $setting ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No setting provided', 'easy-digital-downloads' ),
				),
				400
			);
		}

		// Find and execute the appropriate handler for this setting.
		foreach ( $this->get_handlers() as $handler ) {
			$allowed_settings = $handler::get_allowed_ajax_settings();
			if ( in_array( $setting, $allowed_settings, true ) ) {
				$handler::ajax_toggle_setting();
				return;
			}
		}

		// No handler found for this setting.
		wp_send_json_error(
			array(
				'message' => __( 'Invalid setting', 'easy-digital-downloads' ),
			),
			400
		);
	}

	/**
	 * Get the handlers for the toggle setting dispatcher.
	 *
	 * @since 3.6.1
	 * @return array
	 */
	private function get_handlers(): array {
		return apply_filters(
			'edd_toggle_setting_handlers',
			array()
		);
	}
}
