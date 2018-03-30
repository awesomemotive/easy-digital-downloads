<?php
/**
 * Discount Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add a discount
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int
 */
function edd_add_discount( $data = array() ) {
	$discounts = new EDD_Discount_Query();

	return $discounts->add_item( $data );
}

/**
 * Delete a discount
 *
 * @since 3.0.0
 * @param int $discount_id
 * @return int
 */
function edd_delete_discount( $discount_id = 0 ) {
	$discounts = new EDD_Discount_Query();

	do_action( 'edd_pre_delete_discount', $discount_id );

	$retval = $discounts->delete_item( $discount_id );

	do_action( 'edd_post_delete_discount', $discount_id );

	return $retval;
}

/**
 * Get Discount.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object
 *
 * @param int $discount_id Discount ID.
 * @return mixed object|bool EDD_Discount object or false if not found.
 */
function edd_get_discount( $discount_id = 0 ) {
	return edd_get_discount_by( 'id', $discount_id );
}

/**
 * Get Discount By Code.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object
 *
 * @param string $code Discount code.
 * @return EDD_Discount|bool EDD_Discount object or false if not found.
 */
function edd_get_discount_by_code( $code = '' ) {
	return edd_get_discount_by( 'code', $code );
}

/**
 * Retrieve discount by a given field
 *
 * @since 2.0
 * @since 2.7 Updated to use EDD_Discount object
 *
 * @param string $field The field to retrieve the discount with.
 * @param mixed  $value The value for $field.
 * @return mixed object|bool EDD_Discount object or false if not found.
 */
function edd_get_discount_by( $field = '', $value = '' ) {
	$discounts = new EDD_Discount_Query();
	$discount  = $discounts->get_item_by( $field, $value );

	// Return discount
	return $discount;
}

/**
 * Retrieve discount by a given field
 *
 * @since 2.0
 * @since 2.7 Updated to use EDD_Discount object
 *
 * @param string $field The field to retrieve the discount with.
 * @param mixed  $value The value for $field.
 * @return mixed object|bool EDD_Discount object or false if not found.
 */
function edd_get_discount_field( $discount_id, $field = '' ) {
	$discount = edd_get_discount( $discount_id );

	// Check that field exists
	return isset( $discount->{$field} )
		? $discount->{$field}
		: null;
}

/**
 * Update a discount
 *
 * @since 3.0.0
 * @param int $discount_id Discount ID.
 * @param array $data
 * @return int
 */
function edd_update_discount( $discount_id = 0, $data = array() ) {
	$discounts = new EDD_Discount_Query();

	return $discounts->update_item( $discount_id, $data );
}

/**
 * Get Discounts
 *
 * Retrieves an array of all available discount codes.
 *
 * @since 1.0
 * @param array $args Query arguments
 * @return mixed array if discounts exist, false otherwise
 */
function edd_get_discounts( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Back compat for old query arg
	if ( isset( $r['posts_per_page'] ) ) {
		$r['number'] = $r['posts_per_page'];
	}

	// Query
	$discounts = new EDD_Discount_Query( $r );

	// Return discounts
	return $discounts->items;
}

/**
 * Return total number of discounts
 *
 * @since 3.0.0
 *
 * @return int
 */
function edd_get_discount_count() {

	// Query for count
	$discounts = new EDD_Discount_Query( array(
		'number' => 0,
		'count'  => true,

		'update_cache'      => false,
		'update_meta_cache' => false
	) );

	// Return count
	return absint( $discounts->found_items );
}

/**
 * Query for and return array of discount counts, keyed by status
 *
 * @since 3.0.0
 *
 * @return array
 */
function edd_get_discount_counts() {

	// Default statuses
	$defaults = array(
		'active'   => 0,
		'inactive' => 0,
		'expired'  => 0,
		'total'    => 0
	);

	// Query for count
	$counts = new EDD_Discount_Query( array(
		'number'  => 0,
		'count'   => true,
		'groupby' => 'status',

		'update_cache'      => false,
		'update_meta_cache' => false
	) );

	// Default array
	$r = array();

	// Loop through counts and shape return value
	if ( ! empty( $counts->items ) ) {

		// Loop through statuses
		foreach ( $counts->items as $status ) {
			$r[ $status['status'] ] = absint( $status['count'] );
		}

		// Total
		$r['total'] = array_sum( $r );
	}

	// Return counts
	return array_merge( $defaults, $r );
}

/**
 * Has Active Discounts
 *
 * Checks if there is any active discounts, returns a boolean.
 *
 * @since 1.0
 * @return bool
 */
function edd_has_active_discounts() {

	// Get active discounts
	$discounts = edd_get_discounts( array(
		'number' => 1,
		'status' => 'active'
	) );

	// Bail if no active discounts
	if ( empty( $discounts ) ) {
		return false;
	}

	// Loop through discounts and run appropriate filters
	foreach ( $discounts as $discount ) {

		// If we catch an active one, we can quit and return true.
		if ( edd_is_discount_active( $discount, false ) ) {
			return true;
		}
	}

	// Return
	return false;
}

/**
 * Stores a discount code. If the code already exists, it updates it, otherwise
 * it creates a new one.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param array $details     Discount args.
 * @param int   $discount_id Discount ID.
 * @return mixed bool|int The discount ID of the discount code, or false on failure.
 */
