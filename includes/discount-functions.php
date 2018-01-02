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
		'number' => 30,
		'status' => array( 'active', 'inactive', 'expired' )
	);

	$args = wp_parse_args( $args, $defaults );

	if( isset( $args['posts_per_page'] ) ) {
		$args['number'] = $args['posts_per_page'];
	}

	$discounts = EDD()->discounts->get_discounts( $args );

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
	$discounts = edd_get_discounts( array(
		'number' => 1,
		'status' => 'active',
	) );

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

	if ( $discount->exists() ) {
		return $discount;
	}
	return false;
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
		$return = (int) $discount->add( $details );
	} else {
		$discount = new EDD_Discount( $discount_id );
		$discount->update( $details );
		$return = (int) $discount->id;
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

	EDD()->discounts->delete( $discount_id );

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
	return (int) $discount->max_uses;
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
	return (int) $discount->uses;
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
 * Check if a discount is not global.
 *
 * By default discounts are applied to all products in the cart. Non global discounts are
 * applied only to the products selected as requirements.
 *
 * @since 1.5
 * @since 2.7 Updated to use EDD_Discount object.
 * @since 3.0 Please use edd_get_discount_scope() instead.
 * @param int $code_id Discount ID.
 * @return boolean Whether or not discount code is not global.
 */
function edd_is_discount_not_global( $code_id = 0 ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->is_not_global;
}

/**
 * Retrieve the discount scope.
 *
 * By default this will return "global" as discounts are applied to all products in the cart. Non global discounts are
 * applied only to the products selected as requirements.
 *
 * @since 3.0
 *
 * @param int $code_id Discount ID.
 * @return string global or not_global.
 */
function edd_get_discount_scope( $code_id = 0 ) {
	$discount = new EDD_Discount( $code_id );
	return $discount->scope;
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
		return (int) $discount->increase_usage();
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
		return (int) $discount->decrease_usage();
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
 * Backwards compatibility filters for get_post_meta() calls on discount codes.
 *
 * @since  3.4
 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
 * @param  int    $object_id   The object ID post meta was requested for.
 * @param  string $meta_key    The meta key requested.
 * @param  bool   $single      If the person wants the single value or an array of the value
 * @return mixed               The value to return
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
	) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $value;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );
	$discount        = new EDD_Discount( $object_id );

	if( ! $discount || ! $discount->id > 0 ) {

		// We didn't find a discount record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT edd_discount_id FROM {$wpdb->prefix}edd_discountmeta WHERE meta_key = 'legacy_id' AND meta_value = $object_id" );

		if ( ! empty( $object_id ) ) {
			$discount = new EDD_Discount( $object_id );
		} else {
			return $value;
		}
	}

	if( ! $discount || ! $discount->id > 0 ) {
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

			$key = str_replace( '_edd_discount_', '', $meta_key );

			$value = $discount->$key;

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
	) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $check;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );

	$discount = new EDD_Discount( $object_id );

	if ( ! $discount || ! $discount->id > 0 ) {
		// We didn't find a discount record with this ID... so let's check and see if it was a migrated one
		$table_name = EDD()->discount_meta->table_name;

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

	if ( ! $discount || ! $discount->id > 0 ) {
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
add_filter( 'add_post_metadata', '_edd_discount_update_meta_backcompat', 99, 5 );

/**
 * Add a message for anyone to trying to get discounts via get_post/get_posts/WP_Query.
 *
 * @since 3.0
 *
 * @param WP_Query $query
 */
function _edd_discount_get_post_doing_it_wrong( $query ) {
	global $wpdb;

	if ( 'edd_discount' !== $query->get( 'post_type' ) ) {
		return;
	}

	$message = sprintf(
		__( 'As of Easy Digital Downloads 3.0, discounts no longer exist in the %1$s table. They have been migrated to %2$s. Discounts should be accessed using %3$s, %4$s or instantiating a new instance of %5$s. See %6$s for more information.', 'easy-digital-downloads' ),
		'<code>' . $wpdb->posts . '</code>',
		'<code>' . EDD()->discounts->table_name . '</code>',
		'<code>edd_get_discounts()</code>',
		'<code>edd_get_discount()</code>',
		'<code>EDD_Discount</code>',
		'https://easydigitaldownloads.com/development/'
	);

	$stack = print_r( debug_backtrace(), true );

//	edd_debug_log( 'Discounts not queried correctly and not using edd_get_discounts(), edd_get_discount(), or instantiating EDD_Discount object. ' . $stack );

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
					$args['orderby'] = 'id';
					break;
				case 'date':
				case 'post_date':
					$args['orderby'] = 'date_created';
					break;
				default:
					$args['orderby'] = 'id';
					break;
			}
		}

		$offset = $query->get( 'offset', false );
		if ( $offset ) {
			$args['offset'] = $offset;
		}

		if ( 'any' === $args['status'] ) {
			$args['status'] = $defaults['status'];
		}

		$args = wp_parse_args( $args, $defaults );

		$args['offset'] = absint( $args['offset'] );
		$args['number'] = absint( $args['number'] );

		$table_name = EDD()->discounts->table_name;

		$where = '';

		$request = "SELECT id FROM {$table_name} $where ORDER BY {$args['orderby']} {$args['order']} LIMIT {$args['offset']}, {$args['number']};";
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
