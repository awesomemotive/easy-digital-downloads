<?php
/**
 * Handles EDD admin styles.
 *
 * @package     EDD
 * @subpackage  Admin/Assets
 * @since       3.3.0
 */

namespace EDD\Admin\Assets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Styles class.
 */
class Styles {

	/**
	 * Register the EDD admin styles.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function register() {
		$css_dir    = EDD_PLUGIN_URL . 'assets/css/';
		$css_suffix = is_rtl() ? '-rtl.min.css' : '.min.css';
		$version    = edd_admin_get_script_version();
		$deps       = array( 'edd-admin' );

		// Register styles.
		wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $css_suffix, array(), $version );
		wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-fresh' . $css_suffix, array(), $version );
		wp_register_style( 'edd-admin', $css_dir . 'edd-admin' . $css_suffix, array( 'forms' ), $version );
		wp_register_style( 'edd-admin-menu', $css_dir . 'edd-admin-menu' . $css_suffix, array(), $version );
		wp_register_style( 'edd-admin-chosen', $css_dir . 'edd-admin-chosen' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-email-tags', $css_dir . 'edd-admin-email-tags' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-datepicker', $css_dir . 'edd-admin-datepicker' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-tax-rates', $css_dir . 'edd-admin-tax-rates' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-onboarding', $css_dir . 'edd-admin-onboarding' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-emails', $css_dir . 'edd-admin-emails' . $css_suffix, $deps, $version );
	}

	/**
	 * Enqueue the EDD admin styles.
	 *
	 * @since 3.3.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public static function enqueue( $hook ) {
		// Always enqueue the admin menu CSS.
		wp_enqueue_style( 'edd-admin-menu' );

		// Bail if not an EDD admin page
		if ( ! edd_should_load_admin_scripts( $hook ) ) {
			return;
		}

		// Loop through and enqueue the scripts.
		foreach ( self::get_styles() as $style ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * Get the EDD admin styles. They are enqueued in priority order.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_styles() {
		return array(
			'jquery-chosen',
			'thickbox',
			'wp-jquery-ui-dialog',
			'wp-color-picker',
			'edd-admin',
			'edd-admin-chosen',
			'edd-admin-datepicker',
		);
	}
}
