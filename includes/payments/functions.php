<?php
/**
 * Payment Functions
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieves an instance of EDD_Payment for a specified ID.
 *
 * @since 2.7
 *
 * @param mixed int|EDD_Payment|WP_Post $payment Payment ID, EDD_Payment object or WP_Post object.
 * @param bool                          $by_txn  Is the ID supplied as the first parameter
 *
 * @return EDD_Payment|false false|object EDD_Payment if a valid payment ID, false otherwise.
 */
function edd_get_payment( $payment_or_txn_id = null, $by_txn = false ) {
	if ( $payment_or_txn_id instanceof WP_Post || $payment_or_txn_id instanceof EDD_Payment ) {
		$payment_id = $payment_or_txn_id->ID;
	} elseif ( $by_txn ) {
		if ( empty( $payment_or_txn_id ) ) {
			return false;
		}

		$payment_id = edd_get_order_id_from_transaction_id( $payment_or_txn_id );

		if ( empty( $payment_id ) ) {
			return false;
		}
	} else {
		$payment_id = $payment_or_txn_id;
	}

	if ( empty( $payment_id ) ) {
		return false;
	}

	$payment = new EDD_Payment( $payment_id );

	if ( empty( $payment->ID ) || ( ! $by_txn && (int) $payment->ID !== (int) $payment_id ) ) {
		return false;
	}

	return $payment;
}

/**
 * Retrieve payments from the database.
 *
 * Since 1.2, this function takes an array of arguments, instead of individual
 * parameters. All of the original parameters remain, but can be passed in any
 * order via the array.
 *
 * $offset = 0, $number = 20, $mode = 'live', $orderby = 'ID', $order = 'DESC',
 * $user = null, $status = 'any', $meta_key = null
 *
 * @since 1.0
 * @since 1.8 Refactored to be a wrapper for EDD_Payments_Query.
 *
 * @param array $args Arguments passed to get payments.
 * @return EDD_Payment[]|int $payments Payments retrieved from the database.
 */
function edd_get_payments( $args = array() ) {
	$args     = apply_filters( 'edd_get_payments_args', $args );
	$payments = new EDD_Payments_Query( $args );
	return $payments->get_payments();
}

/**
 * Retrieve payment by a given field.
 *
 * @since 2.0
 *
 * @param string $field The field to retrieve the payment with.
 * @param mixed  $value The value for $field.
 *
 * @return mixed
 */
function edd_get_payment_by( $field = '', $value = '' ) {
	$payment = false;

	if ( ! empty( $field ) && ! empty( $value ) ) {
		switch ( strtolower( $field ) ) {
			case 'id':
				$payment = edd_get_payment( $value );

				if ( ! $payment->ID > 0 ) {
					$payment = false;
				}
				break;
			case 'key':
				$order = edd_get_order_by( 'payment_key', $value );

				if ( $order ) {
					$payment = edd_get_payment( $order->id );

					if ( ! $payment->ID > 0 ) {
						$payment = false;
					}
				}
				break;
			case 'payment_number':
				$order = edd_get_order_by( 'order_number', $value );

				if ( $order ) {
					$payment = edd_get_payment( $order->id );

					if ( ! $payment->ID > 0 ) {
						$payment = false;
					}
				}
				break;
		}
	}

	return $payment;
}

/**
 * Insert an order into the database.
 *
 * @since 1.0
 * @since 3.0 Refactored to add orders using new methods.
 *
 * @param array $order_data Order data to process.
 * @return int|bool Order ID if the order was successfully inserted, false otherwise.
 */
function edd_insert_payment( $order_data = array() ) {
	if ( empty( $order_data ) ) {
		return false;
	}

	return edd_build_order( $order_data );
}

/**
 * Updates a payment status.
 *
 * @since 1.0
 * @since 3.0 Updated to use new order methods.
 *
 * @param  int    $order_id Order ID.
 * @param  string $new_status order status (default: publish)
 *
 * @return bool True if the status was updated successfully, false otherwise.
 */
function edd_update_payment_status( $order_id = 0, $new_status = 'complete' ) {
	return edd_update_order_status( $order_id, $new_status );
}

/**
 * Deletes a Purchase
 *
 * @since 1.0
 * @since 3.0 Added logic to bail early if order not found in database.
 *
 * @param int  $payment_id           Payment ID. Default 0.
 * @param bool $update_customer      If we should update the customer stats. Default true.
 * @param bool $delete_download_logs If we should remove all file download logs associated with the payment. Default false.
 */
function edd_delete_purchase( $payment_id = 0, $update_customer = true, $delete_download_logs = false ) {
	$payment = edd_get_payment( $payment_id );

	// Bail if an order does not exist.
	if ( ! $payment ) {
		return;
	}

	// Update sale counts and earnings for all purchased products
	edd_undo_purchase( false, $payment_id );

	$amount      = edd_get_payment_amount( $payment_id );
	$status      = $payment->post_status;
	$customer_id = edd_get_payment_customer_id( $payment_id );

	$customer = edd_get_customer( $customer_id );

	// Only decrease earnings if they haven't already been decreased (or were never increased for this payment).
	if ( 'revoked' === $status || 'complete' === $status ) {
		edd_decrease_total_earnings( $amount );

		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
	}

	do_action( 'edd_payment_delete', $payment_id );

	if ( $customer && $customer->id && $update_customer ) {

		// Remove the payment ID from the customer
		$customer->remove_payment( $payment_id );
	}

	// Remove the order.
	edd_delete_order( $payment_id );

	// Delete file download logs.
	if ( $delete_download_logs ) {
		$logs = edd_get_file_download_logs( array(
			'order_id' => $payment_id,
		) );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				edd_delete_file_download_log( $log->id );
			}
		}
	}

	do_action( 'edd_payment_deleted', $payment_id );
}

