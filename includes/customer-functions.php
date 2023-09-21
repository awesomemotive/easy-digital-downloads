<?php
/**
 * Customer Functions.
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
 * Add a customer.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of customer data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $user_id        WordPress user ID linked to the customer account.
 *                                  Default 0.
 *     @type string $email          Customer's primary email address. Default empty.
 *     @type string $name           Customer's name. Default empty.
 *     @type string $status         Customer's status. Default `active`.
 *     @type float  $purchase_value Aggregated purchase value of the customer.
 *                                  Default 0.
 *     @type int    $purchase_count Aggregated purchase count of the customer.
 *                                  Default 0.
 *     @type string $date_created   Optional. Automatically calculated on add/edit.
 *                                  The date & time the customer was created.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified  Optional. Automatically calculated on add/edit.
 *                                  The date & time the customer was last modified.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of the inserted customer, false on failure.
 */
function edd_add_customer( $data = array() ) {

	// An email must be given for every customer that is created.
	if ( ! isset( $data['email'] ) || empty( $data['email'] ) ) {
		return false;
	}

	$customers  = new EDD\Database\Queries\Customer();

	return $customers->add_item( $data );
}

/**
 * Delete a customer.
 *
 * @since 3.0
 *
 * @param int ID of customer to delete.
 * @return int|false `1` if the customer was deleted successfully, false on error.
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
 * @return int|false `1` if the customer was deleted successfully, false on error.
 */
function edd_destroy_customer( $customer_id = 0 ) {

	// Bail if a customer ID was not passed.
	if ( empty( $customer_id ) ) {
		return false;
	}

	// Get email addresses.
	$email_addresses = edd_get_customer_email_addresses( array(
		'customer_id'   => $customer_id,
		'no_found_rows' => true,
	) );

	// Destroy email addresses.
	if ( ! empty( $email_addresses ) ) {
		foreach ( $email_addresses as $email_address ) {
			edd_delete_customer_email_address( $email_address->id );
		}
	}

	// Get addresses.
	$addresses = edd_get_customer_addresses( array(
		'customer_id'   => $customer_id,
		'no_found_rows' => true,
	) );

	// Destroy addresses.
	if ( ! empty( $addresses ) ) {
		foreach ( $addresses as $address ) {
			edd_delete_customer_address( $address->id );
		}
	}

	// Delete the customer.
	$customer_destroyed = edd_delete_customer( $customer_id );
	if ( ! empty( $customer_destroyed ) ) {
		/**
		 * Action that runs when a customer is destroyed with the edd_destroy_customer function.
		 *
		 * This action is similar to edd_customer_deleted and includes running that action,
		 * but also includes deleting all customer addresses and email addresses.
		 *
		 * @since 3.0.4
		 *
		 * @param int          $customer_id Customer ID being destroyed.
		 */
		do_action( 'edd_customer_destroyed', $customer_id );
	}

	return $customer_destroyed;
}

/**
 * Update a customer.
 *
 * @since 3.0
 *
 * @param int $customer_id Customer ID.
 * @param array $data {
 *     Array of customer data. Default empty.
 *
 *     @type int    $user_id        WordPress user ID linked to the customer account.
 *                                  Default 0.
 *     @type string $email          Customer's primary email address. Default empty.
 *     @type string $name           Customer's name. Default empty.
 *     @type string $status         Customer's status. Default `active`.
 *     @type float  $purchase_value Aggregated purchase value of the customer.
 *                                  Default 0.
 *     @type int    $purchase_count Aggregated purchase count of the customer.
 *                                  Default 0.
 *     @type string $date_created   Optional. Automatically calculated on add/edit.
 *                                  The date & time the customer was created.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified  Optional. Automatically calculated on add/edit.
 *                                  The date & time the customer was last modified.
 *                                  Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_customer( $customer_id = 0, $data = array() ) {
	$customers = new EDD\Database\Queries\Customer();

	return $customers->update_item( $customer_id, $data );
}

/**
 * Get a customer item by ID.
 *
 * @since 3.0
 *
 * @param int $customer_id Customer ID.
 * @return EDD_Customer|false Customer object if successful, false otherwise.
 */
