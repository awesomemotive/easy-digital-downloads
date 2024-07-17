<?php
/**
 * Registers the GB Legacy notification.
 *
 * @package EDD
 * @subpackage Admin\Notifications
 * @since 3.3.0
 */

namespace EDD\Admin\Notifications;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents the GB Legacy notification.
 *
 * @since 3.3.0
 */
class GBLegacy extends Notification {

	/**
	 * The ID of the GBLegacy notification.
	 *
	 * @var string
	 */
	protected static $id = 'gb-legacy';

	/**
	 * Determines if registration is allowed.
	 *
	 * This method checks if registration is allowed based on certain conditions.
	 *
	 * @return bool Returns true if registration is allowed, false otherwise.
	 */
	public static function can_register(): bool {
		$can_register = array(
			self::base_state_is_legacy(),
			self::tax_rate_uses_legacy_region(),
		);

		return ! empty( array_filter( $can_register ) );
	}

	/**
	 * Registers the GBLegacy notification parameters.
	 *
	 * @since 3.3.0
	 * @return array The notification parameters.
	 */
	protected static function register(): array {
		return array(
			'title'   => __( 'Please Update Your Settings', 'easy-digital-downloads' ),
			'content' => __(
				'We recently updated our list of regions for the United Kingdom. We have detected that your store is using an outdated region for the business settings or tax rates. Please review these settings and update them if needed.',
				'easy-digital-downloads'
			),
			'buttons' => self::get_buttons(),
		);
	}

	/**
	 * Checks if the base state is a legacy region.
	 *
	 * @since 3.3.0
	 * @return bool True if the base state is a legacy region, false otherwise.
	 */
	private static function base_state_is_legacy() {
		if ( 'GB' !== edd_get_option( 'base_country', 'US' ) ) {
			return false;
		}

		$base_state = edd_get_option( 'base_state', '' );
		if ( empty( $base_state ) ) {
			return false;
		}

		return (bool) array_key_exists( $base_state, self::get_legacy_states() );
	}

	/**
	 * Checks if any tax rate is using a legacy region.
	 *
	 * @since 3.3.0
	 * @return bool True if a tax rate is using a legacy region, false otherwise.
	 */
	private static function tax_rate_uses_legacy_region() {
		return ! empty(
			edd_get_tax_rates(
				array(
					'name'            => 'GB',
					'number'          => 1,
					'scope'           => 'region',
					'description__in' => array_keys( self::get_legacy_states() ),
					'status'          => 'active',
				)
			)
		);
	}

	/**
	 * Gets the buttons for the notification.
	 *
	 * @since 3.3.0
	 * @return array The buttons for the notification.
	 */
	private static function get_buttons() {
		$buttons = array();
		if ( self::base_state_is_legacy() ) {
			$buttons[] = array(
				'text' => __( 'Update Business Region', 'easy-digital-downloads' ),
				'url'  => edd_get_admin_url(
					array(
						'page' => 'edd-settings',
						'tab'  => 'general',
					)
				),
				'type' => 'primary',
			);
		}
		if ( self::tax_rate_uses_legacy_region() ) {
			$buttons[] = array(
				'text' => __( 'Review Tax Rates', 'easy-digital-downloads' ),
				'url'  => edd_get_admin_url(
					array(
						'page'    => 'edd-settings',
						'tab'     => 'taxes',
						'section' => 'rates',
					)
				),
			);
		}

		return $buttons;
	}

	/**
	 * Gets the legacy states.
	 *
	 * @since 3.3.0
	 * @return array The legacy states.
	 */
	private static function get_legacy_states() {
		return include EDD_PLUGIN_DIR . 'i18n/states-gb-legacy.php';
	}
}
