<?php
/**
 * Order Refund Functions
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Check order can be refunded and is within the refund window.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return bool True if refundable, false otherwise.
 */
function edd_is_order_refundable( $order_id = 0 ) {
	global $wpdb;

	// Bail if no order ID was passed.
	if ( empty( $order_id ) ) {
		return false;
	}

	$order = edd_get_order( $order_id );

	// Bail if order was not found.
	if ( ! $order ) {
		return false;
	}

	// Only completed orders can be refunded.
	if ( 'complete' !== $order->status ) {
		return false;
	}

	// Check order hasn't already been refunded.
	$query          = "SELECT COUNT(id) FROM {$wpdb->edd_orders} WHERE parent = %d AND status = '%s'";
	$prepare        = sprintf( $query, $order_id, esc_sql( 'refunded' ) );
	$refunded_order = $wpdb->get_var( $prepare ); // WPCS: unprepared SQL ok.

	if ( 0 < absint( $refunded_order ) ) {
		return false;
	}

	// Refund dates may not have been set retroactively so we need to calculate it manually.
	if ( '0000-00-00 00:00:00' === $order->date_refundable ) {
		$refund_window = absint( edd_get_option( 'refund_window', 30 ) );

		// Refund window is infinite.
		if ( 0 === $refund_window ) {
			return true;
		} else {
			$date_refundable = \Carbon\Carbon::parse( $order->date_completed, 'UTC' )->setTimezone( edd_get_timezone_id() )->addDays( $refund_window );
		}

	// Parse date using Carbon.
	} else {
		$date_refundable = \Carbon\Carbon::parse( $order->date_refundable, 'UTC' )->setTimezone( edd_get_timezone_id() );
	}

	// Bail if we have passed the refund date.
	if ( $date_refundable->isPast() ) {
		return false;
	}

	// If we have reached here, every other check holds so the order is refundable.
	return true;
}

/**
 * Refund entire order.
 *
 * @since 3.0
 *
 * @param int    $order_id    Order ID.
 * @param string $status      Optional. Refund status. Default `complete`.
 * @param array  $order_items Optional. Array of Order Item IDs to allow for a partial refund.
 *
 * @return int|false New order ID if successful, false otherwise.
 */
