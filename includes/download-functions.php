<?php
/**
 * Download Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a download by a given field.
 *
 * @since 2.0
 *
 * @param string $field Field to retrieve the download with.
 * @param mixed  $value Value of the row.
 *
 * @return WP_Post|false WP_Post object if download found, false otherwise.
 */
function edd_get_download_by( $field = '', $value = '' ) {

	// Bail if empty values passed.
	if ( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch ( strtolower( $field ) ) {
		case 'id':
			$download = get_post( $value );

			if ( 'download' !== get_post_type( $download ) ) {
				return false;
			}

			break;

		case 'slug':
		case 'name':
			$download = get_posts( array(
				'post_type'      => 'download',
				'name'           => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any',
			) );

			if ( $download ) {
				$download = $download[0];
			}

			break;

		case 'sku':
			$download = get_posts( array(
				'post_type'      => 'download',
				'meta_key'       => 'edd_sku',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any',
			) );

			if ( $download ) {
				$download = $download[0];
			}

			break;

		default:
			return false;
	}

	return $download ?: false;
}

/**
 * Retrieves a download post object by ID or slug.
 *
 * @since 1.0
 * @since 2.9 - Return an EDD_Download object.
 *
 * @param int $download_id Download ID.
 * @return EDD_Download|null EDD_Download object if found, null otherwise.
 */
function edd_get_download( $download_id = 0 ) {
	$download = null;

	if ( is_numeric( $download_id ) ) {
		$found_download = new EDD_Download( $download_id );

		if ( ! empty( $found_download->ID ) ) {
			$download = $found_download;
		}

	// Fetch download by name.
	} else {
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
 * Checks whether or not a download is free.
 *
 * @since 2.1
 *
 * @param int $download_id Download ID.
 * @param int $price_id    Optional. Price ID.
 * @return bool $is_free True if the product is free, false if the product is not free or the check fails
 */
function edd_is_free_download( $download_id = 0, $price_id = false ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->is_free( $price_id )
		: false;
}

/**
 * Return the name of a download.
 *
 * Pass a price ID to append the specific price variation name.
 *
 * @since 3.0
 *
 * @param int $download_id
 * @param int|null $price_id
 *
 * @return false|string
 */
function edd_get_download_name( $download_id = 0, $price_id = null ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) || ! is_numeric( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	// Bail if the download cannot be retrieved.
	if ( ! $download instanceof EDD_Download ) {
		return false;
	}

	// Get the download title
	$retval = $download->get_name();

	// Check for variable pricing
	if ( $download->has_variable_prices() && is_numeric( $price_id ) ) {

		// Check for price option name
		$price_name = edd_get_price_option_name( $download_id, $price_id );

		// Product has prices
		if ( ! empty( $price_name ) ) {
			$retval .= ' — ' . $price_name;
		}
	}

	/**
	 * Override the download name.
	 *
	 * @since 3.0
	 *
	 * @param string $retval   The download name.
	 * @param int    $id       The download ID.
	 * @param int    $price_id The price ID, if any.
	 */
	return apply_filters( 'edd_get_download_name', $retval, $download_id, $price_id );
}

/**
 * Returns the price of a download, but only for non-variable priced downloads.
 *
 * @since 1.0
 *
 * @param int $download_id Download ID.
 * @return string|int Price of the download.
 */
function edd_get_download_price( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_price()
		: 0;
}

/**
 * Displays a formatted price for a download.
 *
 * @since 1.0
 *
 * @param int  $download_id Download ID.
 * @param bool $echo        Optional. Whether to echo or return the result. Default true.
 * @param int  $price_id    Optional. Price ID.
 *
 * @return string Download price if $echo set to false.
 */
function edd_price( $download_id = 0, $echo = true, $price_id = false ) {

	// Attempt to get the ID of the current item in the WordPress loop.
	if ( empty( $download_id ) || ! is_numeric( $download_id ) ) {
		$download_id = get_the_ID();
	}

	// Variable prices
	if ( edd_has_variable_prices( $download_id ) ) {

		// Get the price variations
		$prices = edd_get_variable_prices( $download_id );

		// Use the amount for the price ID
		if ( is_numeric( $price_id ) && isset( $prices[ $price_id ] ) ) {
			$price = edd_get_price_option_amount( $download_id, $price_id );

		// Maybe use the default variable price
		} elseif ( $default = edd_get_default_variable_price( $download_id ) ) {
			$price = edd_get_price_option_amount( $download_id, $default );

		// Maybe guess the lowest price
		} else {
			$price = edd_get_lowest_price_option( $download_id );
		}

	// Single price (not variable)
	} else {
		$price = edd_get_download_price( $download_id );
	}

	// Filter the price (already sanitized)
	$price           = apply_filters( 'edd_download_price', $price, $download_id, $price_id );

	// Format the price (do not escape $price)
	$formatted_price = '<span class="edd_price" id="edd_price_' . esc_attr( $download_id ) . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'edd_download_price_after_html', $formatted_price, $download_id, $price, $price_id );

	// Echo or return
	if ( ! empty( $echo ) ) {
		echo $formatted_price; // WPCS: XSS ok.
	} else {
		return $formatted_price;
	}
}
add_filter( 'edd_download_price', 'edd_format_amount',   10 );
add_filter( 'edd_download_price', 'edd_currency_filter', 20 );

/**
 * Retrieves the final price of a downloadable product after purchase.
 * This price includes any necessary discounts that were applied
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

	if ( isset( $user_purchase_info['discount'] ) && 'none' !== $user_purchase_info['discount'] ) {

		// If the discount was a percentage, we modify the amount.
		// Flat rate discounts are ignored
		if ( EDD_Discount::FLAT !== edd_get_discount_type( edd_get_discount_id_by_code( $user_purchase_info['discount'] ) ) ) {
			$price = edd_get_discounted_amount( $user_purchase_info['discount'], $original_price );
		} else {
			$price = $original_price;
		}
	} else {
		$price = $original_price;
	}

	// Filter & return.
	return apply_filters( 'edd_final_price', $price, $download_id, $user_purchase_info );
}

/**
 * Retrieves the variable prices for a download.
 *
 * @since 1.2
 *
 * @param int $download_id Download ID.
 * @return array|false Variable prices if found, false otherwise.
 */
function edd_get_variable_prices( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_prices()
		: false;
}

/**
 * Checks to see if a download has variable prices enabled.
 *
 * @since 1.0.7
 *
 * @param int $download_id Download ID.
 * @return bool True if the download has variable prices, false otherwise.
 */
function edd_has_variable_prices( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = new EDD_Download( $download_id );

	return $download
		? $download->has_variable_prices()
		: false;
}

/**
 * Returns the default price ID for variable pricing, or the first price if
 * none set.
 *
 * @since 2.2
 * @since 3.1.2 Moved this behavior into the EDD_Download class as it really does belong there.
 *
 * @param  int $download_id Download ID.
 * @return int|null The default price ID, or false if the product does not have variable prices.
 */
function edd_get_default_variable_price( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( ! is_numeric( $download_id ) || empty( $download_id ) ) {
		return null;
	}

	$download = new EDD_Download( $download_id );

	return $download->get_default_price_id();
}

/**
 * Retrieves the name of a variable price option.
 *
 * @since 1.0.9
 * @since 3.0 Renamed $payment_id parameter to $order_id.
 *
 * @param int $download_id Download ID.
 * @param int $price_id    Price ID.
 * @param int $order_id    Optional. Order ID for use in filters.
 *
 * @return string $price_name Name of the price option.
 */
function edd_get_price_option_name( $download_id = 0, $price_id = 0, $order_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	// Fetch variable prices.
	$prices = edd_get_variable_prices( $download_id );

	$price_name = '';

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) ) {
			$price_name = $prices[ $price_id ]['name'];
		}
	}

	return apply_filters( 'edd_get_price_option_name', $price_name, $download_id, $order_id, $price_id );
}