/**
 * Undo a purchase, including the decrease of sale and earning stats. Used for
 * when refunding or deleting a purchase.
 *
 * @since 1.0.8.1
 * @since 3.0 Updated to use new refunds API and new query methods.
 *            Updated to use new nomenclature.
 *            Set default value of order ID to 0.
 *            Method now returns the refunded order ID.
 *
 * @param int $download_id Download ID.
 * @param int $order_id    Order ID.
 *
 * @return int|false Refunded order ID, false otherwise.
 */
function edd_undo_purchase( $download_id = 0, $order_id = 0 ) {

	/**
	 * In 2.5.7, a bug was found that $download_id was an incorrect usage. Passing it in
	 * now does nothing, but we're holding it in place for legacy support of the argument order.
	 */

	if ( ! empty( $download_id ) ) {
		_edd_deprected_argument( 'download_id', 'edd_undo_purchase', '2.5.7' );
	}

	// Bail if no order ID was passed.
	if ( empty( $order_id ) ) {
		return false;
	}

	// Refund the order.
	return edd_refund_order( $order_id );
}

/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @since 1.0
 * @since 3.0 Refactored to work with edd_orders table.
 *
 * @param array $args List of arguments to base the payments count on.
 *
 * @return object $stats Number of orders grouped by order status.
 */
function edd_count_payments( $args = array() ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'user'       => null,
		'customer'   => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null,
		'download'   => null,
		'gateway'    => null,
		'type'       => 'sale',
	) );

	$select  = 'SELECT edd_o.status, COUNT(*) AS count';
	$from    = "FROM {$wpdb->edd_orders} edd_o";
	$join    = '';
	$where   = 'WHERE 1=1';
	$orderby = '';
	$groupby = 'GROUP BY edd_o.status';

	// Hold the query arguments passed to edd_count_orders().
	$query_args = array();

	// Count orders for a specific user.
	if ( ! empty( $args['user'] ) ) {
		if ( is_email( $args['user'] ) ) {
			$where .= $wpdb->prepare( ' AND edd_o.email = %s', sanitize_email( $args['user'] ) );
		} elseif ( is_numeric( $args['user'] ) ) {
			$where .= $wpdb->prepare( ' AND edd_o.user_id = %d', absint( $args['user'] ) );
		}

		// Count orders for a specific customer.
	} elseif ( ! empty( $args['customer'] ) ) {
		$where .= $wpdb->prepare( ' AND edd_o.customer_id = %d', absint( $args['customer'] ) );

		// Count payments for a search
	} elseif ( ! empty( $args['s'] ) ) {
		$args['s'] = sanitize_text_field( $args['s'] );

		// Filter by email address
		if ( is_email( $args['s'] ) ) {
			$where .= $wpdb->prepare( ' AND edd_o.email = %s', sanitize_email( $args['s'] ) );

			// Filter by payment key.
		} elseif ( 32 === strlen( $args['s'] ) ) {
			$where .= $wpdb->prepare( ' AND edd_o.payment_key = %s', sanitize_email( $args['s'] ) );

			// Filter by download ID.
		} elseif ( '#' === substr( $args['s'], 0, 1 ) ) {
			$search = str_replace( '#:', '', $args['s'] );
			$search = str_replace( '#', '', $search );

			$join   = "INNER JOIN {$wpdb->edd_order_items} edd_oi ON edd_o.id = edd_oi.order_id";
			$where .= $wpdb->prepare( ' AND edd_oi.product_id = %d', $search );

			// Filter by user ID.
		} elseif ( is_numeric( $args['s'] ) ) {
			$query_args['user_id'] = absint( $args['s'] );

			// Filter by discount code.
		} elseif ( 0 === strpos( $args['s'], 'discount:' ) ) {
			$search = str_replace( 'discount:', '', $args['s'] );

			$join   = "INNER JOIN {$wpdb->edd_order_adjustments} edd_oa ON edd_o.id = edd_oa.order_id";
			$where .= $wpdb->prepare( " AND edd_oa.description = %s AND edd_oa.type = 'discount'", $search );
		}
	}

	if ( ! empty( $args['download'] ) && is_numeric( $args['download'] ) ) {
		$join   = "INNER JOIN {$wpdb->edd_order_items} edd_oi ON edd_o.id = edd_oi.order_id";
		$where .= $wpdb->prepare( ' AND edd_oi.product_id = %d', absint( $args['download'] ) );
	}

	if ( ! empty( $args['gateway'] ) ) {
		$where .= $wpdb->prepare( ' AND edd_o.gateway = %s', sanitize_text_field( $args['gateway'] ) );
	}

	if ( ! empty( $args['type'] ) ) {
		$where .= $wpdb->prepare( ' AND edd_o.type = %s', sanitize_text_field( $args['type'] ) );
	}

	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '/' ) ) {
		$date_parts = explode( '/', $args['start-date'] );
		$month      = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$day        = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$year       = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {
			$date   = new DateTime( $args['start-date'] );
			$where .= $wpdb->prepare( ' AND edd_o.date_created >= %s', $date->format( 'Y-m-d 00:00:00' ) );
		}

		// Fixes an issue with the payments list table counts when no end date is specified (partly with stats class).
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}
	}

	if ( ! empty( $args['end-date'] ) && false !== strpos( $args['end-date'], '/' ) ) {
		$date_parts = explode( '/', $args['end-date'] );

		$month = ! empty( $date_parts[0] ) ? $date_parts[0] : 0;
		$day   = ! empty( $date_parts[1] ) ? $date_parts[1] : 0;
		$year  = ! empty( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {
			$date   = date( 'Y-m-d', strtotime( '+1 day', mktime( 0, 0, 0, $month, $day, $year ) ) );
			$where .= $wpdb->prepare( ' AND edd_o.date_created < %s', $date );
		}
	}

	$where = apply_filters( 'edd_count_payments_where', $where );
	$join  = apply_filters( 'edd_count_payments_join', $join );

	$query = "{$select} {$from} {$join} {$where} {$orderby} {$groupby}";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );
	if ( false !== $count ) {
		return $count;
	}

	$counts = $wpdb->get_results( $query, ARRAY_A );

	// Here for backwards compatibility.
	$statuses = array_merge( get_post_stati(), edd_get_payment_status_keys() );
	if ( isset( $statuses['private'] ) && empty( $args['s'] ) ) {
		unset( $statuses['private'] );
	}

	$stats = array();
	foreach ( $statuses as $status ) {
		$stats[ $status ] = 0;
	}

	foreach ( (array) $counts as $row ) {

		// Here for backwards compatibility.
		if ( 'private' === $row['status'] && empty( $args['s'] ) ) {
			continue;
		}

		$stats[ $row['status'] ] = absint( $row['count'] );
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
}


