<?php
/**
 * Payment Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Payment Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/

// retrieve payments from the database
function edd_get_payments( $offset = 0, $number = 20, $mode = 'live', $orderby = 'ID', $order = 'DESC' ) {
	$payment_args = array(
		'post_type' => 'edd_payment', 
		'posts_per_page' => $number, 
		'post_status' => 'any', 
		'offset' => $offset,
		'meta_key' => '_edd_payment_mode',
		'meta_value' => $mode,
		'order' => $order,
		'orderby' => $orderby
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
		$status = $payment_data['status'];
	} else {
		$status = 'pending';
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
			'cart_details' => serialize($payment_data['cart_details']),
			'user_id' => $payment_data['user_info']['id']
		);
		
		if ($_SERVER['HTTP_X_FORWARD_FOR']) {
			$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		// record the payment details
		update_post_meta($payment, '_edd_payment_meta', apply_filters('edd_payment_meta', $payment_meta, $payment_data));
		update_post_meta($payment, '_edd_payment_user_id', $payment_data['user_info']['id']);
		update_post_meta($payment, '_edd_payment_user_email', $payment_data['user_email']);
		update_post_meta($payment, '_edd_payment_user_ip', $ip);
		update_post_meta($payment, '_edd_payment_purchase_key', $payment_data['purchase_key']);
		$mode = edd_is_test_mode() ? 'test' : 'live';
		update_post_meta($payment, '_edd_payment_mode', $mode);
		
		// clear the user's purchased cache
		delete_transient('edd_user_' . $payment_data['user_info']['id'] . '_purchases');
		
		do_action('edd_insert_payment', $payment, $payment_data);		
		
		return $payment; // return the ID
	}
	// return false if no payment was inserted
	return false;
}

// updates a payment status, and performs all necessary functions to mark it as complete, and to finish a purchase
function edd_update_payment_status($payment_id, $status = 'publish') {
	
	if($status == 'completed' || $status == 'complete') {
		$status = 'publish';
	}
	
	$payment = get_post($payment_id);
	if($payment->post_status == 'publish') {
		// for some reason this is occasionally coming back as true even when the payment is not		
		return;
	}
	$payment_data = get_post_meta($payment_id, '_edd_payment_meta', true);
	$downloads = maybe_unserialize($payment_data['downloads']);
	$user_info = maybe_unserialize($payment_data['user_info']);
	$cart_details = maybe_unserialize($payment_data['cart_details']);
	
	wp_update_post(array('ID' => $payment_id, 'post_status' => $status));
	
	if(!edd_is_test_mode()) {
		// increase purchase count and earnings
		foreach($downloads as $download) {
			edd_record_sale_in_log($download['id'], $payment_id, $user_info, $payment_data['date']);
			edd_increase_purchase_count($download['id']);
			$amount = null;
			if(is_array($cart_details)) {
				$cart_item_id = array_search($download['id'], $cart_details);
				$amount = isset($cart_details[$cart_item_id]['price']) ? $cart_details[$cart_item_id]['price'] : null;
			}
			$amount = edd_get_download_final_price($download['id'], $user_info, $amount);
			edd_increase_earnings($download['id'], $amount);
		}
	
		if(isset($payment_data['user_info']['discount'])) {
			edd_increase_discount_usage($payment_data['user_info']['discount']);
		}
	}
	
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

/*
* Checks whether a payment has been marked as complete
* @since v1.0.8
* @param $payment_id INT the ID number of the payment to check
* @return bool true if complete, false otherwise
*/
function edd_is_payment_complete($payment_id) {
	$payment = get_post($payment_id);
	if( $payment )
		if( $payment->post_status == 'publish' )
			return true;
	return false;
}