/**
 * Retrieves the amount for a variable price option.
 *
 * @since 1.8.2
 *
 * @param int $download_id Download ID.
 * @param int $price_id    Price ID.
 *
 * @return float $amount Price option amount.
 */
function edd_get_price_option_amount( $download_id = 0, $price_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	// Fetch variable prices.
	$prices = edd_get_variable_prices( $download_id );

	// Set default prices.
	$amount = 0.00;

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) ) {
			$amount = $prices[ $price_id ]['amount'];
		}
	}

	// Filter & return.
	return apply_filters( 'edd_get_price_option_amount', edd_sanitize_amount( $amount ), $download_id, $price_id );
}

/**
 * Retrieve the lowest price option of a variable priced download.
 *
 * @since 1.4.4
 *
 * @param int $download_id Download ID.
 * @return float Amount of the lowest price option.
 */
function edd_get_lowest_price_option( $download_id = 0 ) {

	// Attempt to get the ID of the current item in the WordPress loop.
	if ( empty( $download_id ) ) {
		$download_id = get_the_ID();
	}

	// Bail if download ID is still empty.
	if ( empty( $download_id ) ) {
		return false;
	}

	// Return download price if variable prices do not exist for download.
	if ( ! edd_has_variable_prices( $download_id ) ) {
		return edd_get_download_price( $download_id );
	}

	// Set lowest to 0.
	$lowest       = 0.00;
	$prices       = edd_get_variable_prices( $download_id );
	$list_handler = new EDD\Utils\ListHandler( $prices );
	$min_key      = $list_handler->search( 'amount', 'min' );
	if ( false !== $min_key ) {
		$lowest = $prices[ $min_key ]['amount'];
	}

	return edd_sanitize_amount( $lowest );
}

