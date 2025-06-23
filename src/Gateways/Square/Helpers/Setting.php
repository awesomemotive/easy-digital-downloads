<?php
/**
 * Setting helper for the Square gateway.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Mode;

/**
 * Setting helper for the Square gateway.
 *
 * @since 3.4.0
 */
class Setting {

	/**
	 * Get the setting.
	 *
	 * @since 3.4.0
	 * @param string $key The key of the setting.
	 * @param string $default_value The default value of the setting.
	 *
	 * @return string The setting.
	 */
	public static function get( $key, $default_value = false ) {
		return edd_get_option( self::get_option_name( $key ), $default_value );
	}

	/**
	 * Set the setting.
	 *
	 * @since 3.4.0
	 * @param string $key The key of the setting.
	 * @param string $value The value of the setting.
	 *
	 * @return void
	 */
	public static function set( $key, $value ) {
		edd_update_option( self::get_option_name( $key ), $value );
	}

	/**
	 * Delete the setting.
	 *
	 * @since 3.4.0
	 * @param string $key The key of the setting.
	 *
	 * @return void
	 */
	public static function delete( $key ) {
		edd_delete_option( self::get_option_name( $key ) );
	}

	/**
	 * Get the option name. Updated to include the mode.
	 *
	 * @since 3.4.0
	 * @param string $key The key of the setting.
	 *
	 * @return string The option name.
	 */
	private static function get_option_name( $key ) {
		$mode = Mode::get();
		return "square_{$mode}_{$key}";
	}
}
