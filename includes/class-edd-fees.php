<?php
/**
 * Fees
 *
 * This class is for adding arbitrary fees to the cart. Fees can be positive or negative (discounts)
 *
 * @package     EDD
 * @subpackage  Classes/Fees
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
	 * @return array The fees.
	 */
	public function add_fee( $args = array() ) {

		// Backwards compatibility with pre 2.0
		if ( func_num_args() > 1 ) {

			$args     = func_get_args();
			$amount   = $args[0];
			$label    = isset( $args[1] ) ? $args[1] : '';
			$id       = isset( $args[2] ) ? $args[2] : '';
			$type     = 'fee';

			$args = array(
				'amount' => $amount,
				'label'  => $label,
				'id'     => $id,
				'type'   => $type,
				'no_tax' => false,
				'download_id' => 0,
				'price_id'    => NULL
			);

		} else {

			$defaults = array(
				'amount'      => 0,
				'label'       => '',
				'id'          => '',
				'no_tax'      => false,
				'type'        => 'fee',
				'download_id' => 0,
				'price_id'    => NULL
			);

			$args = wp_parse_args( $args, $defaults );

			if( $args['type'] != 'fee' && $args['type'] != 'item' ) {
				$args['type'] = 'fee';
			}

		}

		// If the fee is for an "item" but we passed in a download id
		if( 'item' === $args['type'] && ! empty( $args['download_id'] ) ) {
			unset( $args['download_id'] );
			unset( $args['price_id'] );
		}

		if ( ! empty( $args['download_id'] ) ) {
			$options = isset( $args['price_id'] ) ? array( 'price_id' => $args['price_id'] ) : array();
			if ( ! edd_item_in_cart( $args['download_id'], $options ) ) {
				return false;
			}
		}

		$fees = $this->get_fees( 'all' );

		// Determine the key
		$key = empty( $args['id'] ) ? sanitize_key( $args['label'] ) : sanitize_key( $args['id'] );

		// Remove the unneeded id key
		unset( $args['id'] );

		// Sanitize the amount
		$args['amount'] = edd_sanitize_amount( $args['amount'] );

		// Force the amount to have the proper number of decimal places.
		$args['amount'] = number_format( (float) $args['amount'], edd_currency_decimal_filter(), '.', '' );

		// Force no_tax to true if the amount is negative
		if( $args['amount'] < 0 ) {
			$args['no_tax'] = true;
		}

		// Set the fee
		$fees[ $key ] = apply_filters( 'edd_fees_add_fee', $args, $this );

		// Allow 3rd parties to process the fees before storing them in the session
		$fees = apply_filters( 'edd_fees_set_fees', $fees, $this );

		// Update fees
		EDD()->session->set( 'edd_cart_fees', $fees );

		do_action( 'edd_post_add_fee', $fees, $key, $args );

		return $fees;
	}

	/**
	 * Remove an Existing Fee
	 *
	 * @since 1.5
	 * @param string $id Fee ID
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Session::set()
	 * @return array Remaining fees
	 */
	public function remove_fee( $id = '' ) {

		$fees = $this->get_fees( 'all' );

		if ( isset( $fees[ $id ] ) ) {
			unset( $fees[ $id ] );
			EDD()->session->set( 'edd_cart_fees', $fees );

			do_action( 'edd_post_remove_fee', $fees, $id );
		}

		return $fees;
	}

	/**
	 * Check if any fees are present
	 *
	 * @since 1.5
	 * @param string $type Fee type, "fee" or "item"
	 * @uses EDD_Fees::get_fees()
	 * @return bool True if there are fees, false otherwise
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
	 * @since 1.5
	 * @param string $type Fee type, "fee" or "item"
	 * @param int $download_id The download ID whose fees to retrieve
	 * @param null|int $price_id The variable price ID whose fees to retrieve
	 * @uses EDD_Session::get()
	 * @return array|bool List of fees when available, false when there are no fees
	 */
	public function get_fees( $type = 'fee', $download_id = 0, $price_id = null ) {
		$fees = EDD()->session->get( 'edd_cart_fees' );

		if ( EDD()->cart->is_empty() ) {
			// We can only get item type fees when the cart is empty
			$type = 'item';
		}

		if ( ! empty( $fees ) && ! empty( $type ) && 'all' !== $type ) {
			foreach ( $fees as $key => $fee ) {
				if ( ! empty( $fee['type'] ) && $type != $fee['type'] ) {
					unset( $fees[ $key ] );
				}
			}
		}

		if ( ! empty( $fees ) && ! empty( $download_id ) ) {
			// Remove fees that don't belong to the specified Download
			$applied_fees = array();
			foreach ( $fees as $key => $fee ) {

				if ( empty( $fee['download_id'] ) || (int) $download_id !== (int) $fee['download_id'] ) {
					unset( $fees[ $key ] );
				}

				$string_to_hash = "{$key}_{$download_id}";
				if ( ! is_null( $price_id ) && isset( $fee['price_id'] ) ) {
					$string_to_hash .= "_{$fee['price_id']}";
				}
				$fee_hash = md5( $string_to_hash );

				if ( in_array( $fee_hash, $applied_fees, true ) ) {
					unset( $fees[ $key ] );
				}

				$applied_fees[] = $fee_hash;
			}
		}

		// Now that we've removed any fees that are for other Downloads, lets also remove any fees that don't match this price id
		if ( ! empty( $fees ) && ! empty( $download_id ) && ! is_null( $price_id ) ) {
			// Remove fees that don't belong to the specified Download AND Price ID
			foreach ( $fees as $key => $fee ) {
				if ( is_null( $fee['price_id'] ) ) {
					continue;
				}

				if ( (int) $price_id !== (int) $fee['price_id'] ){
					unset( $fees[ $key ] );
				}
			}
		}

		if ( ! empty( $fees ) ) {
			// Remove fees that belong to a specific download but are not in the cart
			foreach ( $fees as $key => $fee ) {
				if ( empty( $fee['download_id'] ) ) {
					continue;
				}

				if ( ! edd_item_in_cart( $fee['download_id'] ) ) {
					unset( $fees[ $key ] );
				}
			}
		}

		// Allow 3rd parties to process the fees before returning them
		return apply_filters( 'edd_fees_get_fees', ! empty( $fees ) ? $fees : array(), $this );
	}

	/**
	 * Retrieve a specific fee
	 *
	 * @since 1.5
	 *
	 * @param string $id ID of the fee to get
	 * @return array|bool The fee array when available, false otherwise
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
	 * @since 2.0
	 * @param string $type Fee type, "fee" or "item"
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Fees::has_fees()
	 * @return float Total fee amount
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
	 * @since 1.5
	 * @uses EDD_Fees::get_fees()
	 * @uses EDD_Fees::has_fees()
	 * @param int $download_id The download ID whose fees to retrieve
	 * @return float Total fee amount
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
	 * @since 1.5
	 * @uses EDD_Session::set()
	 * @param array $payment_meta The meta data to store with the payment
	 * @param array $payment_data The info sent from process-purchase.php
	 * @return array Return the payment meta with the fees added
	*/
	public function record_fees( $payment_meta, $payment_data ) {
		if ( $this->has_fees( 'all' ) ) {

			$payment_meta['fees'] = $this->get_fees( 'all' );

			// Only clear fees from session when status is not pending
			if( ! empty( $payment_data['status'] ) && 'pending' !== strtolower( $payment_data['status'] ) ) {

				EDD()->session->set( 'edd_cart_fees', null );

			}
		}

		return $payment_meta;
	}

	/**
	 * Gets the tax to be added to a fee.
	 *
	 * @since 3.0
	 * @param  array   $fee
	 * @param  float   $tax_rate
	 * @return float
	 */
	public function get_calculated_tax( $fee, $tax_rate ) {
		$tax = 0.00;
		if ( ! ( $tax_rate || empty( $fee['no_tax'] ) ) || $fee['amount'] < 0 ) {
			return $tax;
		}

		return ( floatval( $fee['amount'] ) * $tax_rate ) / 100;
	}
}