function edd_get_customer( $customer_id = 0 ) {
	$customers = new EDD\Database\Queries\Customer();

	// Return customer.
	return $customers->get_item( $customer_id );
}

/**
 * Get a customer item by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD_Customer|false Customer object if successful, false otherwise.
 */
function edd_get_customer_by( $field = '', $value = '' ) {
	// For backwards compatibility in filters only.
	$customers_db = EDD()->customers;

	/**
	 * Filters the Customer before querying the database.
	 *
	 * Return a non-null value to bypass the default query and return early.
	 *
	 * @since 2.9.23
	 *
	 * @param mixed|null          $customer         Customer to return instead. Default null to use default method.
	 * @param string              $field            The field to retrieve by.
	 * @param mixed               $value            The value to search by.
	 * @param EDD\Compat\Customer $edd_customers_db Customer database class. Deprecated in 3.0.
	 */
	$found = apply_filters( 'edd_pre_get_customer', null, $field, $value, $customers_db );

	if ( null !== $found ) {
		return $found;
	}

	$customers = new EDD\Database\Queries\Customer();
	if ( 'email' === $field ) {
		$customer_emails = new EDD\Database\Queries\Customer_Email_Address();
		$customer_email  = $customer_emails->get_item_by( 'email', $value );

		if ( empty( $customer_email ) ) {
			$customer = $customers->get_item_by( 'email', $value );

			if ( ! empty( $customer ) ) {
				$customer_has_primary = edd_count_customer_email_addresses(
					array(
						'customer_id' => $customer->id,
						'type'        => 'primary',
					)
				);
				$email_address_id     = edd_add_customer_email_address(
					array(
						'customer_id' => $customer->id,
						'email'       => $value,
						'type'        => $customer_has_primary ? 'secondary' : 'primary',
					)
				);
				$customer_email       = $customer_emails->get_item( $email_address_id );
			}
		}

		$customer = false;
		if ( ! empty( $customer_email->customer_id ) ) {
			$customer = $customers->get_item_by( 'id', $customer_email->customer_id );
		}
	} else {
		$customer = $customers->get_item_by( $field, $value );
	}

	/**
	 * Filters the single Customer retrieved from the database based on field.
	 *
	 * @since 2.9.23
	 *
	 * @param EDD_Customer|false  $customer         Customer query result. False if no Customer is found.
	 * @param array               $args             Arguments used to query the Customer.
	 * @param EDD\Compat\Customer $edd_customers_db Customer database class. Deprecated in 3.0.
	 */
	$customer = apply_filters( "edd_get_customer_by_{$field}", $customer, $customers->query_vars, $customers_db );

	/**
	 * Filters the single Customer retrieved from the database.
	 *
	 * @since 2.9.23
	 *
	 * @param EDD_Customer|false $customer         Customer query result. False if no Customer is found.
	 * @param array              $args             Arguments used to query the Customer.
	 * @param EDD_DB_Customers   $edd_customers_db Customer database class.
	 */
	$customer = apply_filters( 'edd_get_customer', $customer, $customers->query_vars, $customers_db );

	return $customer;
}

/**
 * Get a field from a customer object.
 *
 * @since 3.0
 *
 * @param int    $customer_id Customer ID. Default `0`.
 * @param string $field       Field to retrieve from object. Default empty.
 *
 * @return mixed Null if customer does not exist. Value of Customer if exists.
 */
function edd_get_customer_field( $customer_id = 0, $field = '' ) {
	$customer = edd_get_customer( $customer_id );

	// Check that field exists.
	return isset( $customer->{$field} )
		? $customer->{$field}
		: null;
}

