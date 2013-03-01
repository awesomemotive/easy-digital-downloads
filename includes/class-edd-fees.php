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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fees Class
 *
 * @access      public
 * @since       1.5
 */
class EDD_Fees {

	/**
	 * Get us started
	 *
	 * @access      private
	 * @since       1.5
	 * @return      void
	 */
	public function __construct() {
		add_filter( 'edd_payment_meta', array( $this, 'record_fees' ), 10, 2 );
	}

	/**
	 * Add a new fee
	 *
	 * @access      public
	 * @since       1.5
	 * @return      array
	 */
	public function add_fee( $amount = '', $label = '', $id = '' ) {

		$fees = $this->get_fees();

		$key = empty( $id ) ? sanitize_key( $label ) : sanitize_key( $id );

		$fees[ $key ] = array( 'amount' => $amount, 'label' => $label );

		$_SESSION['edd_cart_fees'] = $fees;

		return $fees;
	}


	/**
	 * Remove an existing fee
	 *
	 * @access      public
	 * @since       1.5
	 * @return      array
	 */
	public function remove_fee( $id = '' ) {

		$fees = $this->get_fees();

		if( isset( $fees[ $id ] ) ) {
			unset( $fees[ $id ] );
		}

		$_SESSION['edd_cart_fees'] = $fees;

		return $fees;

	}


	/**
	 * Check if any fees are present
	 *
	 * @access      public
	 * @since       1.5
	 * @return      bool
	 */
	public function has_fees() {
		return ! empty( $_SESSION['edd_cart_fees'] ) && is_array( $_SESSION['edd_cart_fees'] );
	}

	/**
	 * Retrieve all active fees
	 *
	 * @access      public
	 * @since       1.5
	 * @return      array|bool
	 */
	public function get_fees() {
		return $this->has_fees() ? $_SESSION['edd_cart_fees'] : array();
	}


	/**
	 * Retrieve a specific fee
	 *
	 * @access      public
	 * @since       1.5
	 * @return      array|bool
	 */
	public function get_fee( $id = '' ) {

		$fees = $this->get_fees();

		if( !isset( $fees[ $id ] ) )
			return false;

		return $fees[ $id ];

	}


	/**
	 * Calculate the total fee amount
	 *
	 * Can be negative
	 *
	 * @access      public
	 * @since       1.5
	 * @return      float
	 */
	public function total() {
		$fees  = $this->get_fees();
		$total = (float) 0.00;
		if ( $this->has_fees() ) {
			foreach ( $fees as $fee ) {
				$total += $fee['amount'];
			}
		}
		return edd_sanitize_amount( $total );
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
		if ( $this->has_fees() ) {
			$payment_meta['fees'] = $this->get_fees();
			$_SESSION['edd_cart_fees'] = null;
		}

		return $payment_meta;
	}
}