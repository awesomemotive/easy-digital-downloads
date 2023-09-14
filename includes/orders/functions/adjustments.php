<?php
/**
 * Order Adjustment Functions.
 *
 * @package     EDD
 * @subpackage  Orders\Adjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add an order adjustment.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of order adjustment data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $parent        Parent ID. Only used when creating refunds to link
 *                                 a refund order adjustment to the original order adjustment.
 *                                 Default 0.
 *     @type int    $object_id     Object ID that the adjustment refers to. This would
 *                                 be an ID that corresponds to the object type
 *                                 specified. E.g. an object ID of 25 with object
 *                                 type of `order` refers to order 25 in the
 *                                 `edd_orders` table. Default empty.
 *     @type string $object_type   Object type that the adjustment refers to.
 *                                 E.g. `order` or `order_item`. Default empty.
 *     @type int    $type_id       Object ID of the adjustment type. E.g. a type
 *                                 ID of 25 with type of `discount` refers to
 *                                 discount 25 in te `edd_discounts` table.
 *                                 Default empty.
 *     @type string $type          Object type of the adjustment type. E.g. `discount`.
 *                                 Default empty.
 *     @type string $description   Description. Default empty.
 *     @type float  $subtotal      Subtotal. Default 0.
 *     @type float  $tax           Tax applicable. Default 0.
 *     @type float  $total         Adjustment total. Default 0.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the adjustment was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the adjustment was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created order adjustment, false on error.
 */
function edd_add_order_adjustment( $data ) {

	// An object ID and object ID must be supplied for every order that is
	// inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment();

	return $order_adjustments->add_item( $data );
}

/**
 * Delete an order adjustment.
 *
 * @since 3.0
 *
 * @param int $order_adjustment_id Order adjustment ID.
 * @return int|false `1` if the adjustment was deleted successfully, false on error.
 */
function edd_delete_order_adjustment( $order_adjustment_id = 0 ) {
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment();

	return $order_adjustments->delete_item( $order_adjustment_id );
}

/**
 * Update an order adjustment.
 *
 * @since 3.0
 *
 * @param int   $order_adjustment_id Order adjustment ID.
 * @param array $data {
 *     Array of order adjustment data. Default empty.
 *
 *     @type int    $object_id     Object ID that the adjustment refers to. This would
 *                                 be an ID that corresponds to the object type
 *                                 specified. E.g. an object ID of 25 with object
 *                                 type of `order` refers to order 25 in the
 *                                 `edd_orders` table. Default empty.
 *     @type string $object_type   Object type that the adjustment refers to.
 *                                 E.g. `order` or `order_item`. Default empty.
 *     @type int    $type_id       Object ID of the adjustment type. E.g. a type
 *                                 ID of 25 with type of `discount` refers to
 *                                 discount 25 in te `edd_discounts` table.
 *                                 Default empty.
 *     @type string $type          Object type of the adjustment type. E.g. `discount`.
 *                                 Default empty.
 *     @type string $description   Description. Default empty.
 *     @type float  $subtotal      Subtotal. Default 0.
 *     @type float  $tax           Tax applicable. Default 0.
 *     @type float  $total         Adjustment total. Default 0.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the adjustment was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the adjustment was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_order_adjustment( $order_adjustment_id = 0, $data = array() ) {
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment();

	return $order_adjustments->update_item( $order_adjustment_id, $data );
}

/**
 * Get an order adjustment by ID.
 *
 * @since 3.0
 *
 * @param int $order_adjustment_id Order adjustment ID.
 * @return EDD\Orders\Order_Adjustment|false Order_Adjustment object if successful,
 *                                           false otherwise.
 */
function edd_get_order_adjustment( $order_adjustment_id = 0 ) {
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment();

	// Return order adjustment
	return $order_adjustments->get_item( $order_adjustment_id );
}

/**
 * Get an order adjustment by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD\Orders\Order_Adjustment|false Order_Adjustment object if successful,
 *                                           false otherwise.
 */
function edd_get_order_adjustment_by( $field = '', $value = '' ) {
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment();

	// Return order adjustment
	return $order_adjustments->get_item_by( $field, $value );
}

/**
 * Query for order adjustments.
 *
 * @see \EDD\Database\Queries\Order_Adjustment::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Adjustment` for
 *                    accepted arguments.
 * @return \EDD\Orders\Order_Adjustment[] Array of `Order_Adjustment` objects.
 */
function edd_get_order_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment();

	// Return orders
	return $order_adjustments->query( $r );
}

/**
 * Count order adjustments.
 *
 * @see \EDD\Database\Queries\Order_Adjustment::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Adjustment` for
 *                    accepted arguments.
 * @return int Number of order adjustments returned based on query arguments passed.
 */
function edd_count_order_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s)
	$order_adjustments = new EDD\Database\Queries\Order_Adjustment( $r );

	// Return count(s)
	return absint( $order_adjustments->found_items );
}

/**
 * Query for and return array of order adjustment counts, keyed by status.
 *
 * @see \EDD\Database\Queries\Order_Adjustment::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Adjustment` for
 *                    accepted arguments.
 *
 * @return array Order adjustment counts keyed by status.
 */
function edd_get_order_adjustment_counts( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'object_id'   => 0,
		'object_type' => 'order',
		'count'       => true,
		'groupby'     => 'type',
	) );

	// Query for count
	$counts = new EDD\Database\Queries\Order_Adjustment( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}
