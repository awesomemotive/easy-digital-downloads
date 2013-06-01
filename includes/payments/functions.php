<?php
/**
 * Payment Functions
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Payments
 *
 * Retrieve payments from the database.
 *
 * Since 1.2, this function takes an array of arguments, instead of individual
 * parameters. All of the original paremeters remain, but can be passed in any
 * order via the array.
 *
 * $offset = 0, $number = 20, $mode = 'live', $orderby = 'ID', $order = 'DESC',
 * $user = null, $status = 'any', $meta_key = null
 *
 * @since 1.0
 * @param array $args Arguments passed to get payments
 * @return object $payments Payments retrieved from the database
 */
function edd_get_payments( $args = array() ) {
	$defaults = array(
		'number'   => 20,
		'page'     => null,
		'mode'     => 'live',
		'orderby'  => 'ID',
		'order'    => 'DESC',
		'user'     => null,
		'status'   => 'any',
		'meta_key' => null,
		'year'     => null,
		'month'    => null,
		'day'      => null,
		's'        => null,
		'children' => false,
		'fields'   => null
	);

	$args = wp_parse_args( $args, $defaults );

	$payment_args = array(
		'post_type'      => 'edd_payment',
		'paged'          => $args['page'],
		'order'          => $args['order'],
		'orderby'        => $args['orderby'],
		'post_status'    => $args['status'],
		'year'           => $args['year'],
		'monthnum'       => $args['month'],
		'day'            => $args['day'],
		'fields'         => $args['fields']
	);

	if( $args['number'] == -1 )
		$payment_args['nopaging'] = true;
	else
		$payment_args['posts_per_page'] = $args['number'];

	switch ( $args['orderby'] ) :
		case 'amount' :
			$payment_args['orderby']  = 'meta_value_num';
			$payment_args['meta_key'] = '_edd_payment_total';
			break;
		default :
			$payment_args['orderby'] = $args['orderby'];
			break;
	endswitch;

	if ( ! $args['children'] )
		$payment_args['post_parent'] = 0; // Only get top level payments

	if ( ! is_null( $args['meta_key'] ) )
		$payment_args['meta_key'] = $args['meta_key'];

	if ( ! is_null( $args['user'] ) ) {
		if ( is_numeric( $args['user'] ) ) {
			$user_key = '_edd_payment_user_id';
		} else {
			$user_key = '_edd_payment_user_email';
		}
		$payment_args['meta_query'] = array(
			array(
				'key'   => $user_key,
				'value' => $args['user']
			)
		);
	}

	$search = trim( $args['s'] );

	if ( is_email( $search ) || strlen( $search ) == 32 ) {
		// This is a purchase key search
		$key = is_email( $search ) ? '_edd_payment_user_email' : '_edd_payment_purchase_key';

		$search_meta = array(
			'key'   => $key,
			'value' => $search
		);

		if ( isset( $payment_args['meta_query'] ) ) {
			$payment_args['meta_query'][1] = $search_meta;
		} else {
			// Create a new meta query
			$payment_args['meta_query'] = array( $search_meta );
		}
	} elseif ( is_numeric( $search ) ) {
		// Searching for payments by user ID
		$search_meta = array(
			'key'   => '_edd_payment_user_id',
			'value' => $search
		);

		if ( isset( $payment_args['meta_query'] ) ) {
			$payment_args['meta_query'][1] = $search_meta;
		} else {
			// Create a new meta query
			$payment_args['meta_query'] = array( $search_meta );
		}
	} else {
		$payment_args['s'] = $search;
	}

	if ( $args['mode'] != 'all' ) {
		if ( isset( $payment_args['meta_query'] ) ) {

			// Append to the user meta query
			$payment_args['meta_query'][2] = array(
				'key'   => '_edd_payment_mode',
				'value' => $args['mode']
			);
		} else {
			// Create a new meta query
			$payment_args['meta_query'] = array(
				array(
					'key'   => '_edd_payment_mode',
					'value' => $args['mode']
				)
			);
		}
	}

	$payment_args = apply_filters( 'edd_get_payments_args', $payment_args );
	$payments     = new WP_Query;
	if ( $payments ) {
		return $payments->query( $payment_args );
	}

	return false;
}

