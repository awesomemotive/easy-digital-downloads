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
	$download = get_post( $download_id );

	if( 'download' != $download->post_type )
		return; // Not a download product

	if ( ! current_user_can( 'edit_post', $download->ID ) && ( $download->post_status == 'draft' || $download->post_status == 'pending' ) )
		return; // Do not allow draft/pending to be purchased if can't edit. Fixes #1056

	do_action( 'edd_pre_add_to_cart', $download_id, $options );

	if ( edd_has_variable_prices( $download_id )  && ! isset( $options['price_id'] ) ) {
		// Forces to the first price ID if none is specified and download has variable prices
		$options['price_id'] = 0;
	}

	$to_add = array();

	if( isset( $options['quantity'] ) ) {
		$quantity = absint( $options['quantity'] );
		unset( $options['quantity'] );
	} else {
		$quantity = 1;
	}

	if ( isset( $options['price_id'] ) && is_array( $options['price_id'] ) ) {
		// Process multiple price options at once
		foreach ( $options['price_id'] as $price ) {
			$price_options = array( 'price_id' => $price );
			$to_add[] = apply_filters( 'edd_add_to_cart_item', array( 'id' => $download_id, 'options' => $price_options, 'quantity' => $quantity ) );
		}
	} else {
		// Add a single item
		$to_add[] = apply_filters( 'edd_add_to_cart_item', array( 'id' => $download_id, 'options' => $options, 'quantity' => $quantity ) );
	}

	if ( is_array( $cart ) ) {
		$cart = array_merge( $cart, $to_add );
	} else {
		$cart = $to_add;
	}

	$cart = EDD()->session->set( 'edd_cart', $cart );

	do_action( 'edd_post_add_to_cart', $download_id, $options );

	// Clear all the checkout errors, if any
	edd_clear_errors();

	return count( $cart ) - 1;
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
		$item_id = isset( $cart[ $cart_key ][ 'id' ] ) ? $cart[ $cart_key ][ 'id' ] : null;
		unset( $cart[ $cart_key ] );
	}

	EDD()->session->set( 'edd_cart', $cart );

	do_action( 'edd_post_remove_from_cart', $cart_key, $item_id );

	// Clear all the checkout errors, if any
	edd_clear_errors();

	return $cart; // The updated cart items
}

/**
 * Checks the see if an item is already in the cart and returns a boolean
 *
 * @since 1.0
 *
 * @param int   $download_id ID of the download to remove
 * @param array $options
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
 *
 * @param int   $download_id ID of the download to get position of
 * @param array $options array of price options
 * @return bool|int|string false if empty cart |  position of the item in the cart
 */
function edd_get_item_position_in_cart( $download_id = 0, $options = array() ) {
	$cart_items = edd_get_cart_contents();
	if ( ! is_array( $cart_items ) ) {
		return false; // Empty cart
	} else {
		foreach ( $cart_items as $position => $item ) {
			if ( $item['id'] == $download_id ) {
				if ( isset( $options['price_id'] ) && isset( $item['options']['price_id'] ) ) {
					if ( (int) $options['price_id'] == (int) $item['options']['price_id'] ) {
						return $position;
					}
				} else {
					return $position;
				}
			}
		}
	}
	return false; // Not found
}


/**
 * Check if quantities are enabled
 *
 * @since 1.7
 * @return bool
 */
function edd_item_quanities_enabled() {
	global $edd_options;
	$ret = isset( $edd_options['item_quantities'] );
	return apply_filters( 'edd_item_quantities_enabled', $ret );
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
	$cart = edd_get_cart_contents();
	$key  = edd_get_item_position_in_cart( $download_id, $options );

	if( $quantity < 1 )
		$quantity = 1;

	$cart[ $key ]['quantity'] = $quantity;
	EDD()->session->set( 'edd_cart', $cart );
	return $cart;

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
	$cart     = edd_get_cart_contents();
	$key      = edd_get_item_position_in_cart( $download_id, $options );
	$quantity = isset( $cart[ $key ]['quantity'] ) && edd_item_quanities_enabled() ? $cart[ $key ]['quantity'] : 1;
	if( $quantity < 1 )
		$quantity = 1;
	return apply_filters( 'edd_get_cart_item_quantity', $quantity, $download_id, $options );
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
 * @param int   $item_id Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @param bool  $taxed
 * @return mixed|void Price for this item
 */
function edd_get_cart_item_price( $download_id = 0, $options = array(), $taxed = true ) {
	global $edd_options;

	$price = edd_get_download_price( $download_id );

	// If variable prices are enabled, retrieve the options

	if ( edd_has_variable_prices( $download_id ) && ! empty( $options ) ) {
		$prices = edd_get_variable_prices( $download_id );
		if ( $prices ) {
			$price = isset( $prices[ $options['price_id'] ] ) ? $prices[ $options['price_id'] ]['amount'] : $price;
		}
	}


	// Determine if we need to add tax to the price
	if ( $taxed &&
		(
			( edd_prices_include_tax() && ! edd_is_cart_taxed() && edd_use_taxes() ) ||
			( edd_is_cart_taxed() && edd_prices_show_tax_on_checkout() || ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) )
		)
	) {
		$price = edd_calculate_tax( $price );
	}

	return apply_filters( 'edd_cart_item_price', $price, $download_id, $options, $taxed );
}