/**
 * Check for existing payment.
 *
 * @since 1.0
 * @since 3.0 Refactored to use EDD\Orders\Order.
 *
 * @param int $order_id Order ID.
 * @return bool True if payment exists, false otherwise.
 */
function edd_check_for_existing_payment( $order_id ) {
	$exists = false;

	$order = edd_get_order( $order_id );

	// Bail if an order was not found.
	if ( ! $order ) {
		return false;
	}

	if ( (int) $order_id === (int) $order->id && $order->is_complete() ) {
		$exists = true;
	}

	return $exists;
}

/**
 * Get order status.
 *
 * @since 1.0
 * @since 3.0 Updated to use new EDD\Order\Order class.
 *
 * @param mixed $order        Payment post object, EDD_Payment object, or payment/post ID.
 * @param bool  $return_label Whether to return the payment status or not
 *
 * @return bool|mixed if payment status exists, false otherwise
 */
function edd_get_payment_status( $order, $return_label = false ) {
	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );

		if ( ! $order ) {
			return false;
		}
	}

	if ( $order instanceof EDD_Payment ) {
		/** @var EDD_Payment $order */
		$order = edd_get_order( $order->id );
	}

	if ( $order instanceof WP_Post ) {
		/** @var WP_Post $order */
		$order = edd_get_order( $order->ID );
	}

	if ( ! is_object( $order ) ) {
		return false;
	}

	$status = $order->status;

	if ( empty( $status ) ) {
		return false;
	}

	if ( true === $return_label ) {
		$status = edd_get_payment_status_label( $status );
	} else {
		$keys      = edd_get_payment_status_keys();
		$found_key = array_search( strtolower( $status ), $keys );
		$status    = false !== $found_key && array_key_exists( $found_key, $keys ) ? $keys[ $found_key ] : false;
	}

	return ! empty( $status ) ? $status : false;
}

/**
 * Given a payment status string, return the label for that string.
 *
 * @since 2.9.2
 * @param string $status
 *
 * @return bool|mixed
 */
