<?php
/**
 * Download Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve a download by a given field
 *
 * @since       2.0
 * @param       string $field The field to retrieve the discount with
 * @param       mixed $value The value for field
 * @return      mixed
 */
function edd_get_download_by( $field = '', $value = '' ) {

	if( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch( strtolower( $field ) ) {

		case 'id':
			$download = get_post( $value );

			if( get_post_type( $download ) != 'download' ) {
				return false;
			}

			break;

		case 'slug':
		case 'name':
			$download = query_posts( array(
				'post_type'      => 'download',
				'name'           => sanitize_title_for_query( $value ),
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $download ) {
				$download = $download[0];
			}

			break;

		case 'sku':
			$download = query_posts( array(
				'post_type'      => 'download',
				'meta_key'       => 'edd_sku',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $download ) {
				$download = $download[0];
			}

			break;

		default:
			return false;
	}

	if( $download ) {
		return $download;
	}

	return false;
}

/**
 * Retrieves a download post object by ID or slug.
 *
 * @since 1.0
 * @param int $download Download ID
 * @return WP_Post $download Entire download data
 */
function edd_get_download( $download ) {
	if ( is_numeric( $download ) ) {
		$download = get_post( $download );
		if ( ! $download || 'download' !== $download->post_type )
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
 * Returns the price of a download, but only for non-variable priced downloads.
 *
 * @since 1.0
 * @param int $download_id ID number of the download to retrieve a price for
 * @return mixed string|int Price of the download
 */
function edd_get_download_price( $download_id = 0 ) {
	$price = get_post_meta( $download_id, 'edd_price', true );
	if ( $price )
		$price = edd_sanitize_amount( $price );
	else
		$price = 0;

	return apply_filters( 'edd_get_download_price', $price, $download_id );
}

/**
 * Displays a formatted price for a download
 *
 * @since 1.0
 * @param int $download_id ID of the download price to show
 * @param bool $echo Whether to echo or return the results
 * @return void
 */
function edd_price( $download_id, $echo = true ) {
	if ( edd_has_variable_prices( $download_id ) ) {
		$prices = edd_get_variable_prices( $download_id );
		// Return the lowest price
		$price_float = 0;
        foreach ($prices as $key => $value)
            if ( ( ( (float)$prices[ $key ]['amount'] ) < $price_float ) or ( $price_float == 0 ) )
                $price_float = (float)$prices[ $key ]['amount'];
            $price = edd_sanitize_amount( $price_float );
	} else {
		$price = edd_get_download_price( $download_id );
	}

	$price = apply_filters( 'edd_download_price', edd_sanitize_amount( $price ), $download_id );

	$price = '<span class="edd_price" id="edd_price_' . $download_id . '">' . $price . '</span>';

	if ( $echo )
		echo $price;
	else
		return $price;
}
add_filter( 'edd_download_price', 'edd_format_amount', 10 );
add_filter( 'edd_download_price', 'edd_currency_filter', 20 );

/**
 * Retrieves the final price of a downloadable product after purchase
 * this price includes any necessary discounts that were applied
 *
 * @since 1.0
 * @param int $download_id ID of the download
 * @param array $user_purchase_info - an array of all information for the payment
 * @param string $amount_override a custom amount that over rides the 'edd_price' meta, used for variable prices
 * @return string - the price of the download
 */
function edd_get_download_final_price( $download_id, $user_purchase_info, $amount_override = null ) {
	if ( is_null( $amount_override ) ) {
		$original_price = get_post_meta( $download_id, 'edd_price', true );
	} else {
		$original_price = $amount_override;
	}
	if ( isset( $user_purchase_info['discount'] ) && $user_purchase_info['discount'] != 'none' ) {
		// if the discount was a %, we modify the amount. Flat rate discounts are ignored
		if ( edd_get_discount_type( edd_get_discount_id_by_code( $user_purchase_info['discount'] ) ) != 'flat' )
			$price = edd_get_discounted_amount( $user_purchase_info['discount'], $original_price );
		else
			$price = $original_price;
	} else {
		$price = $original_price;
	}
	return apply_filters( 'edd_final_price', $price, $download_id, $user_purchase_info );
}

/**
 * Retrieves the variable prices for a download
 *
 * @since 1.2
 * @param int $download_id ID of the download
 * @return array Variable prices
 */
function edd_get_variable_prices( $download_id ) {
	$variable_prices = get_post_meta( $download_id, 'edd_variable_prices', true );
	return apply_filters( 'edd_get_variable_prices', $variable_prices, $download_id );
}

/**
 * Checks to see if a download has variable prices enabled.
 *
 * @since 1.0.7
 * @param int $download_id ID number of the download to check
 * @return bool true if has variable prices, false otherwise
 */
function edd_has_variable_prices( $download_id ) {
	if ( get_post_meta( $download_id, '_variable_pricing', true ) ) {
		return true;
	}

	return false;
}

/**
 * Retrieves the name of a variable price option
 *
 * @since 1.0.9
 * @param int $download_id ID of the download
 * @param int $price_id ID of the price option
 * @param int $payment_id optional payment ID for use in filters
 * @return string $price_name Name of the price option
 */
function edd_get_price_option_name( $download_id = 0, $price_id = 0, $payment_id = 0 ) {
	$prices = edd_get_variable_prices( $download_id );
	$price_name = '';

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$price_name = $prices[ $price_id ]['name'];
	}

	return apply_filters( 'edd_get_price_option_name', $price_name, $download_id, $payment_id );
}

/**
 * Retrieves the amount of a variable price option
 *
 * @since 1.8.2
 * @param int $download_id ID of the download
 * @param int $price_id ID of the price option
 * @param int @payment_id ID of the payment
 * @return float $amount Amount of the price option
 */
function edd_get_price_option_amount( $download_id, $price_id = 0 ) {
	$prices = edd_get_variable_prices( $download_id );
	$amount = 0.00;

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$amount = $prices[ $price_id ]['amount'];
	}

	return apply_filters( 'edd_get_price_option_amount', edd_sanitize_amount( $amount ), $download_id );
}

/**
 * Retrieves cheapest price option of a variable priced download
 *
 * @since 1.4.4
 * @param int $download_id ID of the download
 * @return float Amount of the lowest price
 */
function edd_get_lowest_price_option( $download_id = 0 ) {
	if ( empty( $download_id ) )
		$download_id = get_the_ID();

	if ( ! edd_has_variable_prices( $download_id ) )
		return edd_get_download_price( $download_id );

	$prices = edd_get_variable_prices( $download_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {
		$min = 0;

		foreach ( $prices as $key => $price ) {
			if ( empty( $price['amount'] ) )
				continue;
			if ( $prices[ $min ]['amount'] > $price['amount'] )
				$min = $key;
		}

		$low = $prices[ $min ]['amount'];
	}

	return edd_sanitize_amount( $low );
}

/**
 * Retrieves most expensive price option of a variable priced download
 *
 * @since 1.4.4
 * @param int $download_id ID of the download
 * @return float Amount of the highest price
 */
function edd_get_highest_price_option( $download_id = 0 ) {
	if ( empty( $download_id ) )
		$download_id = get_the_ID();

	if ( ! edd_has_variable_prices( $download_id ) )
		return edd_get_download_price( $download_id );

	$prices = edd_get_variable_prices( $download_id );

	$high = 0.00;

	if ( ! empty( $prices ) ) {
		$max = 0;

		foreach ( $prices as $key => $price ) {
			if ( empty( $price['amount'] ) )
				continue;

			if ( $prices[ $max ]['amount'] < $price['amount'] )
				$max = $key;
		}

		$high = $prices[ $max ]['amount'];
	}

	return edd_sanitize_amount( $high );
}

/**
 * Retrieves a price from from low to high of a variable priced download
 *
 * @since 1.4.4
 * @param int $download_id ID of the download
 * @return string $range A fully formatted price range
 */
function edd_price_range( $download_id = 0 ) {
	$low   = edd_get_lowest_price_option( $download_id );
	$high  = edd_get_highest_price_option( $download_id );
	$range = '<span class="edd_price_range_low">' . edd_currency_filter( edd_format_amount( $low ) ) . '</span>';
	$range .= '<span class="edd_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
	$range .= '<span class="edd_price_range_high">' . edd_currency_filter( edd_format_amount( $high ) ) . '</span>';

	return apply_filters( 'edd_price_range', $range, $download_id, $low, $high );
}

/**
 * Checks to see if multiple price options can be purchased at once
 *
 * @since 1.4.2
 * @param int $download_id Download ID
 * @return bool
 */
function edd_single_price_option_mode( $download_id = 0 ) {
	if ( empty( $download_id ) )
		$download_id = get_the_ID();

	$ret = get_post_meta( $download_id, '_edd_price_options_mode', true );

	return (bool) apply_filters( 'edd_single_price_option_mode', $ret, $download_id );
}

/**
 * Get product types
 *
 * @since 1.8
 * @return array $types Download types
 */
function edd_get_download_types() {

	$types = array(
		'0'       => __( 'Default', 'edd' ),
		'bundle'  => __( 'Bundle', 'edd' )
	);

	return apply_filters( 'edd_download_types', $types );
}

/**
 * Gets the Download type, either default or "bundled"
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @return string $type Download type
 */
function edd_get_download_type( $download_id ) {
	$type = get_post_meta( $download_id, '_edd_product_type', true );
	if( empty( $type ) )
		$type = 'default';
	return apply_filters( 'edd_get_download_type', $type, $download_id );
}

/**
 * Determines if a product is a bundle
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @return bool
 */
function edd_is_bundled_product( $download_id = 0 ) {
	return 'bundle' === edd_get_download_type( $download_id );
}


/**
 * Retrieves the product IDs of bundled products
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @return array $products Products in the bundle
 */
function edd_get_bundled_products( $download_id = 0 ) {
	$products = get_post_meta( $download_id, '_edd_bundled_products', true );
	return apply_filters( 'edd_get_bundled_products', $products, $download_id );
}

/**
 * Returns the total earnings for a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @return int $earnings Earnings for a certain download
 */
function edd_get_download_earnings_stats( $download_id ) {

	if ( '' == get_post_meta( $download_id, '_edd_download_earnings', true ) ) {
		add_post_meta( $download_id, '_edd_download_earnings', 0 );
	}

	$earnings = get_post_meta( $download_id, '_edd_download_earnings', true );

	if( $earnings < 0 ) {
		// Never let earnings be less than zero
		$earnings = 0;
	}

	return $earnings;
}

/**
 * Return the sales number for a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @return int $sales Amount of sales for a certain download
 */
function edd_get_download_sales_stats( $download_id ) {

	if ( '' == get_post_meta( $download_id, '_edd_download_sales', true ) ) {
		add_post_meta( $download_id, '_edd_download_sales', 0 );
	} // End if

	$sales = get_post_meta( $download_id, '_edd_download_sales', true );

	if ( $sales < 0 ) {
		// Never let sales be less than zero
		$sales = 0;
	}

	return $sales;
}

/**
 * Record Sale In Log
 *
 * Stores log information for a download sale.
 *
 * @since 1.0
 * @global $edd_logs
 * @param int $download_id Download ID
 * @param int $payment_id Payment ID
 * @param bool|int $price_id Price ID, if any
 * @return void
*/
function edd_record_sale_in_log( $download_id, $payment_id, $price_id = false ) {
	global $edd_logs;

	$log_data = array(
		'post_parent' 	=> $download_id,
		'log_type'		=> 'sale'
	);

	$log_meta = array(
		'payment_id'    => $payment_id,
		'price_id'      => (int) $price_id
	);

	$edd_logs->insert_log( $log_data, $log_meta );
}

/**
 * Record Download In Log
 *
 * Stores a log entry for a file download.
 *
 * @since 1.0
 * @global $edd_logs
 * @param int $download_id Download ID
 * @param int $file_id ID of the file downloaded
 * @param array $user_info User information
 * @param string $ip IP Address
 * @param int $payment_id Payment ID
 * @param int $price_id Price ID, if any
 * @return void
 */
function edd_record_download_in_log( $download_id, $file_id, $user_info, $ip, $payment_id, $price_id = false ) {
	global $edd_logs;

	$log_data = array(
		'post_parent'	=> $download_id,
		'log_type'		=> 'file_download'
	);

	$user_id = isset( $user_info['id'] ) ? $user_info['id'] : (int) -1;

	$log_meta = array(
		'user_info'	=> $user_info,
		'user_id'	=> $user_id,
		'file_id'	=> (int) $file_id,
		'ip'		=> $ip,
		'payment_id'=> $payment_id,
		'price_id'  => (int) $price_id
	);

	$edd_logs->insert_log( $log_data, $log_meta );
}

/**
 * Delete log entries when deleting download product
 *
 * Removes all related log entries when a download is completely deleted.
 * (Does not run when a download is trashed)
 *
 * @since 1.3.4
 * @param int $download_id Download ID
 * @return void
 */
function edd_remove_download_logs_on_delete( $download_id = 0 ) {
	if ( 'download' !== get_post_type( $download_id ) )
		return;

	global $edd_logs;

	// Remove all log entries related to this download
	$edd_logs->delete_logs( $download_id );
}
add_action( 'delete_post', 'edd_remove_download_logs_on_delete' );

/**
 *
 * Increases the sale count of a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @return bool|int
 */
function edd_increase_purchase_count( $download_id ) {
	$sales = edd_get_download_sales_stats( $download_id );
	$sales = $sales + 1;
	if ( update_post_meta( $download_id, '_edd_download_sales', $sales ) )
		return $sales;

	return false;
}

/**
 * Decreases the sale count of a download. Primarily for when a purchase is
 * refunded.
 *
 * @since 1.0.8.1
 * @param int $download_id Download ID
 * @return bool|int
 */
function edd_decrease_purchase_count( $download_id ) {
	$sales = edd_get_download_sales_stats( $download_id );
	if ( $sales > 0 ) // Only decrease if not already zero
		$sales = $sales - 1;

	if ( update_post_meta( $download_id, '_edd_download_sales', $sales ) )
		return $sales;

	return false;
}

/**
 * Increases the total earnings of a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @param int $amount Earnings
 * @return bool|int
 */
function edd_increase_earnings( $download_id, $amount ) {
	$earnings = edd_get_download_earnings_stats( $download_id );
	$earnings = $earnings + $amount;

	if ( update_post_meta( $download_id, '_edd_download_earnings', $earnings ) )
		return $earnings;

	return false;
}

/**
 * Decreases the total earnings of a download. Primarily for when a purchase is refunded.
 *
 * @since 1.0.8.1
 * @param int $download_id Download ID
 * @param int $amount Earnings
 * @return bool|int
 */
function edd_decrease_earnings( $download_id, $amount ) {
	$earnings = edd_get_download_earnings_stats( $download_id );

	if ( $earnings > 0 ) // Only decrease if greater than zero
		$earnings = $earnings - $amount;

	if ( update_post_meta( $download_id, '_edd_download_earnings', $earnings ) )
		return $earnings;

	return false;
}

/**
 * Retrieves the average monthly earnings for a specific download
 *
 * @since 1.3
 * @param int $download_id Download ID
 * @return float $earnings Average monthly earnings
 */
function edd_get_average_monthly_download_earnings( $download_id ) {
	$earnings 	  = edd_get_download_earnings_stats( $download_id );
	$release_date = get_post_field( 'post_date', $download_id );

	$diff 	= abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

    $months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 ) {
		$earnings = ( $earnings / $months );
	}

	return $earnings < 0 ? 0 : $earnings;
}

/**
 * Retrieves the average monthly sales for a specific download
 *
 * @since 1.3
 * @param int $download_id Download ID
 * @return float $sales Average monthly sales
 */
function edd_get_average_monthly_download_sales( $download_id ) {
    $sales          = edd_get_download_sales_stats( $download_id );
    $release_date   = get_post_field( 'post_date', $download_id );

    $diff   = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

    $months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

    if ( $months > 0 )
        $sales = ( $sales / $months );

    return $sales;
}

/**
 * Gets all download files for a product
 *
 * Can retrieve files specific to price ID
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @param int $variable_price_id Variable pricing option ID
 * @return array $files Download files
 */
function edd_get_download_files( $download_id = 0, $variable_price_id = null ) {
	$files = array();

	// Bundled products are not allowed to have files
	if( edd_is_bundled_product( $download_id ) )
		return $files;

	$download_files = get_post_meta( $download_id, 'edd_download_files', true );

	if ( $download_files ) {
		if ( ! is_null( $variable_price_id ) ) {
			foreach ( $download_files as $key => $file_info ) {
				if ( isset( $file_info['condition'] ) ) {
					if ( $file_info['condition'] == $variable_price_id || 'all' === $file_info['condition'] ) {
						$files[ $key ] = $file_info;
					}
				}
			}
		} else {
			$files = $download_files;
		}
	}

	return apply_filters( 'edd_download_files', $files, $download_id, $variable_price_id );
}

/**
 * Retrieves a file name for a product's download file
 *
 * Defaults to the file's actual name if no 'name' key is present
 *
 * @since 1.6
 * @param array $file File array
 * @return string The file name
 */
function edd_get_file_name( $file = array() ) {
	if( empty( $file ) || ! is_array( $file ) )
		return false;
	$name = ! empty( $file['name'] ) ? esc_html( $file['name'] ) : basename( $file['file'] );

	return $name;
}

/**
 * Gets the number of times a file has been downloaded for a specific purchase
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @param int $file_key File key
 * @param int $payment_id The ID number of the associated payment
 * @return int Number of times the file has been downloaded for the purchase
 */
function edd_get_file_downloaded_count( $download_id = 0, $file_key = 0, $payment_id = 0 ) {
	global $edd_logs;

	$meta_query = array(
		'relation'	=> 'AND',
		array(
			'key' 	=> '_edd_log_file_id',
			'value' => (int) $file_key
		),
		array(
			'key' 	=> '_edd_log_payment_id',
			'value' => (int) $payment_id
		)
	);

	return $edd_logs->get_log_count( $download_id, 'file_download', $meta_query );
}


/**
 * Gets the file download file limit for a particular download
 *
 * This limit refers to the maximum number of times files connected to a product
 * can be downloaded.
 *
 * @since 1.3.1
 * @param int $download_id Download ID
 * @return int $limit File download limit
 */
function edd_get_file_download_limit( $download_id = 0 ) {
	global $edd_options;

	$ret    = 0;
	$limit  = get_post_meta( $download_id, '_edd_download_limit', true );
	$global = edd_get_option( 'file_download_limit', 0 );

	if ( ! empty( $limit ) || ( is_numeric( $limit ) && (int)$limit == 0 ) ) {
		// Download specific limit
		$ret = absint( $limit );
	} else {
		// Global limit
		$ret = strlen( $limit ) == 0  || $global ? $global : 0;
	}
	return apply_filters( 'edd_file_download_limit', $ret, $download_id );
}

/**
 * Gets the file download file limit override for a particular download
 *
 * The override allows the main file download limit to be bypassed
 *
 * @since 1.3.2
 * @param int $download_id Download ID
 * @param int $payment_id Payment ID
 * @return int $limit_override The new limit
*/
function edd_get_file_download_limit_override( $download_id = 0, $payment_id = 0 ) {
	$limit_override = get_post_meta( $download_id, '_edd_download_limit_override_' . $payment_id, true );
	if ( $limit_override ) {
		return absint( $limit_override );
	}
	return 0;
}

/**
 * Sets the file download file limit override for a particular download
 *
 * The override allows the main file download limit to be bypassed
 * If no override is set yet, the override is set to the main limit + 1
 * If the override is already set, then it is simply incremented by 1
 *
 * @since 1.3.2
 * @param int $download_id Download ID
 * @param int $payment_id Payment ID
 * @return void
 */
function edd_set_file_download_limit_override( $download_id = 0, $payment_id = 0 ) {
	$override 	= edd_get_file_download_limit_override( $download_id, $payment_id );
	$limit 		= edd_get_file_download_limit( $download_id );

	if ( ! empty( $override ) ) {
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
 * @since 1.3.1
 * @uses EDD_Logging::get_log_count()
 * @param int $download_id Download ID
 * @param int $payment_id Payment ID
 * @param int $file_id File ID
 * @param int $price_id Price ID
 * @return bool True if at limit, false otherwise
 */
function edd_is_file_at_download_limit( $download_id = 0, $payment_id = 0, $file_id = 0, $price_id = false ) {

	// Checks to see if at limit
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
		),
		array(
			'key' 	=> '_edd_log_price_id',
			'value' => (int) $price_id
		)
	);

	$ret                = false;
	$download_count     = $logs->get_log_count( $download_id, 'file_download', $meta_query );

	$download_limit     = edd_get_file_download_limit( $download_id );
	$unlimited_purchase = edd_payment_has_unlimited_downloads( $payment_id );

	if ( ! empty( $download_limit ) && empty( $unlimited_purchase ) ) {
		if ( $download_count >= $download_limit ) {
			$ret = true;

			// Check to make sure the limit isn't overwritten
			// A limit is overwritten when purchase receipt is resent
			$limit_override = edd_get_file_download_limit_override( $download_id, $payment_id );

			if ( ! empty( $limit_override ) && $download_count < $limit_override ) {
				$ret = false;
			}
		}
	}

	return (bool) apply_filters( 'edd_is_file_at_download_limit', $ret, $download_id, $payment_id, $file_id );
}

/**
 * Gets the Price ID that can download a file
 *
 * @since 1.0.9
 * @param int $download_id Download ID
 * @param string $file_key File Key
 * @return string - the price ID if restricted, "all" otherwise
 */
function edd_get_file_price_condition( $download_id, $file_key ) {
	$files = edd_get_download_files( $download_id );

	if ( ! $files )
		return false;

	$condition = isset( $files[ $file_key ]['condition']) ? $files[ $file_key ]['condition'] : 'all';

	return $condition;
}

/**
 * Get Download File Url
 * Constructs the file download url for a specific file.
 *
 * @since 1.0
 *
 * @param string $key
 * @param string $email Customer email address
 * @param int    $filekey
 * @param int    $download_id
 * @param bool   $price_id
 *
 * @return string Constructed download URL
 */
function edd_get_download_file_url( $key, $email, $filekey, $download_id, $price_id = false ) {
	global $edd_options;

	$hours = isset( $edd_options['download_link_expiration'] )
			&& is_numeric( $edd_options['download_link_expiration'] )
			? absint( $edd_options['download_link_expiration'] ) : 24;

	if ( ! ( $date = strtotime( '+' . $hours . 'hours', current_time( 'timestamp') ) ) )
		$date = 2147472000; // Highest possible date, January 19, 2038

	$params = array(
		'download_key' 	=> $key,
		'email' 		=> rawurlencode( $email ),
		'file' 			=> $filekey,
		'price_id'      => (int) $price_id,
		'download_id' 	=> $download_id,
		'expire' 		=> rawurlencode( base64_encode( $date ) )
	);

	$params = apply_filters( 'edd_download_file_url_args', $params );

	$download_url = add_query_arg( $params, home_url( 'index.php' ) );

	return $download_url;
}

/**
 * Verifies a download purchase using a purchase key and email.
 *
 * @since 1.0
 *
 * @param int    $download_id
 * @param string $key
 * @param string $email
 * @param string $expire
 * @param int    $file_key
 *
 * @return bool True if payment and link was verified, false otherwise
 */
function edd_verify_download_link( $download_id = 0, $key = '', $email = '', $expire = '', $file_key = 0 ) {

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

	$accepted_stati = apply_filters( 'edd_allowed_download_stati', array( 'publish', 'complete' ) );

	$payments = get_posts( array( 'meta_query' => $meta_query, 'post_type' => 'edd_payment', 'post_status' => $accepted_stati ) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$cart_details = edd_get_payment_meta_cart_details( $payment->ID, true );

			if ( ! empty( $cart_details ) ) {
				foreach ( $cart_details as $cart_key => $cart_item ) {

					if ( $cart_item['id'] != $download_id )
						continue;

					$price_options 	= isset( $cart_item['item_number']['options'] ) ? $cart_item['item_number']['options'] : false;
					$price_id 		= isset( $price_options['price_id'] ) ? $price_options['price_id'] : false;

					$file_condition = edd_get_file_price_condition( $cart_item['id'], $file_key );

					// Check to see if the file download limit has been reached
					if ( edd_is_file_at_download_limit( $cart_item['id'], $payment->ID, $file_key, $price_id ) )
						wp_die( apply_filters( 'edd_download_limit_reached_text', __( 'Sorry but you have hit your download limit for this file.', 'edd' ) ), __( 'Error', 'edd' ) );

					// If this download has variable prices, we have to confirm that this file was included in their purchase
					if ( ! empty( $price_options ) && $file_condition != 'all' && edd_has_variable_prices( $cart_item['id'] ) ) {
						if ( $file_condition == $price_options['price_id'] )
							return $payment->ID;
					}

					// Make sure the link hasn't expired
					if ( current_time( 'timestamp' ) > $expire ) {
						wp_die( apply_filters( 'edd_download_link_expired_text', __( 'Sorry but your download link has expired.', 'edd' ) ), __( 'Error', 'edd' ) );
					}
					return $payment->ID; // Payment has been verified and link is still valid
				}

			}

		}

	} else {
		wp_die( __( 'No payments matching your request were found.', 'edd' ), __( 'Error', 'edd' ) );
	}
	// Payment not verified
	return false;
}

/**
 * Get product notes
 *
 * @since 1.2.1
 * @param int $download_id Download ID
 * @return string $notes Product notes
 */
function edd_get_product_notes( $download_id ) {
	$notes = get_post_meta( $download_id, 'edd_product_notes', true );

	if ( $notes )
		return (string) apply_filters( 'edd_product_notes', $notes, $download_id );

	return '';
}

/**
 * Retrieves a download SKU by ID.
 *
 * @since 1.6
 *
 * @author Daniel J Griffiths
 * @param int $download_id
 *
 * @return mixed|void Download SKU
 */
function edd_get_download_sku( $download_id = 0 ) {
	$sku = get_post_meta( $download_id, 'edd_sku', true );
	if ( empty( $sku ) )
		$sku = '-';

	return apply_filters( 'edd_get_download_sku', $sku, $download_id );
}

/**
 * get the Download button behavior, either add to cart or direct
 *
 * @since 1.7
 *
 * @param int $download_id
 * @return mixed|void Add to Cart or Direct
 */
function edd_get_download_button_behavior( $download_id = 0 ) {
	$behavior = get_post_meta( $download_id, '_edd_button_behavior', true );
	if( empty( $behavior ) ) {
		$behavior = 'add_to_cart';
	}
	return apply_filters( 'edd_get_download_button_behavior', $behavior, $download_id );
}

/**
 * Get the file Download method
 *
 * @since 1.6
 * @return string The method to use for file downloads
 */
function edd_get_file_download_method() {
	global $edd_options;
	$method = isset( $edd_options['download_method'] ) ? $edd_options['download_method'] : 'direct';
	return apply_filters( 'edd_file_download_method', $method );
}

/**
 * Returns a random download
 *
 * @since 1.7
 * @author Chris Christoff
 * @param bool $post_ids True for array of post ids, false if array of posts
 */
function edd_get_random_download( $post_ids = true ) {
	 edd_get_random_downloads( 1, $post_ids );
}

/**
 * Returns random downloads
 *
 * @since 1.7
 * @author Chris Christoff
 * @param int $num The number of posts to return
 * @param bool $post_ids True for array of post objects, else array of ids
 * @return mixed $query Returns an array of id's or an array of post objects
 */
function edd_get_random_downloads( $num = 3, $post_ids = true ) {
	if ( $post_ids ) {
		$args = array( 'post_type' => 'download', 'orderby' => 'rand', 'post_count' => $num, 'fields' => 'ids' );
	} else {
		$args = array( 'post_type' => 'download', 'orderby' => 'rand', 'post_count' => $num );
	}
	$args  = apply_filters( 'edd_get_random_downloads', $args );
	return get_posts( $args );
}
