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

namespace EDD\Gateways\PayPal;

/**
 * Partner attribution ID
 *
 * @link https://developer.paypal.com/docs/api/reference/api-requests/#paypal-partner-attribution-id
 */
if ( ! defined( 'EDD_PAYPAL_PARTNER_ATTRIBUTION_ID' ) ) {
	define( 'EDD_PAYPAL_PARTNER_ATTRIBUTION_ID', 'EasyDigitalDownloadsLLC_PPFM_pcp' );
}

/**
 * Partner merchant ID
 */
if ( ! defined( 'EDD_PAYPAL_MERCHANT_ID' ) ) {
	define( 'EDD_PAYPAL_MERCHANT_ID', 'GFJPUJ4SNZYJN' );
}

if ( ! defined( 'EDD_PAYPAL_SANDBOX_MERCHANT_ID' ) ) {
	define( 'EDD_PAYPAL_SANDBOX_MERCHANT_ID', 'NUGJTUUBANR46' );
}

/**
 * Include PayPal gateway files
 */
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-api-exception.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-authentication-exception.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-gateway-exception.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-invalid-merchant-details.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/exceptions/class-missing-merchant-details.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/buy-now.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/checkout-actions.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-account-status-validator.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-merchant-account.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-paypal-api.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-token.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/deprecated.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/functions.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/gateway-filters.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/refunds.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/scripts.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/webhooks/functions.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/integrations.php';
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/ipn.php';

if ( is_admin() ) {
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/connect.php';
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/notices.php';
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/scripts.php';
	require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/admin/settings.php';
}
