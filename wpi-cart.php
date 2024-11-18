<?php
/**
 * Plugin Name: WPI Cart (based on Easy Digital Downloads 2.5.17)
 * Description: The easiest way to sell digital products with WordPress.
 * Version: 1.0
 * Domain Path: languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WPI_Cart' ) ) :

final class WPI_Cart {
	/**
	 * @var WPI_Cart The one true WPI_Cart
	 */
	private static $instance;

	/**
	 * EDD Roles Object.
	 *
	 * @var object|EDD_Roles
	 */
	public $roles;

	/**
	 * EDD Cart Fees Object.
	 *
	 * @var object|EDD_Fees
	 */
	public $fees;

	/**
	 * EDD API Object.
	 *
	 * @var object|EDD_API
	 */
	public $api;

	/**
	 * EDD HTML Session Object.
	 *
	 * This holds cart items, purchase sessions, and anything else stored in the session.
	 *
	 * @var object|EDD_Session
	 */
	public $session;

	/**
	 * EDD HTML Element Helper Object.
	 *
	 * @var object|EDD_HTML_Elements
	 */
	public $html;

	/**
	 * EDD Emails Object.
	 *
	 * @var object|EDD_Emails
	 */
	public $emails;

	/**
	 * EDD Email Template Tags Object.
	 *
	 * @var object|EDD_Email_Template_Tags
	 */
	public $email_tags;

	/**
	 * EDD Customers DB Object.
	 *
	 * @var object|EDD_DB_Customers
	 */
	public $customers;

	/**
	 * EDD Customer meta DB Object.
	 *
	 * @var object|EDD_DB_Customer_Meta
	 */
	public $customer_meta;

	/**
	 * Main WPI_Cart Instance.
	 *
	 * Insures that only one instance of WPI_Cart exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @static
	 * @staticvar array $instance
	 * @uses WPI_Cart::setup_constants() Setup the constants needed.
	 * @uses WPI_Cart::includes() Include the required files.
	 * @uses WPI_Cart::load_textdomain() load the language files.
	 * @see EDD()
	 * @return object|WPI_Cart The one true WPI_Cart
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPI_Cart ) ) {
			self::$instance = new WPI_Cart;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->roles      = new EDD_Roles();
			self::$instance->fees       = new EDD_Fees();
			self::$instance->api        = new EDD_API();
			self::$instance->session    = new EDD_Session();
			self::$instance->html       = new EDD_HTML_Elements();
			self::$instance->emails     = new EDD_Emails();
			self::$instance->email_tags = new EDD_Email_Template_Tags();
			self::$instance->customers  = new EDD_DB_Customers();
			self::$instance->customer_meta = new EDD_DB_Customer_Meta();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-digital-downloads' ), '1.6' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-digital-downloads' ), '1.6' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'EDD_VERSION' ) ) {
			define( 'EDD_VERSION', '2.5.106' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'EDD_PLUGIN_DIR' ) ) {
			define( 'EDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'EDD_PLUGIN_URL' ) ) {
			define( 'EDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'EDD_PLUGIN_FILE' ) ) {
			define( 'EDD_PLUGIN_FILE', __FILE__ );
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
	 * @return void
	 */
	private function includes() {
		global $edd_options;

		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$edd_options = edd_get_settings();

		require_once EDD_PLUGIN_DIR . 'includes/actions.php';
		if( file_exists( EDD_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/deprecated-functions.php';
		}
		require_once EDD_PLUGIN_DIR . 'includes/ajax-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/api/class-edd-api.php';
		require_once EDD_PLUGIN_DIR . 'includes/template-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/checkout/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/checkout/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-db.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-db-customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-db-customer-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-customer.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-download.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-cache-helper.php';
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once EDD_PLUGIN_DIR . 'includes/class-edd-cli.php';
		}
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-cron.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-fees.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-html-elements.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-logging.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-session.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-stats.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-roles.php';
		require_once EDD_PLUGIN_DIR . 'includes/country-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/formatting.php';
		require_once EDD_PLUGIN_DIR . 'includes/widgets.php';
		require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/mime-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/functions.php';
		if ( version_compare( phpversion(), 5.3, '>' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/gateways/amazon-payments.php';
		}
		require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/manual.php';
		require_once EDD_PLUGIN_DIR . 'includes/discount-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/class-edd-payment.php';
		require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/download-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/scripts.php';
		require_once EDD_PLUGIN_DIR . 'includes/post-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/class-edd-emails.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/class-edd-email-tags.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/error-tracking.php';
		require_once EDD_PLUGIN_DIR . 'includes/user-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/query-filters.php';
		require_once EDD_PLUGIN_DIR . 'includes/tax-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/process-purchase.php';
		require_once EDD_PLUGIN_DIR . 'includes/login-register.php';
		require_once EDD_PLUGIN_DIR . 'includes/shortcodes.php';

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/admin/add-ons.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/admin-footer.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/thickbox.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/actions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/pdf-reports.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-edd-graph.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-edd-pie-graph.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/tracking.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/tools.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/plugins.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-heartbeat.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/tools/tools-actions.php';
		} else {
			require_once EDD_PLUGIN_DIR . 'includes/process-download.php';
			require_once EDD_PLUGIN_DIR . 'includes/theme-compatibility.php';
		}

		require_once EDD_PLUGIN_DIR . 'includes/class-edd-register-meta.php';
		require_once EDD_PLUGIN_DIR . 'includes/install.php';
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @access public
	 * @return void
	 */
	public function load_textdomain() {

		add_filter( 'load_textdomain_mofile', array( $this, 'load_old_textdomain' ), 10, 2 );

		// Set filter for plugin's languages directory.
        $plugin_path = dirname(__FILE__);
        $edd_lang_dir = $plugin_path . '/languages/';

		// Traditional WordPress plugin locale filter.
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'easy-digital-downloads' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'easy-digital-downloads', $locale );

		// Look for wp-content/languages/edd/easy-digital-downloads-{lang}_{country}.mo
		$mofile_global1 = WP_LANG_DIR . '/edd/easy-digital-downloads-' . $locale . '.mo';

		// Look for wp-content/languages/edd/edd-{lang}_{country}.mo
		$mofile_global2 = WP_LANG_DIR . '/edd/edd-' . $locale . '.mo';

		// Look in wp-content/languages/plugins/easy-digital-downloads
		$mofile_global3 = WP_LANG_DIR . '/plugins/easy-digital-downloads/' . $mofile;

        $mofile_local = $edd_lang_dir . $mofile;

		if ( file_exists( $mofile_global1 ) ) {

			load_textdomain( 'easy-digital-downloads', $mofile_global1 );

		} elseif ( file_exists( $mofile_global2 ) ) {

			load_textdomain( 'easy-digital-downloads', $mofile_global2 );

		} elseif ( file_exists( $mofile_global3 ) ) {

			load_textdomain( 'easy-digital-downloads', $mofile_global3 );

		}
        elseif ( file_exists( $mofile_local ) ) {

            load_textdomain( 'easy-digital-downloads', $mofile_local );

        }
        else {
			// Load the default language files.
			load_plugin_textdomain( 'easy-digital-downloads', false, $edd_lang_dir );
		}

	}

	/**
	 * Load a .mo file for the old textdomain if one exists.
	 *
	 * h/t: https://github.com/10up/grunt-wp-plugin/issues/21#issuecomment-62003284
	 */
	function load_old_textdomain( $mofile, $textdomain ) {

		if ( $textdomain === 'easy-digital-downloads' && ! file_exists( $mofile ) ) {
			$mofile = dirname( $mofile ) . DIRECTORY_SEPARATOR . str_replace( $textdomain, 'edd', basename( $mofile ) );
		}

		return $mofile;
	}

}

endif;


/**
 * The main function for that returns WPI_Cart
 *
 * The main function responsible for returning the one true WPI_Cart
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd = EDD(); ?>
 *
* @return object|WPI_Cart The one true WPI_Cart Instance.
 */
function EDD() {
	return WPI_Cart::instance();
}

// Get EDD Running.
EDD();