/**
 * Query for customers.
 *
 * @see \EDD\Database\Queries\Customer::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer` for
 *                    accepted arguments.
 * @return \EDD_Customer[] Array of `EDD_Customer` objects.
 */
function edd_get_customers( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	if ( -1 == $r['number'] ) {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Do not use -1 to retrieve all results.', 'easy-digital-downloads' ), '3.0' );
		$r['number'] = 9999999;
	}

	// Instantiate a query object
	$customers = new EDD\Database\Queries\Customer();

	// Return customers
	return $customers->query( $r );
}

/**
 * Get total number of customers.
 *
 * @see \EDD\Database\Queries\Customer::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer` for
 *                    accepted arguments.
 * @return int Number of customers returned based on query arguments passed.
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
 * Query for and return array of customer counts, keyed by status.
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer` for
 *                    accepted arguments.
 * @return array Customer counts keyed by status.
 */
function edd_get_customer_counts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'count'   => true,
		'groupby' => 'status'
	) );

	// Query for count
	$counts = new EDD\Database\Queries\Customer( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}

/**
 * Return the role used to edit customers.
 *
 * @since 3.0
 *
 * @return string Role used to edit customers.
 */
function edd_get_edit_customers_role() {

	/**
	 * Filter role used to edit customers.
	 *
	 * @since 2.3
	 *
	 * @param string WordPress role used to edit customers. Default `edit_shop_payments`.
	 */
	return apply_filters( 'edd_edit_customers_role', 'edit_shop_payments' );
}

/**
 * Retrieve all of the IP addresses used by a customer.
 *
 * @since 3.0
 *
 * @param int $customer_id Customer ID.
 *
 * @return array Array of objects containing IP addresses.
 */
function edd_get_customer_ip_addresses( $customer_id = 0 ) {

	// Bail if no customer ID was passed.
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
 * @since 3.0
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
 * @since 3.0
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
 * @since 3.0
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
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	// Return customer address.
	return $customer_addresses->get_item( $customer_address_id );
}

/**
 * Add a customer address.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of customer address data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $customer_id   Customer ID. Default `0`.
 *     @type string $type          Address type. Default `billing`.
 *     @type string $status        Address status, if used or not. Default `active`.
 *     @type string $address       First line of address. Default empty.
 *     @type string $address2      Second line of address. Default empty.
 *     @type string $city          City. Default empty.
 *     @type string $region        Region. See `edd_get_shop_states()` for
 *                                 accepted values. Default empty.
 *     @type string $postal_code   Postal code. Default empty.
 *     @type string $country       Country. See `edd_get_country_list()` for
 *                                 accepted values. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created customer address, false on error.
 */
function edd_add_customer_address( $data = array() ) {

	// A customer ID must be supplied for every address inserted.
	if ( empty( $data['customer_id'] ) ) {
		return false;
	}

	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	return $customer_addresses->add_item( $data );
}

/**
 * Delete a customer address.
 *
 * @since 3.0
 *
 * @param int $customer_address_id Customer address ID.
 * @return int|false `1` if the adjustment was deleted successfully, false on error.
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
 * @param array $data {
 *     Array of customer address data. Default empty.
 *
 *     @type int    $customer_id   Customer ID. Default `0`.
 *     @type string $type          Address type. Default `billing`.
 *     @type string $status        Address status, if used or not. Default `active`.
 *     @type string $address       First line of address. Default empty.
 *     @type string $address2      Second line of address. Default empty.
 *     @type string $city          City. Default empty.
 *     @type string $region        Region. See `edd_get_shop_states()` for
 *                                 accepted values. Default empty.
 *     @type string $postal_code   Postal code. Default empty.
 *     @type string $country       Country. See `edd_get_country_list()` for
 *                                 accepted values. Default empty.
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
function edd_update_customer_address( $customer_address_id = 0, $data = array() ) {
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	return $customer_addresses->update_item( $customer_address_id, $data );
}

/**
 * Get a customer address by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Customers\Customer_Address|false Customer_Address if successful,
 *                                               false otherwise.
 */
