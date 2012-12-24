<?php
/**
 * Discount Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Discount Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Discounts
 *
 * Retrieves an array of all available discount codes.
 *
 * @access      public
 * @since       1.0
 * @return      boolean
 */
function edd_get_discounts() {
	$discounts = get_option( 'edd_discounts' );

	if ( false === $discounts ) {
		add_option( 'edd_discounts' );
	}

	if ( $discounts )
		return $discounts;
	return false;
}


/**
 * Has Active Discounts
 *
 * Checks if there is any active discounts, returns a boolean.
 *
 * @access      public
 * @since       1.0
 * @return      boolean
 */
function edd_has_active_discounts() {
	$has_active = false;
	$discounts  = edd_get_discounts();
	if ( is_array( $discounts ) && !empty( $discounts ) ) {
		foreach ( $discounts as $discount ) {
			if ( isset( $discount['status'] ) && $discount['status'] == 'active' && !edd_is_discount_expired( $discount['code'] ) ) {
				$has_active = true;
				break;
			}
		}
	}
	return $has_active;
}


/**
 * Get Discount
 *
 * Retrieves a complete discount code by ID/key.
 *
 * @param string $key
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function edd_get_discount( $key ) {
	$discounts = edd_get_discounts();
	if ( $discounts ) {
		return isset( $discounts[$key] ) ? $discounts[$key] : false;
	}
	return false;
}


/**
 * Get Discount By Code
 *
 * Retrieves all details for a discount by its code.
 *
 * @param string $code
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function edd_get_discount_by_code( $code ) {
	$discounts = edd_get_discounts();
	if ( is_array( $discounts ) ) {
		foreach ( $discounts as $id => $discount ) {
			if ( $discount['code'] == $code ) {
				return $discounts[$id];
			}
		}
	}
	return false;
}


/**
 * Store Discount
 *
 * Stores a discount code.
 * If the code exists, it updates it, otherwise it creates a new one.
 *
 * @param string  $discount_details
 * @param int     $id
 *
 * @access      public
 * @since       1.0
 * @return      boolean
 */
function edd_store_discount( $discount_details, $id = null ) {
	if ( edd_discount_exists( $id ) && !is_null( $id ) ) {

		// update an existing discount
		$discounts = edd_get_discounts();
		if ( !$discounts ) $discounts = array();

		$discounts[$id] = $discount_details;

		apply_filters( 'edd_update_discount', $discount_details, $id );

		update_option( 'edd_discounts', $discounts );

		do_action( 'edd_post_update_discount', $discount_details, $id );

		// discount code updated
		return true;

	} else {
		// add the discount
		$discounts = edd_get_discounts();

		if ( !$discounts ) $discounts = array();

		$discounts[] = $discount_details;

		apply_filters( 'edd_insert_discount', $discount_details );

		update_option( 'edd_discounts', $discounts );

		do_action( 'edd_post_insert_discount', $discount_details );

		// discount code created
		return true;
	}
}


