<?php
/**
 * Cart Saving
 *
 * This class adds the ability for customers to save the contents of
 * their cart.
 *
 * For logged in users, the contents of the cart will be saved to their user meta
 * For logged out users, the contents of the cart will be saved as a transiet in the
 * database and will automatically be deleted after one week.
 *
 * A cookie will be saved with a identifier allowing users to restore their cart without
 * having to visit a specific URL or having to log in.
 *
 * @since 1.8
 */

/**
 * EDD_Cart_Saving Class
 *
 * @since 1.8
 */
final class EDD_Cart_Saving {
 	public function __construct() {
 		$this->init();
 	}

 	public function init() {
 		/**
		 * Allows developers to quickly enable/disable the cart saving function
		 * on their site.
		 */
		if ( ! apply_filters( 'edd_cart_saving', true ) )
			return;
 	}
}