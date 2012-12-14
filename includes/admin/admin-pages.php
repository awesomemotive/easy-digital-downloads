<?php
/**
 * Admin Pages
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Pages
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add Options Link
 *
 * Creates the admin submenu pages.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_add_options_link() {
	global $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_upgrades_screen;

	$edd_payments_page 	= add_submenu_page( 'edit.php?post_type=download', __( 'Payment History', 'edd' ), __( 'Payment History', 'edd' ), 'manage_options', 'edd-payment-history', 'edd_payment_history_page' );
	$edd_discounts_page = add_submenu_page( 'edit.php?post_type=download', __( 'Discount Codes', 'edd' ), __( 'Discount Codes', 'edd' ), 'manage_options', 'edd-discounts', 'edd_discounts_page' );
	$edd_reports_page 	= add_submenu_page( 'edit.php?post_type=download', __( 'Earnings and Sales Reports', 'edd' ), __( 'Reports', 'edd' ), 'manage_options', 'edd-reports', 'edd_reports_page' );
	$edd_settings_page 	= add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Settings', 'edd' ), __( 'Settings', 'edd' ), 'manage_options', 'edd-settings', 'edd_options_page' );
	$edd_add_ons_page 	= add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Add Ons', 'edd' ), __( 'Add Ons', 'edd' ), 'manage_options', 'edd-addons', 'edd_add_ons_page' );
	$edd_upgrades_screen= add_submenu_page( null, __( 'EDD Upgrades', 'edd' ), __( 'EDD Upgrades', 'edd' ), 'manage_options', 'edd-upgrades', 'edd_upgrades_screen' );


	add_action( 'load-' . $edd_discounts_page, 'edd_discounts_contextual_help' );
}
add_action( 'admin_menu', 'edd_add_options_link', 10 );