function edd_get_payment_status_label( $status = '' ) {
	$default  = str_replace( '_', ' ', $status );
	$default  = ucwords( $default );
	$statuses = edd_get_payment_statuses();

	if ( ! is_array( $statuses ) || empty( $statuses ) ) {
		return $default;
	}

	if ( array_key_exists( $status, $statuses ) ) {
		return $statuses[ $status ];
	}

	return $default;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since 1.0.8.1
 * @since 3.0 Updated 'publish' status to be 'Completed' for consistency with other statuses.
 *
 * @return array $payment_status All the available payment statuses.
 */
function edd_get_payment_statuses() {
	return apply_filters(
		'edd_payment_statuses',
		array(
			'pending'            => __( 'Pending', 'easy-digital-downloads' ),
			'processing'         => __( 'Processing', 'easy-digital-downloads' ),
			'complete'           => __( 'Completed', 'easy-digital-downloads' ),
			'refunded'           => __( 'Refunded', 'easy-digital-downloads' ),
			'partially_refunded' => __( 'Partially Refunded', 'easy-digital-downloads' ),
			'revoked'            => __( 'Revoked', 'easy-digital-downloads' ),
			'failed'             => __( 'Failed', 'easy-digital-downloads' ),
			'abandoned'          => __( 'Abandoned', 'easy-digital-downloads' ),
			'on_hold'            => __( 'On Hold', 'easy-digital-downloads' ),
		)
	);
}

/**
 * Retrieves keys for all available statuses for payments.
 *
 * @since 2.3
 *
 * @return array $payment_status All the available payment statuses.
 */
function edd_get_payment_status_keys() {
	$statuses = array_keys( edd_get_payment_statuses() );
	asort( $statuses );

	return array_values( $statuses );
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @since 1.0.8
 * @since 3.0 Refactored to use EDD\Orders\Order.
 *
 * @param int $order_id Order ID to check against.
 * @return bool True if complete, false otherwise.
 */
function edd_is_payment_complete( $order_id = 0 ) {
	$order = edd_get_order( $order_id );

	$ret    = false;
	$status = null;

	if ( $order ) {
		$status = $order->status;
		if ( (int) $order_id === (int) $order->id && $order->is_complete() ) {
			$ret = true;
		}
	}

	return apply_filters( 'edd_is_payment_complete', $ret, $order_id, $status );
}

/**
 * Retrieve total number of orders.
 *
 * @since 1.2.2
 *
 * @return int $count Total sales
 */
function edd_get_total_sales() {
	$payments = edd_count_payments( array( 'type' => 'sale' ) );

	return $payments->revoked + $payments->complete;
}

/**
 * Calculate the total earnings of the store.
 *
 * @since 1.2
 * @since 3.0   Refactored to work with new tables.
 * @since 3.0.4 Added the $force argument, to force querying again.
 *
 * @param bool $include_taxes Whether taxes should be included. Default true.
 * @param bool $force         If we should force a new calculation.
 * @return float $total Total earnings.
 */
function edd_get_total_earnings( $include_taxes = true, $force = true ) {
	global $wpdb;

	$key = $include_taxes ? 'edd_earnings_total' : 'edd_earnings_total_without_tax';

	$total = $force ? false : get_transient( $key );

	// If no total stored in the database, use old method of calculating total earnings.
	if ( false === $total ) {

		$stats = new EDD\Stats();

		$total = $stats->get_order_earnings(
			array(
				'output'        => 'typed',
				'exclude_taxes' => ! $include_taxes,
				'revenue_type'  => 'net',
			)
		);

		// Cache results for 1 day. This cache is cleared automatically when a payment is made.
		set_transient( $key, $total, 86400 );

		// Store as an option for backwards compatibility.
		update_option( $key, $total, false );
	} else {
		// Always ensure that we're working with a float, since the transient comes back as a string.
		$total = (float) $total;
	}

	// Don't ever show negative earnings.
	if ( $total < 0 ) {
		$total = 0;
	}

	$total = edd_format_amount( $total, true, edd_get_currency(), 'typed' );

	return apply_filters( 'edd_total_earnings', $total );
}

/**
 * Increase the store's total earnings.
 *
 * @since 1.8.4
 *
 * @param $amount int The amount you would like to increase the total earnings by.
 * @return float $total Total earnings
 */
function edd_increase_total_earnings( $amount = 0 ) {
	$total  = floatval( edd_get_total_earnings( true, true ) );
	$total += floatval( $amount );

	return $total;
}

/**
 * Decrease the store's total earnings.
 *
 * @since 1.8.4
 *
 * @param $amount int The amount you would like to decrease the total earnings by.
 * @return float $total Total earnings.
 */
function edd_decrease_total_earnings( $amount = 0 ) {
	$total  = edd_get_total_earnings( true, true );
	$total -= $amount;

	if ( $total < 0 ) {
		$total = 0;
	}

	return $total;
}

/**
 * Retrieve order meta field for an order.
 *
 * @since 1.2
 *
 * @internal This needs to continue making a call to EDD_Payment::get_meta() as it handles the backwards compatibility for
 *           _edd_payment_meta if requested.
 *
 * @param int     $payment_id Payment ID.
 * @param string  $key        Optional. The meta key to retrieve. By default, returns data for all keys. Default '_edd_payment_meta'.
 * @param bool    $single     Optional, default is false. If true, return only the first value of the specified meta_key.
 *                            This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_payment_meta( $payment_id = 0, $key = '_edd_payment_meta', $single = true ) {
	$payment = edd_get_payment( $payment_id );

	return $payment
		? $payment->get_meta( $key, $single )
		: false;
}

/**
 * Update order meta field based on order ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and order ID.
 *
 * If the meta field for the order does not exist, it will be added.
 *
 * @since 1.2
 *
 * @internal This needs to continue making a call to EDD_Payment::update_meta() as it handles the backwards compatibility for
 *           _edd_payment_meta.
 *
 * @param int     $payment_id Payment ID.
 * @param string  $meta_key   Meta data key.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
	$payment = edd_get_payment( $payment_id );

	return $payment
		? $payment->update_meta( $meta_key, $meta_value, $prev_value )
		: false;
}

/**
 * Retrieve the user information associated with an order.
 *
 * This method exists for backwards compatibility; in future, the order query methods should be used.
 *
 * @since 1.2
 *
 * @internal This needs to continue retrieving from EDD_Payment as it handles backwards compatibility.
 *
 * @param int $payment_id Payment ID.
 * @return array User Information.
 */
function edd_get_payment_meta_user_info( $payment_id ) {
	$payment = edd_get_payment( $payment_id );

	return $payment
		? $payment->user_info
		: array();
}

/**
 * Retrieve the downloads associated with an order.
 *
 * This method exists for backwards compatibility; in future, the order query methods should be used.
 *
 * @since 1.2
 *
 * @param int $payment_id Payment ID.
 * @return array Downloads.
 */
function edd_get_payment_meta_downloads( $payment_id ) {
	$payment = edd_get_payment( $payment_id );

	return $payment
		? $payment->downloads
		: array();
}

/**
 * Retrieve the cart details.
 *
 * This method exists for backwards compatibility; in future, the order query methods should be used.
 *
 * @since 1.2
 *
 * @internal This needs to continue retrieving from EDD_Payment as it handles backwards compatibility.
 *
 * @param int  $payment_id           Payment ID
 * @param bool $include_bundle_files Whether to retrieve product IDs associated with a bundled product and return them in the array
 *
 * @return array $cart_details Cart Details Meta Values
 */
function edd_get_payment_meta_cart_details( $payment_id, $include_bundle_files = false ) {
	$payment      = edd_get_payment( $payment_id );
	$cart_details = $payment->cart_details;

	$payment_currency = $payment->currency;

	if ( ! empty( $cart_details ) && is_array( $cart_details ) ) {
		foreach ( $cart_details as $key => $cart_item ) {
			$cart_details[ $key ]['currency'] = $payment_currency;

			// Ensure subtotal is set, for pre-1.9 orders.
			if ( ! isset( $cart_item['subtotal'] ) ) {
				$cart_details[ $key ]['subtotal'] = $cart_item['price'];
			}

			if ( $include_bundle_files ) {
				if ( 'bundle' !== edd_get_download_type( $cart_item['id'] ) ) {
					continue;
				}

				$price_id = edd_get_cart_item_price_id( $cart_item );
				$products = edd_get_bundled_products( $cart_item['id'], $price_id );

				if ( empty( $products ) ) {
					continue;
				}

				foreach ( $products as $product_id ) {
					$cart_details[] = array(
						'id'            => $product_id,
						'name'          => get_the_title( $product_id ),
						'item_number'   => array(
							'id'      => $product_id,
							'options' => array(),
						),
						'price'         => 0,
						'subtotal'      => 0,
						'quantity'      => 1,
						'tax'           => 0,
						'in_bundle'     => 1,
						'parent'        => array(
							'id'      => $cart_item['id'],
							'options' => isset( $cart_item['item_number']['options'] )
								? $cart_item['item_number']['options']
								: array(),
						),
						'order_item_id' => $cart_item['order_item_id'],
					);
				}
			}
		}
	}

	return apply_filters( 'edd_payment_meta_cart_details', $cart_details, $payment_id );
}

/**
 * Get the user email associated with a payment
 *
 * @since 1.2
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return string $email User email.
 */
function edd_get_payment_user_email( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return '';
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->email
		: '';
}

/**
 * Check if the order is associated with a user.
 *
 * @since 2.4.4
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return bool True if the payment is **not** associated with a user, false otherwise.
 */
function edd_is_guest_payment( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return false;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	$is_guest_payment = ! empty( $order->user_id ) && $order->user_id > 0
		? false
		: true;

	return (bool) apply_filters( 'edd_is_guest_payment', $is_guest_payment, $order->id );
}

/**
 * Get the user ID associated with an order.
 *
 * @since 1.5.1
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return string $user_id User ID.
 */
function edd_get_payment_user_id( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return 0;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->user_id
		: 0;
}

/**
 * Get the customer ID associated with an order.
 *
 * @since 2.1
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return int $customer_id Customer ID.
 */
function edd_get_payment_customer_id( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return 0;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->customer_id
		: 0;
}

/**
 * Get the status of the unlimited downloads flag
 *
 * @since 2.0
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return bool True if the payment has unlimited downloads, false otherwise.
 */
function edd_payment_has_unlimited_downloads( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return false;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->has_unlimited_downloads()
		: false;
}

/**
 * Get the IP address used to make a purchase.
 *
 * @since 1.9
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return string User's IP address.
 */
function edd_get_payment_user_ip( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return '';
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->ip
		: '';
}

/**
 * Get the date an order was completed.
 *
 * @since 2.0
 * @since 3.0 Parameter renamed to $order_id.
 *
 * @param int $order_id Order ID.
 * @return string The date the order was completed.
 */
function edd_get_payment_completed_date( $order_id = 0 ) {
	$payment = edd_get_payment( $order_id );
	return $payment->completed_date;
}

/**
 * Get the gateway associated with an order.
 *
 * @since 1.2
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return string Payment gateway used for the order.
 */
function edd_get_payment_gateway( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return '';
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->gateway
		: '';
}

/**
 * Get the currency code an order was made in.
 *
 * @since 2.2
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return string $currency The currency code
 */
function edd_get_payment_currency_code( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return '';
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->currency
		: '';
}

/**
 * Get the currency name a payment was made in.
 *
 * @since 2.2
 * @since 3.0 Parameter renamed to $order_id.
 *
 * @param int $order_id Order ID.
 * @return string $currency The currency name.
 */
function edd_get_payment_currency( $order_id = 0 ) {
	$currency = edd_get_payment_currency_code( $order_id );

	/**
	 * Allow the currency to be filtered.
	 *
	 * @since 2.2
	 *
	 * @param string $currency Currency name.
	 * @param int    $order_id Order ID.
	 */
	return apply_filters( 'edd_payment_currency', edd_get_currency_name( $currency ), $order_id );
}

/**
 * Get the payment key for an order.
 *
 * @since 1.2
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return string $key Purchase key.
 */
function edd_get_payment_key( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return '';
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->payment_key
		: '';
}

/**
 * Get the payment order number.
 *
 * This will return the order ID if sequential order numbers are not enabled or the order number does not exist.
 *
 * @since 2.0
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return int|string Payment order number.
 */
function edd_get_payment_number( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return 0;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->get_number()
		: 0;
}

/**
 * Formats the order number with the prefix and postfix.
 *
 * @todo As of 3.1.2, no longer used, but not officially deprecated. Deprecate.
 * @since 2.4
 *
 * @param int $number The order number to format.
 * @return string The formatted order number
 */
function edd_format_payment_number( $number ) {
	$order_number = new EDD\Orders\Number();

	return $order_number->format( $number );
}

/**
 * Given a given a number, remove the pre/postfix.
 *
 * @since 2.4
 * @todo As of 3.1.2, no longer used, but not officially deprecated. Deprecate.
 * @param string $number The formatted number to increment.
 * @return string  The new order number without prefix and postfix.
 */
function edd_remove_payment_prefix_postfix( $number ) {
	$order_number = new EDD\Orders\Number();

	return $order_number->unformat( $number );
}

/**
 * Get the fully formatted order amount. The order amount is retrieved using
 * edd_get_payment_amount() and is then sent through edd_currency_filter() and
 * edd_format_amount() to format the amount correctly.
 *
 * @since 1.4
 * @since 3.0 Parameter renamed to $order_id.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 *
 * @return string $amount Fully formatted payment amount
 */
function edd_payment_amount( $order = 0 ) {
	if ( is_numeric( $order ) && ! empty( $order ) ) {
		$order = edd_get_order( $order );
	}

	return edd_display_amount( edd_get_payment_amount( $order ), edd_get_payment_currency_code( $order ) );
}

/**
 * Get the amount associated with an order.
 *
 * @since 1.2
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return float Order amount.
 */
function edd_get_payment_amount( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return 0.00;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	$total = $order
		? $order->total
		: 0.00;

	/**
	 * Filter the order amount.
	 *
	 * @since 1.2
	 *
	 * @param float $total    Order total.
	 * @param int   $order_id Order ID.
	 */
	return apply_filters( 'edd_payment_amount', floatval( $total ), $order->id );
}

/**
 * Retrieves subtotal for an order (this is the amount before taxes) and then
 * returns a full formatted amount. This function essentially calls
 * edd_get_payment_subtotal().
 *
 * @since 1.3.3
 * @since 3.0 Parameter renamed to $order_id.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order_id Order ID.
 *
 * @return string Fully formatted order subtotal.
 */
function edd_payment_subtotal( $order_id = 0 ) {
	$subtotal = edd_get_payment_subtotal( $order_id );

	return edd_display_amount( $subtotal, edd_get_payment_currency_code( $order_id ) );
}

/**
 * Retrieves subtotal for an order (this is the amount before taxes) and then
 * returns a non formatted amount.
 *
 * @since 1.3.3
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @return float $subtotal Subtotal for the order (non formatted).
 */
function edd_get_payment_subtotal( $order = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return 0.00;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->subtotal
		: 0.00;
}

/**
 * Retrieves taxed amount for payment and then returns a full formatted amount
 * This function essentially calls edd_get_payment_tax()
 *
 * @since 1.3.3
 * @since 3.0 Parameter renamed to $order_id.
 *
 * @param int  $order_id     Order ID.
 * @param bool $payment_meta Parameter no longer used.
 *
 * @return string $tax Fully formatted tax amount.
 */
function edd_payment_tax( $order_id = 0, $payment_meta = null ) {
	$tax = edd_get_payment_tax( $order_id, false );

	return edd_display_amount( $tax, edd_get_payment_currency_code( $order_id ) );
}

/**
 * Retrieves taxed amount for payment and then returns a non formatted amount
 *
 * @since 1.3.3
 * @since 3.0 Refactored to use EDD\Orders\Order.
 * @since 3.1.1 Allows passing a full EDD\Orders\Order object in, instead of just the ID
 *
 * @param int $order Order ID or the EDD\Orders\Order object
 * @param bool $payment_meta Parameter no longer used.
 *
 * @return float $tax Tax for payment (non formatted)
 */
function edd_get_payment_tax( $order = 0, $payment_meta = null ) {

	// Bail if nothing was passed.
	if ( empty( $order ) ) {
		return 0.00;
	}

	if ( is_numeric( $order ) ) {
		$order = edd_get_order( $order );
	}

	return $order
		? $order->tax
		: 0.00;
}

/**
 * Retrieve the tax for a cart item by the cart key.
 *
 * @since 2.5
 * @since 3.0 Refactored to use EDD\Orders\Order_Item.
 *
 * @param  int $order_id   Order ID.
 * @param  int $cart_index Cart index.
 *
 * @return float Cart item tax amount.
 */
function edd_get_payment_item_tax( $order_id = 0, $cart_index = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order_id ) ) {
		return 0.00;
	}

	$order_item_tax = edd_get_order_items( array(
		'number'     => 1,
		'order_id'   => $order_id,
		'cart_index' => $cart_index,
		'fields'     => 'tax',
	) );

	$order_item_tax = ( $order_item_tax && ! empty( $order_item_tax ) )
		? $order_item_tax[0]
		: 0.00;

	return (float) $order_item_tax;
}

