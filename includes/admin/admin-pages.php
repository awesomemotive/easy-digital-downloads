<?php
/**
 * Admin Pages
 *
 * @package     EDD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
 * @global @edd_payments_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_system_info_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 * @return void
 */
function edd_add_options_link() {
	global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_settings_export, $edd_upgrades_screen;

	require_once 'system-info.php';

	$edd_payments_page   	= add_submenu_page( 'edit.php?post_type=download', __( 'Payment History', 'edd' ), __( 'Payment History', 'edd' ), 'edit_shop_payments', 'edd-payment-history', 'edd_payment_history_page' );
	$edd_discounts_page     = add_submenu_page( 'edit.php?post_type=download', __( 'Discount Codes', 'edd' ), __( 'Discount Codes', 'edd' ), 'manage_shop_discounts', 'edd-discounts', 'edd_discounts_page' );
	$edd_reports_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Earnings and Sales Reports', 'edd' ), __( 'Reports', 'edd' ), 'view_shop_reports', 'edd-reports', 'edd_reports_page' );
	$edd_settings_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Settings', 'edd' ), __( 'Settings', 'edd' ), 'manage_shop_settings', 'edd-settings', 'edd_options_page' );
	$edd_system_info_page 	= add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download System Info', 'edd' ), __( 'System Info', 'edd' ), 'install_plugins', 'edd-system-info', 'edd_system_info' );
	$edd_add_ons_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Add Ons', 'edd' ), __( 'Add Ons', 'edd' ), 'install_plugins', 'edd-addons', 'edd_add_ons_page' );
	$edd_settings_export    = add_management_page( __( 'EDD Settings Export / Import', 'edd' ), __( 'Export / Import EDD', 'edd' ), 'manage_shop_settings', 'edd-settings-export-import', 'edd_export_import' );
	$edd_upgrades_screen    = add_submenu_page( null, __( 'EDD Upgrades', 'edd' ), __( 'EDD Upgrades', 'edd' ), 'install_plugins', 'edd-upgrades', 'edd_upgrades_screen' );
}
add_action( 'admin_menu', 'edd_add_options_link', 10 );
