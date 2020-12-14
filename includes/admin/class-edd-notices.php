<?php
/**
 * Admin Notices Class
 *
 * @package     EDD
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Notices Class
 *
 * @since 2.3
 */
class EDD_Notices {

	/**
	 * @var array Array of notices to output to the current user
	 */
	private $notices = array();

	/**
	 * Get things started
	 *
	 * @since 2.3
	 */
	public function __construct() {
		add_action( 'edd_dismiss_notices', array( $this, 'dismiss_notices' )     );
		add_action( 'admin_init',          array( $this, 'add_notices'     ), 20 );
		add_action( 'admin_notices',       array( $this, 'display_notices' ), 30 );
	}

	/**
	 * Add a notice to the notices array
	 *
	 * @since 3.0
	 *
	 * @param string          $id             Unique ID for each message
	 * @param string|WP_Error $message        A message to be displayed or {@link WP_Error}
	 * @param string          $class          Optional. A class to be added to the message div
	 * @param bool            $is_dismissible Optional. True to dismiss, false to persist
	 *
	 * @return void
	 */
	public function add_notice( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'id'             => '',
			'message'        => '',
			'class'          => false,
			'is_dismissible' => true
		) );

		$default_class ='updated';

		// One message as string
		if ( is_string( $r['message'] ) ) {
			$message       = '<p>' . $this->esc_notice( $r['message'] ) . '</p>';

		} elseif ( is_array( $r['message'] ) ) {
			$message       = '<p>' . implode( '</p><p>', array_map( array( $this, 'esc_notice' ), $r['message'] ) ) . '</p>';

			// Messages as objects
		} elseif ( is_wp_error( $r['message'] ) ) {
			$default_class = 'is-error';
			$errors        = $r['message']->get_error_messages();

			switch ( count( $errors ) ) {
				case 0:
					return false;

				case 1:
					$message = '<p>' . $this->esc_notice( $errors[0] ) . '</p>';
					break;

				default:
					$escaped = array_map( array( $this, 'esc_notice' ), $errors );
					$message = '<ul>' . "\n\t" . '<li>' . implode( '</li>' . "\n\t" . '<li>', $escaped ) . '</li>' . "\n" . '</ul>';
					break;
			}

			// Message is an unknown format, so bail
		} else {
			return false;
		}

		// CSS Classes
		$classes = ! empty( $r['class'] )
			? array( $r['class'] )
			: array( $default_class );

		// Add dismissible class
		if ( ! empty( $r['is_dismissible'] ) ) {
			array_push( $classes, 'is-dismissible' );
		}

		// Assemble the message
		$message = '<div class="notice ' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '">' . $message . '</div>';
		$message = str_replace( "'", "\'", $message );

		// Avoid malformed notices variable
		if ( ! is_array( $this->notices ) ) {
			$this->notices = array();
		}

		// Add notice to notices array
		$this->notices[] = $message;
	}

	/**
	 * Add all admin area notices
	 *
	 * @since 3.0
	 */
	public function add_notices() {

		// User can edit pages
		if ( current_user_can( 'edit_pages' ) ) {
			$this->add_page_notices();
		}

		// User can view shop reports
		if ( current_user_can( 'view_shop_reports' ) ) {
			$this->add_reports_notices();
		}

		// User can manage the entire shop
		if ( current_user_can( 'manage_shop_settings' ) ) {
			$this->add_system_notices();
			$this->add_data_notices();
			$this->add_tax_rate_notice();
			$this->add_settings_notices();
		}

		// Generic notices
		if ( ! empty( $_REQUEST['edd-message'] ) ) {
			$this->add_user_action_notices( $_REQUEST['edd-message'] );
		}
	}

	/**
	 * Dismiss admin notices when dismiss links are clicked
	 *
	 * @since 2.3
	 */
	public function dismiss_notices() {

		// Bail if no notices to dismiss
		if ( empty( $_GET['edd_notice'] ) || empty( $_GET['_wpnonce'] ) ) {
			return;
		}

		// Construct key we are dismissing
		$key = sanitize_key( $_GET['edd_notice'] );

		// Bail if sanitized notice is empty
		if ( empty( $key ) ) {
			return;
		}

		// Bail if nonce does not verify
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_notice_nonce' ) ) {
			return;
		}

		// Dismiss notice
		edd_update_option( $key, 1 );
		edd_redirect( remove_query_arg( array( 'edd_action', 'edd_notice', '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Output all admin area notices
	 *
	 * @since 2.6.0 bbPress (r6771)
	 */
	public function display_notices() {

		// Bail if no notices
		if ( empty( $this->notices ) || ! is_array( $this->notices ) ) {
			return;
		}

		// Start an output buffer
		ob_start();

		// Loop through notices, and add them to buffer
		foreach ( $this->notices as $notice ) {
			echo $notice;
		}

		// Output the current buffer
		$notices = ob_get_clean();

		// Only echo if not empty
		if ( ! empty( $notices ) ) {
			echo $notices;
		}
	}

	/** Private Methods *******************************************************/

	/**
	 * Notices about missing pages
	 *
	 * @since 3.0
	 */
	private function add_page_notices() {

		// Checkout page is missing
		$purchase_page = edd_get_option( 'purchase_page', '' );
		if ( empty( $purchase_page ) || ( 'trash' === get_post_status( $purchase_page ) ) && ! edd_get_option( 'dismissed_notice_checkout' ) ) {
			$this->add_notice( array(
				'id'             => 'edd-no-purchase-page',
				'message'        => sprintf( __( 'No checkout page is configured. Set one in <a href="%s">Settings</a>.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=general&section=pages' ) ),
				'class'          => 'error',
				'is_dismissible' => false
			) );
		}
	}

	/**
	 * Notices about reports
	 *
	 * @since 3.0
	 */
	private function add_reports_notices() {

	}

	/**
	 * Notices for the entire shop
	 *
	 * @since 3.0
	 */
	private function add_system_notices() {

		// Bail if not an EDD admin page
		if ( ! edd_is_admin_page() || edd_is_dev_environment() || edd_is_admin_page( 'index.php' ) ) {
			return;
		}

		// Bail if user cannot manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Bail if uploads directory is protected
		if ( edd_is_uploads_url_protected() ) {
			return;
		}

		// Bail if already permanently dismissed
		if ( edd_get_option( 'dismissed_notice_uploads', false ) ) {
			return;
		}

		// Get the upload directory
		$upload_directory   = edd_get_upload_dir();
		$dismiss_notice_url = wp_nonce_url( add_query_arg( array(
			'edd_action' => 'dismiss_notices',
			'edd_notice' => 'dismissed_notice_uploads'
		) ), 'edd_notice_nonce' );

		// Running NGINX
		$show_nginx_notice = apply_filters( 'edd_show_nginx_redirect_notice', true );
		if ( $show_nginx_notice && ! empty( $GLOBALS['is_nginx'] ) ) {
			$this->add_notice( array(
				'id'             => 'edd-nginx',
				'class'          => 'error',
				'is_dismissible' => false,
				'message'        => array(
					sprintf( __( 'The files in %s are not currently protected.', 'easy-digital-downloads' ), '<code>' . $upload_directory . '</code>' ),
					__( 'To protect them, you must add this <a href="http://docs.easydigitaldownloads.com/article/682-protected-download-files-on-nginx">NGINX redirect rule</a>.', 'easy-digital-downloads' ),
					sprintf( __( 'If you have already done this, or it does not apply to your site, you may permenently %s.', 'easy-digital-downloads' ), '<a href="' . esc_url( $dismiss_notice_url ) . '">' . __( 'dismiss this notice', 'easy-digital-downloads' ) . '</a>' )
				)
			) );
		}

		// Running Apache
		if ( ! empty( $GLOBALS['is_apache'] ) && ! edd_htaccess_exists() ) {
			$this->add_notice( array(
				'id'             => 'edd-apache',
				'class'          => 'error',
				'is_dismissible' => false,
				'message'        => array(
					sprintf( __( 'The .htaccess file is missing from: %s', 'easy-digital-downloads' ), '<strong>' . $upload_directory . '</strong>' ),
					sprintf( __( 'First, please resave the Misc settings tab a few times. If this warning continues to appear, create a file called ".htaccess" in the %s directory, and copy the following into it:', 'easy-digital-downloads' ), '<strong>' . $upload_directory . '</strong>' ),
					sprintf( __( 'If you have already done this, or it does not apply to your site, you may permenently %s.', 'easy-digital-downloads' ), '<a href="' . esc_url( $dismiss_notice_url ) . '">' . __( 'dismiss this notice', 'easy-digital-downloads' ) . '</a>' ),
					'<pre>' . edd_get_htaccess_rules() . '</pre>'
				)
			) );
		}
	}

	/**
	 * Notices about data (migrations, etc...)
	 *
	 * @since 3.0
	 */
	private function add_data_notices() {

		// Recount earnings
		if ( class_exists( 'EDD_Recount_Earnings' ) ) {
			$this->add_notice( array(
				'id'             => 'edd-recount-earnings',
				'class'          => 'error',
				'is_dismissible' => false,
				'message'        => sprintf( __( 'Easy Digital Downloads 2.5 contains a <a href="%s">built in recount tool</a>. Please <a href="%s">deactivate the Easy Digital Downloads - Recount Earnings plugin</a>', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-tools&tab=general' ), admin_url( 'plugins.php' ) )
			) );
		}
	}

	/**
	 * Adds a notice about the deprecated Default Rate for Taxes.
	 *
	 * @since 3.0
	 */
	private function add_tax_rate_notice() {

		// Default tax rate not detected.
		if ( false === edd_get_option( 'tax_rate' ) ) {
			return;
		}

		// On Rates page, settings notice is shown.
		if ( ! empty( $_GET['page'] ) && 'edd-settings' === $_GET['page'] && ! empty( $_GET['section'] ) && 'rates' === $_GET['section'] ) {
			return;
		}

		// URL to fix this
		$url = edd_get_admin_url( array(
			'page'      => 'edd-settings',
			'tab'       => 'taxes',
			'section'   => 'rates'
		) );

		// Link
		$link = '<a href="' . esc_url( $url ) . '" class="button button-secondary">' . __( 'Review Tax Rates', 'easy-digital-downloads' ) . '</a>';

		// Add the notice
		$this->add_notice( array(
			'id'             => 'edd-default-tax-rate',
			'class'          => 'error',
			/* translators: Link to review existing tax rates. */
			'message'        => '<strong>' . __( 'A default tax rate was detected.', 'easy-digital-downloads' ) . '</strong></p><p>' . __( 'This setting is no longer used in this version of Easy Digital Downloads. Please confirm your regional tax rates are properly configured and update tax settings to remove this notice.', 'easy-digital-downloads' ) . '</p><p>' . $link,
			'is_dismissible' => false
		) );
	}

	/**
	 * Notices about settings (updating, etc...)
	 *
	 * @since 3.0
	 */
	private function add_settings_notices() {

		// Settings area
		if ( ! empty( $_GET['page'] ) && ( 'edd-settings' === $_GET['page'] ) ) {

			// Settings updated
			if ( ! empty( $_GET['settings-updated'] ) ) {
				$this->add_notice( array(
					'id'      => 'edd-notices',
					'message' => __( 'Settings updated.', 'easy-digital-downloads' )
				) );
			}

			// No payment gateways are enabled
			if ( ! edd_get_option( 'gateways' ) && edd_is_test_mode() ) {

				// URL to fix this
				$url = add_query_arg( array(
					'post_type' => 'download',
					'page'      => 'edd-settings',
					'tab'       => 'gateways'
				) );

				// Link
				$link = '<a href="' . esc_url( $url ) . '">' . __( 'Fix this', 'easy-digital-downloads' ) . '</a>';

				// Add the notice
				$this->add_notice( array(
					'id'             => 'edd-gateways',
					'class'          => 'error',
					'message'        => sprintf( __( 'No payment gateways are enabled. %s.', 'easy-digital-downloads' ), $link ),
					'is_dismissible' => false
				) );
			}
		}
	}

	/**
	 * Notices about actions that the user has taken
	 *
	 * @since 3.0
	 *
	 * @param string $notice
	 */
	private function add_user_action_notices( $notice = '' ) {

		// Sanitize notice key
		$notice = sanitize_key( $notice );

		// Bail if notice is empty
		if ( empty( $notice ) ) {
			return;
		}

		// Shop discounts errors
		if ( current_user_can( 'manage_shop_discounts' ) ) {
			switch ( $notice ) {
				case 'discount_added' :
					$this->add_notice( array(
						'id'      => 'edd-discount-added',
						'message' => __( 'Discount code added.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_add_failed' :
					$this->add_notice( array(
						'id'      => 'edd-discount-add-fail',
						'message' => __( 'There was a problem adding that discount code, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_exists' :
					$this->add_notice( array(
						'id'      => 'edd-discount-exists',
						'message' => __( 'A discount with that code already exists, please use a different code.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_updated' :
					$this->add_notice( array(
						'id'      => 'edd-discount-updated',
						'message' => __( 'Discount code updated.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_not_changed' :
					$this->add_notice( array(
						'id'      => 'edd-discount-not-changed',
						'message' => __( 'No changes were made to that discount code.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_update_failed' :
					$this->add_notice( array(
						'id'      => 'edd-discount-updated-fail',
						'message' => __( 'There was a problem updating that discount code, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_validation_failed' :
					$this->add_notice( array(
						'id'      => 'edd-discount-validation-fail',
						'message' => __( 'The discount code could not be added because one or more of the required fields was empty, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_invalid_code':
					$this->add_notice( array(
						'id'      => 'edd-discount-invalid-code',
						'message' => __( 'The discount code entered is invalid; only alphanumeric characters are allowed, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_deleted':
					$this->add_notice( array(
						'id'      => 'edd-discount-deleted',
						'message' => __( 'Discount code deleted.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_delete_failed':
					$this->add_notice( array(
						'id'      => 'edd-discount-delete-fail',
						'message' => __( 'There was a problem deleting that discount code, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_activated':
					$this->add_notice( array(
						'id'      => 'edd-discount-activated',
						'message' => __( 'Discount code activated.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_activation_failed':
					$this->add_notice( array(
						'id'      => 'edd-discount-activation-fail',
						'message' => __( 'There was a problem activating that discount code, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'discount_deactivated':
					$this->add_notice( array(
						'id'      => 'edd-discount-deactivated',
						'message' => __( 'Discount code deactivated.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_deactivation_failed':
					$this->add_notice( array(
						'id'      => 'edd-discount-deactivation-fail',
						'message' => __( 'There was a problem deactivating that discount code, please try again.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
			}
		}

		// Shop reports errors
		if ( current_user_can( 'view_shop_reports' ) ) {
			switch( $notice ) {
				case 'refreshed-reports' :
					$this->add_notice( array(
						'id'      => 'edd-refreshed-reports',
						'message' => __( 'The reports have been refreshed.', 'easy-digital-downloads' )
					) );
					break;
			}
		}

		// Shop settings errors
		if ( current_user_can( 'manage_shop_settings' ) ) {
			switch( $notice ) {
				case 'settings-imported' :
					$this->add_notice( array(
						'id'      => 'edd-settings-imported',
						'message' => __( 'The settings have been imported.', 'easy-digital-downloads' )
					) );
					break;
				case 'api-key-generated' :
					$this->add_notice( array(
						'id'      => 'edd-api-key-generated',
						'message' => __( 'API keys successfully generated.', 'easy-digital-downloads' )
					) );
					break;
				case 'api-key-exists' :
					$this->add_notice( array(
						'id'      => 'edd-api-key-exists',
						'message' => __( 'The specified user already has API keys.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'api-key-regenerated' :
					$this->add_notice( array(
						'id'      => 'edd-api-key-regenerated',
						'message' => __( 'API keys successfully regenerated.', 'easy-digital-downloads' )
					) );
					break;
				case 'api-key-revoked' :
					$this->add_notice( array(
						'id'      => 'edd-api-key-revoked',
						'message' => __( 'API keys successfully revoked.', 'easy-digital-downloads' )
					) );
					break;
				case 'sendwp-connected' :
					$this->add_notice( array(
						'id'      => 'edd-sendwp-connected',
						'message' => __( 'SendWP has been successfully connected!', 'easy-digital-downloads' )
					) );
			}
		}

		// Shop payments errors
		if ( current_user_can( 'edit_shop_payments' ) ) {
			switch( $notice ) {
				case 'note-added' :
					$this->add_notice( array(
						'id'      => 'edd-note-added',
						'message' => __( 'The note has been added successfully.', 'easy-digital-downloads' )
					) );
					break;
				case 'payment-updated' :
					$this->add_notice( array(
						'id'      => 'edd-payment-updated',
						'message' => __( 'The order has been updated successfully.', 'easy-digital-downloads' )
					) );
					break;
				case 'order_added' :
					$this->add_notice( array(
						'id'      => 'edd-order-added',
						'message' => __( 'Order successfully created.', 'easy-digital-downloads' )
					) );
					break;
				case 'order_trashed' :
					$this->add_notice( array(
						'id'      => 'edd-order-trashed',
						'message' => __( 'The order has been moved to the trash.', 'easy-digital-downloads' )
					) );
					break;
				case 'order_restored' :
					$this->add_notice( array(
						'id'      => 'edd-order-restored',
						'message' => __( 'The order has been restored.', 'easy-digital-downloads' )
					) );
					break;
				case 'payment_deleted' :
					$this->add_notice( array(
						'id'      => 'edd-payment-deleted',
						'message' => __( 'The payment has been deleted.', 'easy-digital-downloads' )
					) );
					break;
				case 'email_sent' :
					$this->add_notice( array(
						'id'      => 'edd-payment-sent',
						'message' => __( 'The purchase receipt has been resent.', 'easy-digital-downloads' )
					) );
					break;
				case 'refreshed-reports' :
					$this->add_notice( array(
						'id'      => 'edd-refreshed-reports',
						'message' => __( 'The reports have been refreshed.', 'easy-digital-downloads' )
					) );
					break;
				case 'payment-note-deleted' :
					$this->add_notice( array(
						'id'      => 'edd-note-deleted',
						'message' => __( 'The payment note has been deleted.', 'easy-digital-downloads' )
					) );
					break;
			}
		}

		// Customer Notices
		if ( current_user_can( 'edit_shop_payments' ) ) {
			switch( $notice ) {
				case 'customer-deleted' :
					$this->add_notice( array(
						'id'      => 'edd-customer-deleted',
						'message' => __( 'Customer successfully deleted.', 'easy-digital-downloads' ),
					) );
					break;
				case 'user-verified' :
					$this->add_notice( array(
						'id'      => 'edd-user-verified',
						'message' => __( 'User successfully verified.', 'easy-digital-downloads' ),
					) );
					break;
				case 'email-added' :
					$this->add_notice( array(
						'id'      => 'edd-customer-email-added',
						'message' => __( 'Customer email added.', 'easy-digital-downloads' ),
					) );
					break;
				case 'email-removed' :
					$this->add_notice( array(
						'id'      => 'edd-customer-email-removed',
						'message' => __( 'Customer email deleted.', 'easy-digital-downloads' ),
					) );
					break;
				case 'email-remove-failed' :
					$this->add_notice( array(
						'id'      => 'edd-customer-email-remove-failed',
						'message' => __( 'Failed to delete customer email.', 'easy-digital-downloads' ),
						'class'   => 'error',
					) );
					break;
				case 'primary-email-updated' :
					$this->add_notice( array(
						'id'      => 'eddedd-customer-primary-email-updated',
						'message' => __( 'Primary email updated for customer.', 'easy-digital-downloads' )
					) );
					break;
				case 'primary-email-failed' :
					$this->add_notice( array(
						'id'      => 'edd-customer-primary-email-failed',
						'message' => __( 'Failed to set primary email.', 'easy-digital-downloads' ),
						'class'   => 'error',
					) );
					break;
				case 'address-removed' :
					$this->add_notice( array(
						'id'      => 'edd-customer-address-removed',
						'message' => __( 'Customer address deleted.', 'easy-digital-downloads' )
					) );
					break;
				case 'address-remove-failed' :
					$this->add_notice( array(
						'id'      => 'edd-customer-address-remove-failed',
						'message' => __( 'Failed to delete customer address.', 'easy-digital-downloads' ),
						'class'   => 'error',
					) );
					break;
			}
		}
	}

	/**
	 * Escape message string output
	 *
	 * @since 2.6.0 bbPress (r6775)
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	private function esc_notice( $message = '' ) {
		$tags = wp_kses_allowed_html( 'post' );
		$text = wp_kses( $message, $tags );

		return $text;
	}
}