function edd_store_discount( $details, $discount_id = 0 ) {
	$return = false;

	if ( null == $discount_id ) {
		$return = (int) edd_add_discount( $details );
	} else {
		$return = (int) edd_update_discount( $discount_id );
	}

	return $return;
}

/**
 * Deletes a discount code.
 *
 * @since 1.0
 * @deprecated 3.0.0
 *
 * @param int $discount_id Discount ID.
 * @return void
 */
function edd_remove_discount( $discount_id = 0 ) {
	edd_delete_discount( $discount_id );
}

/**
 * Updates a discount status from one status to another.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int    $discount_id Discount ID (default: 0)
 * @param string $new_status  New status (default: active)
 *
 * @return bool Whether the status has been updated or not.
 */
function edd_update_discount_status( $discount_id = 0, $new_status = 'active' ) {

	// Defaults
	$updated    = false;
	$new_status = sanitize_key( $new_status );
	$discount   = edd_get_discount( $discount_id );

	// No change
	if ( $new_status === $discount->status ) {
		return true;
	}

	// Try to update status
	if ( ! empty( $discount->id ) ) {
		$updated = edd_update_discount( $discount->id, array(
			'status' => $new_status
		) );
	}

	// Return
	return $updated;
}

/**
 * Checks to see if a discount code already exists.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 *
 * @return bool Whether or not the discount exists.
 */
function edd_discount_exists( $discount_id ) {
	$discount = edd_get_discount( $discount_id );
	return $discount->exists();
}

/**
 * Checks whether a discount code is active.
 *
 * @since 1.0
 * @since 2.6.11 Added $update parameter.
 * @since 2.7    Updated to use EDD_Discount object.
 *
 * @param int  $discount_id   Discount ID.
 * @param bool $update    Update the discount to expired if an one is found but has an active status/
 * @param bool $set_error Whether an error message should be set in session.
 * @return bool Whether or not the discount is active.
 */
function edd_is_discount_active( $discount_id = 0, $update = true, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );

	return $discount->is_active( $update, $set_error );
}

/**
 * Retrieve the discount code.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return string $code Discount Code.
 */
function edd_get_discount_code( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'code' );
}

/**
 * Retrieve the discount code start date.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return string $start Discount start date.
 */
function edd_get_discount_start_date( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'start_date' );
}

/**
 * Retrieve the discount code expiration date.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return string $expiration Discount expiration.
 */
function edd_get_discount_expiration( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'end_date' );
}

/**
 * Retrieve the maximum uses that a certain discount code.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return int $max_uses Maximum number of uses for the discount code.
 */
function edd_get_discount_max_uses( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'max_uses' );
}

/**
 * Retrieve number of times a discount has been used.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return int $uses Number of times a discount has been used.
 */
function edd_get_discount_uses( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'use_count' );
}

/**
 * Retrieve the minimum purchase amount for a discount.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return float $min_price Minimum purchase amount.
 */
function edd_get_discount_min_price( $discount_id = 0 ) {
	return edd_format_amount( edd_get_discount_field( $discount_id, 'min_cart_price' ) );
}

/**
 * Retrieve the discount amount.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return float $amount Discount amount.
 */
function edd_get_discount_amount( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );
	return edd_format_discount_rate( $discount->type, $discount->amount );
}

/**
 * Retrieve the discount type
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return string $type Discount type
 */
function edd_get_discount_type( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'type' );
}

/**
 * Retrieve the products the discount cannot be applied to.
 *
 * @since 1.9
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return array $excluded_products IDs of the required products.
 */
function edd_get_discount_excluded_products( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );
	return $discount->excluded_products;
}

/**
 * Retrieve the discount product requirements.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 * @return array $product_reqs IDs of the required products.
 */
function edd_get_discount_product_reqs( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );
	return $discount->product_reqs;
}

/**
 * Retrieve the product condition.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 *
 * @return string Product condition.
 */
function edd_get_discount_product_condition( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'product_condition' );
}

/**
 * Retrieves the discount status label.
 *
 * @since 2.9
 *
 * @param int $code_id Discount ID.
 * @return string Product condition.
 */
function edd_get_discount_status_label( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );

	return $discount->get_status_label();
}

/**
 * Check if a discount is not global.
 *
 * By default discounts are applied to all products in the cart. Non global discounts are
 * applied only to the products selected as requirements.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Please use edd_get_discount_scope() instead.
 *
 * @param int $discount_id Discount ID.
 *
 * @return boolean Whether or not discount code is not global.
 */
function edd_is_discount_not_global( $discount_id = 0 ) {
	return ( 'global' === edd_get_discount_field( $discount_id, 'scope' ) );
}

/**
 * Retrieve the discount scope.
 *
 * By default this will return "global" as discounts are applied to all products in the cart. Non global discounts are
 * applied only to the products selected as requirements.
 *
 * @since 3.0
 *
 * @param int $discount_id Discount ID.
 *
 * @return string global or not_global.
 */
function edd_get_discount_scope( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'scope' );
}

/**
 * Checks whether a discount code is expired.
 *
 * @since 1.0
 * @since 2.6.11 Added $update parameter.
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $discount_id Discount ID.
 * @param bool $update  Update the discount to expired if an one is found but has an active status.
 * @return bool Whether on not the discount has expired.
 */
