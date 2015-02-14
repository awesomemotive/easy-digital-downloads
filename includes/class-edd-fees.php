<?php
/**
 * Fees
 *
 * This class is for adding arbitrary fees to the cart. Fees can be positive or negative (discounts)
 *
 * @package     EDD
 * @subpackage  Classes/Fees
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
	 */
	public function __construct() {
		add_filter( 'edd_payment_meta', array( $this, 'record_fees' ), 10, 2 );
	}

	/**
	 * Adds a new Fee
	 *
	 * @since 1.5
	 *
	 * @param array $args Fee arguments
	 *
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Session::set()
	 *
	 * @return mixed
	 */
	public function add_fee( $args = array() ) {

		// Backwards compatabliity with pre 2.0
		if ( func_num_args() > 1 ) {

			$args   = func_get_args();
			$amount = $args[0];
			$label  = isset( $args[1] ) ? $args[1] : '';
			$id     = isset( $args[2] ) ? $args[2] : '';
			$type   = 'fee';

			$args = array(
				'amount' => $amount,
				'label'  => $label,
				'id'     => $id,
				'type'   => $type,
				'no_tax' => false,
				'download_id' => 0
			);

		} else {

			$defaults = array(
				'amount'      => 0,
				'label'       => '',
				'id'          => '',
				'no_tax'      => false,
				'type'        => 'fee',
				'download_id' => 0
			);

			$args = wp_parse_args( $args, $defaults );

			if( $args['type'] != 'fee' && $args['type'] != 'item' ) {
				$args['type'] = 'fee';
			}

		}

		if( 'item' === $args['type'] && ! empty( $args['download_id'] ) ) {
			unset( $args['download_id'] );
		}

		$fees = $this->get_fees( 'all' );

		// Determine the key
		$key = empty( $args['id'] ) ? sanitize_key( $args['label'] ) : sanitize_key( $args['id'] );

		// Remove the unneeded id key
		unset( $args['id'] );

		// Sanitize the amount
		$args['amount'] = edd_sanitize_amount( $args['amount'] );

		// Set the fee
		$fees[ $key ] = $args;

		// Update fees
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

		$fees = $this->get_fees( 'all' );

		if ( isset( $fees[ $id ] ) ) {
			unset( $fees[ $id ] );
			EDD()->session->set( 'edd_cart_fees', $fees );
		}

		return $fees;
	}

	/**
	 * Check if any fees are present
	 *
	 * @access public
	 * @since 1.5
	 * @param string $type Fee type, "fee" or "item"
	 * @uses EDD_Fees::get_fees()
	 * @return bool
	 */
	public function has_fees( $type = 'fee' ) {

		if( 'all' == $type || 'fee' == $type ) {

			if( ! edd_get_cart_contents() ) {
				$type = 'item';
			}

		}

		$fees = $this->get_fees( $type );
		return ! empty( $fees ) && is_array( $fees );
	}

	/**
	 * Retrieve all active fees
	 *
	 * @access public
	 * @since 1.5
	 * @param string $type Fee type, "fee" or "item"
	 * @param int $download_id The download ID whose fees to retrieve
	 * @uses EDD_Session::get()
	 * @return mixed array|bool
	 */
	public function get_fees( $type = 'fee', $download_id = 0 ) {

		$fees = EDD()->session->get( 'edd_cart_fees' );

		if( ! edd_get_cart_contents() ) {
			// We can only get item type fees when the cart is empty
			$type = 'item';
		}

		if( ! empty( $fees ) && ! empty( $type ) && 'all' !== $type ) {

			foreach( $fees as $key => $fee ) {

				if( ! empty( $fee['type'] ) && $type != $fee['type'] ) {

					unset( $fees[ $key ] );

				}

			}

		}

		if( ! empty( $fees ) && ! empty( $download_id ) ) {

			// Remove fees that don't belong to the specified Download
			foreach( $fees as $key => $fee ) {

				if( (int) $download_id !== (int) $fee['download_id'] ) {

					unset( $fees[ $key ] );

				}

			}

		}

		if( ! empty( $fees ) ) {

			// Remove fees that belong to a specific download but are not in the cart
			foreach( $fees as $key => $fee ) {

				if( empty( $fee['download_id'] ) ) {
					continue;
				}

				if( ! edd_item_in_cart( $fee['download_id'] ) ) {

					unset( $fees[ $key ] );

				}

			}

		}

		return ! empty( $fees ) ? $fees : array();
	}

	/**
	 * Retrieve a specific fee
	 *
	 * @since 1.5
	 *
	 * @param string $id
	 * @return bool
	 */
	public function get_fee( $id = '' ) {
		$fees = $this->get_fees( 'all' );

		if ( ! isset( $fees[ $id ] ) )
			return false;

		return $fees[ $id ];
	}

	/**
	 * Calculate the total fee amount for a specific fee type
	 *
	 * Can be negative
	 *
	 * @access public
	 * @since 2.0
	 * @param string $type Fee type, "fee" or "item"
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Fees::has_fees()
	 * @return float $total Total fee amount
	 */
	public function type_total( $type = 'fee' ) {
		$fees  = $this->get_fees( $type );
		$total = (float) 0.00;

		if ( $this->has_fees( $type ) ) {
			foreach ( $fees as $fee ) {
				$total += edd_sanitize_amount( $fee['amount'] );
			}
		}

		return edd_sanitize_amount( $total );
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
	 * @param int $download_id The download ID whose fees to retrieve
	 * @return float $total Total fee amount
	 */
	public function total( $download_id = 0 ) {
		$fees  = $this->get_fees( 'all', $download_id );
		$total = (float) 0.00;

		if ( $this->has_fees( 'all' ) ) {
			foreach ( $fees as $fee ) {
				$total += edd_sanitize_amount( $fee['amount'] );
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
		if ( $this->has_fees( 'all' ) ) {
			$payment_meta['fees'] = $this->get_fees( 'all' );
			EDD()->session->set( 'edd_cart_fees', null );
		}

		return $payment_meta;
	}
}