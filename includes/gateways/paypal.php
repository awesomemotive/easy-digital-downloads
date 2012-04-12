<?php

// PayPal Standard does not need a CC form, so remove it
function edd_paypal_remove_cc_form() {
	// we only register the action so that the default CC form is not shown
}
add_action('edd_paypal_cc_form', 'edd_paypal_remove_cc_form');

function edd_process_paypal_purchase($purchase_data) {
	global $edd_options;

	/* 
	* purchase data comes in like this
	*
	$purchase_data = array(
		'downloads' => array of download IDs,
		'price' => total price of cart contents,
		'purchase_key' =>  // random key
		'user_email' => $user_email,
		'date' => date('Y-m-d H:i:s'),
		'user_id' => $user_id,
		'post_data' => $_POST,
		'user_info' => array of user's information and used discount code
		'cart_details' => array of cart details,
	);
	*/
	
	$payment_data = array( 
		'price' => $purchase_data['price'], 
		'date' => $purchase_data['date'], 
		'user_email' => $purchase_data['user_email'],
		'purchase_key' => $purchase_data['purchase_key'],
		'currency' => $edd_options['currency'],
		'downloads' => $purchase_data['downloads'],
		'user_info' => $purchase_data['user_info'],
		'status' => 'pending'
	);
	
	// record the pending payment
	$payment = edd_insert_payment($payment_data);
	
	if($payment) {
		// only send to paypal if the pending payment is created successfully
		$listener_url = add_query_arg('edd-listener', 'IPN', home_url());
		$return_url = add_query_arg('payment-confirmation', 'paypal', get_permalink($edd_options['success_page']));
		$cart_summary = edd_get_purchase_summary($purchase_data, $false);		
		
		// one time payment
		if(edd_is_test_mode()) {
			$paypal_redirect = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick';
		} else {
			$paypal_redirect = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick';
		}
		$paypal_redirect .= '&amount=' . $purchase_data['price'];
		$paypal_redirect .= '&business=' . urlencode($edd_options['paypal_email']);
		$paypal_redirect .= '&item_name=' . urlencode($cart_summary);
		$paypal_redirect .= '&email=' . $purchase_data['user_email'];
		$paypal_redirect .= '&no_shipping=1&no_note=1&item_number=' . $purchase_data['purchase_key'];
		$paypal_redirect .= '&currency_code=' . $edd_options['currency'];
		$paypal_redirect .= '&charset=UTF-8&return=' . urlencode($return_url);
		$paypal_redirect .= '&notify_url=' . urlencode($listener_url);
		$paypal_redirect .= '&rm=2&custom=' . $payment;
		// Redirect to paypal
		wp_redirect($paypal_redirect);
		exit;
		
	} else {
		// if errors are present, send the user back to the purchase page so they can be corrected
		edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
	}
}
add_action('edd_gateway_paypal', 'edd_process_paypal_purchase');

function edd_confirm_paypal_payment($content) {
	// this is an extra check that runs on the confirmation page to make sure the user's payment is updated to complete
	if(isset($_POST['txn_type']) && $_POST['txn_type'] == 'web_accept') {
	
		global $edd_options;
	
		$payment_id 		= $_POST['custom'];
		$purchase_key	 	= $_POST['item_number'];
		$amount 			= $_POST['mc_gross'];
		$payment_status 	= $_POST['payment_status'];
		$currency_code		= $_POST['mc_currency'];
		
		if($currency_code != $edd_options['currency']) {
			return __('There was an error and your payment cannot be confirmed. Please contact site adminstrators.', 'edd');
		}
				
		// set the payment to complete. This also sends the emails
		edd_update_payment_status($payment_id, 'publish');
		
	}
	return $content;
}
add_filter('edd_payment_confirm_paypal', 'edd_confirm_paypal_payment');

// listens for a PayPal IPN requests and then sends to the processing function
function edd_listen_for_paypal_ipn() {
	global $edd_options;
	
	// regular PayPal IPN
	if(!isset($edd_options['paypal_alternate_verification'])) {
		
		if(isset($_GET['edd-listener']) && $_GET['edd-listener'] == 'IPN') {
			do_action('edd_verify_paypal_ipn');
		}
		
	// alternate purchase verification	
	} else { 
		if(isset($_GET['tx']) && isset($_GET['st']) && isset($_GET['amt']) && isset($_GET['cc']) && isset($_GET['cm']) && isset($_GET['item_number'])) {
			// we are using the alternate method of verifying PayPal purchases
			
			// setup each of the variables from PayPal
			$payment_status = $_GET['st'];
			$paypal_amount = $_GET['amt'];
			$payment_id = $_GET['cm'];
			$purchase_key = $_GET['item_number'];
			$currency = $_GET['cc'];
			
			// retrieve the meta info for this payment
			$payment_meta = get_post_meta($payment_id, '_edd_payment_meta', true);
			$payment_amount = edd_format_amount($payment_meta['amount']);
			if($currency != $edd_options['currency']) {
				return; // the currency code is invalid
			}
			if($paypal_amount != $payment_amount) {
				return; // the prices don't match
			}
			if($purchase_key != $payment_meta['key']) {
				return; // purchase keys don't match
			}
			if(strtolower($payment_status) != 'completed') {
				return; // payment wasn't completed
			}
			
			// everything has been verified, update the payment to "complete"
			edd_update_payment_status($payment_id, 'publish');
		}
	}
}
add_action('init', 'edd_listen_for_paypal_ipn');

function edd_process_paypal_ipn() {

	global $edd_options;
	
	// instantiate the IpnListener class
	if(!class_exists('IpnListener')) {
		include_once(EDD_PLUGIN_DIR . 'includes/gateways/libraries/paypal/ipnlistener.php');
	}
	
	$listener = new IpnListener();

	if(edd_is_test_mode()) {
		$listener->use_sandbox = true;
	}
	
	
	if(isset($edd_options['ssl'])) {
		$listener->use_ssl = false;
	}
	// to post using the fsockopen() function rather than cURL, use:
	if(isset($edd_options['paypal_disable_curl'])) {
		$listener->use_curl = false;
	}
	
	try {
		$listener->requirePostMethod();
		$verified = $listener->processIpn();
	} catch (Exception $e) {
		wp_mail(get_bloginfo('admin_email'), 'IPN Error', $e->getMessage());
		exit(0);
	}

	if ($verified) {
		$payment_id 		= $_POST['custom'];
		$purchase_key	 	= $_POST['item_number'];
		$amount 			= $_POST['mc_gross'];
		$payment_status 	= $_POST['payment_status'];
		$currency_code		= strtolower($_POST['mc_currency']);
		
		if($currency_code != strtolower($edd_options['currency'])) {
			return; // the currency code is invalid
		}
				
		if(isset($_POST['txn_type']) && $_POST['txn_type'] == 'web_accept') {
			if(strtolower($payment_status) == 'completed') {
				// set the payment to complete. This also sends the emails
				edd_update_payment_status($payment_id, 'publish');
			}
		}

	} else {
		wp_mail(get_bloginfo('admin_email'), __('Invalid IPN', 'edd'), $listener->getTextReport());
	}
}
add_action('edd_verify_paypal_ipn', 'edd_process_paypal_ipn');