/**
 * Insert Payment
 *
 * @since 1.0
 * @param array $payment_data
 * @return bool true if payment is inserted, false otherwise
 */
function edd_insert_payment( $payment_data = array() ) {
	if ( empty( $payment_data ) )
		return false;

	// Make sure the payment is inserted with the correct timezone
	date_default_timezone_set( edd_get_timezone_id() );

	// Construct the payment title
	if ( isset( $payment_data['user_info']['first_name'] ) || isset( $payment_data['user_info']['last_name'] ) ) {
		$payment_title = $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'];
	} else {
		$payment_title = $payment_data['user_email'];
	}

	// Retrieve the ID of the discount used, if any
	if ( $payment_data['user_info']['discount'] != 'none' ) {
		$discount = edd_get_discount_by_code( $payment_data['user_info']['discount'] );
	}

	$args = apply_filters( 'edd_insert_payment_args', array(
		'post_title'    => $payment_title,
		'post_status'   => isset( $payment_data['status'] ) ? $payment_data['status'] : 'pending',
		'post_type'     => 'edd_payment',
		'post_parent'   => isset( $payment_data['parent'] ) ? $payment_data['parent'] : null,
		'post_date'     => isset( $payment_data['post_date'] ) ? $payment_data['post_date'] : null,
		'post_date_gmt' => isset( $payment_data['post_date'] ) ? $payment_data['post_date'] : null
	), $payment_data );

	// Create a blank payment
	$payment = wp_insert_post( $args );

	if ( $payment ) {
		$payment_meta = array(
			'currency'     => $payment_data['currency'],
			'downloads'    => serialize( $payment_data['downloads'] ),
			'user_info'    => serialize( $payment_data['user_info'] ),
			'cart_details' => serialize( $payment_data['cart_details'] ),
			'tax'          => edd_is_cart_taxed() ? edd_get_cart_tax() : 0,
		);

		$mode    = edd_is_test_mode() ? 'test' : 'live';
		$gateway = isset( $_POST['edd-gateway'] ) ? $_POST['edd-gateway'] : '';

		// Record the payment details
		update_post_meta( $payment, '_edd_payment_meta',         apply_filters( 'edd_payment_meta', $payment_meta, $payment_data ) );
		update_post_meta( $payment, '_edd_payment_user_id',      $payment_data['user_info']['id'] );
		update_post_meta( $payment, '_edd_payment_user_email',   $payment_data['user_email'] );
		update_post_meta( $payment, '_edd_payment_user_ip',      edd_get_ip() );
		update_post_meta( $payment, '_edd_payment_purchase_key', $payment_data['purchase_key'] );
		update_post_meta( $payment, '_edd_payment_total',        $payment_data['price'] );
		update_post_meta( $payment, '_edd_payment_mode',         $mode );
		update_post_meta( $payment, '_edd_payment_gateway',      $gateway );
		if ( ! empty( $discount ) )
			update_post_meta( $payment, '_edd_payment_discount_id',  $discount->ID );

		// Clear the user's purchased cache
		delete_transient( 'edd_user_' . $payment_data['user_info']['id'] . '_purchases' );

		do_action( 'edd_insert_payment', $payment, $payment_data );

		return $payment; // Return the ID
	}
	// Return false if no payment was inserted
	return false;
}

/**
 * Updates a payment status.
 *
 * @since 1.0
 * @param int $payment_id Payment ID
 * @param string $new_status New Payment Status (default: publish)
 * @return void
 */
