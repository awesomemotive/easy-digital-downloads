<?php

/**
 * Fees
 *
 * This class is for adding arbitrary fees to the cart. Fees can be positive or negative (discounts)
 *
 * @package     Easy Digital Downloads
 * @subpackage  Fees
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/


/**
 * Fees Class
 *
 * @access      public
 * @since       1.5
 *
 */

class EDD_Fees {


	/**
	 * Get us started
	 *
	 * @access      private
	 * @since       1.5
	 *
	 * @return      void
	 */

	function __construct() {
		add_filter( 'edd_payment_meta', array( $this, 'record_fees' ), 10, 2 );
	}


	/**
	 * Add a new fee
	 *
	 * @access      public
	 * @since       1.5
	 *
	 * @return      void
	 */

	public function add_fee( $amount = '', $label = '' ) {

		if( ! $this->has_fees() )
			$fees = array();

		$fees[] = array( 'amount' => $amount, 'label' => $label );

		$_SESSION['edd_cart_fees'] = $fees;
	}


	/**
	 * Check if any fees are present
	 *
	 * @access      public
	 * @since       1.5
	 *
	 * @return      void
	 */

	public function has_fees() {
		return ! empty( $_SESSION['edd_cart_fees'] ) && is_array( $_SESSION['edd_cart_fees'] );
	}


	/**
	 * Retrieve all active fees
	 *
	 * @access      public
	 * @since       1.5
	 *
	 * @return      array
	 */

	public function get_fees() {
		return $this->has_fees() ? $_SESSION['edd_cart_fees'] : false;
	}


	/**
	 * Calculate the total fee amount
	 *
	 * Can be negative
	 *
	 * @access      public
	 * @since       1.5
	 *
	 * @return      float
	 */

	public function total() {
		$fees  = $this->get_fees();
		$total = (float) 0.00;
		if( $this->has_fees() ) {
			foreach( $fees as $fee ) {
				$total += $fee['amount'];
			}
		}
		return $total;
	}


	/**
	 * Stores the fees in the payment meta
	 *
	 * @access      public
	 * @since       1.5
	 * @param 		$payment_meta array The meta data to store with the payment
	 * @param 		$payment_data array The info sent from process-purchase.php
	 * @return      array
	*/

	public function record_fees( $payment_meta, $payment_data ) {

		if( $this->has_fees() ) {
			$payment_meta['fees'] = $this->get_fees();
			$_SESSION['edd_cart_fees'] = null;
		}

		return $payment_meta;

	}


}


/**
 * Check if cart has fees applied
 *
 * Just a simple wrapper function for EDD_Fees::has_fees()
 *
 * @access      public
 * @since       1.5
 *
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
 *
 * @return      array
 */

function edd_get_cart_fees() {
	return EDD()->fees->get_fees();
}