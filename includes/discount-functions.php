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

	$discounts_hash = md5( json_encode( $args ) );
	$discounts      = edd_get_discounts_cache( $discounts_hash );

	if ( false === $discounts ) {
		$discounts = get_posts( $args );
		edd_set_discounts_cache( $discounts_hash, $discounts );
	}

	if ( $discounts ) {
		return $discounts;
	}

	// If no discounts are found and we are searching, re-query with a meta key to find discounts by code
	if( ! $discounts && ! empty( $args['s'] ) ) {
		$args['meta_key']     = '_edd_discount_code';
		$args['meta_value']   = $args['s'];
		$args['meta_compare'] = 'LIKE';
		unset( $args['s'] );

		$discounts_hash = md5( json_encode( $args ) );
		$discounts      = edd_get_discounts_cache( $discounts_hash );

		if ( false === $discounts ) {
			$discounts = get_posts( $args );
			edd_set_discounts_cache( $discounts_hash, $discounts );
		}
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
	$discounts = edd_get_discounts(
		array(
			'post_status'    => 'active',
			'posts_per_page' => 100,
			'fields'         => 'ids'
		)
	);

	// When there are no discounts found anymore there are no active ones.
	if ( ! is_array( $discounts ) || array() === $discounts ) {
		return false;
	}

	foreach ( $discounts as $discount ) {
		// If we catch an active one, we can quit and return true.
		if ( edd_is_discount_active( $discount, false ) ) {
			return true;
		}
	}

	return false;
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
	if ( empty( $discount_id ) ) {
		return false;
	}

	$discount = new EDD_Discount( $discount_id );

	if ( ! $discount->ID > 0 ) {
		return false;
	}

	return $discount;
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
	$discount = new EDD_Discount( $code, true );

	if ( ! $discount->ID > 0 ) {
		return false;
	}

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
function edd_get_discount_by( $field = '', $value = '' ) {
	if ( empty( $field ) || empty( $value ) ) {
		return false;
	}

	if( ! is_string( $field ) ) {
		return false;
	}

	switch( strtolower( $field ) ) {
		case 'code':
			$discount = edd_get_discount_by_code( $value );
			break;

		case 'id':
			$discount = edd_get_discount( $value );
			break;

		case 'name':
			$discount = new EDD_Discount( $value, false, true );
			break;

		default:
			return false;
	}

	if ( ! empty( $discount ) ) {
		return $discount;
	}

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
function edd_store_discount( $details, $discount_id = null ) {
	$return = false;

	if ( null == $discount_id ) {
		$discount = new EDD_Discount;
		$discount->add( $details );

		if ( ! empty( $discount->ID ) ) {
			$return = $discount->ID;
		}
	} else {
		$discount = new EDD_Discount( $discount_id );
		$discount->update( $details );
		$return = $discount->ID;
	}

	// If we stored a discount, we need to clear the edd_get_discounts_cache global.
	if ( false !== $return ) {
		global $edd_get_discounts_cache;
		$edd_get_discounts_cache = array();
	}

	return $return;
}

/**
 * Deletes a discount code.
 *
 * @since 1.0
 *
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
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int    $code_id    Discount ID (default: 0)
 * @param string $new_status New status (default: active)
 * @return bool Whether the status has been updated or not.
 */
function edd_update_discount_status( $code_id = 0, $new_status = 'active' ) {
	$updated = false;
	$discount = new EDD_Discount( $code_id );

	if ( $discount && $discount->ID > 0 ) {
		$updated = $discount->update_status( $new_status );
	}

	return $updated;
}

/**
 * Checks to see if a discount code already exists.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return bool Whether or not the discount exists.
 */
function edd_discount_exists( $code_id ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->exists();
}

/**
 * Checks whether a discount code is active.
 *
 * @since 1.0
 * @since 2.6.11 Added $update parameter.
 * @since 2.7    Updated to use EDD_Discount object.
 *
 * @param int  $code_id   Discount ID.
 * @param bool $update    Update the discount to expired if an one is found but has an active status/
 * @param bool $set_error Whether an error message should be set in session.
 * @return bool Whether or not the discount is active.
 */
function edd_is_discount_active( $code_id = null, $update = true, $set_error = true ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_active( $update, $set_error );
}

/**
 * Retrieve the discount code.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return string $code Discount Code.
 */
function edd_get_discount_code( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->code;
}

/**
 * Retrieve the discount code start date.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return string $start Discount start date.
 */
function edd_get_discount_start_date( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->start;
}

/**
 * Retrieve the discount code expiration date.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return string $expiration Discount expiration.
 */
function edd_get_discount_expiration( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->expiration;
}

/**
 * Retrieve the maximum uses that a certain discount code.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return int $max_uses Maximum number of uses for the discount code.
 */
function edd_get_discount_max_uses( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->max_uses;
}

/**
 * Retrieve number of times a discount has been used.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return int $uses Number of times a discount has been used.
 */
function edd_get_discount_uses( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->uses;
}

/**
 * Retrieve the minimum purchase amount for a discount.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return float $min_price Minimum purchase amount.
 */
function edd_get_discount_min_price( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->min_price;
}

/**
 * Retrieve the discount amount.
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return float $amount Discount amount.
 */
function edd_get_discount_amount( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->amount;
}

/**
 * Retrieve the discount type
 *
 * @since 1.4
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return string $type Discount type
 */
function edd_get_discount_type( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->type;
}

/**
 * Retrieve the products the discount canot be applied to.
 *
 * @since 1.9
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return array $excluded_products IDs of the required products.
 */
function edd_get_discount_excluded_products( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->excluded_products;
}

/**
 * Retrieve the discount product requirements.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return array $product_reqs IDs of the required products.
 */
function edd_get_discount_product_reqs( $code_id = null ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->product_reqs;
}

/**
 * Retrieve the product condition.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return string Product condition.
 */
function edd_get_discount_product_condition( $code_id = 0 ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->product_condition;
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
 *
 * @param int $code_id Discount ID.
 * @return boolean Whether or not discount code is not global.
 */
function edd_is_discount_not_global( $code_id = 0 ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_not_global;
}

/**
 * Checks whether a discount code is expired.
 *
 * @since 1.0
 * @since 2.6.11 Added $update parameter.
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $code_id Discount ID.
 * @param bool $update  Update the discount to expired if an one is found but has an active status.
 * @return bool Whether on not the discount has expired.
 */
function edd_is_discount_expired( $code_id = null, $update = true ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_expired( $update );
}

/**
 * Checks whether a discount code is available to use yet (start date).
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $code_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Is discount started?
 */
function edd_is_discount_started( $code_id = null, $set_error = true ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_started( $set_error );
}

/**
 * Is Discount Maxed Out.
 *
 * @since 1.0
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $code_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Is discount maxed out?
 */
function edd_is_discount_maxed_out( $code_id = null, $set_error = true ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_maxed_out( $set_error );
}

/**
 * Checks to see if the minimum purchase amount has been met.
 *
 * @since 1.1.7
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $code_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Whether the minimum amount has been met or not.
 */
function edd_discount_is_min_met( $code_id = null, $set_error = true ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_min_price_met( $set_error );
}

/**
 * Is the discount limited to a single use per customer?
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int $code_id Discount ID.
 * @return bool Whether the discount is single use or not.
 */
function edd_discount_is_single_use( $code_id = 0 ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_single_use;
}

/**
 * Checks to see if the required products are in the cart
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param int  $code_id   Discount ID.
 * @param bool $set_error Whether an error message be set in session.
 * @return bool Are required products in the cart for the discount to hold.
 */
function edd_discount_product_reqs_met( $code_id = null, $set_error = true ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_product_requirements_met( $set_error );
}

/**
 * Checks to see if a user has already used a discount.
 *
 * @since 1.1.5
 * @since 1.5 Added $code_id parameter.
 * @since 2.7 Updated to use EDD_Discount object.
 *
 * @param string $code      Discount Code.
 * @param string $user      User info.
 * @param int    $code_id   Discount ID.
 * @param bool   $set_error Whether an error message be set in session
 * @return bool $return Whether the the discount code is used.
 */
function edd_is_discount_used( $code = null, $user = '', $code_id = 0, $set_error = true ) {
	if ( null == $code ) {
		$discount = new EDD_Discount( $code, true );
	} else {
		$discount = new EDD_Discount( $code_id );
	}

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
	$discount = new EDD_Discount( $code, true );
	return $discount->is_valid( $user, $set_error );
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
function edd_get_discount_id_by_code( $code ) {
	$discount = new EDD_Discount( $code, true );
	return $discount->ID;
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
function edd_get_discounted_amount( $code, $base_price ) {
	$discount = new EDD_Discount( $code, true );
	return $discount->get_discounted_amount( $base_price );
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
function edd_increase_discount_usage( $code ) {
	$discount = new EDD_Discount( $code, true );

	if ( $discount && $discount->ID > 0 ) {
		return $discount->increase_usage();
	} else {
		return false;
	}
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
function edd_decrease_discount_usage( $code ) {
	$discount = new EDD_Discount( $code, true );

	if ( $discount && $discount->ID > 0 ) {
		return $discount->decrease_usage();
	} else {
		return false;
	}
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
 * @return string[] All currently active discounts
 */
function edd_set_cart_discount( $code = '' ) {

	if( edd_multiple_discounts_allowed() ) {
		// Get all active cart discounts
		$discounts = edd_get_cart_discounts();
	} else {
		$discounts = false; // Only one discount allowed per purchase, so override any existing
	}

	if ( $discounts ) {
		$key = array_search( strtolower( $code ), array_map( 'strtolower', $discounts ) );
		if( false !== $key ) {
			unset( $discounts[ $key ] ); // Can't set the same discount more than once
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
 * Note the $formatted paramter was removed from the display_cart_discount() function
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
 * Updates discounts that are expired or at max use (that are not already marked as so) as inactive or expired
 *
 * @since 2.6
 * @return void
*/
function edd_discount_status_cleanup() {
	global $wpdb;

	// We only want to get 25 active discounts to check their status per step here
	$cron_discount_number   = apply_filters( 'edd_discount_status_cleanup_count', 25 );
	$discount_ids_to_update = array();
	$needs_inactive_meta    = array();
	$needs_expired_meta     = array();

	// start by getting the last 25 that hit their maximum usage
	$args = array(
		'suppress_filters' => false,
		'post_status'      => array( 'active' ),
		'posts_per_page'   => $cron_discount_number,
		'order'            => 'ASC',
		'meta_query'       => array(
			'relation' => 'AND',
			array(
				'key'     => '_edd_discount_uses',
				'value'   => 'mt1.meta_value',
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => '_edd_discount_max_uses',
				'value'   => array( '', 0 ),
				'compare' => 'NOT IN',
			),
			array(
				'key'     => '_edd_discount_max_uses',
				'compare' => 'EXISTS',
			),
		),
	);

	add_filter( 'posts_request', 'edd_filter_discount_code_cleanup' );
	$discounts = edd_get_discounts( $args );
	remove_filter( 'posts_request', 'edd_filter_discount_code_cleanup' );

	if ( $discounts ) {
		foreach ( $discounts as $discount ) {

			$discount_ids_to_update[] = (int) $discount->ID;
			$needs_inactive_meta[] = (int) $discount->ID;

		}
	}

	// Now lets look at the last 25 that hit their expiration without hitting their limit
	$args = array(
		'post_status'    => array( 'active' ),
		'posts_per_page' => $cron_discount_number,
		'order'          => 'ASC',
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => '_edd_discount_expiration',
				'value'   => '',
				'compare' => '!=',
			),
			array(
				'key'     => '_edd_discount_expiration',
				'value'   => date( 'm/d/Y H:i:s', current_time( 'timestamp' ) ),
				'compare' => '<',
			),
		),
	);

	$discounts = edd_get_discounts( $args );

	if ( $discounts ) {
		foreach ( $discounts as $discount ) {

			$discount_ids_to_update[] = (int) $discount->ID;
			if ( ! in_array( $discount->ID, $needs_inactive_meta ) ) {
				$needs_expired_meta[] = (int) $discount->ID;
			}

		}
	}

	$discount_ids_to_update = array_unique( $discount_ids_to_update );
	if ( ! empty ( $discount_ids_to_update ) ) {
		$discount_ids_string = "'" . implode( "','", $discount_ids_to_update ) . "'";
		$sql                 = "UPDATE $wpdb->posts SET post_status = 'inactive' WHERE ID IN ($discount_ids_string)";
		$wpdb->query( $sql );
	}

	$needs_inactive_meta = array_unique( $needs_inactive_meta );
	if ( ! empty( $needs_inactive_meta ) ) {
		$inactive_ids = "'" . implode( "','", $needs_inactive_meta ) . "'";
		$sql          = "UPDATE $wpdb->postmeta SET meta_value = 'inactive' WHERE meta_key = '_edd_discount_status' AND post_id IN ($inactive_ids)";
		$wpdb->query( $sql );
	}

	$needs_expired_meta = array_unique( $needs_expired_meta );
	if ( ! empty( $needs_expired_meta ) ) {
		$expired_ids = "'" . implode( "','", $needs_expired_meta ) . "'";
		$sql         = "UPDATE $wpdb->postmeta SET meta_value = 'inactive' WHERE meta_key = '_edd_discount_status' AND post_id IN ($expired_ids)";
		$wpdb->query( $sql );
	}

}

/**
 * Check to see if this set of discounts has been queried for already.
 *
 * @since 2.8.7
 * @param $hash string The hash of the edd_get_discount args.
 *
 * @return bool|mixed  Found discounts if already queried, or false if it has not been queried yet.
 */
function edd_get_discounts_cache( $hash ) {
	global $edd_get_discounts_cache;

	if ( ! is_array( $edd_get_discounts_cache ) ) {
		$edd_get_discounts_cache = array();
	}

	if ( ! isset( $edd_get_discounts_cache[ $hash ] ) ) {
		return false;
	}

	return $edd_get_discounts_cache[ $hash ];
}

/**
 * Store found discounts with the hash.
 * This is a non-persistent cache and uses a PHP global.
 *
 * @since 2.8.7
 * @param $hash string The hash of the arguments from edd_get_discounts.
 * @param $data array  The data to store for this hash.
 */
function edd_set_discounts_cache( $hash, $data ) {
	global $edd_get_discounts_cache;

	if ( ! is_array( $edd_get_discounts_cache ) ) {
		$edd_get_discounts_cache = array();
	}

	$edd_get_discounts_cache[ $hash ] = $data;
}

/**
 * Disabled until https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5619 is completed
 * See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5631
 *
add_action( 'edd_daily_scheduled_events', 'edd_discount_status_cleanup' );
 */

/**
 * Used during edd_discount_status_cleanup to filter out a meta query properly
 *
 * @since  2.6.6
 * @param  string  $sql The unmodified SQL statement.
 * @return string      The sql statement with removed quotes from the column.
 */
function edd_filter_discount_code_cleanup( $sql ) {
	return str_replace( "'mt1.meta_value'", "mt1.meta_value", $sql );
}
