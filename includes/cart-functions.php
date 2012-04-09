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
* return - cart key of the new item
*/
function edd_add_to_cart($download_id) {
	$cart = edd_get_cart_contents();
	if(is_array($cart)) {
		$cart[] = $download_id;
	} else {
		$cart = array($download_id);
	}
	
	$_SESSION['edd_cart'] = $cart;
	
	// clear all the checkout errors, if any
	edd_clear_errors();
	
	return count($cart) - 1;
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
	$cart = edd_get_cart_contents();
	if(!is_array($cart)) {
		return false; // empty cart
	} else {
		if(in_array($download_id, $cart)) {
			return true;
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
			$item_price = edd_get_download_price($item);
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
		$summary .= get_the_title($download) . ', ';
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
				'name' => get_the_title($item),
				'item_number' => $item,
				'price' => edd_get_download_price($item),
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
* Empties the cart
*/
function edd_empty_cart() {
	$_SESSION['edd_cart'] = NULL;
}

function edd_empty_cart_message() {
	echo '<p class="edd-empty-cart">' . __('Your cart is empty', 'edd') . '</p>';
}
add_action('edd_empty_cart', 'edd_empty_cart_message');

// make sure a session is started
if(!session_id()){
	add_action( 'init', 'session_start', -1 );
}