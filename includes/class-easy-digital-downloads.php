<?php


if ( ! class_exists( 'Easy_Digital_Downloads' ) ) :

/**
 * Easy_Digital_Downloads Class.
 *
 * @since 1.4
 * @since 3.0 Refactored and restructured to work with EDD_Requirements_Check.
 */
final class Easy_Digital_Downloads {

	/**
	 * @var Easy_Digital_Downloads The one true Easy_Digital_Downloads
	 *
	 * @since 1.4
	 */
	private static $instance;

	/**
	 * EDD loader file.
	 *
	 * @since 3.0
	 * @var string
	 */
	private $file = '';

	/**
	 * EDD Roles Object.
	 *
	 * @var object|EDD_Roles
	 * @since 1.5
	 */
	public $roles;

	/**
	 * EDD Cart Fees Object.
	 *
	 * @var object|EDD_Fees
	 * @since 1.5
	 */
	public $fees;

	/**
	 * EDD API Object.
	 *
	 * @var object|EDD_API
	 * @since 1.5
	 */
	public $api;

	/**
	 * EDD Utilities Object.
	 *
	 * @var object|EDD\Utilities
	 * @since 3.0
	 */
	public $utils;

	/**
	 * EDD HTML Session Object.
	 *
	 * This holds cart items, purchase sessions, and anything else stored in the session.
	 *
	 * @var object|EDD_Session
	 * @since 1.5
	 */
	public $session;

	/**
	 * EDD HTML Element Helper Object.
	 *
	 * @var object|EDD_HTML_Elements
	 * @since 1.5
	 */
	public $html;

	/**
	 * EDD Emails Object.
	 *
	 * @var object|EDD_Emails
	 * @since 2.1
	 */
	public $emails;

	/**
	 * EDD Email Template Tags Object.
	 *
	 * @var object|EDD_Email_Template_Tags
	 * @since 1.9
	 */
	public $email_tags;

	/**
	 * EDD Email Summary Cron Object.
	 *
	 * @var object|EDD_Email_Summary_Cron
	 * @since 3.1
	 */
	public $email_summary_cron;

	/**
	 * EDD Cart Object
	 *
	 * @var object|EDD_Cart
	 * @since 2.7
	 */
	public $cart;

	/**
	 * EDD Tracking Object
	 *
	 * @var object|EDD\Telemetry\Tracking
	 * @since 3.0
	 */
	public $tracking;

	/**
	 * EDD Notices Object
	 *
	 * @var object|EDD_Notices
	 * @since 3.0
	 */
	public $notices;

	/**
	 * EDD Structured Data Object
	 *
	 * @var object|EDD_Structured_Data
	 * @since 3.0
	 */
	public $structured_data;

	/**
	 * @var \EDD\Database\NotificationsDB
	 */
	public $notifications;

	/**
	 * EDD Payment Stats Object
	 *
	 * @var EDD_Payment_Stats
	 * @since 1.8
	 */
	public $payment_stats;

	/**
	 * @var EDD\Logging
	 */
	public $debug_log;

	/**
	 * Holds registered premium EDD extensions.
	 *
	 * @var \EDD\Extensions\ExtensionRegistry
	 * @since 2.11.4
	 */
	public $extensionRegistry;

	/**
	 * EDD Components array
	 *
	 * @var EDD\Component[]
	 * @since 3.0
	 */
	public $components = array();

	/**
	 * Pro Install
	 */
	private $pro = false;

	/**
	 * Email Summary Admin
	 *
	 * @var EDD_Email_Summary_Admin
	 */
	public $email_summary_admin;

	/**
	 * The currently viewed report.
	 *
	 * @var EDD\Reports\Report
	 */
	public $report;