function edd_is_discount_expired( $discount_id = 0, $update = true ) {
	$discount = edd_get_discount( $discount_id );
	return ! empty( $discount->id )
		? $discount->is_expired( $update )
		: false;
}

/**
 * Checks whether a discount code is available to use yet (start date).
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $discount_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Is discount started?
 */
function edd_is_discount_started( $discount_id = 0, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );
	return ! empty( $discount->id )
		? $discount->is_started( $set_error )
		: false;
}

/**
 * Is Discount Maxed Out.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $discount_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Is discount maxed out?
 */
function edd_is_discount_maxed_out( $discount_id = 0, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );
	return ! empty( $discount->id )
		? $discount->is_maxed_out( $set_error )
		: false;
}

/**
 * Checks to see if the minimum purchase amount has been met.
 *
 * @since 1.1.7
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $discount_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Whether the minimum amount has been met or not.
 */
function edd_discount_is_min_met( $discount_id = 0, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );
	return ! empty( $discount->id )
		? $discount->is_min_price_met( $set_error )
		: false;
}

/**
 * Is the discount limited to a single use per customer?
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $discount_id Discount ID.
 *
 * @return bool Whether the discount is single use or not.
 */
function edd_discount_is_single_use( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'once_per_customer' );
}

/**
 * Checks to see if the required products are in the cart
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $discount_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Are required products in the cart for the discount to hold.
 */
function edd_discount_product_reqs_met( $discount_id = 0, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );
	return $discount->is_product_requirements_met( $set_error );
}

/**
 * Checks to see if a user has already used a discount.
 *
 * @since 1.1.5
 * @since 1.5 Added $discount_id parameter.
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string $code      Discount Code.
 * @param string $user      User info.
 * @param int    $discount_id   Discount ID.
 * @param bool   $set_error Whether an error message be set in session
 *
 * @return bool $return Whether the the discount code is used.
 */
function edd_is_discount_used( $code = null, $user = '', $discount_id = 0, $set_error = true ) {
	$discount = ( null == $code )
		? edd_get_discount_by_code( $code )
		: edd_get_discount( $discount_id );

	return $discount->is_used( $user, $set_error );
}

/**
 * Check whether a discount code is valid (when purchasing).
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string $code      Discount Code.
 * @param string $user      User info.
 * @param bool   $set_error Whether an error message be set in session.
 * @return bool Whether the discount code is valid.
 */
function edd_is_discount_valid( $code = '', $user = '', $set_error = true ) {
	$discount = edd_get_discount_by_code( $code );

	return ! empty( $discount->id )
		? $discount->is_valid( $user, $set_error )
		: false;
}

/**
 * Retrieves a discount ID from the code.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string $code Discount code.
 * @return int Discount ID.
 */
function edd_get_discount_id_by_code( $code = '' ) {
	$discount = edd_get_discount_by_code( $code );
	return $discount->id;
}

/**
 * Get Discounted Amount.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string           $code       Code to calculate a discount for.
 * @param mixed string|int $base_price Price before discount.
 * @return string Amount after discount.
 */
function edd_get_discounted_amount( $code = '', $base_price = 0 ) {
	$discount = edd_get_discount_by_code( $code );

	return ! empty( $discount->id )
		? $discount->get_discounted_amount( $base_price )
		: false;
}

/**
 * Increases the use count of a discount code.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string $code Discount code to be incremented.
 * @return int New usage.
 */
function edd_increase_discount_usage( $code = '' ) {
	$discount = edd_get_discount_by_code( $code );

	// Increase if discount exists
	return ! empty( $discount->id )
		? (int) $discount->increase_usage()
		: false;
}

/**
 * Decreases the use count of a discount code.
 *
 * @since 2.5.7
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string $code Discount code to be decremented.
 * @return int New usage.
 */
function edd_decrease_discount_usage( $code = '' ) {
	$discount = edd_get_discount_by_code( $code );

	// Decrease if discount exists
	return ! empty( $discount->id )
		? (int) $discount->decrease_usage()
		: false;
}

/**
 * Format Discount Rate
 *
 * @since 1.0
 * @param string $type Discount code type
 * @param string|int $amount Discount code amount
 * @return string $amount Formatted amount
 */
function edd_format_discount_rate( $type = '', $amount = '' ) {
	return ( 'flat' === $type )
		? edd_currency_filter( edd_format_amount( $amount ) )
		: edd_format_amount( $amount ) . '%';
}

/** Meta **********************************************************************/

