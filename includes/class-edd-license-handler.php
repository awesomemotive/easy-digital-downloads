<?php
/**
 * License handler for Easy Digital Downloads
 *
 * This class should simplify the process of adding license information
 * to new EDD extensions.
 *
 * @version 1.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Licensing\API;
use EDD\Licensing\License;

if ( ! class_exists( 'EDD_License' ) ) :

/**
 * EDD_License Class
 */
class EDD_License {
	private $file;
	public $license;
	private $item_name;
	private $item_id;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url;
	private $api_handler;

	/**
	 * The pass manager.
	 *
	 * @var \EDD\Admin\Pass_Manager
	 */
	private $pass_manager;

	/**
	 * The EDD license object.
	 * This contains standard license data (from the API response) and the license key.
	 *
	 * @var \EDD\Licensing\License
	 */
	private $edd_license;

	/**
	 * Whether the license being checked is a pro license.
	 *
	 * @since 3.1.1
	 * @var bool
	 */
	private $is_pro_license = false;

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 * @param int     $_item_id
	 */
	public function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null, $_item_id = null ) {
		$this->file      = $_file;
		$this->item_name = $_item_name;

		if ( is_numeric( $_item_id ) ) {
			$this->item_id = absint( $_item_id );
		}

		$this->item_shortname = 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->edd_license    = new License( $this->item_name, $_optname );
		if ( empty( $_api_url ) && ( empty( $this->edd_license->key ) || empty( $this->edd_license->license ) ) ) {
			$pro_license = new License( 'pro' );
			if ( ! empty( $pro_license->key ) ) {
				$this->is_pro_license = true;
				$this->edd_license    = $pro_license;
			}
		}
		$this->license      = $this->edd_license->key;
		$this->author       = $_author;
		$this->api_handler  = new API( $_api_url );
		$this->api_url      = $_api_url;
		$this->pass_manager = new \EDD\Admin\Pass_Manager();

		// Setup hooks
		$this->hooks();

		/**
		 * Maintain an array of active, licensed plugins that have a license key entered.
		 * This is to help us more easily determine if the site has a license key entered
		 * at all. Initializing it this way helps us limit the data to activated plugins only.
		 * If we relied on the options table (`edd_%_license_active`) then we could accidentally
		 * be picking up plugins that have since been deactivated.
		 *
		 * @see \EDD\Admin\Promos\Notices\License_Upgrade_Notice::__construct()
		 */
		if ( is_null( $this->api_url ) ) {
			global $edd_licensed_products;
			if ( ! is_array( $edd_licensed_products ) ) {
				$edd_licensed_products = array();
			}
			$edd_licensed_products[ $this->item_shortname ] = (int) (bool) ( $this->license && empty( $this->edd_license->error ) );
		}
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'edd_settings_licenses', array( $this, 'settings' ) );

		// Check that license is valid once per week
		add_action( 'edd_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );

		// For testing license notices, uncomment this line to force checks on every page load
		//add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Updater
		add_action( 'init', array( $this, 'auto_updater' ) );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );

		// Register plugins for beta support
		add_filter( 'edd_beta_enabled_extensions', array( $this, 'register_beta_support' ) );

		// Add the EDD version to the API parameters.
		add_filter( 'edd_sl_plugin_updater_api_params', array( $this, 'filter_sl_api_params' ), 10, 3 );
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {

		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		$license = $this->license;
		// Fall back to the highest license key if one is not saved for this extension or there isn't a pro license.
		if ( empty( $license ) && empty( $this->api_url ) ) {
			if ( $this->pass_manager->highest_license_key ) {
				$license = $this->pass_manager->highest_license_key;
			}
		}

		// Don't check for updates if there isn't a license key.
		if ( empty( $license ) ) {
			return;
		}

		$args = array(
			'version' => $this->version,
			'license' => $license,
			'author'  => $this->author,
			'beta'    => function_exists( 'edd_extension_has_beta_support' ) && edd_extension_has_beta_support( $this->item_shortname ),
		);

		if ( ! empty( $this->item_id ) ) {
			$args['item_id'] = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once 'EDD_SL_Plugin_Updater.php';
		}

		// Setup the updater
		new EDD_SL_Plugin_Updater(
			is_null( $this->api_url ) ? $this->api_handler->get_url() : $this->api_url,
			$this->file,
			$args
		);
	}

	/**
	 * Add license field to settings
	 *
	 * @param array   $settings
	 * @return  array
	 */
	public function settings( $settings ) {
		return array_merge( $settings, array(
			array(
				'id'      => $this->item_shortname . '_license_key',
				'name'    => $this->item_name,
				'desc'    => '',
				'type'    => 'license_key',
				'options' => array(
					'is_valid_license_option' => $this->item_shortname . '_license_active',
					'item_id'                 => $this->item_id,
					'api_url'                 => $this->api_url,
					'file'                    => $this->file,
				),
				'size'    => 'regular',
			)
		) );
	}

	/**
	 * Check if license key is valid once per week
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		// If a pro license is active, that license check is handled separately.
		if ( $this->is_pro_license && empty( $this->api_url ) ) {
			return;
		}

		// Don't fire when saving settings.
		if ( ! empty( $_POST['edd_settings'] ) ) {
			return;
		}

		if ( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $this->license,
			'item_name'  => urlencode( $this->item_name ),
		);

		if ( ! empty( $this->item_id ) ) {
			$api_params['item_id'] = $this->item_id;
		}

		$license_data = $this->api_handler->make_request( $api_params );
		if ( ! $license_data ) {
			return false;
		}

		if ( empty( $this->api_url ) ) {
			$this->pass_manager->maybe_set_pass_flag( $this->license, $license_data );
		}
		$this->edd_license->save( $license_data );
	}

	/**
	 * Admin notices for errors
	 *
	 * @return  void
	 */
	public function notices() {
		if ( empty( $this->license ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		if ( ! empty( $_GET['tab'] ) && 'licenses' === $_GET['tab'] ) {
			return;
		}

		if ( edd_is_pro() ) {
			return;
		}

		if ( ( empty( $this->edd_license->license ) || 'valid' !== $this->edd_license->license ) ) {

			EDD()->notices->add_notice(
				array(
					'id'             => 'edd-missing-license',
					'class'          => "error {$this->item_shortname}-license-error",
					'message'        => sprintf(
						/* translators: 1. opening anchor tag; 2. closing anchor tag */
						__( 'You have invalid or expired license keys for Easy Digital Downloads. %1$sActivate License(s)%2$s', 'easy-digital-downloads' ),
						'<a href="' . esc_url( edd_get_admin_url( array( 'page' => 'edd-settings', 'tab' => 'licenses' ) ) ) . '" class="button button-secondary">',
						'</a>'
					),
					'is_dismissible' => false,
				)
			);
		}
	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {
		static $showed_imissing_key_message = array();

		$license = $this->edd_license;

		if ( ( empty( $license->license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {
			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=licenses' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'easy-digital-downloads' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->item_shortname ] = true;
		}

	}

	/**
	 * Adds this plugin to the beta page
	 *
	 * @param   array $products
	 * @since   2.6.11
	 * @return  void
	 */
	public function register_beta_support( $products ) {
		$products[ $this->item_shortname ] = $this->item_name;

		return $products;
	}

	/**
	 * Adds the EDD version to the API parameters.
	 *
	 * @since 2.11
	 * @param array  $api_params  The array of parameters sent in the API request.
	 * @param array  $api_data    The array of API data defined when instantiating the class.
	 * @param string $plugin_file The path to the plugin file.
	 * @return array
	 */
	public function filter_sl_api_params( $api_params, $api_data, $plugin_file ) {

		if ( $this->file === $plugin_file ) {
			$api_params['easy-digital-downloads_version'] = defined( 'EDD_VERSION' ) ? EDD_VERSION : '';
		}

		return $api_params;
	}

	/**
	 * If the original Stripe gateway key is set and the new one is not,
	 * update the license key to fix automatic updates.
	 *
	 * @since 3.0.4
	 * @deprecated 3.2.1
	 * @return void
	 */
	public function fix_stripe_key() {
		$license_key = edd_get_option( 'edd_stripe_pro_payment_gateway_license_key' );
		if ( $license_key ) {
			return;
		}
		$old_key = edd_get_option( 'edd_stripe_payment_gateway_license_key' );
		if ( $old_key ) {
			edd_update_option( 'edd_stripe_pro_payment_gateway_license_key', sanitize_text_field( $old_key ) );
			edd_delete_option( 'edd_stripe_payment_gateway_license_key' );
		}

		$old_license_status = get_option( 'edd_stripe_payment_gateway_license_key_active' );
		if ( $old_license_status ) {
			update_option( 'edd_stripe_pro_payment_gateway_license_key_active', santize_text_field( $old_license_status ) );
			delete_option( 'edd_stripe_payment_gateway_license_key_active' );
		}
	}

	/**
	 * Activate the license key.
	 *
	 * @deprecated 3.1.1
	 * @return  void
	 */
	public function activate_license() {

		if ( ! isset( $_POST['edd_settings'] ) ) {
			return;
		}

		if ( ! isset( $_REQUEST[ $this->item_shortname . '_license_key-nonce'] ) || ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		if ( empty( $_POST['edd_settings'][ $this->item_shortname . '_license_key'] ) ) {
			delete_option( $this->item_shortname . '_license_active' );
			return;
		}

		foreach ( $_POST as $key => $value ) {
			if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		if ( 'valid' === $this->edd_license->license ) {
			return;
		}

		$license = sanitize_text_field( $_POST['edd_settings'][ $this->item_shortname . '_license_key' ] );

		if ( empty( $license ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		if ( ! empty( $this->item_id ) ) {
			$api_params['item_id'] = $this->item_id;
		}

		// Call the API
		$license_data = $this->api_handler->make_request( $api_params );

		// Make sure there are no errors
		if ( ! $license_data ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		$this->pass_manager->maybe_set_pass_flag( $license, $license_data );

		// Clear the option for licensed extensions to force regeneration.
		if ( ! empty( $license_data->license ) && 'valid' === $license_data->license ) {
			delete_option( 'edd_licensed_extensions' );
		}

		$this->edd_license->save( $license_data );
	}

	/**
	 * Deactivate the license key
	 *
	 * @deprecated 3.1.1
	 * @return  void
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST['edd_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['edd_settings'][ $this->item_shortname . '_license_key'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
			wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate'] ) ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			if ( ! empty( $this->item_id ) ) {
				$api_params['item_id'] = $this->item_id;
			}

			// Call the API
			$response = $this->api_handler->make_request( $api_params );

			// Make sure there are no errors
			if ( ! $response ) {
				return;
			}

			$this->pass_manager->maybe_remove_pass_flag( $this->license );

			delete_option( $this->item_shortname . '_license_active' );
		}
	}

	/**
	 * Display help text at the top of the Licenses tag
	 *
	 * @since   2.5
	 * @deprecated 3.1.1.4
	 * @param   string   $active_tab
	 * @return  void
	 */
	public function license_help_text( $active_tab = '' ) {}
}

endif; // end class_exists check
