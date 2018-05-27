<?php
/**
 * Order Object.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Orders;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Class.
 *
 * @since 3.0
 */
class Order extends Base_Object {

	/**
	 * Order ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $id;

	/**
	 * Parent order.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $parent;

	/**
	 * Order number.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $number;

	/**
	 * Order status.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $status;

	/**
	 * Date created.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $date_created;

	/**
	 * Date completed.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $date_completed;

	/**
	 * User ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $user_id;

	/**
	 * Customer ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $customer_id;

	/**
	 * Email.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $email;

	/**
	 * IP.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $ip;

	/**
	 * Order gateway.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $gateway;

	/**
	 * Order mode.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $mode;

	/**
	 * Order currency.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $currency;

	/**
	 * Payment key.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $payment_key;

	/**
	 * Subtotal.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $subtotal;

	/**
	 * Tax.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $tax;

	/**
	 * Order discount.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $discount;

	/**
	 * Order total.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $total;

	/**
	 * Order items.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $items;

	/**
	 * Order adjustments.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $adjustments;

	/**
	 * Object constructor.
	 *
	 * @access public
	 * @since  1.9
	 *
	 * @param mixed $object Object to populate members for.
	 */
	public function __construct( $object = null ) {
		if ( $object ) {
			foreach ( get_object_vars( $object ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}

		$this->items = edd_get_order_items( array(
			'order_id' => $this->get_id()
		) );

		$this->adjustments = edd_get_order_adjustments( array(
			'order_id' => $this->get_id()
		) );
	}

	/**
	 * Retrieve order ID.
	 *
	 * @since 3.0
	 *
	 * @return int Order ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve parent order ID.
	 *
	 * @since 3.0
	 *
	 * @return int Parent order ID.
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Retrieve order number.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * Retrieve order status.
	 *
	 * @since 3.0
	 *
	 * @return string Order status.
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Retrieve the date the order was created.
	 *
	 * @since 3.0
	 *
	 * @return string Date order was created.
	 */
	public function get_date_created() {
		return $this->date_created;
	}

	/**
	 * Retrieve the date the order was completed.
	 *
	 * @since 3.0
	 *
	 * @return string Date order was completed.
	 */
	public function get_date_completed() {
		return $this->date_completed;
	}

	/**
	 * Retrieve user ID associated with order.
	 *
	 * @since 3.0
	 *
	 * @return int User ID.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Retrieve customer ID associated with order.
	 *
	 * @since 3.0
	 *
	 * @return int Customer ID.
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Retrieve email address associated with order.
	 *
	 * @since 3.0
	 *
	 * @return string Email address.
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Retrieve IP address used to complete the order.
	 *
	 * @since 3.0
	 *
	 * @return string IP address.
	 */
	public function get_ip() {
		return $this->ip;
	}

	/**
	 * Retrieve the payment gateway used to complete the order.
	 *
	 * @since 3.0
	 *
	 * @return string Payment gateway.
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Retrieve the mode (i.e. test or live) that the order was placed in.
	 *
	 * @since 3.0
	 *
	 * @return string Order mode.
	 */
	public function get_mode() {
		return $this->mode;
	}

	/**
	 * Retrieve the currency that was used for the order.
	 *
	 * @since 3.0
	 *
	 * @return string Order currency.
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Retrieve payment key.
	 *
	 * @since 3.0
	 *
	 * @return string Payment key.
	 */
	public function get_payment_key() {
		return $this->payment_key;
	}

	/**
	 * Retrieve order subtotal.
	 *
	 * @since 3.0
	 *
	 * @return float Subtotal.
	 */
	public function get_subtotal() {
		return $this->subtotal;
	}

	/**
	 * Retrieve tax applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return float Tax.
	 */
	public function get_tax() {
		return $this->tax;
	}

	/**
	 * Retrieve the discounted amount that was applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return array Order discount.
	 */
	public function get_discount() {
		return $this->discount;
	}

	/**
	 * Retrieve order total.
	 *
	 * @since 3.0
	 *
	 * @return float Order total.
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Retrieve all the items in the order.
	 *
	 * @since 3.0
	 *
	 * @return array Order items.
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Retrieve all the adjustments applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return array Order adjustments.
	 */
	public function get_adjustments() {
		return $this->adjustments;
	}
}