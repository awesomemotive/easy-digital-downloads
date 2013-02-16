<?php
/**
 * Admin Notices
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Notices
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Admin Messages
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_admin_messages() {
	global $typenow, $edd_options;

	if ( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'discount_updated' && current_user_can( 'manage_shop_discounts' ) ) {
		 add_settings_error( 'edd-notices', 'edd-discount-updated', __( 'Discount code updated.', 'edd' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'discount_update_failed' && current_user_can( 'manage_shop_discounts' ) ) {
		add_settings_error( 'edd-notices', 'edd-discount-updated-fail', __( 'There was a problem updating your discount code, please try again.', 'edd' ), 'error' );
	}

	if ( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'payment_deleted' && current_user_can( 'view_shop_reports' ) ) {
		add_settings_error( 'edd-notices', 'edd-payment-deleted', __( 'The payment has been deleted.', 'edd' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'email_sent' && current_user_can( 'view_shop_reports' ) ) {
		add_settings_error( 'edd-notices', 'edd-payment-sent', __( 'The purchase receipt has been resent.', 'edd' ), 'updated' );
	}

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'edd-payment-history' && current_user_can( 'view_shop_reports' ) && edd_is_test_mode() ) {
		add_settings_error( 'edd-notices', 'edd-payment-sent', sprintf( __( 'Note: Test Mode is enabled, only test payments are shown below. %sSettings%s.', 'edd' ), '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings' ) . '">', '</a>' ), 'updated' );
	}

	if ( ( empty( $edd_options['purchase_page'] ) || 'trash' == get_post_status( $edd_options['purchase_page'] ) ) && current_user_can( 'edit_pages' ) ) {
		add_settings_error( 'edd-notices', 'set-checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) );
	}

	settings_errors( 'edd-notices' );
}
add_action( 'admin_notices', 'edd_admin_messages' );


/**
 * Admin Addons Notices
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_admin_addons_notices() {
	add_settings_error( 'edd-notices', 'edd-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'edd' ), 'error' );
	settings_errors( 'edd-notices' );
}