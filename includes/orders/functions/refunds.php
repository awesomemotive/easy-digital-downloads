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

use EDD\Orders\Refund_Validator;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Returns an array of order statuses that support refunds.
 *
 * @since 3.0
 * @return array
 */
function edd_get_refundable_order_statuses() {
	$refundable_order_statuses = array( 'complete', 'publish', 'partially_refunded' );

	/**
	 * Filters the order statuses that are allowed to be refunded.
	 *
	 * @param array $refundable_order_statuses
	 *
	 * @since 3.0
	 */
	return (array) apply_filters( 'edd_refundable_order_statuses', $refundable_order_statuses );
}

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

	// Only orders with a supported status can be refunded.
	if ( ! in_array( $order->status, edd_get_refundable_order_statuses(), true ) ) {
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
 * @param int   $order_id      Order ID.
 * @param array $order_items   {
 *                             Optional. Array of Order Item IDs to allow for a partial refund.
 *
 * @type int    $order_item_id Required. ID of the order item.
 * @type int    $quantity      Required. Quantity being refunded.
 * @type float  $subtotal      Required. Amount to refund, excluding tax.
 * @type float  $tax           Optional. Amount of tax to refund.
 * }
 *
 * @param array $fees          {
 *                             Optional. Array of fees to refund.
 *
 * @type int    $fee_id        Required. ID of the order adjustment being refunded.
 * @type float  $subtotal      Required. Amount to refund, excluding tax.
 * @type float  $tax           Required. Amount of tax to refund.
 * }
 *
 * @return int|WP_Error New order ID if successful, WP_Error on failure.
 */
function edd_refund_order( $order_id, $order_items = array(), $fees = array() ) {
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

	/** Validate refund amounts *************************************************/

	try {
		$validator = new Refund_Validator( $order, $order_items, $fees );
		$validator->validate_and_calculate_totals();
	} catch ( \Exception $e ) {
		return new WP_Error( 'refund_validation_error', $e->getMessage() );
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
		'tax_rate_id'  => $order->tax_rate_id,
		'subtotal'     => edd_negate_amount( $validator->subtotal ),
		'tax'          => edd_negate_amount( $validator->tax ),
		'total'        => edd_negate_amount( $validator->total ),
	);

	// Full refund is inserted first to allow for conditional checks to run later
	// and update the order, but we need an INSERT to be executed to generate a
	// new order ID.
	$refund_id = edd_add_order( $order_data );

	/** Insert order items ****************************************************/

	foreach ( $validator->get_refunded_order_items() as $order_item ) {
		$order_item['order_id'] = $refund_id;

		edd_add_order_item( $order_item );

		// Update the status on the original order item.
		if ( ! empty( $order_item['parent'] ) && ! empty( $order_item['original_item_status'] ) ) {
			edd_update_order_item( $order_item['parent'], array( 'status' => $order_item['original_item_status'] ) );
		}
	}

	/** Insert order adjustments **********************************************/

	foreach( $validator->get_refunded_fees() as $fee ) {
		if ( ! empty( $fee['object_type'] ) && 'order' === $fee['object_type'] ) {
			$fee['object_id'] = $refund_id;
		}

		/*
		 * Note: Order item adjustments retain their `object_id` link to the *original* order item -- not the
		 * refunded order item. This isn't ideal, but it's because you could refund an order item fee without
		 * refunding the associated item, in which case there would be no refunded order item to reference.
		 * So we link back to the *original* order item in all cases to be consistent.
		 */

		edd_add_order_adjustment( $fee );

		// Update the status on the original order adjustment.
		if ( ! empty( $fee['parent'] ) && ! empty( $fee['original_item_status'] ) ) {
			edd_update_order_adjustment( $fee['parent'], array( 'status' => $fee['original_item_status'] ) );
		}
	}

	// Update order status to `refunded` once refund is complete and if all items are marked as refunded.
	$all_refunded = true;
	if ( edd_get_order_total( $order_id ) > 0 ) {
		$all_refunded = false;
	}

	$order_status = true === $all_refunded
		? 'refunded'
		: 'partially_refunded';

	edd_update_order( $order_id, array( 'status' => $order_status ) );

	/**
	 * Fires when an order has been refunded.
	 *
	 * @since 3.0
	 *
	 * @param int  $order_id     Order ID of the original order.
	 * @param int  $refund_id    ID of the new refund object.
	 * @param bool $all_refunded Whether or not the entire order was refunded.
	 */
	do_action( 'edd_refund_order', $order_id, $refund_id, $all_refunded );

	return $refund_id;
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
function edd_get_order_total( $order_id ) {
	global $wpdb;

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
