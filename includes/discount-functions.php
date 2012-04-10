<?php

// retrieves an array of all available discount codes
function edd_get_discounts() {
	$discounts = get_option('edd_discounts');
	if($discounts)
		return $discounts;
	return false;
}

// retrieves a complete discount code by ID/key 
function edd_get_discount($key) {
	$discounts = edd_get_discounts();
	if($discounts) {
		return isset($discounts[$key]) ? $discounts[$key] : false;
	}
	return false;
}

// retrieves all details for a discount by its code
function edd_get_discount_id_by_code($code) {
	$discounts = edd_get_discounts();
	if($discounts) {
		foreach($discounts as $id => $discount) {
			if($discount['code'] == $code) {
				return $id;
			}
		}
	}
	return false;
}

// setups and stores a new discount code
function edd_add_discount($data) {
	if(wp_verify_nonce($data['edd-discount-nonce'], 'edd_discount_nonce')) {
		// setup the discount code details
		$posted = array();
		foreach($data as $key => $value) {
			if($key != 'edd-discount-nonce' && $key != 'edd-action')
			$posted[$key] = strip_tags(addslashes($value));
		}
		$posted['status'] = 'active'; // set the discount code's default status toe active
		$save = edd_store_discount($posted);
	}
}
add_action('edd_add_discount', 'edd_add_discount');

// saves an edited discount
function edd_edit_discount($data) {
	if(isset($data['edd-discount-nonce']) && wp_verify_nonce($data['edd-discount-nonce'], 'edd_discount_nonce')) {
		// setup the discount code details
		$posted = array();
		foreach($data as $key => $value) {
			if($key != 'edd-discount-nonce' && $key != 'edd-action' && $key != 'discount-id' && $key != 'edd-redirect')
			$discount[$key] = strip_tags(addslashes($value));
		}
		if(edd_store_discount($discount, $data['discount-id'])) {
			wp_redirect(add_query_arg('edd-message', 'discount_updated', $data['edd-redirect'])); exit;
		} else {
			wp_redirect(add_query_arg('edd-message', 'discount_update_failed', $data['edd-redirect'])); exit;
		}
	}
}
add_action('edd_edit_discount', 'edd_edit_discount');

/*
* Stores a discount code.
* If the code exists, it updates it, otherwise it creates a new one
*/
function edd_store_discount($discount_details, $id = null) {
	if(edd_discount_exists($id) && !is_null($id)) { // update an existing discount
	
		$discounts = edd_get_discounts(); 
		$discounts[$id] = $discount_details;		
		update_option('edd_discounts', $discounts);
		
		return true; // discount code updated
		
	} else { // add the discount
		
		$discounts = edd_get_discounts();
		$discounts[] = $discount_details;
		
		update_option('edd_discounts', $discounts);
		
		return true; // discount code created
	}
	
	return false; // something went wrong
}

// listens for when a discount delete button is clicked
function edd_delete_discount($data) {
	$discount_id = $data['discount'];
	edd_remove_discount($discount_id);
}
add_action('edd_delete_discount', 'edd_delete_discount');

// deletes a discount code
function edd_remove_discount($discount_id) {
	
	$discounts = edd_get_discounts();
	unset($discounts[$discount_id]);
	
	update_option('edd_discounts', $discounts);
}

// sets a discount code to active
function edd_activate_discount($data) {
	$id = $data['discount'];
	edd_update_discount_status($id, 'active');
}
add_action('edd_activate_discount', 'edd_activate_discount');


function edd_deactivate_discount($data) {
	$id = $data['discount'];
	edd_update_discount_status($id, 'inactive');
}
add_action('edd_deactivate_discount', 'edd_deactivate_discount');

// updates a discount's status from one status to another. 
function edd_update_discount_status($code_id, $new_status) {
	$discount = edd_get_discount($code_id);
	$discounts = edd_get_discounts();
	if($discount) {
		$discounts[$code_id]['status'] = $new_status;
		update_option('edd_discounts', $discounts);
		return true;
	}
		
	return false;
}

