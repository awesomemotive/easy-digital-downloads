<?php
/**
 * Admin Notices Class
 *
 * @package     EDD
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Notices Class
 *
 * @since 2.3
 */
class EDD_Notices {

	/**
	 * Get things started
	 *
	 * @since 2.3
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'edd_dismiss_notices', array( $this, 'dismiss_notices' ) );
	}

	/**
	 * Show relevant notices
	 *
	 * @since 2.3
	 */
	public function show_notices() {
		$notices = array(
			'updated'	=> array(),
			'error'		=> array()
		);

		// Global (non-action-based) messages
		if ( edd_get_option( 'purchase_page', '' ) == '' || 'trash' == get_post_status( edd_get_option( 'purchase_page', '' ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_edd_set_checkout_dismissed' ) ) {
			echo '<div class="error">';
				echo '<p>' . sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) . '</p>';
				echo '<p><a href="' . add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'set_checkout' ) ) . '">' . __( 'Dismiss Notice', 'easy-digital-downloads' ) . '</a></p>';
			echo '</div>';
		}

		if ( isset( $_GET['page'] ) && 'edd-payment-history' == $_GET['page'] && current_user_can( 'view_shop_reports' ) && edd_is_test_mode() ) {
			$notices['updated']['edd-payment-history-test-mode'] = sprintf( __( 'Note: Test Mode is enabled, only test payments are shown below. <a href="%s">Settings</a>.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) );
		}

		if( stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) && ! get_user_meta( get_current_user_id(), '_edd_nginx_redirect_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {

			echo '<div class="error">';
				echo '<p>' . sprintf( __( 'The download files in <strong>%s</strong> are not currently protected due to your site running on NGINX.', 'easy-digital-downloads' ), edd_get_upload_dir() ) . '</p>';
				echo '<p>' . __( 'To protect them, you must add a redirect rule as explained in <a href="http://docs.easydigitaldownloads.com/article/682-protected-download-files-on-nginx">this guide</a>.', 'easy-digital-downloads' ) . '</p>';
				echo '<p>' . __( 'If you have already added the redirect rule, you may safely dismiss this notice', 'easy-digital-downloads' ) . '</p>';
				echo '<p><a href="' . add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'nginx_redirect' ) ) . '">' . __( 'Dismiss Notice', 'easy-digital-downloads' ) . '</a></p>';
			echo '</div>';

		}

