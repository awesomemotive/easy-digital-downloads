<?php
/**
 * Cart Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Cart Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


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
	return isset($_SESSION['edd_cart']) ? $_SESSION['edd_cart'] : false;
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
	if($cart)
		$quantity = count($cart);
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

function edd_add_to_cart($download_id, $options = array()) {
	$cart = edd_get_cart_contents();
	if(!edd_item_in_cart($download_id)) {
		if(is_array($cart)) {
			$cart[] = array('id' => $download_id, 'options' => $options);
		} else {
			$cart = array(array('id' => $download_id, 'options' => $options));
		}
	
		$_SESSION['edd_cart'] = $cart;
	
		// clear all the checkout errors, if any
		edd_clear_errors();
	
		return count($cart) - 1;
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

function edd_remove_from_cart($cart_key) {
	$cart = edd_get_cart_contents();
	if(!is_array($cart)) {
		return true; // empty cart
	} else {
		unset($cart[$cart_key]);
	}
	$_SESSION['edd_cart'] = $cart;
	
	// clear all the checkout errors, if any
	edd_clear_errors();
	
	return $cart; // the updated cart items
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

function edd_item_in_cart($download_id) {
	$cart_items = edd_get_cart_contents();
	if(!is_array($cart_items)) {
		return false; // empty cart
	} else {
		foreach($cart_items as $item) {
			if($item['id'] == $download_id) {
				return true;
			}
		}
	}
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

function edd_get_item_position_in_cart($download_id) {
	$cart_items = edd_get_cart_contents();
	if(!is_array($cart_items)) {
		return false; // empty cart
	} else {
		foreach($cart_items as $postion => $item) {
			if($item['id'] == $download_id) {
				return $postion;
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

function edd_get_cart_item_quantity($item) {
	$cart = edd_get_cart_contents();
	$item_counts = array_count_values($cart);
	$quantity = $item_counts[$item];
	return $quantity;
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

function edd_get_cart_item_price($item_id, $options = array()) {
	
	$variable_pricing = get_post_meta($item_id, '_variable_pricing', true);
	$price = get_post_meta($item_id, 'edd_price', true); 
	if($variable_pricing && !empty($options)) {
		// if variable prices are enabled, retrieve the options
		$prices = get_post_meta($item_id, 'edd_variable_prices', true);
		if($prices) {
			$price = $prices[$options['price_id']]['amount'];
		}
	}
	return $price;
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

function edd_get_price_name($item_id, $options = array()) {
	
	$variable_pricing = get_post_meta($item_id, '_variable_pricing', true);
	if($variable_pricing && !empty($options)) {
		// if variable prices are enabled, retrieve the options
		$prices = get_post_meta($item_id, 'edd_variable_prices', true);
		if($prices) {
			$name = $prices[$options['price_id']]['name'];
		}
		return $name;
	}
	return false;
}


/**
 * Get Cart Amount
 *
 * Gets the total price amount in the cart.
 * uses edd_get_cart_contents().
 *
 * @access      public
 * @since       1.0
 * @return      string - the name of the price option
*/

function edd_get_cart_amount() {
	$cart_items = edd_get_cart_contents();
	$amount = 0;
	if($cart_items) {
		foreach($cart_items as $item) {
			$item_price = edd_get_cart_item_price($item['id'], $item['options']);
			$amount = $amount + $item_price;
		}
		if(isset($_POST['edd-discount']) && $_POST['edd-discount'] != '') {
			// discount is validated before this function runs, so no need to check for it
			$amount = edd_get_discounted_amount($_POST['edd-discount'], $amount);
		}
		
		return $amount;
	}
	return 0;
}


/**
 * Format Amount
 *
 * Returns a nicely formatted amount.
 *
 * @access      public
 * @since       1.0
 * @param       $amount string the price amount to format
 * @param       $options array optional parameters, used for defining variable prices
 * @return      string - the newly formatted amount
*/

function edd_format_amount($amount) {
	global $edd_options;
	$thousands_sep = isset($edd_options['thousands_separator']) ? $edd_options['thousands_separator'] : ',';
	$decimal_sep = isset($edd_options['decimal_separator']) ? $edd_options['decimal_separator'] : '.';
	return number_format($amount, 2, $decimal_sep, $thousands_sep);
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

function edd_get_purchase_summary($purchase_data, $email = true) {

	$summary = '';
	if($email) {
		$summary .= $purchase_data['user_email'] . ' - ';
	}
	foreach($purchase_data['downloads'] as $download) {
		$summary .= get_the_title($download['id']) . ', ';
	}
	$summary = substr($summary, 0, -2);
	
	return $summary;
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
	$details = array();
	if($cart_items) {
		foreach($cart_items as $key => $item) {
			$details[$key] = array(
				'name' => get_the_title($item['id']),
				'id' => $item['id'],
				'item_number' => $item,
				'price' => edd_get_cart_item_price($item['id'], $item['options']),
				'quantity' => 1
			);
		}
	}
	if(!empty($details)) {
		return $details;
	}
	return false;
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

function edd_add_collection_to_cart($taxonomy, $terms) {
	
	if(!is_string($taxonomy)) return false;
	
	$field = is_int($terms) ? 'id' : 'slug';
	
	$cart_item_ids = array();	
	
	$args = array(
		'post_type' => 'download',
		'posts_per_page' => -1,
		$taxonomy => $terms
	);	
	
	$items = get_posts($args);
	if($items) {

		foreach($items as $item) {
			edd_add_to_cart($item->ID);
			$cart_item_ids[] = $item->ID;
		}	
	}
	return $cart_item_ids;
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

function edd_show_added_to_cart_messages($download_id) {
	if( isset( $_POST['edd_action'] ) && $_POST['edd_action'] == 'add_to_cart' ) {
		$alert = sprintf( __('You have successfully added %s to your shopping cart.', 'edd'), get_the_title( $download_id ) );
		$alert .= ' <a href="' . edd_get_checkout_uri() . '" class="edd_alert_checkout_link">' . __('Checkout.', 'edd') . '</a>';
		echo '<div class="edd_added_to_cart_alert">' . $alert . '</div>';
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
 * @return      mixed - the full URL to the checkout page, if present, NULL if it doesn't exist
*/

function edd_get_checkout_uri() {
    global $edd_options;
    return isset( $edd_options['purchase_page'] ) ? get_permalink( $edd_options['purchase_page'] ) : NULL;
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
	$_SESSION['edd_cart'] = NULL;
}


// make sure a session is started
if(!session_id()){
	add_action( 'init', 'session_start', -1 );
}