<?php
/**
 * Customer Functions
 *
 * This file contains all of the first class functions for interacting with a
 * customer or it's related meta data.
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a customer to the database
 *
 * @since 3.0
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
 * @since 3.0
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
 * Destroy a customer.
 *
 * Completely deletes a customer, and the addresses and email addresses with it.
 *
 * @since 3.0
 *
 * @param int $customer_id Customer ID.
 * @return int
 */
function edd_destroy_customer( $customer_id = 0 ) {

	// Get email addresses.
	$email_addresses = edd_get_customer_email_addresses(
		array(
			'customer_id'   => $customer_id,
			'no_found_rows' => true,
		)
	);

	// Destroy email addresses.
	if ( ! empty( $email_addresses ) ) {
		foreach ( $email_addresses as $email_address ) {
			edd_delete_customer_email_address( $email_address->id );
		}
	}

	// Get addresses.
	$addresses = edd_get_customer_addresses(
		array(
			'customer_id'   => $customer_id,
			'no_found_rows' => true,
		)
	);

	// Destroy addresses.
	if ( ! empty( $addresses ) ) {
		foreach ( $addresses as $address ) {
			edd_delete_customer_address( $address->id );
		}
	}

	// Delete the customer.
	return edd_delete_customer( $customer_id );
}

/**
 * Update a customer.
 *
 * @since 3.0
 * @param int   $customer_id Customer ID.
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
 * @since 3.0
 * @param int $customer_id Customer ID.
 *
 * @return EDD_Customer
 */
function edd_get_customer( $customer_id = 0 ) {
	return edd_get_customer_by( 'id', $customer_id );
}

/**
 * Get a customer item by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field
 * @param string $value
 *
 * @return EDD_Customer
 */
function edd_get_customer_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$customers = new EDD\Database\Queries\Customer();

	// Get an item
	return $customers->get_item_by( $field, $value );
}

/**
 * Get a field from a customer object.
 *
 * @since 3.0
 *
 * @param int    $customer_id
 * @param string $field
 *
 * @return mixed Null if customer does not exist. Value of Customer if exists
 */
function edd_get_customer_field( $customer_id = 0, $field = '' ) {
	$customer = edd_get_customer( $customer_id );

	// Check that field exists
	return isset( $customer->{$field} )
		? $customer->{$field}
		: null;
}

/**
 * Query for customers
 *
 * @since 3.0
 * @param array $args
 *
 * @return array
 */
function edd_get_customers( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

	// Instantiate a query object
	$customers = new EDD\Database\Queries\Customer();

	// Return customers
	return $customers->query( $r );
}

/**
 * Get total number of customers
 *
 * @since 3.0
 *
 * @param array $args Arguments.
 * @return int
 */
function edd_count_customers( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s)
	$customers = new EDD\Database\Queries\Customer( $r );

	// Return count(s)
	return absint( $customers->found_items );
}

/**
 * Query for and return array of customer counts, keyed by status
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_customer_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args(
		$args,
		array(
			'count'   => true,
			'groupby' => 'status',
		)
	);

	// Query for count
	$counts = new EDD\Database\Queries\Customer( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}

/**
 * Return the role used to edit customers
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_edit_customers_role() {
	return apply_filters( 'edd_edit_customers_role', 'edit_shop_payments' );
}

/**
 * Retrieve all of the IP addresses used by a customer.
 *
 * @since 3.0
 *
 * @param int $customer_id Customer ID.
 *
 * @return array Array of objects containing IP address.
 */
function edd_get_customer_ip_addresses( $customer_id = 0 ) {
	if ( empty( $customer_id ) ) {
		return array();
	}

	$customer = edd_get_customer( $customer_id );

	return $customer->get_ips();
}

/** Meta **********************************************************************/

