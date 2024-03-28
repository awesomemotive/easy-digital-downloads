<?php
/**
 * Discount Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Add a discount.
 *
 * @since 1.0
 * @since 3.0 This function has been repurposed. Previously it was an internal admin callback for adding
 *        a discount via the UI. It's now used as a public function for inserting a new discount
 *        into the database.
 *
 * @param array $data Discount data.
 * @return int Discount ID.
 */
function edd_add_discount( $data = array() ) {

	// Juggle requirements and products.
	$product_requirements = isset( $data['product_reqs'] ) ? $data['product_reqs'] : null;
	$excluded_products    = isset( $data['excluded_products'] ) ? $data['excluded_products'] : null;
	$product_condition    = isset( $data['product_condition'] ) ? $data['product_condition'] : null;
	$categories           = ! empty( $data['categories'] ) ? array_map( 'intval', $data['categories'] ) : null;
	$term_condition       = ! empty( $data['term_condition'] ) ? $data['term_condition'] : null;
	$pre_convert_args     = $data;
	unset( $data['product_reqs'], $data['excluded_products'], $data['product_condition'], $data['categories'], $data['term_condition'] );

	if ( ! empty( $data['expiration'] ) ) {
		$data['end_date'] = $data['expiration'];
	}

	if ( ! empty( $data['start'] ) ) {
		$data['start_date'] = $data['start'];
	}

	// Always unset the old keys, even if they were empty.
	unset( $data['expiration'], $data['start'] );

	// Setup the discounts query.
	$discounts = new EDD\Compat\Discount_Query();

	// Attempt to add the discount.
	$discount_id = $discounts->add_item( $data );

	// Maybe add requirements & exclusions.
	if ( ! empty( $discount_id ) ) {

		// Product requirements.
		if ( ! empty( $product_requirements ) ) {
			if ( is_string( $product_requirements ) ) {
				$product_requirements = maybe_unserialize( $product_requirements );
			}

			if ( is_array( $product_requirements ) ) {
				foreach ( array_filter( $product_requirements ) as $product_requirement ) {
					edd_add_adjustment_meta( $discount_id, 'product_requirement', $product_requirement );
				}
			}
		}

		// Excluded products.
		if ( ! empty( $excluded_products ) ) {
			if ( is_string( $excluded_products ) ) {
				$excluded_products = maybe_unserialize( $excluded_products );
			}

			if ( is_array( $excluded_products ) ) {
				foreach ( array_filter( $excluded_products ) as $excluded_product ) {
					edd_add_adjustment_meta( $discount_id, 'excluded_product', $excluded_product );
				}
			}
		}

		if ( ! empty( $product_condition ) ) {
			edd_add_adjustment_meta( $discount_id, 'product_condition', $product_condition );
		}

		if ( ! empty( $categories ) ) {
			edd_add_adjustment_meta( $discount_id, 'categories', $categories );
			if ( ! empty( $term_condition ) ) {
				edd_add_adjustment_meta( $discount_id, 'term_condition', $term_condition );
			}
		}

		// If the end date has passed, mark the discount as expired.
		edd_is_discount_expired( $discount_id );
	}

	/**
	 * Fires after the discount code is inserted. This hook exists for
	 * backwards compatibility purposes. It uses the $pre_convert_args variable
	 * to ensure the arguments maintain backwards compatible array keys.
	 *
	 * @since 2.7
	 *
	 * @param array $pre_convert_args Discount args.
	 * @param int   $return Discount  ID.
	 */
	do_action( 'edd_post_insert_discount', $pre_convert_args, $discount_id );

	// Return the new discount ID.
	return $discount_id;
}

/**
 * Delete a discount.
 *
 * @since 3.0
 *
 * @param int $discount_id Discount ID.
 * @return int
 */
function edd_delete_discount( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );

	// Do not allow for a discount to be deleted if it has been used.
	if ( $discount && 0 < $discount->use_count ) {
		return false;
	}

	$discounts = new EDD\Compat\Discount_Query();

	// Pre-3.0 pre action.
	do_action( 'edd_pre_delete_discount', $discount_id );

	$retval = $discounts->delete_item( $discount_id );

	// Pre-3.0 post action.
	do_action( 'edd_post_delete_discount', $discount_id );

	return $retval;
}

