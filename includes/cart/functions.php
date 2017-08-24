<?php
/**
 * Cart Functions
 *
 * @package     EDD
 * @subpackage  Cart
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the contents of the cart
 *
 * @since 1.0
 * @return array Returns an array of cart contents, or an empty array if no items in the cart
 */
function edd_get_cart_contents() {
	return EDD()->cart->get_contents();
}

/**
 * Retrieve the Cart Content Details
 *
 * Includes prices, tax, etc of all items.
 *
 * @since 1.0
 * @return array $details Cart content details
 */
function edd_get_cart_content_details() {
	return EDD()->cart->get_contents_details();
}

/**
 * Get Cart Quantity
 *
 * @since 1.0
 * @return int Sum quantity of items in the cart
 */
function edd_get_cart_quantity() {
	return EDD()->cart->get_quantity();
}

/**
 * Add To Cart
 *
 * Adds a download ID to the shopping cart.
 *
 * @since 1.0
 *
 * @param int $download_id Download IDs to be added to the cart
 * @param array $options Array of options, such as variable price
 *
 * @return string Cart key of the new item
 */
function edd_add_to_cart( $download_id, $options = array() ) {
	return EDD()->cart->add( $download_id, $options );
}

/**
 * Removes a Download from the Cart
 *
 * @since 1.0
 * @param int $cart_key the cart key to remove. This key is the numerical index of the item contained within the cart array.
 * @return array Updated cart items
 */
function edd_remove_from_cart( $cart_key ) {
	return EDD()->cart->remove( $cart_key );
}

/**
 * Checks to see if an item is already in the cart and returns a boolean
 *
 * @since 1.0
 *
 * @param int   $download_id ID of the download to remove
 * @param array $options
 * @return bool Item in the cart or not?
 */
function edd_item_in_cart( $download_id = 0, $options = array() ) {
	return EDD()->cart->is_item_in_cart( $download_id, $options );
}

/**
 * Get the Item Position in Cart
 *
 * @since 1.0.7.2
 *
 * @param int   $download_id ID of the download to get position of
 * @param array $options array of price options
 * @return bool|int|string false if empty cart |  position of the item in the cart
 */
function edd_get_item_position_in_cart( $download_id = 0, $options = array() ) {
	return EDD()->cart->get_item_position( $download_id, $options );
}

/**
 * Check if quantities are enabled
 *
 * @since 1.7
 * @return bool
 */
function edd_item_quantities_enabled() {
	$ret = edd_get_option( 'item_quantities', false );
	return (bool) apply_filters( 'edd_item_quantities_enabled', $ret );
}

/**
 * Set Cart Item Quantity
 *
 * @since 1.7
 *
 * @param int   $download_id Download (cart item) ID number
 * @param int   $quantity
 * @param array $options Download options, such as price ID
 * @return mixed New Cart array
 */
function edd_set_cart_item_quantity( $download_id = 0, $quantity = 1, $options = array() ) {
	return EDD()->cart->set_item_quantity( $download_id, $quantity, $options );
}

/**
 * Get Cart Item Quantity
 *
 * @since 1.0
 * @param int $download_id Download (cart item) ID number
 * @param array $options Download options, such as price ID
 * @return int $quantity Cart item quantity
 */
function edd_get_cart_item_quantity( $download_id = 0, $options = array() ) {
	return EDD()->cart->get_item_quantity( $download_id, $options );
}

/**
 * Get Cart Item Price
 *
 * @since 1.0
 *
 * @param int   $item_id Download (cart item) ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return string Fully formatted price
 */
function edd_cart_item_price( $item_id = 0, $options = array() ) {
	return EDD()->cart->item_price( $item_id, $options );
}

/**
 * Get Cart Item Price
 *
 * Gets the price of the cart item. Always exclusive of taxes
 *
 * Do not use this for getting the final price (with taxes and discounts) of an item.
 * Use edd_get_cart_item_final_price()
 *
 * @since 1.0
 * @param int   $download_id Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @param bool  $remove_tax_from_inclusive Remove the tax amount from tax inclusive priced products.
 * @return float|bool Price for this item
 */
