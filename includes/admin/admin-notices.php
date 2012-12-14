<?php
/**
 * Admin Notices
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Notices
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_admin_messages() {
	global $typenow;

	if( 'download' != $typenow )
		return;

	$edd_access_level = edd_get_menu_access_level();
	if( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'discount_updated' && current_user_can( $edd_access_level ) ) {
		 add_settings_error( 'edd-notices', 'edd-discount-updated', __( 'Discount code updated.', 'edd' ), 'updated' );
	}
	if( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'discount_update_failed' && current_user_can( $edd_access_level ) ) {
		add_settings_error( 'edd-notices', 'edd-discount-updated-fail', __( 'There was a problem updating your discount code, please try again.', 'edd' ), 'error' );
	}
	if( isset( $_GET['edd-message'] ) && $_GET['edd-message'] == 'payment_deleted' && current_user_can( $edd_access_level ) ) {
		add_settings_error( 'edd-notices', 'edd-payment-deleted', __( 'The payment has been deleted.', 'edd' ), 'updated' );
	}
	if( isset( $_GET['edd-message']) && $_GET['edd-message'] == 'email_sent' && current_user_can( $edd_access_level ) ) {
		add_settings_error( 'edd-notices', 'edd-payment-sent', __( 'The purchase receipt has been resent.', 'edd' ), 'updated' );
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
