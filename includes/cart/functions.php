<?php
/**
 * Cart Functions
 *
 * @package     EDD
 * @subpackage  Cart
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the contents of the cart
 *
 * @since 1.0
 * @return mixed array if cart isn't empty | false otherwise
 */
function edd_get_cart_contents() {
	$cart = EDD()->session->get( 'edd_cart' );
	return ! empty( $cart ) ? apply_filters( 'edd_cart_contents', $cart ) : false;
}

/**
 * Get Cart Quantity
 *
 * @since 1.0
 * @return int $quantity Quantity of one item in the cart
 */
function edd_get_cart_quantity() {
	return ( $cart = edd_get_cart_contents() ) ? count( $cart ) : 0;
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
	$cart = edd_get_cart_contents();
	if ( ! edd_item_in_cart( $download_id, $options ) ) {
		$download = get_post( $download_id );

		if( 'download' != $download->post_type )
			return; // Not a download product

		if ( !current_user_can( 'edit_post', $download->ID ) && ( $download->post_status == 'draft' || $download->post_status == 'pending' ) )
			return; // Do not allow draft/pending to be purchased if can't edit. Fixes #1056

		do_action( 'edd_pre_add_to_cart', $download_id, $options );

		if ( edd_has_variable_prices( $download_id )  && ! isset( $options['price_id'] ) ) {
			// Forces to the first price ID if none is specified and download has variable prices
			$options['price_id'] = 0;
		}

		$to_add = array();

		if ( isset( $options['price_id'] ) && is_array( $options['price_id'] ) ) {
			// Process multiple price options at once
			foreach ( $options['price_id'] as $price ) {
				$price_options = array( 'price_id' => $price );
				$to_add[] = apply_filters( 'edd_add_to_cart_item', array( 'id' => $download_id, 'options' => $price_options ) );
			}
		} else {
			// Add a single item
			$to_add[] = apply_filters( 'edd_add_to_cart_item', array( 'id' => $download_id, 'options' => $options ) );
		}

		if ( is_array( $cart ) ) {
			$cart = array_merge( $cart, $to_add );
		} else {
			$cart = $to_add;
		}

		EDD()->session->set( 'edd_cart', $cart );

		do_action( 'edd_post_add_to_cart', $download_id, $options );

		// Clear all the checkout errors, if any
		edd_clear_errors();

		return count( $cart ) - 1;
	}
}

/**
 * Removes a Download from the Cart
 *
 * @since 1.0
 * @param int $cart_key the cart key to remove
 * @return array Updated cart items
 */
function edd_remove_from_cart( $cart_key ) {
	$cart = edd_get_cart_contents();

	do_action( 'edd_pre_remove_from_cart', $cart_key );

	if ( ! is_array( $cart ) ) {
		return true; // Empty cart
	} else {
		unset( $cart[ $cart_key ] );
	}

	EDD()->session->set( 'edd_cart', $cart );

	do_action( 'edd_post_remove_from_cart', $cart_key );

	// Clear all the checkout errors, if any
	edd_clear_errors();

	return $cart; // The updated cart items
}

/**
 * Checks the see if an item is already in the cart and returns a boolean
 *
 * @since 1.0
 * @param int $download_id ID of the download to remove
 * @return bool Item in the cart or not?
 */
function edd_item_in_cart( $download_id = 0, $options = array() ) {
	$cart_items = edd_get_cart_contents();

	$ret = false;

	if ( is_array( $cart_items ) ) {
		foreach ( $cart_items as $item ) {
			if ( $item['id'] == $download_id ) {
				if ( isset( $options['price_id'] ) && isset( $item['options']['price_id'] ) ) {
					if ( $options['price_id'] == $item['options']['price_id'] ) {
						$ret = true;
						break;
					}
				} else {
					$ret = true;
					break;
				}
			}
		}
	}

	return (bool) apply_filters( 'edd_item_in_cart', $ret, $download_id, $options );
}

/**
 * Get the Item Position in Cart
 *
 * @since 1.0.7.2
 * @param int $download_id ID of the download to get position of
 * @return mixed false if empty cart | int $position position of the item in the cart
 */
