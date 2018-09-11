<?php
/**
 * Order Item Functions
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
 * Add an order item.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order, false on error.
 */
function edd_add_order_item( $data = array() ) {

	// An order ID and product ID must be supplied for every order that is
	// inserted into the database.
	if ( empty( $data['order_id'] ) || empty( $data['product_id'] ) ) {
		return false;
	}

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Item();

	return $orders->add_item( $data );
}

/**
 * Delete an order item.
 *
 * @since 3.0
 *
 * @param int $order_item_id Order item ID.
 * @return int
 */
function edd_delete_order_item( $order_item_id = 0 ) {
	$orders = new EDD\Database\Queries\Order_Item();

	return $orders->delete_item( $order_item_id );
}

/**
 * Update an order item.
 *
 * @since 3.0
 *
 * @param int   $order_item_id Order item ID.
 * @param array $data          Updated file download order data.
 * @return bool Whether or not the file download order was updated.
 */
function edd_update_order_item( $order_item_id = 0, $data = array() ) {
	$orders = new EDD\Database\Queries\Order_Item();

	return $orders->update_item( $order_item_id, $data );
}

/**
 * Get an order item by ID.
 *
 * @since 3.0
 *
 * @param int $order_item_id Order item ID.
 * @return EDD\Orders\Order_Item
 */
function edd_get_order_item( $order_item_id = 0 ) {
	return edd_get_order_item_by( 'id', $order_item_id );
}

/**
 * Get an order item by field and value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_order_item_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Item();

	// Return item
	return $orders->get_item_by( $field, $value );
}

/**
 * Query for order items.
 *
 * @since 3.0
 *
 * @param array $args
 * @return \EDD\Orders\Order_Item[]
 */
function edd_get_order_items( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object
	$orders = new EDD\Database\Queries\Order_Item();

	// Return items
	return $orders->query( $r );
}

/**
 * Count order items.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_order_items( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s)
	$orders = new EDD\Database\Queries\Order_Item( $r );

	// Return count(s)
	return absint( $orders->found_items );
}

/**
 * Query for and return array of order item counts, keyed by status.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_item_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'order_id' => 0,
		'count'    => true,
		'groupby'  => 'status',
	) );

	// Query for count
	$counts = new EDD\Database\Queries\Order_Item( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}