/**
 * Get Discount.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object
 * @since 3.0 Updated to call use new query class.
 *
 * @param int $discount_id Discount ID.
 * @return \EDD_Discount|bool EDD_Discount object or false if not found.
 */
function edd_get_discount( $discount_id = 0 ) {
	$discounts = new EDD\Compat\Discount_Query();

	// Return discount.
	return $discounts->get_item( $discount_id );
}

/**
 * Get discount by code.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object
 * @since 3.0 Updated to call use new query class.
 * @since 3.0 Updated to include a filter.
 *
 * @param string $code Discount code.
 * @return EDD_Discount|bool EDD_Discount object or false if not found.
 */
function edd_get_discount_by_code( $code = '' ) {
	$discount = edd_get_discount_by( 'code', $code );

	/**
	 * Filters the get discount by request.
	 *
	 * @since 3.0
	 *
	 * @param \EDD_Discount $discount     Discount object.
	 * @param string        $code               Discount code.
	 */
	return apply_filters( 'edd_get_discount_by_code', $discount, $code );
}

/**
 * Retrieve discount by a given field
 *
 * @since 2.0
 * @since 2.7 Updated to use EDD_Discount object
 * @since 3.0 Updated to call use new query class.
 *
 * @param string $field The field to retrieve the discount with.
 * @param mixed  $value The value for $field.
 * @return mixed EDD_Discount|bool EDD_Discount object or false if not found.
 */
function edd_get_discount_by( $field = '', $value = '' ) {
	$discounts = new EDD\Compat\Discount_Query();

	// Return discount.
	return $discounts->get_item_by( $field, $value );
}

/**
 * Retrieve discount by a given field
 *
 * @since 2.0
 * @since 2.7 Updated to use EDD_Discount object
 * @since 3.0 Updated to call edd_get_discount()
 *
 * @param int    $discount_id Discount ID.
 * @param string $field The field to retrieve the discount with.
 * @return mixed object|bool EDD_Discount object or false if not found.
 */
function edd_get_discount_field( $discount_id, $field = '' ) {
	$discount = edd_get_discount( $discount_id );

	// Check that field exists.
	return isset( $discount->{$field} )
		? $discount->{$field}
		: null;
}

/**
 * Update a discount
 *
 * @since 3.0
 * @param int   $discount_id Discount ID.
 * @param array $data
 * @return int
 */
