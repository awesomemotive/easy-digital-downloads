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

use EDD\Database\Rows as Rows;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $parent
 * @property string $order_number
 * @property string $type
 * @property string $status
 * @property string $date_created
 * @property string $date_modified
 * @property string|null $date_completed
 * @property string|null $date_refundable
 * @property int $user_id
 * @property int $customer_id
 * @property string $email
 * @property string $ip
 * @property string $gateway
 * @property string $mode
 * @property string $currency
 * @property string $payment_key
 * @property float $subtotal
 * @property float $tax
 * @property float $discount
 * @property float $total
 * @property Order_Item[] $items
 * @property Order_Adjustment[] $adjustments
 * @property Order_Address $address
 */
class Order extends Rows\Order {

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
	protected $order_number;

	/**
	 * Order status.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $status;

	/**
	 * Order type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $type;

	/**
	 * Date created.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $date_created;

	/**
	 * Date modified.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $date_modified;

	/**
	 * Date completed.
	 *
	 * @since 3.0
	 * @var   string|null
	 */
	protected $date_completed;

	/**
	 * Date refundable.
	 *
	 * @since 3.0
	 * @var   string|null
	 */
	protected $date_refundable;

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
	 * @var   float
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
	 * @var   \EDD\Orders\Order_Item[]
	 */
	protected $items = null;

	/**
	 * Order adjustments.
	 *
	 * @since 3.0
	 * @var   \EDD\Orders\Order_Adjustment[]
	 */
	protected $adjustments = null;

	/**
	 * Order address.
	 *
	 * @since 3.0
	 * @var   \EDD\Orders\Order_Address
	 */
	protected $address = null;

	/**
	 * Magic getter for immutability.
	 *
	 * @since 3.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		if ( 'adjustments' === $key && null === $this->adjustments ) {
			$this->adjustments = edd_get_order_adjustments( array(
				'object_id'     => $this->id,
				'object_type'   => 'order',
				'no_found_rows' => true,
				'order'         => 'ASC',
			) );
		} elseif ( 'items' === $key && null === $this->items ) {
			$this->items = edd_get_order_items( array(
				'order_id'      => $this->id,
				'orderby'       => 'cart_index',
				'order'         => 'ASC',
				'no_found_rows' => true,
			) );
		}

		return parent::__get( $key );
	}

	/**
	 * Retrieve order number.
	 *
	 * An order number is only retrieved if sequential order numbers are enabled,
	 * otherwise the order ID is returned.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_number() {

		if ( $this->order_number && edd_get_option( 'enable_sequential' ) ) {
			$number = $this->order_number;
		} else {
			$number = $this->id;
		}

		/**
		 * The edd_payment_number filter allows the order_number value to be changed.
		 *
		 * This filter used to run in the  EDD_Payment class's get_number method upon its setup.
		 * It now exists only here in EDD_Order since EDD 3.0. EDD Payment gets its order_number
		 * value from EDD_Order (this class), so it gets run for both EDD_Payment and EDD_Order this way.
		 *
		 * @since 2.5
		 * @since 3.0 Updated the 3rd paramater from an EDD_Payment object to an EDD_Order object.
		 *
		 * @param string    The unique value to represent this order. This is a string because pre-fixes and post-fixes can be appended via the filter.
		 * @param int       The row ID of the Payment/Order.
		 * @param EDD_Order Prior to EDD 3.0, this was an EDD_Payment object. Now it is an EDD_Order object.
		 */
		$number = apply_filters( 'edd_payment_number', $number, $this->ID, $this );

		/**
		 * This filter is exactly the same as edd_payment_number, and exists purely so that
		 * the "order" terminology has a filter as well.
		 *
		 * @since 3.0
		 *
		 * @param string    The unique value to represent this order. This is a string because pre-fixes and post-fixes can be appended via the filter.
		 * @param int       The row ID of the Payment/Order.
		 * @param EDD_Order The EDD_Order object.
		 */
		$number = apply_filters( 'edd_order_number', $number, $this->ID, $this );