function edd_update_payment_status( $payment_id, $new_status = 'publish' ) {
	if ( $new_status == 'completed' || $new_status == 'complete' ) {
		$new_status = 'publish';
	}

	$payment = get_post( $payment_id );

	if ( is_wp_error( $payment ) || !is_object( $payment ) )
		return;

	$old_status = $payment->post_status;

	if ( $old_status === $new_status )
		return; // Don't permit status changes that aren't changes

	do_action( 'edd_before_payment_status_change', $payment_id, $new_status, $old_status );

	$update_fields = array( 'ID' => $payment_id, 'post_status' => $new_status, 'edit_date' => current_time( 'mysql' ) );

	wp_update_post( apply_filters( 'edd_update_payment_status_fields', $update_fields ) );

	do_action( 'edd_update_payment_status', $payment_id, $new_status, $old_status );
}

/**
 * Deletes a Purchase
 *
 * @since 1.0
 * @global $edd_logs
 * @uses EDD_Logging::delete_logs()
 * @param int $payment_id Payment ID (default: 0)
 * @return void
 */
function edd_delete_purchase( $payment_id = 0 ) {
	global $edd_logs;

	$downloads = edd_get_payment_meta_downloads( $payment_id );

	if ( is_array( $downloads ) ) {
		// Update sale counts and earnings for all purchased products
		foreach ( $downloads as $download ) {
			edd_undo_purchase( $download['id'], $payment_id );
		}
	}

	do_action( 'edd_payment_delete', $payment_id );

	// Remove the payment
	wp_delete_post( $payment_id, true );

	// Remove related sale log entries
	$edd_logs->delete_logs(
		null,
		'sale',
		array(
			array(
				'key'   => '_edd_log_payment_id',
				'value' => $payment_id
			)
		)
	);

	do_action( 'edd_payment_deleted', $payment_id );
}

/**
 * Undos a purchase, including the decrease of sale and earning stats. Used for
 * when refunding or deleting a purchase
 *
 * @since 1.0.8.1
 * @param int $download_id Download (Post) ID
 * @param int $payment_id Payment ID
 * @return void
 */
function edd_undo_purchase( $download_id, $payment_id ) {
	$payment = get_post( $payment_id );

	$status  = $payment->post_status;

	if ( $status != 'publish' )
		return; // Payment has already been reversed, or was never completed

	edd_decrease_purchase_count( $download_id );
	$purchase_meta      = edd_get_payment_meta( $payment_id );
	$user_purchase_info = maybe_unserialize( $purchase_meta['user_info'] );
	$cart_details       = maybe_unserialize( $purchase_meta['cart_details'] );
	$amount             = null;

	if ( is_array( $cart_details ) ) {
		$cart_item_id   = array_search( $download_id, $cart_details );
		$amount         = isset( $cart_details[$cart_item_id]['price'] ) ? $cart_details[$cart_item_id]['price'] : null;
	}

	$amount = edd_get_download_final_price( $download_id, $user_purchase_info, $amount );

	edd_decrease_earnings( $download_id, $amount );
}

/**
 * Check For Existing Payment
 *
 * @since 1.0
 * @param int $payment_id Payment ID
 * @return bool true if payment exists, false otherwise
 */
function edd_check_for_existing_payment( $payment_id ) {
	$payment = get_post( $payment_id );

	if ( $payment && $payment->post_status == 'publish' ) {
		return true; // Payment exists
	}

	return false; // This payment doesn't exist
}

/**
 * Get Payment Status
 *
 * @since 1.0
 * @param obj $payment Payment Object
 * @param bool $return_label Whether to return the payment status or not
 * @return mixed string if payment status exists, false otherwise
 */
