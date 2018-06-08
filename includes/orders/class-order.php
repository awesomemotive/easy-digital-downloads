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
		parent::__construct( $object );

		$this->items = edd_get_order_items( array(
			'order_id' => $this->get_id(),
			'orderby'  => 'cart_index',
			'order'    => 'ASC',
		) );

		$this->adjustments = edd_get_order_adjustments( array(
			'object_id'   => $this->get_id(),
			'object_type' => 'order',
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
		// An order number is only retrieved if sequential order numbers are enabled, otherwise the order ID is returned.
		return edd_get_option( 'enable_sequential' ) ? $this->number : $this->id;
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

	/**
	 * Retrieve the discounts applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return array Order discounts.
	 */
	public function get_discounts() {
		if ( empty( $this->adjustments ) ) {
			return array();
		}

		$discounts = array();

		foreach ( $this->adjustments as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'discount' === $adjustment->get_type() ) {
				$discounts[] = $adjustment;
			}
		}

		return $discounts;
	}

	/**
	 * Retrieve the fees applied to the order. This retrieves the fees applied to the entire order and to individual items.
	 *
	 * @since 3.0
	 *
	 * @return array Order fees.
	 */
	public function get_fees() {
		if ( empty( $this->adjustments ) ) {
			return array();
		}

		$fees = array();

		// Fetch the fees that applied to the entire order.
		foreach ( $this->get_adjustments() as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'fee' === $adjustment->get_type() ) {
				$fees[] = $adjustment;
			}
		}

		// Fetch the fees that applied to specific items in the order.
		foreach ( $this->get_items() as $item ) {
			/** @var Order_Item $item */

			foreach ( $item->get_fees() as $fee ) {
				$fees[] = $fee;
			}
		}

		return $fees;
	}

	/**
	 * Retrieve the transaction ID associated with the order.
	 *
	 * @since 3.0
	 *
	 * @return string Transaction ID.
	 */
	public function get_transaction_id() {
		return edd_get_order_meta( $this->id, 'transaction_id', true );
	}

	/**
	 * Retrieve the tax rate associated with the order.
	 *
	 * @since 3.0
	 *
	 * @return string Tax rate.
	 */
	public function get_tax_rate() {

		// Default rate
		$rate = 0;

		// Query for rates
		$rates = edd_get_order_adjustments( array(
			'object_id'     => $this->id,
			'object_type'   => 'order',
			'type_id'       => 0,
			'type'          => 'tax',
			'number'        => 1,
			'no_found_rows' => true,
		) );

		// Get rate amount
		if ( ! empty( $rates ) ) {
			$rate = reset( $rates );
			$rate = $rate->get_amount();
		}

		return $rate;
	}

	/**
	 * Retrieve the customer information associated with the order.
	 *
	 * @since 3.0
	 *
	 * @return array User information.
	 */
	public function get_user_info() {
		return edd_get_order_meta( $this->id, 'user_info', true );
	}

	/**
	 * Retrieve the customer address associated with the order.
	 *
	 * @since 3.0
	 *
	 * @return string Customer address.
	 */
	public function get_customer_address() {
		$user_info = edd_get_order_meta( $this->id, 'user_info', true );

		$address = ! empty( $user_info['address'] )
			? $user_info['address']
			: array();

		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'country' => '',
			'state'   => '',
			'zip'     => '',
		);

		return wp_parse_args( $address, $defaults );
	}

	/**
	 * Retrieve whether or not unlimited downloads have been enabled on this order.
	 *
	 * @since 3.0
	 *
	 * @return bool True if unlimited downloads are enabled, false otherwise.
	 */
	public function has_unlimited_downloads() {
		return (bool) edd_get_order_meta( $this->id, 'unlimited_downloads', true );
	}

	/**
	 * Retrieve all the notes for this order.
	 *
	 * @since 3.0
	 *
	 * @return array Notes associated with this order.
	 */
	public function get_notes() {
		$notes = edd_get_notes( array(
			'object_id'   => $this->get_id(),
			'object_type' => 'order',
			'order'       => 'ASC',
		) );

		return $notes;
	}
}