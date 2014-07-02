<?php
/**
 * Discount Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Pippin Williamson
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
 * @since 1.0
 * @param array $args Query arguments
 * @return mixed array if discounts exist, false otherwise
 */
function edd_get_discounts( $args = array() ) {
	$defaults = array(
		'post_type'      => 'edd_discount',
		'posts_per_page' => 30,
		'paged'          => null,
		'post_status'    => array( 'active', 'inactive', 'expired' )
	);

	$args = wp_parse_args( $args, $defaults );

	$discounts = get_posts( $args );

	if ( $discounts ) {
		return $discounts;
	}

	if( ! $discounts && ! empty( $args['s'] ) ) {
		// If no discounts are found and we are searching, re-query with a meta key to find discounts by code
		$args['meta_key']     = '_edd_discount_code';
		$args['meta_value']   = $args['s'];
		$args['meta_compare'] = 'LIKE';
		unset( $args['s'] );
		$discounts = get_posts( $args );
	}

	if( $discounts ) {
		return $discounts;
	}

	return false;
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
	$has_active = false;

	$discounts  = edd_get_discounts();

	if ( $discounts) {
		foreach ( $discounts as $discount ) {
			if ( edd_is_discount_active( $discount->ID ) ) {
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
 * Retrieves a complete discount code by discount ID.
 *
 * @since 1.0
 * @param string $discount_id Discount ID
 * @return array
 */
function edd_get_discount( $discount_id = 0 ) {

	if( empty( $discount_id ) ) {
		return false;
	}

	$discount = get_post( $discount_id );

	if ( get_post_type( $discount_id ) != 'edd_discount' ) {
		return false;
	}

	return $discount;
}

/**
 * Get Discount By Code
 *
 * Retrieves all details for a discount by its code.
 *
 * @param string $code
 *
 * @since       1.0
 * @return      int
 */
function edd_get_discount_by_code( $code = '' ) {

	if( empty( $code ) || ! is_string( $code ) ) {
		return false;
	}

	return edd_get_discount_by( 'code', $code );

}

/**
 * Retrieve discount by a given field
 *
 * @since       2.0
 * @param       string $field The field to retrieve the discount with
 * @param       mixed $value The value for $field
 * @return      mixed
 */
function edd_get_discount_by( $field = '', $value = '' ) {

	if( empty( $field ) || empty( $value ) ) {
		return false;
	}

	if( ! is_string( $field ) ) {
		return false;
	}

	switch( strtolower( $field ) ) {

		case 'code':
			$discount = edd_get_discounts( array(
				'meta_key'       => '_edd_discount_code',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $discount ) {
				$discount = $discount[0];
			}

			break;

		case 'id':
			$discount = edd_get_discount( $value );

			break;

		case 'name':
			$discount = query_posts( array(
				'post_type'      => 'edd_discount',
				'name'           => sanitize_title( $value ),
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $discount ) {
				$discount = $discount[0];
			}

			break;

		default:
			return false;
	}

	if( ! empty( $discount ) ) {
		return $discount;
	}

	return false;
}

/**
 * Stores a discount code. If the code already exists, it updates it, otherwise
 * it creates a new one.
 *
 * @since 1.0
 * @param string $details
 * @param int $discount_id
 * @return bool Whether or not discount code was created
 */
function edd_store_discount( $details, $discount_id = null ) {

	$meta = array(
		'code'              => isset( $details['code'] )             ? $details['code']              : '',
		'uses'              => isset( $details['uses'] )             ? $details['uses']              : '',
		'max_uses'          => isset( $details['max'] )              ? $details['max']               : '',
		'amount'            => isset( $details['amount'] )           ? $details['amount']            : '',
		'start'             => isset( $details['start'] )            ? $details['start']             : '',
		'expiration'        => isset( $details['expiration'] )       ? $details['expiration']        : '',
		'type'              => isset( $details['type'] )             ? $details['type']              : '',
		'min_price'         => isset( $details['min_price'] )        ? $details['min_price']         : '',
		'product_reqs'      => isset( $details['products'] )         ? $details['products']          : array(),
		'product_condition' => isset( $details['product_condition'] )? $details['product_condition'] : '',
		'excluded_products' => isset( $details['excluded-products'] )? $details['excluded-products'] : array(),
		'is_not_global'     => isset( $details['not_global'] )       ? $details['not_global']        : false,
		'is_single_use'     => isset( $details['use_once'] )         ? $details['use_once']          : false,
	);

	if( ! empty( $meta['start'] ) ) {
		$meta['start']      = date( 'm/d/Y H:i:s', strtotime( $meta['start'] ) );
	}

	if( ! empty( $meta['expiration'] ) ) {
		$meta['expiration'] = date( 'm/d/Y H:i:s', strtotime(  date( 'm/d/Y', strtotime( $meta['expiration'] ) ) . ' 23:59:59' ) );
		if( ! empty( $meta['start'] ) && $meta['start'] > $meta['expiration'] ) {
			// Set the expiration date to the start date if start is later than expiration
			$meta['expiration'] = $meta['start'];
		}
	}

	if ( edd_discount_exists( $discount_id ) && ! empty( $discount_id ) ) {
		// Update an existing discount

		$details = apply_filters( 'edd_update_discount', $details, $discount_id );

		do_action( 'edd_pre_update_discount', $details, $discount_id );

		wp_update_post( array(
			'ID'          => $discount_id,
			'post_title'  => $details['name'],
			'post_status' => $details['status']
		) );

		foreach( $meta as $key => $value ) {
			update_post_meta( $discount_id, '_edd_discount_' . $key, $value );
		}

		do_action( 'edd_post_update_discount', $details, $discount_id );

		// Discount code updated
		return $discount_id;
	} else {
		// Add the discount

		$details = apply_filters( 'edd_insert_discount', $details );

		do_action( 'edd_pre_insert_discount', $details );

		$discount_id = wp_insert_post( array(
			'post_type'   => 'edd_discount',
			'post_title'  => isset( $details['name'] ) ? $details['name'] : '',
			'post_status' => 'active'
		) );

		foreach( $meta as $key => $value ) {
			update_post_meta( $discount_id, '_edd_discount_' . $key, $value );
		}

		do_action( 'edd_post_insert_discount', $details, $discount_id );

		// Discount code created
		return $discount_id;
	}
}


/**
 * Deletes a discount code.
 *
 * @since 1.0
 * @param int $discount_id Discount ID (default: 0)
 * @return void
 */
function edd_remove_discount( $discount_id = 0 ) {
	do_action( 'edd_pre_delete_discount', $discount_id );

	wp_delete_post( $discount_id, true );

	do_action( 'edd_post_delete_discount', $discount_id );
}


/**
 * Updates a discount's status from one status to another.
 *
 * @since 1.0
 * @param int $code_id Discount ID (default: 0)
 * @param string $new_status New status (default: active)
 * @return bool
 */
function edd_update_discount_status( $code_id = 0, $new_status = 'active' ) {
	$discount = edd_get_discount(  $code_id );

	if ( $discount ) {
		do_action( 'edd_pre_update_discount_status', $code_id, $new_status, $discount->post_status );

		wp_update_post( array( 'ID' => $code_id, 'post_status' => $new_status ) );

		do_action( 'edd_post_update_discount_status', $code_id, $new_status, $discount->post_status );

		return true;
	}

	return false;
}

/**
 * Checks to see if a discount code already exists.
 *
 * @since 1.0
 * @param int $code_id Discount ID
 * @return bool
 */
function edd_discount_exists( $code_id ) {
	if ( edd_get_discount(  $code_id ) ) {
		return true;
	}

	return false;
}

/**
 * Checks whether a discount code is active.
 *
 * @since 1.0
 * @param int $code_id
 * @return bool
 */
function edd_is_discount_active( $code_id = null ) {
	$discount = edd_get_discount(  $code_id );
	$return   = false;

	if ( $discount ) {
		if ( edd_is_discount_expired( $code_id ) ) {
			if( defined( 'DOING_AJAX' ) ) {
				edd_set_error( 'edd-discount-error', __( 'This discount is expired.', 'edd' ) );
			}
		} elseif ( $discount->post_status == 'active' ) {
			$return = true;
		} else {
			if( defined( 'DOING_AJAX' ) ) {
				edd_set_error( 'edd-discount-error', __( 'This discount is not active.', 'edd' ) );
			}
		}
	}

	return apply_filters( 'edd_is_discount_active', $return, $code_id );
}

/**
 * Retrieve the discount code
 *
 * @since 1.4
 * @param int $code_id
 * @return string $code Discount Code
 */
function edd_get_discount_code( $code_id = null ) {
	$code = get_post_meta( $code_id, '_edd_discount_code', true );

	return apply_filters( 'edd_get_discount_code', $code, $code_id );
}

/**
 * Retrieve the discount code start date
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return string $start_date Discount start date
 */
function edd_get_discount_start_date( $code_id = null ) {
	$start_date = get_post_meta( $code_id, '_edd_discount_start', true );

	return apply_filters( 'edd_get_discount_start_date', $start_date, $code_id );
}

/**
 * Retrieve the discount code expiration date
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return string $expiration Discount expiration
 */
function edd_get_discount_expiration( $code_id = null ) {
	$expiration = get_post_meta( $code_id, '_edd_discount_expiration', true );

	return apply_filters( 'edd_get_discount_expiration', $expiration, $code_id );
}

/**
 * Retrieve the maximum uses that a certain discount code
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return int $max_uses Maximum number of uses for the discount code
 */
function edd_get_discount_max_uses( $code_id = null ) {
	$max_uses = get_post_meta( $code_id, '_edd_discount_max_uses', true );

	return (int) apply_filters( 'edd_get_discount_max_uses', $max_uses, $code_id );
}

/**
 * Retrieve number of times a discount has been used
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return int $uses Number of times a discount has been used
 */
function edd_get_discount_uses( $code_id = null ) {
	$uses = get_post_meta( $code_id, '_edd_discount_uses', true );

	return (int) apply_filters( 'edd_get_discount_uses', $uses, $code_id );
}

/**
 * Retrieve the minimum purchase amount for a discount
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return float $min_price Minimum purchase amount
 */
function edd_get_discount_min_price( $code_id = null ) {
	$min_price = get_post_meta( $code_id, '_edd_discount_min_price', true );

	return (float) apply_filters( 'edd_get_discount_min_price', $min_price, $code_id );
}

/**
 * Retrieve the discount amount
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return int $amount Discount code amounts
 * @return float
 */
function edd_get_discount_amount( $code_id = null ) {
	$amount = get_post_meta( $code_id, '_edd_discount_amount', true );

	return (float) apply_filters( 'edd_get_discount_amount', $amount, $code_id );
}

/**
 * Retrieve the discount type
 *
 * @since 1.4
 * @param int $code_id Discount ID
 * @return string $type Discount type
 * @return float
 */
function edd_get_discount_type( $code_id = null ) {
	$type = strtolower( get_post_meta( $code_id, '_edd_discount_type', true ) );

	return apply_filters( 'edd_get_discount_type', $type, $code_id );
}

/**
 * Retrieve the products the discount canot be applied to
 *
 * @since 1.9
 * @param int $code_id Discount ID
 * @return array $excluded_products IDs of the required products
 */
function edd_get_discount_excluded_products( $code_id = null ) {
	$excluded_products = get_post_meta( $code_id, '_edd_discount_excluded_products', true );

	if ( empty( $excluded_products ) || ! is_array( $excluded_products ) ) {
		$excluded_products = array();
	}

	return (array) apply_filters( 'edd_get_discount_excluded_products', $excluded_products, $code_id );
}

/**
 * Retrieve the discount product requirements
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return array $product_reqs IDs of the required products
 */
function edd_get_discount_product_reqs( $code_id = null ) {
	$product_reqs = get_post_meta( $code_id, '_edd_discount_product_reqs', true );

	if ( empty( $product_reqs ) || ! is_array( $product_reqs ) ) {
		$product_reqs = array();
	}

	return (array) apply_filters( 'edd_get_discount_product_reqs', $product_reqs, $code_id );
}

/**
 * Retrieve the product condition
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return string Product condition
 * @return string
 */
function edd_get_discount_product_condition( $code_id = 0 ) {
	return get_post_meta( $code_id, '_edd_discount_product_condition', true );
}

/**
 * Check if a discount is not global
 *
 * By default discounts are applied to all products in the cart. Non global discounts are
 * applied only to the products selected as requirements
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return array $product_reqs IDs of the required products
 * @return bool Whether or not discount code is global
 */
function edd_is_discount_not_global( $code_id = 0 ) {
	return (bool) get_post_meta( $code_id, '_edd_discount_is_not_global', true );
}

/**
 * Is Discount Expired
 *
 * Checks whether a discount code is expired.
 *
 * @param int $code_id
 *
 * @since       1.0
 * @return      bool
 */
function edd_is_discount_expired( $code_id = null ) {
	$discount = edd_get_discount(  $code_id );
	$return   = false;

	if ( $discount ) {
		$expiration = edd_get_discount_expiration( $code_id );
		if ( $expiration ) {
			$expiration = strtotime( $expiration );
			if ( $expiration < current_time( 'timestamp' ) ) {
				// Discount is expired
				edd_update_discount_status( $code_id, 'inactive' );
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
 * @since 1.0
 * @param int $code_id Discount ID
 * @return bool Is discount started?
 */
function edd_is_discount_started( $code_id = null ) {
	$discount = edd_get_discount(  $code_id );
	$return   = false;

	if ( $discount ) {
		$start_date = edd_get_discount_start_date( $code_id );

		if ( $start_date ) {
			$start_date = strtotime( $start_date );

			if ( $start_date < current_time( 'timestamp' ) ) {
				// Discount has pased the start date
				$return = true;
			} else {
				edd_set_error( 'edd-discount-error', __( 'This discount is not active yet.', 'edd' ) );
			}
		} else {
			// No start date for this discount, so has to be true
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
 * @since 1.0
 * @param int $code_id Discount ID
 * @return bool Is discount maxed out?
 */
function edd_is_discount_maxed_out( $code_id = null ) {
	$discount = edd_get_discount(  $code_id );
	$return   = false;

	if ( $discount ) {
		$uses = edd_get_discount_uses( $code_id );
		// Large number that will never be reached
		$max_uses = edd_get_discount_max_uses( $code_id );
		// Should never be greater than, but just in case
		if ( $uses >= $max_uses && ! empty( $max_uses ) ) {
			// Discount is maxed out
			edd_set_error( 'edd-discount-error', __( 'This discount has reached it\'s maximum usage.', 'edd' ) );
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
 * @since 1.1.7
 * @param int $code_id Discount ID
 * @return bool $return
 */
function edd_discount_is_min_met( $code_id = null ) {
	$discount = edd_get_discount(  $code_id );
	$return   = false;

	if ( $discount ) {
		$min         = edd_get_discount_min_price( $code_id );
		$cart_amount = edd_get_cart_subtotal();

		if ( (float) $cart_amount >= (float) $min ) {
			// Minimum has been met
			$return = true;
		} else {
			edd_set_error( 'edd-discount-error', sprintf( __( 'Minimum order of %s not met.', 'edd' ), edd_currency_filter( edd_format_amount( $min ) ) ) );
		}
	}

	return apply_filters( 'edd_is_discount_min_met', $return, $code_id );
}

/**
 * Is the discount limited to a single use per customer?
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return bool $single_Use
 */
function edd_discount_is_single_use( $code_id = 0 ) {
	$single_use = get_post_meta( $code_id, '_edd_discount_is_single_use', true );
	return (bool) apply_filters( 'edd_is_discount_single_use', $single_use, $code_id );
}

/**
 * Checks to see if the required products are in the cart
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return bool $ret Are required products in the cart?
 */
function edd_discount_product_reqs_met( $code_id = null ) {
	$product_reqs = edd_get_discount_product_reqs( $code_id );
	$condition    = edd_get_discount_product_condition( $code_id );
	$excluded_ps  = edd_get_discount_excluded_products( $code_id );
	$cart_items   = edd_get_cart_contents();
	$cart_ids     = $cart_items ? wp_list_pluck( $cart_items, 'id' ) : null;
	$ret          = false;

	if ( empty( $product_reqs ) ) {
		$ret = true;
	}

	// Ensure we have requirements before proceeding
	if ( ! $ret ) {
		switch( $condition ) {
			case 'all' :
				// Default back to true
				$ret = true;

				foreach ( $product_reqs as $download_id ) {
					if ( ! edd_item_in_cart( $download_id ) ) {
						edd_set_error( 'edd-discount-error', __( 'The product requirements for this discount are not met.', 'edd' ) );
						$ret = false;
						break;
					}
				}

				break;

			default : // Any
				foreach ( $product_reqs as $download_id ) {

					if ( edd_item_in_cart( $download_id ) ) {
						$ret = true;
						break;
					}

				}

				if( ! $ret ) {

					edd_set_error( 'edd-discount-error', __( 'The product requirements for this discount are not met.', 'edd' ) );

				}

				break;
		}
	}

	if( $excluded_ps ) {
		// Check that there are products other than excluded ones in the cart
		if( $cart_ids == $excluded_ps ) {
			edd_set_error( 'edd-discount-error', __( 'This discount is not valid for the cart contents.', 'edd' ) );
			$ret = false;
		}
	}

	return (bool) apply_filters( 'edd_is_discount_products_req_met', $ret, $code_id, $condition );
}

/**
 * Is Discount Used
 *
 * Checks to see if a user has already used a discount.
 *
 * @since 1.1.5
 *
 * @param string $code
 * @param string $user
 * @param int $code_id (since 1.5) ID of the discount code to check
 *
 * @return bool $return
 */
function edd_is_discount_used( $code = null, $user = '', $code_id = 0 ) {

	$return     = false;
	$user_found = false;

	if ( empty( $code_id ) ) {
		$code_id = edd_get_discount_id_by_code( $code );
		if( empty( $code_id ) ) {
			return false; // No discount was found
		}
	}

	if ( edd_discount_is_single_use( $code_id ) ) {
		if ( is_email( $user ) ) {
			$user_found = true; // All we need is the email
			$key        = '_edd_payment_user_email';
			$value      = $user;
		} else {
			$user_data = get_user_by( 'login', $user );

			if ( $user_data ) {
				$user_found	= true;
				$key   		= '_edd_payment_user_id';
				$value 		= $user_data->ID;
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
					$payment_meta = edd_get_payment_meta( $payment );
					$user_info    = maybe_unserialize( $payment_meta['user_info'] );
					if ( $user_info['discount'] == $code ) {
						edd_set_error( 'edd-discount-error', __( 'This discount has already been redeemed.', 'edd' ) );
						$return = true;
					}
				}
			}
		}
	}

	return apply_filters( 'edd_is_discount_used', $return, $code, $user );
}

/**
 * Check whether a discount code is valid (when purchasing).
 *
 * @since 1.0
 * @param string $code Discount Code
 * @param string $user User info
 * @return bool
 */
function edd_is_discount_valid( $code = '', $user = '' ) {


	$return      = false;
	$discount_id = edd_get_discount_id_by_code( $code );
	$user        = trim( $user );

	if( edd_get_cart_contents() ) {

		if ( $discount_id ) {
			if (
				edd_is_discount_active( $discount_id ) &&
				edd_is_discount_started( $discount_id ) &&
				!edd_is_discount_maxed_out( $discount_id ) &&
				!edd_is_discount_used( $code, $user, $discount_id ) &&
				edd_discount_is_min_met( $discount_id ) &&
				edd_discount_product_reqs_met( $discount_id )
			) {
				$return = true;
			}
		} else {
			edd_set_error( 'edd-discount-error', __( 'This discount is invalid.', 'edd' ) );
		}

	}

	return apply_filters( 'edd_is_discount_valid', $return, $discount_id, $code, $user );
}


/**
 * Get Discount By Code
 *
 * Retrieves a discount code ID from the code.
 *
 * @since       1.0
 * @param       $code string The discount code to retrieve an ID for
 * @return      int
 */
function edd_get_discount_id_by_code( $code ) {
	$discount = edd_get_discount_by_code( $code );
	if( $discount ) {
		return $discount->ID;
	}
	return false;
}


/**
 * Get Discounted Amount
 *
 * Gets the discounted price.
 *
 * @since 1.0
 * @param string $code Code to calculate a discount for
 * @param string|int $base_price Price before discount
 * @return string $discounted_price Amount after discount
 */
function edd_get_discounted_amount( $code, $base_price ) {
	$amount      = 0;
	$discount_id = edd_get_discount_id_by_code( $code );

	if( $discount_id ) {
		$type        = edd_get_discount_type( $discount_id );
		$rate        = edd_get_discount_amount( $discount_id );

		if ( $type == 'flat' ) {
			// Set amount
			$amount = $base_price - $rate;
			if ( $amount < 0 ) {
				$amount = 0;
			}

		} else {
			// Percentage discount
			$amount = $base_price - ( $base_price * ( $rate / 100 ) );
		}

	}

	return apply_filters( 'edd_discounted_amount', $amount );
}

/**
 * Increase Discount Usage
 *
 * Increases the use count of a discount code.
 *
 * @since 1.0
 * @param string $code Discount code to be incremented
 * @return int
 */
function edd_increase_discount_usage( $code ) {

	$id   = edd_get_discount_id_by_code( $code );
	$uses = edd_get_discount_uses( $id );

	if ( $uses ) {
		$uses++;
	} else {
		$uses = 1;
	}

	update_post_meta( $id, '_edd_discount_uses', $uses );

	do_action( 'edd_discount_increase_use_count', $uses, $id, $code );

	return $uses;

}

/**
 * Format Discount Rate
 *
 * @since 1.0
 * @param string $type Discount code type
 * @param string|int $amount Discount code amount
 * @return string $amount Formatted amount
 */
function edd_format_discount_rate( $type, $amount ) {
	if ( $type == 'flat' ) {
		return edd_currency_filter( edd_format_amount( $amount ) );
	} else {
		return $amount . '%';
	}
}

/**
 * Set the active discount for the shopping cart
 *
 * @since 1.4.1
 * @param string $code Discount code
 * @return array All currently active discounts
 */
function edd_set_cart_discount( $code = '' ) {

	if( edd_multiple_discounts_allowed() ) {
		// Get all active cart discounts
		$discounts = edd_get_cart_discounts();
	} else {
		$discounts = false; // Only one discount allowed per purchase, so override any existing
	}

	if ( $discounts ) {
		$key = array_search( $code, $discounts );
		if( false !== $key ) {
			unset( $discounts[ $key ] ); // Can't set the same discount more than once
		}
		$discounts[] = $code;
	} else {
		$discounts = array();
		$discounts[] = $code;
	}

	EDD()->session->set( 'cart_discounts', implode( '|', $discounts ) );

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
		$key = array_search( $code, $discounts );
		unset( $discounts[ $key ] );
		$discounts = implode( '|', array_values( $discounts ) );
		// update the active discounts
		EDD()->session->set( 'cart_discounts', $discounts );
	}

	return $discounts;
}

/**
 * Remove all active discounts
 *
 * @since 1.4.1
 * @return void
 */
function edd_unset_all_cart_discounts() {
	EDD()->session->set( 'cart_discounts', null );
}

/**
 * Retrieve the currently applied discount
 *
 * @since 1.4.1
 * @return array $discounts The active discount codes
 */
function edd_get_cart_discounts() {
	$discounts = EDD()->session->get( 'cart_discounts' );
	$discounts = ! empty( $discounts ) ? explode( '|', $discounts ) : false;
	return $discounts;
}

/**
 * Check if the cart has any active discounts applied to it
 *
 * @since 1.4.1
 * @return bool
 */
function edd_cart_has_discounts() {
	$ret = false;

	if ( edd_get_cart_discounts() ) {
		$ret = true;
	}

	return apply_filters( 'edd_cart_has_discounts', $ret );
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

	$amount = 0;
	$items  = edd_get_cart_content_details();
	if( $items ) {

		$discounts = wp_list_pluck( $items, 'discount' );

		if( is_array( $discounts ) ) {
			$amount = array_sum( $discounts );
		}

	}

	return apply_filters( 'edd_get_cart_discounted_amount', $amount );
}

/**
 * Get the discounted amount on a price
 *
 * @since 1.9
 * @param array $item Cart item array
 * @return float The discounted amount
 */
function edd_get_cart_item_discount_amount( $item = array() ) {

	$amount           = 0;
	$price            = edd_get_cart_item_price( $item['id'], $item['options'], edd_prices_include_tax() );
	$discounted_price = $price;

	// Retrieve all discounts applied to the cart
	$discounts = edd_get_cart_discounts();

	if( $discounts ) {

		foreach ( $discounts as $discount ) {

			$code_id           = edd_get_discount_id_by_code( $discount );
			$reqs              = edd_get_discount_product_reqs( $code_id );
			$excluded_products = edd_get_discount_excluded_products( $code_id );

			// Make sure requirements are set and that this discount shouldn't apply to the whole cart
			if ( ! empty( $reqs ) && edd_is_discount_not_global( $code_id ) ) {

				// This is a product(s) specific discount

				foreach ( $reqs as $download_id ) {

					if ( $download_id == $item['id'] && ! in_array( $item['id'], $excluded_products ) ) {
						$discounted_price -= $price - edd_get_discounted_amount( $discount, $price );
					}

				}

			} else {

				// This is a global cart discount
				if( ! in_array( $item['id'], $excluded_products ) ) {

					if( 'flat' === edd_get_discount_type( $code_id ) ) {

						/* *
						 * In order to correctly record individual item amounts, global flat rate discounts
						 * are distributed across all cart items. The discount amount is divided by the number
						 * of items in the cart and then a portion is evenly applied to each cart item
						 */

						$discounted_amount = edd_get_discount_amount( $code_id );
						$discounted_amount = ( $discounted_amount / edd_get_cart_quantity() );
						$discounted_price -= $discounted_amount;
					} else {

						$discounted_price -= $price - edd_get_discounted_amount( $discount, $price );
					}

				}

			}

		}

		$amount = ( $price - apply_filters( 'edd_get_cart_item_discounted_amount', $discounted_price, $discounts, $item, $price ) );

		if( 'flat' !== edd_get_discount_type( $code_id ) ) {

			$amount = $amount * $item['quantity'];

		}

	}

	return $amount;

}

/**
 * Outputs the HTML for all discounts applied to the cart
 *
 * @since 1.4.1
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
 * @param bool $discounts
 * @return mixed|void
 */
function edd_get_cart_discounts_html( $discounts = false ) {
	if ( ! $discounts ) {
		$discounts = edd_get_cart_discounts();
	}

	if ( ! $discounts ) {
		return;
	}

	$html = '';

	foreach ( $discounts as $discount ) {
		$discount_id  = edd_get_discount_id_by_code( $discount );
		$rate         = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

		$remove_url   = add_query_arg(
			array(
				'edd_action'    => 'remove_cart_discount',
				'discount_id'   => $discount_id,
				'discount_code' => $discount
			),
			edd_get_checkout_uri()
		);

		$html .= "<span class=\"edd_discount\">\n";
			$html .= "<span class=\"edd_discount_rate\">$discount&nbsp;&ndash;&nbsp;$rate</span>\n";
			$html .= "<a href=\"$remove_url\" data-code=\"$discount\" class=\"edd_discount_remove\"></a>\n";
		$html .= "</span>\n";
	}

	return apply_filters( 'edd_get_cart_discounts_html', $html, $discounts, $rate, $remove_url );
}

/**
 * Show the fully formatted cart discount
 *
 * @since 1.4.1
 * @param bool $formatted
 * @param bool $echo Echo?
 * @return string $amount Fully formatted cart discount
 */
function edd_display_cart_discount( $formatted = false, $echo = false ) {
	$discounts = edd_get_cart_discounts();

	if ( empty( $discounts ) ) {
		return false;
	}

	$discount_id  = edd_get_discount_id_by_code( $discounts[0] );
	$amount       = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

	if ( $echo ) {
		echo $amount;
	}

	return $amount;
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
	global $edd_options;
	$ret = isset( $edd_options['allow_multiple_discounts'] );
	return apply_filters( 'edd_multiple_discounts_allowed', $ret );
}

/**
 * Listens for a discount and automatically applies it if present and valid
 *
 * @since 2.0
 * @return void
 */
function edd_listen_for_cart_discount() {

	if( ! edd_is_checkout() ) {
		return;
	}

	if( empty( $_REQUEST['discount'] ) ) {
		return;
	}

	$code = sanitize_text_field( $_REQUEST['discount'] );

	if( ! edd_is_discount_valid( $code ) ) {
		return;
	}

	edd_set_cart_discount( $code );

}
add_action( 'template_redirect', 'edd_listen_for_cart_discount', 500 );