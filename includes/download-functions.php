<?php
/**
 * Download Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
			$download = get_posts( array(
				'post_type'      => 'download',
				'name'           => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $download ) {
				$download = $download[0];
			}

			break;

		case 'sku':
			$download = get_posts( array(
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
 * @since 2.9 - Return an EDD_Download object.
 *
 * @param int $download_id Download ID.
 *
 * @return EDD_Download $download Entire download data.
 */
function edd_get_download( $download_id = 0 ) {
	$download = null;

	if ( is_numeric( $download_id ) ) {

		$found_download = new EDD_Download( $download_id );

		if ( ! empty( $found_download->ID ) ) {
			$download = $found_download;
		}

	} else { // Support getting a download by name.
		$args = array(
			'post_type'     => 'download',
			'name'          => $download_id,
			'post_per_page' => 1,
			'fields'        => 'ids',
		);

		$downloads = new WP_Query( $args );
		if ( is_array( $downloads->posts ) && ! empty( $downloads->posts ) ) {

			$download_id = $downloads->posts[0];

			$download = new EDD_Download( $download_id );

		}
	}

	return $download;
}

/**
 * Checks whether or not a download is free
 *
 * @since 2.1
 * @author Daniel J Griffiths
 * @param int $download_id ID number of the download to check
 * @param int $price_id (Optional) ID number of a variably priced item to check
 * @return bool $is_free True if the product is free, false if the product is not free or the check fails
 */
function edd_is_free_download( $download_id = 0, $price_id = false ) {

	if( empty( $download_id ) ) {
		return false;
	}

	$download = new EDD_Download( $download_id );
	return $download->is_free( $price_id );
}

/**
 * Returns the price of a download, but only for non-variable priced downloads.
 *
 * @since 1.0
 * @param int $download_id ID number of the download to retrieve a price for
 * @return mixed|string|int Price of the download
 */
function edd_get_download_price( $download_id = 0 ) {

	if( empty( $download_id ) ) {
		return false;
	}

	$download = new EDD_Download( $download_id );
	return $download->get_price();
}

/**
 * Displays a formatted price for a download
 *
 * @since 1.0
 * @param int $download_id ID of the download price to show
 * @param bool $echo Whether to echo or return the results
 * @param int $price_id Optional price id for variable pricing
 * @return void
 */
