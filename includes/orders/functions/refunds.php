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
 * Check order can be refunded.
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
	if ( ! in_array( $order->status, array( 'complete', 'publish', 'partially_refunded' ), true ) ) {
		return false;
	}

	// Check order hasn't already been refunded.
	$query          = "SELECT COUNT(id) FROM {$wpdb->edd_orders} WHERE parent = %d AND status = '%s'";
	$prepare        = sprintf( $query, $order_id, esc_sql( 'refunded' ) );
	$refunded_order = $wpdb->get_var( $prepare ); // WPCS: unprepared SQL ok.

	if ( 0 < absint( $refunded_order ) ) {
		return false;
	}

	// Allow overrides.
	if ( true === edd_is_order_refundable_by_override( $order->id ) ) {
		return true;
	}

	// Outside of Refund window.
	if ( true === edd_is_order_refund_window_passed( $order->id ) ) {
		return false;
	}

	// If we have reached here, every other check holds so the order is refundable.
	return true;
}

/**
 * Check order is passed its refund window.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return bool True if in window, false otherwise.
 */
function edd_is_order_refund_window_passed( $order_id = 0 ) {
	// Bail if no order ID was passed.
	if ( empty( $order_id ) ) {
		return false;
	}

	$order = edd_get_order( $order_id );

	// Bail if order was not found.
	if ( ! $order ) {
		return false;
	}

	// Refund dates may not have been set retroactively so we need to calculate it manually.
	if ( empty( $order->date_refundable ) ) {
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

	return true === $date_refundable->isPast();
}

/**
 * Check order can be refunded via a capability override.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return bool True if refundable via capability override, false otherwise.
 */
function edd_is_order_refundable_by_override( $order_id = 0 ) {
	// Bail if no order ID was passed.
	if ( empty( $order_id ) ) {
		return false;
	}

	$order = edd_get_order( $order_id );

	// Bail if order was not found.
	if ( ! $order ) {
		return false;
	}

	// Allow certain capabilities to always provide refunds.
	$caps = array( 'edit_shop_payments' );

	/**
	 * Filters the user capabilities that are required for overriding
	 * refundability requirements.
	 *
	 * @since 3.0
	 *
	 * @param array $caps     List of capabilities that can override
	 *                        refundability. Default `edit_shop_payments`.
	 * @param int   $order_id ID of current Order being refunded.
	 */
	$caps = apply_filters( 'edd_is_order_refundable_by_override_caps', $caps, $order_id );

	$can_override = false;

	foreach ( $caps as $cap ) {
		if ( true === current_user_can( $cap ) ) {
			$can_override = true;
			break;
		}
	}

	/**
	 * Filters the allowance of refunds on an Order.
	 *
	 * @since 3.0
	 *
	 * @param bool $can_override If the refundability can be overriden by
	 *                           the current user.
	 * @param int  $order_id     ID of current Order being refunded.
	 */
	$can_override = apply_filters( 'edd_is_order_refundable_by_override', $can_override, $order_id );

	return $can_override;
}

/**
 * Refund entire order.
 *
 * @since 3.0
 *
 * @param int    $order_id    Order ID.
 * @param array  $order_items {
 *                            Optional. Array of Order Item IDs to allow for a partial refund.
 * @type int   $order_item_id Required. ID of the order item.
 * @type int   $quantity      Required. Quantity being refunded.
 * @type float $subtotal      Required. Amount to refund, excluding tax.
 * @type float $tax           Optional. Amount of tax to refund.
 *                }
 *
 * @return int|WP_Error New order ID if successful, WP_Error on failure.
 */
function edd_refund_order( $order_id, $order_items = array() ) {
	global $wpdb;

	// Ensure the order ID is an integer.
	$order_id = absint( $order_id );

	// Fetch order.
	$order = edd_get_order( $order_id );

	if ( ! $order ) {
		return new WP_Error( 'invalid_order', __( 'Invalid order.', 'easy-digital-downloads' ) );
	}

	if ( false === edd_is_order_refundable( $order_id ) ) {
		return new WP_Error( 'not_refundable', __( 'Order not refundable.', 'easy-digital-downloads' ) );
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
		return new WP_Error( 'refund_not_allowed', __( 'Refund not allowed on this order.', 'easy-digital-downloads' ) );
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

	/** Validate refund amounts*************************************************/

	$keyed_order_items = array();
	if ( is_array( $order_items ) && ! empty( $order_items ) ) {

		// Reorder the `$order_items` array to use the order item ID as the key.
		foreach( $order_items as $order_item ) {
			if ( isset( $order_item['order_item_id'] ) ) {
				$order_item['status'] = 'refunded'; // Assume fully refunded for now, we'll adjust later if partial.

				$keyed_order_items[ intval( $order_item['order_item_id'] ) ] = $order_item;
			}
		}

		$subtotal = 0;
		$tax      = 0;
		$total    = 0;

		foreach ( $order->items as $item ) {
			if ( ! array_key_exists( $item->id, $keyed_order_items ) ) {
				continue;
			}

			$maximum_refund_amounts = $item->get_refundable_amounts();

			// The refund amount for each order item cannot exceed the original amount minus what's already been refunded.

			// Note: quantity is not checked because you might process multiple partial refunds for the same order item.

			$refund_subtotal = isset( $keyed_order_items[ $item->id ]['subtotal'] )
				? $keyed_order_items[ $item->id ]['subtotal']
				: $item->subtotal;
			if ( $refund_subtotal > $maximum_refund_amounts['subtotal'] ) {
				return new WP_Error( 'invalid_refund_amount', sprintf( __( 'The maximum refund subtotal for order item #%d is %s.', 'easy-digital-downloads' ), $item->id, edd_currency_filter( $maximum_refund_amounts['subtotal'] ) ) );
			}

			$refund_tax = isset( $keyed_order_items[ $item->id ]['tax'] )
				? $keyed_order_items[ $item->id ]['tax']
				: $item->tax;
			if ( $refund_tax > $maximum_refund_amounts['tax'] ) {
				return new WP_Error( 'invalid_refund_amount', sprintf( __( 'The maximum refund tax amount for order item #%d is %s.', 'easy-digital-downloads' ), $item->id, edd_currency_filter( $maximum_refund_amounts['tax'] ) ) );
			}

			$refund_total = $refund_subtotal + $refund_tax;
			if ( $refund_total > $maximum_refund_amounts['total'] ) {
				return new WP_Error( 'invalid_refund_amount', sprintf( __( 'The maximum refund total for order item #%d is %s.', 'easy-digital-downloads' ), $item->id, edd_currency_filter( $maximum_refund_amounts['total'] ) ) );
			} elseif ( $refund_total < $maximum_refund_amounts['total'] ) {
				// Change to partially refunded status if we're not refunding the whole thing.
				$keyed_order_items[ $item->id ]['status'] = 'partially_refunded';
			}

			$subtotal += $refund_subtotal;
			$tax      += $refund_tax;
			$total    += $refund_total;
		}

	} else {
		$subtotal = $order->subtotal;
		$tax      = $order->tax;
		$total    = $order->total;
	}

	// Overall refund total cannot be over total refundable amount.
	$order_total = edd_get_order_total( $order_id );
	if ( $total > $order_total ) {
		return new WP_Error( 'invalid_refund_amount', sprintf( __( 'The maximum refund amount is %s.', 'easy-digital-downloads' ), edd_currency_filter( $order_total ) ) );
	}

	/** Insert order **********************************************************/

	$order_data = array(
		'parent'       => $order_id,
		'order_number' => $number,
		'status'       => 'complete',
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
		'tax'          => edd_negate_amount( $tax ),
		'total'        => edd_negate_amount( $total ),
	);

	// Full refund is inserted first to allow for conditional checks to run later
	// and update the order, but we need an INSERT to be executed to generate a
	// new order ID.
	$new_order_id = edd_add_order( $order_data );

	/** Insert order items ****************************************************/

	foreach ( $order->items as $item ) {

		// If the $keyed_order_items var is an array, and it's not empty, verify this item is one being refunded.
		if ( is_array( $keyed_order_items ) && ! empty( $keyed_order_items ) && ! array_key_exists( $item->id, $keyed_order_items ) ) {
			continue;
		}

		// Get values from `$keyed_order_items` if we can, otherwise fall back to defaults.
		$status   = isset( $keyed_order_items[ $item->id ]['status'] ) ? $keyed_order_items[ $item->id ]['status'] : 'refunded';
		$quantity = isset( $keyed_order_items[ $item->id ]['quantity'] ) ? intval( $keyed_order_items[ $item->id ]['quantity'] ) : $item->quantity;
		$subtotal = isset( $keyed_order_items[ $item->id ]['subtotal'] ) ? $keyed_order_items[ $item->id ]['subtotal'] : $item->subtotal;
		$tax      = isset( $keyed_order_items[ $item->id ]['tax'] ) ? $keyed_order_items[ $item->id ]['tax'] : $item->tax;

		$order_item_id = edd_add_order_item( array(
			'order_id'     => $new_order_id,
			'product_id'   => $item->product_id,
			'product_name' => $item->product_name,
			'price_id'     => $item->price_id,
			'cart_index'   => $item->cart_index,
			'type'         => $item->type,
			'status'       => $status,
			'quantity'     => edd_negate_int( $quantity ),
			'amount'       => edd_negate_amount( $item->amount ),
			'subtotal'     => edd_negate_amount( $subtotal ),
			'tax'          => edd_negate_amount( $tax ),
			'total'        => edd_negate_amount( $subtotal + $tax ),
		) );

		// @todo Item adjustments.

		// Set the order item status on the original order.
		edd_update_order_item( $item->id, array( 'status' => $status ) );
	}

	/** Insert order adjustments **********************************************/

	// @todo

	// Update order status to `refunded` once refund is complete and if all items are marked as refunded.
	$all_refunded = true;
	if ( edd_get_order_total() > 0 ) {
		$all_refunded = false;
	}

	$order_status = true === $all_refunded
		? 'refunded'
		: 'partially_refunded';

	edd_update_order(
		$order_id,
		array(
			'status'      => $order_status,
			'customer_id' => $order->customer_id,
		)
	);

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
 * @return int|WP_Error New order ID if successful, WP_Error on failure otherwise.
 */
function edd_refund_order_item( $order_item_id ) {
	global $wpdb;

	// Fetch order item.
	$order_item = edd_get_order_item( $order_item_id );

	// Bail if order item was not found.
	if ( ! $order_item ) {
		return new WP_Error( 'invalid_order_item', __( 'Order item not found.', 'easy-digital-downloads' ) );
	}

	// Fetch order.
	$order = edd_get_order( $order_item->order_id );

	// Bail if order has been revoked.
	if ( 'revoked' === $order_item->status ) {
		return new WP_Error( 'order_item_revoked', __( 'This order item has been revoked and cannot be refunded.', 'easy-digital-downloads' ) );
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
		return new WP_Error( 'refund_not_allowed', __( 'Refunds are not allowed on this order item.', 'easy-digital-downloads' ) );
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
			'type_key'    => $adjustment->type_key,
			'description' => $adjustment->description,
			'subtotal'    => edd_negate_amount( $adjustment->subtotal ),
			'tax'         => edd_negate_amount( $adjustment->tax ),
			'total'       => edd_negate_amount( $adjustment->total ),
		) );
	}

	return $new_order_id;
}

/**
 * Queries for order refunds.
 *
 * @see \EDD\Database\Queries\Order::__construct()
 *
 * @since 3.0
 *
 * @param int $order_id Parent Order.
 * @return \EDD\Orders\Order[] Array of `Order` objects.
 */
function edd_get_order_refunds( $order_id = 0 ) {
	$order_refunds = new \EDD\Database\Queries\Order();

	return $order_refunds->query( array(
		'type'   => 'refund',
		'parent' => $order_id,
	) );
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
