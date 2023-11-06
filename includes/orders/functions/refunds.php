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

	// Check that a full refund has not already been created.
	$refunded_order = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(id) FROM {$wpdb->edd_orders} WHERE parent = %d AND type = 'refund' AND status = 'complete' AND total = %s",
			$order_id,
			- abs( $order->total )
		)
	);

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
			$date_refundable = \EDD\Utils\Date::parse( $order->date_completed, 'UTC' )->setTimezone( edd_get_timezone_id() )->addDays( $refund_window );
		}

	// Parse date using Carbon.
	} else {
		$date_refundable = \EDD\Utils\Date::parse( $order->date_refundable, 'UTC' )->setTimezone( edd_get_timezone_id() );
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
 * @param int          $order_id      Order ID.
 * @param array|string $order_items   {
 *                             Optional. Either `all` as a string to refund all order items, or an array of
 *                             order item IDs, amounts, and quantities to refund.
 *
 * @type int           $order_item_id Required. ID of the order item.
 * @type int           $quantity      Required. Quantity being refunded.
 * @type float         $subtotal      Required. Amount to refund, excluding tax.
 * @type float         $tax           Optional. Amount of tax to refund.
 * }
 *
 * @param array|string $adjustments   {
 *                             Optional. Either `all` as a string to refund all order adjustments, or an array of
 *                             order adjustment IDs and amounts to refund.
 *
 * @type int           $adjustment_id Required. ID of the order adjustment being refunded.
 * @type float         $subtotal      Required. Amount to refund, excluding tax.
 * @type float         $tax           Required. Amount of tax to refund.
 * }
 *
 * @return int|WP_Error New order ID if successful, WP_Error on failure.
 */
function edd_refund_order( $order_id, $order_items = 'all', $adjustments = 'all' ) {
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
		$validator = new Refund_Validator( $order, $order_items, $adjustments );
		$validator->validate_and_calculate_totals();
	} catch( \EDD\Utils\Exceptions\Invalid_Argument $e ) {
		return new WP_Error( 'refund_validation_error', __( 'Invalid argument. Please check your amounts and try again.', 'easy-digital-downloads' ) );
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

	// If we have tax, but no tax rate, manually save the percentage.
	$tax_rate_meta = edd_get_order_meta( $order_id, 'tax_rate', true );
	if ( $tax_rate_meta ) {
		edd_update_order_meta( $refund_id, 'tax_rate', $tax_rate_meta );
	}

	/** Insert order items ****************************************************/

	// Maintain a mapping of old order item IDs => new for easier lookup when we do fees.
	$order_item_id_map = array();
	foreach ( $validator->get_refunded_order_items() as $order_item ) {
		$order_item['order_id'] = $refund_id;

		$new_item_id = edd_add_order_item( $order_item );

		if ( ! empty( $order_item['parent'] ) ) {
			$order_item_id_map[ $order_item['parent'] ] = $new_item_id;
		}

		// Update the status on the original order item.
		if ( ! empty( $order_item['parent'] ) && ! empty( $order_item['original_item_status'] ) ) {
			edd_update_order_item( $order_item['parent'], array( 'status' => $order_item['original_item_status'] ) );
		}
	}

	/** Insert order adjustments **********************************************/

	foreach ( $validator->get_refunded_adjustments() as $adjustment ) {
		if ( ! empty( $adjustment['object_type'] ) && 'order' === $adjustment['object_type'] ) {
			$adjustment['object_id'] = $refund_id;
		} elseif ( ! empty( $adjustment['object_type'] ) && 'order_item' === $adjustment['object_type'] ) {
			/*
			 * At this point, `object_id` references an order item which is attached to the
			 * original order record. We need to try to convert this to a _refund_ order item
			 * instead.
			 *
			 * If we can't (such as, if the order item was never refunded), we'll have to
			 * convert the adjustment to be an `order` object type instead. That's because we
			 * _have_ to reference a refund object of some kind.
			 */
			$order_item_match_found = false;
			if ( ! empty( $adjustment['object_id'] ) && ! empty( $order_item_id_map[ $adjustment['object_id'] ] ) ) {
				// We don't need to convert to an `order` adjustment if we are also refunding the original order item.
				$adjustment['object_id'] = $order_item_id_map[ $adjustment['object_id'] ];
				$order_item_match_found  = true;
			}

			if ( ! $order_item_match_found ) {
				$adjustment['object_type'] = 'order';
				$adjustment['object_id']   = $refund_id;
			}
		}

		/*
		 * Note: Order item adjustments retain their `object_id` link to the *original* order item -- not the
		 * refunded order item. This isn't ideal, but it's because you could refund an order item fee without
		 * refunding the associated item, in which case there would be no refunded order item to reference.
		 * So we link back to the *original* order item in all cases to be consistent.
		 */

		edd_add_order_adjustment( $adjustment );
	}

	// Update order status to `refunded` once refund is complete and if all items are marked as refunded.
	$all_refunded    = true;
	$remaining_items = edd_count_order_items(
		array(
			'order_id'       => $order_id,
			'status__not_in' => array( 'refunded' ),
		)
	);
	if ( edd_get_order_total( $order_id ) > 0 || $remaining_items > 0 ) {
		$all_refunded = false;
	}

	$order_status = true === $all_refunded
		? 'refunded'
		: 'partially_refunded';

	edd_update_order( $order_id, array( 'status' => $order_status ) );

	edd_update_order( $refund_id, array( 'date_completed' => date( 'Y-m-d H:i:s' ) ) );
	/**
	 * Fires when an order has been refunded.
	 * This hook will trigger the legacy `edd_pre_refund_payment` and `edd_post_refund_payment`
	 * hooks for the time being, but any code using either of those should be updated.
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

	$total = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(total) FROM {$wpdb->edd_orders} WHERE id = %d OR ( type = 'refund' AND parent = %d )",
			$order_id,
			$order_id
		)
	);

	return is_null( $total ) ? 0.00 : floatval( $total );
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
