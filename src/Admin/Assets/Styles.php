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
		$css_dir    = edd_get_assets_url( 'css/admin' );
		$vendor_dir = edd_get_assets_url( 'vendor/css' );
		$css_suffix = is_rtl() ? '-rtl.min.css' : '.min.css';
		$version    = edd_admin_get_script_version();
		$deps       = array( 'edd-admin' );

		// Register vendor styles from assets/vendor/css.
		wp_register_style( 'jquery-chosen', $vendor_dir . 'chosen' . $css_suffix, array(), $version );
		wp_register_style( 'jquery-ui-css', $vendor_dir . 'jquery-ui-fresh' . $css_suffix, array(), $version );

		// Register compiled styles from assets/build/css.
		wp_register_style( 'edd-admin', $css_dir . 'admin' . $css_suffix, array( 'forms' ), $version );
		wp_register_style( 'edd-admin-menu', $css_dir . 'menu' . $css_suffix, array(), $version );
		wp_register_style( 'edd-admin-chosen', $css_dir . 'chosen' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-email-tags', $css_dir . 'email-tags' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-datepicker', $css_dir . 'datepicker' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-tax-rates', $css_dir . 'tax-rates' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-onboarding', $css_dir . 'onboarding' . $css_suffix, $deps, $version );
		wp_register_style( 'edd-admin-emails', $css_dir . 'emails' . $css_suffix, $deps, $version );
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

		// Bail if not an EDD admin page.
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
