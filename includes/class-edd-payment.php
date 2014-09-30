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
	 * @param int $price_id A price ID for the download
	 * @return void
	 */
	public function add_download( $download_id, $price_id = false ) {

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
