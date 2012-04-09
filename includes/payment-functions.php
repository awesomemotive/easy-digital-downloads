<?php
// retrieve payments from the database
function edd_get_payments( $offset = 0, $number = 20, $mode = 'live' ) {
	$payment_args = array(
		'post_type' => 'edd_payment', 
		'posts_per_page' => $number, 
		'post_status' => 'any', 
		'offset' => $offset,
		'meta_key' => '_edd_payment_mode',
		'meta_value' => $mode
	);
	$payments = get_posts($payment_args);
	if($payments) {
		return $payments;
	}
	return false;
}

// returns the total number of payments recorded
function edd_count_payments($mode) {
	$payments = edd_get_payments(0, -1, $mode);
	$count = 0;
	if($payments) {
		$count = count($payments);
	}
	return $count;
}

function edd_insert_payment($payment_data = array()) {

	if(empty($payment_data))
		return false;

	// construct the payment title
	if(isset($payment_data['user_info']['first_name']) || isset($payment_data['user_info']['last_name'])) {
		$payment_title = $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'];
	} else {
		$payment_title = $payment_data['user_email'];
	}
	
	if(isset($payment_data['status'])) {
		$status = 'pending';
	} else {
		$status = 'publish';
	}
	
	// create a blank payment
	$payment = wp_insert_post( array('post_title' => $payment_title, 'post_status' => $status, 'post_type' => 'edd_payment'));
	
	if($payment) {
		$payment_meta = array( 
			'amount' => $payment_data['price'], 
			'date' => $payment_data['date'], 
			'email' => $payment_data['user_email'],
			'key' => $payment_data['purchase_key'],
			'currency' => $payment_data['currency'],
			'downloads' => serialize($payment_data['downloads']),
			'user_info' => serialize($payment_data['user_info']),
			'user_id' => $payment_data['user_info']['id']
		);
		// record the payment details
		update_post_meta($payment, '_edd_payment_meta', apply_filters('_edd_payment_meta', $payment_meta));
		update_post_meta($payment, '_edd_payment_user_id', $payment_data['user_info']['id']);
		update_post_meta($payment, '_edd_payment_user_email', $payment_data['user_email']);
		update_post_meta($payment, '_edd_payment_purchase_key', $payment_data['purchase_key']);
		$mode = edd_is_test_mode() ? 'test' : 'live';
		update_post_meta($payment, '_edd_payment_mode', $mode);
		
		// clear the user's purchased cache
		delete_transient('edd_user_' . $payment_data['user_info']['id'] . '_purchases');
		
		if(!edd_is_test_mode()) {
			// increase purchase count and earnings
			foreach($payment_data['downloads'] as $download) {
				edd_increase_purchase_count($download);
				edd_increase_earnings($download, $payment_data['price']);
			}
		
			if(isset($payment_data['user_info']['discount'])) {
				edd_increase_discount_usage($payment_data['user_info']['discount']);
			}
		}
		
		do_action('edd_insert_payment', $payment_data);		
		
		return $payment; // return the ID
	}
	// return false if no payment was inserted
	return false;
}

// updates the purchase date for a payment. Used primarily for adding new downloads to a purchase
function edd_update_purchased_downloads($data) {
	if(wp_verify_nonce($data['edd-payment-nonce'], 'edd_payment_nonce')) {
		$payment_id = $_POST['payment-id'];
		$payment_data = get_post_meta($payment_id, '_edd_payment_meta', true);
		$payment_data['downloads'] = serialize($_POST['edd-purchased-downloads']);
		update_post_meta($payment_id, '_edd_payment_meta', $payment_data);
	}
}
add_action('edd_edit_payment', 'edd_update_purchased_downloads');

function edd_update_payment_status($payment_id, $status = 'pending') {
	
	if($status == 'completed' || $status == 'complete') {
		$status = 'publish';
	}
	
	wp_update_post(array('ID' => $payment_id, 'post_status' => $status));
	
	// send email with secure download link
	edd_email_download_link($payment_id);
	
	// empty the shopping cart
	edd_empty_cart();
	
	do_action('edd_update_payment_status', $payment_id, $status);
}

function edd_check_for_existing_payment($payment_id) {
	$payment = get_post($payment_id);
	if($payment && $payment->post_status == 'publish') {
		return true; // payment exists
	}
	return false; // this payment doesn't exist
}

// retrieves the status of a payment
function edd_get_payment_status($payment = OBJECT) {
	if(!is_object($payment))
		return;
	switch($payment->post_status) :
		case 'pending' :
			return __('pending', 'edd');
			break;
		case 'publish' :
			return __('complete', 'edd');
			break;
		default:
			return;
			break;
	endswitch;
}


function edd_get_earnings_by_date($month_num, $year) {
	$sales = get_posts(
		array(
			'post_type' => 'edd_payment', 
			'posts_per_page' => -1, 
			'year' => $year, 
			'monthnum' => $month_num, 
			'meta_key' => '_edd_payment_mode',
			'meta_value' => 'live'
		)
	);
	$total = 0;
	if($sales) {
		foreach($sales as $sale) {
			$sale_meta = get_post_meta($sale->ID, '_edd_payment_meta', true);
			$amount = $sale_meta['amount'];
			$total = $total + $amount;
		}
	}
	return $total;
}
