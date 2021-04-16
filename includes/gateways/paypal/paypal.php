<?php
/**
 * PayPal Gateway
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

/**
 * Partner attribution ID
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
	define( 'EDD_PAYPAL_MERCHANT_ID', '' );
}

if ( ! defined( 'EDD_PAYPAL_SANDBOX_MERCHANT_ID' ) ) {
	define( 'EDD_PAYPAL_SANDBOX_MERCHANT_ID', '' );
}

/**
 * Partner client ID
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
require_once EDD_PLUGIN_DIR . 'includes/gateways/paypal/class-paypal-api.php';