function edd_get_payment_status( $payment = OBJECT, $return_label = false ) {
	if ( ! is_object( $payment ) || !isset( $payment->post_status ) )
		return false;

	$statuses = edd_get_payment_statuses();
	if ( ! is_array( $statuses ) || empty( $statuses ) )
		return false;

	if ( array_key_exists( $payment->post_status, $statuses ) ) {
		if ( true === $return_label ) {
			return $statuses[ $payment->post_status ];
		} else {
			return array_search( $payment->post_status, $statuses );
		}
	}

	return false;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since 1.0.8.1
 * @return array $payment_status All the available payment statuses
 */
function edd_get_payment_statuses() {
	$payment_statuses = array(
		'pending'   => __( 'Pending', 'edd' ),
		'publish'   => __( 'Complete', 'edd' ),
		'refunded'  => __( 'Refunded', 'edd' ),
		'failed'    => __( 'Failed', 'edd' ),
		'abandoned' => __( 'Abandoned', 'edd' ),
		'revoked'   => __( 'Revoked', 'edd' )
	);

	return apply_filters( 'edd_payment_statuses', $payment_statuses );
}

/**
 * Get Earnings By Date
 *
 * @since 1.0
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $earnings Earnings
 */
function edd_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null ) {
	$args = array(
		'post_type'      => 'edd_payment',
		'nopaging'       => true,
		'year'           => $year,
		'monthnum'       => $month_num,
		'meta_key'       => '_edd_payment_mode',
		'meta_value'     => 'live',
		'post_status'    => array( 'publish', 'revoked' ),
		'fields'         => 'ids',
		'update_post_term_cache' => false
	);
	if ( ! empty( $day ) )
		$args['day'] = $day;

	if ( ! empty( $hour ) )
		$args['hour'] = $hour;

	$args     = apply_filters( 'edd_get_earnings_by_date_args', $args );
	$key      = md5( serialize( $args ) );
	delete_transient( $key );
	$earnings = get_transient( $key );

	if( false === $earnings ) {
		$sales = get_posts( $args );
		$earnings = 0;
		if ( $sales ) {
			foreach ( $sales as $sale ) {
				$amount    = edd_get_payment_amount( $sale );
				$earnings  = $earnings + $amount;
			}
		}
		// Cache the results for one hour
		set_transient( $key, $earnings, 60*60 );
	}

	return round( $earnings, 2 );
}

/**
 * Get Sales By Date
 *
 * @since 1.1.4.0
 * @author Sunny Ratilal
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Sales
 */
function edd_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {
	$args = array(
		'post_type'      => 'edd_payment',
		'nopaging'       => true,
		'year'           => $year,
		'meta_key'       => '_edd_payment_mode',
		'meta_value'     => 'live',
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);

	if ( ! empty( $month_num ) )
		$args['monthnum'] = $month_num;

	if ( ! empty( $day ) )
		$args['day'] = $day;

	if ( ! empty( $hour ) )
		$args['hour'] = $hour;

	$key   = md5( serialize( $args ) );
	$count = get_transient( $key, 'edd' );

	if( false === $count ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;
		// Cache the results for one hour
		set_transient( $key, $count, 60*60 );
	}

	return $count;
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @since 1.0.8
 * @param int $payment_id Payment ID to check against
 * @return bool true if complete, false otherwise
 */
function edd_is_payment_complete( $payment_id ) {
	$payment = get_post( $payment_id );
	if ( $payment )
		if ( $payment->post_status == 'publish' )
			return true;
	return false;
}

/**
 * Get Total Sales
 *
 * @since 1.2.2
 * @return int $count Total sales
 */
function edd_get_total_sales() {
	$args = apply_filters( 'edd_get_total_sales_args', array(
		'post_type'              => 'edd_payment',
		'nopaging'               => true,
		'meta_key'               => '_edd_payment_mode',
		'meta_value'             => 'live',
		'fields'                 => 'ids',
		'post_status'            => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	) );

	$key   = md5( serialize( $args ) );
	$count = get_transient( $key );

	if ( false === $count ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;
		set_transient( $key, $count, 60 * 60 );

	}
	return $count;
}

/**
 * Get Total Earnings
 *
 * @since 1.2
 * @return float $total Total earnings
 */
function edd_get_total_earnings() {

	$total = get_transient( 'edd_earnings_total' );

	if( false === $total ) {

		$total = (float) 0;

		$args = apply_filters( 'edd_get_total_earnings_args', array(
			'offset' => 0,
			'number' => -1,
			'mode'   => 'live',
			'status' => array( 'publish', 'revoked' ),
			'fields' => 'ids'
		) );

		$payments = edd_get_payments( $args );
		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$total += edd_get_payment_amount( $payment );
			}
		}

		// Cache results for 1 day. This cache is cleared automatically when a payment is made
		set_transient( 'edd_earnings_total', $total, 86400 );
	}
	return apply_filters( 'edd_total_earnings', round( $total, 2 ) );
}

