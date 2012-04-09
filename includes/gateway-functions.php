<?php

// returns a list of all available gateways
function edd_get_payment_gateways() {
	
	// default, built-in gateways
	$gateways = array(
		'paypal' => array('admin_label' => 'PayPal', 'checkout_label' => 'PayPal'),
		'manual' => array('admin_label' => __('Manual Payment', 'edd'), 'checkout_label' => __('Manual Payment', 'edd')),
	);
	
	$gateways = apply_filters('edd_payment_gateways', $gateways);
	
	return $gateways;
}

// returns a list of all enabled gateways
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

/*
* Checks whether a specified gateway is activated
* * Uses edd_get_enabled_payment_gateways()
* Parameter - string The ID name of the gateway to check for
* Return bool - true if enabled, false otherwise
*/
function edd_is_gateway_active($gateway) {
	$gateways = edd_get_enabled_payment_gateways();
	if(array_key_exists($gateway, $gateways)) {
		return true;
	}
	return false;
}


// sends the registration data to the specified gateway
function edd_send_to_gateway($gateway, $payment_data) {
	// $gateway must match the ID used when registering the gateway
	do_action('edd_gateway_' . $gateway, $payment_data);
}