/**
 * Retrieves arbitrary fees for the order.
 *
 * @since 1.5
 * @since 3.0 Parameter renamed to $order_id.
 *
 * @param int    $order_id Order ID.
 * @param string $type     Fee type. Default all.
 *
 * @return array Order fees.
 */
function edd_get_payment_fees( $order_id = 0, $type = 'all' ) {

	// Bail if nothing was passed.
	if ( empty( $order_id ) ) {
		return array();
	}

	$payment = edd_get_payment( $order_id );

	return $payment
		? $payment->get_fees( $type )
		: array();
}

/**
 * Retrieves the transaction ID for an order.
 *
 * @since 2.1
 * @since 3.0 Refactored to use EDD\Orders\Order.
 *
 * @param int $order_id Order ID.
 * @return string Transaction ID.
 */
function edd_get_payment_transaction_id( $order_id = 0 ) {

	// Bail if nothing was passed.
	if ( empty( $order_id ) ) {
		return '';
	}

	$order = edd_get_order( $order_id );

	return $order
		? $order->get_transaction_id()
		: '';
}

/**
 * Sets a transaction ID for a given order.
 *
 * @since 2.1
 * @since 3.0 Updated to use new methods and store data in the new tables.
 *            Added $amount parameter.
 *
 * @param int    $order_id       Order ID.
 * @param string $transaction_id Transaction ID from the gateway.
 * @param mixed  $amount         Transaction amount.
 *
 * @return mixed Meta ID if successful, false if unsuccessful.
 */
