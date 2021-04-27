<?php
/**
 * PayPal Commerce Gateway
 *
 * Loads all required files for PayPal Commerce. This gateway uses:
 *
 * Onboarding: "Build Onboarding into Software"
 * @link https://developer.paypal.com/docs/platforms/seller-onboarding/build-onboarding/
 *
 * JavaScript SDK
 * @link https://developer.paypal.com/docs/business/javascript-sdk/javascript-sdk-reference/
 *
 * - REST API
 * @link https://developer.paypal.com/docs/api/overview/
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\PayPal;

/**
 * Partner attribution ID
 *
 * @link https://developer.paypal.com/docs/api/reference/api-requests/#paypal-partner-attribution-id
 */
if ( ! defined( 'EDD_PAYPAL_PARTNER_ATTRIBUTION_ID' ) ) {
	define( 'EDD_PAYPAL_PARTNER_ATTRIBUTION_ID', '' );
}

if ( ! defined( 'EDD_PAYPAL_SANDBOX_PARTNER_ATTRIBUTION_ID' ) ) {
	define( 'EDD_PAYPAL_SANDBOX_PARTNER_ATTRIBUTION_ID', '' );
}

/**
 * Partner merchant ID
 */
if ( ! defined( 'EDD_PAYPAL_MERCHANT_ID' ) ) {
	define( 'EDD_PAYPAL_MERCHANT_ID', '8NYVREYC28TBQ' );
}

if ( ! defined( 'EDD_PAYPAL_SANDBOX_MERCHANT_ID' ) ) {
	define( 'EDD_PAYPAL_SANDBOX_MERCHANT_ID', 'NUGJTUUBANR46' );
}

/**
 * Partner client ID
 * @todo I think we can remove this?
 */
if ( ! defined( 'EDD_PAYPAL_PARTNER_CLIENT_ID' ) ) {
	define( 'EDD_PAYPAL_PARTNER_CLIENT_ID', '' );
}

if ( ! defined( 'EDD_PAYPAL_SANDBOX_PARTNER_CLIENT_ID' ) ) {
	define( 'EDD_PAYPAL_SANDBOX_PARTNER_CLIENT_ID', '' );
}

/**
 * Include PayPal gateway files
 */
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-api-exception.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-authentication-exception.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-gateway-exception.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/buy-now.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/checkout-actions.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-paypal-api.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-token.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/functions.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/gateway-filters.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/refunds.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/scripts.php';

if ( is_admin() ) {
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/connect.php';
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/scripts.php';
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/settings.php';
}
