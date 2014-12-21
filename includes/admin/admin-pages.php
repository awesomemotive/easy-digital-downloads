<?php
/**
 * Admin Pages
 *
 * @package     EDD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 * @return void
 */
function edd_add_options_link() {
	global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen, $edd_tools_page;

	$edd_payment            = get_post_type_object( 'edd_payment' );

	$edd_store_menu         = add_menu_page( __( 'Payment History', 'edd' ), __( 'Store', 'edd' ), 'manage_shop_settings', 'edd-store', 'edd_payment_history_page', 'dashicons-store', 25.544 );
	$edd_payments_page      = add_submenu_page( 'edd-store', $edd_payment->labels->name, $edd_payment->labels->menu_name, 'edit_shop_payments', 'edd-store', 'edd_payment_history_page' );
	$edd_discounts_page     = add_submenu_page( 'edd-store', __( 'Discount Codes', 'edd' ), __( 'Discount Codes', 'edd' ), 'manage_shop_discounts', 'edd-discounts', 'edd_discounts_page' );
	$edd_reports_page 	    = add_submenu_page( 'edd-store', __( 'Earnings and Sales Reports', 'edd' ), __( 'Reports', 'edd' ), 'view_shop_reports', 'edd-reports', 'edd_reports_page' );
	$edd_settings_page 	    = add_submenu_page( 'edd-store', __( 'Easy Digital Download Settings', 'edd' ), __( 'Settings', 'edd' ), 'manage_shop_settings', 'edd-settings', 'edd_options_page' );
	$edd_tools_page         = add_submenu_page( 'edd-store', __( 'Easy Digital Download Info and Tools', 'edd' ), __( 'Tools', 'edd' ), 'install_plugins', 'edd-tools', 'edd_tools_page' );
	$edd_add_ons_page 	    = add_submenu_page( 'edd-store', __( 'Easy Digital Download Add Ons', 'edd' ), __( 'Add Ons', 'edd' ), 'install_plugins', 'edd-addons', 'edd_add_ons_page' );
	$edd_upgrades_screen    = add_submenu_page( null, __( 'EDD Upgrades', 'edd' ), __( 'EDD Upgrades', 'edd' ), 'install_plugins', 'edd-upgrades', 'edd_upgrades_screen' );
}
add_action( 'admin_menu', 'edd_add_options_link', 10 );

/**
 *  Determines whether the current admin page is an EDD admin page.
 *  
 *  Only works after the `wp_loaded` hook, & most effective 
 *  starting on `admin_menu` hook.
 *  
 *  @since 1.9.6
 *  @return bool True if EDD admin page.
 */
function edd_is_admin_page() {

	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		return false;
	}
	
	global $pagenow, $typenow, $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen;

	if ( 'download' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
		return true;
	}
	
	$edd_admin_pages = apply_filters( 'edd_admin_pages', array( $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen, ) );
	
	if ( in_array( $pagenow, $edd_admin_pages ) ) {
		return true;
	} else {
		return false;
	}
}
