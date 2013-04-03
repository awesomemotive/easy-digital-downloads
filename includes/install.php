<?php
/**
 * Install Function
 *
 * @package     EDD
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'downloads' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successfull install, the user is redirected to the EDD Welcome
 * screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $edd_options
 * @global $wp_version
 * @return void
 */
function edd_install() {
	global $wpdb, $edd_options, $wp_version;

	if ( (float) $wp_version < 3.3 ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( 'Looks like you\'re running an older version of WordPress, you need to be running at least WordPress 3.3 to use Easy Digital Downloads.', 'edd' ), __( 'Easy Digital Downloads is not compatible with this version of WordPress.', 'edd' ), array( 'back_link' => true ) );
	}

	// Setup the Downloads Custom Post Type
	edd_setup_edd_post_types();

	// Setup the Download Taxonomies
	edd_setup_download_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules();

	// Checks if the purchase page option exists
	if ( ! isset( $edd_options['purchase_page'] ) ) {
	    // Checkout Page
		$checkout = wp_insert_post(
			array(
				'post_title'     => __( 'Checkout', 'edd' ),
				'post_content'   => '[download_checkout]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Purchase Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => __( 'Purchase Confirmation', 'edd' ),
				'post_content'   => __( 'Thank you for your purchase! [edd_receipt]', 'edd' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'edd' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'edd' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		// Purchase History (History) Page
		$history = wp_insert_post(
			array(
				'post_title'     => __( 'Purchase History', 'edd' ),
				'post_content'   => '[download_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options = array(
			'purchase_page' => $checkout,
			'success_page'  => $success,
			'failure_page'  => $failed
		);

		update_option( 'edd_settings_general', $options );
		update_option( 'edd_version', EDD_VERSION );
	}

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) )

	// Add the transient to redirect
    set_transient( '_edd_activation_redirect', true, 30 );
}
register_activation_hook( EDD_PLUGIN_FILE, 'edd_install' );