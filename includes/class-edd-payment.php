<?php
/**
 * Payments
 *
 * This class is for working with payments in EDD.
 *
 * @package     EDD
 * @subpackage  Classes/Payment
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * EDD_Payment Class
 *
 * @since 2.2
 */
class EDD_Payment {

	/**
	 * The Payment we are working with
	 *
	 * @var int
	 * @access private
	 * @since 2.2
	 */
	private $payment_id;


	/**
	 * Setup the EDD Payments class
	 *
	 * @since 2.2
	 * @param int $payment_id A given payment
	 * @return mixed void|false
	 */
	public function __construct( $payment_id = false ) {
		// If no payment ID is specified, create a new payment
		if( ! $payment_id ) {
			$args = array(
				'post_title'    => '',
				'post_status'   => 'pending',
				'post_type'     => 'edd_payment'
			);

			$payment_id = wp_insert_post( $args );
		}

		// Store the payment ID for later use
		if( $payment_id ) {
			$this->payment_id = $payment_id;
		} else {
			return false;
		}
	}


	/**
	 * Add a download to a given payment
	 *
	 * @since 2.2
	 * @param int $download_id The download to add
	 * @param int $args Other arguments to pass to the function
	 * @return void
	 */
	public function add_download( $download_id, $args = array() ) {
		// Bail if no download ID specified
		if( ! $download_id ) {
			return false;
		}

		$download = get_post( $download_id );

		// Bail if this post isn't a download
		if( $download->post_type !== 'download' ) {
			return false;
		}

		// Set some defaults
		$defaults = array(
			'quantity'    => 1,
			'price_id'    => false,
			'amount'      => false,
			'tax'         => 0,
			'fees'        => 0
		);

		$args = wp_parse_args( apply_filters( 'edd_payment_add_download_args', $args ), $defaults );

		// Allow overriding the price
		if( $args['amount'] ) {
			$item_price = $args['amount'];
		} else {
			// Deal with variable pricing
			if( edd_has_variable_prices( $download_id ) ) {
				$prices = get_post_meta( $download_id, 'edd_variable_prices', true );

				if( $args['price_id'] && array_key_exists( $args['price_id'], (array) $prices ) ) {
					$item_price = $prices[$args['price_id']]['amount'];
				} else {
					$item_price = edd_get_lowest_price_option( $download_id );
					$args['price_id'] = edd_get_lowest_price_id( $download_id );
				}
			} else {
				$item_price = edd_get_download_price( $download_id );
			}
		}

		// Sanitizing the price here so we don't have a dozen calls later
		$item_price = edd_sanitize_amoun( $item_price );

		// Silly item_number array
		$item_number = array(
			'id'        => $download_id,
			'quantity'  => $args['quantity'],
			'options'   => array(
				'price_id'  => $args['price_id'],
				'quantity'  => $args['quantity']
			)
		);

		$cart_details[] = array(
			'name'          => $download->post_title,
			'id'		    => $download_id,
			'item_number'   => $item_number,
			'price'         => $item_price,
			'quantity'      => $args['quantity'],
			'tax'           => $args['tax'],
			'subtotal'      => ( $item_price * $args['quantity'] )
		);

		$purchase_data = array(
			'downloads'     => (array) $download,
			'cart_details'  => $cart_details,
		);

		// Retrieve the current meta
		$meta = edd_get_payment_meta( $download_id );

		$new_meta = array(
			'downloads'    = array( $download ),
			'cart_details' = $cart_details
		);

		edd_update_payment_meta( $this->payment_id, '_edd_payment_meta', array_merge( $meta, $new_meta ) );

		// TODO: Still need to update purchase_data... I don't see anywhere that's being done?
	}


	/**
	 * Add a fee to a given payment
	 *
	 * @since 2.2
	 * @param string $label The description of the fee
	 * @param int $amount The amount of the fee
	 * @return void
	 */
	public function add_fee( $label = '', $amount ) {

	}


	/**
	 * Add a discount to a given payment
	 *
	 * @since 2.2
	 * @param string $code The discount code to apply
	 * @return void
	 */
	public function add_discount( $code ) {

	}


	/**
	 * Set or update the total for a payment
	 *
	 * @since 2.2
	 * @param int $amount The amount of the payment
	 * @return void
	 */
	public function set_total( $amount ) {

	}


	/**
	 * Set the tax for a payment
	 *
	 * @since 2.2
	 * @param int $amount The amount of the tax
	 * @return void
	 */
	public function set_tax( $amount ) {

	}


	/**
	 * Add a note to a payment
	 *
	 * @since 2.2
	 * @param string $note The note to add
	 * @return void
	 */
	public function add_note( $note = '' ) {

	}


	/**
	 * Complete processing of the payment
	 *
	 * @since 2.2
	 * @return void
	 */
	public function complete() {

	}
}
