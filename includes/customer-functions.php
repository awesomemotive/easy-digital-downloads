<?php
/**
 * Customer Functions
 *
 * This file contains all of the first class functions for interacting with a
 * customer or it's related meta data.
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a customer to the database
 *
 * @since 3.0.0
 *
 * @param array $data
 *
 * @return mixed False on failure. ID of new EDD_Customer object on success.
 */
function edd_add_customer( $data = array() ) {

	// An email must be given for every customer that is created.
	if ( ! isset( $data['email'] ) || empty( $data['email'] ) ) {
		return false;
	}

	// Instantiate a query object
	$customers = new EDD\Database\Queries\Customer();

	return $customers->add_item( $data );
}

/**
 * Delete a customer from the database
 *
 * @since 3.0.0
 *
 * @param int ID of customer to delete
 *
 * @return mixed False on failure. ID of new EDD_Customer object on success.
 */
function edd_delete_customer( $customer_id = 0 ) {
	$customers = new EDD\Database\Queries\Customer();

	return $customers->delete_item( $customer_id );
}

/**
 * Update a customer.
 *
 * @since 3.0.0
 * @param int $customer_id Customer ID.
 * @param array $data
 *
 * @return int
 */
function edd_update_customer( $customer_id = 0, $data = array() ) {
	$customers = new EDD\Database\Queries\Customer();

	return $customers->update_item( $customer_id, $data );
}

/**
 * Get a customer item by ID.
 *
 * @since 3.0.0
 * @param int $customer_id Customer ID.
 * @param array $data
 *
 * @return int
 */
function edd_get_customer( $customer_id = 0 ) {
	return edd_get_customer_by( 'id', $customer_id );
}

/**
 * Get a customer item by a specific field's value.
 *
 * @since 3.0.0
 * @param array $args
 *
 * @return object
 */
function edd_get_customer_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$customers = new EDD\Database\Queries\Customer();

	// Get an item
	return $customers->get_item_by( $field, $value );
}

/**
 * Query for customers
 *
 * @since 3.0.0
 * @param array $args
 *
 * @return array
 */
function edd_get_customers( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$customers = new EDD\Database\Queries\Customer();

	// Return customers
	return $customers->query( $r );
}

/**
 * Get total number of customers
 *
 * @since 3.0.0
 *
 * @param array $args Arguments.
 * @return int
 */
function edd_count_customers( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$customers = new EDD\Database\Queries\Customer( $r );

	// Return count(s)
	return absint( $customers->found_items );
}

/**
 * Return the role used to edit customers
 *
 * @since 3.0.0
 *
 * @return string
 */
function edd_get_edit_customers_role() {
	return apply_filters( 'edd_edit_customers_role', 'edit_shop_payments' );
}

/** Meta **********************************************************************/

/**
 * Add meta data field to a customer.
 *
 * @since 3.0.0
 *
 * @param int     $customer_id  Customer ID.
 * @param string  $meta_key     Meta data name.
 * @param mixed   $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique       Optional. Whether the same key should not be added.
 *                              Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_customer_meta( $customer_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_customer', $customer_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from a customer.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate meta data with the same key. It also
 * allows removing all meta data matching key, if needed.
 *
 * @since 3.0.0
 *
 * @param int     $customer_id  Customer ID.
 * @param string  $meta_key     Meta data name.
 * @param mixed   $meta_value   Optional. Meta data value. Must be serializable if
 *                              non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_customer_meta( $customer_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_customer', $customer_id, $meta_key, $meta_value );
}

/**
 * Retrieve customer meta field for a customer.
 *
 * @since 3.0.0
 *
 * @param int     $customer_id  Customer ID.
 * @param string  $key          Optional. The meta key to retrieve. By default, returns
 *                              data for all keys. Default empty.
 * @param bool    $single       Optional, default is false.
 *                              If true, return only the first value of the specified meta_key.
 *                              This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function edd_get_customer_meta( $customer_id, $key = '', $single = false ) {
	return get_metadata( 'edd_customer', $customer_id, $key, $single );
}

/**
 * Update customer meta field based on customer ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and customer ID.
 *
 * If the meta field for the customer does not exist, it will be added.
 *
 * @since 3.0.0
 *
 * @param int     $customer_id  Customer ID.
 * @param string  $meta_key     Meta data key.
 * @param mixed   $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value   Optional. Previous value to check before removing.
 *                              Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function edd_update_customer_meta( $customer_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_customer', $customer_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from customer meta matching meta key.
 *
 * @since 3.0.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the customer meta key was deleted from the database.
 */
function edd_delete_customer_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_customer', null, $meta_key, '', true );
}

/** Back Compat ***************************************************************/

/**
 * Backwards compatibility layer for old `customer_id` column.
 *
 * @since 3.0.0
 *
 * @param string  $query   SQL query.
 * @return string $request SQL query with column replaced.
 */
function _edd_filter_customer_meta_id_column( $query = '' ) {

	// Get customer meta
	$customer_meta = edd_get_component_interface( 'customer', 'meta' );

	// Bail if no customer meta
	if ( empty( $customer_meta ) ) {
		return $query;
	}

	// Bail if not a customer meta query
	if ( ! strpos( $query, $customer_meta->table_name ) ) {
		return $query;
	}

	// Bail if not a customer ID query
	if ( ! strpos( $query, 'customer_id' ) ) {
		return $query;
	}

	// Replace, but also avoid double replacements
	$replaced = str_replace( 'customer_id',         'edd_customer_id', $query );
	$replaced = str_replace( 'edd_edd_customer_id', 'edd_customer_id', $query );

	// Return
	return $replaced;
}
add_filter( 'query', '_edd_filter_customer_meta_id_column', 10 );
