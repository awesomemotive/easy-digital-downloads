<?php
/**
 * AJAX Toggle Settings Trait
 *
 * Provides common functionality for settings that can be toggled via AJAX.
 *
 * @package     EDD\Admin\Settings\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.1
 */

namespace EDD\Admin\Settings\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * AjaxToggle Trait
 *
 * Provides shared logic for handling AJAX toggle requests for settings.
 * Implementing classes must define get_allowed_ajax_settings() method.
 *
 * @since 3.6.1
 */
trait AjaxToggle {

	/**
	 * Get the list of settings that this handler allows to be toggled via AJAX.
	 *
	 * @since 3.6.1
	 * @return array List of setting keys allowed to be toggled.
	 */
	abstract public static function get_allowed_ajax_settings(): array;

	/**
	 * Register the handler with the toggle dispatcher.
	 *
	 * @since 3.6.1
	 * @param array $handlers The list of handlers.
	 * @return array The list of handlers.
	 */
	public function register_handler( $handlers ): array {
		$handlers[] = self::class;

		return $handlers;
	}

	/**
	 * Handle the AJAX toggle request for this handler's allowed settings.
	 *
	 * @since 3.6.1
	 * @return void
	 */
	public static function ajax_toggle_setting(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorized', 'easy-digital-downloads' ),
				),
				403
			);
		}

		check_ajax_referer( 'edd-toggle-nonce', 'nonce' );

		$setting = isset( $_POST['setting'] ) ? sanitize_key( wp_unslash( $_POST['setting'] ) ) : '';
		if ( empty( $setting ) || ! in_array( $setting, static::get_allowed_ajax_settings(), true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid setting', 'easy-digital-downloads' ),
				),
				400
			);
		}

		$value = filter_input( INPUT_POST, 'value', FILTER_VALIDATE_BOOLEAN );
		if ( $value ) {
			edd_update_option( $setting, true );
		} else {
			edd_delete_option( $setting );
		}

		$response = array(
			'setting' => $setting,
			'value'   => $value,
		);

		wp_send_json_success( $response );
	}
}
