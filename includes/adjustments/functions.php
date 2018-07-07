<?php
/**
 * Adjustment Functions
 *
 * @package     EDD
 * @subpackage  Adjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a adjustment.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int
 */
function edd_add_adjustment( $data = array() ) {
	$adjustments = new EDD\Database\Queries\Adjustment();

	return $adjustments->add_item( $data );
}

/**
 * Delete a adjustment.
 *
 * @since 3.0
 *
 * @param int $adjustment_id Adjustment ID.
 * @return int
 */
function edd_delete_adjustment( $adjustment_id = 0 ) {
	$adjustments = new EDD\Database\Queries\Adjustment();

	return $adjustments->delete_item( $adjustment_id );
}

/**
 * Update a adjustment.
 *
 * @since 3.0
 *
 * @param int   $adjustment_id Adjustment ID.
 * @param array $data          Updated adjustment data.
 * @return bool Whether or not the adjustment was updated.
 */
function edd_update_adjustment( $adjustment_id = 0, $data = array() ) {
	$adjustments = new EDD\Database\Queries\Adjustment();

	return $adjustments->update_item( $adjustment_id, $data );
}

/**
 * Get a adjustment by ID.
 *
 * @since 3.0
 *
 * @param int $adjustment_id Adjustment ID.
 * @return object
 */
function edd_get_adjustment( $adjustment_id = 0 ) {
	return edd_get_adjustment_by( 'id', $adjustment_id );
}

/**
 * Get a adjustment by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_adjustment_by( $field = '', $value = '' ) {
	$adjustments = new EDD\Database\Queries\Adjustment();

	// Return adjustment
	return $adjustments->get_item_by( $field, $value );
}

/**
 * Query for adjustments.
 *
 * @since 3.0
 *
 * @param array $args
 * @return array
 */
function edd_get_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$adjustments = new EDD\Database\Queries\Adjustment();

	// Return adjustments
	return $adjustments->query( $r );
}

/**
 * Count adjustments.
 *
 * @since 3.0
 *
 * @param array $args Arguments.
 * @return int
 */
function edd_count_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$adjustments = new EDD\Database\Queries\Adjustment( $r );

	// Return count(s)
	return absint( $adjustments->found_items );
}

/** Meta **********************************************************************/

/**
 * Add meta data field to an adjustment.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id Adjustment ID.
 * @param string  $meta_key      Meta data name.
 * @param mixed   $meta_value    Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique        Optional. Whether the same key should not be added.
 *                               Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_adjustment_meta( $adjustment_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_adjustment', $adjustment_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from an adjustment.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate meta data with the same key. It also
 * allows removing all meta data matching key, if needed.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id Adjustment ID.
 * @param string  $meta_key      Meta data name.
 * @param mixed   $meta_value    Optional. Meta data value. Must be serializable if
 *                               non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_adjustment_meta( $adjustment_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_adjustment', $adjustment_id, $meta_key, $meta_value );
}

/**
 * Retrieve adjustment meta field for an adjustment.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id Adjustment ID.
 * @param string  $key           Optional. The meta key to retrieve. By default, returns
 *                               data for all keys. Default empty.
 * @param bool    $single        Optional, default is false.
 *                               If true, return only the first value of the specified meta_key.
 *                               This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function edd_get_adjustment_meta( $adjustment_id, $key = '', $single = false ) {
	return get_metadata( 'edd_adjustment', $adjustment_id, $key, $single );
}

/**
 * Update discount meta field based on discount ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and discount ID.
 *
 * If the meta field for the discount does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int     $adjustment_id  Adjustment ID.
 * @param string  $meta_key     Meta data key.
 * @param mixed   $meta_value   Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value   Optional. Previous value to check before removing.
 *                              Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function edd_update_adjustment_meta( $adjustment_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_adjustment', $adjustment_id, $meta_key, $meta_value, $prev_value );
}