/**
 * Remove Discount
 *
 * Deletes a discount code.
 *
 * @param int $discount_id
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_remove_discount( $discount_id ) {

	$discounts = edd_get_discounts();

	do_action( 'edd_pre_delete_discount', $discount_id );

	unset( $discounts[$discount_id] );

	do_action( 'edd_post_delete_discount', $discount_id );

	update_option( 'edd_discounts', $discounts );
}


/**
 * Update Discount Status
 *
 * Updates a discount's status from one status to another.
 *
 * @param int    $code_id
 * @param string $new_status
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_update_discount_status( $code_id, $new_status ) {
	$discount  = edd_get_discount( $code_id );
	$discounts = edd_get_discounts();

	if ( $discount ) {
		$discounts[$code_id]['status'] = $new_status;
		update_option( 'edd_discounts', $discounts );
		return true;
	}

	return false;
}


/**
 * Discount Exists
 *
 * Checks to see if a discount code already exists.
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_discount_exists( $code_id ) {
	$discounts = edd_get_discounts();

	// no discounts, so the code does not exist
	if ( !$discounts ) return false;

	// a discount with this code has been found
	if ( isset( $discounts[$code_id] ) ) return true;

	// no discount with the specified ID exists
	return false;
}


/**
 * Is Discount Active
 *
 * Checks whether a discount code is active.
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_is_discount_active( $code_id = null ) {
	$discount = edd_get_discount( $code_id );
	$return   = false;

	if ( $discount ) {
		if ( isset( $discount['status'] ) && $discount['status'] == 'active' && !edd_is_discount_expired( $code_id ) ) {
			$return = true;
		}
	}

	return apply_filters( 'edd_is_discount_active', $return, $code_id );
}


/**
 * Is Discount Expired
 *
 * Checks whether a discount code is expired.
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_is_discount_expired( $code_id = null ) {
	$discount = edd_get_discount( $code_id );
	$return   = false;

	if ( $discount ) {
		if ( isset( $discount['expiration'] ) && $discount['expiration'] != '' ) {
			$expiration = strtotime( $discount['expiration'] );
			if ( $expiration < time() - ( 24 * 60 * 60 ) ) {
				// discount is expired
				$return = true;
			}
		}
	}

	return apply_filters( 'edd_is_discount_expired', $return, $code_id );
}


/**
 * Is Discount Started
 *
 * Checks whether a discount code is available yet (start date).
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_is_discount_started( $code_id = null ) {
	$discount = edd_get_discount( $code_id );
	$return   = false;
	if ( $discount ) {
		if ( isset( $discount['start'] ) && $discount['start'] != '' ) {
			$start_date = strtotime( $discount['start'] );
			if ( $start_date < time() ) {
				// discount has pased the start date
				$return = true;
			}
		} else {
			// no start date for this discount, so has to be true
			$return = true;
		}
	}
	return apply_filters( 'edd_is_discount_started', $return, $code_id );
}


/**
 * Is Discount Maxed Out
 *
 * Checks to see if a discount has uses left.
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_is_discount_maxed_out( $code_id = null ) {
	$discount = edd_get_discount( $code_id );
	$return   = false;

	if ( $discount ) {
		$uses = isset( $discount['uses'] ) ? $discount['uses'] : 0;
		// large number that will never be reached
		$max_uses = isset( $discount['max'] ) ? $discount['max'] : 99999999;
		// should never be greater than, but just in case
		if ( $uses >= $max_uses && $max_uses != '' && isset( $discount['max'] ) ) {
			// discount is maxed out
			$return = true;
		}
	}

	return apply_filters( 'edd_is_discount_maxed_out', $return, $code_id );
}


/**
 * Is Cart Minimum Met
 *
 * Checks to see if the minimum purchase amount has been met
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.1.7
 * @return      bool
 */

function edd_discount_is_min_met( $code_id = null ) {
	$discount = edd_get_discount( $code_id );
	$return   = false;

	if ( $discount ) {
		$min         = isset( $discount['min_price'] ) ? $discount['min_price'] : 0;
		$cart_amount = edd_get_cart_amount();

		if ( (float) $cart_amount >= (float) $min ) {
			// minimum has been met
			$return = true;
		}
	}

	return apply_filters( 'edd_is_discount_min_met', $return, $code_id );
}


/**
 * Is Discount Used
 *
 * Checks to see if a user has already used a discount.
 *
 * @param string $code
 * @param string $user
 * @access      public
 * @since       1.1.5
 * @return      bool
 */
function edd_is_discount_used( $code = null, $user = '' ) {

	$return     = false;
	$user_found = true;

	if ( is_email( $user ) ) {

		$user_found = true; // all we need is the email
		$key        = '_edd_payment_user_email';
		$value      = $user;

	} else {

		$user_data = get_user_by( 'login', $user );

		if ( !is_wp_error( $user_data ) ) {

			$key   = '_edd_payment_user_id';
			$value = $user_data->ID;

		} else {

			$user_found = false; // bail, no user found
		}
	}

	if ( $user_found ) {

		$query_args = array(
			'post_type'  => 'edd_payment',
			'meta_query' => array(
				array(
					'key'     => $key,
					'value'   => $value,
					'compare' => '='
				)
			),
			'fields'     => 'ids'
		);

		$payments = get_posts( $query_args ); // Get all payments with matching email

		if ( $payments ) {
			foreach ( $payments as $payment ) {
				// Check all matching payments for discount code.
				$payment_meta = get_post_meta( $payment, '_edd_payment_meta', true );
				$user_info    = maybe_unserialize( $payment_meta['user_info'] );
				if ( $user_info['discount'] == $code ) {
					$return = true;
				}
			}
		}

	}

	return apply_filters( 'edd_is_discount_used', $return, $code, $user );
}


