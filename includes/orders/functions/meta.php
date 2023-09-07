<?php
/**
 * Order Meta Functions
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Orders ********************************************************************/

/**
 * Add meta data field to an order.
 *
 * @since 3.0
 *
 * @param int     $order_id   Order ID.
 * @param string  $meta_key   Meta data name.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique     Optional. Whether the same key should not be added. Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_order_meta( $order_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_order', $order_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from an order.
 *
 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
 * meta data with the same key. It also allows removing all meta data matching key, if needed.
 *
 * @since 3.0
 *
 * @param int     $order_id   Order ID.
 * @param string  $meta_key   Meta data name.
 * @param mixed   $meta_value Optional. Meta data value. Must be serializable if non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_order_meta( $order_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_order', $order_id, $meta_key, $meta_value );
}

/**
 * Retrieve order meta field for an order.
 *
 * @since 3.0
 *
 * @param int     $order_id  Order ID.
 * @param string  $key       Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
 * @param bool    $single    Optional, default is false. If true, return only the first value of the specified meta_key.
 *                           This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_order_meta( $order_id, $key = '', $single = false ) {
	return get_metadata( 'edd_order', $order_id, $key, $single );
}

/**
 * Update order meta field based on order ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and order ID.
 *
 * If the meta field for the order does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int     $order_id   Order ID.
 * @param string  $meta_key   Meta data key.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_order_meta( $order_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_order', $order_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from order meta matching meta key.
 *
 * @since 3.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the order meta key was deleted from the database.
 */
function edd_delete_order_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_order', null, $meta_key, '', true );
}

/** Order Items ***************************************************************/

/**
 * Add meta data field to an order item.
 *
 * @since 3.0
 *
 * @param int     $order_item_id  Order ID.
 * @param string  $meta_key       Meta data name.
 * @param mixed   $meta_value     Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique         Optional. Whether the same key should not be added. Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_order_item_meta( $order_item_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_order_item', $order_item_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from an order item.
 *
 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
 * meta data with the same key. It also allows removing all meta data matching key, if needed.
 *
 * @since 3.0
 *
 * @param int     $order_item_id  Order ID.
 * @param string  $meta_key       Meta data name.
 * @param mixed   $meta_value     Optional. Meta data value. Must be serializable if non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_order_item_meta( $order_item_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_order_item', $order_item_id, $meta_key, $meta_value );
}

/**
 * Retrieve order_item meta field for an order item.
 *
 * @since 3.0
 *
 * @param int     $order_item_id  Order ID.
 * @param string  $key            Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
 * @param bool    $single         Optional, default is false. If true, return only the first value of the specified meta_key.
 *                                This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_order_item_meta( $order_item_id, $key = '', $single = false ) {
	return get_metadata( 'edd_order_item', $order_item_id, $key, $single );
}

/**
 * Update order_item meta field based on order_item ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and order_item ID.
 *
 * If the meta field for the order_item does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int     $order_item_id  Order Item ID.
 * @param string  $meta_key       Meta data key.
 * @param mixed   $meta_value     Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value     Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_order_item_meta( $order_item_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_order_item', $order_item_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from order_item meta matching meta key.
 *
 * @since 3.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the order_item meta key was deleted from the database.
 */
function edd_delete_order_item_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_order_item', null, $meta_key, '', true );
}

/** Order Adjustments *********************************************************/

/**
 * Add meta data field to an order item.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id  Order ID.
 * @param string  $meta_key       Meta data name.
 * @param mixed   $meta_value     Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique         Optional. Whether the same key should not be added. Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_order_adjustment_meta( $adjustment_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_order_adjustment', $adjustment_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from an order item.
 *
 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
 * meta data with the same key. It also allows removing all meta data matching key, if needed.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id  Order ID.
 * @param string  $meta_key       Meta data name.
 * @param mixed   $meta_value     Optional. Meta data value. Must be serializable if non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_order_adjustment_meta( $adjustment_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_order_adjustment', $adjustment_id, $meta_key, $meta_value );
}

/**
 * Retrieve order_item meta field for an order item.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id  Order ID.
 * @param string  $key            Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
 * @param bool    $single         Optional, default is false. If true, return only the first value of the specified meta_key.
 *                                This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_order_adjustment_meta( $adjustment_id, $key = '', $single = false ) {
	return get_metadata( 'edd_order_adjustment', $adjustment_id, $key, $single );
}

/**
 * Update order_item meta field based on order_item ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and order_item ID.
 *
 * If the meta field for the order_item does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id  Order Item ID.
 * @param string  $meta_key       Meta data key.
 * @param mixed   $meta_value     Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value     Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_order_adjustment_meta( $adjustment_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_order_adjustment', $adjustment_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from order_item meta matching meta key.
 *
 * @since 3.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the order_item meta key was deleted from the database.
 */
function edd_delete_order_adjustment_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_order_adjustment', null, $meta_key, '', true );
}

/**
 * Add extra metadata to a fee.
 *
 * @since 3.2.0
 *
 * @param int   $adjustment_id The adjustment ID.
 * @param array $fee           The fee data.
 * @return void
 */
function edd_add_extra_fee_order_adjustment_meta( $adjustment_id, $fee ) {
	$adjustment_properties = array(
		'id',
		'label',
		'amount',
		'no_tax',
		'type',
		'download_id',
		'price_id',
	);
	$adjustment_meta       = array_diff_key( $fee, array_flip( $adjustment_properties ) );
	if ( empty( $adjustment_meta ) ) {
		return;
	}
	foreach ( $adjustment_meta as $meta_key => $meta_value ) {
		$meta_key = sanitize_key( $meta_key );

		edd_add_order_adjustment_meta( $adjustment_id, $meta_key, $meta_value );
	}
}