/**
 * Get Payment Meta for a specific Payment
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return array $meta Payment Meta
 */
function edd_get_payment_meta( $payment_id ) {
	$meta = get_post_meta( $payment_id, '_edd_payment_meta', true );

	// Payment meta was simplified in EDD v1.5, so these are here for backwards compatibility
	if ( ! isset( $meta['key'] ) )
		$meta['key'] = edd_get_payment_key( $payment_id );

	if ( ! isset( $meta['amount'] ) )
		$meta['amount'] = edd_get_payment_amount( $payment_id );

	if ( ! isset( $meta['email'] ) )
		$meta['email'] = edd_get_payment_user_email( $payment_id );

	if ( ! isset( $meta['date'] ) )
		$meta['date'] = get_post_field( 'post_date', $payment_id );

	return apply_filters( 'edd_get_payment_meta', $meta, $payment_id );
}

/**
 * Get the user_info Key from Payment Meta
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return array $user_info User Info Meta Values
 */
function edd_get_payment_meta_user_info( $payment_id ) {
	$payment_meta = edd_get_payment_meta( $payment_id );
	$user_info    = isset( $payment_meta['user_info'] ) ? maybe_unserialize( $payment_meta['user_info'] ) : false;

	return apply_filters( 'edd_payment_meta_user_info', $user_info );
}

/**
 * Get the downloads Key from Payment Meta
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return array $downloads Downloads Meta Values
 */
function edd_get_payment_meta_downloads( $payment_id ) {
	$payment_meta = edd_get_payment_meta( $payment_id );
	$downloads    = maybe_unserialize( $payment_meta['downloads'] );

	return apply_filters( 'edd_payment_meta_downloads', $downloads );
}

/**
 * Get the cart_details Key from Payment Meta
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @param bool $include_bundle_files Whether to retrieve product IDs associated with a bundled product and return them in the array
 * @return array $cart_details Cart Details Meta Values
 */
function edd_get_payment_meta_cart_details( $payment_id, $include_bundle_files = false ) {
	$payment_meta = edd_get_payment_meta( $payment_id );
	$cart_details = maybe_unserialize( $payment_meta['cart_details'] );

	if( $include_bundle_files ) {

		foreach( $cart_details as $cart_item ) {

			if( 'bundle' != edd_get_download_type( $cart_item['id'] ) )
				continue;

			$products = edd_get_bundled_products( $cart_item['id'] );
			if( empty( $products ) )
				continue;

			foreach( $products as $product_id ) {
				$cart_details[]   = array(
					'id'          => $product_id,
					'name'        => get_the_title( $product_id ),
					'item_number' => array(
						'id'      => $product_id,
						'options' => array(),
					),
					'price'       => 0,
					'quantity'    => 1,
					'tax'         => 0,
					'in_bundle'   => 1
				);
			}
		}
	}

	return apply_filters( 'edd_payment_meta_cart_details', $cart_details );
}

/**
 * Get the user email associated with a payment
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $email User Email
 */
function edd_get_payment_user_email( $payment_id ) {
	$email = get_post_meta( $payment_id, '_edd_payment_user_email', true );

	return apply_filters( 'edd_payment_user_email', $email );
}