/**
 * Add meta data field to a discount.
 *
 * @since 3.0.0
 *
 * @param int     $discount_id  Discount ID.
 * @param string  $meta_key     Meta data name.
 * @param mixed   $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique       Optional. Whether the same key should not be added.
 *                              Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_discount_meta( $discount_id, $meta_key, $meta_value, $unique = false ) {
	$discounts = new EDD_Discount_Query();
	return $discounts->add_item_meta( $discount_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from a discount.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate meta data with the same key. It also
 * allows removing all meta data matching key, if needed.
 *
 * @since 3.0.0
 *
 * @param int     $discount_id  Discount ID.
 * @param string  $meta_key     Meta data name.
 * @param mixed   $meta_value   Optional. Meta data value. Must be serializable if
 *                              non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_discount_meta( $discount_id, $meta_key, $meta_value = '' ) {
	$discounts = new EDD_Discount_Query();
	return $discounts->delete_item_meta( $discount_id, $meta_key, $meta_value );
}

/**
 * Retrieve discount meta field for a discount.
 *
 * @since 3.0.0
 *
 * @param int     $discount_id  Discount ID.
 * @param string  $key          Optional. The meta key to retrieve. By default, returns
 *                              data for all keys. Default empty.
 * @param bool    $single       Optional, default is false.
 *                              If true, return only the first value of the specified meta_key.
 *                              This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function edd_get_discount_meta( $discount_id, $key = '', $single = false ) {
	$discounts = new EDD_Discount_Query();
	return $discounts->get_item_meta( $discount_id, $key, $single );
}

/**
 * Update discount meta field based on discount ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and discount ID.
 *
 * If the meta field for the discount does not exist, it will be added.
 *
 * @since 3.0.0
 *
 * @param int     $discount_id  Discount ID.
 * @param string  $meta_key     Meta data key.
 * @param mixed   $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value   Optional. Previous value to check before removing.
 *                              Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function edd_update_discount_meta( $discount_id, $meta_key, $meta_value, $prev_value = '' ) {
	$discounts = new EDD_Discount_Query();
	return $discounts->update_item_meta( $discount_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from discount meta matching meta key.
 *
 * @since 3.0.0
 *
 * @param string $discount_meta_key Key to search for when deleting.
 *
 * @return bool Whether the discount meta key was deleted from the database.
 */
function delete_discount_meta_by_key( $discount_meta_key ) {
	$discounts = new EDD_Discount_Query();
	return $discounts->delete_item_meta( null, $discount_meta_key, '', true );
}

/** Cart **********************************************************************/

/**
 * Set the active discount for the shopping cart
 *
 * @since 1.4.1
 * @param string $code Discount code
 * @return string[] All currently active discounts
 */
function edd_set_cart_discount( $code = '' ) {

	// Get all active cart discounts
	if ( edd_multiple_discounts_allowed() ) {
		$discounts = edd_get_cart_discounts();

	// Only one discount allowed per purchase, so override any existing
	} else {
		$discounts = false;
	}

	if ( $discounts ) {
		$key = array_search( strtolower( $code ), array_map( 'strtolower', $discounts ) );

		// Can't set the same discount more than once
		if ( false !== $key ) {
			unset( $discounts[ $key ] );
		}
		$discounts[] = $code;
	} else {
		$discounts = array();
		$discounts[] = $code;
	}

	EDD()->session->set( 'cart_discounts', implode( '|', $discounts ) );

	do_action( 'edd_cart_discount_set', $code, $discounts );
	do_action( 'edd_cart_discounts_updated', $discounts );

	return $discounts;
}

/**
 * Remove an active discount from the shopping cart
 *
 * @since 1.4.1
 * @param string $code Discount code
 * @return array $discounts All remaining active discounts
 */
function edd_unset_cart_discount( $code = '' ) {
	$discounts = edd_get_cart_discounts();

	if ( $discounts ) {
		$discounts = array_map( 'strtoupper', $discounts );
		$key       = array_search( strtoupper( $code ), $discounts );

		if ( false !== $key ) {
			unset( $discounts[ $key ] );
		}

		$discounts = implode( '|', array_values( $discounts ) );
		// update the active discounts
		EDD()->session->set( 'cart_discounts', $discounts );
	}

	do_action( 'edd_cart_discount_removed', $code, $discounts );
	do_action( 'edd_cart_discounts_updated', $discounts );

	return $discounts;
}

/**
 * Remove all active discounts
 *
 * @since 1.4.1
 * @return void
 */
function edd_unset_all_cart_discounts() {
	EDD()->cart->remove_all_discounts();
}

/**
 * Retrieve the currently applied discount
 *
 * @since 1.4.1
 * @return array $discounts The active discount codes
 */
function edd_get_cart_discounts() {
	return EDD()->cart->get_discounts();
}

/**
 * Check if the cart has any active discounts applied to it
 *
 * @since 1.4.1
 * @return bool
 */
function edd_cart_has_discounts() {
	return EDD()->cart->has_discounts();
}

/**
 * Retrieves the total discounted amount on the cart
 *
 * @since 1.4.1
 *
 * @param bool $discounts Discount codes
 *
 * @return float|mixed|void Total discounted amount
 */
function edd_get_cart_discounted_amount( $discounts = false ) {
	return EDD()->cart->get_discounted_amount( $discounts );
}

/**
 * Get the discounted amount on a price
 *
 * @since 1.9
 * @param array $item Cart item array
 * @param bool|string $discount False to use the cart discounts or a string to check with a discount code
 * @return float The discounted amount
 */
function edd_get_cart_item_discount_amount( $item = array(), $discount = false ) {
	return EDD()->cart->get_item_discount_amount( $item, $discount );
}

/**
 * Outputs the HTML for all discounts applied to the cart
 *
 * @since 1.4.1
 *
 * @return void
 */
function edd_cart_discounts_html() {
	echo edd_get_cart_discounts_html();
}

