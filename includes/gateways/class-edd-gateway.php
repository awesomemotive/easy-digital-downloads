<?php
/**
 * Payment Gateway Abstract Class
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Gateway Class.
 *
 * @since   2.7
 * @version 1.0
 */
abstract class EDD_Gateway {
	/**
	 * Gateway ID.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $ID;

	/**
	 * Checkout Label.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $checkout_label;

	/**
	 * Amdin Label.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $admin_label;

	/**
	 * Purchase Data.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $purchase_data;

	/**
	 * Customer ID.
	 *
	 * @access public
	 * @since  2.7
	 * @var    int
	 */
	public $customer_id;

	/**
	 * Customer object.
	 *
	 * @access public
	 * @since  2.7
	 * @var    object
	 */
	public $customer;

	/**
	 * Gateway Supports.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $supports = array();

	/**
	 * Is test mode?
	 *
	 * @access public
	 * @since  2.7
	 * @var    bool
	 */
	public $test_mode;

	/**
	 * Price.
	 *
	 * @access public
	 * @since  2.7
	 * @var    float
	 */
	public $price;

	/**
	 * Email address.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $email;

	/**
	 * User info.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $user_info;

	/**
	 * Purchase Key.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $purchase_key;

	/**
	 * Currency Code.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $currency;

	/**
	 * Downloads associated with the purchase.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $downloads;

	/**
	 * Status.
	 *
	 * @access public
	 * @since  2.7
	 * @var    string
	 */
	public $status;

	/**
	 * Cart details.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $cart_details;

	/**
	 * Constructor to initialise gateway.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param array $purchase_data Purchase data.
	 * @return void
	 */
	public function __construct( $purchase_data = array() ) {
		$this->setup_purchase_data( $purchase_data );
		$this->fill_vars();
		$this->init();
	}

	/**
	 * Setup the purchase data with the object vars.
	 *
	 * @access private
	 * @since  2.7
	 *
	 * @return void
	 */
	private function setup_purchase_data( $purchase_data = array() ) {
		$this->price        = $purchase_data['price'];
		$this->date         = $purchase_data['date'];
		$this->email        = $purchase_data['user_email'];
		$this->purchase_key = $purchase_data['purchase_key'];
		$this->currency     = edd_get_currency();
		$this->downloads    = $purchase_data['downloads'];
		$this->user_info    = $purchase_data['user_info'];
		$this->cart_details = $purchase_data['cart_details'];
		$this->status       = ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending';
	}

	/**
	 * Fill the object vars based on the ID set.
	 *
	 * @access private
	 * @since  2.7
	 *
	 * @return void
	 */
	private function fill_vars() {
		$gateways = EDD()->gateways->get_gateways();

		$this->test_mode = edd_is_test_mode();

		if ( array_key_exists( $this->ID, $gateways ) ) {
			$this->checkout_label = $gateways[ $this->ID ]['checkout_label'];
			$this->admin_label = $gateways[ $this->ID ]['admin_label'];
			$this->supports = $gateways[ $this->ID ]['supports'];
		}
	}

	/**
	 * Used to initialise the gateway.
	 *
	 * @access public
	 * @since  2.7
	 * @abstract
	 *
	 * @return void
	 */
	public function init() {}

	/**
	 * Process the webhooks (e.g. PayPal IPN).
	 *
	 * @access public
	 * @since  2.7
	 * @abstract
	 *
	 * @return void
	 */
	public function process_webhooks() {}

	/**
	 * Load any scripts required by the gateway.
	 *
	 * @access public
	 * @since  2.7
	 * @abstract
	 *
	 * @return void
	 */
	public function scripts() {}

	/**
	 * Print the fields for the gateway.
	 *
	 * @access public
	 * @since  2.7
	 * @abstract
	 *
	 * @return void
	 */
	public function fields() {}

	/**
	 * Validate fields
	 *
	 * @access public
	 * @since  2.7
	 * @abstract
	 *
	 * @return void
	 */
	public function validate_fields() {}

	/**
	 * Check if a gateway supports a feature.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param string $item Item to be checked.
	 * @return bool Whether the item is supported or not.
	 */
	public function supports( $item = '' ) {
		return in_array( $item, $this->supports );
	}

	/**
	 * Add an error that is displayed on the checkout.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @return void
	 */
	public function add_error( $code = '', $message = '' ) {
		edd_set_error( $code, $message );
	}

	/**
	 * Process the purchase.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return void
	 */
	public function process_purchase() {}
}