/**
 * Is Discount Valid
 *
 * Check whether a discount code is valid (when purchasing).
 *
 * @param string $code
 * @param string $user
 * @access      public
 * @since       1.0
 * @return      bool
 */
function edd_is_discount_valid( $code = '', $user = '' ) {

	$return      = false;
	$discount_id = edd_get_discount_id_by_code( $code );
	$user        = trim( $user );

	if ( $discount_id !== false ) {
		if (
			edd_is_discount_active( $discount_id ) &&
			edd_is_discount_started( $discount_id ) &&
			!edd_is_discount_maxed_out( $discount_id ) &&
			!edd_is_discount_used( $code, $user ) &&
			edd_discount_is_min_met( $discount_id )
		) {
			$return = true;
		}
	}

	return apply_filters( 'edd_is_discount_valid', $return, $discount_id, $code, $user );
}


/**
 * Get Discount By Code
 *
 * Retrieves a discount code ID from the code.
 *
 * @access      public
 * @since       1.0
 * @param        $code string The discount code to retrieve an ID for
 * @return      int
 */
function edd_get_discount_id_by_code( $code ) {
	$discounts = edd_get_discounts();
	$code_id   = false;
	if ( is_array( $discounts ) ) {
		foreach ( $discounts as $key => $discount ) {
			if ( isset( $discount['code'] ) && trim( $discount['code'] ) === trim( $code ) ) {
				$code_id = $key;
			}
		}
	}
	return $code_id;
}


/**
 * Get Discounted Amount
 *
 * Gets the discounted price.
 *
 * @access      public
 * @since       1.0
 * @param       string     $code        the code to calculate a discount for
 * @param       string|int $base_price  the price before discount
 * @return      string $discounted_price the amount after discount
 */
function edd_get_discounted_amount( $code, $base_price ) {

	$discount_id = edd_get_discount_id_by_code( $code );
	$discounts   = edd_get_discounts();
	$type        = $discounts[$discount_id]['type'];
	$rate        = $discounts[$discount_id]['amount'];

	if ( $type == 'flat' ) {
		// set amount
		$discounted_price = $base_price - $rate;
		if ( $discounted_price < 0 ) {
			$discounted_price = 0;
		}

	} else {
		// percentage discount
		$discounted_price = $base_price - ( $base_price * ( $rate / 100 ) );
	}
	return apply_filters( 'edd_discounted_amount', number_format( $discounted_price, 2 ) );
}


/**
 * Increase Discount Usage
 *
 * Increases the use count of a discount code.
 *
 * @access      public
 * @since       1.0
 * @param       $code string - the discount code to be incremented
 * @return      int - the new use count
 */
function edd_increase_discount_usage( $code ) {
	$discount_id = edd_get_discount_id_by_code( $code );
	$discounts   = edd_get_discounts();
	$uses        = isset( $discounts[$discount_id]['uses'] ) ? $discounts[$discount_id]['uses'] : false;

	if ( $uses ) {
		$uses++;
	} else {
		$uses = 1;
	}

	$discounts[$discount_id]['uses'] = $uses;

	return update_option( 'edd_discounts', $discounts );
}


/**
 * Format Discount Rate
 *
 * @param string     $type
 * @param string|int $amount
 * @access      public
 * @since       1.0
 * @return      string
 */
function edd_format_discount_rate( $type, $amount ) {
	if ( $type == 'flat' ) {
		return edd_currency_filter( edd_format_amount( $amount ) );
	} else {
		return $amount . '%';
	}
}