		if( ! edd_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_edd_htaccess_missing_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			if( ! stristr( $_SERVER['SERVER_SOFTWARE'], 'apache' ) )
				return; // Bail if we aren't using Apache... nginx doesn't use htaccess!

			echo '<div class="error">';
				echo '<p>' . sprintf( __( 'The Easy Digital Downloads .htaccess file is missing from <strong>%s</strong>!', 'easy-digital-downloads' ), edd_get_upload_dir() ) . '</p>';
				echo '<p>' . sprintf( __( 'First, please resave the Misc settings tab a few times. If this warning continues to appear, create a file called ".htaccess" in the <strong>%s</strong> directory, and copy the following into it:', 'easy-digital-downloads' ), edd_get_upload_dir() ) . '</p>';
				echo '<p><pre>' . edd_get_htaccess_rules() . '</pre>';
				echo '<p><a href="' . add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'htaccess_missing' ) ) . '">' . __( 'Dismiss Notice', 'easy-digital-downloads' ) . '</a></p>';
			echo '</div>';
		}

		/* Commented out per https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/3475
		if( ! edd_test_ajax_works() && ! get_user_meta( get_current_user_id(), '_edd_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			echo '<div class="error">';
				echo '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may causes issues with your store.', 'easy-digital-downloads' ) . '</p>';
				echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'easy-digital-downloads' ), 'https://easydigitaldownloads.com/docs/admin-ajax-blocked' ) . '</p>';
				echo '<p><a href="' . add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'admin_ajax_inaccessible' ) ) . '">' . __( 'Dismiss Notice', 'easy-digital-downloads' ) . '</a></p>';
			echo '</div>';
		}
		*/

		if ( isset( $_GET['edd-message'] ) ) {
			// Shop discounts errors
			if( current_user_can( 'manage_shop_discounts' ) ) {
				switch( $_GET['edd-message'] ) {
					case 'discount_added' :
						$notices['updated']['edd-discount-added'] = __( 'Discount code added.', 'easy-digital-downloads' );
						break;
					case 'discount_add_failed' :
						$notices['error']['edd-discount-add-fail'] = __( 'There was a problem adding your discount code, please try again.', 'easy-digital-downloads' );
						break;
					case 'discount_exists' :
						$notices['error']['edd-discount-exists'] = __( 'A discount with that code already exists, please use a different code.', 'easy-digital-downloads' );
						break;
					case 'discount_updated' :
						$notices['updated']['edd-discount-updated'] = __( 'Discount code updated.', 'easy-digital-downloads' );
						break;
					case 'discount_update_failed' :
						$notices['error']['edd-discount-updated-fail'] = __( 'There was a problem updating your discount code, please try again.', 'easy-digital-downloads' );
						break;
				}
			}

			// Shop reports errors
			if( current_user_can( 'view_shop_reports' ) ) {
				switch( $_GET['edd-message'] ) {
					case 'payment_deleted' :
						$notices['updated']['edd-payment-deleted'] = __( 'The payment has been deleted.', 'easy-digital-downloads' );
						break;
					case 'email_sent' :
						$notices['updated']['edd-payment-sent'] = __( 'The purchase receipt has been resent.', 'easy-digital-downloads' );
						break;
					case 'payment-note-deleted' :
						$notices['updated']['edd-payment-note-deleted'] = __( 'The payment note has been deleted.', 'easy-digital-downloads' );
						break;
				}
			}

			// Shop settings errors
			if( current_user_can( 'manage_shop_settings' ) ) {
				switch( $_GET['edd-message'] ) {
					case 'settings-imported' :
						$notices['updated']['edd-settings-imported'] = __( 'The settings have been imported.', 'easy-digital-downloads' );
						break;
					case 'api-key-generated' :
						$notices['updated']['edd-api-key-generated'] = __( 'API keys successfully generated.', 'easy-digital-downloads' );
						break;
					case 'api-key-exists' :
						$notices['error']['edd-api-key-exists'] = __( 'The specified user already has API keys.', 'easy-digital-downloads' );
						break;
					case 'api-key-regenerated' :
						$notices['updated']['edd-api-key-regenerated'] = __( 'API keys successfully regenerated.', 'easy-digital-downloads' );
						break;
					case 'api-key-revoked' :
						$notices['updated']['edd-api-key-revoked'] = __( 'API keys successfully revoked.', 'easy-digital-downloads' );
						break;
				}
			}

			// Shop payments errors
			if( current_user_can( 'edit_shop_payments' ) ) {
				switch( $_GET['edd-message'] ) {
					case 'note-added' :
						$notices['updated']['edd-note-added'] = __( 'The payment note has been added successfully.', 'easy-digital-downloads' );
						break;
					case 'payment-updated' :
						$notices['updated']['edd-payment-updated'] = __( 'The payment has been successfully updated.', 'easy-digital-downloads' );
						break;
				}
			}

			// Customer Notices
			if ( current_user_can( 'edit_shop_payments' ) ) {
				switch( $_GET['edd-message'] ) {
					case 'customer-deleted' :
						$notices['updated']['edd-customer-deleted'] = __( 'Customer successfully deleted', 'easy-digital-downloads' );
						break;
					case 'user-verified' :
						$notices['updated']['edd-user-verified'] = __( 'User successfully verified', 'easy-digital-downloads' );
						break;
				}
			}

		}

		if ( count( $notices['updated'] ) > 0 ) {
			foreach( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'edd-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach( $notices['error'] as $notice => $message ) {
				add_settings_error( 'edd-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'edd-notices' );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @since 2.3
	 * @return void
	 */
	function dismiss_notices() {
		if( isset( $_GET['edd_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_edd_' . $_GET['edd_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'edd_action', 'edd_notice' ) ) );
			exit;
		}
	}
}
new EDD_Notices;