/**
 * Retrieves the ID for the cheapest price option of a variable priced download.
 *
 * @since 2.2
 *
 * @param int $download_id Download ID.
 * @return int|false ID of the lowest price, false if download does not exist.
 */
function edd_get_lowest_price_id( $download_id = 0 ) {

	// Attempt to get the ID of the current item in the WordPress loop.
	if ( empty( $download_id ) ) {
		$download_id = get_the_ID();
	}

	// Bail if download ID is still empty.
	if ( empty( $download_id ) ) {
		return false;
	}

	// Return download price if variable prices do not exist for download.
	if ( ! edd_has_variable_prices( $download_id ) ) {
		return edd_get_download_price( $download_id );
	}

	$list_handler = new EDD\Utils\ListHandler( edd_get_variable_prices( $download_id ) );
	$min_key      = $list_handler->search( 'amount', 'min' );

	return false !== $min_key ? absint( $min_key ) : false;
}

/**
 * Retrieves most expensive price option of a variable priced download
 *
 * @since 1.4.4
 * @param int $download_id ID of the download
 * @return float Amount of the highest price
 */
function edd_get_highest_price_option( $download_id = 0 ) {

	// Attempt to get the ID of the current item in the WordPress loop.
	if ( empty( $download_id ) ) {
		$download_id = get_the_ID();
	}

	// Bail if download ID is still empty.
	if ( empty( $download_id ) ) {
		return false;
	}

	// Return download price if variable prices do not exist for download.
	if ( ! edd_has_variable_prices( $download_id ) ) {
		return edd_get_download_price( $download_id );
	}

	// Set highest to 0.
	$highest      = 0.00;
	$prices       = edd_get_variable_prices( $download_id );
	$list_handler = new EDD\Utils\ListHandler( $prices );
	$max_key      = $list_handler->search( 'amount', 'max' );
	if ( false !== $max_key ) {
		$highest = $prices[ $max_key ]['amount'];
	}

	return edd_sanitize_amount( $highest );
}

/**
 * Retrieves a price from from low to high of a variable priced download.
 *
 * @since 1.4.4
 *
 * @param int $download_id Download ID.
 * @return string $range A fully formatted price range.
 */
function edd_price_range( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$low    = edd_get_lowest_price_option( $download_id );
	$high   = edd_get_highest_price_option( $download_id );
	$range  = '<span class="edd_price edd_price_range_low" id="edd_price_low_' . $download_id . '">' . edd_currency_filter( edd_format_amount( $low ) ) . '</span>';
	$range .= '<span class="edd_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
	$range .= '<span class="edd_price edd_price_range_high" id="edd_price_high_' . $download_id . '">' . edd_currency_filter( edd_format_amount( $high ) ) . '</span>';

	return apply_filters( 'edd_price_range', $range, $download_id, $low, $high );
}

/**
 * Checks to see if multiple price options can be purchased at once.
 *
 * @since 1.4.2
 *
 * @param int $download_id Download ID.
 * @return bool True if multiple price options can be purchased at once, false otherwise.
 */
function edd_single_price_option_mode( $download_id = 0 ) {

	// Attempt to get the ID of the current item in the WordPress loop.
	if ( empty( $download_id ) ) {
		$download_id = get_the_ID();
	}

	// Bail if download ID is still empty.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );
	return $download
		? $download->is_single_price_mode()
		: false;
}

/**
 * Get product types.
 *
 * @since 1.8
 *
 * @return array $types Download types.
 */