	/**
	 * Main Easy_Digital_Downloads Instance.
	 *
	 * Insures that only one instance of Easy_Digital_Downloads exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.4
	 * @since 3.0 Accepts $file parameter to work with EDD_Requirements_Check
	 *
	 * @static
	 * @staticvar array $instance
	 *
	 * @uses Easy_Digital_Downloads::setup_constants() Setup constants.
	 * @uses Easy_Digital_Downloads::setup_files() Setup required files.
	 * @see EDD()
	 *
	 * @return object|Easy_Digital_Downloads The one true Easy_Digital_Downloads
	 */
	public static function instance( $file = '' ) {

		// Return if already instantiated
		if ( self::is_instantiated() ) {
			return self::$instance;
		}

		// Setup the singleton
		self::setup_instance( $file );

		// Bootstrap
		self::$instance->setup_constants();
		self::$instance->setup_files();
		self::$instance->setup_application();
		self::$instance->setup_compat();

		// APIs
		self::$instance->roles              = new EDD_Roles();
		self::$instance->fees               = new EDD_Fees();
		self::$instance->api                = new EDD_API();
		self::$instance->debug_log          = new EDD\Logging();
		self::$instance->utils              = new EDD\Utilities();
		self::$instance->session            = new EDD_Session();
		self::$instance->html               = new EDD\HTML\Elements();
		self::$instance->emails             = new EDD_Emails();
		self::$instance->email_tags         = new EDD_Email_Template_Tags();
		self::$instance->email_summary_cron = new EDD_Email_Summary_Cron();
		self::$instance->payment_stats      = new EDD_Payment_Stats();
		self::$instance->cart               = new EDD_Cart();
		self::$instance->structured_data    = new EDD\Structured_Data();
		self::$instance->notifications      = new \EDD\Database\NotificationsDB();
		self::$instance->extensionRegistry  = new \EDD\Extensions\ExtensionRegistry();

		// Admin APIs
		if ( is_admin() ) {
			self::$instance->notices             = new EDD_Notices();
			self::$instance->email_summary_admin = new EDD_Email_Summary_Admin();
		}

		// Parachute
		self::$instance->backcompat_globals();

		self::$instance->registerApiEndpoints();

		// Check if the pro code is present.
		if ( class_exists( '\\EDD\\Pro\\Core' ) ) {
			self::$instance->pro = true;
			if ( edd_is_pro() ) {
				new EDD\Pro\Core();
			}
		}
		if ( ! edd_is_pro() && class_exists( '\\EDD\\Lite\\Core' ) ) {
			new EDD\Lite\Core();
		}

		$tracking                 = edd_get_namespace( 'Telemetry\\Tracking' );
		self::$instance->tracking = new $tracking();

		// Return the instance.
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.6
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Method Not Allowed.', 'easy-digital-downloads' ), '1.6' );
	}

	/**
	 * Disable un-serializing of the class.
	 *
	 * @since 1.6
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Method Not Allowed.', 'easy-digital-downloads' ), '1.6' );
	}

	/**
	 * Backwards compatibility for some database properties
	 *
	 * This is probably still not working right, so don't count on it yet.
	 *
	 * @since 3.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		switch ( $key ) {
			case 'customers' :
				return new EDD\Compat\Customer();
			case 'customermeta' :
			case 'customer_meta' :
				return new EDD\Compat\CustomerMeta();

			default :
				return isset( $this->{$key} )
					? $this->{$key}
					: null;
		}
	}

	/**
	 * Whether the current install is a pro install.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	public function is_pro() {
		return $this->pro;
	}

	/**
	 * Return whether the main loading class has been instantiated or not.
	 *
	 * @since 3.0
	 *
	 * @return boolean True if instantiated. False if not.
	 */
	private static function is_instantiated() {

		// Return true if instance is correct class
		if ( ! empty( self::$instance ) && ( self::$instance instanceof Easy_Digital_Downloads ) ) {
			return true;
		}

		// Return false if not instantiated correctly
		return false;
	}

