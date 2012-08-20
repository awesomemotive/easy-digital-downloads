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
 * Get Download Final Price
 *
 * retrieves the price of a downloadable product after purchase
 * this price includes any necessary discounts that were applied.
 *
 * @access      public
 * @since       1.0 
 * @param       int $download_id - the ID of the download
 * @param       array $user_purchase_info - an array of all information for the payment
 * @param       string $amount_override a custom amount that over rides the 'edd_price' meta, used for variable prices
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
 * Get Download Price Name
 *
 * retrieves the name of a variable price option
 *
 * @access      public
 * @since       1.0.9
 * @param       int $download_id - the ID of the download
 * @param		int $price_id - the ID of the price option
 * @return      string - the name of the price option
*/

function edd_get_price_option_name($download_id, $price_id) {
	$prices = get_post_meta($download_id, 'edd_variable_prices', true);
	if( $prices && is_array( $prices ) ) {
		$price_name = $prices[$price_id]['name'];
	} else {
		$price_name = '';
	}
	return $price_name;
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

	// If the current Download CPT has no earnings value associated wht it, we need to initialize it.
	// This is what enables us to sort it.
	if ( '' == get_post_meta($download_id, '_edd_download_earnings', true) ) {
		add_post_meta( $download_id, '_edd_download_earnings', 0 );
	} // end if
	
	$earnings = get_post_meta( $download_id, '_edd_download_earnings', true );
	
	return $earnings;
	
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
	
	// If the current Download CPT has no sales value associated wht it, we need to initialize it.
	// This is what enables us to sort it.
	if ( '' == get_post_meta($download_id, '_edd_download_sales', true) ) {
		add_post_meta( $download_id, '_edd_download_sales', 0 );
	} // end if
	
	$sales = get_post_meta( $download_id, '_edd_download_sales', true );
	
	return $sales;
}


/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a download.
 * 
 * @param		 $download_id INT the ID number of the download to retrieve a log for
 * @param		 $paginate bool whether to paginate the results or not
 * @param		 $number int the number of results to return
 * @param		 $offset int the number of items to skip
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_download_sales_log($download_id, $paginate = false, $number = 10, $offset = 0) {
	
	$sales_log = get_post_meta($download_id, '_edd_sales_log', true);
	if($sales_log) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );		
		$log['sales'] = $sales_log;
		if( $paginate ) {
			$log['sales'] = array_slice( $sales_log, $offset, $number );
		}
		return $log;
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
 * 
 * @param		 $download_id INT the ID number of the download to retrieve a log for
 * @param		 $paginate bool whether to paginate the results or not
 * @param		 $number int the number of results to return
 * @param		 $offset int the number of items to skip
 *
 * @return      array
*/

function edd_get_file_download_log($download_id, $paginate = false, $number = 10, $offset = 0) {
	$download_log = get_post_meta($download_id, '_edd_file_download_log', true);
	if($download_log) {
		$download_log = array_reverse( $download_log );
		$log = array();
		$log['number'] = count($download_log);		
		$log['downloads'] = $download_log;
		if( $paginate ) {
			$log['downloads'] = array_slice($download_log, $offset, $number);
		}
		return $log;
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
	$log = get_post_meta($download_id, '_edd_sales_log', true);
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
	$log = get_post_meta($download_id, '_edd_file_download_log', true);
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
 * @param		bool whether to echo or return the results
* @return       void
*/

function edd_price($download_id, $echo = true) {
	if(edd_has_variable_prices($download_id)) {
		$prices = get_post_meta($download_id, 'edd_variable_prices', true);
		$price = edd_currency_filter($prices[0]['amount']); // show the first price option
	} else {
		$price = edd_currency_filter(edd_get_download_price($download_id));
	}
	if( $echo )
		echo $price;
	else
		return $price;
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
 * Increases the sale count of a download.
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
 * Decrease Purchase Count
 *
 * Decreases the sale count of a download. Primarily for when a purchase is refunded.
 *
 * @access      public
 * @since       1.0.8.1
 * @return      void
*/

function edd_decrease_purchase_count($download_id) {
	$sales = edd_get_download_sales_stats($download_id);
	if($sales > 0) // only decrease if not already zero
		$sales = $sales - 1;
	
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
 * Decrease Earnings
 *
 * Decreases the total earnings of a download. Primarily for when a purchase is refunded.
 *
 * @access      public
 * @since       1.0.8.1 
 * @return      void
*/

function edd_decrease_earnings($download_id, $amount) {
	$earnings = edd_get_download_earnings_stats($download_id);
	
	if($earnings > 0) // only decrease if greater than zero
		$earnings = $earnings - $amount;
	
	if(update_post_meta($download_id, '_edd_download_earnings', $earnings))
		return $earnings;
	
	return false;
}


/**
 * Gets all download files for a product
 *
 * Can retrieve files specific to price ID
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_get_download_files( $download_id, $variable_price_id = null ) {

	$files = array();
	$download_files = get_post_meta($download_id, 'edd_download_files', true);
	if( $download_files ) {
		if( !is_null( $variable_price_id ) ) {
			foreach( $download_files as $key => $file_info ) {
				if( isset( $file_info['condition'] ) ) {
					if( $file_info['condition'] == $variable_price_id || $file_info['condition'] == 'all' ) {
						$files[$key] = $file_info;
					}
				}
			}
		} else {
			$files = $download_files;
		}
	}

	return $files;
}

/**
 * Gets the Price ID that can download a file
 *
 * @access      public
 * @since       1.0.9 
 * @return      string - the price ID if restricted, "all" otherwise
*/

function edd_get_file_price_condition( $download_id, $file_key ) {
	$files = edd_get_download_files( $download_id );
	if( ! $files )
		return false;
		
	$condition = isset($files[$file_key]['condition']) ? $files[$file_key]['condition'] : 'all';
	
	return $condition;
	
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

function edd_get_download_file_url($key, $email, $filekey, $download_id) {
	
	global $edd_options;

	$hours = isset($edd_options['download_link_expiration']) && is_numeric($edd_options['download_link_expiration']) ? absint($edd_options['download_link_expiration']) : 24;

	$params = array(
		'download_key' => $key,
		'email' => rawurlencode($email),
		'file' => $filekey,
		'download' => $download_id, 
		'expire' => urlencode(base64_encode(strtotime('+' . $hours . 'hours', time())))
	);

	$params = apply_filters('edd_download_file_url_args', $params);
	
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

function edd_verify_download_link($download_id, $key, $email, $expire, $file_key) {

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
			$cart_details = unserialize( $payment_meta['cart_details'] );
			if( $payment->post_status != 'publish' && $payment->post_status != 'complete' )
				return false;

			if($downloads) {
				foreach($downloads as $key => $download) {
					
					$id = isset($payment_meta['cart_details']) ? $download['id'] : $download;
					
					$price_options = $cart_details[$key]['item_number']['options'];

					$file_condition = edd_get_file_price_condition( $id, $file_key );
					
					$variable_prices_enabled = get_post_meta($id, '_variable_pricing', true);
							
					// if this download has variable prices, we have to confirm that this file was included in their purchase
					if( !empty( $price_options ) && $file_condition != 'all' && $variable_prices_enabled) {
						
						if( $file_condition !== $price_options['price_id'] )
							return false;
					}
					
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