/**
 * Get the user ID associated with a payment
 *
 * @since 1.5.1
 * @param int $payment_id Payment ID
 * @return string $user_id User ID
 */
function edd_get_payment_user_id( $payment_id ) {
	$user_id = get_post_meta( $payment_id, '_edd_payment_user_id', true );

	return apply_filters( 'edd_payment_user_id', $user_id );
}

/**
 * Get the gateway associated with a payment
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $gateway Gateway
 */
function edd_get_payment_gateway( $payment_id ) {
	$gateway = get_post_meta( $payment_id, '_edd_payment_gateway', true );

	return apply_filters( 'edd_payment_gateway', $gateway );
}

/**
 * Get the purchase key for a purchase
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $key Purchase key
 */
function edd_get_payment_key( $payment_id = 0 ) {
	$key = get_post_meta( $payment_id, '_edd_payment_purchase_key', true );
	return apply_filters( 'edd_payment_key', $key, $payment_id );
}

/**
 * Get the fully formatted payment amount. The payment amount is retrieved using
 * edd_get_payment_amount() and is then sent through edd_currency_filter() and
 * edd_format_amount() to format the amount correctly.
 *
 * @since 1.4
 * @param int $payment_id Payment ID
 * @return string $amount Fully formatted payment amount
 */
function edd_payment_amount( $payment_id = 0 ) {
	$amount = edd_get_payment_amount( $payment_id );
	return edd_currency_filter( edd_format_amount( $amount ) );
}
/**
 * Get the amount associated with a payment
 *
 * @access public
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $amount Payment amount
 */
function edd_get_payment_amount( $payment_id ) {
	$amount = get_post_meta( $payment_id, '_edd_payment_total', true );

	if ( ! is_numeric( $amount ) ) {
		$payment_meta = edd_get_payment_meta( $payment_id );
		$amount       = $payment_meta['amount'];
	}

	return apply_filters( 'edd_payment_amount', $amount );
}

/**
 * Retrieves subtotal for payment (this is the amount before taxes) and then
 * returns a full formatted amount. This function essentialy calls
 * edd_get_payment_subtotal()
 *
 * @since 1.3.3
 * @see edd_get_payment_subtotal()
 * @param int $payment_id Payment ID
 * @param bool $payment_meta Payment Meta provided? (default: false)
 * @return string $subtotal Fully formatted payment subtotal
 */
function edd_payment_subtotal( $payment_id = 0, $payment_meta = false ) {
	$subtotal = edd_get_payment_subtotal( $payment_id, $payment_meta );

	return edd_currency_filter( edd_format_amount( $subtotal ) );
}

/**
 * Retrieves subtotal for payment (this is the amount before taxes) and then
 * returns a non formatted amount.
 *
 * @since 1.3.3
 * @global $edd_options Array of all the EDD Options
 * @param int $payment_id Payment ID
 * @param bool $payment_meta Get payment meta?
 * @return float $subtotal Subtotal for payment (non formatted)
 */
function edd_get_payment_subtotal( $payment_id = 0, $payment_meta = false ) {
	global $edd_options;

	if ( ! $payment_meta )
		$payment_meta = edd_get_payment_meta( $payment_id );

	$subtotal = isset( $payment_meta['subtotal'] ) ? $payment_meta['subtotal'] : $payment_meta['amount'];

	$tax = edd_use_taxes() ? edd_get_payment_tax( $payment_id ) : 0;

	if (
		( isset( $edd_options['prices_include_tax'] ) && $edd_options['prices_include_tax'] == 'no' && ! edd_prices_show_tax_on_checkout() ) ||
		( isset( $edd_options['prices_include_tax'] ) && ! edd_prices_show_tax_on_checkout() && $edd_options['prices_include_tax'] == 'yes' )
	) {
		$subtotal -= $tax;
	}

	return apply_filters( 'edd_get_payment_subtotal', $subtotal, $payment_id );
}