/**
 * Get Price Name
 *
 * Gets the name of the specified price option,
 * for variable pricing only.
 *
 * @since 1.0
 *
 * @param       $item_id Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return mixed|void Name of the price option
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
 *
 * @param array $item Cart item array
 * @return int Price id
 */
function edd_get_cart_item_price_id( $item = array() ) {
	if( isset( $item['item_number'] ) ) {
		$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
	} else {
		$price_id = isset( $item['options']['price_id'] ) ? $item['options']['price_id'] : null;
	}
	return $price_id;
}

/**
 * Get cart item price name
 *
 * @since 1.8
 * @param int $item Cart item array
 * @return string Price name
 */
function edd_get_cart_item_price_name( $item = array() ) {
	$price_id = (int) edd_get_cart_item_price_id( $item );
	$prices   = edd_get_variable_prices( $item['id'] );
	$name     = !empty( $prices ) ? $prices[ $price_id ]['name'] : '';
	return apply_filters( 'edd_get_cart_item_price_name', $name, $item['id'], $price_id, $item );
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

	$price = esc_html( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ) );

	if ( edd_is_cart_taxed() ) {
		if ( edd_prices_show_tax_on_checkout() ) {
			$price .= '<span class="edd_cart_subtotal_tax_text" style="font-weight:normal;text-transform:none;">' . __( '(excl. tax)', 'edd' ) . '</span>';
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
 * @return float Total amount before taxes
 */
function edd_get_cart_subtotal( $tax = false) {
	global $edd_options;

	$cart_items = edd_get_cart_contents();
	$amount = 0;

	if ( $cart_items ) {
		foreach ( $cart_items as $item ) {
			$amount += ( edd_get_cart_item_price( $item['id'], $item['options'], $tax ) * edd_get_cart_item_quantity( $item['id'], $item['options'] ) );
		}
	}

	if( $amount < 0 )
		$amount = 0.00;

	return apply_filters( 'edd_get_cart_subtotal', $amount );
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

	// This function needs to be deprecated but is still used

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

	if( $amount < 0 )
		$amount = 0.00;

	return apply_filters( 'edd_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Total Cart Amount
 *
 * Returns amount after taxes and discounts
 *
 * @since 1.4.1
 *
 * @global $edd_options Array of all the EDD Options
 *
 * @param bool $discounts Array of discounts to apply (needed during AJAX calls)
 *
 * @return float Cart amount
 */
function edd_get_cart_total( $discounts = false ) {
	global $edd_options;

	$subtotal = edd_get_cart_subtotal();
	$fees     = edd_get_cart_fee_total();
	$cart_tax = edd_prices_include_tax() ? 0 : edd_get_cart_tax( $discounts );
	$discount = edd_get_cart_discounted_amount( $discounts );
	$total    = $subtotal + $fees + $cart_tax - $discount;

	if( $total < 0 )
		$total = 0.00;

	return (float) apply_filters( 'edd_get_cart_total', $total );
}


/**
 * Get Total Cart Amount
 *
 * Gets the fully formatted total price amount in the cart.
 * uses edd_get_cart_amount().
 *
 * @global $edd_options Array of all the EDD Options
 * @since 1.3.3
 *
 * @param bool $echo
 * @return mixed|string|void
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
 *
 * @param bool $discounts Array of discounts to take into account (required for AJAX calls)
 * @return mixed|void Total tax amount
 */
function edd_get_cart_tax( $discounts = false ) {

	$add_taxes    = edd_taxes_after_discounts() ? true : false;
	$subtotal     = edd_get_cart_subtotal( false );
	$cart_fees    = edd_get_cart_fee_total();
	$subtotal    += $cart_fees;
	$cart_tax     = 0;
	$billing_info = edd_get_purchase_cc_info();

	if ( edd_is_cart_taxed() ) {

		if ( edd_taxes_after_discounts() ) {
			$subtotal -= edd_get_cart_discounted_amount( $discounts );
		} else {
			if( edd_get_cart_fee_total() < 0 ) {
				// If fees are negative, they are treated as a discount. This undoes the step above where we added $cart_fees to the $subtotal
				$subtotal -= $cart_fees;
			}
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

		$original_price = edd_get_cart_item_price( $item['id'], $item['options'], false );
		$price      = edd_get_cart_item_price( $item['id'], $item['options'] );
		$base_price = edd_get_cart_item_price( $item['id'], $item['options'], false );

		// Calculate the discounted price, if any
		$discounts = edd_get_cart_discounts();
		if( $discounts ) {
			foreach ( $discounts as $discount ) {
				$code_id = edd_get_discount_id_by_code( $discount );
				$reqs    = edd_get_discount_product_reqs( $code_id );

				// Make sure requirements are set and that this discount shouldn't apply to the whole cart
				if ( ! empty( $reqs ) && edd_is_discount_not_global( $code_id ) ) {
					// This is a product(s) specific discount

					foreach ( $reqs as $download_id ) {
						if ( $download_id == $item['id'] ) {

							if( edd_taxes_after_discounts() ) {

								$price = edd_get_discounted_amount( $discount, $base_price );

							} else {

								$price = edd_get_discounted_amount( $discount, $price );

							}

						}
					}

				} else {

					// This is a global cart discount

					if( edd_taxes_after_discounts() ) {

						$price = edd_get_discounted_amount( $discount, $base_price );

					} else {

						$price = edd_get_discounted_amount( $discount, $price );

					}
				}

			}
		}

		if( edd_prices_include_tax() && ! edd_taxes_after_discounts() ) {
			$tax = $is_taxed ? $original_price - edd_calculate_tax( $original_price ) : 0.00;
			$price += $tax;
		} elseif( edd_prices_include_tax() && edd_taxes_after_discounts() ) {
			$tax = $is_taxed ? $price - edd_calculate_tax( $price ) : 0.00;
		} elseif( edd_taxes_after_discounts() ) {
			$tax = $is_taxed ? edd_calculate_tax( $price ) - $price : 0.00;
			$price += $tax;
		} else {
			$tax = $is_taxed ? edd_calculate_tax( $original_price ) - $base_price : 0.00;
		}


		$details[ $key ]  = array(
			'name'        => get_the_title( $item['id'] ),
			'id'          => $item['id'],
			'item_number' => $item,
			'price'       => $price,
			'quantity'    => edd_get_cart_item_quantity( $item['id'], $item['options'] ),
			'tax'         => $tax,
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
 * @param object $post Download (post) object
 * @param bool $ajax AJAX?
 * @return string $remove_url URL to remove the cart item
 */
function edd_remove_item_url( $cart_key, $post, $ajax = false ) {
	global $post;

	if( is_page() ) {
		$current_page = add_query_arg( 'page_id', $post->ID, home_url( 'index.php' ) );
	} else if( is_singular() ) {
		$current_page = add_query_arg( 'p', $post->ID, home_url( 'index.php' ) );
	} else {
		$current_page = edd_get_current_page_url();
	}
	$remove_url = add_query_arg( array( 'cart_item' => $cart_key, 'edd_action' => 'remove' ), $current_page );

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
 *
 * @param bool $extras Extras to append to the URL
 * @return mixed|void Full URL to the Transaction Failed page, if present, home page if it doesn't exist
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

	do_action( 'edd_empty_cart' );
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

/**
 * Checks if cart saving has been disabled
 *
 * @since 1.8
 * @global $edd_options
 * @return bool Whether or not cart saving has been disabled
 */
function edd_is_cart_saving_disabled() {
	global $edd_options;

	return apply_filters( 'edd_cart_saving_disabled', isset( $edd_options['disable_cart_saving'] ) );
}

/**
 * Checks if a cart has been saved
 *
 * @since 1.8
 * @return bool
 */
function edd_is_cart_saved() {

	if( edd_is_cart_saving_disabled() )
		return false;

	if ( is_user_logged_in() ) {

		$saved_cart = get_user_meta( get_current_user_id(), 'edd_saved_cart', true );

		// Check that a cart exists
		if( ! $saved_cart )
			return false;

		// Check that the saved cart is not the same as the current cart
		if ( $saved_cart === EDD()->session->get( 'edd_cart' ) )
			return false;

		return true;

	} else {

		// Check that a saved cart exists
		if ( ! isset( $_COOKIE['edd_saved_cart'] ) )
			return false;

		// Check that the saved cart is not the same as the current cart
		if ( stripslashes( maybe_unserialize( $_COOKIE['edd_saved_cart'] ) ) === EDD()->session->get( 'edd_cart' ) )
			return false;

		return true;

	}

	return false;
}

/**
 * Process the Cart Save
 *
 * @since 1.8
 * @return void
 */
function edd_save_cart() {
	global $edd_options;

	if ( edd_is_cart_saving_disabled() )
		return;

	$user_id  = get_current_user_id();
	$cart     = EDD()->session->get( 'edd_cart' );
	$token    = edd_generate_cart_token();
	$messages = EDD()->session->get( 'edd_cart_messages' );

	if ( is_user_logged_in() ) {

		update_user_meta( $user_id, 'edd_saved_cart', $cart, false );
		update_user_meta( $user_id, 'edd_cart_token', $token, false );

	} else {

		$cart = serialize( $cart );

		setcookie( 'edd_saved_cart', $cart, time()+3600*24*7, COOKIEPATH, COOKIE_DOMAIN );
		setcookie( 'edd_cart_token', $token, time()+3600*24*7, COOKIEPATH, COOKIE_DOMAIN );

	}

	$messages = EDD()->session->get( 'edd_cart_messages' );

	if ( ! $messages )
		$messages = array();

	$messages['edd_cart_save_successful'] = sprintf(
		'<strong>%1$s</strong>: %2$s',
		__( 'Success', 'edd' ),
		__( 'Cart saved successfully. You can restore your cart using this URL:', 'edd' ) . ' ' . '<a href="' .  edd_get_checkout_uri() . '?edd_action=restore_cart&edd_cart_token=' . $token . '">' .  edd_get_checkout_uri() . '?edd_action=restore_cart&edd_cart_token=' . $token . '</a>'
	);

	EDD()->session->set( 'edd_cart_messages', $messages );
}


/**
 * Process the Cart Restoration
 *
 * @since 1.8
 * @return void || false Returns false if cart saving is disabled
 */
function edd_restore_cart() {

	if ( edd_is_cart_saving_disabled() )
		return;

	$user_id    = get_current_user_id();
	$saved_cart = get_user_meta( $user_id, 'edd_saved_cart', true );
	$token      = edd_get_cart_token();

	if ( is_user_logged_in() && $saved_cart ) {

		$messages = EDD()->session->get( 'edd_cart_messages' );

		if ( ! $messages )
			$messages = array();

		if ( isset( $_GET['edd_cart_token'] ) && $_GET['edd_cart_token'] != $token ) {

			$messages['edd_cart_restoration_failed'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Error', 'edd' ), __( 'Cart restoration failed. Invalid token.', 'edd' ) );
			EDD()->session->set( 'edd_cart_messages', $messages );

			return new WP_Error( 'invalid_cart_token', __( 'The cart cannot be restored. Invalid token.', 'edd' ) );
		}

		delete_user_meta( $user_id, 'edd_saved_cart' );
		delete_user_meta( $user_id, 'edd_cart_token' );

	} elseif ( ! is_user_logged_in() && isset( $_COOKIE['edd_saved_cart'] ) && $token ) {

		$saved_cart = $_COOKIE['edd_saved_cart'];

		if ( $_GET['edd_cart_token'] != $token ) {

			$messages['edd_cart_restoration_failed'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Error', 'edd' ), __( 'Cart restoration failed. Invalid token.', 'edd' ) );
			EDD()->session->set( 'edd_cart_messages', $messages );

			return new WP_Error( 'invalid_cart_token', __( 'The cart cannot be restored. Invalid token.', 'edd' ) );
		}

		$saved_cart = maybe_unserialize( stripslashes( $saved_cart ) );

		setcookie( 'edd_saved_cart', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );
		setcookie( 'edd_cart_token', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );

	}

	$messages['edd_cart_restoration_successful'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Success', 'edd' ), __( 'Cart restored successfully.', 'edd' ) );
	EDD()->session->set( 'edd_cart', $saved_cart );
	EDD()->session->set( 'edd_cart_messages', $messages );
}

/**
 * Retrieve a saved cart token. Used in validating saved carts
 *
 * @since 1.8
 * @return int
 */
function edd_get_cart_token() {

	$user_id = get_current_user_id();

	if( is_user_logged_in() ) {
		$token = get_user_meta( $user_id, 'edd_cart_token', true );
	} else {
		$token = isset( $_COOKIE['edd_cart_token'] ) ? $_COOKIE['edd_cart_token'] : false;
	}
	return apply_filters( 'edd_get_cart_token', $token, $user_id );
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
	return apply_filters( 'edd_generate_cart_token', time() );
}
