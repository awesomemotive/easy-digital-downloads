<?php
/**
 * Admin Pages
 *
 * @package     EDD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the admin pages.
 *
 * @since 3.0
 * @since 3.3.0 Moved to EDD\Admin\Menu\Pages::get_pages().
 *
 * @return array
 */
function edd_get_admin_pages() {
	return (array) apply_filters( 'edd_admin_pages', EDD\Admin\Menu\Pages::get_pages() );
}

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 *
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_customers_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 */
function edd_add_options_link() {
	EDD\Admin\Menu\Pages::register();
}
add_action( 'admin_menu', 'edd_add_options_link', 10 );

/**
 * Whether the current admin area page is one that allows the insertion of a
 * button to make inserting Downloads easier.
 *
 * @since 3.0
 * @global $pagenow $pagenow
 * @global $typenow $typenow
 * @return boolean
 */
function edd_is_insertable_admin_page() {
	global $pagenow, $typenow;

	// Allowed pages.
	$pages = array(
		'post.php',
		'page.php',
		'post-new.php',
		'post-edit.php',
	);

	// Allowed post types.
	$types = get_post_types_by_support( 'edd_insert_download' );

	// Return if page and type are allowed.
	return in_array( $pagenow, $pages, true ) && in_array( $typenow, $types, true );
}

/**
 * Forces the Cache-Control header on our admin pages to send the no-store header
 * which prevents the back-forward cache (bfcache) from storing a copy of this page in local
 * cache. This helps make sure that page elements modified via AJAX and DOM manipulations aren't
 * incorrectly shown as if they never changed.
 *
 * @since 3.0
 * @param array $headers An array of nocache headers.
 *
 * @return array
 */
function _edd_bfcache_buster( $headers ) {
	if ( ! is_admin() & ! edd_is_admin_page() ) {
		return $headers;
	}

	$headers['Cache-Control'] = 'no-cache, must-revalidate, max-age=0, no-store';

	return $headers;
}
add_filter( 'nocache_headers', '_edd_bfcache_buster', 10, 1 );
