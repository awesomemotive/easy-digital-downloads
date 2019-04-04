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
 * @param  bool $network_side If the plugin is being network-activated
 * @return void
 */
function edd_install( $network_wide = false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			edd_run_install();
			restore_current_blog();

		}

	} else {

		edd_run_install();

	}

}
register_activation_hook( EDD_PLUGIN_FILE, 'edd_install' );

/**
 * Run the EDD Install process
 *
 * @since  2.5
 * @return void
 */
function edd_run_install() {
	global $wpdb, $edd_options;

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

	// Pull options from WP, not EDD's global
	$current_options = get_option( 'edd_settings', array() );

	// Checks if the purchase page option exists
	$purchase_page = array_key_exists( 'purchase_page', $current_options ) ? get_post( $current_options['purchase_page'] ) : false;
	if ( empty( $purchase_page ) ) {
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

		$options['purchase_page'] = $checkout;
	}

	$checkout = isset( $checkout ) ? $checkout : $current_options['purchase_page'];

	$success_page = array_key_exists( 'success_page', $current_options ) ? get_post( $current_options['success_page'] ) : false;
	if ( empty( $success_page ) ) {
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

		$options['success_page'] = $success;
	}

	$failure_page = array_key_exists( 'failure_page', $current_options ) ? get_post( $current_options['failure_page'] ) : false;
	if ( empty( $failure_page ) ) {
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

		$options['failure_page'] = $failed;
	}

	$history_page = array_key_exists( 'purchase_history_page', $current_options ) ? get_post( $current_options['purchase_history_page'] ) : false;
	if ( empty( $history_page ) ) {
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

		$options['purchase_history_page'] = $history;
	}

	// Populate some default values
	foreach( edd_get_registered_settings() as $tab => $sections ) {
		foreach( $sections as $section => $settings) {

			// Check for backwards compatibility
			$tab_sections = edd_get_settings_tab_sections( $tab );
			if( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section = 'main';
				$settings = $sections;
			}

			foreach ( $settings as $option ) {

				if( ! empty( $option['type'] ) && 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				}

			}
		}

	}

	$merged_options = array_merge( $edd_options, $options );
	$edd_options    = $merged_options;

	update_option( 'edd_settings', $merged_options );
	update_option( 'edd_version', EDD_VERSION );

	// Create wp-content/uploads/edd/ folder and the .htaccess file
	edd_create_protection_files( true );

	// Create EDD shop roles
	$roles = new EDD_Roles;
	$roles->add_roles();
	$roles->add_caps();

	$api = new EDD_API;
	update_option( 'edd_default_api_version', 'v' . $api->get_version() );

	// Create the customer databases
	@EDD()->customers->create_table();
	@EDD()->customer_meta->create_table();

	// Check for PHP Session support, and enable if available
	EDD()->session->use_php_sessions();

	// Add a temporary option to note that EDD pages have been created
	set_transient( '_edd_installed', $merged_options, 30 );

	if ( ! $current_version ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

		// When new upgrade routines are added, mark them as complete on fresh install
		$upgrade_routines = array(
			'upgrade_payment_taxes',
			'upgrade_customer_payments_association',
			'upgrade_user_api_keys',
			'remove_refunded_sale_logs',
			'update_file_download_log_data',
		);

		foreach ( $upgrade_routines as $upgrade ) {
			edd_set_upgrade_complete( $upgrade );
		}
	}

}

/**
 * When a new Blog is created in multisite, see if EDD is network activated, and run the installer
 *
 * @since  2.5
 * @param  int|WP_Site $blog WordPress 5.1 passes a WP_Site object.
 * @return void
 */
function edd_new_blog_created( $blog ) {
	if ( ! is_plugin_active_for_network( plugin_basename( EDD_PLUGIN_FILE ) ) ) {
		return;
	}

	if ( ! is_int( $blog ) ) {
		$blog = $blog->id;
	}

	switch_to_blog( $blog );
	edd_install();
	restore_current_blog();
}
if ( version_compare( get_bloginfo( 'version' ), '5.1', '>=' ) ) {
	add_action( 'wp_initialize_site', 'edd_new_blog_created' );
} else {
	add_action( 'wpmu_new_blog', 'edd_new_blog_created' );
}

/**
 * Drop our custom tables when a mu site is deleted
 *
 * @since  2.5
 * @param  array $tables  The tables to drop
 * @param  int   $blog_id The Blog ID being deleted
 * @return array          The tables to drop
 */
function edd_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$customers_db     = new EDD_DB_Customers();
	$customer_meta_db = new EDD_DB_Customer_Meta();
	if ( $customers_db->installed() ) {
		$tables[] = $customers_db->table_name;
		$tables[] = $customer_meta_db->table_name;
	}
	restore_current_blog();

	return $tables;

}
add_filter( 'wpmu_drop_tables', 'edd_wpmu_drop_tables', 10, 2 );

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

	$edd_options     = get_transient( '_edd_installed' );
	$edd_table_check = get_option( '_edd_table_check', false );

	if ( false === $edd_table_check || current_time( 'timestamp' ) > $edd_table_check ) {

		if ( ! @EDD()->customer_meta->installed() ) {

			// Create the customer meta database (this ensures it creates it on multisite instances where it is network activated)
			@EDD()->customer_meta->create_table();

		}

		if ( ! @EDD()->customers->installed() ) {
			// Create the customers database (this ensures it creates it on multisite instances where it is network activated)
			@EDD()->customers->create_table();
			@EDD()->customer_meta->create_table();

			do_action( 'edd_after_install', $edd_options );
		}

		update_option( '_edd_table_check', ( current_time( 'timestamp' ) + WEEK_IN_SECONDS ) );

	}

	if ( false !== $edd_options ) {
		// Delete the transient
		delete_transient( '_edd_installed' );
	}


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


	if( empty( $wp_roles->roles ) || ! array_key_exists( 'shop_manager', $wp_roles->roles ) ) {

		// Create EDD shop roles
		$roles = new EDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}
add_action( 'admin_init', 'edd_install_roles_on_network' );
