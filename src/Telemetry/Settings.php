<?php
/**
 * Gets the store settings data to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Telemetry;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 *
 * @since 3.1.1
 * @package EDD\Telemetry
 */
class Settings {
	use Traits\Anonymize;

	/**
	 * Gets the array of settings data.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public function get() {
		$data          = array();
		$settings_tabs = edd_get_settings_tabs();
		$settings      = edd_get_registered_settings();
		foreach ( $settings_tabs as $tab_key => $tab_contents ) {
			$tab_sections = edd_get_settings_tab_sections( $tab_key );
			foreach ( $tab_sections as $section_key => $section_title ) {
				$section_setting_types = edd_get_registered_settings_types( $tab_key, $section_key );
				if ( ! empty( $settings[ $tab_key ] ) && ! empty( $settings[ $tab_key ][ $section_key ] ) ) {
					$section_settings = $settings[ $tab_key ][ $section_key ];
					foreach ( $section_settings as $setting_key => $setting ) {
						$value = $this->get_setting_value( $tab_key, $section_key, $setting_key );
						// If the value is null, it's a skipped setting.
						if ( is_null( $value ) ) {
							continue;
						}
						$setting_id = isset( $setting['id'] ) ? $setting['id'] : sanitize_title( $setting['name'] );
						if ( is_array( $value ) ) {
							foreach ( $value as $v ) {
								$data[ "{$setting_id}_{$v}" ] = 1;
							}
						} else {
							$data[ $setting_id ] = $value;
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Gets the id and value for an individual setting.
	 *
	 * @param string $tab_key
	 * @param string $section_key
	 * @param string $setting_key
	 * @return mixed
	 */
	private function get_setting_value( $tab_key, $section_key, $setting_key ) {
		$setting = edd_get_registered_setting_details( $tab_key, $section_key, $setting_key );
		if ( ! $this->can_include_setting( $setting ) ) {
			return null;
		}

		$default = isset( $setting['std'] ) ? $setting['std'] : '';
		$value   = edd_get_option( $setting['id'], $default );
		if ( in_array( $setting['type'], array( 'checkbox', 'checkbox_description' ), true ) ) {
			return (int) (bool) $value;
		}
		if ( empty( $value ) ) {
			if ( 'currency' === $setting['id'] ) {
				return edd_get_currency();
			}
			if ( 'base_country' === $setting['id'] ) {
				return strtoupper( edd_get_option( 'stripe_connect_account_country', $value ) );
			}
		}
		if ( in_array( $setting['type'], $this->text_settings(), true ) ) {
			return $this->anonymize( $value );
		}
		if ( $this->should_populate_array( $setting ) ) {
			return $this->update_setting_value_array( $value, $setting );
		}

		return $value;
	}

	/**
	 * Evaluates whether a setting can be included in the telemetry data.
	 *
	 * @since 3.1.1
	 * @param array $setting
	 * @return bool
	 */
	private function can_include_setting( $setting ) {

		if ( empty( $setting ) ) {
			return false;
		}

		// If the setting is marked readonly then it's not really a setting.
		if ( ! empty( $setting['args']['readonly'] ) ) {
			return false;
		}

		// Certain types of settings should always be skipped.
		if ( in_array( $setting['type'], $this->skipped_settings_types(), true ) ) {
			return false;
		}

		// Settings known to be PII are excluded.
		if ( in_array( $setting['id'], $this->sensitive_settings(), true ) ) {
			return false;
		}

		// Text settings are always excluded unless specifically included.
		if ( in_array( $setting['type'], $this->text_settings(), true ) && ! in_array( $setting['id'], $this->allowed_text_settings(), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * These settings types are either not settings or nearly always full of sensitive data/PII.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function skipped_settings_types() {

		return array_merge(
			edd_get_non_setting_types(),
			array(
				'rich_editor',
				'upload',
				'color',
				'recapture',
				'password',
			)
		);
	}

	/**
	 * These settings are known to be sensitive/PII and are not otherwise excluded.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function sensitive_settings() {
		return array(
			'base_state',
			'paypal_live_client_id',
			'paypal_live_client_secret',
			'paypal_sandbox_client_id',
			'paypal_sandbox_client_secret',
		);
	}

	/**
	 * We assume that any text field should be excluded unless it's in this array.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function allowed_text_settings() {
		return array();
	}

	/**
	 * Settings types which will be strings and which should be evaluated for PII.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function text_settings() {
		return array(
			'text',
			'textarea',
			'email',
		);
	}

	/**
	 * Whether an array of settings should be populated, due to the setting type.
	 *
	 * @since 3.1.1
	 * @param array $setting
	 * @return bool
	 */
	private function should_populate_array( $setting ) {
		$settings = array( 'gateways', 'accepted_cards' );

		return 'multicheck' === $setting['type'] || in_array( $setting['id'], $settings, true );
	}

	/**
	 * Updates the an array setting value to include all options.
	 *
	 * @since 3.1.1
	 * @param mixed $saved_value The actual saved value (can be empty).
	 * @param array $setting     The setting definition.
	 * @return array
	 */
	private function update_setting_value_array( $saved_value, $setting ) {
		$value = array();
		foreach ( $setting['options'] as $key => $label ) {
			if ( is_array( $saved_value ) && ! empty( $saved_value[ $key ] ) ) {
				$value[] = $key;
			}
		}

		return $value;
	}
}
