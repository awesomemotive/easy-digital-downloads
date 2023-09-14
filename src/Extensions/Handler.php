<?php
/**
 * License handler for extensions using the ExtensionRegistry.
 *
 * @since 3.1.1.4
 */

namespace EDD\Extensions;

use EDD\Licensing\API;
use EDD\Licensing\License;
use EDD\Admin\Pass_Manager;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handler Class
 */
class Handler {

	/**
	 * The license key.
	 *
	 * @var string
	 */
	private $license_key;

	/**
	 * The plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	private $item_name;

	/**
	 * The extension item ID.
	 *
	 * @var int
	 */
	private $item_id;

	/**
	 * The extension shortname.
	 *
	 * @var string
	 */
	private $item_shortname;

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	private $version;

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
	 * @param string $_file
	 * @param int    $_item_id
	 * @param string $_item_name
	 * @param string $_version
	 * @param string $_optname
	 */
	public function __construct( $_file, $_item_id, $_item_name, $_version, $_optname = null ) {
		$this->file           = $_file;
		$this->item_id        = absint( $_item_id );
		$this->item_name      = $_item_name;
		$this->item_shortname = $this->get_shortname();
		$this->version        = $_version;
		$this->pass_manager   = new Pass_Manager();
		$this->edd_license    = $this->get_license( $_optname );
		$this->license_key    = $this->edd_license->key;

		$this->hooks();
		$this->update_global();
	}

	/**
	 * Set up hooks.
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings.
		add_filter( 'edd_settings_licenses', array( $this, 'settings' ), 1 );

		// Check that license is valid once per week.
		if ( ! $this->is_pro_license ) {
			add_action( 'edd_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );
		}

		// Updater.
		add_action( 'init', array( $this, 'auto_updater' ) );

		// Display notices to admins.
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );

		// Register plugins for beta support.
		add_filter( 'edd_beta_enabled_extensions', array( $this, 'register_beta_support' ) );
	}

	/**
	 * Auto updater
	 *
	 * @return  void
	 */
	public function auto_updater() {

		if ( ! current_user_can( 'manage_options' ) && ! edd_doing_cron() ) {
			return;
		}

		// Fall back to the highest license key if one is not saved for this extension or there isn't a pro license.
		if ( empty( $this->license_key ) ) {
			if ( $this->pass_manager->highest_license_key ) {
				$this->license_key = $this->pass_manager->highest_license_key;
			}
		}

		// Don't check for updates if there isn't a license key.
		if ( empty( $this->license_key ) ) {
			return;
		}

		$args = array(
			'version' => $this->version,
			'license' => $this->license_key,
			'item_id' => $this->item_id,
			'beta'    => function_exists( 'edd_extension_has_beta_support' ) && edd_extension_has_beta_support( $this->item_shortname ),
		);

		// Set up the updater.
		new Updater(
			$this->file,
			$args
		);
	}

