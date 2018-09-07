<?php
/**
 * Order Transaction Functions
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
 * Add an order transaction.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created order transaction, false on error.
 */
function edd_add_order_transaction( $data ) {

	// An object ID and object type must be supplied for every transaction inserted.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object.
	$transactions = new EDD\Database\Queries\Order_Transaction();

	return $transactions->add_item( $data );
}

/**
 * Delete an order transaction.
 *
 * @since 3.0
 *
 * @param int $order_transaction_id Order transaction ID.
 * @return int
 */
function edd_delete_order_transaction( $order_transaction_id = 0 ) {
	$transactions = new EDD\Database\Queries\Order_Transaction();

	return $transactions->delete_item( $order_transaction_id );
}

/**
 * Update an order address.
 *
 * @since 3.0
 *
 * @param int   $order_transaction_id Order transaction ID.
 * @param array $data                 Updated order transaction data.
 *
 * @return bool Whether or not the API request order was updated.
 */
function edd_update_order_transaction( $order_transaction_id = 0, $data = array() ) {
	$transactions = new EDD\Database\Queries\Order_Transaction();

	return $transactions->update_item( $order_transaction_id, $data );
}

/**
 * Get an order address by ID.
 *
 * @since 3.0
 *
 * @param int $order_transaction_id Order transaction ID.
 * @return object
 */
function edd_get_order_transaction( $order_transaction_id = 0 ) {
	return edd_get_order_transaction_by( 'id', $order_transaction_id );
}

/**
 * Get an order transaction by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Orders\Order_Transaction|false Object if successful, false otherwise.
 */
function edd_get_order_transaction_by( $field = '', $value = '' ) {
	$transactions = new EDD\Database\Queries\Order_Transaction();

	// Return order transaction.
	return $transactions->get_item_by( $field, $value );
}

/**
 * Query for order addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return \EDD\Orders\Order_Transaction[]
 */
function edd_get_order_transactions( $args = array() ) {

	// Parse args.
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object.
	$transactions = new EDD\Database\Queries\Order_Transaction();

	// Return order transactions.
	return $transactions->query( $r );
}

/**
 * Count order transactions.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_order_transactions( $args = array() ) {

	// Parse args.
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s).
	$transactions = new EDD\Database\Queries\Order_Transaction( $r );

	// Return count(s).
	return absint( $transactions->found_items );
}

/**
 * Retrieve order ID based on the transaction ID.
 *
 * @since 3.0
 *
 * @param string $transaction_id Transaction ID.
 * @return int $order_id Order ID.
 */
function edd_get_order_id_from_transaction_id( $transaction_id = '' ) {

	// Default return value
	$retval = 0;

	// Bail if no transaction ID passed.
	if ( ! empty( $transaction_id ) ) {

		// Look for a transaction by gateway transaction ID
		$transaction = edd_get_order_transaction_by( 'transaction_id', $transaction_id );

		// Return object ID if found
		if ( ! empty( $transaction->object_id ) ) {
			$retval = $transaction->object_id;
		}
	}

	// Return
	return absint( $retval );
}
