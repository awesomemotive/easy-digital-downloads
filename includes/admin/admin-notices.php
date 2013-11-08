<?php
/**
 * Admin Notices
 *
 * @package     EDD
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_admin_messages() {
	global $edd_options;

	if ( isset( $_GET['edd-message'] ) && 'discount_added' == $_GET['edd-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		 add_settings_error( 'edd-notices', 'edd-discount-added', __( 'Discount code added.', 'edd' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && 'discount_add_failed' == $_GET['edd-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		add_settings_error( 'edd-notices', 'edd-discount-add-fail', __( 'There was a problem adding your discount code, please try again.', 'edd' ), 'error' );
	}

	if ( isset( $_GET['edd-message'] ) && 'discount_updated' == $_GET['edd-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		 add_settings_error( 'edd-notices', 'edd-discount-updated', __( 'Discount code updated.', 'edd' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && 'discount_update_failed' == $_GET['edd-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		add_settings_error( 'edd-notices', 'edd-discount-updated-fail', __( 'There was a problem updating your discount code, please try again.', 'edd' ), 'error' );
	}

	if ( isset( $_GET['edd-message'] ) && 'payment_deleted' == $_GET['edd-message'] && current_user_can( 'view_shop_reports' ) ) {
		add_settings_error( 'edd-notices', 'edd-payment-deleted', __( 'The payment has been deleted.', 'edd' ), 'updated' );
	}

	if ( isset( $_GET['edd-message'] ) && 'email_sent' == $_GET['edd-message'] && current_user_can( 'view_shop_reports' ) ) {
		add_settings_error( 'edd-notices', 'edd-payment-sent', __( 'The purchase receipt has been resent.', 'edd' ), 'updated' );
    }

    if ( isset( $_GET['edd-message'] ) && 'payment_note_deleted' == $_GET['edd-message'] && current_user_can( 'view_shop_reports' ) ) {
        add_settings_error( 'edd-notices', 'edd-payment-note-deleted', __( 'The payment note has been deleted.', 'edd' ), 'updated' );
    }

	if ( isset( $_GET['page'] ) && 'edd-payment-history' == $_GET['page'] && current_user_can( 'view_shop_reports' ) && edd_is_test_mode() ) {
		add_settings_error( 'edd-notices', 'edd-payment-sent', sprintf( __( 'Note: Test Mode is enabled, only test payments are shown below. %sSettings%s.', 'edd' ), '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings' ) . '">', '</a>' ), 'updated' );
	}

	if ( ( empty( $edd_options['purchase_page'] ) || 'trash' == get_post_status( $edd_options['purchase_page'] ) ) && current_user_can( 'edit_pages' ) ) {
		add_settings_error( 'edd-notices', 'set-checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) );
	}

	if ( isset( $_GET['edd-message'] ) && 'settings-imported' == $_GET['edd-message'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'edd-notices', 'edd-settings-imported', __( 'The settings have been imported.', 'edd' ), 'updated' );
	}

    if( ! edd_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_edd_htaccess_missing_dismissed', true ) ) {
        if( ! stristr( $_SERVER['SERVER_SOFTWARE'], 'apache' ) )
            return; // Bail if we aren't using Apache... nginx doesn't use htaccess!

		echo '<div class="error">';
			echo '<p>' . sprintf( __( 'The Easy Digital Downloads .htaccess file is missing from <strong>%s</strong>!', 'edd' ), edd_get_upload_dir() ) . '</p>';
			echo '<p>' . sprintf( __( 'First, please resave the Misc settings tab. If this warning continues to appear, create a file called ".htaccess" in the <strong>%s</strong> directory, and copy the following into it:', 'edd' ), edd_get_upload_dir() ) . '</p>';
			echo '<p><pre>' . edd_get_htaccess_rules() . '</pre>';
			echo '<p><a href="' . add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'htaccess_missing' ) ) . '">' . __( 'Dismiss Notice', 'edd' ) . '</a></p>';
		echo '</div>';
	}

	settings_errors( 'edd-notices' );
}
add_action( 'admin_notices', 'edd_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since 1.0
 * @return void
*/
function edd_admin_addons_notices() {
	add_settings_error( 'edd-notices', 'edd-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'edd' ), 'error' );
	settings_errors( 'edd-notices' );
}

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.8
 * @return void
*/
function edd_dismiss_notices() {

	$notice = isset( $_GET['edd_notice'] ) ? $_GET['edd_notice'] : false;

	if( ! $notice )
		return; // No notice, so get out of here

	update_user_meta( get_current_user_id(), '_edd_' . $notice . '_dismissed', 1 );

	wp_redirect( remove_query_arg( array( 'edd_action', 'edd_notice' ) ) ); exit;

}
add_action( 'edd_dismiss_notices', 'edd_dismiss_notices' );
