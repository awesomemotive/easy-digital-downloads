<?php

// retrieve contents from the cart
function edd_get_cart_contents() {
	return isset($_SESSION['edd_cart']) ? $_SESSION['edd_cart'] : false;
}

/*
* Gets the total quanity of items cart
* Return - INT - number of this item in the cart
*/
function edd_get_cart_quantity() {
	$cart = edd_get_cart_contents();
	if($cart)
		$quantity = count($cart);
	else
		$quantity = 0;
	return $quantity;
}


/*
* Adds a download ID to the shopping cart
* Uses edd_get_cart_contents()
* @param - $download_id INT the ID number of the download to add to the cart
* @param - $options array an array of options, such as variable price
* return - cart key of the new item
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

/*
* Removes a download from the shopping cart
* Uses edd_get_cart_contents()
* @param - $cart_key INT the cart key to remove
* return - array of updated cart items
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


/*
* Checks to see if an item is already in the cart
* Uses edd_get_cart_contents()
* @param - $download_id INT the ID number of the download to remove
* @param - $cart_key INT the cart key to remove
* return - array of updated cart items
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

/*
* Gets the quanity for an item in the cart
* Paramter - $item INT the download (cart item) ID number
* Return - INT - number of this item in the cart
*/
function edd_get_cart_item_quantity($item) {
	$cart = edd_get_cart_contents();
	$item_counts = array_count_values($cart);
	$quantity = $item_counts[$item];
	return $quantity;
}

/*
* Gets the price of the cart item
* @param - $item INT the download ID number
* @param - $options array optional parameters, used for defining variable prices
* Return - string - price for this item
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

/*
* Gets the name of the specified price option, for variable pricing only
* @param - $item INT the download ID number
* @param - $options array optional parameters, used for defining variable prices
* Return - string - the name of the price option
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

/*
* Gets the total price amount in the cart
* uses edd_get_cart_contents()
* uses edd_get_download_price()
* uses edd_get_discounted_amount()
* Return - string - the price of all items in the cart
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

/*
* Returns a nicely formatted amount
* @param - $amount string the price amount to format
* Return - string - the newly formatted amount
*/
function edd_format_amount($amount) {
	global $edd_options;
	$thousands_sep = isset($edd_options['thousands_separator']) ? $edd_options['thousands_separator'] : ',';
	$decimal_sep = isset($edd_options['decimal_separator']) ? $edd_options['decimal_separator'] : '.';
	return number_format($amount, 2, $decimal_sep, $thousands_sep);
}

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

/*
* Adds all downloads within a taxonomy term to the cart
* @since v1.0.6
* @param $taxonomy string - the name of the taxonomy
* @param $terms mixed - the slug or id of the term from which to add ites, or an array of terms
* @return array of IDs for each item added to the cart
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

/*
* Empties the cart
*/
function edd_empty_cart() {
	$_SESSION['edd_cart'] = NULL;
}

// make sure a session is started
if(!session_id()){
	add_action( 'init', 'session_start', -1 );
}