function edd_get_item_position_in_cart( $download_id ) {
	$cart_items = edd_get_cart_contents();
	if ( ! is_array( $cart_items ) ) {
		return false; // Empty cart
	} else {
		foreach ( $cart_items as $position => $item ) {
			if ( $item['id'] == $download_id ) {
				return $position;
			}
		}
	}
}

/**
 * Get Cart Item Quantity
 *
 * @since 1.0
 * @param int $item Download (cart item) ID number
 * @return int $quantity Cart item quantity
 */
function edd_get_cart_item_quantity( $item ) {
	$cart        = edd_get_cart_contents();
	$item_counts = array_count_values( $cart );
	return $item_counts[ $item ];
}

/**
 * Get Cart Item Price
 *
 * @since 1.0
 * @param int $item Download (cart item) ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return string Fully formatted price
 */
function edd_cart_item_price( $item_id = 0, $options = array() ) {
	global $edd_options;

	$price = edd_get_cart_item_price( $item_id, $options );
	$label = '';

	if ( edd_is_cart_taxed() ) {

		if ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) {
			$label .= ' ' . __( '(ex. tax)', 'edd' );
		}

		if ( edd_prices_show_tax_on_checkout() && ! edd_prices_include_tax() ) {
			$label .= ' ' . __( '(incl. tax)', 'edd' );
		}

	}

	$price = edd_currency_filter( edd_format_amount( $price ) );

	return esc_html( $price . $label );
}

/**
 * Get Cart Item Price
 *
 * Gets the price of the cart item.
 *
 * @since 1.0
 * @param int $item Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return string Price for this item
 */
function edd_get_cart_item_price( $item_id, $options = array(), $tax = true ) {
	global $edd_options;

	$price = edd_get_download_price( $item_id );

	// If variable prices are enabled, retrieve the options
	$variable_pricing = get_post_meta( $item_id, '_variable_pricing', true) ;

	if ( $variable_pricing && ! empty( $options ) ) {
		// If variable prices are enabled, retrieve the options
		$prices = get_post_meta( $item_id, 'edd_variable_prices', true );
		if ( $prices ) {
			$price = isset( $prices[ $options['price_id'] ] ) ? $prices[ $options['price_id'] ]['amount'] : $price;
		}
	}

	// Determine if we need to add tax toe the price
	if ( $tax &&
		(
			( edd_prices_include_tax() && ! edd_is_cart_taxed() && edd_use_taxes() ) ||
			( edd_is_cart_taxed() && edd_prices_show_tax_on_checkout() || ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) )
		)
	) {
		$price = edd_calculate_tax( $price );
	}

	return apply_filters( 'edd_cart_item_price', $price, $item_id, $options );
}

/**
 * Get Price Name
 *
 * Gets the name of the specified price option,
 * for variable pricing only.
 *
 * @since 1.0
 * @param int $item Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return string Name of the price option
 */
function edd_get_price_name( $item_id, $options = array() ) {
	$return = false;
	$variable_pricing = get_post_meta($item_id, '_variable_pricing', true);
	if( $variable_pricing && !empty( $options ) ) {
		// If variable prices are enabled, retrieve the options
		$prices = get_post_meta( $item_id, 'edd_variable_prices', true );
		$name = false;
		if( $prices ) {
			if( isset( $prices[ $options['price_id'] ] ) )
				$name = $prices[ $options['price_id'] ]['name'];
		}
		$return = $name;
	}
	return apply_filters( 'edd_get_price_name', $return, $item_id, $options );
}


/**
 * Get cart item price id
 *
 * @since 1.0
 * @param int $item Cart item array
 * @return int Price id
 */
function edd_get_cart_item_price_id( $item = array() ) {
	return isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
}


/**
 * Cart Subtotal
 *
 * Shows the subtotal for the shopping cart (no taxes)
 *
 * @since 1.4
 * @global $edd_options Array of all the EDD Options
 * @return float Total amount before taxes fully formatted
 */