function edd_get_customer_address_by( $field = '', $value = '' ) {
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	// Return customer address
	return $customer_addresses->get_item_by( $field, $value );
}

/**
 * Query for customer addresses.
 *
 * @see \EDD\Database\Queries\Customer_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer_Address` for
 *                    accepted arguments.
 * @return \EDD\Customers\Customer_Address[] Array of `Customer_Address` objects.
 */
function edd_get_customer_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$customer_addresses = new EDD\Database\Queries\Customer_Address();

	// Return addresses
	return $customer_addresses->query( $r );
}

/**
 * Count customer addresses.
 *
 * @see \EDD\Database\Queries\Customer_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer_Address` for
 *                    accepted arguments.
 * @return int Number of customer addresses returned based on query arguments passed.
 */
function edd_count_customer_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$customer_addresses = new EDD\Database\Queries\Customer_Address( $r );

	// Return count(s)
	return absint( $customer_addresses->found_items );
}

/**
 * Maybe add a customer address. Only unique addresses will be added. Used
 * by `edd_build_order()` and `edd_add_manual_order()` to maybe add order
 * addresses to the customer addresses table. Also used by the data migrator
 * class when migrating orders from 2.9.
 *
 * @since 3.0
 *
 * @param int   $customer_id Customer ID.
 * @param array $data {
 *     Array of customer address data. Default empty.
 *
 *     @type string $type          Address type. Default `billing`.
 *     @type string $status        Address status, if used or not. Default `active`.
 *     @type string $address       First line of address. Default empty.
 *     @type string $address2      Second line of address. Default empty.
 *     @type string $city          City. Default empty.
 *     @type string $region        Region. See `edd_get_shop_states()` for
 *                                 accepted values. Default empty.
 *     @type string $postal_code   Postal code. Default empty.
 *     @type string $country       Country. See `edd_get_country_list()` for
 *                                 accepted values. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false ID of the insert customer address. False otherwise.
 */
function edd_maybe_add_customer_address( $customer_id = 0, $data = array() ) {

	// Bail if nothing passed.
	if ( empty( $customer_id ) || empty( $data ) ) {
		return false;
	}

	// Set up an array with empty address keys. If all of these are empty in $data, the address should not be added.
	$empty_address    = array(
		'address'     => '',
		'address2'    => '',
		'city'        => '',
		'region'      => '',
		'country'     => '',
		'postal_code' => '',
	);
	$address_to_check = array_intersect_key( $data, $empty_address );
	$address_to_check = array_filter( $address_to_check );
	if ( empty( $address_to_check ) ) {
		return false;
	}
	$address_to_check['customer_id'] = $customer_id;
	$address_to_check['type']        = empty( $data['type'] ) ? 'billing' : $data['type'];

	// Check if this address is already assigned to the customer.
	$address_exists = edd_get_customer_addresses( $address_to_check );
	if ( ! empty( $address_exists ) ) {
		if ( 'billing' === $address_to_check['type'] ) {
			edd_update_customer_address( $address_exists[0], array( 'is_primary' => true ) );
		}
		return false;
	}

	$data['customer_id'] = $customer_id;
	if ( 'billing' === $address_to_check['type'] ) {
		$data['is_primary'] = true;
	}

	// Add the new address to the customer record.
	return edd_add_customer_address( $data );
}

/**
 * Query for and return array of customer address counts, keyed by status.
 *
 * @see \EDD\Database\Queries\Customer_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer_Address` for
 *                    accepted arguments.
 * @return array Customer address counts keyed by status.
 */
