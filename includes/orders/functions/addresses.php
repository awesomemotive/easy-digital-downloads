<?php
/**
 * Order Address Functions
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
 * Add an order address.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order address, false on error.
 */
function edd_add_order_address( $data ) {

	// An order ID must be supplied for every address inserted.
	if ( empty( $data['order_id'] ) ) {
		return false;
	}

	// Instantiate a query object
	$order_addresses = new EDD\Database\Queries\Order_Address();

	return $order_addresses->add_item( $data );
}

/**
 * Delete an order address.
 *
 * @since 3.0
 *
 * @param int $order_address_id Order address ID.
 * @return int
 */
function edd_delete_order_address( $order_address_id = 0 ) {
	$order_addresses = new EDD\Database\Queries\Order_Address();

	return $order_addresses->delete_item( $order_address_id );
}

/**
 * Update an order address.
 *
 * @since 3.0
 *
 * @param int   $order_address_id Order address ID.
 * @param array $data             Updated order address data.
 * @return bool Whether or not the API request order was updated.
 */
function edd_update_order_address( $order_address_id = 0, $data = array() ) {
	$order_addresses = new EDD\Database\Queries\Order_Address();

	return $order_addresses->update_item( $order_address_id, $data );
}

/**
 * Get an order address by ID.
 *
 * @since 3.0
 *
 * @param int $order_address_id Order adjustment ID.
 * @return object
 */
function edd_get_order_address( $order_address_id = 0 ) {
	return edd_get_order_address_by( 'id', $order_address_id );
}

/**
 * Get an order address by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Orders\Order_Address|false Object if successful, false otherwise.
 */
function edd_get_order_address_by( $field = '', $value = '' ) {
	$order_addresses = new EDD\Database\Queries\Order_Address();

	// Return order address
	return $order_addresses->get_item_by( $field, $value );
}

/**
 * Query for order addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return \EDD\Orders\Order_Address[]
 */
function edd_get_order_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object
	$order_addresses = new EDD\Database\Queries\Order_Address();

	// Return orders
	return $order_addresses->query( $r );
}

/**
 * Count order addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_order_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s)
	$order_addresses = new EDD\Database\Queries\Order_Address( $r );

	// Return count(s)
	return absint( $order_addresses->found_items );
}