function edd_cart_subtotal() {
	global $edd_options;

	$tax = ( ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) || ( ! edd_prices_include_tax() && edd_prices_show_tax_on_checkout() ) );
	$price = esc_html( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ) );

	if ( edd_is_cart_taxed() ) {
		if ( ! edd_prices_show_tax_on_checkout() && ! edd_prices_include_tax() ) {
			$price .= '<br/><span style="font-weight:normal;text-transform:none;">' . __( '(ex. tax)', 'edd' ) . '</span>';
		}

		if ( edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) {
			$price .= '<br/><span style="font-weight:normal;text-transform:none;">' . __( '(incl. tax)', 'edd' ) . '</span>';
		}
	}

	return $price;
}

/**
 * Get Cart Subtotal
 *
 * Gets the total price amount in the cart before taxes and before any discounts
 * uses edd_get_cart_contents().
 *
 * @since 1.3.3
 * @global $edd_options Array of all the EDD Options
 * @param bool $tax Whether tax is enabled or not (default: true)
 * @return float Total amount before taxes
 */
function edd_get_cart_subtotal( $tax = true ) {
	global $edd_options;

	$cart_items = edd_get_cart_contents();
	$amount = 0;

	if ( $cart_items ) {
		foreach ( $cart_items as $item ) {
			$amount += edd_get_cart_item_price( $item['id'], $item['options'], $tax );

		}
	}
	return apply_filters( 'edd_get_cart_subtotal', $amount );
}

/**
 * Check if cart has fees applied
 *
 * Just a simple wrapper function for EDD_Fees::has_fees()
 *
 * @since 1.5
 * @uses EDD()->fees->has_fees()
 * @return bool Whether the cart has fees applied or not
 */
function edd_cart_has_fees() {
	return EDD()->fees->has_fees();
}

/**
 * Get Cart Fees
 *
 * Just a simple wrapper function for EDD_Fees::get_fees()
 *
 * @since 1.5
 * @uses EDD()->fees->get_fees()
 * @return array All the cart fees that have been applied
 */
function edd_get_cart_fees() {
	return EDD()->fees->get_fees();
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
	return EDD()->fees->total();
}