function edd_get_cart_item_price( $download_id = 0, $options = array(), $remove_tax_from_inclusive = false ) {
	return EDD()->cart->get_item_price( $download_id, $options, $remove_tax_from_inclusive );
}

/**
 * Get cart item's final price
 *
 * Gets the amount after taxes and discounts
 *
 * @since 1.9
 * @param int    $item_key Cart item key
 * @return float Final price for the item
 */
function edd_get_cart_item_final_price( $item_key = 0 ) {
	return EDD()->cart->get_item_final_price( $item_key );
}

/**
 * Get cart item tax
 *
 * @since 1.9
 * @param array $download_id Download ID
 * @param array $options Cart item options
 * @param float $subtotal Cart item subtotal
 * @return float Tax amount
 */
function edd_get_cart_item_tax( $download_id = 0, $options = array(), $subtotal = '' ) {
	return EDD()->cart->get_item_tax( $download_id, $options, $subtotal );
}

/**
 * Get Price Name
 *
 * Gets the name of the specified price option,
 * for variable pricing only.
 *
 * @since 1.0
 *
 * @param       $download_id Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return mixed|void Name of the price option
 */
function edd_get_price_name( $download_id = 0, $options = array() ) {
	$return = false;
	if( edd_has_variable_prices( $download_id ) && ! empty( $options ) ) {
		$prices = edd_get_variable_prices( $download_id );
		$name   = false;
		if( $prices ) {
			if( isset( $prices[ $options['price_id'] ] ) )
				$name = $prices[ $options['price_id'] ]['name'];
		}
		$return = $name;
	}
	return apply_filters( 'edd_get_price_name', $return, $download_id, $options );
}

/**
 * Get cart item price id
 *
 * @since 1.0
 *
 * @param array $item Cart item array
 * @return int Price id
 */
function edd_get_cart_item_price_id( $item = array() ) {
	return EDD()->cart->get_item_price_id( $item );
}

/**
 * Get cart item price name
 *
 * @since 1.8
 * @param int $item Cart item array
 * @return string Price name
 */
function edd_get_cart_item_price_name( $item = array() ) {
	return EDD()->cart->get_item_price_name( $item );
}

/**
 * Get cart item title
 *
 * @since 2.4.3
 * @param int $item Cart item array
 * @return string item title
 */
function edd_get_cart_item_name( $item = array() ) {
	return EDD()->cart->get_item_name( $item );
}

/**
 * Cart Subtotal
 *
 * Shows the subtotal for the shopping cart (no taxes)
 *
 * @since 1.4
 * @return float Total amount before taxes fully formatted
 */
function edd_cart_subtotal() {
	return EDD()->cart->subtotal();
}

/**
 * Get Cart Subtotal
 *
 * Gets the total price amount in the cart before taxes and before any discounts
 * uses edd_get_cart_contents().
 *
 * @since 1.3.3
 * @return float Total amount before taxes
 */
function edd_get_cart_subtotal() {
	return EDD()->cart->get_subtotal();
}

/**
 * Get Cart Discountable Subtotal.
 *
 * @return float Total discountable amount before taxes
 */
function edd_get_cart_discountable_subtotal( $code_id ) {
	return EDD()->cart->get_discountable_subtotal( $code_id );
}

/**
 * Get cart items subtotal
 * @param array $items Cart items array
 *
 * @return float items subtotal
 */
function edd_get_cart_items_subtotal( $items ) {
	return EDD()->cart->get_items_subtotal( $items );
}
/**
 * Get Total Cart Amount
 *
 * Returns amount after taxes and discounts
 *
 * @since 1.4.1
 * @param bool $discounts Array of discounts to apply (needed during AJAX calls)
 * @return float Cart amount
 */
function edd_get_cart_total( $discounts = false ) {
	return EDD()->cart->get_total( $discounts );
}


/**
 * Get Total Cart Amount
 *
 * Gets the fully formatted total price amount in the cart.
 * uses edd_get_cart_amount().
 *
 * @since 1.3.3
 *
 * @param bool $echo
 * @return mixed|string|void
 */
function edd_cart_total( $echo = true ) {
	if ( ! $echo ) {
		return EDD()->cart->total( $echo );
	}

	EDD()->cart->total( $echo );
}

