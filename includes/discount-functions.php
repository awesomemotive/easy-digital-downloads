<?php
/**
 * Discount Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Discount Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
function edd_get_discounts( $args = array() ) {

	$defaults = array(
		'post_type'      => 'edd_discount',
		'posts_per_page' => 30,
		'paged'          => null,
		'post_status'    => array( 'active', 'inactive', 'expired' )
	);

	$args = wp_parse_args( $args, $defaults );

	$discounts = get_posts( $args );

	if( $discounts )
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
	if ( $discounts) {
		foreach ( $discounts as $discount ) {
			if ( $discount->post_status == 'active' && ! edd_is_discount_expired( edd_get_discount_code( $discount->ID ) ) ) {
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
function edd_get_discount( $discount_id ) {
	$discount = get_post( $discount_id );

	if( get_post_type( $discount_id ) != 'edd_discount' )
		return false;

	return $discount;
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
 * @return      int
 */
function edd_get_discount_by_code( $code ) {

	$discounts = edd_get_discounts( array(
		'meta_key'       => '_edd_discount_code',
		'meta_value'     => $code,
		'posts_per_page' => 1
	) );
	if( $discounts )
		return $discounts[0];

	return false;
}


/**
 * Store Discount
 *
 * Stores a discount code.
 * If the code exists, it updates it, otherwise it creates a new one.
 *
 * @param string  $discount_details
 * @param int     $discount_id
 *
 * @access      public
 * @since       1.0
 * @return      boolean
 */