function edd_set_payment_transaction_id( $order_id = 0, $transaction_id = '', $amount = false ) {

	// Bail if nothing was passed.
	if ( empty( $order_id ) || empty( $transaction_id ) ) {
		return false;
	}

	/**
	 * Filter the transaction ID before being stored in the database.
	 *
	 * @since 2.1
	 *
	 * @param string $transaction_id Transaction ID.
	 * @param int    $order_id       Order ID.
	 */
	$transaction_id = apply_filters( 'edd_set_payment_transaction_id', $transaction_id, $order_id );

	$order = edd_get_order( $order_id );

	if ( $order ) {
		$amount = false === $amount
			? $order->total
			: floatval( $amount );

		$transaction_ids = array_values( edd_get_order_transactions( array(
			'fields'      => 'ids',
			'number'      => 1,
			'object_id'   => $order_id,
			'object_type' => 'order',
			'orderby'     => 'date_created',
			'order'       => 'ASC',
		) ) );

		if ( $transaction_ids && isset( $transaction_ids[0] ) ) {
			return edd_update_order_transaction( $transaction_ids[0], array(
				'transaction_id' => $transaction_id,
				'gateway'        => $order->gateway,
				'total'          => $amount,
			) );
		} else {
			return edd_add_order_transaction( array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => $transaction_id,
				'gateway'        => $order->gateway,
				'status'         => 'complete',
				'total'          => $amount,
			) );
		}
	}

	return false;
}

/**
 * Retrieve the order ID based on the payment key.
 *
 * @since 1.3.2
 * @since 3.0 Updated to use new query methods. Renamed parameter to $payment_key.
 *
 * @param string $payment_key Payment key to search for.
 * @return int Order ID.
 */