/**
 * Check if cart has fees applied
 *
 * Just a simple wrapper function for EDD_Fees::has_fees()
 *
 * @since 1.5
 * @param string $type
 * @uses EDD()->fees->has_fees()
 * @return bool Whether the cart has fees applied or not
 */
function edd_cart_has_fees( $type = 'all' ) {
	return EDD()->fees->has_fees( $type );
}

/**
 * Get Cart Fees
 *
 * Just a simple wrapper function for EDD_Fees::get_fees()
 *
 * @since 1.5
 * @param string $type
 * @param int $download_id
 * @uses EDD()->fees->get_fees()
 * @return array All the cart fees that have been applied
 */
function edd_get_cart_fees( $type = 'all', $download_id = 0, $price_id = NULL ) {
	return EDD()->cart->get_fees( $type, $download_id, $price_id );
}

/**
 * Get Cart Fee Total
 *
 * Just a simple wrapper function for EDD_Fees::total()
 *
 * @since 1.5
 * @uses EDD()->fees->total()
 * @return float Total Cart Fees
 */
function edd_get_cart_fee_total() {
	return EDD()->cart->get_total_fees();
}

/**
 * Get cart tax on Fees
 *
 * @since 2.0
 * @uses EDD()->fees->get_fees()
 * @return float Total Cart tax on Fees
 */
function edd_get_cart_fee_tax() {
	return EDD()->cart->get_tax_on_fees();
}

/**
 * Get Purchase Summary
 *
 * Retrieves the purchase summary.
 *
 * @since       1.0
 *
 * @param      $purchase_data
 * @param bool $email
 * @return string
 */
function edd_get_purchase_summary( $purchase_data, $email = true ) {
	$summary = '';

	if ( $email ) {
		$summary .= $purchase_data['user_email'] . ' - ';
	}

	if ( ! empty( $purchase_data['downloads'] ) ) {
		foreach ( $purchase_data['downloads'] as $download ) {
			$summary .= get_the_title( $download['id'] ) . ', ';
		}

		$summary = substr( $summary, 0, -2 );
	}

	return apply_filters( 'edd_get_purchase_summary', $summary, $purchase_data, $email );
}

/**
 * Gets the total tax amount for the cart contents
 *
 * @since 1.2.3
 *
 * @return mixed|void Total tax amount
 */
function edd_get_cart_tax() {
	return EDD()->cart->get_tax();
}

/**
 * Gets the tax rate charged on the cart.
 *
 * @since 2.7
 * @param string $country     Country code for tax rate.
 * @param string $state       State for tax rate.
 * @param string $postal_code Postal code for tax rate. Not used by core, but for developers.
 * @return float Tax rate.
 */
function edd_get_cart_tax_rate( $country = '', $state = '', $postal_code = '' ) {
	$rate = edd_get_tax_rate( $country, $state );
	return apply_filters( 'edd_get_cart_tax_rate', floatval( $rate ), $country, $state, $postal_code );
}

/**
 * Gets the total tax amount for the cart contents in a fully formatted way
 *
 * @since 1.2.3
 * @param bool $echo Whether to echo the tax amount or not (default: false)
 * @return string Total tax amount (if $echo is set to true)
 */
function edd_cart_tax( $echo = false ) {
	if ( ! $echo ) {
		return EDD()->cart->tax( $echo );
	} else {
		EDD()->cart->tax( $echo );
	}
}

/**
 * Add Collection to Cart
 *
 * Adds all downloads within a taxonomy term to the cart.
 *
 * @since 1.0.6
 * @param string $taxonomy Name of the taxonomy
 * @param mixed $terms Slug or ID of the term from which to add ites | An array of terms
 * @return array Array of IDs for each item added to the cart
 */
function edd_add_collection_to_cart( $taxonomy, $terms ) {
	if ( ! is_string( $taxonomy ) ) return false;

	if( is_numeric( $terms ) ) {
		$terms = get_term( $terms, $taxonomy );
		$terms = $terms->slug;
	}

	$cart_item_ids = array();

	$args = array(
		'post_type' => 'download',
		'posts_per_page' => -1,
		$taxonomy => $terms
	);

	$items = get_posts( $args );
	if ( $items ) {
		foreach ( $items as $item ) {
			edd_add_to_cart( $item->ID );
			$cart_item_ids[] = $item->ID;
		}
	}
	return $cart_item_ids;
}