/**
 * Retrieves the HTML for all discounts applied to the cart
 *
 * @since 1.4.1
 *
 * @param mixed $discounts Array of cart discounts.
 * @return mixed|void
 */
function edd_get_cart_discounts_html( $discounts = false ) {
	if ( ! $discounts ) {
		$discounts = EDD()->cart->get_discounts();
	}

	if ( empty( $discounts ) ) {
		return;
	}

	$html = '';

	foreach ( $discounts as $discount ) {
		$discount_id = edd_get_discount_id_by_code( $discount );
		$rate        = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

		$remove_url  = add_query_arg(
			array(
				'edd_action'    => 'remove_cart_discount',
				'discount_id'   => $discount_id,
				'discount_code' => $discount
			),
			edd_get_checkout_uri()
		);

		$discount_html = '';
		$discount_html .= "<span class=\"edd_discount\">\n";
			$discount_html .= "<span class=\"edd_discount_rate\">$discount&nbsp;&ndash;&nbsp;$rate</span>\n";
			$discount_html .= "<a href=\"$remove_url\" data-code=\"$discount\" class=\"edd_discount_remove\"></a>\n";
		$discount_html .= "</span>\n";

		$html .= apply_filters( 'edd_get_cart_discount_html', $discount_html, $discount, $rate, $remove_url );
	}

	return apply_filters( 'edd_get_cart_discounts_html', $html, $discounts, $rate, $remove_url );
}

/**
 * Show the fully formatted cart discount
 *
 * Note the $formatted parameter was removed from the display_cart_discount() function
 * within EDD_Cart in 2.7 as it was a redundant parameter.
 *
 * @since 1.4.1
 * @param bool $formatted
 * @param bool $echo Echo?
 * @return string $amount Fully formatted cart discount
 */
function edd_display_cart_discount( $formatted = false, $echo = false ) {
	if ( ! $echo ) {
		return EDD()->cart->display_cart_discount( $echo );
	} else {
		EDD()->cart->display_cart_discount( $echo );
	}
}

/**
 * Processes a remove discount from cart request
 *
 * @since 1.4.1
 * @return void
 */
function edd_remove_cart_discount() {
	if ( ! isset( $_GET['discount_id'] ) || ! isset( $_GET['discount_code'] ) ) {
		return;
	}

	do_action( 'edd_pre_remove_cart_discount', absint( $_GET['discount_id'] ) );

	edd_unset_cart_discount( urldecode( $_GET['discount_code'] ) );

	do_action( 'edd_post_remove_cart_discount', absint( $_GET['discount_id'] ) );

	wp_redirect( edd_get_checkout_uri() ); edd_die();
}
add_action( 'edd_remove_cart_discount', 'edd_remove_cart_discount' );

/**
 * Checks whether discounts are still valid when removing items from the cart
 *
 * If a discount requires a certain product, and that product is no longer in the cart, the discount is removed
 *
 * @since 1.5.2
 *
 * @param int $cart_key
 */
function edd_maybe_remove_cart_discount( $cart_key = 0 ) {

	$discounts = edd_get_cart_discounts();

	if ( ! $discounts ) {
		return;
	}

	foreach ( $discounts as $discount ) {
		if ( ! edd_is_discount_valid( $discount ) ) {
			edd_unset_cart_discount( $discount );
		}

	}
}
add_action( 'edd_post_remove_from_cart', 'edd_maybe_remove_cart_discount' );

/**
 * Checks whether multiple discounts can be applied to the same purchase
 *
 * @since 1.7
 * @return bool
 */
function edd_multiple_discounts_allowed() {
	$ret = edd_get_option( 'allow_multiple_discounts', false );
	return (bool) apply_filters( 'edd_multiple_discounts_allowed', $ret );
}

/**
 * Listens for a discount and automatically applies it if present and valid
 *
 * @since 2.0
 * @return void
 */
function edd_listen_for_cart_discount() {

	// Array stops the bulk delete of discount codes from storing as a preset_discount
	if ( empty( $_REQUEST['discount'] ) || is_array( $_REQUEST['discount'] ) ) {
		return;
	}

	$code = preg_replace('/[^a-zA-Z0-9-_]+/', '', $_REQUEST['discount'] );

	EDD()->session->set( 'preset_discount', $code );
}
add_action( 'init', 'edd_listen_for_cart_discount', 0 );

/**
 * Applies the preset discount, if any. This is separated from edd_listen_for_cart_discount() in order to allow items to be
 * added to the cart and for it to persist across page loads if necessary
 *
 * @return void
 */
function edd_apply_preset_discount() {

	$code = sanitize_text_field( EDD()->session->get( 'preset_discount' ) );

	if ( ! $code ) {
		return;
	}

	if ( ! edd_is_discount_valid( $code, '', false ) ) {
		return;
	}

	$code = apply_filters( 'edd_apply_preset_discount', $code );

	edd_set_cart_discount( $code );

	EDD()->session->set( 'preset_discount', null );
}
add_action( 'init', 'edd_apply_preset_discount', 999 );


/**
 * Backwards compatibility filters for get_post_meta() calls on discount codes.
 *
 * @since  3.0
 *
 * @param int    $object_id The object ID post meta was requested for.
 * @param string $meta_key  The meta key requested.
 * @param bool   $single    If the person wants the single value or an array of the value.
 * @return mixed The value to return.
 */