function edd_get_download_types() {
	$types = array(
		''        => __( 'Single Product', 'easy-digital-downloads' ),
		'bundle'  => __( 'Bundle', 'easy-digital-downloads' ),
		'service' => __( 'Service', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_download_types', $types );
}

/**
 * Get the download type: either `default` or `bundled`.
 *
 * @since 1.6
 *
 * @param int $download_id Download ID.
 * @return string $type Download type.
 */
function edd_get_download_type( $download_id = 0 ) {
	$download = edd_get_download( $download_id );

	return $download
		? $download->type
		: false;
}

/**
 * Determines if a product is a bundle.
 *
 * @since 1.6
 *
 * @param int $download_id Download ID.
 * @return bool True if a bundle, false otherwise.
 */
function edd_is_bundled_product( $download_id = 0 ) {
	$download = edd_get_download( $download_id );

	return $download
		? $download->is_bundled_download()
		: false;
}


/**
 * Retrieves the product IDs of bundled products.
 *
 * @since 1.6
 * @since 2.7 Added $price_id parameter.
 *
 * @param int $download_id Download ID.
 * @param int $price_id    Optional. Price ID. Default null.
 *
 * @return array|false Products in the bundle, false if download does not exist.
 */
function edd_get_bundled_products( $download_id = 0, $price_id = null ) {
	$download = edd_get_download( $download_id );

	// Bail if download does not exist.
	if ( ! $download ) {
		return false;
	}

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
 *
 * @param int $download_id Download ID.
 * @return float|false $earnings Download earnings, false if download not found.
 */
function edd_get_download_earnings_stats( $download_id = 0 ) {
	$download = edd_get_download( $download_id );

	return $download
		? $download->earnings
		: false;
}

/**
 * Return the sales number for a download.
 *
 * @since 1.0
 *
 * @param int $download_id Download ID.
 * @return int|false Number of sales, false if download was not found.
 */
function edd_get_download_sales_stats( $download_id = 0 ) {
	$download = edd_get_download( $download_id );

	return $download
		? $download->sales
		: false;
}

/**
 * Record a file download.
 *
 * @since 1.0
 * @since 3.0 Refactored to use new query methods.
 *
 * @param int    $download_id Download ID.
 * @param int    $file_id     File ID.
 * @param array  $user_info   User information (deprecated).
 * @param string $ip          Optional. IP address.
 * @param int    $order_id    Order ID.
 * @param int    $price_id    Optional. Price ID,
 * @param string $user_agent  Optional. User agent.
 * @return void
 */
function edd_record_download_in_log( $download_id = 0, $file_id = 0, $user_info = array(), $ip = '', $order_id = 0, $price_id = 0, $user_agent = '' ) {
	$order = edd_get_order( $order_id );

	if ( empty( $user_agent ) ) {
		if ( ! class_exists( 'Browser' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/libraries/browser.php';
		}
		$browser    = new Browser();
		$user_agent = $browser->getBrowser() . ' ' . $browser->getVersion() . '/' . $browser->getPlatform();
	}

	if ( empty( $ip ) ) {
		$ip = edd_get_ip();
	}

	$file_id   = absint( $file_id );
	$files     = edd_get_download_files( $download_id );
	$file_name = '';

	if ( is_array( $files ) ) {
		foreach ( $files as $key => $file ) {
			if ( absint( $key ) === $file_id ) {
				$file_name = edd_get_file_name( $file );
				break;
			}
		}
	}

	$log_id = edd_add_file_download_log( array(
		'product_id'  => absint( $download_id ),
		'file_id'     => $file_id,
		'order_id'    => absint( $order_id ),
		'price_id'    => absint( $price_id ),
		'customer_id' => $order->customer_id,
		'ip'          => sanitize_text_field( $ip ),
		'user_agent'  => $user_agent,
	) );

	if ( $log_id && ! empty( $file_name ) ) {
		edd_add_file_download_log_meta( $log_id, 'file_name', $file_name );
	}
}

/**
 * Delete log entries when deleting downloads.
 *
 * Removes all related log entries when a download is completely deleted.
 * (Does not run when a download is trashed)
 *
 * @since 1.3.4
 * @since 3.0 Updated to use new query methods.
 *
 * @param int $download_id Download ID.
 */
function edd_remove_download_logs_on_delete( $download_id = 0 ) {
	global $wpdb;

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return;
	}

	// Ensure download ID is an integer.
	$download_id = absint( $download_id );

	// Bail if the post type is not `download`.
	if ( 'download' !== get_post_type( $download_id ) ) {
		return;
	}

	// Delete file download logs.
	$wpdb->delete( $wpdb->edd_logs_file_downloads, array(
		'product_id' => $download_id,
	), array( '%d' ) );

	// Delete logs.
	$wpdb->delete( $wpdb->edd_logs, array(
		'object_id'   => $download_id,
		'object_type' => 'download',
	), array( '%d', '%s' ) );
}
add_action( 'delete_post', 'edd_remove_download_logs_on_delete' );

/**
 * Recalculates both the net and gross sales and earnings for a download.
 *
 * @since 3.0
 * @param int $download_id
 * @return void
 */
function edd_recalculate_download_sales_earnings( $download_id ) {
	$download = edd_get_download( $download_id );
	if ( ! $download instanceof \EDD_Download ) {
		return;
	}
	$download->recalculate_net_sales_earnings();
	$download->recalculate_gross_sales_earnings();
}

/**
 * Retrieves the average monthly earnings for a specific download.
 *
 * @since 1.3
 *
 * @param int $download_id Download ID.
 * @return float $earnings Average monthly earnings.
 */
function edd_get_average_monthly_download_earnings( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return 0;
	}

	$earnings     = edd_get_download_earnings_stats( $download_id );
	$release_date = get_post_field( 'post_date', $download_id );

	$diff = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	// Number of months since publication
	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) );

	if ( $months > 0 ) {
		$earnings = ( $earnings / $months );
	}

	return $earnings < 0
		? 0
		: $earnings;
}

