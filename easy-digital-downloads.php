<?php
/*
Plugin Name: Easy Digital Downloads
Plugin URI: http://easydigitaldownloads.com
Description: Serve Digital Downloads Through WordPress
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 1.4.4.1
Text Domain: edd
Domain Path: languages

Easy Digital Downloads is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Easy Digital Downloads is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Easy Digital Downloads. If not, see <http://www.gnu.org/licenses/>.
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Easy_Digital_Downloads' ) ) :

/**
 * Main Easy_Digital_Downloads Class
 *
 * @since v1.4
 */

final class Easy_Digital_Downloads {


	/** Singleton *************************************************************/

	/**
	 * @var Easy_Digital_Downloads The one true Easy_Digital_Downloads
	 */
	private static $instance;


	/**
	 * EDD user roles and capabilities object
	 * @since 1.4.4.1
	 */
	private $roles;


	/**
	 * EDD cart fees object
	 * @since x.x
	 */
	public $fees;


	/**
	 * Main Easy_Digital_Downloads Instance
	 *
	 * Insures that only one instance of Easy_Digital_Downloads exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since v1.4
	 * @staticvar array $instance
	 * @uses Easy_Digital_Downloads::setup_globals() Setup the globals needed
	 * @uses Easy_Digital_Downloads::includes() Include the required files
	 * @uses Easy_Digital_Downloads::setup_actions() Setup the hooks and actions
	 * @see EDD()
	 * @return The one true Easy_Digital_Downloads
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Easy_Digital_Downloads;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->load_textdomain();
			self::$instance->roles = new EDD_Roles();
			self::$instance->fees = new EDD_Fees();
		}
		return self::$instance;
	}


	/**
	 * Setup plugin constants
	 *
	 * @since v1.4
	 * @access private
	 * @uses plugin_dir_path() To generate EDD plugin path
	 * @uses plugin_dir_url() To generate EDD plugin url
	 */
	private function setup_constants() {

		// Plugin version
		if( !defined( 'EDD_VERSION' ) )
			define( 'EDD_VERSION', '1.4.4' );

		// Plugin Folder URL
		if( !defined( 'EDD_PLUGIN_URL' ) )
			define( 'EDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Plugin Folder Path
		if( !defined( 'EDD_PLUGIN_DIR' ) )
			define( 'EDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Root File
		if( !defined( 'EDD_PLUGIN_FILE' ) )
			define( 'EDD_PLUGIN_FILE', __FILE__ );

	}




	/**
	 * Include required files
	 *
	 * @since v1.4
	 * @access private
	 * @uses is_admin() If in WordPress admin, load additional file
	 */
	private function includes() {

		global $edd_options;

		require_once EDD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$edd_options = edd_get_settings();
		require_once EDD_PLUGIN_DIR . 'includes/install.php';
		require_once EDD_PLUGIN_DIR . 'includes/actions.php';


		require_once EDD_PLUGIN_DIR . 'includes/deprecated-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/ajax-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/template-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/checkout/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/checkout/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/cart/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-fees.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-logging.php';
		require_once EDD_PLUGIN_DIR . 'includes/class-edd-roles.php';
		require_once EDD_PLUGIN_DIR . 'includes/formatting.php';
		require_once EDD_PLUGIN_DIR . 'includes/widgets.php';
		require_once EDD_PLUGIN_DIR . 'includes/mime-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
		require_once EDD_PLUGIN_DIR . 'includes/gateways/manual.php';
		require_once EDD_PLUGIN_DIR . 'includes/discount-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/payments/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/download-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/scripts.php';
		require_once EDD_PLUGIN_DIR . 'includes/post-types.php';
		require_once EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/template.php';
		require_once EDD_PLUGIN_DIR . 'includes/emails/actions.php';
		require_once EDD_PLUGIN_DIR . 'includes/error-tracking.php';
		require_once EDD_PLUGIN_DIR . 'includes/user-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/query-filters.php';
		require_once EDD_PLUGIN_DIR . 'includes/tax-functions.php';
		require_once EDD_PLUGIN_DIR . 'includes/process-purchase.php';
		require_once EDD_PLUGIN_DIR . 'includes/login-register.php';

		if( is_admin() ) {
			require_once EDD_PLUGIN_DIR . 'includes/admin/add-ons.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/admin-notices.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/export-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/thickbox.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/pdf-reports.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
			require_once EDD_PLUGIN_DIR . 'includes/admin/welcome.php';
		} else {
			require_once EDD_PLUGIN_DIR . 'includes/process-download.php';
			require_once EDD_PLUGIN_DIR . 'includes/shortcodes.php';
			require_once EDD_PLUGIN_DIR . 'includes/theme-compatibility.php';
		}

	}


	/**
	 * Loads the plugin language files
	 *
	 * @since v1.4
	 * @access private
	 * @uses dirname()
	 * @uses plugin_basename()
	 * @uses apply_filters()
	 * @uses load_textdomain()
	 * @uses get_locale()
	 * @uses load_plugin_textdomain()
	 *
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$edd_lang_dir = dirname( plugin_basename( EDD_PLUGIN_FILE ) ) . '/languages/';
		$edd_lang_dir = apply_filters( 'edd_languages_directory', $edd_lang_dir );


		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'edd' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'edd', $locale );

		// Setup paths to current locale file
		$mofile_local  = $edd_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/edd/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/edd folder
			load_textdomain( 'edd', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/easy-digital-downloads/languages/ folder
			load_textdomain( 'edd', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'edd', false, $edd_lang_dir );
		}

	}
}

endif; // End if class_exists check


/**
 * The main function responsible for returning the one true Easy_Digital_Downloads Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd = EDD(); ?>
 *
 * @since v1.4
 *
 * @return The one true Easy_Digital_Downloads Instance
 */

function EDD() {
	return Easy_Digital_Downloads::instance();
}

// Starts EDD running
EDD();
