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


/**
 * Get Payments
 *
 * Retrieve payments from the database.
 *
 * @access      public
 * @since       1.0 
 * @return      object
*/

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


/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @access      public
 * @since       1.0 
 * @return      integer
*/

function edd_count_payments($mode) {
	$payments = edd_get_payments(0, -1, $mode);
	$count = 0;
	if($payments) {
		$count = count($payments);
	}
	return $count;
}


/**
 * Insert Payment
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

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
		
		if ( isset( $_SERVER['HTTP_X_FORWARD_FOR'] ) ) {
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
		
		$gateway = isset( $_POST['edd-gateway'] ) ? $_POST['edd-gateway'] : ''; 
		update_post_meta($payment, '_edd_payment_gateway', $gateway);
		
		// clear the user's purchased cache
		delete_transient('edd_user_' . $payment_data['user_info']['id'] . '_purchases');
		
		do_action('edd_insert_payment', $payment, $payment_data);		
		
		return $payment; // return the ID
	}
	// return false if no payment was inserted
	return false;
}


/**
 * Update Payment Status
 *
 * Updates a payment status, and performs all necessary functions to mark it as complete, and to finish a purchase.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_update_payment_status($payment_id, $status = 'publish') {
	
	if($status == 'completed' || $status == 'complete') {
		$status = 'publish';
	}
	
	$payment = get_post($payment_id);
	if($payment->post_status == 'publish') {
		// for some reason this is occasionally coming back as true even when the payment is not		
		//return;
	}
	
	$old_status = $payment->post_status;	
	
	do_action('edd_before_payment_status_change', $payment_id, $status, $old_status);	
	
	wp_update_post(array('ID' => $payment_id, 'post_status' => $status));
	
	do_action('edd_update_payment_status', $payment_id, $status, $old_status);
}



/**
 * Undos a purchase, including the decrease of sale and earning stats
 *
 * Used for when refunding or deleting a purchase
 *
 * @access      public
 * @since       1.0.8.1
 * @param       int $download_id - the ID number of the download
 * @param       int $payment_id - the ID number of the purchase
 * @return      
*/

function edd_undo_purchase( $download_id, $payment_id ) {
	
	$payment = get_post( $payment_id );
	if( edd_get_payment_status( $payment ) == 'refunded' )
		return; // payment has already been reversed
	
	edd_decrease_purchase_count( $download_id );
				
	$purchase_meta = get_post_meta( $payment_id, '_edd_payment_meta', true );
	
	$user_purchase_info = maybe_unserialize( $purchase_meta['user_info'] );
	
	$cart_details = maybe_unserialize( $purchase_meta['cart_details'] );					
			
	$amount = null;
	if(is_array($cart_details)) {
		$cart_item_id = array_search($download_id, $cart_details);
		$amount = isset($cart_details[$cart_item_id]['price']) ? $cart_details[$cart_item_id]['price'] : null;
	}				
					
	$amount = edd_get_download_final_price( $download_id, $user_purchase_info, $amount );
	
	edd_decrease_earnings( $download_id, $amount );
	
}


/**
 * Check For Existing Payment
 *
 * @access      public
 * @since       1.0 
 * @return      boolean
*/

function edd_check_for_existing_payment($payment_id) {
	$payment = get_post($payment_id);
	if($payment && $payment->post_status == 'publish') {
		return true; // payment exists
	}
	return false; // this payment doesn't exist
}


/**
 * Get Payment Status
 *
 * Retrieves the status of a payment.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_get_payment_status($payment = OBJECT, $return_label = false) {
	if( !is_object($payment) && !isset($payment->post_status) )
    	return;
     
   $statuses = edd_get_payment_statuses();
   if (!is_array($statuses) || empty($statuses)) 
   	return;
     
   if ( array_key_exists( $payment->post_status, $statuses) ) {
      if ( true === $return_label ) {
      	return $statuses[$payment->post_status];
      } else {
      	return array_search( $payment->post_status, $statuses );
   	}
   }        
}


/**
 * Get Payment Statuses
 *
 * Retrieves all available statuses for payments
 *
 * @access      public
 * @since       1.0.8.1 
 * @return      string
*/

function edd_get_payment_statuses() {
	
	$payment_statuses = array(
		'pending' => __('Pending', 'edd'),
		'publish' => __('Complete', 'edd'),
		'refunded' => __('Refunded', 'edd')
	);
	
	return apply_filters( 'edd_payment_statuses', $payment_statuses ); 
	
}

/**
 * Get Earnings By Date
 *
 * @access      public
 * @since       1.0 
 * @return      integer
*/

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


/**
 * Is Payment Complete
 *
 * Checks whether a payment has been marked as complete.
 *
 * @access      public
 * @since       1.0.8
 * @param       $payment_id INT the ID number of the payment to check
 * @return      boolean true if complete, false otherwise
*/

function edd_is_payment_complete($payment_id) {
	$payment = get_post($payment_id);
	if( $payment )
		if( $payment->post_status == 'publish' )
			return true;
	return false;
}

/**
 * Get Users Purchases
 *
 * Retrieves a list of all purchases by a specific user.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_users_purchases($user_id) {
	
	$purchases = get_transient('edd_user_' . $user_id . '_purchases');
	if(false === $purchases || edd_is_test_mode()) {
		$mode = edd_is_test_mode() ? 'test' : 'live';
		$purchases = get_posts(
			array(
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_edd_payment_mode',
						'value' => $mode
					),
					array(
						'key' => '_edd_payment_user_id',
						'value' => $user_id
					)
				),
				'post_type' => 'edd_payment', 
				'posts_per_page' => -1
			)
		);
		set_transient('edd_user_' . $user_id . '_purchases', $purchases, 7200);
	}
	if($purchases) {
	    // return the download list
		return $purchases;
	}
	
	// no downloads	
	return false;	
}


/**
 * Has Purchases
 *
 * Checks to see if a user has purchased at least one item.
 *
 * @access      public
 * @since       1.0 
 * @param       $user_id int - the ID of the user to check
 * @return      bool - true if has purchased, false other wise.
*/

function edd_has_purchases($user_id) {
	if(edd_get_users_purchases($user_id)) {
		return true; // user has at least one purchase
	}
	return false; // user has never purchased anything
}


/**
 * Get Downloads Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @access      public
 * @since       1.0 
 * @param       int $payment_id - the ID number of the purchase
 * @return      mixed - array if purchase exists, false otherwise
*/

function edd_get_downloads_of_purchase($payment_id, $payment_meta = null){
	if(is_null($payment_meta)) {
		$payment_meta = get_post_meta($payment_id, '_edd_payment_meta', true);
	}
	$downloads = maybe_unserialize($payment_meta['downloads']);
	if($downloads)
		return $downloads;
	return false;
}

