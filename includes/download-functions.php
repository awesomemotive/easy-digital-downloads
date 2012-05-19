<?php
/**
 * Download Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Download Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Get Download
 *
 * Retrieves a download post object by ID or slug.
 *
 * @access      public
 * @since       1.0 
 * @return      object
*/

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


/**
 * Get Download Final Price
 *
 * retrieves the price of a downloadable product after purchase
 * this price includes any necessary discounts that were applied.
 *
 * @access      public
 * @since       1.0 
 * @param       int $download_id - the ID of the download
 * @param       array $user_purchase_info - an array of all information for the payment
 * @param       string $amount_override a custom amount taht over rides the 'edd_price' meta, used for variable prices
 * @return      string - the price of the download
*/

function edd_get_download_final_price($download_id, $user_purchase_info, $amount_override = null) {
	if(is_null($amount_override)) {
		$original_price = get_post_meta($download_id, 'edd_price', true);
	} else {
		$original_price = $amount_override;
	}
	if(isset($user_purchase_info['discount']) && $user_purchase_info['discount'] != 'none') {
		$price = edd_get_discounted_amount($user_purchase_info['discount'], $original_price);
	} else {
		$price = $original_price;
	}
	return $price;
}


/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_get_file_extension($str)
{
   $parts = explode('.', $str);
   return end($parts);
}


/**
 * Get Download Earnings Stats
 *
 * Returns the total earnings for a download.
 *
 * @access      public
 * @since       1.0 
 * @return      integer
*/

function edd_get_download_earnings_stats($download_id) {
	$earnings = get_post_meta($download_id, '_edd_download_earnings', true);
	if($earnings)
		return $earnings;
	return 0;
}


/**
 * Get Download Sales Stats
 *
 * Return the sales number for a download.
 *
 * @access      public
 * @since       1.0 
 * @return      integer
*/

function edd_get_download_sales_stats($download_id) {
	$sales = get_post_meta($download_id, '_edd_download_sales', true);
	if($sales)
		return $sales;
	return 0;
}


/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a download.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_download_sales_log($download_id) {
	$sales_log = get_post_meta($download_id, '_edd_sales_log', true);
	if($sales_log) {
		return $sales_log;
	}
	return false;
}


/**
 * Get File Download Log
 *
 * Returns an array of file download dates and user info.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_file_download_log($download_id) {
	$download_log = get_post_meta($download_id, '_edd_file_download_log', true);
	if($download_log) {
		return $download_log;
	}
	return false;
}


/**
 * Record Sale In Log
 *
 * Stores log information for a download sale.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

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


/**
 * Record Download In Log
 *
 * Stores a log entry for a file download.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

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


/**
 * Get Download Price
 *
 * Returns the price of a download, but only for non-variable priced downloads.
 *
 * @access      public
 * @since       1.0 
 * @param       $download_id INT the ID number of the download to retrieve a price for
 * @return      $string/int the price of the download
*/

function edd_get_download_price($download_id) {
	$price = get_post_meta($download_id, 'edd_price', true);
	if($price)
		return $price;
	return 0;
}


/**
 * Price
 *
 * Displays a formatted price for a download.
 *
 * @access      public
 * @since       1.0
 * @param       int $download_id the ID of the download price to show
 * @return      void
*/

function edd_price($download_id) {
	if(edd_has_variable_prices($download_id)) {
		$prices = get_post_meta($download_id, 'edd_variable_prices', true);
		echo edd_currency_filter($prices[0]['amount']); // show the first price option
	} else {
		echo edd_currency_filter(edd_get_download_price($download_id));
	}
}


/**
 * Has Variable Prices
 *
 * Checks to see if a download has variable prices enabled.
 *
 * @access      public
 * @since       1.0.7
 * @param       int $download_id the ID number of the download to checl
 * @return      boolean true if has variable prices, false otherwise
*/

function edd_has_variable_prices($download_id) {
	if(get_post_meta($download_id, '_variable_pricing', true)) {
		return true;	
	}
	return false;
}


/**
 * Increase Purchase Count
 *
 * Increases the sale count od a download.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_increase_purchase_count($download_id) {
	$sales = edd_get_download_sales_stats($download_id);
	$sales = $sales + 1;
	if(update_post_meta($download_id, '_edd_download_sales', $sales))
		return $sales;

	return false;
}


/**
 * Increase Earnings
 *
 * Increases the total earnings of a download.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_increase_earnings($download_id, $amount) {
	$earnings = edd_get_download_earnings_stats($download_id);
	$earnings = $earnings + $amount;
	
	if(update_post_meta($download_id, '_edd_download_earnings', $earnings))
		return $earnings;
	
	return false;
}


/**
 * Get Download Files
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_download_files($download_id) {
	$files = get_post_meta($download_id, 'edd_download_files', true);
	if($files)
		return $files;
	return false;
}


/**
 * Get Download File Url
 *
 * Constructs the file download url for a specific file.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_get_download_file_url($key, $email, $filekey, $download) {
	
	$params = array(
		'download_key' => $key,
		'email' => urlencode($email),
		'file' => $filekey,
		'download' => $download, 
		'expire' => urlencode(base64_encode(strtotime('+1 day', time())))
	);	
	
	$download_url = add_query_arg($params, home_url());
	return $download_url;	
}


/**
 * Verify Download Link
 *
 * Verifies a download purchase using a purchase key and email.
 *
 * @access      public
 * @since       1.0 
 * @return      boolean
*/

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
			if($downloads) {
				foreach($downloads as $download) {
					$id = isset($payment_meta['cart_details']) ? $download['id'] : $download;
					if($id == $download_id) {
						if(time() < $expire) {
							return true; // payment has been verified and link is still valid
						}
						return false; // payment verified, but link is no longer valid
					}
				}
			}
		}
	}
	// payment not verified
	return false;
}


/**
 * Has User Purchased
 *
 * Checks to see if a user has purchased a download.
 *
 * @access      public
 * @since       1.0 
 * @param       int $user_id - the ID of the user to check
 * @param       int $download_Id - the ID of the download to check for
 * @return      boolean - true if has purchased, false otherwise
*/

function edd_has_user_purchased($user_id, $download_id) {
	$users_purchases = edd_get_users_purchases($user_id);
	if($users_purchases) {
		foreach($users_purchases as $purchase) {
			$purchase_meta = get_post_meta($purchase->ID, '_edd_payment_meta', true);
			$purchased_files = maybe_unserialize($purchase_meta['downloads']);
			if(is_array($purchased_files)) {
				if(array_search($download_id, $purchased_files) !== false) {
				    // user has purchased the download
					return true;
				}
			}
		}
	}
	// user has not purchased the download
	return false;
}