/**
 * Retrieves the average monthly sales for a specific download.
 *
 * @since 1.3
 *
 * @param int $download_id Download ID.
 * @return float $sales Average monthly sales.
 */
function edd_get_average_monthly_download_sales( $download_id = 0 ) {
	$sales        = edd_get_download_sales_stats( $download_id );
	$release_date = get_post_field( 'post_date', $download_id );

	$diff = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	// Number of months since publication
	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) );

	if ( $months > 0 ) {
		$sales = ( $sales / $months );
	}

	return $sales;
}

/**
 * Gets all download files for a product.
 *
 * @since 1.0
 * @since 3.0 Renamed $variable_price_id parameter to $price)id for consistency.
 *
 * @param int $download_id Download ID.
 * @param int $price_id    Optional. Price ID. Default null.
 *
 * @return array|false Download files, false if invalid data was passed.
 */
function edd_get_download_files( $download_id = 0, $price_id = null ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_files( $price_id )
		: false;
}

/**
 * Retrieves a file name for a file attached to a download. Defaults to the
 * file's actual name if no 'name' key is present.
 *
 * @since 1.6
 *
 * @param array $file File information.
 * @return string Filename.
 */
function edd_get_file_name( $file = array() ) {

	// Bail if no data was passed.
	if ( empty( $file ) || ! is_array( $file ) ) {
		return false;
	}

	$name = ! empty( $file['name'] )
		? esc_html( $file['name'] )
		: basename( $file['file'] );

	return $name;
}

/**
 * Gets the number of times a file has been downloaded for a specific order.
 *
 * @since 1.6
 * @since 3.0 Renamed parameters for consistency across new query methods
 *            introduced.
 *            Refactored to use new query methods.
 *
 * @param int $download_id Download ID.
 * @param int $file_id     File ID.
 * @param int $order_id    Order ID.
 *
 * @return int Number of times the file has been downloaded for the order.
 */
function edd_get_file_downloaded_count( $download_id = 0, $file_id = 0, $order_id = 0 ) {

	// Bail if no download ID or order ID was passed.
	if ( empty( $download_id ) || empty( $order_id ) ) {
		return false;
	}

	// Ensure arguments passed are valid.
	$download_id = absint( $download_id );
	$file_id     = absint( $file_id );
	$order_id    = absint( $order_id );

	return edd_count_file_download_logs( array(
		'product_id' => $download_id,
		'order_id'   => $order_id,
		'file_id'    => $file_id,
	) );
}


/**
 * Gets the file download file limit for a particular download. This limit refers
 * to the maximum number of times files connected to a product can be downloaded.
 *
 * @since 1.3.1
 *
 * @param int $download_id Download ID.
 * @return int|false File download limit, false if invalid download ID passed.
 */
function edd_get_file_download_limit( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_file_download_limit()
		: false;
}

/**
 * Gets the file refund window for a particular download
 *
 * This window refers to the maximum number of days it can be refunded after
 * it has been purchased.
 *
 * @since 3.0
 *
 * @param int $download_id Download ID.
 * @return int Refund window.
 */
function edd_get_download_refund_window( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_refund_window()
		: false;
}

/**
 * Get the refundability status for a download.
 *
 * @since 3.0
 *
 * @param int $download_id Download ID.
 * @return string `refundable` or `nonrefundable`.
 */
function edd_get_download_refundability( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_refundability()
		: false;
}

/**
 * Gets the file download file limit override for a particular download.
 * The override allows the main file download limit to be bypassed.
 *
 * @since 1.3.2
 * @since 3.0 Renamed $payment_id parameter to $order_id.
 *
 * @param int $download_id Download ID.
 * @param int $order_id    Order ID.
 *
 * @return int|false New file download limit, false if invalid download ID passed.
*/
function edd_get_file_download_limit_override( $download_id = 0, $order_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$limit_override = get_post_meta( $download_id, '_edd_download_limit_override_' . $order_id, true );

	return $limit_override
		? absint( $limit_override )
		: 0;
}

/**
 * Sets the file download file limit override for a particular download.
 *
 * The override allows the main file download limit to be bypassed.
 * If no override is set yet, the override is set to the main limit + 1.
 * If the override is already set, then it is simply incremented by 1.
 *
 * @since 1.3.2
 * @since 3.0 Renamed $payment_id parameter to $order_id.
 *
 * @param int $download_id Download ID.
 * @param int $order_id    Order ID.
 *
 * @return false False if invalid download ID or order ID was passed.
 */
