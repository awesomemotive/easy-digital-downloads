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
 * @since       2.4
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * EDD_Payment Class
 *
 * @since 2.4
 */
class EDD_Payment {

	/**
	 * The Payment we are working with
	 *
	 * @var int
	 * @access private
	 * @since 2.4
	 */
	public $ID;
	public $number;
	public $key;
	public $total;
	public $subtotal;
	public $tax;
	public $fees;
	public $discount;
	public $discounts = array();
	public $date;
	public $completed_date;
	public $status;
	public $post_status; // Same as $status but here for backwards compat
	public $customer_id;
	public $user_info;
	public $transaction_id;
	public $downloads = array();
	public $ip;
	public $gateway;
	public $currency;
	public $cart_details;
	public $downloads;


	/**
	 * Setup the EDD Payments class
	 *
	 * @since 2.4
	 * @param int $payment_id A given payment
	 * @return mixed void|false
	 */
	public function __construct( $payment_id = false ) {

		if( empty( $payment_id ) ) {
			return false;
		}

		$this->setup_payment( $payment_id );

	}

	/**
	 * Setup payment properties
	 *
	 * @since  2.4
	 * @param  int $payment_id The payment ID
	 * @return bool            If the setup was successful or not
	 */
	private function setup_payment( $payment_id ) {

		if ( empty( $payment_id ) ) {
			return false;
		}

		$payment = get_post( $payment_id );

		if( ! $payment || is_wp_error( $payment ) ) {
			return false;
		}

		if( 'edd_payment' !== $payment->post_type ) {
			return false;
		}

		$this->ID             = absint( $payment_id );
		$this->total          = edd_get_payment_amount( $payment_id );
		$this->subtotal       = edd_get_payment_subtotal( $payment_id );
		$this->tax            = edd_get_payment_tax( $payment_id );
		$this->fees           = edd_get_payment_fees( $payment_id );
		$this->customer_id    = edd_get_payment_customer_id( $payment_id );
		$this->transaction_id = edd_get_payment_transaction_id( $payment_id );
		$this->ip             = edd_get_payment_user_ip( $payment_id );
		$this->date           = $payment->post_date;
		$this->completed_date = edd_get_payment_completed_date( $payment_id );
		$this->gateway        = edd_get_payment_gateway( $payment_id );
		$this->currency       = edd_get_payment_currency_code( $payment_id );
		$this->key            = edd_get_payment_key( $payment_id );
		$this->number         = edd_get_payment_number( $payment_id );
		$this->cart_details   = edd_get_payment_meta( $payment_id );
		$this->user_info      = edd_get_payment_meta_user_info( $payment_id );
		$this->downloads      = edd_get_payment_meta_downloads( $payment_id );

		return false;

	}

	/**
	 * Add a download to a given payment
	 *
	 * @since 2.4
	 * @param int $download_id The download to add
	 * @param int $args Other arguments to pass to the function
	 * @return void
	 */
	public function add_download( $download_id = 0, $args = array() ) {
		$download = get_post( $download_id );

		// Bail if this post isn't a download
		if( ! $download || $download->post_type !== 'download' ) {
			return false;
		}

		// Set some defaults
		$defaults = array(
			'quantity'    => 1,
			'price_id'    => false,
			'amount'      => false,
			'tax'         => 0,
			'fees'        => array()
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
		$item_price = edd_sanitize_amount( $item_price );

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

		// Retrieve the current meta
		$downloads   = edd_get_payment_meta_downloads( $download_id );
        $downloads[] = $download;

		$purchase_data = array(
			'downloads'     => (array) $downloads,
			'cart_details'  => $cart_details,
		);

		$meta = edd_get_payment_meta( $download_id );

		$new_meta = array(
			'cart_details' = $cart_details
		);

		edd_update_payment_meta( $this->payment_id, '_edd_payment_meta', array_merge( $meta, $new_meta ) );

		// TODO: Still need to update purchase_data... I don't see anywhere that's being done?
	}


	/**
	 * Add a fee to a given payment
	 *
	 * @since 2.4
	 * @param string $label The description of the fee
	 * @param int $amount The amount of the fee
	 * @return void
	 */
	public function add_fee( $label = '', $amount ) {

	}


	/**
	 * Add a discount to a given payment
	 *
	 * @since 2.4
	 * @param string $code The discount code to apply
	 * @return void
	 */
	public function add_discount( $code ) {

	}


	/**
	 * Set or update the total for a payment
	 *
	 * @since 2.4
	 * @param int $amount The amount of the payment
	 * @return void
	 */
	public function set_total( $amount ) {

	}


	/**
	 * Set the tax for a payment
	 *
	 * @since 2.4
	 * @param int $amount The amount of the tax
	 * @return void
	 */
	public function set_tax( $amount ) {

	}


	/**
	 * Add a note to a payment
	 *
	 * @since 2.4
	 * @param string $note The note to add
	 * @return void
	 */
	public function add_note( $note = false ) {
		// Bail if no note specified
		if( ! $note ) {
			return false;
		}

		edd_insert_payment_note( $this->payment_id, $note );
	}


	/**
	 * Complete processing of the payment
	 *
	 * @since 2.4
	 * @return void
	 */
	public function complete() {

	}
}