	/**
	 * Add license field to settings, unless the extension is included in the user's pass.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function settings( $settings ) {
		if ( $this->is_pro_license && $this->is_included_in_pass() ) {
			return $settings;
		}

		return array_merge(
			$settings,
			array(
				array(
					'id'      => "{$this->item_shortname}_license_key",
					'name'    => $this->item_name,
					'type'    => 'license_key',
					'options' => array(
						'is_valid_license_option' => "{$this->item_shortname}_license_active",
						'item_id'                 => $this->item_id,
					),
				),
			)
		);
	}

	/**
	 * Check if license key is valid once per week
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		// Don't fire when saving settings.
		if ( ! empty( $_POST['edd_settings'] ) ) {
			return;
		}

		if ( empty( $this->license_key ) ) {
			return;
		}

		if ( ! edd_doing_cron() ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $this->license_key,
			'item_name'  => urlencode( $this->item_name ),
			'item_id'    => $this->item_id,
		);

		$api_handler  = new API();
		$license_data = $api_handler->make_request( $api_params );
		if ( ! $license_data ) {
			return false;
		}

		$this->pass_manager->maybe_set_pass_flag( $this->license_key, $license_data );
		$this->edd_license->save( $license_data );
	}

	/**
	 * Admin notices for errors.
	 *
	 * @return  void
	 */
	public function notices() {
		if ( ! $this->should_show_error_notice() ) {
			return;
		}

		EDD()->notices->add_notice(
			array(
				'id'             => 'edd-missing-license',
				'class'          => "error {$this->item_shortname}-license-error",
				'message'        => sprintf(
					/* translators: 1. opening anchor tag; 2. closing anchor tag */
					__( 'You have invalid or expired license keys for Easy Digital Downloads. %1$sActivate License(s)%2$s', 'easy-digital-downloads' ),
					'<a href="' . esc_url( $this->get_license_tab_url() ) . '" class="button button-secondary">',
					'</a>'
				),
				'is_dismissible' => false,
			)
		);
	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @since   2.5
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {
		static $showed_imissing_key_message = array();

		if ( ! $this->is_license_valid() && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {
			echo '&nbsp;<strong><a href="' . esc_url( $this->get_license_tab_url() ) . '">' . esc_html__( 'Enter valid license key for automatic updates.', 'easy-digital-downloads' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->item_shortname ] = true;
		}
	}

	/**
	 * Adds this plugin to the beta page
	 *
	 * @param   array $products
	 * @since   2.6.11
	 * @return  array
	 */
	public function register_beta_support( $products ) {
		$products[ $this->item_shortname ] = $this->item_name;

		return $products;
	}

	/**
	 * Gets the URL for the licensing tab.
	 *
	 * @since 3.1.1.4
	 * @return string
	 */
	private function get_license_tab_url() {
		return edd_get_admin_url(
			array(
				'page' => 'edd-settings',
				'tab'  => 'licenses',
			)
		);
	}

	/**
	 * Whether the license is valid.
	 *
	 * @since 3.1.1.4
	 * @return bool
	 */
	private function is_license_valid() {
		return ! empty( $this->license_key ) && 'valid' === $this->edd_license->license;
	}

	/**
	 * Gets the extension shortname.
	 *
	 * @since 3.1.1.4
	 * @return string
	 */
	private function get_shortname() {
		return 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
	}

	/**
	 * Maintain an array of active, licensed plugins that have a license key entered.
	 * This is to help us more easily determine if the site has a license key entered
	 * at all. Initializing it this way helps us limit the data to activated plugins only.
	 * If we relied on the options table (`edd_%_license_active`) then we could accidentally
	 * be picking up plugins that have since been deactivated.
	 *
	 * @see \EDD\Admin\Promos\Notices\License_Upgrade_Notice::__construct()
	 */
	private function update_global() {
		global $edd_licensed_products;
		if ( ! is_array( $edd_licensed_products ) ) {
			$edd_licensed_products = array();
		}
		$edd_licensed_products[ $this->item_shortname ] = (int) (bool) $this->is_license_valid();
	}

	/**
	 * Whether a given product is included in the customer's active pass.
	 * Note this is nearly a copy of what's in EDD\Licensing\Traits\Controls\is_included_in_pass().
	 *
	 * @since 3.1.1.4
	 * @return bool
	 */
	private function is_included_in_pass() {
		// All Access and lifetime passes can access everything.
		if ( $this->pass_manager->hasAllAccessPass() ) {
			return true;
		}

		$api          = new \EDD\Admin\Extensions\ExtensionsAPI();
		$product_data = $api->get_product_data( array(), $this->item_id );
		if ( ! $product_data || empty( $product_data->categories ) ) {
			return false;
		}

		return (bool) $this->pass_manager->can_access_categories( $product_data->categories );
	}

	/**
	 * Helper method to determine if we should show the error notice.
	 *
	 * @since 3.1.1.4
	 * @return bool
	 */
	private function should_show_error_notice() {

		// Don't show the notice if EDD (Pro) is active.
		if ( edd_is_pro() ) {
			return false;
		}

		// Included in pass.
		if ( $this->is_included_in_pass() ) {
			return false;
		}

		if ( ! edd_is_admin_page() ) {
			return false;
		}

		// Not a pro license, but valid.
		if ( ! $this->is_pro_license && $this->is_license_valid() ) {
			return false;
		}

		// Current user lacks permissions.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}

		// It's the licenses tab.
		if ( ! empty( $_GET['tab'] ) && 'licenses' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		return true;
	}

	/**
	 * Gets the license object.
	 * The pro license is preferred; the individual extension license is used as a fallback.
	 *
	 * @since 3.1.4
	 * @param null|string $option_name The custom option name for the license key.
	 * @return \EDD\Licensing\License
	 */
	private function get_license( $option_name ) {
		$pro_license = new License( 'pro' );
		if ( ! empty( $pro_license->key ) && $this->is_included_in_pass() ) {
			$this->is_pro_license = true;
			return $pro_license;
		}

		return new License( $this->item_name, $option_name );
	}
}