function edd_price( $download_id = 0, $echo = true, $price_id = false ) {

	if( empty( $download_id ) ) {
		$download_id = get_the_ID();
	}

	if ( edd_has_variable_prices( $download_id ) ) {

		$prices = edd_get_variable_prices( $download_id );

		if ( false !== $price_id && isset( $prices[$price_id] ) ) {

			$price = edd_get_price_option_amount( $download_id, $price_id );

		} elseif( $default = edd_get_default_variable_price( $download_id ) ) {

			$price = edd_get_price_option_amount( $download_id, $default );

		} else {

			$price = edd_get_lowest_price_option( $download_id );

		}

		$price = edd_sanitize_amount( $price );

	} else {

		$price = edd_get_download_price( $download_id );

	}

	$price           = apply_filters( 'edd_download_price', edd_sanitize_amount( $price ), $download_id, $price_id );
	$formatted_price = '<span class="edd_price" id="edd_price_' . $download_id . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'edd_download_price_after_html', $formatted_price, $download_id, $price, $price_id );

	if ( $echo ) {
		echo $formatted_price;
	} else {
		return $formatted_price;
	}
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
function edd_get_download_final_price( $download_id = 0, $user_purchase_info, $amount_override = null ) {
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
function edd_get_variable_prices( $download_id = 0 ) {

	if( empty( $download_id ) ) {
		return false;
	}

	$download = new EDD_Download( $download_id );
	return $download->get_prices();
}

/**
 * Checks to see if a download has variable prices enabled.
 *
 * @since 1.0.7
 * @param int $download_id ID number of the download to check
 * @return bool true if has variable prices, false otherwise
 */
function edd_has_variable_prices( $download_id = 0 ) {

	if( empty( $download_id ) ) {
		return false;
	}

	$download = new EDD_Download( $download_id );
	return $download->has_variable_prices();
}

/**
 * Returns the default price ID for variable pricing, or the first
 * price if none is set
 *
 * @since  2.2
 * @param  int $download_id ID number of the download to check
 * @return int              The Price ID to select by default
 */
function edd_get_default_variable_price( $download_id = 0 ) {

	if ( ! edd_has_variable_prices( $download_id ) ) {
		return false;
	}

	$prices = edd_get_variable_prices( $download_id );
	$default_price_id = get_post_meta( $download_id, '_edd_default_price_id', true );

	if ( $default_price_id === '' ||  ! isset( $prices[$default_price_id] ) ) {
		$default_price_id = current( array_keys( $prices ) );
	}

	return apply_filters( 'edd_variable_default_price_id', absint( $default_price_id ), $download_id );

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

	return apply_filters( 'edd_get_price_option_name', $price_name, $download_id, $payment_id, $price_id );
}

/**
 * Retrieves the amount of a variable price option
 *
 * @since 1.8.2
 * @param int $download_id ID of the download
 * @param int $price_id ID of the price option
 * @param int $payment_id ID of the payment
 * @return float $amount Amount of the price option
 */
function edd_get_price_option_amount( $download_id = 0, $price_id = 0 ) {
	$prices = edd_get_variable_prices( $download_id );
	$amount = 0.00;

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$amount = $prices[ $price_id ]['amount'];
	}

	return apply_filters( 'edd_get_price_option_amount', edd_sanitize_amount( $amount ), $download_id, $price_id );
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

	if ( ! edd_has_variable_prices( $download_id ) ) {
		return edd_get_download_price( $download_id );
	}

	$prices = edd_get_variable_prices( $download_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['amount'];
			} else {
				$min = min( $min, $price['amount'] );
			}

			if ( $price['amount'] == $min ) {
				$min_id = $key;
			}
		}

		$low = $prices[ $min_id ]['amount'];

	}

	return edd_sanitize_amount( $low );
}

/**
 * Retrieves the ID for the cheapest price option of a variable priced download
 *
 * @since 2.2
 * @param int $download_id ID of the download
 * @return int ID of the lowest price
 */
function edd_get_lowest_price_id( $download_id = 0 ) {
	if ( empty( $download_id ) )
		$download_id = get_the_ID();

	if ( ! edd_has_variable_prices( $download_id ) ) {
		return edd_get_download_price( $download_id );
	}

	$prices = edd_get_variable_prices( $download_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['amount'];
			} else {
				$min = min( $min, $price['amount'] );
			}

			if ( $price['amount'] == $min ) {
				$min_id = $key;
			}
		}
	}

	return (int) $min_id;
}

/**
 * Retrieves most expensive price option of a variable priced download
 *
 * @since 1.4.4
 * @param int $download_id ID of the download
 * @return float Amount of the highest price
 */
function edd_get_highest_price_option( $download_id = 0 ) {

	if ( empty( $download_id ) ) {
		$download_id = get_the_ID();
	}

	if ( ! edd_has_variable_prices( $download_id ) ) {
		return edd_get_download_price( $download_id );
	}

	$prices = edd_get_variable_prices( $download_id );

	$high = 0.00;

	if ( ! empty( $prices ) ) {

		$max = 0;

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			$max = max( $max, $price['amount'] );

			if ( $price['amount'] == $max ) {
				$max_id = $key;
			}
		}

		$high = $prices[ $max_id ]['amount'];
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
	$range = '<span class="edd_price edd_price_range_low" id="edd_price_low_' . $download_id . '">' . edd_currency_filter( edd_format_amount( $low ) ) . '</span>';
	$range .= '<span class="edd_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
	$range .= '<span class="edd_price edd_price_range_high" id="edd_price_high_' . $download_id . '">' . edd_currency_filter( edd_format_amount( $high ) ) . '</span>';

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

	if ( empty( $download_id ) ) {
		$download = get_post();

		$download_id = isset( $download->ID ) ? $download->ID : 0;
	}

	if ( empty( $download_id ) ) {
		return false;
	}

	$download = new EDD_Download( $download_id );
	return $download->is_single_price_mode();

}

