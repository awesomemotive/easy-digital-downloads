<?php
/**
 * Discount Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Discount Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Get Discounts
 *
 * Retrieves an array of all available discount codes.
 *
 * @access      public
 * @since       1.0 
 * @return      boolean
*/

function edd_get_discounts() {
	$discounts = get_option('edd_discounts');
	if(false === $discounts) { 
		add_option('edd_discounts');
	}
	if($discounts)
		return $discounts;
	return false;
}


/**
 * Has Active Discounts
 *
 * Checks if there is any active discounts, returns a boolean.
 *
 * @access      public
 * @since       1.0 
 * @return      boolean
*/

function edd_has_active_discounts() {
	$has_active = false; 
	$discounts = edd_get_discounts();
	if(is_array($discounts) && !empty($discounts)) {
        foreach($discounts as $discount) {
            if(isset($discount['status']) && $discount['status'] == 'active' && !edd_is_discount_expired($discount['code'])) {
                $has_active = true;
                break;
            }
        }
    }
    return $has_active;
}
 

/**
 * Get Discount
 *
 * Retrieves a complete discount code by ID/key.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_discount($key) {
	$discounts = edd_get_discounts();
	if($discounts) {
		return isset($discounts[$key]) ? $discounts[$key] : false;
	}
	return false;
}


/**
 * Get Discount Id By Code
 *
 * Retrieves all details for a discount by its code.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

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


/**
 * Add Discount
 *
 * Setups and stores a new discount code.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_add_discount($data) {
	if(wp_verify_nonce($data['edd-discount-nonce'], 'edd_discount_nonce')) {
		// setup the discount code details
		$posted = array();
		foreach($data as $key => $value) {
			if($key != 'edd-discount-nonce' && $key != 'edd-action')
			$posted[$key] = strip_tags(addslashes($value));
		}
		// set the discount code's default status to active
		$posted['status'] = 'active';
		$save = edd_store_discount($posted);
	}
}
add_action('edd_add_discount', 'edd_add_discount');


/**
 * Edit Discount
 *
 * Saves an edited discount.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_edit_discount($data) {
	if(isset($data['edd-discount-nonce']) && wp_verify_nonce($data['edd-discount-nonce'], 'edd_discount_nonce')) {
		// setup the discount code details
		$discount = array();
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


/**
 * Store Discount
 *
 * Stores a discount code.
 * If the code exists, it updates it, otherwise it creates a new one.
 *
 * @access      public
 * @since       1.0 
 * @return      boolean
*/

function edd_store_discount($discount_details, $id = null) {
	if(edd_discount_exists($id) && !is_null($id)) {
	    // update an existing discount
		$discounts = edd_get_discounts();
		if(!$discounts) $discounts = array();
		$discounts[$id] = $discount_details;		
		update_option('edd_discounts', $discounts);
		
		// discount code updated
		return true;
		
	} else {
	    // add the discount
		$discounts = edd_get_discounts();
		if(!$discounts) $discounts = array();
		$discounts[] = $discount_details;
		
		update_option('edd_discounts', $discounts);
		
		// discount code created
		return true;
	}
	
	// something went wrong
	return false;
}


