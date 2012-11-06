<?php
/*
Plugin Name: Easy Digital Downloads
Plugin URI: http://easydigitaldownloads.com
Description: Serve Digital Downloads Through WordPress
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 1.3.2.1
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

/* PHP Hack to Get Plugin Headers in the .POT File */
	$edd_plugin_header_translate = array(
		__( 'Easy Digital Downloads', 'edd' ),
    	__( 'Serve Digital Downloads Through WordPress', 'edd' ),
    	__( 'Pippin Williamson', 'edd' ),
    	__( 'http://easydigitaldownloads.com/', 'edd' ),
    );

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/
// Plugin version
if( !defined( 'EDD_VERSION' ) ) {
	define( 'EDD_VERSION', '1.3.2.1' );
}
// Plugin Folder URL
if( !defined( 'EDD_PLUGIN_URL' ) ) {
	define( 'EDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// Plugin Folder Path
if( !defined( 'EDD_PLUGIN_DIR' ) ) {
	define( 'EDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
// Plugin Root File
if( !defined( 'EDD_PLUGIN_FILE' ) ) {
	define( 'EDD_PLUGIN_FILE', __FILE__ );
}

/*
|--------------------------------------------------------------------------
| GLOBALS
|--------------------------------------------------------------------------
*/

global $edd_options;

/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function edd_textdomain() {
	// Set filter for plugin's languages directory
	$edd_lang_dir = dirname( plugin_basename( EDD_PLUGIN_FILE ) ) . '/languages/';
	$edd_lang_dir = apply_filters( 'edd_languages_directory', $edd_lang_dir );

	// Load the translations
	load_plugin_textdomain( 'edd', false, $edd_lang_dir );
}
add_action( 'init', 'edd_textdomain' );

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

include_once( EDD_PLUGIN_DIR . 'includes/register-settings.php' );
$edd_options = edd_get_settings();
include_once( EDD_PLUGIN_DIR . 'includes/install.php' );
include_once( EDD_PLUGIN_DIR . 'includes/actions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/deprecated-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/template-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/checkout-template.php' );
include_once( EDD_PLUGIN_DIR . 'includes/cart-template.php' );
include_once( EDD_PLUGIN_DIR . 'includes/cart-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/cart-actions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/class-edd-logging.php' );
include_once( EDD_PLUGIN_DIR . 'includes/ajax-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/formatting.php' );
include_once( EDD_PLUGIN_DIR . 'includes/widgets.php' );
include_once( EDD_PLUGIN_DIR . 'includes/mime-types.php' );
include_once( EDD_PLUGIN_DIR . 'includes/gateway-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/discount-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/payment-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/payment-actions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/misc-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/download-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/scripts.php' );
include_once( EDD_PLUGIN_DIR . 'includes/post-types.php' );
include_once( EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php' );
include_once( EDD_PLUGIN_DIR . 'includes/email-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/email-template.php' );
include_once( EDD_PLUGIN_DIR . 'includes/email-actions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/error-tracking.php' );
include_once( EDD_PLUGIN_DIR . 'includes/user-functions.php' );
include_once( EDD_PLUGIN_DIR . 'includes/query-filters.php' );
if( is_admin() ) {
	include_once( EDD_PLUGIN_DIR . 'includes/admin/add-ons.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/admin-actions.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/admin-notices.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/export-functions.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/thickbox.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/reporting/pdf-reports.php' );	
	include_once( EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/settings/settings.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php' );
} else {
	include_once( EDD_PLUGIN_DIR . 'includes/process-purchase.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/process-download.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/shortcodes.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/gateways/manual.php' );
	include_once( EDD_PLUGIN_DIR . 'includes/login-register.php' );
}
