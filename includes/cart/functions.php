<?php
/**
 * Cart Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Cart Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Cart Contents
 *
 * Retrieve contents from the cart.
 *
 * @access      public
 * @since       1.0
 * @return      array | false
 */
function edd_get_cart_contents() {
	$cart = EDD()->session->get( 'edd_cart' );
	return ! empty( $cart ) ? apply_filters( 'edd_cart_contents', $cart ) : false;
}

/**
 * Get Cart Quantity
 *
 * Gets the total quanity of items cart.
 *
 * @access      public
 * @since       1.0
 * @return      INT - number of this item in the cart
 */
function edd_get_cart_quantity() {
	$cart = edd_get_cart_contents();
	if ( $cart )
		$quantity = count( $cart );
	else
		$quantity = 0;
	return $quantity;
}

/**
 * Add To Cart
 *
 * Adds a download ID to the shopping cart.
 * Uses edd_get_cart_contents().
 *
 * @access      public
 * @since       1.0
 * @param       $download_id - INT the ID number of the download to add to the cart
 * @param       $options - array an array of options, such as variable price
 * @return      string - cart key of the new item
 */
function edd_add_to_cart( $download_id, $options = array() ) {
	$cart = edd_get_cart_contents();
	if ( ! edd_item_in_cart( $download_id, $options ) ) {
		if( 'download' != get_post_type( $download_id ) )
			return; // Not a download product

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
 * Remove From Cart
 *
 * Removes a download from the shopping cart.
 * Uses edd_get_cart_contents().
 *
 * @access      public
 * @since       1.0
 * @param       $cart_key INT the cart key to remove
 * @return      array - of updated cart items
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
 * Item in Cart
 *
 * Checks to see if an item is already in the cart.
 * Uses edd_get_cart_contents().
 *
 * @access      public
 * @since       1.0
 * @param       $download_id - INT the ID number of the download to remove
 * @return      boolean
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
 * Get Item Position in Cart
 *
 * Gets the position of an item in the cart.
 * Uses edd_get_cart_contents().
 *
 * @access      public
 * @since       1.0.7.2
 * @param       $download_id - INT the ID number of the download to remove
 * @return      $position - INT position of the item in the cart
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
 * Gets the quanity for an item in the cart.
 *
 * @access      public
 * @since       1.0
 * @param       $item INT the download (cart item) ID number
 * @return      $position - INT position of the item in the cart
 */
function edd_get_cart_item_quantity( $item ) {
	$cart = edd_get_cart_contents();
	$item_counts = array_count_values( $cart );
	$quantity = $item_counts[ $item ];
	return $quantity;
}

/**
 * Get Cart Item Price
 *
 * @access      public
 * @since       1.0
 * @param       $item INT the download (cart item) ID number
 * @param       $options - array optional parameters, used for defining variable prices
 * @return      string - the fully formatted price
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
 * @access      public
 * @since       1.0
 * @param       $item INT the download ID number
 * @param       $options array optional parameters, used for defining variable prices
 * @return      string - price for this item
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
 * @access      public
 * @since       1.0
 * @param       $item INT the download ID number
 * @param       $options array optional parameters, used for defining variable prices
 * @return      string - the name of the price option
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
 * Cart Subtotal
 *
 * Shows the subtotal for the shopping cart (no taxes)
 *
 * @access      public
 * @since       1.4
 * @uses        edd_get_cart_subtotal()
 * @return      float - the total amount before taxes fully formatted
 */
function edd_cart_subtotal() {
	global $edd_options;

	$tax = ( ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) || ( $edd_options['prices_include_tax'] == 'no' && edd_prices_show_tax_on_checkout() ) );
	$price = esc_html( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ) );

	if ( edd_is_cart_taxed() ) {

		if ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) {
			$price .= '<br/><span style="font-weight:normal;text-transform:none;">' . __('(ex. tax)', 'edd') . '</span>';
		}

		if ( edd_prices_show_tax_on_checkout() && $edd_options['prices_include_tax'] == 'no' ) {
			$price .= '<br/><span style="font-weight:normal;text-transform:none;">' . __('(incl. tax)', 'edd') . '</span>';
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
 * @access      public
 * @since       1.3.3
 * @return      float - the total amount before taxes
*/

function edd_get_cart_subtotal( $tax = true ) {
	global $edd_options;

	$cart_items = edd_get_cart_contents();
	$amount = 0;

	if ( $cart_items ) {
		foreach( $cart_items as $item ) {
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
 * @access      public
 * @since       1.5
 * @return      bool
 */
function edd_cart_has_fees() {
	return EDD()->fees->has_fees();
}

/**
 * Get cart fees
 *
 * Just a simple wrapper function for EDD_Fees::get_fees()
 *
 * @access      public
 * @since       1.5
 * @return      array
 */
function edd_get_cart_fees() {
	return EDD()->fees->get_fees();
}

/**
 * Get cart fee total
 *
 * Just a simple wrapper function for EDD_Fees::total()
 *
 * @access      public
 * @since       1.5
 * @return      float
 */
function edd_get_cart_fee_total() {
	return EDD()->fees->total();
}

/**
 * Get Cart Amount
 *
 * Gets the total price amount in the cart.
 * uses edd_get_cart_contents().
 *
 * @access      public
 * @since       1.0
 * @param 		$add_taxes bool Whether to apply taxes (if enabled)
 * @param 		$local_override bool Force the local opt-in param - used for when not reading $_POST
 * @return      float the total amount
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

	if ( edd_use_taxes() && $add_taxes ) {
		if ( edd_local_taxes_only() && ( isset( $_POST['edd_tax_opt_in'] ) || $local_override ) ) {
			// Add the tax amount for a local resident
			$tax = edd_get_cart_tax();
			$amount += $tax;
		} elseif ( ! edd_local_taxes_only() ) {
			// Add the global tax amount
			$tax = edd_get_cart_tax();
			$amount += $tax;
		}
	}

	return apply_filters( 'edd_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Total Cart Amount
 *
 * Returns amount after taxes and discounts
 *
 * @access      public
 * @since       1.4.1
 * @param       $discounts - Array of discounts to apply (needed during ajax processes)
 * @return      float - The cart amount
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
 * @access      public
 * @since       1.3.3
 * @return      string - the cart amount
 */
function edd_cart_total( $echo = true ) {
	global $edd_options;

	$total = apply_filters( 'edd_cart_total', edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ) );

	if ( edd_is_cart_taxed() ) {

		if ( edd_prices_show_tax_on_checkout() ) {
			$total .= '<br/><span>'. sprintf( __('(includes %s tax)', 'edd'), edd_cart_tax() ) . '</span>';
		}

	}

	if ( !$echo ) {
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
 * @access      public
 * @since       1.2.3
 * @param       $discounts Array of discounts to take into account (required for ajax processes)
 * @return      string
*/

function edd_get_cart_tax( $discounts = false ) {

	$subtotal = edd_get_cart_subtotal( false );
	$cart_tax = 0;

	if ( edd_is_cart_taxed() ) {

		if ( edd_taxes_after_discounts() ) {
			$subtotal -= edd_get_cart_discounted_amount( $discounts );
		}

		$cart_tax = edd_calculate_tax( $subtotal, false );

	}

	return apply_filters( 'edd_get_cart_tax', $cart_tax, $subtotal );
}

/**
 * Gets the total tax amount for the cart contents
 *
 * Returns a fully formatted amount
 *
 * @access      public
 * @since       1.2.3
 * @return      string
 */
function edd_cart_tax( $echo = false ) {
	$cart_tax = 0;

	if ( edd_is_cart_taxed() ) {
		$cart_tax = edd_get_cart_tax();
		$cart_tax = edd_currency_filter( edd_format_amount( $cart_tax ) );
	}

	$tax = apply_filters( 'edd_cart_tax', $cart_tax );

	if ( !$echo ) {
		return $tax;
	}

	echo $tax;
}

/**
 * Get Cart Content Details
 *
 * Retrieves the cart contnet details.
 *
 * @access      public
 * @since       1.0
 * @return      array
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
 * @access      public
 * @since       1.0.6
 * @param       $taxonomy string - the name of the taxonomy
 * @param       $terms mixed - the slug or id of the term from which to add ites, or an array of terms
 * @return      array of IDs for each item added to the cart
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
 * Remove Item URL
 *
 * Returns the URL to remove an item.
 *
 * @access      public
 * @since       1.0
 * @return      string
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
 * Renders the added to cart messages.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function edd_show_added_to_cart_messages( $download_id ) {
	if ( isset( $_POST['edd_action'] ) && $_POST['edd_action'] == 'add_to_cart' ) {
		$alert = '<div class="edd_added_to_cart_alert">'
		. sprintf( __('You have successfully added %s to your shopping cart.', 'edd'), get_the_title( $download_id ) )
		. ' <a href="' . edd_get_checkout_uri() . '" class="edd_alert_checkout_link">' . __('Checkout.', 'edd') . '</a>'
		. '</div>';

		echo apply_filters( 'edd_show_added_to_cart_messages', $alert );
	}
}
add_action('edd_after_download_content', 'edd_show_added_to_cart_messages');

/**
 * Get Checkout URI
 *
 * Retrieves the URL of the checkout page.
 *
 * @access      public
 * @since       1.0.8
 * @param       $args Extra query args to add to the URI
 * @return      mixed - the full URL to the checkout page, if present, NULL if it doesn't exist
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
 * Get Failed URI
 *
 * Retrieves the URL of the failed transaction page
 *
 * @access      public
 * @since       1.3.4
 * @return      string - the full URL to the failed transactions page, if present, home page if it doesn't exist
*/
function edd_get_failed_transaction_uri( $extras = false ) {
	global $edd_options;

	$uri = isset( $edd_options['failure_page'] ) ? trailingslashit( get_permalink( $edd_options['failure_page'] ) ) : home_url();
	if ( $extras )
		$uri .= $extras;

	return apply_filters( 'edd_get_failed_transaction_uri', $uri );
}

/**
 * Checks if on checkout page
 *
 * Determines if the current page is the checkout page
 *
 * @access      public
 * @since       1.1.2
 * @return      bool - true if on the page, false otherwise
 */
function edd_is_checkout() {
	global $edd_options;
	$is_checkout = isset( $edd_options['purchase_page'] ) ? is_page( $edd_options['purchase_page'] ) : false;
	return apply_filters( 'edd_is_checkout', $is_checkout );
}

/**
 * Empty Cart
 *
 * Empties the cart.
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_empty_cart() {
	// Remove cart contents
	EDD()->session->set('edd_cart', NULL );

	// Remove any active discounts
	edd_unset_all_cart_discounts();
}

/**
 * Store Purchase Data in Sessions
 *
 * Used for storing info about purchase
 *
 * @access      public
 * @since       1.1.5
 * @return      void
 */
function edd_set_purchase_session( $purchase_data ) {
	EDD()->session->set('edd_purchase', $purchase_data );
}

/**
 * Retrieve Purchase Data from Session
 *
 * Used for retrieving info about purchase
 * after completing a purchase
 *
 * @access      public
 * @since       1.1.5
 * @return      array / false
 */
function edd_get_purchase_session() {
	return EDD()->session->get('edd_purchase');
}
