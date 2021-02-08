<?php
/**
 * Order Transaction Functions.
 *
 * @package     EDD
 * @subpackage  Orders\Transactions
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
 * @param array $data {
 *     Array of order transaction data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $object_id      Object ID that the transaction refers to. This
 *                                  would be an ID that corresponds to the object
 *                                  type specified. E.g. an object ID of 25 with object
 *                                  type of `order` refers to order 25 in the
 *                                  `edd_orders` table. Default 0.
 *     @type string $object_type    Object type that the transaction refers to.
 *                                  Default empty.
 *     @type string $transaction_id Transaction ID from the payment gateway.
 *                                  Default empty.
 *     @type string $gateway        Payment gateway used for the order. Default
 *                                  empty.
 *     @type string $status         Status of the transaction. Default `pending`.
 *     @type float  $total          Total amount processed in the transaction.
 *                                  Default 0.
 *     @type string $date_created   Optional. Automatically calculated on add/edit.
 *                                  The date & time the transaction was inserted.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified  Optional. Automatically calculated on add/edit.
 *                                  The date & time the transaction was last modified.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created order transaction, false on error.
 */
function edd_add_order_transaction( $data ) {

	// An object ID and object type must be supplied for every transaction inserted.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object.
	$order_transactions = new EDD\Database\Queries\Order_Transaction();

	return $order_transactions->add_item( $data );
}

/**
 * Delete an order transaction.
 *
 * @since 3.0
 *
 * @param int $order_transaction_id Order transaction ID.
 * @return int|false `1` if the transaction was deleted successfully, false on error.
 */
function edd_delete_order_transaction( $order_transaction_id = 0 ) {
	$order_transactions = new EDD\Database\Queries\Order_Transaction();

	return $order_transactions->delete_item( $order_transaction_id );
}

/**
 * Update an order address.
 *
 * @since 3.0
 *
 * @param int   $order_transaction_id Order transaction ID.
 * @param array $data {
 *     Array of order transaction data. Default empty.
 *
 *     @type int    $object_id      Object ID that the transaction refers to. This
 *                                  would be an ID that corresponds to the object
 *                                  type specified. E.g. an object ID of 25 with object
 *                                  type of `order` refers to order 25 in the
 *                                  `edd_orders` table. Default 0.
 *     @type string $object_type    Object type that the transaction refers to.
 *                                  Default empty.
 *     @type string $transaction_id Transaction ID from the payment gateway.
 *                                  Default empty.
 *     @type string $gateway        Payment gateway used for the order. Default
 *                                  empty.
 *     @type string $status         Status of the transaction. Default `pending`.
 *     @type float  $total          Total amount processed in the transaction.
 *                                  Default 0.
 *     @type string $date_created   Optional. Automatically calculated on add/edit.
 *                                  The date & time the transaction was inserted.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified  Optional. Automatically calculated on add/edit.
 *                                  The date & time the transaction was last modified.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_order_transaction( $order_transaction_id = 0, $data = array() ) {
	$order_transactions = new EDD\Database\Queries\Order_Transaction();

	return $order_transactions->update_item( $order_transaction_id, $data );
}

/**
 * Get an order transaction by ID.
 *
 * @since 3.0
 *
 * @param int $order_transaction_id Order transaction ID.
 * @return EDD\Orders\Order_Transaction|false Order_Transaction object if successful,
 *                                            false otherwise.
 */
function edd_get_order_transaction( $order_transaction_id = 0 ) {
	$order_transactions = new EDD\Database\Queries\Order_Transaction();

	// Return order transaction.
	return $order_transactions->get_item( $order_transaction_id );
}

/**
 * Get an order transaction by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD\Orders\Order_Transaction|false Order_Transaction object if successful,
 *                                            false otherwise.
 */
function edd_get_order_transaction_by( $field = '', $value = '' ) {
	$order_transactions = new EDD\Database\Queries\Order_Transaction();

	// Return order transaction.
	return $order_transactions->get_item_by( $field, $value );
}

/**
 * Query for order transactions.
 *
 * @see \EDD\Database\Queries\Order_Transaction::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Transaction` for
 *                    accepted arguments.
 * @return \EDD\Orders\Order_Transaction[] Array of `Order_Transaction` objects.
 */
function edd_get_order_transactions( $args = array() ) {

	// Parse args.
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object.
	$order_transactions = new EDD\Database\Queries\Order_Transaction();

	// Return order transactions.
	return $order_transactions->query( $r );
}

/**
 * Count order transactions.
 *
 * @see \EDD\Database\Queries\Order_Transaction::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Transaction` for
 *                    accepted arguments.
 * @return int Number of order transactions returned based on query arguments
 *             passed.
 */
function edd_count_order_transactions( $args = array() ) {

	// Parse args.
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s).
	$order_transactions = new EDD\Database\Queries\Order_Transaction( $r );

	// Return count(s).
	return absint( $order_transactions->found_items );
}

/**
 * Retrieve order ID based on the transaction ID.
 *
 * @since 3.0
 *
 * @param string $transaction_id Transaction ID. Default empty.
 * @return int $order_id Order ID. Default 0.
 */
function edd_get_order_id_from_transaction_id( $transaction_id = '' ) {

	// Default return value.
	$retval = 0;

	// Continue if transaction ID was passed.
	if ( ! empty( $transaction_id ) ) {

		// Look for a transaction by gateway transaction ID.
		$transaction = edd_get_order_transaction_by( 'transaction_id', $transaction_id );

		// Return object ID if found.
		if ( ! empty( $transaction->object_id ) ) {
			$retval = $transaction->object_id;
		}
	}

	// Return.
	return absint( $retval );
}