function _edd_discount_post_meta_bc_filter( $value, $object_id, $meta_key, $single ) {
	global $wpdb;

	$meta_keys = apply_filters( 'edd_post_meta_discount_backwards_compat_keys', array(
		'_edd_discount_status',
		'_edd_discount_amount',
		'_edd_discount_uses',
		'_edd_discount_name',
		'_edd_discount_code',
		'_edd_discount_expiration',
		'_edd_discount_start',
		'_edd_discount_is_single_use',
		'_edd_discount_is_not_global',
		'_edd_discount_product_condition',
		'_edd_discount_min_price',
		'_edd_discount_max_uses'
	) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $value;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );
	$discount        = new EDD_Discount( $object_id );

	if ( empty( $discount->id ) ) {

		// We didn't find a discount record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT edd_discount_id FROM {$wpdb->prefix}edd_discountmeta WHERE meta_key = 'legacy_id' AND meta_value = $object_id" );

		if ( ! empty( $object_id ) ) {
			$discount = new EDD_Discount( $object_id );
		} else {
			return $value;
		}
	}

	if ( empty( $discount->id ) ) {
		return $value;
	}

	switch( $meta_key ) {
		case '_edd_discount_name':
		case '_edd_discount_status':
		case '_edd_discount_amount':
		case '_edd_discount_uses':
		case '_edd_discount_code':
		case '_edd_discount_expiration':
		case '_edd_discount_start':
		case '_edd_discount_product_condition':
		case '_edd_discount_min_price':
		case '_edd_discount_max_uses':
			$key = str_replace( '_edd_discount_', '', $meta_key );

			$value = $discount->$key;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_discount_status postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD_Discount object to get the relevant data, instead.', 'easy-digital-downloadsd' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_edd_discount_is_single_use':
			$key = str_replace( '_edd_discount_', '', $meta_key );
			$value = $discount->get_once_per_customer();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_discount_status postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD_Discount object to get the relevant data, instead.', 'easy-digital-downloadsd' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_edd_discount_is_not_global':
			$key = str_replace( '_edd_discount_', '', $meta_key );
			$value = $discount->get_scope();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_discount_status postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD_Discount object to get the relevant data, instead.', 'easy-digital-downloadsd' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;
		default:
			/*
			 * Developers can hook in here with add_filter( 'edd_get_post_meta_discount_backwards_compat-meta_key... in order to
			 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Discount::get_meta
			 */
			$value = apply_filters( 'edd_get_post_meta_discount_backwards_compat-' . $meta_key, $value, $object_id );
			break;
	}

	return $value;

}
add_filter( 'get_post_metadata', '_edd_discount_post_meta_bc_filter', 99, 4 );


/**
 * Listen for calls to update_post_meta and see if we need to filter them.
 *
 * @since 3.0
 *
 * @param mixed   $check     Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table
 * @param int    $object_id  The object ID post meta was requested for.
 * @param string $meta_key   The meta key requested.
 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
 * @param mixed  $prev_value The previous value of the meta
 *
 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
 */
function _edd_discount_update_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
	global $wpdb;

	$meta_keys = apply_filters( 'edd_update_post_meta_discount_backwards_compat_keys', array(
		'_edd_discount_status',
		'_edd_discount_amount',
		'_edd_discount_uses',
		'_edd_discount_name',
		'_edd_discount_code',
		'_edd_discount_expiration',
		'_edd_discount_start',
		'_edd_discount_is_single_use',
		'_edd_discount_is_not_global',
		'_edd_discount_product_condition',
		'_edd_discount_min_price',
		'_edd_discount_max_uses'
	) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $check;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );

	$discount = new EDD_Discount( $object_id );

	if ( empty( $discount->id ) ) {

		// We didn't find a discount record with this ID... so let's check and see if it was a migrated one
		$table_name = edd_get_component_interface( 'discount', 'meta' )->table_name;

		$object_id = $wpdb->get_var( $wpdb->prepare(
			"
				SELECT edd_discount_id
				FROM $table_name
				WHERE meta_key = %s AND meta_value = %d
			", 'legacy_id', $object_id
		) );

		if ( ! empty( $object_id ) ) {
			$discount = new EDD_Discount( $object_id );
		} else {
			return $check;
		}
	}

	if ( empty( $discount->id ) ) {
		return $check;
	}

	switch ( $meta_key ) {
		case '_edd_discount_name':
		case '_edd_discount_status':
		case '_edd_discount_amount':
		case '_edd_discount_uses':
		case '_edd_discount_code':
		case '_edd_discount_expiration':
		case '_edd_discount_start':
		case '_edd_discount_product_condition':
		case '_edd_discount_min_price':
		case '_edd_discount_max_uses':
			$key = str_replace( '_edd_discount_', '', $meta_key );
			$discount->$key = $meta_value;
			$check = $discount->save();

			// Since the old discounts data was simply stored in a single post meta entry, just don't let it be added.
			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'Discount data is no longer stored in post meta. Please use the new custom database tables to insert a discount record.', 'easy-digital-downloads' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;
		case '_edd_discount_is_single_use':
			$key = str_replace( '_edd_discount_', '', $meta_key );
			$discount->once_per_customer = $meta_value;
			$check = $discount->save();

			// Since the old discounts data was simply stored in a single post meta entry, just don't let it be added.
			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'Discount data is no longer stored in post meta. Please use the new custom database tables to insert a discount record.', 'easy-digital-downloads' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;
		case '_edd_discount_is_not_global':
			$key = str_replace( '_edd_discount_', '', $meta_key );
			$discount->scope = $meta_value;
			$check = $discount->save();

			// Since the old discounts data was simply stored in a single post meta entry, just don't let it be added.
			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'Discount data is no longer stored in post meta. Please use the new custom database tables to insert a discount record.', 'easy-digital-downloads' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;
		default:
			/*
			 * Developers can hook in here with add_filter( 'edd_get_post_meta_discount_backwards_compat-meta_key... in order to
			 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Discount::get_meta
			 */
			$check = apply_filters( 'edd_update_post_meta_discount_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $prev_value );
			break;
	}

	return $check;

}
add_filter( 'update_post_metadata', '_edd_discount_update_meta_backcompat', 99, 5 );
add_filter( 'add_post_metadata',    '_edd_discount_update_meta_backcompat', 99, 5 );

