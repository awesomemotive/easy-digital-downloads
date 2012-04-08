<?php
/*
Plugin Name: Easy Digital Downloads
Plugin URI: http://easydigitaldownloads.com
Description: Serve Digital Downloads Through WordPress
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 1.0-beta
*/


ini_set('display_errors', 'on');

/*************************************
* CONSTANTS
*************************************/

// plugin folder url
if(!defined('EDD_PLUGIN_URL')) {
	define('EDD_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}
// plugin folder path
if(!defined('EDD_PLUGIN_DIR')) {
	define('EDD_PLUGIN_DIR', dirname(__FILE__));
}
// plugin root file
if(!defined('EDD_PLUGIN_FILE')) {
	define('EDD_PLUGIN_FILE', __FILE__);
}

/*************************************
* globals
*************************************/

global $wpdb, $edd_payments_db_name, $edd_payments_db_version;
$edd_payments_db_name = $wpdb->prefix . 'edd_payments';
$edd_payments_db_version = 1.0;

/*****************************************
load the languages
*****************************************/

load_plugin_textdomain( 'edd', false, dirname( plugin_basename( EDD_PLUGIN_FILE ) ) . '/languages/' );


/*************************************
* includes
*************************************/

include(EDD_PLUGIN_DIR . '/includes/register-settings.php');
$edd_options = edd_get_settings();
include(EDD_PLUGIN_DIR . '/includes/template-functions.php');
include(EDD_PLUGIN_DIR . '/includes/checkout-template.php');
include(EDD_PLUGIN_DIR . '/includes/cart-template.php');
include(EDD_PLUGIN_DIR . '/includes/cart-functions.php');
include(EDD_PLUGIN_DIR . '/includes/cart-actions.php');
include(EDD_PLUGIN_DIR . '/includes/ajax-functions.php');
include(EDD_PLUGIN_DIR . '/includes/widgets.php');
include(EDD_PLUGIN_DIR . '/includes/mime-types.php');
include(EDD_PLUGIN_DIR . '/includes/gateway-functions.php');
include(EDD_PLUGIN_DIR . '/includes/discount-functions.php');
include(EDD_PLUGIN_DIR . '/includes/payment-functions.php');
include(EDD_PLUGIN_DIR . '/includes/misc-functions.php');
include(EDD_PLUGIN_DIR . '/includes/download-functions.php');
include(EDD_PLUGIN_DIR . '/includes/scripts.php');
include(EDD_PLUGIN_DIR . '/includes/post-types.php');
include(EDD_PLUGIN_DIR . '/includes/plugin-compatibility.php');
include(EDD_PLUGIN_DIR . '/includes/email-functions.php');
if(is_admin()) {
	include(EDD_PLUGIN_DIR . '/includes/admin-actions.php');
	include(EDD_PLUGIN_DIR . '/includes/metabox.php');
	include(EDD_PLUGIN_DIR . '/includes/admin-pages.php');
	include(EDD_PLUGIN_DIR . '/includes/admin-pages/payments-history.php');
	include(EDD_PLUGIN_DIR . '/includes/admin-pages/settings.php');
	include(EDD_PLUGIN_DIR . '/includes/admin-pages/discount-codes.php');
	include(EDD_PLUGIN_DIR . '/includes/admin-pages/reports.php');
	include(EDD_PLUGIN_DIR . '/includes/admin-notices.php');
	include(EDD_PLUGIN_DIR . '/includes/dashboard-columns.php');
	include(EDD_PLUGIN_DIR . '/includes/thickbox.php');
	include(EDD_PLUGIN_DIR . '/includes/graphing.php');
} else {
	include(EDD_PLUGIN_DIR . '/includes/process-purchase.php');
	include(EDD_PLUGIN_DIR . '/includes/process-download.php');
	include(EDD_PLUGIN_DIR . '/includes/shortcodes.php');
	include(EDD_PLUGIN_DIR . '/includes/error-tracking.php');
	include(EDD_PLUGIN_DIR . '/includes/gateways/paypal.php');
	include(EDD_PLUGIN_DIR . '/includes/gateways/manual.php');
	include(EDD_PLUGIN_DIR . '/includes/login-register.php');
}