function edd_get_purchase_id_by_key( $payment_key ) {
	$global_key_string = 'edd_purchase_id_by_key' . $payment_key;
	global $$global_key_string;

	if ( null !== $$global_key_string ) {
		return $$global_key_string;
	}

	/** @var EDD\Orders\Order $order */
	$order = edd_get_order_by( 'payment_key', $payment_key );

	if ( false !== $order ) {
		$$global_key_string = $order->id;
		return $$global_key_string;
	}

	return 0;
}

/**
 * Retrieve the order ID based on the transaction ID.
 *
 * @since 2.4
 * @since 3.0 Dispatch to edd_get_order_id_from_transaction_id().
 *
 * @see edd_get_order_id_from_transaction_id()
 *
 * @param string $transaction_id Transaction ID to search for.
 * @return int Order ID.
 */
function edd_get_purchase_id_by_transaction_id( $transaction_id ) {
	return edd_get_order_id_from_transaction_id( $transaction_id );
}

/**
 * Retrieve all notes attached to an order.
 *
 * @since 1.4
 * @since 3.0 Updated to use the edd_notes custom table to store notes.
 *
 * @param int    $order_id   The order ID to retrieve notes for.
 * @param string $search     Search for notes that contain a search term.
 * @return array|bool $notes Order notes, false otherwise.
 */
function edd_get_payment_notes( $order_id = 0, $search = '' ) {

	// Bail if nothing passed.
	if ( empty( $order_id ) && empty( $search ) ) {
		return false;
	}

	$args = array(
		'object_type' => 'order',
		'order'       => 'ASC',
	);

	if ( ! empty( $order_id ) ) {
		$args['object_id'] = $order_id;
	}

	if ( ! empty( $search ) ) {
		$args['search'] = sanitize_text_field( $search );
	}

	return edd_get_notes( $args );
}

/**
 * Add a note to an order.
 *
 * @since 1.4
 * @since 3.0 Updated to use the edd_notes custom table to store notes.
 *
 * @param int    $order_id The order ID to store a note for.
 * @param string $note     The content of the note.
 * @return int|false The new note ID, false otherwise.
 */
function edd_insert_payment_note( $order_id = 0, $note = '' ) {

	// Sanitize note contents
	if ( ! empty( $note ) ) {
		$note = trim( wp_kses( $note, edd_get_allowed_tags() ) );
	}

	// Bail if no order ID or note.
	if ( empty( $order_id ) || empty( $note ) ) {
		return false;
	}

	do_action( 'edd_pre_insert_payment_note', $order_id, $note );

	/**
	 * For backwards compatibility purposes, we need to pass the data to
	 * wp_filter_comment in the event that the note data is filtered using the
	 * WordPress Core filters prior to be inserted into the database.
	 */
	$filtered_data = wp_filter_comment( array(
		'comment_post_ID'      => $order_id,
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
	) );

	// Add the note
	$note_id = edd_add_note( array(
		'object_id'   => $filtered_data['comment_post_ID'],
		'content'     => $filtered_data['comment_content'],
		'user_id'     => $filtered_data['user_id'],
		'object_type' => 'order',
	) );

	do_action( 'edd_insert_payment_note', $note_id, $order_id, $note );

	// Return the ID of the new note
	return $note_id;
}

/**
 * Deletes an order note.
 *
 * @since 1.6
 * @since 3.0 Updated to use the edd_notes custom table to store notes.
 *
 * @param int $note_id  Note ID.
 * @param int $order_id Order ID.
 * @return bool True on success, false otherwise.
 */
function edd_delete_payment_note( $note_id = 0, $order_id = 0 ) {
	if ( empty( $note_id ) ) {
		return false;
	}

	do_action( 'edd_pre_delete_payment_note', $note_id, $order_id );

	$ret = edd_delete_note( $note_id );

	do_action( 'edd_post_delete_payment_note', $note_id, $order_id );

	return $ret;
}

/**
 * Gets the payment note HTML.
 *
 * @since 1.9
 * @since 3.0 Deprecated & unused (use edd_admin_get_note_html())
 *
 * @param object|int $note       The note object or ID.
 * @param int        $payment_id The payment ID the note is connected to.
 *
 * @return string Payment note HTML.
 */
function edd_get_payment_note_html( $note, $payment_id = 0 ) {
	return edd_admin_get_note_html( $note );
}

/**
 * Exclude notes (comments) on edd_payment post type from showing in Recent
 * Comments widgets.
 *
 * @since 1.4.1
 *
 * @param object $query WordPress Comment Query Object
 */
function edd_hide_payment_notes( $query ) {
	global $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '>=' ) ) {
		$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();

		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}

		$types[] = 'edd_payment_note';

		$query->query_vars['type__not_in'] = $types;
	}
}
add_action( 'pre_get_comments', 'edd_hide_payment_notes', 10 );

/**
 * Exclude notes (comments) on edd_payment post type from showing in Recent
 * Comments widgets
 *
 * @since 2.2
 *
 * @param array $clauses           Comment clauses for comment query.
 * @param object $wp_comment_query WordPress Comment Query Object.
 *
 * @return array $clauses Updated comment clauses.
 */
function edd_hide_payment_notes_pre_41( $clauses, $wp_comment_query ) {
	global $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
		$clauses['where'] .= ' AND comment_type != "edd_payment_note"';
	}

	return $clauses;
}
add_filter( 'comments_clauses', 'edd_hide_payment_notes_pre_41', 10, 2 );

/**
 * Exclude notes (comments) on edd_payment post type from showing in comment feeds.
 *
 * @since 1.5.1
 *
 * @param string $where
 * @param object $wp_comment_query WordPress Comment Query Object
 *
 * @return string $where Updated WHERE clause.
 */
function edd_hide_payment_notes_from_feeds( $where, $wp_comment_query ) {
	global $wpdb;

	$where .= $wpdb->prepare( ' AND comment_type != %s', 'edd_payment_note' );

	return $where;
}
add_filter( 'comment_feed_where', 'edd_hide_payment_notes_from_feeds', 10, 2 );