/**
 * Add a message for anyone to trying to get discounts via get_post/get_posts/WP_Query.
 *
 * @since 3.0
 *
 * @param WP_Query $query
 */
function _edd_discount_get_post_doing_it_wrong( $query ) {
	global $wpdb;

	// Bail if not a discount
	if ( 'edd_discount' !== $query->get( 'post_type' ) ) {
		return;
	}

	// Setup doing-it-wrong message
	$message = sprintf(
		__( 'As of Easy Digital Downloads 3.0, discounts no longer exist in the %1$s table. They have been migrated to %2$s. Discounts should be accessed using %3$s, %4$s or instantiating a new instance of %5$s. See %6$s for more information.', 'easy-digital-downloads' ),
		'<code>' . $wpdb->posts . '</code>',
		'<code>' . edd_get_component_interface( 'discount', 'table' )->table_name . '</code>',
		'<code>edd_get_discounts()</code>',
		'<code>edd_get_discount()</code>',
		'<code>EDD_Discount</code>',
		'https://easydigitaldownloads.com/development/'
	);

	_doing_it_wrong( 'get_posts()/get_post()', $message, '3.0' );
}
add_action( 'pre_get_posts', '_edd_discount_get_post_doing_it_wrong', 99, 1 );

/**
 * Force filters to run for all queries that have `edd_discount` as the post type.
 *
 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
 *
 * @since 3.0
 *
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function _edd_discounts_bc_force_filters( $query ) {
	if ( 'pre_get_posts' !== current_filter() ) {
		$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
		_doing_it_wrong( __FUNCTION__, $message, '3.0' );
	}

	if ( 'edd_discount' === $query->get( 'post_type' ) ) {
		$query->set( 'suppress_filters', false );
	}
}
add_action( 'pre_get_posts', '_edd_discounts_bc_force_filters', 10, 1 );

/**
 * Hijack the SQL query and rewrite it to fetch data from the discounts table.
 *
 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
 *
 * @since 3.0
 *
 * @param string $request SQL request.
 * @param WP_Query $query Instance of WP_Query.
 *
 * @return string $request Rewritten SQL query.
 */
