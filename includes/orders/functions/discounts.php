<?php
/**
 * Order Discount Functions
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
 * Retroactively apply a discount code to an order.
 *
 * @since 3.0
 *
 * @param int $order_id    Order ID.
 * @param int $discount_id Discount ID.
 *
 * @return int|false New order ID if successful, false otherwise.
 */
function edd_apply_order_discount( $order_id = 0, $discount_id = 0 ) {
	global $wpdb;

	// Bail if no order ID or discount ID was passed.
	if ( empty( $order_id ) || empty( $discount_id ) ) {
		return false;
	}

	// Fetch from the database.
	$order    = edd_get_order( $order_id );
	$discount = edd_get_discount( $discount_id );

	// Bail if either of the objects were not found.
	if ( ! $order || ! $discount ) {
		return false;
	}

	// Fetch the current order total (including all refunds).
	$current_total = edd_get_order_total( $order_id );

	// Bail if the total is already 0.
	if ( 0 === $current_total ) {
		return false;
	}

	// Ensure the discount can be used.
	if ( ! edd_validate_discount( $discount_id, wp_parse_id_list( wp_list_pluck( $order->items, 'id' ) ) ) ) {
		return false;
	}

	/** Generate new order number ********************************************/

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
		$number = $order->id . $refund_suffix . '1';
	}

	// Build new order data.
	$order_data = array(
		'parent'       => $order->id,
		'order_number' => $number,
		'type'         => 'refund',
		'status'       => 'partially_refunded',
		'user_id'      => $order->user_id,
		'customer_id'  => $order->customer_id,
		'email'        => $order->email,
		'ip'           => $order->ip,
		'gateway'      => $order->gateway,
		'mode'         => $order->mode,
		'currency'     => $order->currency,
		'payment_key'  => strtolower( md5( uniqid() ) ),
		'discount'     => 0,
		'total'        => 0,
	);

	$new_order_id = edd_add_order( $order_data );

	$order_items = array();

	foreach ( $order->items as $order_item ) {
		$total     = edd_get_order_item_total( array( $order->id ), $order_item->product_id );
		$reduction = floatval( $total - $discount->get_discounted_amount( $total ) );

		if ( 0 === $total ) {
			continue;
		}

		$item             = $order_item->to_array();
		$item['order_id'] = $new_order_id;
		$item['quantity'] = 0; // Quantity is set to 0 to allow for accurate reporting.
		$item['amount']   = 0;
		$item['subtotal'] = 0;
		$item['discount'] = $reduction;
		$item['total']    = edd_negate_amount( $reduction );
		unset( $item['id'] );
		unset( $item['adjustments'] );

		$order_data['discount'] += $reduction;
		$order_data['total']    += edd_negate_amount( $reduction );

		$order_items[] = $item;
	}

	array_map( 'edd_add_order_item', $order_items );

	edd_add_order_adjustment( array(
		'object_id'   => $new_order_id,
		'object_type' => 'order',
		'type_id'     => $discount_id,
		'type'        => 'discount',
		'description' => $discount->code,
		'subtotal'    => $order_data['discount'],
		'total'       => $order_data['discount'],
	) );

	edd_update_order( $new_order_id, $order_data );

	return $new_order_id;
}