function edd_update_discount( $discount_id = 0, $data = array() ) {

	// Pre-3.0 pre action.
	do_action( 'edd_pre_update_discount', $data, $discount_id );

	// Product requirements.
	if ( isset( $data['product_reqs'] ) && ! empty( $data['product_reqs'] ) ) {
		if ( is_string( $data['product_reqs'] ) ) {
			$data['product_reqs'] = maybe_unserialize( $data['product_reqs'] );
		}

		if ( is_array( $data['product_reqs'] ) ) {
			edd_delete_adjustment_meta( $discount_id, 'product_requirement' );

			foreach ( $data['product_reqs'] as $product_requirement ) {
				edd_add_adjustment_meta( $discount_id, 'product_requirement', $product_requirement );
			}
		}

		unset( $data['product_reqs'] );
	} elseif ( isset( $data['product_reqs'] ) ) {
		edd_delete_adjustment_meta( $discount_id, 'product_requirement' );

		// We don't have product conditions when there are no product requirements.
		edd_delete_adjustment_meta( $discount_id, 'product_condition' );
		unset( $data['product_condition'] );
	}

	// Excluded products are handled differently.
	if ( isset( $data['excluded_products'] ) && ! empty( $data['excluded_products'] ) ) {
		if ( is_string( $data['excluded_products'] ) ) {
			$data['excluded_products'] = maybe_unserialize( $data['excluded_products'] );
		}

		if ( is_array( $data['excluded_products'] ) ) {
			edd_delete_adjustment_meta( $discount_id, 'excluded_product' );

			foreach ( $data['excluded_products'] as $excluded_product ) {
				edd_add_adjustment_meta( $discount_id, 'excluded_product', $excluded_product );
			}
		}

		unset( $data['excluded_products'] );
	} elseif ( isset( $data['excluded_products'] ) ) {
		edd_delete_adjustment_meta( $discount_id, 'excluded_product' );
	}

	if ( isset( $data['product_condition'] ) ) {
		$product_condition = sanitize_text_field( $data['product_condition'] );
		edd_update_adjustment_meta( $discount_id, 'product_condition', $product_condition );
	}

	if ( isset( $data['categories'] ) ) {
		$categories = ! empty( $data['categories'] ) ? array_map( 'intval', $data['categories'] ) : false;
		if ( ! empty( $categories ) ) {
			edd_update_adjustment_meta( $discount_id, 'categories', $categories );
			if ( ! empty( $data['term_condition'] ) ) {
				edd_update_adjustment_meta( $discount_id, 'term_condition', sanitize_text_field( $data['term_condition'] ) );
			} else {
				edd_delete_adjustment_meta( $discount_id, 'term_condition' );
			}
		} else {
			edd_delete_adjustment_meta( $discount_id, 'categories' );
			edd_delete_adjustment_meta( $discount_id, 'term_condition' );
		}
		unset( $data['categories'] );
		unset( $data['term_condition'] );
	}

	$discounts = new EDD\Compat\Discount_Query();

	$retval = $discounts->update_item( $discount_id, $data );

	// Pre-3.0 post action.
	do_action( 'edd_post_update_discount', $data, $discount_id );

	return $retval;
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
	// By default we avoid archived discounts.
	$default_args = array(
		'number'         => 30,
		'status__not_in' => array( 'archived' ),
	);

	// If any of the passed arguments include a status query, remove our default status__not_in.
	if ( isset( $args['status'] ) || isset( $args['status__not_in'] ) || isset( $args['status__in'] ) ) {
		unset( $default_args['status__not_in'] );
	}

	// If there is a search supplied, clear out any status checks.
	if ( isset( $args['search'] ) ) {
		unset( $default_args['status__not_in'] );
		unset( $default_args['status__in'] );
		unset( $default_args['status'] );
	}

	// Parse arguments.
	$r = wp_parse_args( $args, $default_args );

	// Back compat for old query arg.
	if ( isset( $r['posts_per_page'] ) ) {
		$r['number'] = $r['posts_per_page'];
	}

	// Instantiate a query object.
	$discounts = new EDD\Compat\Discount_Query();

	// Return discounts.
	return $discounts->query( $r );
}

/**
 * Return total number of discounts
 *
 * @since 3.0
 *
 * @param array $args Arguments.
 * @return int
 */
function edd_get_discount_count( $args = array() ) {

	// Parse args.
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s).
	$discounts = new EDD\Compat\Discount_Query( $r );

	// Return count(s).
	return absint( $discounts->found_items );
}

/**
 * Query for and return array of discount counts, keyed by status.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_discount_counts( $args = array() ) {

	// Parse arguments.
	$r = wp_parse_args(
		$args,
		array(
			'count'   => true,
			'groupby' => 'status',
		)
	);

	// Query for count.
	$counts = new EDD\Compat\Discount_Query( $r );

	// Format & return.
	return edd_format_counts( $counts, $r['groupby'] );
}

/**
 * Query for discount notes.
 *
 * @since 3.0
 *
 * @param int $discount_id Discount ID.
 * @return array Retrieved notes.
 */
function edd_get_discount_notes( $discount_id = 0 ) {
	return edd_get_notes(
		array(
			'object_id'   => $discount_id,
			'object_type' => 'discount',
			'order'       => 'asc',
		)
	);
}

/**
 * Checks if there is any active discounts, returns a boolean.
 *
 * @since 1.0
 * @since 3.0 Updated to be more efficient and make direct calls to the EDD_Discount object.
 *
 * @return bool
 */
