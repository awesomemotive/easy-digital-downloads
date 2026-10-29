<?php
/**
 * Handles EDD admin scripts.
 *
 * @package     EDD
 * @subpackage  Admin/Assets
 * @since       3.3.0
 */

namespace EDD\Admin\Assets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Scripts class.
 */
class Scripts {

	/**
	 * Register the EDD admin scripts.
	 *
	 * @since 3.3.0
	 * @since 3.7.1 Registers the Chosen shim as edd-admin-chosen-compat.
	 * @return void
	 */
	public static function register() {
		$js_dir     = edd_get_assets_url( 'js/admin' );
		$vendor_dir = edd_get_assets_url( 'vendor/js' );
		$version    = edd_admin_get_script_version();
		$admin_deps = array( 'jquery', 'jquery-form', 'underscore', 'edd-tom-select' );

		// Register vendor scripts from assets/vendor/js.
		wp_register_script( 'edd-tom-select', $vendor_dir . 'tom-select.complete.min.js', array(), $version, true );
		wp_register_script( 'edd-jquery-flot', $vendor_dir . 'jquery.flot.min.js', array( 'jquery' ), $version, true );
		wp_register_script( 'edd-moment-js', $vendor_dir . 'moment.min.js', array(), $version, true );
		wp_register_script( 'edd-moment-timezone-js', $vendor_dir . 'moment-timezone.min.js', array( 'edd-moment-js' ), $version, true );
		wp_register_script( 'edd-chart-js', $vendor_dir . 'chartjs.min.js', array( 'edd-moment-js', 'edd-moment-timezone-js' ), $version, true );

		// Register compiled scripts from assets/build/js.
		wp_register_script( 'edd-admin-chosen-compat', $js_dir . 'chosen-compat.js', array( 'jquery', 'edd-tom-select' ), $version, true );
		wp_register_script( 'edd-admin-scripts', $js_dir . 'admin.js', $admin_deps, $version, true );
		wp_register_script( 'edd-admin-tax-rates', $js_dir . 'tax-rates.js', array( 'react-jsx-runtime', 'wp-element', 'edd-tom-select' ), $version, true );
		wp_register_script( 'edd-admin-email-tags', $js_dir . 'email-tags.js', array( 'wp-util' ), $version, true );
		wp_register_script( 'edd-admin-downloads-editor', $js_dir . 'downloads-editor.js', array( 'wp-dom-ready', 'wp-api-fetch', 'wp-data' ), $version, true );

		// Customers script uses vanilla JS only — no jQuery/underscore dependency.
		wp_register_script( 'edd-admin-customers', $js_dir . 'customers.js', array( 'edd-admin-tools-export' ), $version, true );

		foreach ( self::get_admin_pages() as $page => $deps ) {
			wp_register_script(
				'edd-admin-' . $page,
				$js_dir . $page . '.js',
				array_merge( $admin_deps, $deps ),
				$version,
				true,
			);
		}
	}

	/**
	 * Enqueue the EDD admin scripts.
	 *
	 * @since 3.3.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public static function enqueue( $hook = '' ) {
		if ( ! edd_should_load_admin_scripts( $hook ) ) {
			return;
		}

		/**
		 * Prevent the CM Admin Tools JS from loading on our settings pages, as they
		 * are including options and actions that can permemtnly harm a store's data.
		 */
		wp_deregister_script( 'cmadm-utils' );
		wp_deregister_script( 'cmadm-backend' );

		self::register_chosen_compat();

		// Enqueue media on EDD admin pages.
		wp_enqueue_media();

		// Scripts to enqueue.
		$scripts = array(
			'edd-admin-scripts',
			'edd-admin-chosen-compat',
			'jquery-form',
			'jquery-ui-datepicker',
			'jquery-ui-tooltip',
			'media-upload',
			'wp-ajax-response',
			'wp-color-picker',
		);

		// Loop through and enqueue the scripts.
		foreach ( $scripts as $script ) {
			wp_enqueue_script( $script );
		}

		// Downloads page.
		if ( edd_is_admin_page( 'download' ) ) {
			wp_enqueue_script( 'edd-admin-downloads' );

			$current_screen = get_current_screen();
			if ( $current_screen && $current_screen->is_block_editor() ) {
				wp_enqueue_script( 'edd-admin-downloads-editor' );
			}
		}

		// Upgrades Page.
		if ( in_array( $hook, array( 'edd-admin-upgrades', 'download_page_edd-tools' ), true ) ) {
			wp_enqueue_script( 'edd-admin-tools-export' );
			wp_enqueue_script( 'edd-admin-upgrades' );
		}
	}

	/**
	 * Register the generic `jquery-chosen` handle as a back-compat alias for the EDD shim.
	 *
	 * The handle carries no source of its own and only pulls in `edd-admin-chosen-compat`, so
	 * extensions that enqueue or depend on it get the shim. Deregistering first also drops anything
	 * another plugin attached to the handle with `wp_localize_script()` or `wp_add_inline_script()`;
	 * on an EDD screen the shim stands in for that plugin's Chosen.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private static function register_chosen_compat() {
		wp_deregister_script( 'jquery-chosen' );
		wp_register_script( 'jquery-chosen', false, array( 'edd-admin-chosen-compat' ), edd_admin_get_script_version(), true );
	}

	/**
	 * Get the admin pages and their dependencies.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_admin_pages() {
		return array(
			'dashboard'         => array(),
			'discounts'         => array(),
			'downloads'         => array(),
			'tools-export'      => array(),
			'tools-import'      => array(),
			'notes'             => array(),
			'onboarding'        => array(),
			'orders'            => array(
				'edd-admin-notes',
				'react-jsx-runtime',
				'wp-element',
			),
			'emails-editor'     => array(
				'wp-tinymce',
			),
			'emails-list-table' => array(),
			// Backwards compatibility.
			'payments'          => array(),
			'reports'           => array(
				'edd-chart-js',
				'moment',
			),
			'settings'          => array(),
			'tools'             => array(
				'edd-admin-tools-export',
			),
			'upgrades'          => array(),
		);
	}
}