/**
 * Add meta data field to a customer.
 *
 * @since 3.0
 *
 * @param int    $customer_id  Customer ID.
 * @param string $meta_key     Meta data name.
 * @param mixed  $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param bool   $unique       Optional. Whether the same key should not be added.
 *                             Default false.
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
 * @since 3.0
 *
 * @param int    $customer_id  Customer ID.
 * @param string $meta_key     Meta data name.
 * @param mixed  $meta_value   Optional. Meta data value. Must be serializable if
 *                             non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_customer_meta( $customer_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_customer', $customer_id, $meta_key, $meta_value );
}

/**
 * Retrieve customer meta field for a customer.
 *
 * @since 3.0
 *
 * @param int    $customer_id  Customer ID.
 * @param string $key          Optional. The meta key to retrieve. By default, returns
 *                             data for all keys. Default empty.
 * @param bool   $single       Optional, default is false.
 *                             If true, return only the first value of the specified meta_key.
 *                             This parameter has no effect if meta_key is not specified.
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
 * @since 3.0
 *
 * @param int    $customer_id  Customer ID.
 * @param string $meta_key     Meta data key.
 * @param mixed  $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param mixed  $prev_value   Optional. Previous value to check before removing.
 *                             Default empty.
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
 * @since 3.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the customer meta key was deleted from the database.
 */
function edd_delete_customer_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_customer', null, $meta_key, '', true );
}

/** Customer Addresses **********************************************************/

/**
 * Get a customer address by ID.
 *
 * @internal This method is named edd_fetch_customer_address as edd_get_customer_address
 *           exists for backwards compatibility purposes and returns an array instead of
 *           an object.
 *
 * @since 3.0
 *
 * @param int $customer_address_id Customer address ID.
 * @return EDD\Customers\Customer_Address Customer address object.
 */
function edd_fetch_customer_address( $customer_address_id = 0 ) {
	return edd_get_customer_address_by( 'id', $customer_address_id );
}

/**
 * Add a customer address.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created customer address, false on error.
 */
function edd_add_customer_address( $data ) {

	// An customer ID must be supplied for every address inserted.
	if ( empty( $data['customer_id'] ) ) {
		return false;
	}

	// Instantiate a query object
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	return $customer_addresses->add_item( $data );
}

/**
 * Delete a customer address.
 *
 * @since 3.0
 *
 * @param int $customer_address_id Order address ID.
 * @return int
 */
function edd_delete_customer_address( $customer_address_id = 0 ) {
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	return $customer_addresses->delete_item( $customer_address_id );
}

/**
 * Update a customer address.
 *
 * @since 3.0
 *
 * @param int   $customer_address_id Customer address ID.
 * @param array $data                Updated customer address data.
 * @return bool Whether or not the customer address was updated.
 */
function edd_update_customer_address( $customer_address_id = 0, $data = array() ) {
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	return $customer_addresses->update_item( $customer_address_id, $data );
}

// **
// * Get a customer address by ID.
// *
// * @since 3.0
// *
// * @param int $customer_address_id Order adjustment ID.
// * @return object
// */
// function edd_get_customer_address( $customer_address_id = 0 ) {
// return edd_get_customer_address_by( 'id', $customer_address_id );
// }

/**
 * Get a customer address by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Customers\Customer_Address|false Object if successful, false otherwise.
 */
function edd_get_customer_address_by( $field = '', $value = '' ) {
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	// Return order address
	return $customer_addresses->get_item_by( $field, $value );
}

/**
 * Query for customer addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return \EDD\Customers\Customer_Address[]
 */
function edd_get_customer_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

	// Instantiate a query object
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	// Return orders
	return $customer_addresses->query( $r );
}

/**
 * Count customer addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_customer_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s)
	$customer_addresses = new EDD\Database\Queries\Customer_Address( $r );

	// Return count(s)
	return absint( $customer_addresses->found_items );
}

/**
 * Maybe add a customer address. Used by `edd_build_order()` to maybe add
 * order addresses to the customer addresses table.
 *
 * @since 3.0
 *
 * @param int   $customer_id Customer ID.
 * @param array $args        Customer address to check.
 *
 * @return mixed False if address exists. Otherwise ID of newly added EDD\Customers\Customer_Address object.
 */
function edd_maybe_add_customer_address( $customer_id = 0, $args = array() ) {

	// Bail if nothing passed.
	if ( empty( $customer_id ) || empty( $args ) ) {
		return false;
	}

	$args['customer_id'] = $customer_id;

	$c = edd_count_customer_addresses( $args );

	// Add to the table if an address does not exist.
	if ( 0 === $c ) {
		$args['type'] = 'billing';
		return edd_add_customer_address( $args );
	}

	return false;
}

