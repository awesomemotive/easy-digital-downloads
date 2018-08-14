<?php
/**
 * Tax Rate Functions
 *
 * @package     EDD
 * @subpackage  Tax_Rates
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a tax rate.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int|false ID of newly created tax rate, false on error.
 */
function edd_add_tax_rate( $data = array() ) {

	// A country must be supplied for every tax rate that is inserted into the database.
	if ( empty( $data['country'] ) ) {
		return false;
	}

	// Instantiate a query object
	$tax_rates = new EDD\Database\Queries\Tax_Rate();

	return $tax_rates->add_item( $data );
}

/**
 * Delete a tax rate.
 *
 * @since 3.0
 *
 * @param int $tax_rate_id Tax rate ID.
 * @return int
 */
function edd_delete_tax_rate( $tax_rate_id = 0 ) {
	$tax_rates = new EDD\Database\Queries\Tax_Rate();

	return $tax_rates->delete_item( $tax_rate_id );
}

/**
 * Update a tax rate.
 *
 * @since 3.0
 *
 * @param int   $tax_rate_id Tax rate ID.
 * @param array $data   Updated tax_rate data.
 * @return bool Whether or not the tax_rate was updated.
 */
function edd_update_tax_rate( $tax_rate_id = 0, $data = array() ) {
	$tax_rates = new EDD\Database\Queries\Tax_Rate();

	return $tax_rates->update_item( $tax_rate_id, $data );
}

/**
 * Get a tax rate by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_tax_rate_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$tax_rates = new EDD\Database\Queries\Tax_Rate();

	// Get an item
	return $tax_rates->get_item_by( $field, $value );
}

/**
 * Count tax rates.
 *
 * @since 3.0
 *
 * @param array $args
 * @return int
 */
function edd_count_tax_rates( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$tax_rates = new EDD\Database\Queries\Tax_Rate( $r );

	// Return count(s)
	return absint( $tax_rates->found_items );
}

/**
 * Retrieve tax rates
 *
 * @since 1.6
 * @since 3.0 Updated to use new query class.
 *            Added $output parameter to output an array of EDD\Tax_Rates\Tax_Rate objects, if set to `object`.
 *            Added $args parameter.
 *
 * @param array  $args   Query arguments.
 * @param string $output Optional. Type of data to output. Any of ARRAY_N | OBJECT.
 *
 * @return array|\EDD\Tax_Rates\Tax_Rate[] Tax rates.
 */
function edd_get_tax_rates( $args = array(), $output = ARRAY_N ) {

	if ( isset( $args['type'] ) && 'active' === $args['type'] ) {
		add_filter( 'edd_adjustments_query_clauses', 'edd_active_tax_rates_query_clauses' );
	}

	// Instantiate a query object
	$tax_rates = new EDD\Database\Queries\Tax_Rate();

	// Parse args
	$r = wp_parse_args( $args, array(
		'number'  => 30,
		'orderby' => 'date_created',
		'order'   => 'ASC',
	) );

	if ( isset( $args['type'] ) && 'active' === $args['type'] ) {
		remove_filter( 'edd_adjustments_query_clauses', 'edd_active_tax_rates_query_clauses' );
	}

	$tax_rates->query( $r );

	if ( OBJECT === $output ) {
		return $tax_rates->items;
	}

	$rates = array();

	if ( $tax_rates->items ) {
		foreach ( $tax_rates->items as $tax_rate ) {
			$rate = array(
				'id'      => absint( $tax_rate->id ),
				'country' => esc_attr( $tax_rate->country ),
				'rate'    => floatval( $tax_rate->rate ),
			);

			if ( isset( $tax_rate->region ) && ! empty( $tax_rate->region ) ) {
				$rate['state'] = esc_attr( $tax_rate->region );
			}

			if ( 'country' === $tax_rate->scope ) {
				$rate['global'] = '1';
			}

			$rates[] = $rate;
		}
	}

	return (array) apply_filters( 'edd_get_tax_rates', $rates );
}

/**
 * Add a WHERE clause to ensure only active tax rates are returned.
 *
 * @since 3.0
 *
 * @param array $clauses Query clauses.
 * @return array $clauses Updated query clauses.
 */
function edd_active_tax_rates_query_clauses( $clauses ) {
	$date = \Carbon\Carbon::now( edd_get_timezone_id() )->toDateTimeString();

	$clauses['where'] .= "
		AND ( start_date < '{$date}' OR start_date = '0000-00-00 00:00:00' )
		AND ( end_date > '{$date}' OR end_date = '0000-00-00 00:00:00' )
	";

	return $clauses;
}