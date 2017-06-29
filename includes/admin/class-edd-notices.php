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
			'updated' => array(),
			'error'   => array(),
		);

		// Global (non-action-based) messages
		if ( ( edd_get_option( 'purchase_page', '' ) == '' || 'trash' == get_post_status( edd_get_option( 'purchase_page', '' ) ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_edd_set_checkout_dismissed' ) ) {
			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ); ?></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'set_checkout' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'easy-digital-downloads' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if ( isset( $_GET['page'] ) && 'edd-payment-history' == $_GET['page'] && current_user_can( 'view_shop_reports' ) && edd_is_test_mode() ) {
			$notices['updated']['edd-payment-history-test-mode'] = sprintf( __( 'Note: Test Mode is enabled. While in test mode no live transactions are processed. <a href="%s">Settings</a>.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways' ) );
		}

		if( stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) && ! get_user_meta( get_current_user_id(), '_edd_nginx_redirect_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'The download files in %s are not currently protected due to your site running on NGINX.', 'easy-digital-downloads' ), '<strong>' . edd_get_upload_dir() . '</strong>' ); ?></p>
				<p><?php _e( 'To protect them, you must add a redirect rule as explained in <a href="http://docs.easydigitaldownloads.com/article/682-protected-download-files-on-nginx">this guide</a>.', 'easy-digital-downloads' ); ?></p>
				<p><?php _e( 'If you have already added the redirect rule, you may safely dismiss this notice', 'easy-digital-downloads' ); ?></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'nginx_redirect' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'easy-digital-downloads' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if( ! edd_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_edd_htaccess_missing_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			if( ! stristr( $_SERVER['SERVER_SOFTWARE'], 'apache' ) ) {
				return; // Bail if we aren't using Apache... nginx doesn't use htaccess!
			}

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'The Easy Digital Downloads .htaccess file is missing from %s!', 'easy-digital-downloads' ), '<strong>' . edd_get_upload_dir() . '</strong>' ); ?></p>
				<p><?php printf( __( 'First, please resave the Misc settings tab a few times. If this warning continues to appear, create a file called ".htaccess" in the %s directory, and copy the following into it:', 'easy-digital-downloads' ), '<strong>' . edd_get_upload_dir() . '</strong>' ); ?></p>
				<p><pre><?php echo edd_get_htaccess_rules(); ?></pre></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'dismiss_notices', 'edd_notice' => 'htaccess_missing' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'easy-digital-downloads' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}


		if ( class_exists( 'EDD_Recount_Earnings' ) && current_user_can( 'manage_shop_settings' ) ) {

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'Easy Digital Downloads 2.5 contains a <a href="%s">built in recount tool</a>. Please <a href="%s">deactivate the Easy Digital Downloads - Recount Earnings plugin</a>', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-tools&tab=general' ), admin_url( 'plugins.php' ) ); ?></p>
			</div>
			<?php
			echo ob_get_clean();

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
					case 'discount_validation_failed' :
						$notices['error']['edd-discount-validation-fail'] = __( 'The discount code could not be added because one or more of the required fields was empty, please try again.', 'easy-digital-downloads' );
						break;
					case 'discount_invalid_code':
						$notices['error']['edd-discount-invalid-code'] = __( 'The discount code entered is invalid; only alphanumeric characters are allowed, please try again.', 'easy-digital-downloads' );
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
					case 'refreshed-reports' :
						$notices['updated']['edd-refreshed-reports'] = __( 'The reports have been refreshed.', 'edd' );
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
					case 'email-added' :
						$notices['updated']['edd-customer-email-added'] = __( 'Customer email added', 'easy-digital-downloads' );
						break;
					case 'email-removed' :
						$notices['updated']['edd-customer-email-removed'] = __( 'Customer email removed', 'easy-digital-downloads');
						break;
					case 'email-remove-failed' :
						$notices['error']['edd-customer-email-remove-failed'] = __( 'Failed to remove customer email', 'easy-digital-downloads');
						break;
					case 'primary-email-updated' :
						$notices['updated']['edd-customer-primary-email-updated'] = __( 'Primary email updated for customer', 'easy-digital-downloads');
						break;
					case 'primary-email-failed' :
						$notices['error']['edd-customer-primary-email-failed'] = __( 'Failed to set primary email', 'easy-digital-downloads');
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