/**
 * Remove EDD Comments from the wp_count_comments function
 *
 * @since 1.5.2
 *
 * @param array $stats   Empty from core filter.
 * @param int   $post_id Post ID.
 *
 * @return object Comment counts.
*/
function edd_remove_payment_notes_in_comment_counts( $stats, $post_id ) {
	global $wpdb, $pagenow;

	$array_excluded_pages = array( 'index.php', 'edit-comments.php' );
	if ( ! in_array( $pagenow, $array_excluded_pages, true ) ) {
		return $stats;
	}

	$post_id = (int) $post_id;

	if ( apply_filters( 'edd_count_payment_notes_in_comments', false ) ) {
		return $stats;
	}

	$stats = wp_cache_get( "comments-{$post_id}", 'counts' );

	if ( false !== $stats ) {
		return $stats;
	}

	$where = 'WHERE comment_type != "edd_payment_note"';

	if ( $post_id > 0 ) {
		$where .= $wpdb->prepare( " AND comment_post_ID = %d", $post_id );
	}

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

	$total = 0;
	$stats = array();

	$approved = array(
		'0'            => 'moderated',
		'1'            => 'approved',
		'spam'         => 'spam',
		'trash'        => 'trash',
		'post-trashed' => 'post-trashed',
	);

	foreach ( (array) $count as $row ) {

		// Don't count post-trashed toward totals.
		if ( 'post-trashed' !== $row['comment_approved'] && 'trash' !== $row['comment_approved'] ) {
			$total += $row['num_comments'];
		}

		if ( isset( $approved[ $row['comment_approved'] ] ) ) {
			$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
		}
	}

	$stats['total_comments'] = $total;
	foreach ( $approved as $key ) {
		if ( empty( $stats[ $key ] ) ) {
			$stats[ $key ] = 0;
		}
	}

	$stats = (object) $stats;
	wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

	return $stats;
}
add_filter( 'wp_count_comments', 'edd_remove_payment_notes_in_comment_counts', 10, 2 );

/**
 * Filter where older than one week.
 *
 * @since 1.6
 *
 * @param string $where Where clause.
 * @return string $where Modified where clause.
*/
function edd_filter_where_older_than_week( $where = '' ) {

	// Payments older than one week.
	$start = date( 'Y-m-d', strtotime( '-7 days' ) );

	$where .= " AND post_date <= '{$start}'";

	return $where;
}

/**
 * Gets the payment ID from the final edd_payment post.
 * This was set as an option when the custom orders table was created.
 * For internal use only.
 *
 * @todo deprecate in 3.1
 *
 * @since 3.0
 * @return false|int
 */
function _edd_get_final_payment_id() {
	return get_option( 'edd_v3_migration_pending', false );
}

/**
 * Evaluates whether the EDD 3.0 migration should be run,
 * based on _any_ data existing which will need to be migrated.
 *
 * This should only be run after `edd_v30_is_migration_complete` has returned false.
 *
 * @todo deprecate in 3.1
 *
 * @since 3.0.2
 * @return bool
 */
function _edd_needs_v3_migration() {
	// Return true if a final payment ID was recorded.
	if ( _edd_get_final_payment_id() ) {
		return true;
	}

	// Return true if any tax rates were saved.
	$tax_rates = get_option( 'edd_tax_rates', array() );
	if ( ! empty( $tax_rates ) ) {
		return true;
	}

	// Return true if a fallback tax rate was saved.
	if ( edd_get_option( 'tax_rate', false ) ) {
		return true;
	}

	global $wpdb;

	// Return true if any discounts were saved.
	$discounts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$wpdb->posts}
			 WHERE post_type = %s
			 LIMIT 1",
			esc_sql( 'edd_discount' )
		)
	);
	if ( ! empty( $discounts ) ) {
		return true;
	}

	// Return true if there are any customers.
	$customers = $wpdb->get_results(
		"SELECT *
		FROM {$wpdb->edd_customers}
		LIMIT 1"
	);
	if ( ! empty( $customers ) ) {
		return true;
	}

	// Return true if any customer email addresses were saved.
	$customer_emails = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$wpdb->edd_customermeta}
			 WHERE meta_key = %s
			 LIMIT 1",
			esc_sql( 'additional_email' )
		)
	);
	if ( ! empty( $customer_emails ) ) {
		return true;
	}

	// Return true if any customer addresses are in the user meta table.
	$customer_addresses = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$wpdb->usermeta}
			 WHERE meta_key = %s
			 ORDER BY umeta_id ASC
			 LIMIT 1",
			esc_sql( '_edd_user_address' )
		)
	);
	if ( ! empty( $customer_addresses ) ) {
		return true;
	}

	// Return true if there are any EDD logs (not sales) saved.
	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.*, t.slug
			 FROM {$wpdb->posts} AS p
			 LEFT JOIN {$wpdb->term_relationships} AS tr ON (p.ID = tr.object_id)
			 LEFT JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			 LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id)
			 WHERE p.post_type = %s AND t.slug != %s
			 GROUP BY p.ID
			 LIMIT 1",
			esc_sql( 'edd_log' ),
			esc_sql( 'sale' )
		)
	);
	if ( ! empty( $logs ) ) {
		return true;
	}

	return false;
}

/**
 * Maybe adds a migration in progress notice to the order history.
 *
 * @todo remove in 3.1
 * @since 3.0
 * @return void
 */
add_action( 'edd_pre_order_history', function( $orders, $user_id ) {
	if ( ! _edd_get_final_payment_id() ) {
		return;
	}
	?>
	<p class="edd-notice">
		<?php esc_html_e( 'A store migration is in progress. Past orders will not appear in your purchase history until they have been updated.', 'easy-digital-downloads' ); ?>
	</p>
	<?php
}, 10, 2 );