/**
 * Returns the URL to remove an item from the cart
 *
 * @since 1.0
 * @global $post
 * @param int $cart_key Cart item key
 * @return string $remove_url URL to remove the cart item
 */
function edd_remove_item_url( $cart_key ) {
	return EDD()->cart->remove_item_url( $cart_key );
}

/**
 * Returns the URL to remove an item from the cart
 *
 * @since 1.0
 * @global $post
 * @param string $fee_id Fee ID
 * @return string $remove_url URL to remove the cart item
 */
function edd_remove_cart_fee_url( $fee_id = '') {
	return EDD()->cart->remove_fee_url( $fee_id );
}

/**
 * Empties the Cart
 *
 * @since 1.0
 * @uses EDD()->session->set()
 * @return void
 */
function edd_empty_cart() {
	EDD()->cart->empty_cart();
}

/**
 * Store Purchase Data in Sessions
 *
 * Used for storing info about purchase
 *
 * @since 1.1.5
 *
 * @param $purchase_data
 *
 * @uses EDD()->session->set()
 */
function edd_set_purchase_session( $purchase_data = array() ) {
	EDD()->session->set( 'edd_purchase', $purchase_data );
}

/**
 * Retrieve Purchase Data from Session
 *
 * Used for retrieving info about purchase
 * after completing a purchase
 *
 * @since 1.1.5
 * @uses EDD()->session->get()
 * @return mixed array | false
 */
function edd_get_purchase_session() {
	return EDD()->session->get( 'edd_purchase' );
}

/**
 * Checks if cart saving has been disabled
 *
 * @since 1.8
 * @return bool Whether or not cart saving has been disabled
 */
function edd_is_cart_saving_disabled() {
	return ! EDD()->cart->is_saving_enabled();
}

/**
 * Checks if a cart has been saved
 *
 * @since 1.8
 * @return bool
 */
function edd_is_cart_saved() {
	return EDD()->cart->is_saved();
}

/**
 * Process the Cart Save
 *
 * @since 1.8
 * @return bool
 */
function edd_save_cart() {
	return EDD()->cart->save();
}


/**
 * Process the Cart Restoration
 *
 * @since 1.8
 * @return mixed || false Returns false if cart saving is disabled
 */
function edd_restore_cart() {
	return EDD()->cart->restore();
}

/**
 * Retrieve a saved cart token. Used in validating saved carts
 *
 * @since 1.8
 * @return int
 */
function edd_get_cart_token() {
	return EDD()->cart->get_token();
}

/**
 * Delete Saved Carts after one week
 *
 * @since 1.8
 * @global $wpdb
 * @return void
 */
function edd_delete_saved_carts() {
	global $wpdb;

	$start = date( 'Y-m-d', strtotime( '-7 days' ) );
	$carts = $wpdb->get_results(
		"
		SELECT user_id, meta_key, FROM_UNIXTIME(meta_value, '%Y-%m-%d') AS date
		FROM {$wpdb->usermeta}
		WHERE meta_key = 'edd_cart_token'
		", ARRAY_A
	);

	if ( $carts ) {
		foreach ( $carts as $cart ) {
			$user_id    = $cart['user_id'];
			$meta_value = $cart['date'];

			if ( strtotime( $meta_value ) < strtotime( '-1 week' ) ) {
				$wpdb->delete(
					$wpdb->usermeta,
					array(
						'user_id'  => $user_id,
						'meta_key' => 'edd_cart_token'
					)
				);

				$wpdb->delete(
					$wpdb->usermeta,
					array(
						'user_id'  => $user_id,
						'meta_key' => 'edd_saved_cart'
					)
				);
			}
		}
	}
}
add_action( 'edd_weekly_scheduled_events', 'edd_delete_saved_carts' );

/**
 * Generate URL token to restore the cart via a URL
 *
 * @since 1.8
 * @return string UNIX timestamp
 */
function edd_generate_cart_token() {
	return EDD()->cart->generate_token();
}
