<?php
/**
 * Plugin Name: Easy Digital Downloads
 * Plugin URI: https://easydigitaldownloads.com
 * Description: The easiest way to sell digital products with WordPress.
 * Author: Easy Digital Downloads
 * Author URI: https://easydigitaldownloads.com
 * Version: 3.2.5
 * Text Domain: easy-digital-downloads
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * Easy Digital Downloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Easy Digital Downloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Digital Downloads. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package EDD
 * @category Core
 * @author Easy Digital Downloads
 * @version 3.2.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Don't allow multiple versions to be active.
if ( function_exists( 'EDD' ) ) {

	if ( ! function_exists( 'edd_pro_just_activated' ) ) {
		/**
		 * When we activate a Pro version, we need to do additional operations:
		 * 1) deactivate a Lite version;
		 * 2) register an option so we know when Pro was activated.
		 *
		 * @since 3.1.1
		 */
		function edd_pro_just_activated() {
			if ( ! get_option( 'edd_pro_activation_date', false ) ) {
				update_option( 'edd_pro_activation_date', time() );
			}
			edd_deactivate();
		}
	}
	add_action( 'activate_easy-digital-downloads-pro/easy-digital-downloads.php', 'edd_pro_just_activated' );

	if ( ! function_exists( 'edd_lite_just_activated' ) ) {
		/**
		 * Store temporarily that the Lite version of the plugin was activated.
		 * This is needed because WP does a redirect after activation and
		 * we need to preserve this state to know whether user activated Lite or not.
		 *
		 * @since 1.5.8
		 */
		function edd_lite_just_activated() {

			set_transient( 'edd_lite_just_activated', true );
		}
	}
	add_action( 'activate_easy-digital-downloads/easy-digital-downloads.php', 'edd_lite_just_activated' );

	if ( ! function_exists( 'edd_lite_just_deactivated' ) ) {
		/**
		 * Store temporarily that Lite plugin was deactivated.
		 * Convert temporary "activated" value to a global variable,
		 * so it is available through the request. Remove from the storage.
		 *
		 * @since 1.5.8
		 */
		function edd_lite_just_deactivated() {

			global $edd_lite_just_activated, $edd_lite_just_deactivated;

			$edd_lite_just_activated   = (bool) get_transient( 'edd_lite_just_activated' );
			$edd_lite_just_deactivated = true;

			delete_transient( 'edd_lite_just_activated' );
		}
	}
	add_action( 'deactivate_easy-digital-downloads/easy-digital-downloads.php', 'edd_lite_just_deactivated' );

	if ( ! function_exists( 'edd_deactivate' ) ) {
		/**
		 * Deactivate Lite if EDD Pro already activated.
		 *
		 * @since 1.0.0
		 */
		function edd_deactivate() {

			$plugin = 'easy-digital-downloads/easy-digital-downloads.php';

			deactivate_plugins( $plugin );

			do_action( 'edd_plugin_deactivated', $plugin );
		}
	}
	add_action( 'admin_init', 'edd_deactivate' );

	if ( ! function_exists( 'edd_lite_notice' ) ) {
		/**
		 * Display the notice after deactivation when Pro is still active
		 * and user wanted to activate the Lite version of the plugin.
		 *
		 * @since 1.0.0
		 */
		function edd_lite_notice() {

			global $edd_lite_just_activated, $edd_lite_just_deactivated;

			if (
				empty( $edd_lite_just_activated ) ||
				empty( $edd_lite_just_deactivated )
			) {
				return;
			}

			// Currently tried to activate Lite with Pro still active, so display the message.
			printf(
				'<div class="notice notice-warning">
					<p>%1$s</p>
					<p>%2$s</p>
				</div>',
				esc_html__( 'Heads up!', 'easy-digital-downloads' ),
				esc_html__( 'Your site already has Easy Digital Downloads (Pro) activated. If you want to switch to Easy Digital Downloads, please first go to Plugins → Installed Plugins and deactivate Easy Digital Downloads (Pro). Then, you can activate Easy Digital Downloads.', 'easy-digital-downloads' )
			);

			if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			unset( $edd_lite_just_activated, $edd_lite_just_deactivated );
		}
	}
	add_action( 'admin_notices', 'edd_lite_notice' );

	// Do not process the plugin code further.
	return;
}

// Plugin Root File.
if ( ! defined( 'EDD_PLUGIN_FILE' ) ) {
	define( 'EDD_PLUGIN_FILE', __FILE__ );
}

// Plugin Base Name.
if ( ! defined( 'EDD_PLUGIN_BASE' ) ) {
	define( 'EDD_PLUGIN_BASE', plugin_basename( __FILE__ ) );
}

// Plugin Folder Path.
if ( ! defined( 'EDD_PLUGIN_DIR' ) ) {
	define( 'EDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'EDD_PLUGIN_URL' ) ) {
	define( 'EDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once dirname( __FILE__ ) . '/includes/class-edd-requirements-check.php';
new EDD_Requirements_Check();