function edd_set_file_download_limit_override( $download_id = 0, $order_id = 0 ) {

	// Bail if no download ID or order ID was passed.
	if ( empty( $download_id ) || empty( $order_id ) ) {
		return false;
	}

	$override = edd_get_file_download_limit_override( $download_id, $order_id );
	$limit    = edd_get_file_download_limit( $download_id );

	if ( ! empty( $override ) ) {
		$override = $override += 1;
	} else {
		$override = $limit += 1;
	}

	update_post_meta( $download_id, '_edd_download_limit_override_' . $order_id, $override );
}


/**
 * Checks if a file is at its download limit
 *
 * This limit refers to the maximum number of times files connected to a product
 * can be downloaded.
 *
 * @since 1.3.1
 * @since 3.0 Refactored to use new query methods.
 *            Renamed $payment_id parameter to $order_id.
 *            Set default value of $price_id to 0.
 *
 * @param int $download_id Download ID.
 * @param int $order_id    Order ID.
 * @param int $file_id     File ID.
 * @param int $price_id    Price ID.
 *
 * @return bool True if at limit, false otherwise.
 */
function edd_is_file_at_download_limit( $download_id = 0, $order_id = 0, $file_id = 0, $price_id = 0 ) {

	// Bail if invalid data was passed.
	if ( empty( $download_id ) || empty( $order_id ) ) {
		return false;
	}

	// Sanitize parameters.
	$download_id = absint( $download_id );
	$order_id    = absint( $order_id );
	$file_id     = absint( $file_id );
	$price_id    = absint( $price_id );

	// Default to false.
	$ret            = false;
	$download_limit = edd_get_file_download_limit( $download_id );

	if ( ! empty( $download_limit ) ) {
		$unlimited_purchase = edd_payment_has_unlimited_downloads( $order_id );

		if ( empty( $unlimited_purchase ) ) {
			// Retrieve the file download count.
			$download_count = edd_count_file_download_logs( array(
				'product_id' => $download_id,
				'file_id'    => $file_id,
				'order_id'   => $order_id,
				'price_id'   => $price_id,
			) );

			if ( $download_count >= $download_limit ) {
				$ret = true;

				// Check to make sure the limit isn't overwritten.
				// A limit is overwritten when purchase receipt is resent.
				$limit_override = edd_get_file_download_limit_override( $download_id, $order_id );

				if ( ! empty( $limit_override ) && $download_count < $limit_override ) {
					$ret = false;
				}
			}
		}
	}

	/**
	 * Filters whether or not a file is at its download limit.
	 *
	 * @param bool $ret
	 * @param int  $download_id
	 * @param int  $payment_id
	 * @param int  $file_id
	 * @param int  $price_id
	 *
	 * @since 2.10 Added `$price_id` parameter.
	 */
	return (bool) apply_filters( 'edd_is_file_at_download_limit', $ret, $download_id, $order_id, $file_id, $price_id );
}

/**
 * Retrieve the price option that has access to the specified file.
 *
 * @since 1.0.9
 *
 * @param int    $download_id Download ID.
 * @param string $file_key    File key.
 *
 * @return string|false Price ID if restricted, "all" otherwise, false if no download ID was passed.
 */
function edd_get_file_price_condition( $download_id = 0, $file_key = '' ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_file_price_condition( $file_key )
		: false;
}

/**
 * Constructs a secure file download url for a specific file.
 *
 * @since 1.0
 * @since 3.0  Updated to use new query methods.
 *
 * @param string    $order_or_key The order object or payment key. Using the payment key will eventually be deprecated.
 * @param string    $email        Customer email address. Use edd_get_payment_user_email() to get user email.
 * @param int       $filekey      Index of array of files returned by edd_get_download_files() that this download link is for.
 * @param int       $download_id  Optional. ID of download this download link is for. Default is 0.
 * @param bool|int  $price_id     Optional. Price ID when using variable prices. Default is false.
 *
 * @return string Secure download URL.
 */
