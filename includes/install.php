<?php
/**
 * Install Function
 *
 * @package     EDD
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'downloads' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages.
 *
 * @since 1.0
 * @param  bool $network_wide If the plugin is being network-activated
 * @return void
 */
function edd_install( $network_wide = false ) {

	// Multi-site install
	if ( is_multisite() && ! empty( $network_wide ) ) {
		edd_run_multisite_install();

	// Single site install
	} else {
		edd_run_install();
	}
}

/**
 * Run the EDD installation on every site in the current network.
 *
 * @since 3.0
 */
function edd_run_multisite_install() {
	global $wpdb;

	// Get site count
	$network_id = get_current_network_id();
	$query      = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->blogs} WHERE site_id = %d", $network_id );
	$count      = $wpdb->get_var( $query );

	// Bail if no sites (this is really strange and bad)
	if ( empty( $count ) || is_wp_error( $count ) ) {
		return;
	}

	// Setup the steps
	$per_step    = 100;
	$total_steps = ceil( $count / $per_step );
	$step        = 1;
	$offset      = 0;

	// Step through all sites in this network in groups of 100
	do {

		// Get next batch of site IDs
		$query    = $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = %d LIMIT %d, %d", $network_id, $offset, $per_step );
		$site_ids = $wpdb->get_col( $query );

		// Proceed if site IDs exist
		if ( ! empty( $site_ids ) ) {
			foreach ( $site_ids as $site_id ) {
				edd_run_install( $site_id );
			}
		}

		// Bump the limit for the next iteration
		$offset = ( $step * $per_step ) - 1;

		// Bump the step
		++$step;

	// Bail when steps are greater than or equal to total steps
	} while ( $total_steps > $step );
}

/**
 * Run the EDD Install process
 *
 * @since 2.5
 * @since 3.0 Added $site_id parameter
 */
