<?php
/**
 * EDD Loader
 *
 * The Loader class bootstraps components that are required
 * during both the install process and instantiation of the
 * main Easy_Digital_Downloads class. This allows us to
 * load EDD on plugins_loaded and still access required
 * components from the register_plugin_activation hook.
 *
 * @package		EDD
 * @subpackage	Classes/Loader
 * @copyright	Copyright (c) 2015, Pippin Williamson
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Loader Class
 *
 * A general use class for bootstrapping components required during load and install.
 *
 * @since 2.5
 */
class EDD_Loader {

	/**
	 * EDD plugin file
	 *
	 * @var string
	 * @since 2.5
	 */
	public $plugin_file;

	/**
	 * EDD Roles Object
	 *
	 * @var object
	 * @since 2.5
	 */
	public $roles;

	/**
	 * EDD API Object
	 *
	 * @var object
	 * @since 1.5
	 */
	public $api;

	/**
	 * EDD Session Object
	 *
	 * @var object
	 * @since 2.5
	 */
	public $session;
	
	/**
	 * EDD Customers DB Object
	 *
	 * @var object
	 * @since 2.5
	 */
	public $customers;

	/**
	 * Set up the EDD Loader Class
	 *
	 * @since 2.5
	 * @param string $plugin_file The plugin file
	 */
	public function __construct( $plugin_file ) {
		// We need the main plugin file reference
		$this->plugin_file = $plugin_file;

		$this->setup_constants();
		$this->includes();

		$this->roles     = new EDD_Roles();
		$this->api       = new EDD_API();
		$this->session   = new EDD_Session();
		$this->customers = new EDD_DB_Customers();
	}


	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 2.5
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version
		define( 'EDD_VERSION', '2.4.3' );

		// Plugin folder path
		define( 'EDD_PLUGIN_DIR', plugin_dir_path( $this->plugin_file ) );

		// Plugin folder URL
		define( 'EDD_PLUGIN_URL', plugin_dir_url( $this->plugin_file ) );

		// Plugin root file
		define( 'EDD_PLUGIN_FILE', $this->plugin_file );

		// Make sure CAL_GREGORIAN is defined
		if ( ! defined( 'CAL_GREGORIAN' ) ) {
			define( 'CAL_GREGORIAN', 1 );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 2.5
	 * @return void
	 */
	private function includes() {
		global $edd_options;
		
		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$edd_options = edd_get_settings();

		require_once EDD_PLUGIN_DIR . 'includes/api/class-edd-api.php';
		require_once EDD_PLUGIN_DIR . 'includes/post-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-db.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-db-customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-customer.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-session.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-roles.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-stats.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
		require_once EDD_PLUGIN_DIR . 'includes/country-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/class-edd-emails.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/class-edd-email-tags.php';

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/welcome.php';
		}
	}
}
