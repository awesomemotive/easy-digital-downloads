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
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EDD_License' ) ) :

/**
 * EDD_License Class
 */
class EDD_License {
	private $file;
	private $license;
	private $item_name;
	private $item_id;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'https://easydigitaldownloads.com/edd-sl-api/';

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
	function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null, $_item_id = null ) {
		$this->file = $_file;
		$this->item_name = $_item_name;

		if ( is_numeric( $_item_id ) ) {
			$this->item_id = absint( $_item_id );
		}

		$this->item_shortname = 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = trim( edd_get_option( $this->item_shortname . '_license_key', '' ) );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the plugins had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactive their license.
		 */
		if ( ! empty( $_optname ) ) {
			$opt = edd_get_option( $_optname, false );

			if( isset( $opt ) && empty( $this->license ) ) {
				$this->license = trim( $opt );
			}
		}

		// Setup hooks
		$this->includes();
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
		if ( ! empty( $this->license ) ) {
			global $edd_licensed_products;
			if ( ! is_array( $edd_licensed_products ) ) {
				$edd_licensed_products = array();
			}
			$edd_licensed_products[] = $this->item_shortname;
		}

	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) )  {
			require_once 'EDD_SL_Plugin_Updater.php';
		}
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'edd_settings_licenses', array( $this, 'settings' ), 1 );

		// Display help text at the top of the Licenses tab
		add_action( 'edd_settings_tab_top', array( $this, 'license_help_text' ) );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		// Check that license is valid once per week
		if ( edd_doing_cron() ) {
			add_action( 'edd_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );
		}

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

		$betas = edd_get_option( 'enabled_betas', array() );

		$args = array(
			'version'   => $this->version,
			'license'   => $this->license,
			'author'    => $this->author,
			'beta'      => function_exists( 'edd_extension_has_beta_support' ) && edd_extension_has_beta_support( $this->item_shortname ),
		);

		if( ! empty( $this->item_id ) ) {
			$args['item_id']   = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater(
			$this->api_url,
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
		$edd_license_settings = array(
			array(
				'id'      => $this->item_shortname . '_license_key',
				'name'    => sprintf( __( '%1$s', 'easy-digital-downloads' ), $this->item_name ),
				'desc'    => '',
				'type'    => 'license_key',
				'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
				'size'    => 'regular'
			)
		);

		return array_merge( $settings, $edd_license_settings );
	}


	/**
	 * Display help text at the top of the Licenses tag
	 *
	 * @since   2.5
	 * @param   string   $active_tab
	 * @return  void
	 */
	public function license_help_text( $active_tab = '' ) {

		static $has_ran;

		if( 'licenses' !== $active_tab ) {
			return;
		}

		if( ! empty( $has_ran ) ) {
			return;
		}

		echo '<p>' . sprintf(
			__( 'Enter your extension license keys here to receive updates for purchased extensions. If your license key has expired, please <a href="%s" target="_blank">renew your license</a>.', 'easy-digital-downloads' ),
			'http://docs.easydigitaldownloads.com/article/1000-license-renewal'
		) . '</p>';

		$has_ran = true;

	}

	/**
	 * If the supplied license key is for a pass, updates the `edd_pass_licenses` option with
	 * the pass ID and the date it was checked.
	 *
	 * Note: It's intentional that the `edd_pass_licenses` option is always updated, even if
	 * the provided license data is not for a pass. This is so we have a clearer idea
	 * of when the checks started coming through. If the option doesn't exist in the DB
	 * at all, then we haven't checked any licenses.
	 *
	 * @since 2.10.6
	 *
	 * @param string $license
	 * @param object $api_data
	 */
	private function maybe_set_pass_flag( $license, $api_data ) {
		$passes = get_option( 'edd_pass_licenses' );
		$passes = ! empty( $passes ) ? json_decode( $passes, true ) : array();

		if ( ! empty( $api_data->pass_id ) && ! empty( $api_data->license ) && 'valid' === $api_data->license ) {
			$passes[ $license ] = array(
				'pass_id'      => intval( $api_data->pass_id ),
				'time_checked' => time()
			);
		} else if ( array_key_exists( $license, $passes ) ) {
			unset( $passes[ $license ] );
		}

		update_option( 'edd_pass_licenses', json_encode( $passes ) );
	}

	/**
	 * Removes the pass flag for the supplied license. This happens when a license
	 * is deactivated.
	 *
	 * @since 2.10.6
	 *
	 * @param string $license
	 */
	private function maybe_remove_pass_flag( $license ) {
		$passes = get_option( 'edd_pass_licenses' );
		$passes = ! empty( $passes ) ? json_decode( $passes, true ) : array();

		if ( array_key_exists( $license, $passes ) ) {
			unset( $passes[ $license ] );
		}

		update_option( 'edd_pass_licenses', json_encode( $passes ) );
	}

	/**
	 * Activate the license key
	 *
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
			if( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		$details = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( $_POST['edd_settings'][ $this->item_shortname . '_license_key'] );

		if( empty( $license ) ) {
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
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$this->maybe_set_pass_flag( $this->license, $license_data );

		update_option( $this->item_shortname . '_license_active', $license_data );

	}


	/**
	 * Deactivate the license key
	 *
	 * @return  void
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST['edd_settings'] ) )
			return;

		if ( ! isset( $_POST['edd_settings'][ $this->item_shortname . '_license_key'] ) )
			return;

		if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {

			wp_die( __( 'Nonce verification failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );

		}

		if( ! current_user_can( 'manage_shop_settings' ) ) {
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
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			$this->maybe_remove_pass_flag( $this->license );

			delete_option( $this->item_shortname . '_license_active' );

		}
	}


	/**
	 * Check if license key is valid once per week
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		if( ! empty( $_POST['edd_settings'] ) ) {
			return; // Don't fire when saving settings
		}

		if( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'check_license',
			'license' 	=> $this->license,
			'item_name' => urlencode( $this->item_name ),
			'url'       => home_url()
		);

		if ( ! empty( $this->item_id ) ) {
			$api_params['item_id'] = $this->item_id;
		}

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$this->maybe_set_pass_flag( $this->license, $license_data );

		update_option( $this->item_shortname . '_license_active', $license_data );

	}


	/**
	 * Admin notices for errors
	 *
	 * @return  void
	 */
	public function notices() {

		static $showed_invalid_message;

		if( empty( $this->license ) ) {
			return;
		}

		if( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		$messages = array();

		$license = get_option( $this->item_shortname . '_license_active' );

		if( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {

			if( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {

				$messages[] = sprintf(
					__( 'You have invalid or expired license keys for Easy Digital Downloads. Please go to the <a href="%s">Licenses page</a> to correct this issue.', 'easy-digital-downloads' ),
					admin_url( 'edit.php?post_type=download&page=edd-settings&tab=licenses' )
				);

				$showed_invalid_message = true;

			}

		}

		if( ! empty( $messages ) ) {

			foreach( $messages as $message ) {

				echo '<div class="error">';
					echo '<p>' . $message . '</p>';
				echo '</div>';

			}

		}

	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		static $showed_imissing_key_message;

		$license = get_option( $this->item_shortname . '_license_active' );

		if( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {

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
}

endif; // end class_exists check
