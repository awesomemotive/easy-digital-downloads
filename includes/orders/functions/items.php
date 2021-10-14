<?php
/**
 * Order Item Functions.
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
 * @param array $data {
 *     Array of order item data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int      $parent        Parent ID. Only used when creating refunds to link
 *                                   a refund order item to the original order item.
 *                                   Default 0.
 *     @type int      $order_id      Order ID. Default 0.
 *     @type int      $product_id    Product ID of the order item purchased. ID
 *                                   refers to the download in the `wp_posts` table.
 *                                   Default 0.
 *     @type string   $product_name  Name of the order item. Default empty.
 *     @type int|null $price_id      ID of the price option purchased. Default null (no price ID).
 *     @type int      $cart_index    Position of the order item in the cart.
 *                                   Default 0.
 *     @type string   $type          Order item type. Default `download`.
 *     @type string   $status        Status of the order item. Default `pending`.
 *     @type int      $quantity      Quantity purchased of the order item. Default 0.
 *     @type float    $amount        Amount for the order item. Default 0.
 *     @type float    $subtotal      Subtotal of the order item. Default 0.
 *     @type float    $discount      Discount applied to the order item. Default 0.
 *     @type float    $tax           Tax applied to the order item. Default 0.
 *     @type float    $total         Item total. Default 0.
 *     @type string   $date_created  Optional. Automatically calculated on add/edit.
 *                                   The date & time the order item was inserted.
 *                                   Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string   $date_modified Optional. Automatically calculated on add/edit.
 *                                   The date & time the order item was last modified.
 *                                   Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created order, false on error.
 */
function edd_add_order_item( $data = array() ) {

	// An order ID and product ID must be supplied for every order that is
	// inserted into the database.
	if ( empty( $data['order_id'] ) || empty( $data['product_id'] ) ) {
		return false;
	}

	// Instantiate a query object
	$order_items = new EDD\Database\Queries\Order_Item();

	return $order_items->add_item( $data );
}

/**
 * Delete an order item.
 *
 * @since 3.0
 *
 * @param int $order_item_id Order item ID.
 * @return int|false `1` if the item was deleted successfully, false on error.
 */
function edd_delete_order_item( $order_item_id = 0 ) {
	$order_items = new EDD\Database\Queries\Order_Item();

	return $order_items->delete_item( $order_item_id );
}

/**
 * Update an order item.
 *
 * @since 3.0
 *
 * @param int   $order_item_id Order item ID.
 * @param array $data {
 *     Array of order item data. Default empty.
 *
 *     @type int    $order_id       Order ID. Default 0.
 *     @type int    $product_id     Product ID of the order item purchased. ID
 *                                  refers to the download in the `wp_posts` table.
 *                                  Default 0.
 *     @type string $product_name   Name of the order item. Default empty.
 *     @type int    $price_id       ID of the price option purchased. Default 0.
 *     @type int    $cart_index     Position of the order item in the cart.
 *                                  Default 0.
 *     @type string $type           Order item type. Default `download`.
 *     @type string $status         Status of the order item. Default `inherit`.
 *     @type int    $quantity       Quantity purchased of the order item. Default 0.
 *     @type float  $amount         Amount for the order item. Default 0.
 *     @type float  $subtotal       Subtotal of the order item. Default 0.
 *     @type float  $discount       Discount applied to the order item. Default 0.
 *     @type float  $tax            Tax applied to the order item. Default 0.
 *     @type float  $total          Item total. Default 0.
 *     @type string $date_created   Optional. Automatically calculated on add/edit.
 *                                  The date & time the order item was inserted.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified  Optional. Automatically calculated on add/edit.
 *                                  The date & time the order item was last modified.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_order_item( $order_item_id = 0, $data = array() ) {
	$order_items = new EDD\Database\Queries\Order_Item();

	return $order_items->update_item( $order_item_id, $data );
}

/**
 * Get an order item by ID.
 *
 * @since 3.0
 *
 * @param int $order_item_id Order item ID.
 * @return EDD\Orders\Order_Item|false Order_Item object if successful, false
 *                                     otherwise.
 */
function edd_get_order_item( $order_item_id = 0 ) {
	$order_items = new EDD\Database\Queries\Order_Item();

	// Return order item
	return $order_items->get_item( $order_item_id );
}

/**
 * Get an order item by field and value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD\Orders\Order_Item|false Order_Item object if successful, false
 *                                     otherwise.
 */
function edd_get_order_item_by( $field = '', $value = '' ) {
	$order_items = new EDD\Database\Queries\Order_Item();

	// Return order item
	return $order_items->get_item_by( $field, $value );
}

/**
 * Query for order items.
 *
 * @see \EDD\Database\Queries\Order_Item::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Item` for
 *                    accepted arguments.
 * @return \EDD\Orders\Order_Item[] Array of `Order_Item` objects.
 */
function edd_get_order_items( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object
	$order_items = new EDD\Database\Queries\Order_Item();

	// Return items
	return $order_items->query( $r );
}

/**
 * Count order items.
 *
 * @see \EDD\Database\Queries\Order_Item::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Item` for
 *                    accepted arguments.
 * @return int Number of order items returned based on query arguments passed.
 */
function edd_count_order_items( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s)
	$order_items = new EDD\Database\Queries\Order_Item( $r );

	// Return count(s)
	return absint( $order_items->found_items );
}

/**
 * Query for and return array of order item counts, keyed by status.
 *
 * @see \EDD\Database\Queries\Order_Item::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Order_Item` for
 *                    accepted arguments.
 * @return array Order items keyed by status.
 */
function edd_get_order_item_counts( $args = array() ) {

	// Parse args
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