/**
 * Get product types
 *
 * @since 1.8
 * @return array $types Download types
 */
function edd_get_download_types() {

	$types = array(
		'0'       => __( 'Default', 'easy-digital-downloads' ),
		'bundle'  => __( 'Bundle', 'easy-digital-downloads' )
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
function edd_get_download_type( $download_id = 0 ) {
	$download = new EDD_Download( $download_id );
	return $download->type;
}

/**
 * Determines if a product is a bundle
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @return bool
 */
function edd_is_bundled_product( $download_id = 0 ) {
	$download = new EDD_Download( $download_id );
	return $download->is_bundled_download();
}


/**
 * Retrieves the product IDs of bundled products
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @return array $products Products in the bundle
 *
 * @since 2.7
 * @param int $price_id Variable price ID
 */
function edd_get_bundled_products( $download_id = 0, $price_id = null ) {
	$download = new EDD_Download( $download_id );
	if ( null !== $price_id ) {
		return $download->get_variable_priced_bundled_downloads( $price_id );
	} else {
		return $download->bundled_downloads;
	}
}

/**
 * Returns the total earnings for a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @return int $earnings Earnings for a certain download
 */
function edd_get_download_earnings_stats( $download_id = 0 ) {
	$download = new EDD_Download( $download_id );
	return $download->earnings;
}

/**
 * Return the sales number for a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @return int $sales Amount of sales for a certain download
 */
function edd_get_download_sales_stats( $download_id = 0 ) {
	$download = new EDD_Download( $download_id );
	return $download->sales;
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
 * @param string|null $sale_date The date of the sale
 * @return void
*/
function edd_record_sale_in_log( $download_id = 0, $payment_id, $price_id = false, $sale_date = null ) {
	global $edd_logs;

	$log_data = array(
		'post_parent'   => $download_id,
		'log_type'      => 'sale',
		'post_date'     => ! empty( $sale_date ) ? $sale_date : null,
		'post_date_gmt' => ! empty( $sale_date ) ? get_gmt_from_date( $sale_date ) : null
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
 * @param array $user_info User information (Deprecated)
 * @param string $ip IP Address
 * @param int $payment_id Payment ID
 * @param int $price_id Price ID, if any
 * @return void
 */
function edd_record_download_in_log( $download_id = 0, $file_id, $user_info, $ip, $payment_id, $price_id = false ) {
	global $edd_logs;

	$log_data = array(
		'post_parent' => $download_id,
		'log_type'    => 'file_download',
	);

	$payment = new EDD_Payment( $payment_id );

	$log_meta = array(
		'customer_id' => $payment->customer_id,
		'user_id'     => $payment->user_id,
		'file_id'     => (int) $file_id,
		'ip'          => $ip,
		'payment_id'  => $payment_id,
		'price_id'    => (int) $price_id,
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
 * @param int $quantity Quantity to increase purchase count by
 * @return bool|int
 */
function edd_increase_purchase_count( $download_id = 0, $quantity = 1 ) {
	$quantity = (int) $quantity;
	$download = new EDD_Download( $download_id );
	return $download->increase_sales( $quantity );
}

/**
 * Decreases the sale count of a download. Primarily for when a purchase is
 * refunded.
 *
 * @since 1.0.8.1
 * @param int $download_id Download ID
 * @return bool|int
 */
function edd_decrease_purchase_count( $download_id = 0, $quantity = 1 ) {
	$download = new EDD_Download( $download_id );
	return $download->decrease_sales( $quantity );
}

/**
 * Increases the total earnings of a download.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @param int $amount Earnings
 * @return bool|int
 */
function edd_increase_earnings( $download_id = 0, $amount ) {
	$download = new EDD_Download( $download_id );
	return $download->increase_earnings( $amount );
}

/**
 * Decreases the total earnings of a download. Primarily for when a purchase is refunded.
 *
 * @since 1.0.8.1
 * @param int $download_id Download ID
 * @param int $amount Earnings
 * @return bool|int
 */
function edd_decrease_earnings( $download_id = 0, $amount ) {
	$download = new EDD_Download( $download_id );
	return $download->decrease_earnings( $amount );
}

/**
 * Retrieves the average monthly earnings for a specific download
 *
 * @since 1.3
 * @param int $download_id Download ID
 * @return float $earnings Average monthly earnings
 */
function edd_get_average_monthly_download_earnings( $download_id = 0 ) {
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
function edd_get_average_monthly_download_sales( $download_id = 0 ) {
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
	$download = new EDD_Download( $download_id );
	return $download->get_files( $variable_price_id );
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
	if( empty( $file ) || ! is_array( $file ) ) {
		return false;
	}

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
	$download = new EDD_Download( $download_id );
	return $download->get_file_download_limit();
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

	// Assume that the file download limit has not been hit.
	$ret                = false;
	$download_limit     = edd_get_file_download_limit( $download_id );

	if ( ! empty( $download_limit ) ) {

		// The store does not have unlimited downloads, does this payment?
		$unlimited_purchase = edd_payment_has_unlimited_downloads( $payment_id );

		if ( empty( $unlimited_purchase ) ) {

			// Get the file download count.
			$logs = new EDD_Logging();

			$meta_query = array(
				'relation'  => 'AND',
				array(
					'key'   => '_edd_log_file_id',
					'value' => (int) $file_id,
				),
				array(
					'key'   => '_edd_log_payment_id',
					'value' => (int) $payment_id,
				),
				array(
					'key'   => '_edd_log_price_id',
					'value' => (int) $price_id,
				)
			);

			$download_count     = $logs->get_log_count( $download_id, 'file_download', $meta_query );

			if ( $download_count >= $download_limit ) {
				$ret = true;

				// Check to make sure the limit isn't overwritten.
				// A limit is overwritten when purchase receipt is resent.
				$limit_override = edd_get_file_download_limit_override( $download_id, $payment_id );

				if ( ! empty( $limit_override ) && $download_count < $limit_override ) {
					$ret = false;
				}
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
function edd_get_file_price_condition( $download_id = 0, $file_key ) {
	$download = new EDD_Download( $download_id );
	return $download->get_file_price_condition( $file_key );
}

/**
 * Get Download File Url
 * Constructs a secure file download url for a specific file.
 *
 * @since 1.0
 *
 * @param string    $key Payment key. Use edd_get_payment_key() to get key.
 * @param string    $email Customer email address. Use edd_get_payment_user_email() to get user email.
 * @param int       $filekey Index of array of files returned by edd_get_download_files() that this download link is for.
 * @param int       $download_id Optional. ID of download this download link is for. Default is 0.
 * @param bool|int  $price_id Optional. Price ID when using variable prices. Default is false.
 *
 * @return string A secure download URL
 */
function edd_get_download_file_url( $key, $email, $filekey, $download_id = 0, $price_id = false ) {

	$hours = absint( edd_get_option( 'download_link_expiration', 24 ) );

	if ( ! ( $date = strtotime( '+' . $hours . 'hours', current_time( 'timestamp') ) ) ) {
		$date = 2147472000; // Highest possible date, January 19, 2038
	}

	// Leaving in this array and the filter for backwards compatibility now
	$old_args = array(
		'download_key' 	=> rawurlencode( $key ),
		'email'         => rawurlencode( $email ),
		'file'          => rawurlencode( $filekey ),
		'price_id'      => (int) $price_id,
		'download_id'   => $download_id,
		'expire'        => rawurlencode( $date )
	);

	$params  = apply_filters( 'edd_download_file_url_args', $old_args );
	$payment = edd_get_payment_by( 'key', $params['download_key'] );

	if ( ! $payment ) {
		return false;
	}

	$args = array();

	if ( ! empty( $payment->ID ) ) {

		// Simply the URL by concatenating required data using a colon as a delimiter.
		$args = array(
			'eddfile' => rawurlencode( sprintf( '%d:%d:%d:%d', $payment->ID, $params['download_id'], $params['file'], $price_id ) )
		);

		if ( isset( $params['expire'] ) ) {
			$args['ttl'] = $params['expire'];
		}

		// Ensure all custom args registered with extensions through edd_download_file_url_args get added to the URL, but without adding all the old args
		$args = array_merge( $args, array_diff_key( $params, $old_args ) );

		$args = apply_filters( 'edd_get_download_file_url_args', $args, $payment->ID, $params );

		$args['file']  = $params['file'];
		$args['token'] = edd_get_download_token( add_query_arg( $args, untrailingslashit( site_url() ) ) );
	}

	$download_url = add_query_arg( $args, site_url( 'index.php' ) );

	return $download_url;
}

/**
 * Get product notes
 *
 * @since 1.2.1
 * @param int $download_id Download ID
 * @return string $notes Product notes
 */
function edd_get_product_notes( $download_id = 0 ) {
	$download = new EDD_Download( $download_id );
	return $download->notes;
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
	$download = new EDD_Download( $download_id );
	return $download->sku;
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
	$download = new EDD_Download( $download_id );
	return $download->button_behavior;
}

/**
 * Is quantity input disabled on this product?
 *
 * @since 2.7
 * @return bool
 */
function edd_download_quantities_disabled( $download_id = 0 ) {

	$download = new EDD_Download( $download_id );
	return $download->quantities_disabled();
}

/**
 * Get the file Download method
 *
 * @since 1.6
 * @return string The method to use for file downloads
 */
function edd_get_file_download_method() {
	$method = edd_get_option( 'download_method', 'direct' );
	return apply_filters( 'edd_file_download_method', $method );
}

/**
 * Returns a random download
 *
 * @since 1.7
 * @author Chris Christoff
 * @param bool $post_ids True for array of post ids, false if array of posts
 * @return array Returns an array of post ids or post objects
 */
function edd_get_random_download( $post_ids = true ) {
	 return edd_get_random_downloads( 1, $post_ids );
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
		$args = array( 'post_type' => 'download', 'orderby' => 'rand', 'numberposts' => $num, 'fields' => 'ids' );
	} else {
		$args = array( 'post_type' => 'download', 'orderby' => 'rand', 'numberposts' => $num );
	}
	$args  = apply_filters( 'edd_get_random_downloads', $args );
	return get_posts( $args );
}

/**
 * Generates a token for a given URL.
 *
 * An 'o' query parameter on a URL can include optional variables to test
 * against when verifying a token without passing those variables around in
 * the URL. For example, downloads can be limited to the IP that the URL was
 * generated for by adding 'o=ip' to the query string.
 *
 * Or suppose when WordPress requested a URL for automatic updates, the user
 * agent could be tested to ensure the URL is only valid for requests from
 * that user agent.
 *
 * @since 2.3
 *
 * @param string $url The URL to generate a token for.
 * @return string The token for the URL.
 */
function edd_get_download_token( $url = '' ) {

	$args    = array();
	$hash    = apply_filters( 'edd_get_url_token_algorithm', 'sha256' );
	$secret  = apply_filters( 'edd_get_url_token_secret', hash( $hash, wp_salt() ) );

	/*
	 * Add additional args to the URL for generating the token.
	 * Allows for restricting access to IP and/or user agent.
	 */
	$parts   = parse_url( $url );
	$options = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// o = option checks (ip, user agent).
		if ( ! empty( $query_args['o'] ) ) {

			// Multiple options can be checked by separating them with a colon in the query parameter.
			$options = explode( ':', rawurldecode( $query_args['o'] ) );

			if ( in_array( 'ip', $options ) ) {

				$args['ip'] = edd_get_ip();

			}

			if ( in_array( 'ua', $options ) ) {

				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$args['user_agent'] = rawurlencode( $ua );

			}

		}

	}

	/*
	 * Filter to modify arguments and allow custom options to be tested.
	 * Be sure to rawurlencode any custom options for consistent results.
	 */
	$args = apply_filters( 'edd_get_url_token_args', $args, $url, $options );

	$args['secret'] = $secret;
	$args['token']  = false; // Removes a token if present.

	$url   = add_query_arg( $args, $url );
	$parts = parse_url( $url );

	// In the event there isn't a path, set an empty one so we can MD5 the token
	if ( ! isset( $parts['path'] ) ) {

		$parts['path'] = '';

	}

	$token = hash_hmac( 'sha256', $parts['path'] . '?' . $parts['query'], wp_salt( 'edd_file_download_link' ) );
	return $token;

}

/**
 * Generate a token for a URL and match it against the existing token to make
 * sure the URL hasn't been tampered with.
 *
 * @since 2.3
 *
 * @param string $url URL to test.
 * @return bool
 */
function edd_validate_url_token( $url = '' ) {

	$ret   = false;
	$parts = parse_url( $url );

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// These are the only URL parameters that are allowed to affect the token validation
		$allowed = apply_filters( 'edd_url_token_allowed_params', array(
			'eddfile',
			'file',
			'ttl',
			'token'
		) );

		// Parameters that will be removed from the URL before testing the token
		$remove = array();

		foreach( $query_args as $key => $value ) {
			if( false === in_array( $key, $allowed ) ) {
				$remove[] = $key;
			}
		}

		if( ! empty( $remove ) ) {

			$url = remove_query_arg( $remove, $url );

		}

		if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {

			wp_die( apply_filters( 'edd_download_link_expired_text', __( 'Sorry but your download link has expired.', 'easy-digital-downloads' ) ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );

		}

		if ( isset( $query_args['token'] ) && hash_equals( $query_args['token'], edd_get_download_token( $url ) ) ) {

			$ret = true;

		}

	}

	return apply_filters( 'edd_validate_url_token', $ret, $url, $query_args );
}

/**
 * Allows parsing of the values saved by the product drop down.
 *
 * @since  2.6.9
 * @param  array $values Parse the values from the product dropdown into a readable array
 * @return array         A parsed set of values for download_id and price_id
 */
function edd_parse_product_dropdown_values( $values = array() ) {

	$parsed_values = array();

	if ( is_array( $values ) ) {

		foreach ( $values as $value ) {
			$value = edd_parse_product_dropdown_value( $value );

			$parsed_values[] = array(
				'download_id' => $value['download_id'],
				'price_id'    => $value['price_id'],
			);
		}

	} else {

		$value = edd_parse_product_dropdown_value( $values );
		$parsed_values[] = array(
			'download_id' => $value['download_id'],
			'price_id'    => $value['price_id'],
		);

	}

	return $parsed_values;
}

/**
 * Given a value from the product dropdown array, parse it's parts
 *
 * @since  2.6.9
 * @param  string $values A value saved in a product dropdown array
 * @return array          A parsed set of values for download_id and price_id
 */
function edd_parse_product_dropdown_value( $value ) {
	$parts       = explode( '_', $value );
	$download_id = $parts[0];
	$price_id    = isset( $parts[1] ) ? $parts[1] : false;

	return array( 'download_id' => $download_id, 'price_id' => $price_id );
}

/**
 * Get bundle pricing variations
 *
 * @since  2.7
 * @param  int $download_id
 * @return array|void
 */
function edd_get_bundle_pricing_variations( $download_id = 0 ) {
	if ( $download_id == 0 ) {
		return;
	}

	$download = new EDD_Download( $download_id );
	return $download->get_bundle_pricing_variations();
}
