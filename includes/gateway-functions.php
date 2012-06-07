<?php
/**
 * Gateway Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Gateway Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Get Payment Gateways
 *
 * Rreturns a list of all available gateways.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_payment_gateways() {
	
	// default, built-in gateways
	$gateways = array(
		'paypal' => array('admin_label' => 'PayPal', 'checkout_label' => 'PayPal'),
		'manual' => array('admin_label' => __('Manual Payment', 'edd'), 'checkout_label' => __('Manual Payment', 'edd')),
	);
	
	return apply_filters('edd_payment_gateways', $gateways);

}


/**
 * Get Enabled Payment Gateways
 *
 * Returns a list of all enabled gateways.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_enabled_payment_gateways() {
	global $edd_options;
	$gateways = edd_get_payment_gateways();
	$enabled_gateways = isset( $edd_options['gateways'] ) ? $edd_options['gateways'] : '';
	$gateway_list = array();
	foreach($gateways as $key => $gateway) :
		if(isset($enabled_gateways[$key]) && $enabled_gateways[$key] == 1) :
			$gateway_list[$key] = $gateway;
		endif;
	endforeach;
	return $gateway_list;
}


/**
 * Is Gateway Active
 *
 * Checks whether a specified gateway is activated.
 *
 * @access      public
 * @since       1.0 
 * @param       string - The ID name of the gateway to check for
 * @return      boolean - true if enabled, false otherwise
*/

function edd_is_gateway_active($gateway) {
	$gateways = edd_get_enabled_payment_gateways();
	if(array_key_exists($gateway, $gateways)) {
		return true;
	}
	return false;
}


/**
 * Get gateway admin label
 *
 * Returns the admin label for the specified gateway.
 *
 * @access      public
 * @since       1.0.8.3
 * @param       string - The ID name of the gateway to retrieve a label for
 * @return      string
*/

function edd_get_gateway_admin_label($gateway) {
	$gateways = edd_get_enabled_payment_gateways();
	return $gateways[$gateway]['admin_label'];
}

/**
 * Send to Gateway
 *
 * Sends the registration data to the specified gateway.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_send_to_gateway($gateway, $payment_data) {
	// $gateway must match the ID used when registering the gateway
	do_action('edd_gateway_' . $gateway, $payment_data);
}