function edd_store_discount( $discount_details, $discount_id = null ) {

	if ( edd_discount_exists( $discount_id ) && !is_null( $discount_id ) ) {

		// Update an existing discount

		$discount_details = apply_filters( 'edd_update_discount', $discount_details, $discount_id );

		do_action( 'edd_pre_update_discount', $discount_details, $discount_id );

		wp_update_post( array(
			'ID'          => $discount_id,
			'post_title'  => $discount_details['name'],
			'post_status' => $discount_details['status']
		) );

		$meta = array(
			'code'        => isset( $discount_details['code'] )       ? $discount_details['code']       : '',
			'uses'        => isset( $discount_details['uses'] )       ? $discount_details['uses']       : '',
			'max_uses'    => isset( $discount_details['max'] )        ? $discount_details['max']        : '',
			'amount'      => isset( $discount_details['amount'] )     ? $discount_details['amount']     : '',
			'start'       => isset( $discount_details['start'] )      ? $discount_details['start']      : '',
			'expiration'  => isset( $discount_details['expiration'] ) ? $discount_details['expiration'] : '',
			'type'        => isset( $discount_details['type'] )       ? $discount_details['type']       : '',
			'min_price'   => isset( $discount_details['min_price'] )  ? $discount_details['min_price']  : '',
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $discount_id, '_edd_discount_' . $key, $value );
		}

		do_action( 'edd_post_update_discount', $discount_details, $discount_id );

		// Discount code updated
		return true;

	} else {
		// Add the discount

		$discount_details = apply_filters( 'edd_insert_discount', $discount_details );

		do_action( 'edd_pre_insert_discount', $discount_details );

		$discount_id = wp_insert_post( array(
			'post_type'   => 'edd_discount',
			'post_title'  => isset( $discount_details['name'] ) ? $discount_details['name'] : '',
			'post_status' => 'active'
		) );


		$meta = array(
			'code'        => isset( $discount_details['code'] )       ? $discount_details['code']       : '',
			'uses'        => isset( $discount_details['uses'] )       ? $discount_details['uses']       : '',
			'max_uses'    => isset( $discount_details['max'] )        ? $discount_details['max']        : '',
			'amount'      => isset( $discount_details['amount'] )     ? $discount_details['amount']     : '',
			'start'       => isset( $discount_details['start'] )      ? $discount_details['start']      : '',
			'expiration'  => isset( $discount_details['expiration'] ) ? $discount_details['expiration'] : '',
			'type'        => isset( $discount_details['type'] )       ? $discount_details['type']       : '',
			'min_price'   => isset( $discount_details['min_price'] )  ? $discount_details['min_price']  : '',
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $discount_id, '_edd_discount_' . $key, $value );
		}

		do_action( 'edd_post_insert_discount', $discount_details, $discount_id );

		// Discount code created
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
function edd_remove_discount( $discount_id = 0 ) {

	do_action( 'edd_pre_delete_discount', $discount_id );

	wp_delete_post( $discount_id, true );

	do_action( 'edd_post_delete_discount', $discount_id );

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
function edd_update_discount_status( $code_id = 0, $new_status = 'active' ) {

	$discount = edd_get_discount( $code_id );

	if ( $discount ) {

		do_action( 'edd_pre_update_discount_status', $code_id, $new_status, $discount->post_status );

		wp_update_post( array( 'ID' => $code_id, 'post_status' => $new_status ) );

		do_action( 'edd_post_update_discount_status', $code_id, $new_status, $discount->post_status );

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

	if( edd_get_discount( $code_id ) )
		return true;
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
		if ( $discount->post_status == 'active' && ! edd_is_discount_expired( $code_id ) ) {
			$return = true;
		}
	}

	return apply_filters( 'edd_is_discount_active', $return, $code_id );
}


/**
 * Retrieve the discount code
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      string
 */

function edd_get_discount_code( $code_id = null ) {

	$code = get_post_meta( $code_id, '_edd_discount_code', true );

	return apply_filters( 'edd_get_discount_code', $code, $code_id );

}


/**
 * retrieve discount expiration date
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      string
 */

function edd_get_discount_expiration( $code_id = null ) {

	$expiration = get_post_meta( $code_id, '_edd_discount_expiration', true );

	return apply_filters( 'edd_get_discount_expiration', $expiration, $code_id );

}


/**
 * retrieve discount start date
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      string
 */

function edd_get_discount_start_date( $code_id = null ) {

	$start_date = get_post_meta( $code_id, '_edd_discount_start', true );

	return apply_filters( 'edd_get_discount_start_date', $start_date, $code_id );

}


/**
 * Retrieve discount max uses
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      int
 */

function edd_get_discount_max_uses( $code_id = null ) {

	$max_uses = get_post_meta( $code_id, '_edd_discount_max_uses', true );

	return (int) apply_filters( 'edd_get_discount_max_uses', $max_uses, $code_id );

}


/**
 * Retrieve number of times a discount has been used
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      int
 */

function edd_get_discount_uses( $code_id = null ) {

	$uses = get_post_meta( $code_id, '_edd_discount_uses', true );

	return (int) apply_filters( 'edd_get_discount_uses', $uses, $code_id );

}


/**
 * Retrieve the minimum purchase amount for a discount
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      float
 */

function edd_get_discount_min_price( $code_id = null ) {

	$min_price = get_post_meta( $code_id, '_edd_discount_min_price', true );

	return (float) apply_filters( 'edd_get_discount_min_price', $min_price, $code_id );

}


/**
 * Retrieve the discount amount
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      float
 */

function edd_get_discount_amount( $code_id = null ) {

	$amount = get_post_meta( $code_id, '_edd_discount_amount', true );

	return (float) apply_filters( 'edd_get_discount_amount', $amount, $code_id );

}


/**
 * Retrieve the discount type
 *
 * @param int $code_id
 *
 * @access      public
 * @since       1.4
 * @return      float
 */

function edd_get_discount_type( $code_id = null ) {

	$type = get_post_meta( $code_id, '_edd_discount_type', true );

	return apply_filters( 'edd_get_discount_type', $type, $code_id );

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
		$expiration = edd_get_discount_expiration( $code_id );
		if ( $expiration ) {
			$expiration = strtotime( $expiration );
			if ( $expiration < time() - ( 24 * 60 * 60 ) ) {
				// Discount is expired
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
		$start_date = edd_get_discount_start_date( $code_id );
		if ( $start_date ) {
			$start_date = strtotime( $start_date );
			if ( $start_date < time() ) {
				// Discount has pased the start date
				$return = true;
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
		$uses = edd_get_discount_uses( $code_id );
		// Large number that will never be reached
		$max_uses = edd_get_discount_max_uses( $code_id );
		// Should never be greater than, but just in case
		if ( $uses >= $max_uses && $max_uses != '' && ! empty( $max_uses ) ) {
			// Discount is maxed out
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
		$min         = edd_get_discount_min_price( $code_id );
		$cart_amount = edd_get_cart_subtotal();
		if ( (float) $cart_amount >= (float) $min ) {
			// Minimum has been met
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

		$user_found = true; // All we need is the email
		$key        = '_edd_payment_user_email';
		$value      = $user;

	} else {

		$user_data = get_user_by( 'login', $user );

		if ( $user_data ) {

			$key   = '_edd_payment_user_id';
			$value = $user_data->ID;

		} else {

			$user_found = false; // Bail, no user found
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
 * @param       $code string The discount code to retrieve an ID for
 * @return      int
 */
function edd_get_discount_id_by_code( $code ) {
	$discount = edd_get_discount_by_code( $code );
	if( $discount )
		return $discount->ID;
	return false;
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
	$type        = edd_get_discount_type( $discount_id );
	$rate        = edd_get_discount_amount( $discount_id );

	if ( $type == 'flat' ) {
		// Set amount
		$discounted_price = $base_price - $rate;
		if ( $discounted_price < 0 ) {
			$discounted_price = 0;
		}

	} else {
		// Percentage discount
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
	$uses        = edd_get_discount_uses( $discount_id );

	if ( $uses ) {
		$uses++;
	} else {
		$uses = 1;
	}

	update_post_meta( $discount_id, '_edd_discount_uses', $uses );
	return $uses;
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


/**
 * Set the active discount for the shopping cart
 *
 * @access      public
 * @since       1.4.1
 * @return      array All currently active discounts
 */

function edd_set_cart_discount( $code = '' ) {

	//$discounts = edd_get_cart_discounts();
	// once we fully support multiple discounts, this will retrieve current discounts
	$discounts = false;

	if( $discounts ) {
		$discounts[] = $code;
	} else {
		$discounts = array();
		$discounts[] = $code;
	}

	setcookie( 'wordpress_edd_cart_discount', implode( '|', $discounts ), time()+3600, COOKIEPATH, COOKIE_DOMAIN, false );

	return $discounts;
}


/**
 * Remove an active discount from the shopping cart
 *
 * @access      public
 * @since       1.4.1
 * @return      array All remaining active discounts
 */

function edd_unset_cart_discount( $code = '' ) {
	$discounts = edd_get_cart_discounts();

	if( $discounts ) {
		$key = array_search( $code, $discounts );
		unset( $discounts[ $key ] );
		$discounts = implode( '|', array_values( $discounts ) );
		// update the active discounts
		setcookie( 'wordpress_edd_cart_discount', $discounts, time()+3600, COOKIEPATH, COOKIE_DOMAIN, false );
	}

	return $discounts;
}


/**
 * Remove all active discounts
 *
 * @access      public
 * @since       1.4.1
 * @return      void
 */

function edd_unset_all_cart_discounts() {
	@setcookie( 'wordpress_edd_cart_discount', null, strtotime( '-1 day' ), COOKIEPATH, COOKIE_DOMAIN, false );
}


/**
 * Retrieve the currently applied discount
 *
 * @access      public
 * @since       1.4.1
 * @return      array The active discount codes
 */
function edd_get_cart_discounts() {
	$discounts = isset( $_COOKIE['wordpress_edd_cart_discount'] ) ? explode( '|', $_COOKIE['wordpress_edd_cart_discount'] ) : false;
	return $discounts;
}


/**
 * Check if the cart has any active discounts applied to it
 *
 * @access      public
 * @since       1.4.1
 * @return      bool
 */

function edd_cart_has_discounts() {
	$ret = false;
	if( edd_get_cart_discounts() )
		$ret = true;
	return apply_filters( 'edd_cart_has_discounts', $ret );
}


/**
 * Retrieves the total discounted amount on the cart
 *
 * @access      public
 * @since       1.4.1
 * @return      float
 */

function edd_get_cart_discounted_amount( $discounts = false ) {

	if( empty( $discounts ) )
		$discounts = edd_get_cart_discounts();

	if( ! empty( $_POST['edd-discount'] ) && empty( $discounts ) ) {

		// check for a posted discount
		$posted_discount = isset( $_POST['edd-discount'] ) ? trim( $_POST['edd-discount'] ) : false;

		if( $posted_discount ) {

			$discounts = array();
			$discounts[] = $posted_discount;

		}

	}

	if( empty( $discounts ) )
		return 0.00;

	$subtotal  = edd_get_cart_subtotal();
	$amounts   = array();
	foreach( $discounts as $discount ) {
		$amounts[] = edd_get_discounted_amount( $discount, $subtotal );
	}

	$discount_amount = array_sum( $amounts );
	$subtotal -= $discount_amount;

	return edd_sanitize_amount( $subtotal );
}


/**
 * Outputs the HTML for all discounts applied to the car
 *
 * @access      public
 * @since       1.4.1
 * @return      void
 */

function edd_cart_discounts_html() {
	echo edd_get_cart_discounts_html();
}


/**
 * Retrieves the HTML for all discounts applied to the car
 *
 * @access      public
 * @since       1.4.1
 * @return      string
 */

function edd_get_cart_discounts_html( $discounts = false ) {

	if( ! $discounts )
		$discounts = edd_get_cart_discounts();

	if( ! $discounts )
		return;

	$html = '';

	foreach( $discounts as $discount ) {

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

	return $html;
}


/**
 * Show the fully formatted cart discount
 *
 * @access      public
 * @since       1.4.1
 * @return      string
 */

function edd_display_cart_discount( $formatted = false, $echo = false ) {

	$discounts = edd_get_cart_discounts();
	if( empty( $discounts ) )
		return false;

	$discount_id  = edd_get_discount_id_by_code( $discounts[0] );
	$amount       = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

	if( $echo )
		echo $amount;
	return $amount;
}


/**
 * Processes a remove discount from cart request
 *
 * @access      public
 * @since       1.4.1
 * @return      void
 */

function edd_remove_cart_discount() {
	if( ! isset( $_GET['discount_id'] ) || ! isset( $_GET['discount_code'] ) )
		return;

	do_action( 'edd_pre_remove_cart_discount', absint( $_GET['discount_id'] ) );

	edd_unset_cart_discount( urldecode( $_GET['discount_code'] ) );

	do_action( 'edd_post_remove_cart_discount', absint( $_GET['discount_id'] ) );

	wp_redirect( edd_get_checkout_uri() ); exit;

}
add_action( 'edd_remove_cart_discount', 'edd_remove_cart_discount' );
