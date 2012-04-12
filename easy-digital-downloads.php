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
	define('EDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}
// plugin root file
if(!defined('EDD_PLUGIN_FILE')) {
	define('EDD_PLUGIN_FILE', __FILE__);
}

/*****************************************
load the languages
*****************************************/

load_plugin_textdomain( 'edd', false, dirname( plugin_basename( EDD_PLUGIN_FILE ) ) . '/languages/' );


/*************************************
* includes
*************************************/

include_once(EDD_PLUGIN_DIR . 'includes/register-settings.php');
$edd_options = edd_get_settings();
include_once(EDD_PLUGIN_DIR . 'includes/install.php');
include_once(EDD_PLUGIN_DIR . 'includes/template-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/checkout-template.php');
include_once(EDD_PLUGIN_DIR . 'includes/cart-template.php');
include_once(EDD_PLUGIN_DIR . 'includes/cart-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/cart-actions.php');
include_once(EDD_PLUGIN_DIR . 'includes/ajax-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/widgets.php');
include_once(EDD_PLUGIN_DIR . 'includes/mime-types.php');
include_once(EDD_PLUGIN_DIR . 'includes/gateway-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/discount-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/payment-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/misc-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/download-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/scripts.php');
include_once(EDD_PLUGIN_DIR . 'includes/post-types.php');
include_once(EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php');
include_once(EDD_PLUGIN_DIR . 'includes/email-functions.php');
include_once(EDD_PLUGIN_DIR . 'includes/error-tracking.php');
if(is_admin()) {
	include_once(EDD_PLUGIN_DIR . 'includes/admin-actions.php');
	include_once(EDD_PLUGIN_DIR . 'includes/metabox.php');
	include_once(EDD_PLUGIN_DIR . 'includes/admin-pages.php');
	include_once(EDD_PLUGIN_DIR . 'includes/admin-pages/payments-history.php');
	include_once(EDD_PLUGIN_DIR . 'includes/admin-pages/settings.php');
	include_once(EDD_PLUGIN_DIR . 'includes/admin-pages/discount-codes.php');
	include_once(EDD_PLUGIN_DIR . 'includes/admin-pages/reports.php');
	include_once(EDD_PLUGIN_DIR . 'includes/admin-notices.php');
	include_once(EDD_PLUGIN_DIR . 'includes/dashboard-columns.php');
	include_once(EDD_PLUGIN_DIR . 'includes/thickbox.php');
	include_once(EDD_PLUGIN_DIR . 'includes/graphing.php');
} else {
	include_once(EDD_PLUGIN_DIR . 'includes/process-purchase.php');
	include_once(EDD_PLUGIN_DIR . 'includes/process-download.php');
	include_once(EDD_PLUGIN_DIR . 'includes/shortcodes.php');
	include_once(EDD_PLUGIN_DIR . 'includes/gateways/paypal.php');
	include_once(EDD_PLUGIN_DIR . 'includes/gateways/manual.php');
	include_once(EDD_PLUGIN_DIR . 'includes/login-register.php');
}