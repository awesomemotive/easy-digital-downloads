<?php
/**
 * Adjustment Functions.
 *
 * @package     EDD
 * @subpackage  Adjustments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add an adjustment.
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
 * Delete an adjustment.
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
 * Update an adjustment.
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
 * Get an adjustment by ID.
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
 * Get an adjustment by a specific field value.
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
 * @return \EDD\Adjustments\Adjustment[]
 */
function edd_get_adjustments( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

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
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s)
	$adjustments = new EDD\Database\Queries\Adjustment( $r );

	// Return count(s)
	return absint( $adjustments->found_items );
}