function edd_get_customer_address_counts( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count'   => true,
		'groupby' => 'status'
	) );

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
 * @param array $data {
 *     Array of customer email address data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $customer_id   Customer ID. Default `0`.
 *     @type string $type          Email address type. Default `secondary`.
 *     @type string $status        Email address type. Default `active`.
 *     @type string $email         Email address. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the email address was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the email address was last
 *                                 modified. Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created customer email address, false on error.
 */
function edd_add_customer_email_address( $data ) {

	// A customer ID and email must be supplied for every address inserted.
	if ( empty( $data['customer_id'] ) || empty( $data['email'] ) ) {
		return false;
	}

	// Instantiate a query object.
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	// Check if the address already exists for this customer.
	$existing_addresses = $customer_email_addresses->query(
		array(
			'customer_id' => $data['customer_id'],
			'email'       => $data['email'],
		)
	);
	if ( ! empty( $existing_addresses ) ) {
		return false;
	}

	// Add the email address to the customer.
	return $customer_email_addresses->add_item( $data );
}

/**
 * Delete a customer email address.
 *
 * @since 3.0
 *
 * @param int $customer_email_address_id Customer email address ID.
 * @return int|false `1` if the customer email address was deleted successfully,
 *                   false on error.
 */
function edd_delete_customer_email_address( $customer_email_address_id ) {
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	return $customer_email_addresses->delete_item( $customer_email_address_id );
}

/**
 * Update a customer email address.
 *
 * @since 3.0
 *
 * @param int   $customer_email_address_id Customer email address ID.
 * @param array $data {
 *     Array of customer email address data. Default empty.
 *
 *     @type int    $customer_id   Customer ID. Default `0`.
 *     @type string $type          Email address type. Default `secondary`.
 *     @type string $status        Email address type. Default `active`.
 *     @type string $email         Email address. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the email address was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the email address was last
 *                                 modified. Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_customer_email_address( $customer_email_address_id, $data = array() ) {
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	return $customer_email_addresses->update_item( $customer_email_address_id, $data );
}

/**
 * Get a customer email address by ID.
 *
 * @since 3.0
 *
 * @param int $customer_email_address_id Customer email address ID.
 * @return \EDD\Customers\Customer_Email_Address|false Customer_Email_Address if
 *                                                     successful, false otherwise.
 */
function edd_get_customer_email_address( $customer_email_address_id ) {
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	// Return customer email address
	return $customer_email_addresses->get_item( $customer_email_address_id );
}

/**
 * Get a customer email address by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Customers\Customer_Email_Address|false Customer_Email_Address if
 *                                                     successful, false otherwise.
 */
function edd_get_customer_email_address_by( $field = '', $value = '' ) {
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	// Return customer email address
	return $customer_email_addresses->get_item_by( $field, $value );
}

/**
 * Query for customer email addresses.
 *
 * @see \EDD\Database\Queries\Customer_Email_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer_Email_Address`
 *                    for accepted arguments.
 * @return \EDD\Customers\Customer_Email_Address[] Array of `Customer_Email_Address` objects.
 */
function edd_get_customer_email_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address();

	// Return customer email addresses
	return $customer_email_addresses->query( $r );
}

/**
 * Count customer addresses.
 *
 * @see \EDD\Database\Queries\Customer_Email_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer_Email_Address`
 *                    for accepted arguments.
 * @return int Number of customer email addresses returned based on query arguments passed.
 */
function edd_count_customer_email_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$customer_email_addresses = new EDD\Database\Queries\Customer_Email_Address( $r );

	// Return count(s)
	return absint( $customer_email_addresses->found_items );
}

/**
 * Query for and return array of customer email counts, keyed by status.
 *
 * @see \EDD\Database\Queries\Customer_Email_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Customer_Email_Address`
 *                    for accepted arguments.
 * @return array Customer email addresses keyed by status.
 */
function edd_get_customer_email_address_counts( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count'   => true,
		'groupby' => 'status'
	) );

	// Query for count
	$counts = new EDD\Database\Queries\Customer_Email_Address( $r );

	// Format & return
	return edd_format_counts( $counts, $r['groupby'] );
}