function _edd_discounts_bc_posts_request( $request, $query ) {
	global $wpdb;

	if ( 'posts_request' !== current_filter() ) {
		$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
		_doing_it_wrong( __FUNCTION__, $message, '3.0' );
	}

	if ( 'edd_discount' === $query->get( 'post_type' ) ) {
		$defaults = array(
			'number'  => 30,
			'status'  => array( 'active', 'inactive', 'expired' ),
			'order'   => 'DESC',
			'orderby' => 'date_created',
		);

		$args = array(
			'number' => $query->get( 'posts_per_page' ),
			'status' => $query->get( 'post_status', array( 'active', 'inactive' ) ),
		);

		$orderby = $query->get( 'orderby', false );
		if ( $orderby ) {
			switch ( $orderby ) {
				case 'none':
				case 'ID':
				case 'author':
				case 'post__in':
				case 'type':
				case 'post_type':
					$args['orderby'] = 'id';
					break;
				case 'title':
					$args['orderby'] = 'name';
					break;
				case 'date':
				case 'post_date':
				case 'modified':
				case 'post_modified':
					$args['orderby'] = 'date_created';
					break;
				default:
					$args['orderby'] = 'id';
					break;
			}
		}

		$offset = $query->get( 'offset', false );
		if ( $offset ) {
			$args['offset'] = absint( $offset );
		} else {
			$args['offset'] = 0;
		}

		if ( 'any' === $args['status'] ) {
			$args['status'] = $defaults['status'];
		}

		$args = wp_parse_args( $args, $defaults );

		if ( array_key_exists( 'number', $args ) ) {
			$args['number'] = absint( $args['number'] );
		}

		$table_name = edd_get_component_interface( 'discount', 'table' )->table_name;

		$meta_query = $query->get( 'meta_query' );

		$clauses = array();
		$sql_where = 'WHERE 1=1';

		$meta_key   = $query->get( 'meta_key',   false );
		$meta_value = $query->get( 'meta_value', false );

		// 'meta_key' and 'meta_value' passed as arguments
		if ( $meta_key && $meta_value ) {
			/**
			 * Check that the key exists as a column in the table.
			 * Note: there is no backwards compatibility support for product requirements and excluded
			 * products as these would be serialized under the old schema.
			 */
			if ( in_array( $meta_key, array_keys( EDD()->discounts->get_columns() ) ) ) {
				$sql_where .= ' ' . $wpdb->prepare( $meta_key . ' = %s', $meta_value );
			}
		}

		if ( ! empty( $meta_query ) ) {
			foreach ( $meta_query as $key => $query ) {
				$relation = 'AND'; // Default relation

				if ( is_string( $query ) && 'relation' === $key ) {
					$relation = $query;
				}

				if ( is_array( $query ) ) {
					if ( array_key_exists( 'key', $query ) ) {
						$query['key'] = str_replace( '_edd_discount_', '', $query['key'] );

						/**
						 * Check that the key exists as a column in the table.
						 * Note: there is no backwards compatibility support for product requirements and excluded
						 * products as these would be serialised under the old schema.
						 */
				 		if ( in_array( $query['key'], array_keys( EDD()->discounts->get_columns() ) ) && array_key_exists( 'value', $query ) ) {
							$meta_compare = $query['compare'];
							$meta_compare = strtoupper( $meta_compare );

							$meta_value = $query['value'];

							$where = null;

							switch ( $meta_compare ) {
								case 'IN':
								case 'NOT IN':
									$meta_compare_string = '(' . substr( str_repeat( ',%s', count( $meta_value ) ), 1 ) . ')';
									$where = $wpdb->prepare( $meta_compare_string, $meta_value );
									break;

								case 'BETWEEN':
								case 'NOT BETWEEN':
									$meta_value = array_slice( $meta_value, 0, 2 );
									$where      = $wpdb->prepare( '%s AND %s', $meta_value );
									break;

								case 'LIKE':
								case 'NOT LIKE':
									$meta_value = '%' . $wpdb->esc_like( $meta_value ) . '%';
									$where      = $wpdb->prepare( '%s', $meta_value );
									break;

								// EXISTS with a value is interpreted as '='.
								case 'EXISTS':
									$where = $wpdb->prepare( '%s', $meta_value );
									break;

								// 'value' is ignored for NOT EXISTS.
								case 'NOT EXISTS':
									$where = $query['key'] . ' IS NULL';
									break;

								default:
									$where = $wpdb->prepare( '%s', $meta_value );
									break;
							}

							if ( ! is_null( $where ) ) {
								$clauses['where'][] = $query['key'] . ' ' . $meta_compare . ' ' . $where;
							}
						}
					}

					if ( 0 < count( $clauses['where'] ) ) {
						$sql_where .= ' AND ( ' . implode( ' ' . $relation . ' ', $clauses['where'] ) . ' )';
					}
				}
			}
		}

		$request = "SELECT id FROM {$table_name} $sql_where ORDER BY {$args['orderby']} {$args['order']} LIMIT {$args['offset']}, {$args['number']};";
	}

	return $request;
}
add_filter( 'posts_request', '_edd_discounts_bc_posts_request', 10, 2 );

/**
 * Fill the returned WP_Post objects with the data from the discounts table.
 *
 * @since 3.0
 *
 * @param array $posts Posts returned from the SQL query.
 * @param WP_Query $query Instance of WP_Query.
 *
 * @return array New WP_Post objects.
 */
function _edd_discounts_bc_posts_results( $posts, $query ) {
	if ( 'posts_results' !== current_filter() ) {
		$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
		_doing_it_wrong( __FUNCTION__, $message, '3.0' );
	}

	if ( 'edd_discount' === $query->get( 'post_type' ) ) {
		$new_posts = array();

		foreach ( $posts as $post ) {
			$discount = edd_get_discount( $post->id );

			$object_vars = array(
				'ID'            => $discount->id,
				'post_title'    => $discount->name,
				'post_status'   => $discount->status,
				'post_type'     => 'edd_discount',
				'post_date'     => $discount->date_created,
				'post_date_gmt' => $discount->date_created,
			);

			foreach ( $object_vars as $object_var => $value ) {
				$post->{$object_var} = $value;
			}

			$post = new WP_Post( $post );

			$new_posts[] = $post;
		}

		return $new_posts;
	}

	return $posts;
}
add_filter( 'posts_results', '_edd_discounts_bc_posts_results', 10, 2 );

/**
 * Backwards compatibility layer for wp_count_posts().
 *
 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
 *
 * @since 3.0
 *
 * @param string $request SQL request.
 * @param WP_Query $query Instance of WP_Query.
 *
 * @return string $request Rewritten SQL query.
 */
function _edd_discounts_bc_wp_count_posts( $query ) {
	global $wpdb;

	$expected = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = 'edd_discount' GROUP BY post_status";

	if ( $expected === $query ) {
		$discounts_table = edd_get_component_interface( 'discount', 'table' )->table_name;
		$query           = "SELECT status AS post_status, COUNT( * ) AS num_posts FROM {$discounts_table} GROUP BY post_status";
	}

	return $query;
}
add_filter( 'query', '_edd_discounts_bc_wp_count_posts', 10, 1 );