function edd_has_active_discounts() {

	/**
	 * Get discounts that are not expired, inactive, or archived, but also that
	 * either have no start_date or have a start_date that is in the past, and also have
	 * no end_date or have an end_date that is in the future.
	 *
	 * Searching by date is done with WP_Date_Query.
	 */
	$discount_arguments = array(
		'number'            => 1,
		'type'              => 'discount',
		'status__not_in'    => array( 'expired', 'inactive', 'archived' ),
		'max_uses__compare' => array(
			'relation' => 'OR',
			array(
				'value'   => 'use_count',
				'compare' => '>',
			),
			array(
				'value'   => 0,
				'compare' => '=',
			),
		),
		'date_query'        => array(
			'relation' => 'AND',
			array(
				'relation' => 'OR',
				array(
					'column'    => 'start_date',
					'before'    => 'now',
					'inclusive' => true,
				),
				array(
					'column'  => 'start_date',
					'compare' => 'IS NULL',
				),
			),
			array(
				'relation' => 'OR',
				array(
					'column'    => 'end_date',
					'after'     => 'now',
					'inclusive' => true,
				),
				array(
					'column'  => 'end_date',
					'compare' => 'IS NULL',
				),
			),
		),
	);

	return ! empty( edd_get_adjustments( $discount_arguments ) );
}

/**
 * Stores a discount code. If the code already exists, it updates it, otherwise
 * it creates a new one.
 *
 * @internal This method exists for backwards compatibility. `edd_add_discount()` should be used.
 *
 * @since      1.0
 * @since      2.7 Updated to use EDD_Discount object.
 * @since      3.0 Updated to use new query class.
 * @deprecated 3.0 Use edd_add_discount()
 *
 * @param array $details     Discount args.
 * @param int   $discount_id Discount ID.
 * @return mixed bool|int The discount ID of the discount code, or false on failure.
 */
function edd_store_discount( $details, $discount_id = null ) {

	edd_debug_log(
		sprintf(
			/* translators: 1: Function name, 2: Version number, 3: Replacement function name */
			__( '%1$s is deprecated since Easy Digital Downloads version %2$s! Use %3$s instead.', 'easy-digital-downloads' ),
			__FUNCTION__,
			'3.0',
			'edd_add_discount()'
		),
		true
	);

	// Back-compat for start date.
	if ( isset( $details['start'] ) && strstr( $details['start'], '/' ) ) {
		$time_format           = date( 'H:i:s', strtotime( $details['start'] ) );
		$date_format           = date( 'Y-m-d', strtotime( $details['start'] ) ) . ' ' . $time_format;
		$details['start_date'] = edd_get_utc_equivalent_date( EDD()->utils->date( $date_format, edd_get_timezone_id(), false ) );
	}

	// Back-compat for end date.
	if ( isset( $details['expiration'] ) && strstr( $details['expiration'], '/' ) ) {
		$time_format         = date( 'H:i:s', strtotime( $details['expiration'] ) );
		$date_format         = date( 'Y-m-d', strtotime( $details['expiration'] ) ) . ' ' . ( '00:00:00' !== $time_format ? $time_format : '23:59:59' );
		$details['end_date'] = edd_get_utc_equivalent_date( EDD()->utils->date( $date_format, edd_get_timezone_id(), false ) );
	}

	// Always unset the old keys, even if they were empty.
	unset( $details['start'], $details['expiration'] );

	/**
	 * Filters the args before being inserted into the database. This hook
	 * exists for backwards compatibility purposes.
	 *
	 * @since 2.7
	 *
	 * @param array $details Discount args.
	 */
	$details = apply_filters( 'edd_insert_discount', $details );

	/**
	 * Fires before the discount has been added to the database. This hook
	 * exists for backwards compatibility purposes. It fires before the
	 * call to `EDD_Discount::convert_legacy_args` to ensure the arguments
	 * maintain backwards compatible array keys.
	 *
	 * @since 2.7
	 *
	 * @param array $details Discount args.
	 */
	do_action( 'edd_pre_insert_discount', $details );

	// Convert legacy arguments to new ones accepted by `edd_add_discount()`.
	$details = EDD_Discount::convert_legacy_args( $details );

	if ( null === $discount_id ) {
		return (int) edd_add_discount( $details );
	}

	edd_update_discount( $discount_id, $details );
	return $discount_id;
}

/**
 * Deletes a discount code.
 *
 * @internal This method exists for backwards compatibility. `edd_delete_discount()` should be used.
 *
 * @since 1.0
 * @deprecated 3.0
 *
 * @param int $discount_id Discount ID.
 */
function edd_remove_discount( $discount_id = 0 ) {
	edd_delete_discount( $discount_id );
}

