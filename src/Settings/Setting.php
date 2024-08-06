<?php
/**
 * Setting class.
 *
 * Used to interact with a specific setting. The contents of these methods were mostly pulled from EDD core's edd_*_option functions.
 * In order to allow for custom handling of saving and getting settings, it was moved to this class to unify the process.
 *
 * @since 3.3.3
 * @package EDD\Settings
 */

namespace EDD\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Utils\Convert;

/**
 * Setting class.
 *
 * @since 3.3.3
 */
final class Setting {

	/**
	 * Get a setting.
	 *
	 * @since 3.3.3
	 *
	 * @param string $setting  The setting to get.
	 * @param mixed  $default_value  The default value to return if the setting is not found.
	 * @return mixed
	 */
	public static function get( $setting = '', $default_value = null ) {
		global $edd_options;

		$value = $default_value;

		if ( isset( $edd_options[ $setting ] ) ) {
			if ( is_numeric( $edd_options[ $setting ] ) ) {
				$value = $edd_options[ $setting ];
			} else {
				$value = ! empty( $edd_options[ $setting ] ) ? $edd_options[ $setting ] : $default_value;
			}
		}

		/**
		 * General filter for the value before it is returned.
		 *
		 * This is a generic filter and requires that you verify the `setting` before changing the value and returning.
		 *
		 * @param mixed $value The value of the setting being returned.
		 * @param string $setting The setting being returned.
		 * @param mixed $default_value The default value to return if the setting is not found.
		 */
		$value = apply_filters( 'edd_get_option', $value, $setting, $default_value );

		/**
		 * Filter the value before it is returned.
		 *
		 * This is a specific filter for the $setting value, allowing you to avoid the need to check the setting before continuing with your custom logic.
		 *
		 * @since 3.3.3
		 *
		 * @param mixed $value The value of the setting being returned.
		 * @param string $setting The setting being returned.
		 * @param mixed $default_value The default value to return if the setting is not found.
		 */
		return apply_filters( 'edd_get_option_' . $setting, $value, $setting, $default_value );
	}

	/**
	 * Update a setting.
	 *
	 * @since 3.3.3
	 *
	 * @param string $setting The setting to update.
	 * @param mixed  $value   The value to update the setting with.
	 * @return bool
	 */
	public static function update( $setting = '', $value = '' ) {
		// If no setting, exit.
		if ( empty( $setting ) ) {
			return false;
		}

		// If this is a non-numeric value and empty, treat it as an intent to delete the setting.
		if ( ! is_numeric( $value ) && empty( $value ) ) {
			return self::delete( $setting );
		}

		// First let's grab the current settings.
		$options = get_option( 'edd_settings', array() );

		// Ensure we're working with an array if we end up with an unexpected value here.
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		/**
		 * General filter for the value before it is updated.
		 *
		 * This is a generic filter and requires that you verify the `setting` before changing the value and returning.
		 *
		 * @param mixed $value The value of the setting being updated.
		 * @param string $setting The setting being updated.
		 */
		$value = apply_filters( 'edd_update_option', $value, $setting );

		/**
		 * Filter the value before it is updated.
		 *
		 * This is a specific filter for the $setting value, allowing you to avoid the need to check the setting before continuing with your custom logic.
		 *
		 * @since 3.3.3
		 *
		 * @param mixed $value The value of the setting being updated.
		 */
		$value = apply_filters( 'edd_update_option_' . $setting, $value );

		// Sanitize the value by type, before updating.
		$value = self::sanitize_setting_by_type( $setting, $value );

		/**
		 * After sanitization, if the value is empty, do not update the setting and maintain its current value.
		 *
		 * This will prevent the setting from being updated with an empty value, possibly causing issues.
		 */
		if ( ! is_numeric( $value ) && empty( $value ) ) {
			return false;
		}

		// Next let's try to update the value.
		$options[ $setting ] = $value;
		$did_update          = update_option( 'edd_settings', $options );

		// If it updated, let's update the global variable.
		if ( $did_update ) {
			global $edd_options;
			$edd_options[ $setting ] = $value;
		}

		return $did_update;
	}

	/**
	 * Delete a setting.
	 *
	 * @since 3.3.3
	 *
	 * @param string $setting The setting to delete.
	 * @return bool
	 */
	public static function delete( $setting = '' ) {
		global $edd_options;

		// If no setting, exit.
		if ( empty( $setting ) ) {
			return false;
		}

		// First let's grab the current settings.
		$options = get_option( 'edd_settings' );

		// Next let's try to update the value.
		if ( isset( $options[ $setting ] ) ) {
			unset( $options[ $setting ] );
		}

		// Remove this option from the global EDD settings to the array_merge in edd_settings_sanitize() doesn't re-add it.
		if ( isset( $edd_options[ $setting ] ) ) {
			unset( $edd_options[ $setting ] );
		}

		$did_update = update_option( 'edd_settings', $options );

		// If it updated, let's update the global variable.
		if ( $did_update ) {
			$edd_options = $options;
		}

		return $did_update;
	}

	/**
	 * Sanitize a setting based on the setting type.
	 *
	 * @since 3.3.3
	 *
	 * @param string $setting The setting being sanitized.
	 * @param mixed  $value   The value to sanitize.
	 * @return mixed
	 */
	private static function sanitize_setting_by_type( $setting, $value ) {
		$setting_types = self::get_registered_settings_types();

		if ( ! isset( $setting_types[ $setting ] ) ) {
			return $value;
		}

		$type_class = 'EDD\\Settings\\Sanitize\\Types\\' . Convert::snake_to_camel( $setting_types[ $setting ] );
		if ( ! class_exists( $type_class ) ) {
			return $value;
		}

		return $type_class::sanitize( $value );
	}

	/**
	 * Get the registered setting types.
	 *
	 * While this is just calling the core function, the core function can be filtered, so setting a static variable here
	 * allows us to not have to run the foreach loop multiple times.
	 *
	 * @since 3.3.3
	 * @return array
	 */
	private static function get_registered_settings_types() {
		static $registered_setting_types;

		if ( null !== $registered_setting_types ) {
			return $registered_setting_types;
		}

		$registered_setting_types = edd_get_registered_settings_types();

		return $registered_setting_types;
	}
}