/**
 * Get Cart Amount
 *
 * @since 1.0
 * @param bool $add_taxes Whether to apply taxes (if enabled) (default: true)
 * @param bool $local_override Force the local opt-in param - used for when not reading $_POST (default: false)
 * @return float Total amount
*/
function edd_get_cart_amount( $add_taxes = true, $local_override = false ) {
	$amount = edd_get_cart_subtotal( false );
	if ( ! empty( $_POST['edd-discount'] ) || edd_get_cart_discounts() !== false ) {
		// Retrieve the discount stored in cookies
		$discounts = edd_get_cart_discounts();

		// Check for a posted discount
		$posted_discount = isset( $_POST['edd-discount'] ) ? trim( $_POST['edd-discount'] ) : '';

		if ( $posted_discount && ! in_array( $posted_discount, $discounts ) ) {
			// This discount hasn't been applied, so apply it
			$amount = edd_get_discounted_amount( $posted_discount, $amount );
		}

		if( ! empty( $discounts ) ) {
			// Apply the discounted amount from discounts already applied
			$amount -= edd_get_cart_discounted_amount();
		}
	}

	if ( edd_use_taxes() && edd_is_cart_taxed() && $add_taxes ) {
		$tax = edd_get_cart_tax();
		$amount += $tax;
	}

	return apply_filters( 'edd_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Total Cart Amount
 *
 * Returns amount after taxes and discounts
 *
 * @since 1.4.1
 * @global $edd_options Array of all the EDD Options
 * @param  array $discounts Array of discounts to apply (needed during AJAX calls)
 * @return float Cart amount
 */
function edd_get_cart_total( $discounts = false ) {
	global $edd_options;

	$subtotal = edd_get_cart_subtotal( edd_prices_include_tax() );
	$fees     = edd_get_cart_fee_total();
	$cart_tax = edd_is_cart_taxed() ? edd_get_cart_tax( $discounts ) : 0;
	$discount = edd_get_cart_discounted_amount( $discounts );

	$total    = $subtotal + $fees + $cart_tax - $discount;

	return (float) apply_filters( 'edd_get_cart_total', $total );
}

/**
 * Get Total Cart Amount
 *
 * Gets the fully formatted total price amount in the cart.
 * uses edd_get_cart_amount().
 *
 * @access public
 * @global $edd_options Array of all the EDD Options
 * @since 1.3.3
 * @return string - the cart amount
 */
function edd_cart_total( $echo = true ) {
	global $edd_options;

	$total = apply_filters( 'edd_cart_total', edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ) );

	if ( edd_is_cart_taxed() ) {
		if ( edd_prices_show_tax_on_checkout() ) {
			$total .= '<br/><span>'. sprintf( __('(includes %s tax)', 'edd'), edd_cart_tax() ) . '</span>';
		}
	}

	if ( ! $echo ) {
		return $total;
	}

	echo $total;
}

/**
 * Get Purchase Summary
 *
 * Retrieves the purchase summary.
 *
 * @access      public
 * @since       1.0
 * @return      string
 */
function edd_get_purchase_summary( $purchase_data, $email = true ) {
	$summary = '';

	if ( $email ) {
		$summary .= $purchase_data['user_email'] . ' - ';
	}

	foreach ( $purchase_data['downloads'] as $download ) {
		$summary .= get_the_title( $download['id'] ) . ', ';
	}

	$summary = substr( $summary, 0, -2 );

	return $summary;
}

/**
 * Gets the total tax amount for the cart contents
 *
 * @since 1.2.3
 * @param array $discounts Array of discounts to take into account (required for AJAX calls)
 * @return string Total tax amount
 */
function edd_get_cart_tax( $discounts = false ) {
	$subtotal     = edd_get_cart_subtotal( false );
	$subtotal    += edd_get_cart_fee_total();
	$cart_tax     = 0;
	$billing_info = edd_get_purchase_cc_info();

	if ( edd_is_cart_taxed() ) {

		if ( edd_taxes_after_discounts() ) {
			$subtotal -= edd_get_cart_discounted_amount( $discounts );
		}

		$cart_tax = edd_calculate_tax( $subtotal, false, $billing_info['card_country'], $billing_info['card_state'] );

	}

	return apply_filters( 'edd_get_cart_tax', $cart_tax, $subtotal );
}

/**
 * Gets the total tax amount for the cart contents in a fully formatted way
 *
 * @since 1.2.3
 * @param bool $echo Whether to echo the tax amount or not (default: false)
 * @return string Total tax amount (if $echo is set to true)
 */
function edd_cart_tax( $echo = false ) {
	$cart_tax = 0;

	if ( edd_is_cart_taxed() ) {
		$cart_tax = edd_get_cart_tax();
		$cart_tax = edd_currency_filter( edd_format_amount( $cart_tax ) );
	}

	$tax = apply_filters( 'edd_cart_tax', $cart_tax );

	if ( ! $echo ) {
		return $tax;
	}

	echo $tax;
}

/**
 * Retrieve the Cart Content Details
 *
 * @since 1.0
 * @return array $defailt Cart content details
 */
function edd_get_cart_content_details() {
	$cart_items = edd_get_cart_contents();
	if ( empty( $cart_items ) ) return false;

	$details  = array();
	$is_taxed = edd_is_cart_taxed();

	foreach( $cart_items as $key => $item ) {

		$price = edd_get_cart_item_price( $item['id'], $item['options'] );
		$non_taxed_price = edd_get_cart_item_price( $item['id'], $item['options'], false );

		$details[ $key ]  = array(
			'name'        => get_the_title( $item['id'] ),
			'id'          => $item['id'],
			'item_number' => $item,
			'price'       => $price,
			'quantity'    => 1,
			'tax'         => $is_taxed ? edd_calculate_tax( $non_taxed_price, false ) : 0,
		);
	}

	return $details;
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

	$field = is_int( $terms ) ? 'id' : 'slug';

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
 * @param object $post Download (post) object
 * @param bool $ajax AJAX?
 * @return string $remove_url URL to remove the cart item
 */
function edd_remove_item_url( $cart_key, $post, $ajax = false ) {
	global $post;

	if( is_page() ) {
		$current_page = add_query_arg( 'page_id', $post->ID, home_url('/') );
	} else if( is_singular() ) {
		$current_page = add_query_arg( 'p', $post->ID, home_url('/') );
	} else {
		$current_page = edd_get_current_page_url();
	}
	$remove_url = add_query_arg( array('cart_item' => $cart_key, 'edd_action' => 'remove' ), $current_page );

	return apply_filters( 'edd_remove_item_url', $remove_url );
}

/**
 * Show Added To Cart Messages
 *
 * @since 1.0
 * @param int $download_id Download (Post) ID
 * @return void
 */
function edd_show_added_to_cart_messages( $download_id ) {
	if ( isset( $_POST['edd_action'] ) && $_POST['edd_action'] == 'add_to_cart' ) {
		if ( $download_id != absint( $_POST['download_id'] ) )
			$download_id = absint( $_POST['download_id'] );

		$alert = '<div class="edd_added_to_cart_alert">'
		. sprintf( __('You have successfully added %s to your shopping cart.', 'edd'), get_the_title( $download_id ) )
		. ' <a href="' . edd_get_checkout_uri() . '" class="edd_alert_checkout_link">' . __('Checkout.', 'edd') . '</a>'
		. '</div>';

		echo apply_filters( 'edd_show_added_to_cart_messages', $alert );
	}
}
add_action('edd_after_download_content', 'edd_show_added_to_cart_messages');

/**
 * Get the URL of the Checkout page
 *
 * @since 1.0.8
 * @global $edd_options Array of all the EDD Options
 * @param array $args Extra query args to add to the URI
 * @return mixed Full URL to the checkout page, if present | null if it doesn't exist
 */
function edd_get_checkout_uri( $args = array() ) {
	global $edd_options;

	$uri = isset( $edd_options['purchase_page'] ) ? get_permalink( $edd_options['purchase_page'] ) : NULL;

	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) )
			$args = str_replace( '?', '', $args );

		$args = wp_parse_args( $args );

		$uri = add_query_arg( $args, $uri );
	}

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) ) {
		$uri = preg_replace( '/^http/', 'https', $uri );
	}

	if ( isset( $edd_options['no_cache_checkout'] ) && edd_is_caching_plugin_active() )
		$uri = add_query_arg( 'nocache', 'true', $uri );

	return apply_filters( 'edd_get_checkout_uri', $uri );
}