/**
 * Updates a discount status from one status to another.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount()
 *
 * @param int    $discount_id Discount ID (default: 0)
 * @param string $new_status  New status (default: active)
 *
 * @return bool Whether the status has been updated or not.
 */
function edd_update_discount_status( $discount_id = 0, $new_status = 'active' ) {

	// Bail if an invalid ID is passed.
	if ( $discount_id <= 0 ) {
		return false;
	}

	// Set defaults.
	$updated    = false;
	$new_status = sanitize_key( $new_status );
	$discount   = edd_get_discount( $discount_id );

	// No change.
	if ( $new_status === $discount->status ) {
		return true;
	}

	// Try to update status.
	if ( ! empty( $discount->id ) ) {
		$updated = (bool) edd_update_discount(
			$discount->id,
			array(
				'status' => $new_status,
			)
		);
	}

	// Return.
	return $updated;
}

/**
 * Checks to see if a discount code already exists.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount().
 *
 * @param int $discount_id Discount ID.
 *
 * @return bool Whether or not the discount exists.
 */
function edd_discount_exists( $discount_id ) {
	$discount = edd_get_discount( $discount_id );

	return $discount instanceof EDD_Discount && $discount->exists();
}

/**
 * Checks whether a discount code is active.
 *
 * @since 1.0
 * @since 2.6.11 Added $update parameter.
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount().
 *
 * @param int  $discount_id Discount ID.
 * @param bool $update      Update the discount to expired if an one is found but has an active status/
 * @param bool $set_error   Whether an error message should be set in session.
 * @return bool Whether or not the discount is active.
 */
function edd_is_discount_active( $discount_id = 0, $update = true, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );

	if ( ! $discount instanceof EDD_Discount ) {
		return false;
	}

	return $discount->is_active( $update, $set_error );
}

/**
 * Retrieve the discount code.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_field()
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
 * @since 3.0 Updated to call edd_get_discount_field()
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
 * @since 3.0 Updated to call edd_get_discount_field()
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
 * @since 3.0 Updated to call edd_get_discount_field()
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
 * @since 3.0 Updated to call edd_get_discount_field().
 *
 * @param int $discount_id Discount ID.
 * @return int $uses Number of times a discount has been used.
 */
function edd_get_discount_uses( $discount_id = 0 ) {
	return (int) edd_get_discount_field( $discount_id, 'use_count' );
}

/**
 * Retrieve the minimum purchase amount for a discount.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_field().
 *
 * @param int $discount_id Discount ID.
 * @return float $min_price Minimum purchase amount.
 */
function edd_get_discount_min_price( $discount_id = 0 ) {
	return edd_format_amount( edd_get_discount_field( $discount_id, 'min_charge_amount' ) );
}

/**
 * Retrieve the discount amount.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_field().
 *
 * @param int $discount_id Discount ID.
 * @return float $amount Discount amount.
 */
function edd_get_discount_amount( $discount_id = 0 ) {
	return edd_get_discount_field( $discount_id, 'amount' );
}

/**
 * Retrieve the discount type
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_field().
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
 * @since 3.0 Updated to call edd_get_discount()
 *
 * @param int $discount_id Discount ID.
 * @return array $excluded_products IDs of the required products.
 */
function edd_get_discount_excluded_products( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );

	return $discount instanceof EDD_Discount ? $discount->excluded_products : array();
}

/**
 * Retrieve the discount product requirements.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount()
 *
 * @param int $discount_id Discount ID.
 * @return array $product_reqs IDs of the required products.
 */
function edd_get_discount_product_reqs( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );

	return $discount instanceof EDD_Discount ? $discount->product_reqs : array();
}

/**
 * Retrieve the product condition.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_field()
 *
 * @param int $discount_id Discount ID.
 *
 * @return string Product condition.
 */
function edd_get_discount_product_condition( $discount_id = 0 ) {
	$discount = edd_get_discount( $discount_id );

	return $discount instanceof EDD_Discount ? $discount->product_condition : '';
}

/**
 * Retrieves the discount status label.
 *
 * @since 2.9
 *
 * @param int $discount_id Discount ID.
 * @return string Product condition.
 */