		return $number;
	}

	/**
	 * Retrieve all the items in the order.
	 *
	 * @since 3.0
	 *
	 * @return Order_Item[] Order items.
	 */
	public function get_items() {
		if ( null === $this->items ) {
			$this->items = edd_get_order_items( array(
				'order_id'      => $this->id,
				'orderby'       => 'cart_index',
				'order'         => 'ASC',
				'no_found_rows' => true,
			) );
		}

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
		if ( null === $this->adjustments ) {
			$this->adjustments = edd_get_order_adjustments( array(
				'object_id'     => $this->id,
				'object_type'   => 'order',
				'no_found_rows' => true,
				'order'         => 'ASC',
			) );
		}

		return $this->adjustments;
	}

	/**
	 * Retrieve the discounts applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return Order_Adjustment[] Order discounts.
	 */
	public function get_discounts() {
		$discounts = array();

		if ( empty( $this->adjustments ) ) {
			return $discounts;
		}

		foreach ( $this->adjustments as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'discount' === $adjustment->type ) {
				$discounts[] = $adjustment;
			}
		}

		return $discounts;
	}

	/**
	 * Retrieve the tax rates applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return array Order tax rates.
	 */
	public function get_taxes() {
		$taxes = array();

		if ( empty( $this->adjustments ) ) {
			return $taxes;
		}

		foreach ( $this->adjustments as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'tax_rate' === $adjustment->type ) {
				$taxes[] = $adjustment;
			}
		}

		return $taxes;
	}

	/**
	 * Retrieve the fees applied to the order. This retrieves the fees applied to the entire order and to individual items.
	 *
	 * @since 3.0
	 *
	 * @return Order_Adjustment[] Order fees.
	 */
	public function get_fees() {

		// Default values
		$fees = array();

		// Ensure adjustments exist.
		if ( null === $this->adjustments ) {
			$this->adjustments = edd_get_order_adjustments( array(
				'object_id'     => $this->id,
				'object_type'   => 'order',
				'no_found_rows' => true,
				'order'         => 'ASC',
			) );
		}

		// Ensure items exist.
		if ( null === $this->items ) {
			$this->items = edd_get_order_items( array(
				'order_id'      => $this->id,
				'orderby'       => 'cart_index',
				'order'         => 'ASC',
				'no_found_rows' => true,
			) );
		}

		// Bail if no adjustments.
		if ( empty( $this->adjustments ) ) {
			return $fees;
		}

		// Fetch the fees that applied to the entire order.
		foreach ( $this->adjustments as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'fee' === $adjustment->type ) {
				$fee_id = edd_get_order_adjustment_meta( $adjustment->id, 'fee_id', true );

				$fees[ $fee_id ] = $adjustment;
			}
		}

		// Fetch the fees that applied to specific items in the order.
		foreach ( $this->items as $item ) {
			/** @var Order_Item $item */

			foreach ( $item->get_fees() as $fee ) {
				/** @var Order_Adjustment $fee */

				$fee_id = edd_get_order_adjustment_meta( $fee->id, 'fee_id', true );

				$fees[ $fee_id ] = $fee;
			}
		}

		return $fees;
	}

	/**
	 * Retrieve the transaction ID associated with the order.
	 *
	 * @since 3.0
	 *
	 * @param string $type Transaction type. Default `primary`.
	 * @return string|array $retval Transaction ID(s).
	 */
	public function get_transaction_id( $type = 'primary' ) {
		$retval = '';

		// Retrieve first transaction ID only.
		if ( 'primary' === $type ) {
			$transactions = array_values( edd_get_order_transactions( array(
				'object_id'   => $this->id,
				'object_type' => 'order',
				'orderby'     => 'date_created',
				'order'       => 'ASC',
				'fields'      => 'transaction_id',
				'number'      => 1,
			) ) );

			if ( $transactions ) {
				$retval = esc_attr( $transactions[0] );
			}

		// Retrieve all transaction IDs.
		} else {
			$retval = edd_get_order_transactions( array(
				'object_id'   => $this->id,
				'object_type' => 'order',
				'orderby'     => 'date_created',
				'order'       => 'ASC',
				'fields'      => 'transaction_id',
			) );
		}

		return $retval;
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

		// Get rates from adjustments
		$rates = $this->get_taxes();

		// Get a single rate amount
		if ( ! empty( $rates ) ) {
			$rate = reset( $rates );
			$rate = $rate->amount;
		}

		return $rate;
	}

	/**
	 * Retrieve the address associated with the order.
	 *
	 * @since 3.0
	 *
	 * @return \EDD\Orders\Order_Address|false Object if successful, false otherwise.
	 */
	public function get_address() {

		// Attempt to get the order address.
		$address = edd_get_order_address_by( 'order_id', $this->id );

		// Fallback object if not found.
		if ( empty( $address ) ) {
			$address = (object) array(
				'id'          => 0,
				'order_id'    => 0,
				'first_name'  => '',
				'last_name'   => '',
				'address'     => '',
				'address2'    => '',
				'city'        => '',
				'region'      => '',
				'postal_code' => '',
				'country'     => '',
			);
		}

		// Return address (from DB or fallback).
		return $address;
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
		return edd_get_notes( array(
			'object_id'   => $this->id,
			'object_type' => 'order',
			'order'       => 'ASC',
		) );
	}

	/**
	 * Check if an order is complete.
	 *
	 * @since 3.0
	 *
	 * @return bool True if the order is complete, false otherwise.
	 */
	public function is_complete() {
		return ( 'complete' === $this->status );
	}
}