/**
 * Maybe update the customer's primary address. If the primary address is set,
 * update it or add a primary address.
 *
 * @since 3.0
 *
 * @param int   $customer_id Customer ID.
 * @param array $args        Customer address.
 *
 * @return mixed False if address exists. Otherwise ID of EDD\Customers\Customer_Address object.
 */
function edd_maybe_update_customer_primary_address( $customer_id = 0, $args = array() ) {

	// Bail if nothing passed.
	if ( empty( $customer_id ) || empty( $args ) ) {
		return false;
	}

	$address_ids = edd_get_customer_addresses(
		array(
			'fields'      => 'ids',
			'customer_id' => $customer_id,
			'type'        => 'primary',
			'number'      => 1,
		)
	);

	// Primary address exists, so update it.
	if ( ! empty( $address_ids ) ) {
		$address_id = $address_ids[0];

		edd_update_customer_address( $address_id, $args );

		// Add primary address.
	} else {
		$args['type'] = 'primary';

		$address_id = edd_add_customer_address( $args );
	}

	return $address_id;
}


/**
 * Query for and return array of customer address counts, keyed by status
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_customer_address_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args(
		$args,
		array(
			'count'   => true,
			'groupby' => 'status',
		)
	);

	// Query for count
	$counts = new EDD\Database\Queries\Customer_Address( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}

/** Customer Email Addresses *************************************************/

/**
 * Add a customer email address.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created customer email address, false on error.
 */
function edd_add_customer_email_address( $data ) {

	// An customer ID must be supplied for every address inserted.
	if ( empty( $data['customer_id'] ) ) {
		return false;
	}

	// Instantiate a query object.
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	return $customer_email_addresses->add_item( $data );
}

/**
 * Delete a customer email address.
 *
 * @since 3.0
 *
 * @param int $customer_email_address_id Customer email address ID.
 * @return int
 */
function edd_delete_customer_email_address( $customer_email_address_id = 0 ) {

	// Bail if a customer address ID is not passed.
	if ( empty( $customer_email_address_id ) ) {
		return false;
	}

	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	return $customer_email_addresses->delete_item( $customer_email_address_id );
}

/**
 * Update a customer email address.
 *
 * @since 3.0
 *
 * @param int   $customer_email_address_id Customer email address ID.
 * @param array $data                      Updated customer email address data.
 *
 * @return bool Whether or not the customer email address was updated.
 */
function edd_update_customer_email_address( $customer_email_address_id = 0, $data = array() ) {

	// Bail if a customer address ID is not passed.
	if ( empty( $customer_email_address_id ) ) {
		return false;
	}

	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	return $customer_email_addresses->update_item( $customer_email_address_id, $data );
}

/**
 * Get a customer email address by ID.
 *
 * @since 3.0
 *
 * @param int $customer_email_address_id Customer email address ID.
 * @return \EDD\Customers\Customer_Email_Address|false Object if successful, false otherwise.
 */
function edd_get_customer_email_address( $customer_email_address_id = 0 ) {

	// Bail if a customer address ID is not passed.
	if ( empty( $customer_email_address_id ) ) {
		return false;
	}

	return edd_get_customer_email_address_by( 'id', $customer_email_address_id );
}

/**
 * Get a customer email address by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Customers\Customer_Email_Address|false Object if successful, false otherwise.
 */
function edd_get_customer_email_address_by( $field = '', $value = '' ) {
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	// Return customer email address
	return $customer_email_addresses->get_item_by( $field, $value );
}

/**
 * Query for customer email addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return \EDD\Customers\Customer_Email_Address[]
 */
function edd_get_customer_email_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

	// Instantiate a query object
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	// Return orders
	return $customer_email_addresses->query( $r );
}

/**
 * Count customer addresses.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_customer_email_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s)
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address( $r );

	// Return count(s)
	return absint( $customer_email_addresses->found_items );
}

/**
 * Query for and return array of customer email counts, keyed by status
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_customer_email_address_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args(
		$args,
		array(
			'count'   => true,
			'groupby' => 'status',
		)
	);

	// Query for count
	$counts = new EDD\Database\Queries\Customer_Email_Address( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}