function edd_get_discount_status_label( $discount_id = null ) {
	$discount = edd_get_discount( $discount_id );

	return $discount instanceof EDD_Discount ? $discount->get_status_label() : '';
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
	return ( 'not_global' === edd_get_discount_field( $discount_id, 'scope' ) );
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
 * @since 3.0 Updated to call edd_get_discount()
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
 * @since 3.0 Updated to call edd_get_discount()
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
 * @since 3.0 Updated to call edd_get_discount()
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
 * @since 3.0 Updated to call edd_get_discount()
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
 * @since 3.0 Updated to call edd_get_discount_field()
 *
 * @param int $discount_id Discount ID.
 *
 * @return bool Whether the discount is single use or not.
 */
function edd_discount_is_single_use( $discount_id = 0 ) {
	return (bool) edd_get_discount_field( $discount_id, 'once_per_customer' );
}

/**
 * Checks to see if the required products are in the cart
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount()
 *
 * @param int  $discount_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Are required products in the cart for the discount to hold.
 */
function edd_discount_product_reqs_met( $discount_id = 0, $set_error = true ) {
	$discount = edd_get_discount( $discount_id );

	return $discount instanceof EDD_Discount && $discount->is_product_requirements_met( $set_error );
}

/**
 * Checks to see if a user has already used a discount.
 *
 * @since 1.1.5
 * @since 1.5 Added $discount_id parameter.
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount()
 *
 * @param string $code      Discount Code.
 * @param string $user      User info.
 * @param int    $discount_id   Discount ID.
 * @param bool   $set_error Whether an error message be set in session
 *
 * @return bool $return Whether the the discount code is used.
 */
function edd_is_discount_used( $code = null, $user = '', $discount_id = 0, $set_error = true ) {
	$discount = is_null( $code )
		? edd_get_discount( $discount_id )
		: edd_get_discount_by_code( $code );

	return $discount instanceof EDD_Discount && $discount->is_used( $user, $set_error );
}

/**
 * Check whether a discount code is valid (when purchasing).
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_by_code()
 *
 * @param string $code      Discount Code.
 * @param string $user      User info.
 * @param bool   $set_error Whether an error message be set in session.
 * @return bool Whether the discount code is valid.
 */
function edd_is_discount_valid( $code = '', $user = '', $set_error = true ) {
	$discount = edd_get_discount_by_code( $code );

	if ( ! empty( $discount->id ) ) {
		// We found a discount code, so check it's validity.
		return $discount->is_valid( $user, $set_error );
	}

	if ( true === $set_error ) {
		// We didn't find a discount, so set the default error.
		edd_set_error( 'edd-discount-error', _x( 'This discount is invalid.', 'error for when a discount is invalid based on its configuration', 'easy-digital-downloads' ) );
	}

	return false;
}

/**
 * Retrieves a discount ID from the code.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_by_code()
 *
 * @param string $code Discount code.
 * @return int|bool Discount ID, or false if discount does not exist.
 */
function edd_get_discount_id_by_code( $code = '' ) {
	$discount = edd_get_discount_by_code( $code );

	return ( $discount instanceof EDD_Discount ) ? $discount->id : false;
}

/**
 * Get Discounted Amount.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_by_code()
 *
 * @param string           $code       Code to calculate a discount for.
 * @param mixed string|int $base_price Price before discount.
 * @return string Amount after discount.
 */
function edd_get_discounted_amount( $code = '', $base_price = 0 ) {
	$discount = edd_get_discount_by_code( $code );

	return ! empty( $discount->id )
		? $discount->get_discounted_amount( $base_price )
		: $base_price;
}

/**
 * Increases the use count of a discount code.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_by_code()
 *
 * @param string $code Discount code to be incremented.
 * @return int New usage.
 */
function edd_increase_discount_usage( $code = '' ) {
	$discount = edd_get_discount_by_code( $code );

	// Increase if discount exists.
	return ! empty( $discount->id )
		? (int) $discount->increase_usage()
		: false;
}

/**
 * Decreases the use count of a discount code.
 *
 * @since 2.5.7
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Updated to call edd_get_discount_by_code()
 *
 * @param string $code Discount code to be decremented.
 * @return int New usage.
 */
function edd_decrease_discount_usage( $code = '' ) {
	$discount = edd_get_discount_by_code( $code );

	// Decrease if discount exists.
	return ! empty( $discount->id )
		? (int) $discount->decrease_usage()
		: false;
}

/**
 * Format Discount Rate
 *
 * @since 1.0
 * @param string     $type Discount code type
 * @param string|int $amount Discount code amount
 * @return string $amount Formatted amount
 */
function edd_format_discount_rate( $type = '', $amount = '' ) {
	return ( 'flat' === $type )
		? edd_currency_filter( edd_format_amount( $amount ) )
		: edd_format_amount( $amount ) . '%';
}

/**
 * Retrieves a discount amount for an item.
 *
 * Calculates an amount based on the context of other items.
 *
 * @since 3.0
 * @since 3.2.0 updated to use \EDD\Discounts\ItemAmount.
 *
 * @global float $edd_flat_discount_total Track flat rate discount total for penny adjustments.
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2757
 *
 * @param array                    $item {
 *   Order Item data, matching Cart line item format.
 *
 *   @type string $id       Download ID.
 *   @type array  $options {
 *     Download options.
 *
 *     @type string $price_id Download Price ID.
 *   }
 *   @type int    $quantity Purchase quantity.
 * }
 * @param array                    $items     All items (including item being calculated).
 * @param \EDD_Discount[]|string[] $discounts Discount to determine adjustment from.
 *                                            A discount code can be passed as a string.
 * @param int                      $item_unit_price (Optional) Pass in a defined price for a specific context, such as the cart.
 * @return float Discount amount. 0 if Discount is invalid or no Discount is applied.
 */
function edd_get_item_discount_amount( $item, $items, $discounts, $item_unit_price = false ) {

	$item_amount = new EDD\Discounts\ItemAmount( $item, $items, $discounts, $item_unit_price );

	return $item_amount->get_discount_amount();
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

	// Get all active cart discounts.
	if ( edd_multiple_discounts_allowed() ) {
		$discounts = edd_get_cart_discounts();

		// Only one discount allowed per purchase, so override any existing.
	} else {
		$discounts = false;
	}

	if ( $discounts ) {
		$key = array_search( strtolower( $code ), array_map( 'strtolower', $discounts ), true );

		// Can't set the same discount more than once.
		if ( false !== $key ) {
			unset( $discounts[ $key ] );
		}
		$discounts[] = $code;
	} else {
		$discounts   = array();
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
		$key       = array_search( strtoupper( $code ), $discounts, true );

		if ( false !== $key ) {
			unset( $discounts[ $key ] );
		}

		$discounts = implode( '|', array_values( $discounts ) );
		// Update the active discounts.
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
 * Retrieve the currently applied discount.
 *
 * @since 1.4.1
 * @return array $discounts The active discount codes.
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
 * @param array       $item Cart item array
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
 * @return string
 */
function edd_get_cart_discounts_html( $discounts = false ) {
	if ( ! $discounts ) {
		$discounts = EDD()->cart->get_discounts();
	}

	if ( empty( $discounts ) ) {
		return apply_filters( 'edd_get_cart_discounts_html', '', $discounts, 0, '' );
	}

	$html = _n( 'Discount', 'Discounts', count( $discounts ), 'easy-digital-downloads' ) . ':&nbsp;';

	foreach ( $discounts as $discount ) {
		$discount_id     = edd_get_discount_id_by_code( $discount );
		$discount_amount = 0;
		$items           = EDD()->cart->get_contents_details();

		if ( is_array( $items ) && ! empty( $items ) ) {
			foreach ( $items as $key => $item ) {
				$discount_amount += edd_get_item_discount_amount( $item, $items, array( $discount ), $item['item_price'] );
			}
		}

		$type = edd_get_discount_type( $discount_id );
		$rate = edd_format_discount_rate( $type, edd_get_discount_amount( $discount_id ) );

		$remove_url = add_query_arg(
			array(
				'edd_action'    => 'remove_cart_discount',
				'discount_id'   => urlencode( $discount_id ),
				'discount_code' => urlencode( $discount ),
			),
			edd_get_checkout_uri()
		);

		$discount_html   = '';
		$discount_html  .= "<span class=\"edd_discount\">\n";
		$discount_amount = edd_currency_filter( edd_format_amount( $discount_amount ) );
		$discount_html  .= "<span class=\"edd_discount_total\">{$discount}&nbsp;&ndash;&nbsp;{$discount_amount}</span>\n";
		if ( 'percent' === $type ) {
			$discount_html .= "<span class=\"edd_discount_rate\">($rate)</span>\n";
		}
		$discount_html .= sprintf(
			'<a href="%s" data-code="%s" class="edd_discount_remove"><span class="screen-reader-text">%s</span></a>',
			esc_url( $remove_url ),
			esc_attr( $discount ),
			esc_attr__( 'Remove discount', 'easy-digital-downloads' )
		);
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

	// Get ID.
	$discount_id = isset( $_GET['discount_id'] )
		? absint( $_GET['discount_id'] )
		: 0;

	// Get code.
	$discount_code = isset( $_GET['discount_code'] )
		? urldecode( $_GET['discount_code'] )
		: '';

	// Bail if either ID or code are empty.
	if ( empty( $discount_id ) || empty( $discount_code ) ) {
		return;
	}

	// Pre-3.0 pre action.
	do_action( 'edd_pre_remove_cart_discount', $discount_id );

	edd_unset_cart_discount( $discount_code );

	// Pre-3.0 post action.
	do_action( 'edd_post_remove_cart_discount', $discount_id );

	// Redirect.
	edd_redirect( edd_get_checkout_uri() );
}
add_action( 'edd_remove_cart_discount', 'edd_remove_cart_discount' );

/**
 * Checks whether discounts are still valid when removing items from the cart
 *
 * If a discount requires a certain product, and that product is no longer in
 * the cart, the discount is removed.
 *
 * @since 1.5.2
 *
 * @param int $cart_key
 */
function edd_maybe_remove_cart_discount( $cart_key = 0 ) {

	$discounts = edd_get_cart_discounts();

	if ( empty( $discounts ) ) {
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

	// Bail if in admin.
	if ( is_admin() ) {
		return;
	}

	// Array stops the bulk delete of discount codes from storing as a preset_discount.
	if ( empty( $_REQUEST['discount'] ) || is_array( $_REQUEST['discount'] ) ) {
		return;
	}

	$code = preg_replace( '/[^a-zA-Z0-9-_]+/', '', $_REQUEST['discount'] );

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

	// Bail if in admin.
	if ( is_admin() ) {
		return;
	}

	$code = sanitize_text_field( EDD()->session->get( 'preset_discount' ) );

	if ( empty( $code ) ) {
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
 * Validate discount code, optionally against an array of download IDs.
 * Note: this function does not evaluate whether a current user can use the discount,
 * or check the discount minimum cart requirement.
 *
 * @param int   $discount_id  Discount ID.
 * @param array $download_ids Array of download IDs.
 *
 * @return boolean True if discount holds, false otherwise.
 */
function edd_validate_discount( $discount_id = 0, $download_ids = array() ) {

	// Bail if discount ID not passed.
	if ( empty( $discount_id ) ) {
		return false;
	}

	$discount = edd_get_discount( $discount_id );

	// Bail if discount not found.
	if ( ! $discount ) {
		return false;
	}

	// Check if discount is active, started, and not maxed out.
	if ( ! $discount->is_active( true, false ) || ! $discount->is_started( false ) || $discount->is_maxed_out( false ) ) {
		return false;
	}

	$product_requirements = $discount->get_product_reqs();
	$excluded_products    = $discount->get_excluded_products();
	$categories           = $discount->get_categories();

	// Return true if there are no requirements/excluded products set.
	if ( empty( $product_requirements ) && empty( $excluded_products ) && empty( $categories ) ) {
		return true;
	}

	// Use the discount product requirement check for product requirements and exclusions.
	$is_valid = $discount->is_product_requirements_met( false, $download_ids );

	// If the discount is still valid, check the categories.
	if ( $is_valid && ! empty( $categories ) ) {
		$is_valid = $discount->is_valid_for_categories( false, $download_ids );
	}

	/**
	 * Filters the validity of a discount.
	 *
	 * @since 3.0
	 *
	 * @param bool          $is_valid     True if valid, false otherwise.
	 * @param \EDD_Discount $discount     Discount object.
	 * @param array         $download_ids Download IDs to check against.
	 */
	return apply_filters( 'edd_validate_discount', $is_valid, $discount, $download_ids );
}
