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
	 * @return void
	 */
	public static function register() {
		$js_dir     = EDD_PLUGIN_URL . 'assets/js/';
		$version    = edd_admin_get_script_version();
		$admin_deps = array( 'jquery', 'jquery-form', 'underscore' );

		// Register scripts.
		wp_register_script( 'jquery-chosen', $js_dir . 'vendor/chosen.jquery.min.js', array( 'jquery' ), $version, true );
		wp_register_script( 'edd-jquery-flot', $js_dir . 'vendor/jquery.flot.min.js', array( 'jquery' ), $version, true );
		wp_register_script( 'edd-moment-js', $js_dir . 'vendor/moment.min.js', array(), $version, true );
		wp_register_script( 'edd-moment-timezone-js', $js_dir . 'vendor/moment-timezone.min.js', array( 'edd-moment-js' ), $version, true );
		wp_register_script( 'edd-chart-js', $js_dir . 'vendor/chartjs.min.js', array( 'edd-moment-js', 'edd-moment-timezone-js' ), $version, true );
		wp_register_script( 'edd-admin-scripts', $js_dir . 'edd-admin.js', $admin_deps, $version, true );
		wp_register_script( 'edd-admin-tax-rates', $js_dir . 'edd-admin-tax-rates.js', array( 'wp-backbone', 'jquery-chosen' ), $version, true );
		wp_register_script( 'edd-admin-email-tags', $js_dir . 'edd-admin-email-tags.js', array( 'thickbox', 'wp-util' ), $version, true );
		wp_register_script( 'edd-admin-downloads-editor', $js_dir . 'edd-admin-downloads-editor.js', array( 'wp-dom-ready', 'wp-api-fetch', 'wp-data' ), $version, true );

		foreach ( self::get_admin_pages() as $page => $deps ) {
			wp_register_script(
				'edd-admin-' . $page,
				$js_dir . 'edd-admin-' . $page . '.js',
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

		// Enqueue media on EDD admin pages
		wp_enqueue_media();

		// Scripts to enqueue
		$scripts = array(
			'edd-admin-scripts',
			'jquery-chosen',
			'jquery-form',
			'jquery-ui-datepicker',
			'jquery-ui-dialog',
			'jquery-ui-tooltip',
			'media-upload',
			'thickbox',
			'wp-ajax-response',
			'wp-color-picker',
		);

		// Loop through and enqueue the scripts
		foreach ( $scripts as $script ) {
			wp_enqueue_script( $script );
		}

		// Downloads page.
		if ( edd_is_admin_page( 'download' ) ) {
			wp_enqueue_script( 'edd-admin-downloads' );
		}

		if ( ( edd_is_admin_page( 'download', 'edit' ) || edd_is_admin_page( 'download', 'new' ) ) && get_current_screen()->is_block_editor() ) {
			wp_enqueue_script( 'edd-admin-downloads-editor' );
		}

		// Upgrades Page
		if ( in_array( $hook, array( 'edd-admin-upgrades', 'download_page_edd-tools' ) ) ) {
			wp_enqueue_script( 'edd-admin-tools-export' );
			wp_enqueue_script( 'edd-admin-upgrades' );
		}
	}

	/**
	 * Get the admin pages and their dependencies.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_admin_pages() {
		return array(
			'customers'         => array(
				'edd-admin-tools-export',
			),
			'dashboard'         => array(),
			'discounts'         => array(),
			'downloads'         => array(),
			'tools-export'      => array(),
			'tools-import'      => array(),
			'notes'             => array(),
			'onboarding'        => array(),
			'orders'            => array(
				'edd-admin-notes',
				'wp-util',
				'wp-backbone',
			),
			'emails-editor'     => array(
				'wp-tinymce',
			),
			'emails-list-table' => array(),
			// Backwards compatibility.
			'payments'          => array(),
			'reports'           => array(
				'edd-chart-js',
			),
			'settings'          => array(),
			'tools'             => array(
				'edd-admin-tools-export',
			),
			'upgrades'          => array(),
		);
	}
}
