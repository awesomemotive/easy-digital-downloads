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
	 * Active fees
	 *
	 * @access      private
	 * @since       1.5
	 *
	 */

	private $active_fees = array();


	/**
	 * Get us started
	 *
	 * @access      private
	 * @since       1.5
	 *
	 * @return      void
	 */

	function __construct() {
		add_action( 'edd_cart_items_after', array( $this, 'show_fees' ) );
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
		$this->active_fees[] = array( 'amount' => $amount, 'label' => $label );
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
		return ! empty( $this->active_fees );
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
		return $this->active_fees;
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
	 * Shows fees on checkout
	 *
	 * @access      public
	 * @since       1.5
	 *
	 * @return      void
	 */

	public function show_fees() {

		$fees = $this->get_fees();

		if( ! $this->has_fees() )
			return;

		foreach( $fees as $fee ) {
			echo '<tr class="edd_cart_fee">';
				echo '<td class="edd_cart_fee_label"><span>' . esc_html( $fee['label'] ) . '</span></td>';
				echo '<td class="edd_cart_fee_amount"><span>' . esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ) . '</span></td>';
				echo '<td></td>';
			echo '</tr>';
		}

	}


}