/**
 * Delete Discount
 *
 * Listens for when a discount delete button is clicked.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_delete_discount($data) {
	$discount_id = $data['discount'];
	edd_remove_discount($discount_id);
}
add_action('edd_delete_discount', 'edd_delete_discount');


/**
 * Remove Discount
 *
 * Deletes a discount code.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_remove_discount($discount_id) {
	
	$discounts = edd_get_discounts();
	unset($discounts[$discount_id]);
	
	update_option('edd_discounts', $discounts);
}


/**
 * Activate Discount
 *
 * Sets a discount code to active.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_activate_discount($data) {
	$id = $data['discount'];
	edd_update_discount_status($id, 'active');
}
add_action('edd_activate_discount', 'edd_activate_discount');


/**
 * Deactivate Discount
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_deactivate_discount($data) {
	$id = $data['discount'];
	edd_update_discount_status($id, 'inactive');
}
add_action('edd_deactivate_discount', 'edd_deactivate_discount');
 

/**
 * Update Discount Status
 *
 * Updates a discount's status from one status to another.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

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


/**
 * Discount Exists
 *
 * Checks to see if a discount code already exists.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_discount_exists($code_id) {
	$discounts = edd_get_discounts();
	
	// no discounts, so the code does not exist
	if(!$discounts) return false;
	
	// a discount with this code has been found
	if(isset($discounts[$code_id])) return true;
	
	// no discount with the specified ID exists
	return false;
}


/**
 * Is Discount Active
 *
 * Checks whether a discount code is active.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_is_discount_active($code_id) { 
	$discount = edd_get_discount($code_id);
	if($discount) {
		if(isset($discount['status']) && $discount['status'] == 'active' && !edd_is_discount_expired($code_id)) {
			return true;
		}
	}
	return false;
}


/**
 * Is Discount Expired
 *
 * Checks whether a discount code is expired.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_is_discount_expired($code_id) { 
	$discount = edd_get_discount($code_id);
	if($discount) {
		if(isset($discount['expiration']) && $discount['expiration'] != '') {
			$expiration = strtotime($discount['expiration']);
			if($expiration < time() - (24 * 60 * 60)) {
			    // discount is expired
				return true;
			}
		}
	}
	// discount is NOT expired
	return false;
}


/**
 * Is Discount Started
 *
 * Checks whether a discount code is available yet (start date).
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_is_discount_started($code_id) { 
	$discount = edd_get_discount($code_id);
	if($discount) {
		if(isset($discount['start']) && $discount['start'] != '') {
			$start_date = strtotime($discount['start']);
			if($start_date < time()) {
			    // discount has pased the start date
				return true;
			}
		} else {
		    // no start date for this discount, so has to be true
			return true;
		}
	}
	// discount has not passed the start date
	return false;
}


/**
 * Is Discount Maxed Out
 *
 * Checks to see if a discount has uses left.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

function edd_is_discount_maxed_out($code_id) {
	$discount = edd_get_discount($code_id);
	if($discount) {
		$uses = isset($discount['uses']) ? $discount['uses'] : 0;
		// large number that will never be reached
		$max_uses = isset($discount['max']) ? $discount['max'] : 99999999;
		 // should never be greater than, but just in case
		if($uses >= $max_uses && $max_uses != '' && isset($discount['max'])) {
            // discount is maxed out
			return true;
		}	
	}
	// uses still remain
	return false;
}


/**
 * Is Discount Valid
 *
 * Check whether a discount code is valid (when purchasing).
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

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


/**
 * Get Discount By Code
 *
 * Retrieves a discount code ID from the code.
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

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


/**
 * Get Discounted Amount
 *
 * Gets the discounted price.
 *
 * @access      public
 * @since       1.0 
 * @param       $code - string - the code to calculate a discount for
 * @param       $base_price - string/int the price before discount
 * @return      $discounted_price - string - the amount after discount
*/

function edd_get_discounted_amount($code, $base_price) {
	$discount_id = edd_get_discount_by_code($code);
	$discounts = edd_get_discounts();
	$type = $discounts[$discount_id]['type'];
	$rate = $discounts[$discount_id]['amount'];
	
	if($type == 'flat') { 
	    // set amount
		$discounted_price = $base_price - $rate;
	} else { 
	    // percentage discount
		$discounted_price = $base_price - ( $base_price * ( $rate / 100 ) );
	}
	return edd_format_amount($discounted_price);
}


/**
 * Increase Discount Usage
 *
 * Increases the use count of a discount code.
 *
 * @access      public
 * @since       1.0 
 * @param       $code string - the discount code to be incremented
 * @return      int - the new use count
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


/**
 * Format Discount Rate
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_format_discount_rate($rate, $amount) {
	if($rate == 'flat') {
		return edd_currency_filter($amount);
	} else {
		return $amount . '%';
	}
}