/**
 * Retrieves taxed amount for payment and then returns a full formatted amount
 * This function essentialy calls edd_get_payment_tax()
 *
 * @since 1.3.3
 * @see edd_get_payment_tax()
 * @param int $payment_id Payment ID
 * @param bool $payment_meta Payment Meta provided? (default: false)
 * @return string $subtotal Fully formatted payment subtotal
 */
function edd_payment_tax( $payment_id = 0, $payment_meta = false ) {
	$tax = edd_get_payment_tax( $payment_id, $payment_meta );

	return edd_currency_filter( edd_format_amount( $tax ) );
}

/**
 * Retrieves taxed amount for payment and then returns a non formatted amount
 *
 * @since 1.3.3
 * @param int $payment_id Payment ID
 * @param bool $payment_meta Get payment meta?
 * @return float $subtotal Subtotal for payment (non formatted)
 */
function edd_get_payment_tax( $payment_id = 0, $payment_meta = false ) {
	if ( ! $payment_meta )
		$payment_meta = edd_get_payment_meta( $payment_id );

	$tax = isset( $payment_meta['tax'] ) ? $payment_meta['tax'] : 0;

	return apply_filters( 'edd_get_payment_tax', $tax, $payment_id );
}

/**
 * Retrieves arbitrary fees for the payment
 *
 * @since 1.5
 * @param int $payment_id Payment ID
 * @param bool $payment_meta Get payment meta? (default: false)
 * @return mixed array if payment fees found, false otherwise
 */
function edd_get_payment_fees( $payment_id = 0, $payment_meta = false ) {
	if ( ! $payment_meta )
		$payment_meta = edd_get_payment_meta( $payment_id );

	$fees = array();
	$payment_fees = isset( $payment_meta['fees'] ) ? $payment_meta['fees'] : false;

	if ( ! empty( $payment_fees ) ) {
		foreach ( $payment_fees as $fee_id => $fee ) {
			$fees[] = array(
				'id'     => $fee_id,
				'amount' => $fee['amount'],
				'label'  => $fee['label']
			);
		}
	}

	return apply_filters( 'edd_get_payment_fees', $fees, $payment_id );
}

/**
 * Retrieve the purchase ID based on the purchase key
 *
 * @since 1.3.2
 * @global object $wpdb Used to query the database using the WordPress
 *   Database API
 * @param string $key the purchase key to search for
 * @return int $purchase Purchase ID
 */
function edd_get_purchase_id_by_key( $key ) {
	global $wpdb;

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_purchase_key' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != NULL )
		return $purchase;

	return 0;
}

/**
 * Retrieve all notes attached to a purchase
 *
 * @since 1.4
 * @param int $payment_id The payment ID to retrieve notes for
 * @return array $notes Payment Notes
 */
function edd_get_payment_notes( $payment_id = 0 ) {
	if ( empty( $payment_id ) )
		return false;

	remove_filter( 'comments_clauses', 'edd_hide_payment_notes', 10, 2 );

	$notes = get_comments( array( 'post_id' => $payment_id, 'order' => 'ASC' ) );

	add_filter( 'comments_clauses', 'edd_hide_payment_notes', 10, 2 );

	return $notes;
}

/**
 * Add a note to a payment
 *
 * @since 1.4
 * @param int $payment_id The payment ID to store a note for
 * @param string $note The note to store
 * @return int The new note ID
 */
function edd_insert_payment_note( $payment_id = 0, $note = '' ) {
	if ( empty( $payment_id ) )
		return false;

	do_action( 'edd_pre_insert_payment_note', $payment_id, $note );

	$note_id = wp_insert_comment( wp_filter_comment( array(
		'comment_post_ID'      => $payment_id,
		'comment_content'      => $note,
		'user_id'              => is_admin() ? get_current_user_id() : 0,
		'comment_date'         => current_time( 'mysql' ),
		'comment_date_gmt'     => current_time( 'mysql', 1 ),
		'comment_approved'     => 1,
		'comment_parent'       => 0,
		'comment_author'       => '',
		'comment_author_IP'    => '',
		'comment_author_url'   => '',
		'comment_author_email' => '',
		'comment_type'         => 'edd_payment_note'

	) ) );

	do_action( 'edd_insert_payment_note', $note_id, $payment_id, $note );

	return $note_id;
}