function edd_run_install( $site_id = false ) {

	if ( edd_get_db_version() ) {
		return;
	}

	// Not switched
	$switched = false;

	// Maybe switch to a site
	if ( ! empty( $site_id ) ) {
		switch_to_blog( $site_id );
		$switched = true;
	}

	// Setup the components (customers, discounts, logs, etc...)
	edd_setup_components();

	// Setup the Downloads Custom Post Type
	edd_setup_edd_post_types();

	// Setup the Download Taxonomies
	edd_setup_download_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Install the default pages and settings.
	edd_install_pages();
	edd_install_settings();

	// Set the activation date.
	edd_get_activation_date();

	// Create wp-content/uploads/edd/ folder and the .htaccess file
	if ( ! function_exists( 'edd_create_protection_files' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
	}
	if ( function_exists( 'edd_create_protection_files' ) ) {
		edd_create_protection_files( true );
	}

	// Create EDD shop roles
	EDD()->roles->add_roles();
	EDD()->roles->add_caps();

	// API version
	update_option( 'edd_default_api_version', 'v' . EDD()->api->get_version() );

	// Check for PHP Session support, and enable if available
	EDD()->session->use_php_sessions();

	// Maybe set all upgrades as complete (only on fresh installation)
	edd_set_all_upgrades_complete();

	// Update the database version (must be at end, but before site restore)
	edd_do_automatic_upgrades();

	// Maybe switch back
	if ( true === $switched ) {
		restore_current_blog();
	}

	if ( ! get_option( 'edd_onboarding_completed', false ) ) {
		set_transient( 'edd_onboarding_redirect', true, 30 );
	}
}

/**
 * Maybe set upgrades as complete during a fresh
 * @since 3.0
 */
function edd_set_all_upgrades_complete() {

	// Bail if not a fresh installation
	if ( edd_get_db_version() ) {
		return;
	}

	// When new upgrade routines are added, mark them as complete on fresh install
	$upgrade_routines = edd_get_all_upgrades();

	// Loop through upgrade routines and mark them as complete
	foreach ( $upgrade_routines as $upgrade ) {
		edd_set_upgrade_complete( $upgrade );
	}
}

/**
 * Install the required pages
 *
 * @since 3.0
 */
function edd_install_pages() {

	// Get all of the EDD settings
	$current_options = get_option( 'edd_settings', array() );

	// Required store pages
	$pages = edd_get_required_pages();

	// Look for missing pages
	$missing_pages  = array_diff_key( $pages, $current_options );
	$pages_to_check = array_intersect_key( $current_options, $pages );

	// Query for any existing pages
	$posts = new WP_Query(
		array(
			'include'   => array_values( $pages_to_check ),
			'post_type' => 'page',
			'fields'    => 'ids',
		)
	);

	// Default value for checkout page
	$checkout = 0;

	// We'll only update settings on change
	$changed = false;

	// Use the current user as the page author.
	$user_id = get_current_user_id();

	// Loop through all pages, fix or create any missing ones
	foreach ( $pages as $page => $page_attributes ) {

		$page_attributes = wp_parse_args(
			$page_attributes,
			array(
				'post_status'    => 'publish',
				'post_author'    => $user_id,
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);

		$page_id = ! empty( $pages_to_check[ $page ] ) ? $pages_to_check[ $page ] : false;

		// Checks if the page option exists
		$page_object = ! array_key_exists( $page, $missing_pages ) && ! empty( $posts->posts ) && ! empty( $page_id )
			? get_post( $page_id )
			: array();

		// Skip if page exists
		if ( ! empty( $page_object ) ) {

			// Set the checkout page
			if ( 'purchase_page' === $page ) {
				$checkout = $page_object->ID;
			}

			// Skip if page exists
			continue;
		}

		if ( ! isset( $page_attributes['post_parent'] ) ) {
			$page_attributes['post_parent'] = $checkout;
		}

		// Create the new page
		$new_page = wp_insert_post( $page_attributes );

		// Update the checkout page ID
		if ( 'purchase_page' === $page ) {
			$checkout = $new_page;
		}

		// Set the page option
		$current_options[ $page ] = $new_page;

		// Pages changed
		$changed = true;
	}

	// Update the option
	if ( true === $changed ) {
		update_option( 'edd_settings', $current_options );
	}
}

/**
 * Gets the array of required pages with default attributes and content for EDD.
 *
 * @since 3.1
 * @return array
 */
function edd_get_required_pages() {

	return apply_filters(
		'edd_required_pages',
		array(
			'purchase_page'         => array(
				'post_title'   => __( 'Checkout', 'easy-digital-downloads' ),
				'post_content' => '<!-- wp:shortcode -->[download_checkout]<!-- /wp:shortcode -->',
				'post_parent'  => 0,
			),
			'success_page'          => array(
				'post_title'   => __( 'Purchase Confirmation', 'easy-digital-downloads' ),
				'post_content' => '<!-- wp:paragraph --><p>' . __( 'Thank you for your purchase!', 'easy-digital-downloads' ) . '</p><!-- /wp:paragraph --><!-- wp:shortcode -->[edd_receipt]<!-- /wp:shortcode -->',
			),
			'failure_page'          => array(
				'post_title'   => __( 'Transaction Failed', 'easy-digital-downloads' ),
				'post_content' => '<!-- wp:paragraph --><p>' . __( 'Your transaction failed; please try again or contact site support.', 'easy-digital-downloads' ) .'</p><!-- /wp:paragraph -->',
			),
			'purchase_history_page' => array(
				'post_title'   => __( 'Purchase History', 'easy-digital-downloads' ),
				'post_content' => '<!-- wp:shortcode -->[purchase_history]<!-- /wp:shortcode -->',
			),
		)
	);
}

/**
 * Install the default settings
 *
 * @since 3.0
 * @global array $edd_options
 * @return void
 */
function edd_install_settings() {

	global $edd_options;

	// Setup some default options
	$options = array();

	// Populate some default values
	$all_settings = edd_get_registered_settings();

	if ( ! empty( $all_settings ) ) {
		foreach ( $all_settings as $tab => $sections ) {
			foreach ( $sections as $section => $settings) {

				// Check for backwards compatibility
				$tab_sections = edd_get_settings_tab_sections( $tab );
				if ( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
					$section  = 'main';
					$settings = $sections;
				}

				foreach ( $settings as $option ) {
					if ( ! empty( $option['type'] ) && 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
						$options[ $option['id'] ] = '1';
					}
				}
			}
		}
	}

	$settings       = get_option( 'edd_settings', array() );
	$merged_options = array_merge( $settings, $options );
	$edd_options    = $merged_options;

	// Update the settings
	update_option( 'edd_settings', $merged_options );
}

/**
 * When a new Blog is created in multisite, see if EDD is network activated, and run the installer
 *
 * @since  2.5
 * @param  int|WP_Site $blog WordPress 5.1 passes a WP_Site object.
 * @return void
 */
function edd_new_blog_created( $blog ) {

	// Bail if plugin is not activated for the network
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
add_action( 'wp_initialize_site', 'edd_new_blog_created' );

/**
 * Drop our custom tables when a mu site is deleted
 *
 * @deprecated 3.0   Handled by WP_DB_Table
 * @since      2.5
 * @param      array $tables  The tables to drop
 * @param      int   $blog_id The Blog ID being deleted
 * @return     array          The tables to drop
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

	do_action( 'edd_after_install', $edd_options );

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

	if ( ! is_object( $wp_roles ) ) {
		return;
	}

	if ( empty( $wp_roles->roles ) || ! array_key_exists( 'shop_manager', $wp_roles->roles ) ) {

		if ( empty( $wp_roles->roles ) ) {
			$wp_roles->roles = array();
		}
		// Create EDD shop roles
		$roles = new EDD_Roles();
		$roles->add_roles();
		$roles->add_caps();
	}
}
add_action( 'admin_init', 'edd_install_roles_on_network' );
