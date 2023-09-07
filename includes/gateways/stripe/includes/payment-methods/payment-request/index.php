<?php
/**
 * Payment Request Button
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/ajax.php';
require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/checkout.php';
require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/functions.php';
require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/template.php';
require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/shortcode.php';

if ( is_admin() ) {
	require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/admin/settings.php';
}
