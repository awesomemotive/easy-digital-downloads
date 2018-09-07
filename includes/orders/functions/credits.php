<?php
/**
 * Order Credit Functions
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
 * Apply credit to an order. This method should be used to apply a flat amount
 * of credit to an order.
 *
 * @since 3.0
 *
 * @param int   $order_id Order ID.
 * @param array $data     Credit data. For accepted parameters, see `edd_add_order_adjustment()`.
 *
 * @return int|false New order ID if successful, false otherwise.
 */
function edd_apply_order_credit( $order_id = 0, $data = array() ) {
	global $wpdb;

	// Bail if invalid data passed.
	if ( empty( $order_id ) || empty( $data ) ) {
		return false;
	}

	// Ensure the order ID is an integer.
	$order_id = absint( $order_id );

	// Fetch order.
	$order = edd_get_order( $order_id );

	// Bail if order was not found or it has been revoked.
	if ( ! $order || 'revoked' === $order->status ) {
		return false;
	}

	/**
	 * Filter whether refunds should be allowed.
	 *
	 * @since 3.0
	 *
	 * @param int $order_id Order ID.
	 */
	$should_apply_credit = apply_filters( 'edd_should_apply_order_credit', '__return_true', $order_id );

	// Bail if refund is blocked.
	if ( ! $should_apply_credit ) {
		return false;
	}

	/** Generate new order number ********************************************/

	$last_order = $wpdb->get_row( $wpdb->prepare( "SELECT id, order_number
		FROM {$wpdb->edd_orders}
		WHERE parent = %d
		ORDER BY id DESC
		LIMIT 1", 0 ) );

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
		$number = $last_order->id . $refund_suffix . '1';
	}

	// Parse the adjustment data.
	$data = wp_parse_args( $data, array(
		'type_id'     => 0,
		'description' => '',
		'subtotal'    => 0.00,
		'total'       => 0.00,
	) );

	// Bail if the adjustment is worth nothing.
	if ( 0.00 === floatval( $data['total'] ) ) {
		return false;
	}

	// Bail if the order total is 0 or will drop below 0.
	if ( 0 === edd_get_order_total( $order_id ) || 0 > ( edd_get_order_total( $order_id ) - floatval( $data['total'] ) ) ) {
		return false;
	}

	/** Insert order *********************************************************/

	$order_data = array(
		'parent'       => $order_id,
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
		'total'        => edd_negate_amount( $data['total'] ),
	);

	$new_order_id = edd_add_order( $order_data );

	/** Insert adjustment *****************************************************/

	edd_add_order_adjustment( array(
		'object_type' => 'order',
		'object_id'   => $new_order_id,
		'type_id'     => absint( $data['type_id'] ),
		'type'        => 'credit',
		'description' => sanitize_text_field( $data['description'] ),
		'subtotal'    => floatval( $data['subtotal'] ),
		'total'       => floatval( $data['total'] ),
	) );

	return $new_order_id;
}

/**
 * Apply credit to an order item. This method should be used to apply a flat
 * amount of credit to an order item.
 *
 * @since 3.0
 *
 * @param int   $order_item_id Order item ID.
 * @param array $data          Credit data. For accepted parameters, see `edd_add_order_adjustment()`.
 *
 * @return int|false New order ID if successful, false otherwise.
 */
function edd_apply_order_item_credit( $order_item_id = 0, $data = array() ) {
	global $wpdb;

	// Bail if invalid data passed.
	if ( empty( $order_item_id ) || empty( $data ) ) {
		return false;
	}

	// Ensure the order ID is an integer.
	$order_item_id = absint( $order_item_id );

	$order_item = edd_get_order_item( $order_item_id );

	// Bail if order item was not found .
	if ( ! $order_item ) {
		return false;
	}

	$order = edd_get_order( $order_item->order_id );

	// Bail if order item was not found or it has been revoked.
	if ( ! $order || 'revoked' === $order->status ) {
		return false;
	}

	/**
	 * Filter whether refunds should be allowed.
	 *
	 * @since 3.0
	 *
	 * @param int $order_item_id Order item ID.
	 */
	$should_apply_credit = apply_filters( 'edd_should_apply_order_item_credit', '__return_true', $order_item_id );

	// Bail if refund is blocked.
	if ( ! $should_apply_credit ) {
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

	// Parse the adjustment data.
	$data = wp_parse_args( $data, array(
		'type_id'     => 0,
		'description' => '',
		'subtotal'    => 0.00,
		'total'       => 0.00,
	) );

	// Bail if the adjustment is worth nothing.
	if ( 0.00 === floatval( $data['total'] ) ) {
		return false;
	}

	// Bail if the order total is 0 or will drop below 0.
	if ( 0 === edd_get_order_total( $order->id ) || 0 > ( edd_get_order_total( $order->id ) - floatval( $data['total'] ) ) ) {
		return false;
	}

	/** Insert order *********************************************************/

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
		'total'        => edd_negate_amount( $data['total'] ),
	);

	$new_order_id = edd_add_order( $order_data );

	/** Insert order item ****************************************************/

	$order_item_data = array(
		'order_id'     => $new_order_id,
		'product_id'   => $order_item->product_id,
		'product_name' => $order_item->product_name,
		'price_id'     => $order_item->price_id,
		'cart_index'   => $order_item->cart_index,
		'type'         => 'download',
		'status'       => 'partially_refunded',
		'amount'       => $order_item->amount,
		'subtotal'     => edd_negate_amount( floatval( $data['subtotal'] ) ),
		'total'        => edd_negate_amount( floatval( $data['total'] ) ),
	);

	$new_order_item_id = edd_add_order_item( $order_item_data );

	/** Insert adjustment *****************************************************/

	edd_add_order_adjustment( array(
		'object_type' => 'order_item',
		'object_id'   => $new_order_item_id,
		'type_id'     => absint( $data['type_id'] ),
		'type'        => 'credit',
		'description' => sanitize_text_field( $data['description'] ),
		'subtotal'    => floatval( $data['subtotal'] ),
		'total'       => floatval( $data['total'] ),
	) );

	return $new_order_id;
}
