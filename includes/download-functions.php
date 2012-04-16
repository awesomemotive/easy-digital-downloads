<?php

// retrieves a download post object by ID or slug
function edd_get_download($download) {

	if(is_numeric($download)) {
		$download = get_post($download);
		if($download->post_type != 'download')
			return null;
		return $download;
	}
	
	$args = array(
		'post_type' => 'download',
		'name' => $download,
		'numberposts' => 1
	);
	
	$download = get_posts($args);
    
	if ($download) {
		return $download[0];
	}
	
	return null;
}

// retrieves a list of all purchases by a specific user
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
	if($purchases)
		return $purchases; // return the download list
	
	// no downloads	
	return false;	
}

/*
* Checks to see if a user has purchased at least one item
* Uses edd_get_users_purchases()
* @param - $user_id int - the ID of the user to check
* return bool - true if has purchased, false other wise
*/
function edd_has_purchases($user_id) {
	if(edd_get_users_purchases($user_id)) {
		return true; // user has at least one purchase
	}
	return false; // user has never purchased anything
}

/*
* retrieves an array of all files purchased
* @param int $payment_id - the ID number of the purchase
* return mixed - array if purchase exists, false otherwise
*/
function edd_get_downloads_of_purchase($payment_id, $payment_meta = null){
	if(is_null($download_meta)) {
		$payment_meta = get_post_meta($payment_id, '_edd_payment_meta', true);
	}
	$downloads = maybe_unserialize($payment_meta['downloads']);
	if($downloads)
		return $downloads;
	return false;
}

// returns the file extension of a filename
function edd_get_file_extension($str)
{
   $parts = explode('.', $str);
   return end($parts);
}

// returns the total earnings for a download
function edd_get_download_earnings_stats($download_id) {
	$earnings = get_post_meta($download_id, '_edd_download_earnings', true);
	if($earnings)
		return $earnings;
	return 0;
}

// return the sales number for a download
function edd_get_download_sales_stats($download_id) {
	$sales = get_post_meta($download_id, '_edd_download_sales', true);
	if($sales)
		return $sales;
	return 0;
}

// returns an array of sales and sale info for a download
function edd_get_download_sales_log($download_id) {
	$sales_log = get_post_meta($download_id, '_edd_sales_log', true);
	if($sales_log) {
		return $sales_log;
	}
	return false;
}

// returns an array of file download dates and user info
function edd_get_file_download_log($download_id) {
	$download_log = get_post_meta($download_id, '_edd_file_download_log', true);
	if($download_log) {
		return $download_log;
	}
	return false;
}

// stores log information for a download sale
function edd_record_sale_in_log($download_id, $payment_id, $user_info, $date) {
	$log = edd_get_download_sales_log($download_id);
	if(!$log) {
		$log = array();
	}
	$log_entry = array(
		'payment_id' => $payment_id,
		'user_info' => $user_info,
		'date' => $date
	);
	$log[] = $log_entry;
	
	update_post_meta($download_id, '_edd_sales_log', $log);
}

// stores a log entry for a file download
function edd_record_download_in_log($download_id, $file_id, $user_info, $ip, $date) {
	$log = edd_get_file_download_log($download_id);
	if(!$log) {
		$log = array();
	}
	$log_entry = array(
		'file_id' => $file_id,
		'user_info' => $user_info,
		'ip' => $ip,
		'date' => $date
	);
	$log[] = $log_entry;
	
	update_post_meta($download_id, '_edd_file_download_log', $log);
}

// returns the purchase price of a download
function edd_get_download_price($download_id) {
	$price = get_post_meta($download_id, 'edd_price', true);
	if($price)
		return $price;
	return 0;
}

// increases the sale count od a download
function edd_increase_purchase_count($download_id) {
	$sales = edd_get_download_sales_stats($download_id);
	$sales = $sales + 1;
	if(update_post_meta($download_id, '_edd_download_sales', $sales))
		return $sales;

	return false;
}

// increases the total earnings of a download
function edd_increase_earnings($download_id, $amount) {
	$earnings = edd_get_download_earnings_stats($download_id);
	$earnings = $earnings + $amount;
	
	if(update_post_meta($download_id, '_edd_download_earnings', $earnings))
		return $earnings;
	
	return false;
}

// retrieves an array of all downloadable files for a download
function edd_get_download_files($download_id) {
	$files = get_post_meta($download_id, 'edd_download_files', true);
	if($files)
		return $files;
	return false;
}

// constructs the file download url for a specific file
function edd_get_download_file_url($key, $email, $filekey, $download) {
	$download_url = add_query_arg(
		'download_key', 
		$key,
		add_query_arg(
			'email',
			urlencode($email),
			add_query_arg(
				'file',
				$filekey,
				add_query_arg(
					'download',
					$download,
					add_query_arg(
						'expire',
						urlencode(base64_encode(strtotime('+1 day', time()))), // link valid for 24 hours
						home_url()
					)
				)
			)
		)
	);
	return $download_url;	
}


// verifies a download purchase using a purchase key and email
function edd_verify_download_link($download_id, $key, $email, $expire) {

	$meta_query = array(
		'relation' => 'AND',
		array(
			'key' => '_edd_payment_purchase_key',
			'value' => $key
		),
		array(
			'key' => '_edd_payment_user_email',
			'value' => $email
		)
	);

	$payments = get_posts(array('meta_query' => $meta_query, 'post_type' => 'edd_payment'));
	if($payments) {
		foreach($payments as $payment) {
			$payment_meta = get_post_meta($payment->ID, '_edd_payment_meta', true);
			$downloads = maybe_unserialize($payment_meta['downloads']);
			//print_r($downloads); exit;
			if(array_search($download_id, $downloads) !== false) {
				if(time() < $expire) {
					return true; // payment has been verified and link is still valid
				}
				return false; // payment verified, but link is no longer valid
			}
		}
	}
	// payment not verified
	return false;
}

/*
* Checks to see if a user has purchased a download
* uses edd_get_users_purchases()
* @param int $user_id - the ID of the user to check
* @param int $download_Id - the ID of the download to check for
* return bool - true if has purchased, false otherwise
*/
function edd_has_user_purchased($user_id, $download_id) {
	$users_purchases = edd_get_users_purchases($user_id);
	if($users_purchases) {
		foreach($users_purchases as $purchase) {
			$purchase_meta = get_post_meta($purchase->ID, '_edd_payment_meta', true);
			$purchased_files = maybe_unserialize($purchase_meta['downloads']);
			if(is_array($purchased_files)) {
				if(array_search($download_id, $purchased_files) !== false) {
					return true; // user has purchased the download
				}
			}
		}
	}
	return false; // user has not purchased the download
}