// checks to see if a discount code already exists
function edd_discount_exists($code_id) {
	$discounts = edd_get_discounts();
	
	if(!$discounts) return false; // no discounts, so the code does not exist
	
	if(isset($discounts[$code_id])) return true; // a discount with this code has been found
	
	return false; // no discount with the specified ID exists
}

// checks whether a discount code is active
function edd_is_discount_active($code_id) { 
	$discount = edd_get_discount($code_id);
	if($discount) {
		if(isset($discount['status']) && $discount['status'] == 'active' && !edd_is_discount_expired($code_id)) {
			return true;
		}
	}
	return false;
}

// checks whether a discount code is expired
function edd_is_discount_expired($code_id) { 
	$discount = edd_get_discount($code_id);
	if($discount) {
		if(isset($discount['expiration']) && $discount['expiration'] != '') {
			$expiration = strtotime($discount['expiration']);
			if($expiration < time() - (24 * 60 * 60)) {
				return true; // discount is expired
			}
		}
	}
	return false; // discount is NOT expired
}

// checks whether a discount code is available yet (start date)
function edd_is_discount_started($code_id) { 
	$discount = edd_get_discount($code_id);
	if($discount) {
		if(isset($discount['start']) && $discount['start'] != '') {
			$start_date = strtotime($discount['start']);
			if($start_date < time()) {
				return true; // discount has pased the start date
			}
		} else {
			return true; // no start date for this discount, so has to be true
		}
	}
	return false; // discount has not passed the start date
}

// checks to see if a discount has uses left
function edd_is_discount_maxed_out($code_id) {
	$discount = edd_get_discount($code_id);
	if($discount) {
		$uses = isset($discount['uses']) ? $discount['uses'] : 0;
		$max_uses = isset($discount['max']) ? $discount['max'] : 99999999; // large number that will never be reached
		if($uses >= $max_uses && $max_uses != '' && isset($discount['max'])) { // should never be greater than, but just in case
			return true; // discount is maxed out
		}	
	}
	return false; // uses still remain
}

// check whether a discount code is valid (when purchasing)
function edd_is_discount_valid($code) {
	$discount_id = edd_get_discount_by_code($code);
	if($discount_id !== false) {
		if(edd_is_discount_active($discount_id) && !edd_is_discount_maxed_out($discount_id) && edd_is_discount_started($discount_id)) {
			return true;
		}
	}
	// no discount with this code was found
	return false;
}

// retrieves a discount code ID from the code
function edd_get_discount_by_code($code) {
	$discounts = edd_get_discounts();
	if($discounts) {
		foreach($discounts as $key => $discount) {
			if(isset($discount['code']) && $discount['code'] == $code) {
				return $key;
			}
		}
	}
	return false;
}

/*
* Gets the discounted price
* uses edd_get_discount_by_code()
* uses edd_get_discounts()
* @param $code - string - the code to calculate a discount for
* @param $base_price - string/int the price before discount
* @return $discounted_price - string - the amount after discount 
*/
function edd_get_discounted_amount($code, $base_price) {
	$discount_id = edd_get_discount_by_code($code);
	$discounts = edd_get_discounts();
	$type = $discounts[$discount_id]['type'];
	$rate = $discounts[$discount_id]['amount'];
	
	if($type == 'flat') { // set amount
		$discounted_price = $base_price - $rate;
	} else { // percentage discount
		$discounted_price = $base_price - ( $base_price * ( $rate / 100 ) );
	}
	return edd_format_amount($discounted_price);
}

/*
* Increases the use count of a discount code
* uses edd_get_discount_by_code()
* @param $code string - the discount code to be incremented
* return int - the new use count
*/
function edd_increase_discount_usage($code) {
	$discount_id = edd_get_discount_by_code($code);
	$discounts = edd_get_discounts();
	$uses = isset($discounts[$discount_id]['uses']) ? $discounts[$discount_id]['uses'] : false;
	if($uses) {
		$uses++;
	} else {
		$uses = 1;
	}
	$discounts[$discount_id]['uses'] = $uses;
	$new_use_count = update_option('edd_discounts', $discounts);
	return $new_use_count;
}

function edd_format_discount_rate($rate, $amount) {
	if($rate == 'flat') {
		return edd_currency_filter($amount);
	} else {
		return $amount . '%';
	}
}