	/**
	 * Setup the singleton instance
	 *
	 * @since 3.0
	 * @param string $file
	 */
	private static function setup_instance( $file = '' ) {
		if ( empty( $file ) && defined( EDD_PLUGIN_FILE ) ) {
			$file = EDD_PLUGIN_FILE;
		}
		self::$instance       = new Easy_Digital_Downloads();
		self::$instance->file = $file;
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 1.4
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'EDD_VERSION' ) ) {
			define( 'EDD_VERSION', '3.2.11' );
		}

		// Make sure CAL_GREGORIAN is defined.
		if ( ! defined( 'CAL_GREGORIAN' ) ) {
			define( 'CAL_GREGORIAN', 1 );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.4
	 * @return void
	 */
	private function setup_files() {
		$this->include_options();
		$this->include_utilities();
		$this->include_reports();
		$this->include_components();
		$this->include_backcompat();
		$this->include_objects();
		$this->include_functions();

		// Admin
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->include_admin();
		} else {
			$this->include_frontend();
		}
	}

	/**
	 * Setup backwards compatibility hooks
	 *
	 * This method exists to setup the bridges between EDD versions, most
	 * notably between versions less than 2.9 and greater than 3.0.
	 *
	 * Compatibility classes are not set up during EDD uninstall in order to allow us to use WordPress functions
	 * to cleanly delete the old custom post types from pre-3.0.
	 *
	 * @access private
	 * @since 3.0
	 * @return void
	 */
	private function setup_compat() {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || plugin_basename( $this->file ) !== WP_UNINSTALL_PLUGIN ) {
			new EDD\Compat\Discount();
			new EDD\Compat\Customer();
			new EDD\Compat\Log();
			new EDD\Compat\Payment();
			new EDD\Compat\Tax();
			new EDD\Compat\Template();
		}
	}

	/**
	 * Setup the rest of the application
	 *
	 * @since 3.0
	 */
	private function setup_application() {
		add_action( 'plugins_loaded', 'edd_setup_components', 100 );

		$GLOBALS['edd_options'] = edd_get_settings();

		$this->maybe_load_amazon();

		// Load cache helper.
		new EDD_Cache_Helper();
	}

	/** Includes **************************************************************/

	/**
	 * Setup all of the custom database tables
	 *
	 * This method invokes all of the classes for each custom database table,
	 * and returns them in an array for easier testing.
	 *
	 * In a normal request, this method is called extremely early in EDD's load
	 * order, to ensure these tables have been created & upgraded before any
	 * other utility occurs on them (query, migration, etc...)
	 *
	 * @access public
	 * @since 3.0
	 * @return array
	 */
	private function include_components() {

		// Component helpers are loaded before everything
		require_once EDD_PLUGIN_DIR . 'includes/interface-edd-exception.php';
		require_once EDD_PLUGIN_DIR . 'includes/component-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-component.php';

		// Database Engine
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-base.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-column.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-schema.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-query.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-row.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-table.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-date.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/engine/class-compare.php';

		// Database Schemas
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-adjustments.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-customer-addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-customer-email-addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-logs.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-logs-api-requests.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-logs-file-downloads.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-notes.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-orders.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-order-addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-order-adjustments.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-order-items.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/schemas/class-order-transactions.php';

		// Database Objects
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-adjustment.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-customer.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-customer-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-customer-email-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-log.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-log-api-request.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-log-file-download.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-note.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-order.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-order-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-order-adjustment.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-order-item.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/rows/class-order-transaction.php';

		// Database Tables
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-adjustments.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-customer-addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-customer-email-addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-logs.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-logs-api-requests.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-logs-api-request-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-logs-file-downloads.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-logs-file-download-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-notes.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-orders.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-adjustments.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-adjustment-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-items.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-transactions.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-customer-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-adjustment-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-log-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-note-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/tables/class-order-item-meta.php';

		// Database Table Query Interfaces
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-adjustment.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-customer.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-customer-email-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-customer-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-log.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-log-api-request.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-log-file-download.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-note.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-order.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-order-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-order-adjustment.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-order-item.php';
		require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-order-transaction.php';

		// Old Database Components
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-db.php';
	}

	/**
	 * Setup all EDD settings & options
	 *
	 * @since 3.0
	 */
	private function include_options() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
	}

	/**
	 * Setup utilities
	 *
	 * @since 3.0
	 */
	private function include_utilities() {
		require_once EDD_PLUGIN_DIR . 'includes/class-utilities.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-base-object.php';
	}

	/**
	 * Setup all EDD settings & options
	 *
	 * @since 3.0
	 */
	private function include_reports() {
		require_once EDD_PLUGIN_DIR . 'includes/reports/class-init.php';
	}

	/**
	 * Setup backwards compatibility
	 *
	 * @since 3.0
	 */
	private function include_backcompat() {

		// PHP functions
		require_once EDD_PLUGIN_DIR . 'includes/compat-functions.php';

		// Backwards Compatibility
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-base.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-customer.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-customermeta.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-discount.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-discount-query.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-log.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-payment.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-tax.php';
		require_once EDD_PLUGIN_DIR . 'includes/compat/class-template.php';

		// Original Classes
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-customer.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-customer-query.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-discount.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-download.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-cache-helper.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-register-meta.php';

		// Classes
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-cron.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-fees.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-license-handler.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-session.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-stats.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-roles.php';

		// Deprecated Functions
		if ( file_exists( EDD_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/deprecated-functions.php';
		}
		require_once EDD_PLUGIN_DIR . 'includes/deprecated-hooks.php';
		require_once EDD_PLUGIN_DIR . 'includes/deprecated/classes.php';
	}

	/**
	 * Setup objects
	 *
	 * @since 3.0
	 */
	private function include_objects() {

		// CLI
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once EDD_PLUGIN_DIR . 'includes/class-edd-cli.php';
		}

		// Traits
		require_once EDD_PLUGIN_DIR . 'includes/traits/trait-refundable-item.php';

		// Adjustments
		require_once EDD_PLUGIN_DIR . 'includes/adjustments/class-adjustment.php';
		require_once EDD_PLUGIN_DIR . 'includes/adjustments/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/adjustments/meta.php';

		// API
		require_once EDD_PLUGIN_DIR . 'includes/api/class-edd-api.php';
		require_once EDD_PLUGIN_DIR . 'includes/api/v3/Endpoint.php';

		// Checkout
		require_once EDD_PLUGIN_DIR . 'includes/checkout/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/checkout/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/checkout/pages.php';

		// Customers
		require_once EDD_PLUGIN_DIR . 'includes/customers/class-customer-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/customers/class-customer-email-address.php';

		// Cart
		require_once EDD_PLUGIN_DIR . 'includes/cart/class-edd-cart.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/actions.php';

		// Currency
		require_once EDD_PLUGIN_DIR . 'includes/currency/functions.php';

		// Gateways
		require_once EDD_PLUGIN_DIR . 'includes/gateways/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/paypal.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/manual.php';

		$stripe = EDD_PLUGIN_DIR . 'includes/gateways/stripe/edd-stripe.php';

		if ( file_exists( $stripe ) ) {
			require_once( $stripe );
		}

		// Logs.
		require_once EDD_PLUGIN_DIR . 'includes/logs/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/api-request-log/class-api-request-log.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/api-request-log/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/api-request-log/meta.php';

		require_once EDD_PLUGIN_DIR . 'includes/logs/file-download-log/class-file-download-log.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/file-download-log/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/file-download-log/meta.php';

		require_once EDD_PLUGIN_DIR . 'includes/logs/log/class-log.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/log/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/logs/log/meta.php';

		// Notes
		require_once EDD_PLUGIN_DIR . 'includes/notes/class-note.php';
		require_once EDD_PLUGIN_DIR . 'includes/notes/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/notes/meta.php';

		// Orders
		require_once EDD_PLUGIN_DIR . 'includes/orders/classes/class-order.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/classes/class-order-address.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/classes/class-order-adjustment.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/classes/class-order-item.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/classes/class-order-transaction.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/classes/class-refund-validator.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/types.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/orders.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/items.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/refunds.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/addresses.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/adjustments.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/transactions.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/ui.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/transitions.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/statuses.php';
		require_once EDD_PLUGIN_DIR . 'includes/orders/functions/disputes.php';

		// Payments
		require_once EDD_PLUGIN_DIR . 'includes/payments/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-edd-payment.php';

		// Emails
		require_once EDD_PLUGIN_DIR . 'includes/emails/class-edd-emails.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/class-edd-email-tags.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/tags.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/tags-inserter.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/email-summary/class-edd-email-summary-cron.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/email-summary/class-edd-email-summary.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/email-summary/class-edd-email-summary-blurb.php';

		// Stats
		require_once EDD_PLUGIN_DIR . 'includes/class-stats.php';

		// Downloads
		require_once EDD_PLUGIN_DIR . 'includes/models/Download.php';

		// Blocks
		$blocks = EDD_PLUGIN_DIR . 'includes/blocks/edd-blocks.php';

		if ( file_exists( $blocks ) ) {
			require_once $blocks;
		}
	}

	/**
	 * Setup functions
	 *
	 * @since 3.0
	 */
	private function include_functions() {
		require_once EDD_PLUGIN_DIR . 'includes/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/mime-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/formatting.php';
		require_once EDD_PLUGIN_DIR . 'includes/widgets.php';
		require_once EDD_PLUGIN_DIR . 'includes/scripts.php';
		require_once EDD_PLUGIN_DIR . 'includes/post-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once EDD_PLUGIN_DIR . 'includes/error-tracking.php';
		require_once EDD_PLUGIN_DIR . 'includes/ajax-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/template-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/template-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/country-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/extensions/licensing-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/date-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/discount-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/download-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/downloads/recalculations.php';
		require_once EDD_PLUGIN_DIR . 'includes/customer-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/customers/customer-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/privacy-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/utils/class-tokenizer.php';
		require_once EDD_PLUGIN_DIR . 'includes/user-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/query-filters.php';
		require_once EDD_PLUGIN_DIR . 'includes/tax-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/refund-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/process-purchase.php';
		require_once EDD_PLUGIN_DIR . 'includes/users/login.php';
		require_once EDD_PLUGIN_DIR . 'includes/users/lost-password.php';
		require_once EDD_PLUGIN_DIR . 'includes/users/register.php';
		require_once EDD_PLUGIN_DIR . 'includes/shortcodes.php';
		require_once EDD_PLUGIN_DIR . 'includes/install.php';
		require_once EDD_PLUGIN_DIR . 'includes/upgrades/functions.php';

		// Admin files to load globally (cron, bar, etc...)
		require_once EDD_PLUGIN_DIR . 'includes/admin/admin-bar.php';
	}

	/**
	 * Setup administration
	 *
	 * @since 3.0
	 */
	private function include_admin() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/admin-footer.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/admin-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-heartbeat.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-list-table.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-sections.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/thickbox.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/adjustments/adjustment-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/notes/note-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/notes/note-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/emails/email-summary/class-edd-email-summary-admin.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/import/import-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/import/import-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/refunds.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/orders.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/class-order-sections.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-reports-sections.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/reports-callbacks.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-edd-graph.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-edd-pie-graph.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/plugins.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/deprecated-upgrade-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/downgrades.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/v3/upgrade-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/tools-actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/settings-compatibility.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/admin-deprecated-functions.php';

		require_once EDD_PLUGIN_DIR . 'includes/libraries/class-persistent-dismissible.php';
	}

	/**
	 * Setup front-end specific code
	 *
	 * @since 3.0
	 */
	private function include_frontend() {
		require_once EDD_PLUGIN_DIR . 'includes/process-download.php';
		require_once EDD_PLUGIN_DIR . 'includes/theme-compatibility.php';
	}

	/**
	 * Backwards compatibility for old global values
	 *
	 * @since 3.0
	 */
	private function backcompat_globals() {

		// The $edd_logs global
		$GLOBALS['edd_logs'] = self::$instance->debug_log;
	}

	/**
	 * Registers REST API endpoints.
	 *
	 * @todo move this somewhere better
	 *
	 * @since 2.11.4
	 */
	private function registerApiEndpoints() {
		add_action( 'rest_api_init', function() {
			$endpoints = array(
				'\\EDD\\API\\v3\\Notifications',
			);

			foreach( $endpoints as $endpointClassName ) {
				$endpointNamePieces = explode( '\\', $endpointClassName );
				$endpointName = end( $endpointNamePieces );

				require_once EDD_PLUGIN_DIR . 'includes/api/v3/' . $endpointName . '.php';

				if ( class_exists( $endpointClassName ) ) {
					$endpoint = new $endpointClassName();
					$endpoint->register();
				}
			}
		} );
	}

	/**
	 * Maybe load the Amazon Payments gateway.
	 * If the gateway is not set up, this will do nothing.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function maybe_load_amazon() {
		if ( ! edd_is_gateway_setup( 'amazon', true ) ) {
			return;
		}

		require_once EDD_PLUGIN_DIR . 'includes/gateways/amazon-payments.php';
		PayWithAmazon\EDD_Amazon_Payments::getInstance();
	}
}
endif; // End if class_exists check.

/**
 * Returns the instance of Easy_Digital_Downloads.
 *
 * The main function responsible for returning the one true Easy_Digital_Downloads
 * instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd = EDD(); ?>
 *
 * @since 1.4
 * @return Easy_Digital_Downloads The one true Easy_Digital_Downloads instance.
 */
function EDD() {
	return Easy_Digital_Downloads::instance();
}
