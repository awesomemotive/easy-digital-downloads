<?php
/**
 * Order Object.
 *
 * @package     EDD
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Orders;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Class.
 *
 * @since 3.0.0
 */
class Order {

	/**
	 * Order ID.
	 *
	 * @since 3.0.0
	 * @var   int
	 */
	protected $id;

	/**
	 * Order number.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $number;

	/**
	 * Order status.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $status;

	/**
	 * Date created.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $date_created;

	/**
	 * Date completed.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $date_completed;

	/**
	 * User ID.
	 *
	 * @since 3.0.0
	 * @var   int
	 */
	protected $user_id;

	/**
	 * Customer ID.
	 *
	 * @since 3.0.0
	 * @var   int
	 */
	protected $customer_id;

	/**
	 * Email.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $email;

	/**
	 * IP.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $ip;

	/**
	 * Payment gateway.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $gateway;

	/**
	 * Payment key.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $payment_key;

	/**
	 * Subtotal.
	 *
	 * @since 3.0.0
	 * @var   float
	 */
	protected $subtotal;

	/**
	 * Tax.
	 *
	 * @since 3.0.0
	 * @var   float
	 */
	protected $tax;

	/**
	 * Order discount.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $discount;

	/**
	 * Order total.
	 *
	 * @since 3.0.0
	 * @var   float
	 */
	protected $total;

	/**
	 * Order items.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $items;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @param object $order Order data directly from the database.
	 */
	public function __construct( $order ) {
		if ( is_object( $order ) ) {
			foreach ( get_object_vars( $order ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Retrieve order ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Order ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve order number.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * Retrieve order status.
	 *
	 * @since 3.0.0
	 *
	 * @return string Order status.
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Retrieve the date the order was created.
	 *
	 * @since 3.0.0
	 *
	 * @return string Date order was created.
	 */
	public function get_date_created() {
		return $this->date_created;
	}

	/**
	 * Retrieve the date the order was completed.
	 *
	 * @since 3.0.0
	 *
	 * @return string Date order was completed.
	 */
	public function get_date_completed() {
		return $this->date_completed;
	}

	/**
	 * Retrieve user ID associated with order.
	 *
	 * @since 3.0.0
	 *
	 * @return int User ID.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Retrieve customer ID associated with order.
	 *
	 * @since 3.0.0
	 *
	 * @return int Customer ID.
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Retrieve email address associated with order.
	 *
	 * @since 3.0.0
	 *
	 * @return string Email address.
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Retrieve IP address used to complete the order.
	 *
	 * @since 3.0.0
	 *
	 * @return string IP address.
	 */
	public function get_ip() {
		return $this->ip;
	}

	/**
	 * Retrieve the payment gateway used to complete the order.
	 *
	 * @since 3.0.0
	 *
	 * @return string Payment gateway.
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Retrieve payment key.
	 *
	 * @since 3.0.0
	 *
	 * @return string Payment key.
	 */
	public function get_payment_key() {
		return $this->payment_key;
	}

	/**
	 * Retrieve order subtotal.
	 *
	 * @since 3.0.0
	 *
	 * @return float Subtotal.
	 */
	public function get_subtotal() {
		return $this->subtotal;
	}

	/**
	 * Retrieve tax applied to the order.
	 *
	 * @since 3.0.0
	 *
	 * @return float Tax.
	 */
	public function get_tax() {
		return $this->tax;
	}

	/**
	 * Retrieve the discounted amount that was applied to the order.
	 *
	 * @since 3.0.0
	 *
	 * @return array Order discount.
	 */
	public function get_discount() {
		return $this->discount;
	}

	/**
	 * Retrieve order total.
	 *
	 * @since 3.0.0
	 *
	 * @return float Order total.
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Retrieve all the items in the order.
	 *
	 * @since 3.0.0
	 *
	 * @return array Order items.
	 */
	public function get_items() {
		return $this->items;
	}
}