/**
 * Get the URL of the Transaction Failed page
 *
 * @since 1.3.4
 * @global $edd_options Array of all the EDD Options
 * @param string $extras Extras to append to the URL
 * @return string Full URL to the Transaction Failed page, if present, home page if it doesn't exist
 */
function edd_get_failed_transaction_uri( $extras = false ) {
	global $edd_options;

	$uri = isset( $edd_options['failure_page'] ) ? trailingslashit( get_permalink( $edd_options['failure_page'] ) ) : home_url();
	if ( $extras )
		$uri .= $extras;

	return apply_filters( 'edd_get_failed_transaction_uri', $uri );
}

/**
 * Determines if we're currently on the Checkout page
 *
 * @since 1.1.2
 * @return bool True if on the Checkout page, false otherwise
 */
function edd_is_checkout() {
	global $edd_options;
	$is_checkout = isset( $edd_options['purchase_page'] ) ? is_page( $edd_options['purchase_page'] ) : false;
	return apply_filters( 'edd_is_checkout', $is_checkout );
}

/**
 * Empties the Cart
 *
 * @since 1.0
 * @uses EDD()->session->set()
 * @return void
 */
function edd_empty_cart() {
	// Remove cart contents
	EDD()->session->set( 'edd_cart', NULL );

	// Remove all cart fees
	EDD()->session->set( 'edd_cart_fees', NULL );

	// Remove any active discounts
	edd_unset_all_cart_discounts();
}

/**
 * Store Purchase Data in Sessions
 *
 * Used for storing info about purchase
 *
 * @since 1.1.5
 * @uses EDD()->session->set()
 * @return void
 */
function edd_set_purchase_session( $purchase_data ) {
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