function edd_refund_order( $order_id = 0, $status = 'complete', $order_items = array() ) {
	global $wpdb;

	// Bail if no order ID was passed.
	if ( empty( $order_id ) ) {
		return false;
	}

	// Ensure the order ID is an integer.
	$order_id = absint( $order_id );

	// Sanitize status.
	$status = strtolower( sanitize_text_field( $status ) );

	// Status can only either be `complete` or `pending`.
	if ( ! in_array( $status, array( 'pending', 'complete' ), true ) ) {
		$status = 'complete'; // Default to `complete`.
	}

	// Fetch order.
	$order = edd_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	if ( ! edd_is_order_refundable( $order_id ) ) {
		return false;
	}

	/**
	 * Filter whether refunds should be allowed.
	 *
	 * @since 3.0
	 *
	 * @param int $order_id Order ID.
	 */
	$should_refund = apply_filters( 'edd_should_process_order_refund', true, $order_id );

	// Bail if refund is blocked.
	if ( true !== $should_refund ) {
		return false;
	}

	/** Generate new order number *********************************************/

	$last_order = $wpdb->get_row( $wpdb->prepare( "SELECT id, order_number
		FROM {$wpdb->edd_orders}
		WHERE parent = %d
		ORDER BY id DESC
		LIMIT 1", $order_id ) );

	/**
	 * Filter the suffix applied to order numbers for refunds.
	 *
	 * @since 3.0
	 *
	 * @param string Suffix.
	 */
	$refund_suffix = apply_filters( 'edd_order_refund_suffix', '-R-' );

	if ( $last_order ) {

		// Check for order number first.
		if ( $last_order->order_number && ! empty( $last_order->order_number ) ) {

			// Order has been previously revised.
			if ( false !== strpos( $last_order->order_number, $refund_suffix ) ) {
				$number = $last_order->order_number;
				++$number;

			// First revision to order.
			} else {
				$number = $last_order->id . $refund_suffix . '1';
			}

		// Append to ID.
		} else {
			$number = $last_order->id . $refund_suffix . '1';
		}
	} else {
		$number = $order->id . $refund_suffix . '1';
	}

	/** Insert order **********************************************************/

	if ( is_array( $order_items ) && ! empty( $order_items ) ) {

		$subtotal = 0;
		$discount = 0;
		$tax      = 0;
		$total    = 0;

		foreach ( $order->items as $item ) {
			if ( ! in_array( $item->id, $order_items ) ) {
				continue;
			}

			$subtotal += $item->subtotal;
			$discount += $item->discount;
			$tax      += $item->tax;
			$total    += $item->total;
		}

	} else {
		$subtotal = $order->subtotal;
		$discount = $order->discount;
		$tax      = $order->tax;
		$total    = $order->total;
	}
	$order_data = array(
		'parent'       => $order_id,
		'order_number' => $number,
		'status'       => $status,
		'type'         => 'refund',
		'user_id'      => $order->user_id,
		'customer_id'  => $order->customer_id,
		'email'        => $order->email,
		'ip'           => $order->ip,
		'gateway'      => $order->gateway,
		'mode'         => $order->mode,
		'currency'     => $order->currency,
		'payment_key'  => strtolower( md5( uniqid() ) ),
		'subtotal'     => edd_negate_amount( $subtotal ),
		'discount'     => edd_negate_amount( $discount ),
		'tax'          => edd_negate_amount( $tax ),
		'total'        => edd_negate_amount( $total ),
	);

	// Full refund is inserted first to allow for conditional checks to run later
	// and update the order, but we need an INSERT to be executed to generate a
	// new order ID.
	$new_order_id = edd_add_order( $order_data );

	/** Insert order items ****************************************************/

	foreach ( $order->items as $item ) {

		// If the $order_items var is an array, and it's not empty, verify this item is one being refunded.
		if ( is_array( $order_items ) && ! empty( $order_items ) && ! in_array( $item->id, $order_items ) ) {
			continue;
		}

		$order_item_id = edd_add_order_item( array(
			'order_id'     => $new_order_id,
			'product_id'   => $item->product_id,
			'product_name' => $item->product_name,
			'price_id'     => $item->price_id,
			'cart_index'   => $item->cart_index,
			'type'         => $item->type,
			'status'       => 'refunded',
			'quantity'     => edd_negate_int( $item->quantity ),
			'amount'       => edd_negate_amount( $item->amount ),
			'subtotal'     => edd_negate_amount( $item->subtotal ),
			'discount'     => edd_negate_amount( $item->discount ),
			'tax'          => edd_negate_amount( $item->tax ),
			'total'        => edd_negate_amount( $item->total ),
		) );

		foreach ( $item->adjustments as $adjustment ) {
			edd_add_order_adjustment( array(
				'object_id'   => $order_item_id,
				'object_type' => 'order_item',
				'type_id'     => $adjustment->type_id,
				'type'        => $adjustment->type,
				'description' => $adjustment->description,
				'subtotal'    => edd_negate_amount( $adjustment->subtotal ),
				'tax'         => edd_negate_amount( $adjustment->tax ),
				'total'       => edd_negate_amount( $adjustment->total ),
			) );
		}

		// Set the order item as 'refunded' on the original order.
		edd_update_order_item( $item->id, array( 'status' => 'refunded' ) );
	}

	/** Insert order adjustments **********************************************/

	foreach ( $order->adjustments as $adjustment ) {
		edd_add_order_adjustment( array(
			'object_id'   => $new_order_id,
			'object_type' => 'order',
			'type_id'     => $adjustment->type_id,
			'type'        => $adjustment->type,
			'description' => $adjustment->description,
			'subtotal'    => edd_negate_amount( $adjustment->subtotal ),
			'tax'         => edd_negate_amount( $adjustment->tax ),
			'total'       => edd_negate_amount( $adjustment->total ),
		) );
	}

	// Update order status to `refunded` once refund is complete and if all items are marked as refunded.
	$all_refunded = true;
	$order_items = edd_get_order_items( array(
		'order_id' => $order_id,
		'number'   => 999,
	) );

	foreach ( $order_items as $order_item ) {
		if ( 'refunded' !== $order_item->status ) {
			$all_refunded = false;
			break;
		}
	}
	if ( 'complete' === $status && $all_refunded ) {
		edd_update_order( $order_id, array(
			'status' => 'refunded',
		) );
	}

	/**
	 * Fires when an order has been refunded.
	 *
	 * @since 3.0
	 *
	 * @param int   $order_id     Order ID of the original order.
	 * @param int   $new_order_id Order ID of the refunded order.
	 * @param float $total        Amount refunded.
	 */
	do_action( 'edd_refund_order', $order_id, $new_order_id, floatval( $order->total ) );

	return $new_order_id;
}

/**
 * Refund an order item entirely.
 *
 * @since 3.0
 *
 * @param int $order_item_id Order Item ID.
 * @return int|false New order ID if successful, false otherwise.
 */
function edd_refund_order_item( $order_item_id = 0 ) {
	global $wpdb;

	// Bail if no order item ID was passed.
	if ( empty( $order_item_id ) ) {
		return false;
	}

	// Fetch order item.
	$order_item = edd_get_order_item( $order_item_id );

	// Bail if order item was not found.
	if ( ! $order_item ) {
		return false;
	}

	// Fetch order.
	$order = edd_get_order( $order_item->order_id );

	// Bail if order has been revoked.
	if ( 'revoked' === $order_item->status ) {
		return false;
	}

	/**
	 * Allow refunds to be stopped.
	 *
	 * @since 3.0
	 *
	 * @param int $order_item Order item ID.
	 */
	$should_refund = apply_filters( 'edd_should_process_partial_refund', true, $order_item_id );

	// Bail if refund is blocked.
	if ( true !== $should_refund ) {
		return false;
	}

	/** Generate new order number *********************************************/

	$last_order = $wpdb->get_row( $wpdb->prepare( "SELECT id, order_number
		FROM {$wpdb->edd_orders}
		WHERE parent = %d
		ORDER BY id DESC
		LIMIT 1", $order->id ) );

	/**
	 * Filter the suffix applied to order numbers for refunds.
	 *
	 * @since 3.0
	 *
	 * @param string Suffix.
	 */
	$refund_suffix = apply_filters( 'edd_order_refund_suffix', '-R-' );

	if ( $last_order ) {

		// Check for order number first.
		if ( $last_order->order_number && ! empty( $last_order->order_number ) ) {

			// Order has been previously revised.
			if ( false !== strpos( $last_order->order_number, $refund_suffix ) ) {
				$number = $last_order->order_number;
				++$number;

				// First revision to order.
			} else {
				$number = $last_order->order_number . $refund_suffix . '1';
			}

			// Append to ID.
		} else {
			$number = $last_order->id . $refund_suffix . '1';
		}
	} else {
		$number = $order->get_number() . $refund_suffix . '1';
	}

	/** Insert order **********************************************************/

	$order_data = array(
		'parent'       => $order->id,
		'order_number' => $number,
		'status'       => 'partially_refunded',
		'type'         => 'refund',
		'user_id'      => $order->user_id,
		'customer_id'  => $order->customer_id,
		'email'        => $order->email,
		'ip'           => $order->ip,
		'gateway'      => $order->gateway,
		'mode'         => $order->mode,
		'currency'     => $order->currency,
		'payment_key'  => strtolower( md5( uniqid() ) ),
		'subtotal'     => edd_negate_amount( $order_item->subtotal ),
		'discount'     => edd_negate_amount( $order_item->discount ),
		'tax'          => edd_negate_amount( $order_item->tax ),
		'total'        => edd_negate_amount( $order_item->total ),
	);

	// Order is inserted first to allow for conditional checks to run later and
	// update the order, but we need an INSERT to be executed to generate a new
	// order ID.
	$new_order_id = edd_add_order( $order_data );

	/** Insert order item *****************************************************/

	$order_item_data = array(
		'order_id'     => $new_order_id,
		'product_id'   => $order_item->product_id,
		'product_name' => $order_item->product_name,
		'price_id'     => $order_item->price_id,
		'cart_index'   => $order_item->cart_index,
		'type'         => 'download',
		'status'       => 'refunded',
		'quantity'     => edd_negate_amount( $order_item->quantity ),
		'amount'       => edd_negate_amount( $order_item->amount ),
		'subtotal'     => edd_negate_amount( $order_item->subtotal ),
		'discount'     => edd_negate_amount( $order_item->discount ),
		'tax'          => edd_negate_amount( $order_item->tax ),
		'total'        => edd_negate_amount( $order_item->total ),
	);

	$new_order_item_id = edd_add_order_item( $order_item_data );

	/** Insert adjustments ****************************************************/

	foreach ( $order_item->adjustments as $adjustment ) {
		if ( 'tax_rate' === $adjustment->type ) {
			continue;
		}

		edd_add_order_adjustment( array(
			'object_type' => 'order_item',
			'object_id'   => $new_order_item_id,
			'type'        => $adjustment->type,
			'description' => $adjustment->description,
			'subtotal'    => edd_negate_amount( $adjustment->subtotal ),
			'tax'         => edd_negate_amount( $adjustment->tax ),
			'total'       => edd_negate_amount( $adjustment->total ),
		) );
	}

	return $new_order_id;
}

/**
 * Calculate order total. This method is used to calculate the total of an order
 * by also taking into account any refunds/partial refunds.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return float $total Order total.
 */
function edd_get_order_total( $order_id = 0 ) {
	global $wpdb;

	// Bail if no order ID was passed.
	if ( empty( $order_id ) ) {
		return 0;
	}

	$query   = "SELECT SUM(total) FROM {$wpdb->edd_orders} WHERE id = %d OR parent = %d";
	$prepare = $wpdb->prepare( $query, $order_id, $order_id );
	$total   = $wpdb->get_var( $prepare ); // WPCS: unprepared SQL ok.
	$retval  = ( null === $total )
		? 0.00
		: floatval( $total );

	return $retval;
}

/**
 * Calculate order item total. This method is used to calculate the total of an
 * order item by also taking into account any refunds/partial refunds.
 *
 * @since 3.0
 *
 * @param array $order_ids  Order IDs.
 * @param int   $product_id Product ID.
 *
 * @return float $total Order total.
 */
function edd_get_order_item_total( $order_ids = array(), $product_id = 0 ) {
	global $wpdb;

	// Bail if no order IDs were passed.
	if ( empty( $order_ids ) ) {
		return 0;
	}

	$query   = "SELECT SUM(total) FROM {$wpdb->edd_order_items} WHERE order_id IN (%s) AND product_id = %d";
	$ids     = join( ',', array_map( 'absint', $order_ids ) );
	$prepare = sprintf( $query, $ids, $product_id );
	$total   = $wpdb->get_var( $prepare ); // WPCS: unprepared SQL ok.
	$retval  = ( null === $total )
		? 0.00
		: floatval( $total );

	return $retval;
}
