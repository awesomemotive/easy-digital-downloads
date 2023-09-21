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
		add_action( 'admin_notices',       array( $this, 'remove_notices' ), 0   );
		add_action( 'edd_dismiss_notices', array( $this, 'dismiss_notices' )     );
		add_action( 'admin_init',          array( $this, 'add_notices'     ), 20 );
		add_action( 'admin_notices',       array( $this, 'display_notices' ), 30 );
		add_action( 'wp_ajax_edd_disable_debugging', array( $this, 'edd_disable_debugging' ) );
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

		// Avoid malformed notices variable
		if ( ! is_array( $this->notices ) ) {
			$this->notices = array();
		}

		// Parse args
		$r = wp_parse_args( $args, array(
			'id'             => '',
			'message'        => '',
			'class'          => false,
			'is_dismissible' => true,
		) );

		// Prevent a notice from being added more than once.
		if ( ! empty( $r['id'] ) && array_key_exists( $r['id'], $this->notices ) ) {
			return;
		}

		$default_class = 'updated';

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
		$classes = array( $default_class );
		if ( ! empty( $r['class'] ) ) {
			$classes = explode( ' ', $r['class'] );
		}

		// Add dismissible class
		if ( ! empty( $r['is_dismissible'] ) ) {
			array_push( $classes, 'is-dismissible' );
		}

		// Assemble the message
		$message = '<div class="notice ' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '">' . $message . '</div>';
		$message = str_replace( "'", "\'", $message );

		// Add notice to notices array
		$this->notices[ $r['id'] ] = $message;
	}

	/**
	 * Add all admin area notices
	 *
	 * @since 3.0
	 */
	public function add_notices() {

		// User can view shop reports.
		if ( current_user_can( 'view_shop_reports' ) ) {
			$this->add_reports_notices();
		}

		// User can manage the entire shop.
		if ( current_user_can( 'manage_shop_settings' ) ) {
			$this->add_data_notices();
			$this->add_settings_notices();
			$this->add_order_upgrade_notice();
			$this->add_stripe_notice();
			$this->add_paypal_sync_notice();
		}

		// Generic notices.
		if ( ! empty( $_REQUEST['edd-message'] ) ) {
			$this->add_user_action_notices( $_REQUEST['edd-message'] );
		}

		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'edd-message' ), $_SERVER['REQUEST_URI'] );
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
		update_user_meta( get_current_user_id(), "_edd_{$key}_dismissed", 1 );
		edd_redirect( remove_query_arg( array( 'edd_action', 'edd_notice', '_wpnonce' ) ) );
	}

	/**
	 * Output all admin area notices
	 *
	 * @since 2.6.0 bbPress (r6771)
	 */
	public function display_notices() {
		$screen = get_current_screen();
		if ( 'site-health' === $screen->id ) {
			return;
		}

		$this->show_debugging_notice();

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

	/**
	 * Remove unneed admin notices from EDD admin screens.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function remove_notices() {
		if ( ! edd_is_admin_page() ) {
			return;
		}

		global $wp_filter;
		$all_hooks = $wp_filter['admin_notices']->callbacks;

		foreach ( $all_hooks as $priority => $priority_actions ) {

			$priority_functions = wp_list_pluck( $priority_actions, 'function' );

			foreach ( $priority_functions as $key => $function ) {
				if ( is_array( $function ) ) {

					if ( ! empty( $function[0] ) && is_object( $function[0] ) ) {
						$class_name = strtolower( get_class( $function[0] ) );

						if ( false === strpos( $class_name, 'edd' ) ) {
							unset( $all_hooks[ $priority ][ $key ] );
						}
					}
				} elseif ( is_string( $function ) ) {

					$function = strtolower( $function );
					if ( false === strpos( $function, 'edd' ) ) {

						unset( $all_hooks[ $priority ][ $key ] );
					}
				}
			}
		}
		$wp_filter['admin_notices']->callbacks = $all_hooks;
	}

	/** Private Methods *******************************************************/

	/**
	 * Notices about missing pages
	 *
	 * @since 3.0
	 * @deprecated 3.1.2
	 */
	private function add_page_notices() {

		// Checkout page is missing
		$purchase_page = edd_get_option( 'purchase_page', '' );
		if ( empty( $purchase_page ) || ( 'trash' === get_post_status( $purchase_page ) ) ) {
			$this->add_notice( array(
				'id'             => 'edd-no-purchase-page',
				/* translators: %s: URL to the settings page */
				'message'        => sprintf( __( 'No checkout page is configured. Set one in <a href="%s">Settings</a>.', 'easy-digital-downloads' ), esc_url( edd_get_admin_url( array( 'page' => 'edd-settings', 'tab' => 'general', 'section' => 'pages' ) ) ) ),
				'class'          => 'error',
				'is_dismissible' => false,
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
	 * @deprecated 3.1.2
	 */
	private function add_system_notices() {

		// Bail if not an EDD admin page
		if ( ! edd_is_admin_page() || edd_is_dev_environment() || edd_is_admin_page( 'index.php' ) ) {
			return;
		}

		// Bail if user cannot manage options
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Bail if uploads directory is protected
		if ( edd_is_uploads_url_protected() ) {
			return;
		}

		// Get the upload directory
		$upload_directory   = edd_get_upload_dir();

		// Running NGINX
		$show_nginx_notice = apply_filters( 'edd_show_nginx_redirect_notice', true );
		if ( $show_nginx_notice && ! empty( $GLOBALS['is_nginx'] ) && ! get_user_meta( get_current_user_id(), '_edd_nginx_redirect_dismissed', true ) ) {
			$dismiss_notice_url = wp_nonce_url( add_query_arg( array(
				'edd_action' => 'dismiss_notices',
				'edd_notice' => 'nginx_redirect'
			) ), 'edd_notice_nonce' );

			$this->add_notice( array(
				'id'             => 'edd-nginx',
				'class'          => 'error',
				'is_dismissible' => false,
				'message'        => array(
					/* translators: %s: Uploads directory */
					sprintf( __( 'The files in %s are not currently protected.', 'easy-digital-downloads' ), '<code>' . $upload_directory . '</code>' ),
					__( 'To protect them, you must add this <a href="https://easydigitaldownloads.com/docs/download-files-not-protected-on-nginx/">NGINX redirect rule</a>.', 'easy-digital-downloads' ),
					/* translators: %s: Dismiss notice URL */
					sprintf( __( 'If you have already done this, or it does not apply to your site, you may permenently %s.', 'easy-digital-downloads' ), '<a href="' . esc_url( $dismiss_notice_url ) . '">' . __( 'dismiss this notice', 'easy-digital-downloads' ) . '</a>' )
				)
			) );
		}

		// Running Apache
		if ( ! empty( $GLOBALS['is_apache'] ) && ! edd_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_edd_htaccess_missing_dismissed', true ) ) {
			$dismiss_notice_url = wp_nonce_url( add_query_arg( array(
				'edd_action' => 'dismiss_notices',
				'edd_notice' => 'htaccess_missing'
			) ), 'edd_notice_nonce' );

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
				'message'        => sprintf(
					/* translators: 1. link to the recount tool; 2. link to the plugins screen. */
					__( 'Easy Digital Downloads 2.5 contains a <a href="%1$s">built in recount tool</a>. Please <a href="%2$s">deactivate the Easy Digital Downloads - Recount Earnings plugin</a>', 'easy-digital-downloads' ),
					esc_url( edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'general' ) ) ),
					esc_url( admin_url( 'plugins.php' ) )
				)
			) );
		}
	}

	/**
	 * Adds a notice about the deprecated Default Rate for Taxes.
	 *
	 * @since 3.0
	 * @since 3.0.2 - We've found a way to add default tax rates. Leaving the method in case anyone (for some reason) is calling it.
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
		if ( empty( $_GET['page'] ) || ( 'edd-settings' !== $_GET['page'] ) ) {
			return;
		}

		// Settings updated
		if ( ! empty( $_GET['settings-updated'] ) ) {
			$this->add_notice(
				array(
					'id'      => 'edd-notices',
					'message' => __( 'Settings updated.', 'easy-digital-downloads' )
				)
			);
		}

		if ( 'accounting' === filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
			if ( ! empty( edd_get_option( 'sequential_start_update_failed', false ) ) ) {
				$order_number = new \EDD\Orders\Number();
				$this->add_notice(
					array(
						'id'      => 'edd-sequential-order-numbers-not-updated',
						'message' => sprintf(
							/* translators: %s: Next order number */
							__( 'The sequential starting number could not be updated because it could conflict with existing orders. The next assigned order number is %s.', 'easy-digital-downloads' ),
							'<code>' . $order_number->format( (int) get_option( 'edd_next_order_number' ) ) . '</code>'
						),
						'class'   => 'error',
					)
				);
				edd_delete_option( 'sequential_start_update_failed' );
			}
		}
	}

	/**
	 * Adds a notice if an order migration is running.
	 * This is only shown if the migration is running via UI by a different user or on another screen.
	 *
	 * @since 3.1.2
	 * @return void
	 */
	private function add_order_upgrade_notice() {
		if ( edd_has_upgrade_completed( 'migrate_orders' ) ) {
			return;
		}
		if ( ! get_option( '_edd_v30_doing_order_migration', false ) ) {
			return;
		}
		if ( get_option( 'edd_v30_cli_migration_running', false ) ) {
			return;
		}
		$this->add_notice(
			array(
				'id'             => 'edd-v30-order-migration-running',
				'class'          => 'updated',
				'message'        => __( 'Easy Digital Downloads is migrating orders. Sales and earnings data for your store will be updated when all orders have been migrated.', 'easy-digital-downloads' ),
				'is_dismissible' => false,
			)
		);
	}

	/**
	 * Adds a notice if the Stripe Pro gateway is outdated.
	 * This is due to a name change in the gateway.
	 *
	 * @since 3.2.1
	 * @return void
	 */
	private function add_stripe_notice() {
		if ( ! defined( 'EDD_STRIPE_VERSION' ) || ( defined( 'EDD_STRIPE_VERSION' ) && version_compare( EDD_STRIPE_VERSION, '2.8.4', '>=' ) ) ) {
			return;
		}
		$this->add_notice(
			array(
				'id'             => 'edd-stripe-outdated',
				'class'          => 'notice-warning',
				'message'        => sprintf(
					// translators: 1. opening link tag; 2. opening link tag; 3. closing link tag.
					__( 'You are running an outdated version of the Easy Digital Downloads &mdash; Stripe Pro Payment Gateway. You may need to log into %1$syour account%3$s to download the latest version and %2$smanually upgrade%3$s it.', 'easy-digital-downloads' ),
					'<a href="https://easydigitaldownloads.com/your-account/" target="_blank">',
					'<a href="https://easydigitaldownloads.com/docs/how-do-i-install-an-extension/#faq" target="_blank">',
					'</a>'
				),
				'is_dismissible' => false,
			)
		);
	}

	/**
	 * Adds a notice if PayPal webhooks need to synced.
	 *
	 * @since 3.2.1
	 * @return void
	 */
	private function add_paypal_sync_notice() {
		if ( ! get_option( 'edd_paypal_webhook_sync_failed' ) ) {
			return;
		}
		$url = edd_get_admin_url(
			array(
				'page'    => 'edd-settings',
				'tab'     => 'gateways',
				'section' => 'paypal_commerce',
			)
		);

		$this->add_notice(
			array(
				'id'             => 'edd-paypal-webhook-sync',
				'class'          => 'updated',
				'message'        => sprintf(
					/* translators: %1$s: Opening anchor tag; %2$s: Closing anchor tag. */
					__( 'New webhooks have been registered for PayPal Commerce, but we were unable to update them automatically. Please %1$ssync your webhooks manually%2$s.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( $url ) . '">',
					'</a>'
				),
				'is_dismissible' => false,
			)
		);
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
				case 'discount_invalid_amount' :
					$this->add_notice( array(
						'id'      => 'edd-discount-invalid-amount',
						'message' => __( 'The discount amount must be a valid percentage or numeric flat amount. Please try again.', 'easy-digital-downloads' ),
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
				case 'discount_archived':
					$this->add_notice( array(
						'id'      => 'edd-discount-archived',
						'message' => __( 'Discount code archived.', 'easy-digital-downloads' )
					) );
					break;
				case 'discount_archived_failed':
					$this->add_notice( array(
						'id'      => 'edd-discount-archived-fail',
						'message' => __( 'There was a problem archiving that discount code, please try again.', 'easy-digital-downloads' ),
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
				case 'test-purchase-email-sent':
					$this->add_notice(
						array(
							'id'      => 'edd-test-purchase-receipt-sent',
							'message' => __( 'The test email was sent successfully.', 'easy-digital-downloads' )
						)
					);
					break;
				case 'test-purchase-email-failed':
					$this->add_notice(
						array(
							'id'      => 'edd-test-purchase-receipt-failed',
							'message' => __( 'The test email could not be sent. Please check your email settings and try again.', 'easy-digital-downloads' ),
							'class'   => 'error',
						)
					);
					break;
				case 'test-summary-email-sent':
					$this->add_notice(
						array(
							'id'      => 'edd-test-summary-email-sent',
							'message' => __( 'The test email summary was sent successfully.', 'easy-digital-downloads' )
						)
					);
					break;

				case 'missing-pass-key':
					$this->add_notice(
						array(
							'id'      => 'edd-missing-pass-key',
							'message' => __( 'Your extensions could not be refreshed because you have not verified your license key.', 'easy-digital-downloads' ),
							'class'   => 'error',
						)
					);
					break;
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
						'message' => __( 'The order has been deleted.', 'easy-digital-downloads' )
					) );
					break;
				case 'email_sent' :
					$this->add_notice( array(
						'id'      => 'edd-payment-sent',
						'message' => __( 'The purchase receipt has been resent.', 'easy-digital-downloads' )
					) );
					break;
				case 'email_send_failed':
					$this->add_notice( array(
						'id'      => 'edd-payment-not-sent',
						'message' => __( 'The purchase receipt could not be resent.', 'easy-digital-downloads' ),
						'class'   => 'error'
					) );
					break;
				case 'email_possibly_not_sent':
					$this->add_notice( array(
						'id'      => 'edd-order-receipt-maybe-not-sent',
						'message' => __( 'The purchase receipt may not have been sent.', 'easy-digital-downloads' ),
						'class'   => 'notice-warning'
					) );
					break;
				case 'payment-note-deleted' :
					$this->add_notice( array(
						'id'      => 'edd-note-deleted',
						'message' => __( 'The order note has been deleted.', 'easy-digital-downloads' )
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

		if ( 'one-click-upgrade' === $notice && edd_is_pro() && current_user_can( 'install_plugins' ) && edd_is_admin_page( 'settings' ) ) {
			$this->add_notice(
				array(
					'id'      => 'edd-upgraded',
					'message' => sprintf(
						/* Translators: 1. opening strong tag, do not translate; 2. closing strong tag, do not translate */
						__( 'Congratulations! You are now running %1$sEasy Digital Downloads (Pro)%2$s.', 'easy-digital-downloads' ),
						'<strong>',
						'</strong>'
					),
				)
			);
		}
	}

	/**
	 * Show a notice if debugging is enabled in the EDD settings.
	 * Does not show if only the `EDD_DEBUG_MODE` constant is defined.
	 *
	 * @since 2.11.5
	 * @return void
	 */
	private function show_debugging_notice() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return;
		}
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		if ( ! edd_get_option( 'debug_mode', false ) ) {
			return;
		}

		/**
		 * The notices JS needs to be output wherever the notice is displayed, not just EDD screens.
		 * If more notices add to the script then this enqueue will need to be moved.
		 *
		 * @since 3.0
		 */
		wp_enqueue_script( 'edd-admin-notices', EDD_PLUGIN_URL . 'assets/js/edd-admin-notices.js', array( 'jquery' ), EDD_VERSION, true );
		$view_url = add_query_arg(
			array(
				'post_type' => 'download',
				'page'      => 'edd-tools',
				'tab'       => 'debug_log',
			),
			admin_url( 'edit.php' )
		);
		?>
		<div id="edd-debug-log-notice" class="notice notice-warning">
			<p>
				<?php esc_html_e( 'Easy Digital Downloads debug logging is enabled. Please only leave it enabled for as long as it is needed for troubleshooting.', 'easy-digital-downloads' ); ?>
			</p>
			<p>
				<a class="button button-secondary" href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e( 'View Debug Log', 'easy-digital-downloads' ); ?></a>
				<button class="button button-primary" id="edd-disable-debug-log"><?php esc_html_e( 'Delete Log File and Disable Logging', 'easy-digital-downloads' ); ?></button>
				<?php wp_nonce_field( 'edd_debug_log_delete', 'edd_debug_log_delete' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Disables the debug log setting and deletes the existing log file.
	 *
	 * @since 2.11.5
	 * @return void
	 */
	public function edd_disable_debugging() {
		$validate_nonce = ! empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'edd_debug_log_delete' );
		if ( ! current_user_can( 'manage_shop_settings' ) || ! $validate_nonce ) {
			wp_send_json_error( wpautop( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) ), 403 );
		}
		edd_update_option( 'debug_mode', false );
		global $edd_logs;
		$edd_logs->clear_log_file();
		wp_send_json_success( wpautop( __( 'The debug log has been cleared and logging has been disabled.', 'easy-digital-downloads' ) ) );
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