function edd_get_download_file_url( $order_or_key, $email, $filekey, $download_id = 0, $price_id = false ) {
	$hours = absint( edd_get_option( 'download_link_expiration', 24 ) );

	if ( ! ( $date = strtotime( '+' . $hours . 'hours', current_time( 'timestamp' ) ) ) ) {
		$date = 2147472000; // Highest possible date, January 19, 2038
	}

	// Fetch order.
	if ( $order_or_key instanceof EDD\Orders\Order ) {
		$order = $order_or_key;
		$key   = $order->payment_key;
	} else {
		$key   = $order_or_key;
		$order = edd_get_order_by( 'payment_key', $key );
	}

	// Leaving in this array and the filter for backwards compatibility now
	$old_args = array(
		'download_key' => rawurlencode( $key ),
		'email'        => rawurlencode( $email ),
		'file'         => rawurlencode( $filekey ),
		'price_id'     => (int) $price_id,
		'download_id'  => $download_id,
		'expire'       => rawurlencode( $date ),
	);

	$params = apply_filters( 'edd_download_file_url_args', $old_args );

	// Bail if order wasn't found.
	if ( ! $order ) {
		return false;
	}

	// Get the array of parameters in the same order in which they will be validated.
	$args = array_fill_keys( edd_get_url_token_parameters(), '' );

	// Simply the URL by concatenating required data using a colon as a delimiter.
	if ( ! is_numeric( $price_id ) ) {
		$eddfile = sprintf( '%d:%d:%d', $order->id, $params['download_id'], $params['file'] );
	} else {
		$eddfile = sprintf( '%d:%d:%d:%d', $order->id, $params['download_id'], $params['file'], $price_id );
	}
	$args['eddfile'] = rawurlencode( $eddfile );

	if ( isset( $params['expire'] ) ) {
		$args['ttl'] = $params['expire'];
	}

	// Ensure all custom args registered with extensions through edd_download_file_url_args get added to the URL, but without adding all the old args
	$args = array_merge( $args, array_diff_key( $params, $old_args ) );

	/**
	 * Allow the file download args to be filtered.
	 *
	 * @since 3.1.1 Includes the order object as the fourth parameter.
	 * @param array            $args     The full array of parameters.
	 * @param int              $order_id The order ID.
	 * @param array            $params   The original array of parameters.
	 * @param EDD\Orders\Order $order    The order object.
	 */
	$args = apply_filters( 'edd_get_download_file_url_args', $args, $order->id, $params, $order );

	$args['file']  = $params['file'];
	$args['token'] = edd_get_download_token( add_query_arg( array_filter( $args ), untrailingslashit( site_url() ) ) );

	return add_query_arg( array_filter( $args ), site_url( 'index.php' ) );
}

/**
 * Gets the array of parameters to be used for the URL token generation and validation.
 * Used by `edd_get_download_file_url` and `edd_validate_url_token` so that their parameters are ordered the same.
 *
 * @since 2.11.4
 * @return array
 */
function edd_get_url_token_parameters() {
	return apply_filters(
		'edd_url_token_allowed_params',
		array(
			'eddfile',
			'ttl',
			'file',
			'token',
		)
	);
}

/**
 * Get product notes.
 *
 * @since 1.2.1
 *
 * @param int $download_id Download ID.
 * @return string|false Product notes, false if invalid data was passed.
 */
function edd_get_product_notes( $download_id = 0 ) {

	// Bail if download ID was not passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->notes
		: false;
}

/**
 * Retrieves a download SKU by ID.
 *
 * @since 1.6
 *
 * @param int $download_id Download ID.
 * @return string|false Download SKU, false if invalid data was passed.
 */
function edd_get_download_sku( $download_id = 0 ) {

	// Bail if download ID was not passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->sku
		: false;
}

/**
 * Retrieve the download button behavior: either add to cart or direct.
 *
 * @since 1.7
 *
 * @param int $download_id Download ID.
 * @return string|false `add_to_cart` or `direct`, false if invalid data was passed.
 */
function edd_get_download_button_behavior( $download_id = 0 ) {

	// Bail if download ID was not passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->button_behavior
		: false;
}

/**
 * Is quantity input disabled on this product?
 *
 * @since 2.7
 *
 * @param int $download_id Download ID.
 * @return bool
 */
function edd_download_quantities_disabled( $download_id = 0 ) {

	// Bail if download ID was not passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->quantities_disabled()
		: false;
}

/**
 * Get the file download method.
 *
 * @since 1.6
 *
 * @return string File download method.
 */
function edd_get_file_download_method() {
	$method = edd_get_option( 'download_method', 'direct' );

	return apply_filters( 'edd_file_download_method', $method );
}

/**
 * Returns a random download.
 *
 * @since 1.7
 *
 * @param bool $post_ids Optional. True for of download IDs, false for WP_Post
 *                       objects. Default true.
 * @return array Download IDs/WP_Post objects.
 */
function edd_get_random_download( $post_ids = true ) {
	return edd_get_random_downloads( 1, $post_ids );
}

/**
 * Returns random downloads.
 *
 * @since 1.7
 *
 * @param int  $num      Number of downloads to return. Default 3.
 * @param bool $post_ids Optional. True for array of WP_Post objects, else
 *                       array of IDs. Default true.
 *
 * @return array Download IDs/WP_Post objects.
 */
