<?php
/**
 * Fees
 *
 * This class is for adding arbitrary fees to the cart. Fees can be positive or negative (discounts)
 *
 * @package     EDD
 * @subpackage  Classes/Fees
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Fees Class
 *
 * @since 1.5
 */
class EDD_Fees {

	/**
	 * Setup the EDD Fees
	 *
	 * @since 1.5
	 * @return void
	 */
	public function __construct() {
		add_filter( 'edd_payment_meta', array( $this, 'record_fees' ), 10, 2 );
	}

	/**
	 * Adds a new Fee
	 *
	 * @access public
	 * @since 1.5
	 * @param int $amount Fee Amount
	 * @param string $label Fee label
	 * @param string $id Fee ID
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Session::set()
	 * @return array $fees
	 */
	public function add_fee( $amount = '', $label = '', $id = '' ) {
		$fees = $this->get_fees();

		$key = empty( $id ) ? sanitize_key( $label ) : sanitize_key( $id );

		$fees[ $key ] = array( 'amount' => $amount, 'label' => $label );

		EDD()->session->set( 'edd_cart_fees', $fees );

		return $fees;
	}

	/**
	 * Remove an Existing Fee
	 *
	 * @access public
	 * @since 1.5
	 * @param string $id Fee ID
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Session::set()
	 * @return array $fees
	 */
	public function remove_fee( $id = '' ) {
		$fees = $this->get_fees();

		if ( isset( $fees[ $id ] ) ) {
			unset( $fees[ $id ] );
		}

		EDD()->session->set( 'edd_cart_fees', $fees );

		return $fees;
	}

	/**
	 * Check if any fees are present
	 *
	 * @access public
	 * @since 1.5
	 * @uses EDD_Fees::get_fees()
	 * @return bool
	 */
	public function has_fees() {
		$fees = $this->get_fees();
		return ! empty( $fees ) && is_array( $fees );
	}

	/**
	 * Retrieve all active fees
	 *
	 * @access public
	 * @since 1.5
	 * @uses EDD_Session::get()
	 * @return mixed array|bool
	 */
	public function get_fees() {
		$fees = EDD()->session->get( 'edd_cart_fees' );
		return ! empty( $fees ) ? $fees : array();
	}

	/**
	 * Retrieve a specific fee
	 *
	 * @access public
	 * @since 1.5
	 * @uses EDD_Fees::get_fees()
	 * @return mixed array|bool
	 */
	public function get_fee( $id = '' ) {
		$fees = $this->get_fees();

		if ( ! isset( $fees[ $id ] ) )
			return false;

		return $fees[ $id ];
	}

	/**
	 * Calculate the total fee amount
	 *
	 * Can be negative
	 *
	 * @access public
	 * @since 1.5
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Fees::has_fees()
	 * @return float $total Total fee amount
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
	 * @access public
	 * @since 1.5
	 * @uses EDD_Session::set()
	 * @param array $payment_meta The meta data to store with the payment
	 * @param array $payment_data The info sent from process-purchase.php
	 * @return array $payment_meta Return the payment meta with the fees added
	*/
	public function record_fees( $payment_meta, $payment_data ) {
		if ( $this->has_fees() ) {
			$payment_meta['fees'] = $this->get_fees();
			EDD()->session->set( 'edd_cart_fees', null );
		}

		return $payment_meta;
	}
}