/**
 * Deletes a payment note
 *
 * @since 1.6
 * @param int $comment_id The comment ID to delete
 * @param int $payment_id The payment ID the note is connected to
 * @return bool True on success, false otherwise
 */
function edd_delete_payment_note( $comment_id = 0, $payment_id = 0 ) {
	if( empty( $comment_id ) )
		return false;

	do_action( 'edd_pre_delete_payment_note', $comment_id, $payment_id );
	$ret = wp_delete_comment( $comment_id, true );
	do_action( 'edd_post_delete_payment_note', $comment_id, $payment_id );

	return $ret;
}

/**
 * Exclude notes (comments) on edd_payment post type from showing in Recent
 * Comments widgets
 *
 * @since 1.4.1
 * @param array $clauses Comment clauses for comment query
 * @param obj $wp_comment_query WordPress Comment Query Object
 * @return array $clauses Updated comment clauses
 */
function edd_hide_payment_notes( $clauses, $wp_comment_query ) {
    global $wpdb;

	$clauses['where'] .= ' AND comment_type != "edd_payment_note"';
    return $clauses;
}
add_filter( 'comments_clauses', 'edd_hide_payment_notes', 10, 2 );


/**
 * Exclude notes (comments) on edd_payment post type from showing in comment feeds
 *
 * @since 1.5.1
 * @param array $where
 * @param obj $wp_comment_query WordPress Comment Query Object
 * @return array $where
 */
function edd_hide_payment_notes_from_feeds( $where, $wp_comment_query ) {
    global $wpdb;

	$where .= $wpdb->prepare( " AND comment_type != %s", 'edd_payment_note' );
	return $where;
}
add_filter( 'comment_feed_where', 'edd_hide_payment_notes_from_feeds', 10, 2 );


/**
 * Remove EDD Comments from the wp_count_comments function
 *
 * @access public
 * @since 1.5.2
 * @param array $stats (empty from core filter)
 * @param int $post_id Post ID
 * @return array Array of comment counts
*/
function edd_remove_payment_notes_in_comment_counts( $stats, $post_id ) {
	global $wpdb;

	$post_id = (int) $post_id;

	if ( apply_filters( 'edd_count_payment_notes_in_comments', false ) )
		return array();

	$count = wp_cache_get( "comments-{$post_id}", 'counts' );

	if ( false !== $count )
		return $count;

	$where = 'WHERE comment_type != "edd_payment_note"';

	if ( $post_id > 0 )
		$where .= $wpdb->prepare( " AND comment_post_ID = %d", $post_id );

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

	$total = 0;
	$approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );
	foreach ( (array) $count as $row ) {
		// Don't count post-trashed toward totals
		if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] )
			$total += $row['num_comments'];
		if ( isset( $approved[$row['comment_approved']] ) )
			$stats[$approved[$row['comment_approved']]] = $row['num_comments'];
	}

	$stats['total_comments'] = $total;
	foreach ( $approved as $key ) {
		if ( empty($stats[$key]) )
			$stats[$key] = 0;
	}

	$stats = (object) $stats;
	wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

	return $stats;
}
add_filter( 'wp_count_comments', 'edd_remove_payment_notes_in_comment_counts', 10, 2 );


/**
 * Filter where older than one week
 *
 * @access public
 * @since 1.6
 * @param string $where Where clause
 * @return string $where Modified where clause
*/
function edd_filter_where_older_than_week( $where = '' ) {
	// Payments older than one week
	$start = date( 'Y-m-d', strtotime( '-7 days' ) );
	$where .= " AND post_date <= '{$start}'";
	return $where;
}