function edd_get_random_downloads( $num = 3, $post_ids = true ) {
	$args = array(
		'post_type'   => 'download',
		'orderby'     => 'rand',
		'numberposts' => $num,
	);

	if ( $post_ids ) {
		$args['fields'] = 'ids';
	}

	$args = apply_filters( 'edd_get_random_downloads', $args );

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
 * @param string $url URL to generate a token for.
 * @return string Token for the URL.
 */
function edd_get_download_token( $url = '' ) {
	$args   = array();
	$hash   = apply_filters( 'edd_get_url_token_algorithm', 'sha256' );
	$secret = apply_filters( 'edd_get_url_token_secret', hash( $hash, wp_salt() ) );

	/*
	 * Add additional args to the URL for generating the token.
	 * Allows for restricting access to IP and/or user agent.
	 */
	$parts   = wp_parse_url( $url );
	$options = array();

	if ( isset( $parts['query'] ) ) {
		wp_parse_str( $parts['query'], $query_args );

		// o = option checks (ip, user agent).
		if ( ! empty( $query_args['o'] ) ) {

			// Multiple options can be checked by separating them with a colon in the query parameter.
			$options = explode( ':', rawurldecode( $query_args['o'] ) );

			if ( in_array( 'ip', $options, true ) ) {
				$args['ip'] = edd_get_ip();
			}

			if ( in_array( 'ua', $options, true ) ) {
				$ua = isset( $_SERVER['HTTP_USER_AGENT'] )
					? $_SERVER['HTTP_USER_AGENT']
					: '';

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
	$parts = wp_parse_url( $url );

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
	$ret          = false;
	$parts        = parse_url( $url );
	$query_args   = array();
	$original_url = $url;

	if ( isset( $parts['query'] ) ) {
		wp_parse_str( $parts['query'], $query_args );

		// If the TTL is in the past, die out before we go any further.
		if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {
			wp_die( apply_filters( 'edd_download_link_expired_text', esc_html__( 'Sorry but your download link has expired.', 'easy-digital-downloads' ) ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		// These are the only URL parameters that are allowed to affect the token validation.
		$allowed_args = edd_get_url_token_parameters();

		// Collect the allowed tags in proper order, remove all tags, and re-add only the allowed ones.
		$validated_query_args = array();

		foreach ( $allowed_args as $key ) {
			if ( true === array_key_exists( $key, $query_args ) ) {
				$validated_query_args[ $key ] = $query_args[ $key ];
			}
		}

		// strtok allows a quick clearing of existing query string parameters, so we can re-add the allowed ones.
		$url = add_query_arg( $validated_query_args, strtok( $url, '?' ) );

		if ( isset( $query_args['token'] ) && hash_equals( $query_args['token'], edd_get_download_token( $url ) ) ) {
			$ret = true;
		}
	}

	/**
	 * Filters the URL token validation.
	 *
	 * @param bool   $ret          Whether the URL has validated or not.
	 * @param string $url          The URL used for validation.
	 * @param array  $query_args   The array of query parameters.
	 * @param string $original_url The original URL (added 2.11.3).
	 */
	return apply_filters( 'edd_validate_url_token', $ret, $url, $query_args, $original_url );
}

/**
 * Allows parsing of the values saved by the product drop down.
 *
 * @since 2.6.9
 *
 * @param array $values Parse the values from the product dropdown into a readable array.
 * @return array A parsed set of values for download_id and price_id.
 */
function edd_parse_product_dropdown_values( $values = array() ) {
	$parsed_values = array();

	if ( is_array( $values ) ) {
		foreach ( $values as $value ) {
			$parsed_values[] = edd_parse_product_dropdown_value( $value );
		}
	} else {
		$parsed_values[] = edd_parse_product_dropdown_value( $values );
	}

	return $parsed_values;
}

/**
 * Given a value from the product dropdown array, parse its parts.
 *
 * @since 2.6.9
 *
 * @param string $value A value saved in a product dropdown array
 * @return array A parsed set of values for download_id and price_id.
 */
function edd_parse_product_dropdown_value( $value ) {
	$parts       = explode( '_', $value );
	$download_id = absint( $parts[0] );
	$price_id    = isset( $parts[1] )
		? (int) $parts[1]
		: null;

	return array(
		'download_id' => $download_id,
		'price_id'    => $price_id,
	);
}

/**
 * Get bundle pricing variations
 *
 * @since 2.7
 *
 * @param int $download_id Download ID.
 * @return array|false Bundle pricing variations, false if invalid data was passed.
 */
function edd_get_bundle_pricing_variations( $download_id = 0 ) {

	// Bail if no download ID was passed.
	if ( empty( $download_id ) ) {
		return false;
	}

	$download = edd_get_download( $download_id );

	return $download
		? $download->get_bundle_pricing_variations()
		: false;
}
