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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Download
 *
 * Retrieves a download post object by ID or slug.
 *
 * @access      public
 * @since       1.0
 * @return      object
*/

function edd_get_download( $download ) {
	if( is_numeric( $download ) ) {
		$download = get_post( $download );
		if( $download->post_type != 'download' )
			return null;
		return $download;
	}

	$args = array(
		'post_type'   => 'download',
		'name'        => $download,
		'numberposts' => 1
	);

	$download = get_posts($args);

	if ( $download ) {
		return $download[0];
	}

	return null;
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

function edd_get_download_price( $download_id ) {
	$price = get_post_meta( $download_id, 'edd_price', true );
	if( $price )
		return edd_sanitize_amount( $price );
	return  0;
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

function edd_price( $download_id, $echo = true ) {
	if( edd_has_variable_prices( $download_id ) ) {
		$prices = edd_get_variable_prices( $download_id );
		// return the lowest price
		$price_float = 0;
                foreach($prices as $key => $value)
                        if( ( ( (float)$prices[$key]['amount']) < $price_float) or ($price_float==0) )
                                $price_float = (float)$prices[$key]['amount'];
                $price = edd_sanitize_amount($price_float);
	} else {
		$price = edd_get_download_price( $download_id );
	}


	$price = apply_filters( 'edd_download_price', $price, $download_id );

	$price = '<span class="edd_price" id="edd_price_' . $download_id . '">' . $price . '</span>';

	if( $echo )
		echo $price;
	else
		return $price;
}
add_filter( 'edd_download_price', 'edd_format_amount', 10 );
add_filter( 'edd_download_price', 'edd_currency_filter', 20 );


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

function edd_get_download_final_price( $download_id, $user_purchase_info, $amount_override = null ) {
	if( is_null( $amount_override ) ) {
		$original_price = get_post_meta( $download_id, 'edd_price', true );
	} else {
		$original_price = $amount_override;
	}
	if( isset( $user_purchase_info['discount']) && $user_purchase_info['discount'] != 'none' ) {
		$price = edd_get_discounted_amount( $user_purchase_info['discount'], $original_price );
	} else {
		$price = $original_price;
	}
	return $price;
}


/**
 * Get Download Variable Prices
 *
 * retrieves the variable prices for a download
 *
 * @access      public
 * @since       1.2
 * @param       int $download_id - the ID of the download
 * @return      array
*/

function edd_get_variable_prices( $download_id ) {
	return get_post_meta( $download_id, 'edd_variable_prices', true );
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

function edd_has_variable_prices( $download_id ) {
	if( get_post_meta( $download_id, '_variable_pricing', true ) ) {
		return true;
	}
	return false;
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

function edd_get_price_option_name( $download_id, $price_id ) {
	$prices = edd_get_variable_prices( $download_id );
	if( $prices && is_array( $prices ) ) {
		$price_name = $prices[ $price_id ]['name'];
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

function edd_get_download_earnings_stats( $download_id ) {
	// If the current Download CPT has no earnings value associated with it, we need to initialize it.
	// This is what enables us to sort it.
	if ( '' == get_post_meta( $download_id, '_edd_download_earnings', true ) ) {
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
	// If the current Download CPT has no sales value associated with it, we need to initialize it.
	// This is what enables us to sort it.
	if ( '' == get_post_meta( $download_id, '_edd_download_sales', true ) ) {
		add_post_meta( $download_id, '_edd_download_sales', 0 );
	} // end if

	$sales = get_post_meta( $download_id, '_edd_download_sales', true );

	return $sales;
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

function edd_record_sale_in_log( $download_id, $payment_id ) {

	global $edd_logs;

	$log_data = array(
		'post_parent' 	=> $download_id,
		'log_type'		=> 'sale'
	);

	$log_meta = array(
		'payment_id'    => $payment_id
	);

	$log_id = $edd_logs->insert_log( $log_data, $log_meta );

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

function edd_record_download_in_log( $download_id, $file_id, $user_info, $ip, $payment_id ) {

	global $edd_logs;

	$log_data = array(
		'post_parent'	=> $download_id,
		'log_type'		=> 'file_download'
	);

	$log_meta = array(
		'user_info'	=> $user_info,
		'user_id'	=> (int) $user_info['id'],
		'file_id'	=> (int) $file_id,
		'ip'		=> $ip,
		'payment_id'=> $payment_id
	);

	$log_id = $edd_logs->insert_log( $log_data, $log_meta );


}


/**
 * Delete log entries when deleting download product
 *
 * Removes all related log entries when a download is completely deleted.
 *
 * Does not run when a download is trashed
 *
 * @access      public
 * @since       1.3.4
 * @return      void
*/
function edd_remove_download_logs_on_delete( $download_id = 0 ) {

	if( 'download' != get_post_type( $download_id ) )
		return;

	global $edd_logs;

	// remove all log entries related to this download
	$edd_logs->delete_logs( $download_id );

}
add_action( 'delete_post', 'edd_remove_download_logs_on_delete' );

/**
 * Increase Purchase Count
 *
 * Increases the sale count of a download.
 *
 * @access      public
 * @since       1.0
 * @return      void
*/

function edd_increase_purchase_count( $download_id ) {
	$sales = edd_get_download_sales_stats( $download_id );
	$sales = $sales + 1;
	if( update_post_meta( $download_id, '_edd_download_sales', $sales ) )
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

function edd_decrease_purchase_count( $download_id ) {
	$sales = edd_get_download_sales_stats( $download_id );
	if( $sales > 0 ) // only decrease if not already zero
		$sales = $sales - 1;

	if( update_post_meta( $download_id, '_edd_download_sales', $sales ) )
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

function edd_increase_earnings( $download_id, $amount ) {
	$earnings = edd_get_download_earnings_stats( $download_id );
	$earnings = $earnings + $amount;

	if( update_post_meta( $download_id, '_edd_download_earnings', $earnings ) )
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

function edd_decrease_earnings( $download_id, $amount ) {
	$earnings = edd_get_download_earnings_stats( $download_id );

	if( $earnings > 0 ) // only decrease if greater than zero
		$earnings = $earnings - $amount;

	if( update_post_meta( $download_id, '_edd_download_earnings', $earnings ) )
		return $earnings;

	return false;
}


/**
 * Average Earnings
 *
 * Retreives the average monthly earnings for a specific download
 *
 * @access      public
 * @since       1.3
 * @return      float
*/

function edd_get_average_monthly_download_earnings( $download_id ) {

	$earnings 	  = edd_get_download_earnings_stats( $download_id );
	$release_date = get_post_field( 'post_date', $download_id );

	$diff 	= abs( time() - strtotime( $release_date ) );

	$years 	= floor( $diff / ( 365*60*60*24 ) );							// number of years since publication
	$months = floor( ( $diff - $years * 365*60*60*24 ) / ( 30*60*60*24 ) ); // number of months since publication

	if ( $months > 0 )
		return ( $earnings / $months );
	return $earnings;
}



/**
 * Average Sales
 *
 * Retreives the average monthly sales for a specific download
 *
 * @access      public
 * @since       1.3
 * @return      float
*/

function edd_get_average_monthly_download_sales( $download_id ) {

	$sales			= edd_get_download_sales_stats( $download_id );
	$release_date 	= get_post_field( 'post_date', $download_id );

	$diff 	= abs( time() - strtotime( $release_date ) );

	$years 	= floor( $diff / ( 365*60*60*24 ) );							// number of years since publication
	$months = floor( ( $diff - $years * 365*60*60*24 ) / ( 30*60*60*24 ) ); // number of months since publication

	if ( $months > 0 )
		return ( $sales / $months );
	return $sales;
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
	$download_files = get_post_meta( $download_id, 'edd_download_files', true );

	if( $download_files ) {
		if( !is_null( $variable_price_id ) ) {
			foreach( $download_files as $key => $file_info ) {
				if( isset( $file_info['condition'] ) ) {
					if( $file_info['condition'] == $variable_price_id || $file_info['condition'] == 'all' ) {
						$files[ $key ] = $file_info;
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
 * Gets the file download file limit for a particular download
 *
 * This limit refers to the maximum number of times files connected to a product
 * can be downloaded.
 *
 * @access      public
 * @since       1.3.1
 * @return      int The limit
*/

function edd_get_file_download_limit( $download_id = 0 ) {

	$limit = get_post_meta( $download_id, '_edd_download_limit', true );
	if( $limit )
		return absint( $limit );
	return 0;

}


/**
 * Gets the file download file limit override for a particular download
 *
 * The override allows the main file download limit to be bypassed
 *
 * @access      public
 * @since       1.3.2
 * @return      int The new limit
*/

function edd_get_file_download_limit_override( $download_id = 0, $payment_id = 0 ) {

	$limit_override = get_post_meta( $download_id, '_edd_download_limit_override_' . $payment_id, true );
	if( $limit_override ) {
		return absint( $limit_override );
	}
	return 0;

}


/**
 * Sets the file download file limit override for a particular download
 *
 * The override allows the main file download limit to be bypassed
 * If no override is set yet, the override is set to the main limmit + 1
 * If the override is already set, then it is simply incremented by 1
 *
 * @access      public
 * @since       1.3.2
 * @return      int The new limit
*/

function edd_set_file_download_limit_override( $download_id = 0, $payment_id = 0 ) {

	$override 	= edd_get_file_download_limit_override( $download_id );
	$limit 		= edd_get_file_download_limit( $download_id );

	if( ! empty( $override ) ) {
		$override = $override += 1;
	} else {
		$override = $limit += 1;
	}
	update_post_meta( $download_id, '_edd_download_limit_override_' . $payment_id, $override );

}


/**
 * Checks if a file is at its download limit
 *
 * This limit refers to the maximum number of times files connected to a product
 * can be downloaded.
 *
 * @access      public
 * @since       1.3.1
 * @return      bool False if not at limit, True if at limit
*/

function edd_is_file_at_download_limit( $download_id = 0, $payment_id = 0, $file_id = 0 ) {

	// checks to see if at limit

	$logs = new EDD_Logging();

	$meta_query = array(
		'relation'	=> 'AND',
		array(
			'key' 	=> '_edd_log_file_id',
			'value' => (int) $file_id
		),
		array(
			'key' 	=> '_edd_log_payment_id',
			'value' => (int) $payment_id
		)
	);

	$ret = false;
	$download_count = $logs->get_log_count( $download_id, 'file_download', $meta_query );
	$download_limit = edd_get_file_download_limit( $download_id );

	if( ! empty( $download_limit ) ) {

		if( $download_count >= $download_limit ) {

			$ret = true;

			// check to make sure the limit isn't overwritten
			// a limit is overwritten when purchase receipt is resent
			$limit_override = edd_get_file_download_limit_override( $download_id, $payment_id );

			if( ! empty( $limit_override ) && $download_count < $limit_override ) {
				$ret = false;
			}

		}

	}

	return (bool) apply_filters( 'edd_is_file_at_download_limit', $ret, $download_id, $payment_id, $file_id );
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

	if( !$files )
		return false;

	$condition = isset( $files[ $file_key ]['condition']) ? $files[ $file_key ]['condition'] : 'all';

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

	$hours = isset( $edd_options['download_link_expiration'] )
			&& is_numeric( $edd_options['download_link_expiration'] )
			? absint($edd_options['download_link_expiration']) : 24;

	if( ! ( $date = strtotime( '+' . $hours . 'hours' ) ) )
		$date = 2147472000; // highest possible date, January 19, 2038

	$params = array(
		'download_key' 	=> $key,
		'email' 		=> rawurlencode( $email ),
		'file' 			=> $filekey,
		'download' 		=> $download_id,
		'expire' 		=> rawurlencode( base64_encode( $date ) )
	);

	$params = apply_filters( 'edd_download_file_url_args', $params );

	$download_url = add_query_arg( $params, home_url() );

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

function edd_verify_download_link( $download_id, $key, $email, $expire, $file_key ) {

	$meta_query = array(
		'relation'  => 'AND',
		array(
			'key'   => '_edd_payment_purchase_key',
			'value' => $key
		),
		array(
			'key'   => '_edd_payment_user_email',
			'value' => $email
		)
	);

	$payments = get_posts( array( 'meta_query' => $meta_query, 'post_type' => 'edd_payment' ) );

	if( $payments ) {

		foreach( $payments as $payment ) {

			$payment_meta 	= get_post_meta( $payment->ID, '_edd_payment_meta', true );
			$downloads 		= maybe_unserialize( $payment_meta['downloads'] );
			$cart_details 	= unserialize( $payment_meta['cart_details'] );

			if( $payment->post_status != 'publish' && $payment->post_status != 'complete' )
				return false;

			if( $downloads ) {

				foreach( $downloads as $key => $download ) {

					$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

					$price_options = $cart_details[ $key ]['item_number']['options'];

					$file_condition = edd_get_file_price_condition( $id, $file_key );

					$variable_prices_enabled = get_post_meta( $id, '_variable_pricing', true );

					// if this download has variable prices, we have to confirm that this file was included in their purchase
					if( ! empty( $price_options ) && $file_condition != 'all' && $variable_prices_enabled ) {
						if( $file_condition !== $price_options['price_id'] )
							return false;
					}

					if( $id == $download_id ) {

						// check to see if the file download limit has been reached
						if( edd_is_file_at_download_limit( $id, $payment->ID, $file_key ) )
							wp_die( apply_filters( 'edd_download_limit_reached_text', __('Sorry but you have hit your download limit for this file.' ), 'edd'), __('Error', 'edd') );

						// make sure the link hasn't expired
						if( time() < $expire ) {
							return $payment->ID; // payment has been verified and link is still valid
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
 * Get product notes
 *
 * @access      public
 * @since       1.2.1
 * @return      string
 */
function edd_get_product_notes( $download_id ) {
	$notes = get_post_meta( $download_id, 'edd_product_notes', true );
	if ( $notes )
		return (string) apply_filters( 'edd_product_notes', $notes, $download_id );
	return '';
}
