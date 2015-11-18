<?php
/**
 * Install Function
 *
 * @package     EDD
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
 * pages. After successful install, the user is redirected to the EDD Welcome
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

	if( ! function_exists( 'edd_create_protection_files' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
	}

	// Setup the Downloads Custom Post Type
	edd_setup_edd_post_types();

	// Setup the Download Taxonomies
	edd_setup_download_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'edd_version' );
	if ( $current_version ) {
		update_option( 'edd_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	// Checks if the purchase page option exists
	if ( ! edd_get_option( 'purchase_page', false ) ) {
	  // Checkout Page
		$checkout = wp_insert_post(
			array(
				'post_title'     => __( 'Checkout', 'easy-digital-downloads' ),
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
				'post_title'     => __( 'Purchase Confirmation', 'easy-digital-downloads' ),
				'post_content'   => __( 'Thank you for your purchase! [edd_receipt]', 'easy-digital-downloads' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $checkout,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'easy-digital-downloads' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'easy-digital-downloads' ),
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
				'post_title'     => __( 'Purchase History', 'easy-digital-downloads' ),
				'post_content'   => '[purchase_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['purchase_page']         = $checkout;
		$options['success_page']          = $success;
		$options['failure_page']          = $failed;
		$options['purchase_history_page'] = $history;

	}

	// Populate some default values
	foreach( edd_get_registered_settings() as $tab => $settings ) {

		foreach ( $settings as $option ) {

			if( 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
				$options[ $option['id'] ] = '1';
			}

		}

	}

	update_option( 'edd_settings', array_merge( $edd_options, $options ) );
	update_option( 'edd_version', EDD_VERSION );

	// Create wp-content/uploads/edd/ folder and the .htaccess file
	edd_create_protection_files( true );

	// Create EDD shop roles
	$roles = new EDD_Roles;
	$roles->add_roles();
	$roles->add_caps();

	$api = new EDD_API;
	update_option( 'edd_default_api_version', 'v' . $api->get_version() );

	// Create the customers database
	@EDD()->customers->create_table();

	// Check for PHP Session support, and enable if available
	EDD()->session->use_php_sessions();

	// Add a temporary option to note that EDD pages have been created
	set_transient( '_edd_installed', $options, 30 );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	if ( ! $current_version ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

		// When new upgrade routines are added, mark them as complete on fresh install
		$upgrade_routines = array(
			'upgrade_payment_taxes',
			'upgrade_customer_payments_association',
			'upgrade_user_api_keys',
			'remove_refunded_sale_logs'
		);

		foreach ( $upgrade_routines as $upgrade ) {
			edd_set_upgrade_complete( $upgrade );
		}
	}

	// Add the transient to redirect
	set_transient( '_edd_activation_redirect', true, 30 );
}
register_activation_hook( EDD_PLUGIN_FILE, 'edd_install' );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * edd_after_install hook.
 *
 * @since 1.7
 * @return void
 */
function edd_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$edd_options = get_transient( '_edd_installed' );

	// Exit if not in admin or the transient doesn't exist
	if ( false === $edd_options ) {
		return;
	}

	// Create the customers database (this ensures it creates it on multisite instances where it is network activated)
	@EDD()->customers->create_table();

	// Delete the transient
	delete_transient( '_edd_installed' );

	do_action( 'edd_after_install', $edd_options );
}
add_action( 'admin_init', 'edd_after_install' );

/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when EDD is network activation so we need to create them during admin_init
 *
 * @since 1.9
 * @return void
 */
function edd_install_roles_on_network() {

	global $wp_roles;

	if( ! is_object( $wp_roles ) ) {
		return;
	}

	if( ! in_array( 'shop_manager', $wp_roles->roles ) ) {

		// Create EDD shop roles
		$roles = new EDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}
add_action( 'admin_init', 'edd_install_roles_on_network' );
