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

use EDD\Database\Rows;
use EDD\Database\Rows\Adjustment;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Class.
 *
 * @since 3.0
 *
 * @property int                $id
 * @property int                $parent
 * @property string             $order_number
 * @property string             $type
 * @property string             $status
 * @property string             $date_created
 * @property string             $date_modified
 * @property string|null        $date_completed
 * @property string|null        $date_refundable
 * @property int                $user_id
 * @property int                $customer_id
 * @property string             $email
 * @property string             $ip
 * @property string             $gateway
 * @property string             $mode
 * @property string             $currency
 * @property string             $payment_key
 * @property int|null           $tax_rate_id
 * @property float              $subtotal
 * @property float              $tax
 * @property float              $discount
 * @property float              $total
 * @property float              $rate
 * @property Order_Item[]       $items
 * @property Order_Adjustment[] $adjustments
 * @property Order_Address      $address
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
	 * Date Actions Run
	 *
	 * @since 3.2.0
	 * @var string|null
	 */
	protected $date_actions_run;

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
	 * Tax rate ID.
	 *
	 * @since 3.0
	 * @var   int|null
	 */
	protected $tax_rate_id;

	/**
	 * Tax rate Adjustment object.
	 *
	 * @since 3.0
	 * @var   Adjustment|null
	 */
	protected $tax_rate = null;

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
			$this->adjustments = edd_get_order_adjustments(
				array(
					'object_id'     => $this->id,
					'object_type'   => 'order',
					'no_found_rows' => true,
					'order'         => 'ASC',
				)
			);
		} elseif ( 'items' === $key ) {
			$this->get_items();
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
		 * @param Order     Prior to EDD 3.0, this was an EDD_Payment object. Now it is an EDD_Order object.
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
		 * @param Order     The EDD_Order object.
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
			$this->items = edd_get_order_items(
				array(
					'order_id'      => $this->id,
					'orderby'       => 'cart_index',
					'order'         => 'ASC',
					'no_found_rows' => true,
					'number'        => 200,
				)
			);
		}

		return $this->items;
	}


	/**
	 * Retrieve all the items in the order and transform bundle products into regular items.
	 *
	 * @since 3.0.2
	 *
	 * @return Order_Item[] Order items.
	 */
	public function get_items_with_bundles() {
		$items = array();
		foreach ( $this->get_items() as $index => $item ) {
			if ( edd_is_bundled_product( $item->product_id ) ) {
				$new_items        = array();
				$bundled_products = edd_get_bundled_products( $item->product_id, $item->price_id );
				foreach ( $bundled_products as $bundle_item ) {
					$order_item_args = array(
						'order_id'     => $this->ID,
						'status'       => $item->status,
						'product_id'   => edd_get_bundle_item_id( $bundle_item ),
						'product_name' => edd_get_bundle_item_title( $bundle_item ),
						'price_id'     => edd_get_bundle_item_price_id( $bundle_item ),
						'quantity'     => $item->quantity,
					);
					$new_items[]     = new \EDD\Orders\Order_Item( $order_item_args );
				}
				if ( ! empty( $new_items ) ) {
					$items = array_merge( $items, $new_items );
				}
			} else {
				$items[] = $item;
			}
		}

		return $items;
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
			$this->adjustments = edd_get_order_adjustments(
				array(
					'object_id'     => $this->id,
					'object_type'   => 'order',
					'no_found_rows' => true,
					'order'         => 'ASC',
				)
			);
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

		foreach ( $this->get_adjustments() as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'discount' === $adjustment->type ) {
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
	 * @return Order_Adjustment[] Order fees.
	 */
	public function get_fees() {

		// Default values
		$fees = array();

		// Fetch the fees that applied to the entire order.
		foreach ( $this->get_adjustments() as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'fee' === $adjustment->type ) {
				$fees[] = $adjustment;
			}
		}

		// Ensure items exist.
		if ( null === $this->items ) {
			$this->items = $this->get_items();
		}

		// Fetch the fees that applied to specific items in the order.
		foreach ( $this->items as $item ) {
			/** @var Order_Item $item */

			foreach ( $item->get_fees() as $fee ) {
				/** @var Order_Adjustment $fee */

				$fees[] = $fee;
			}
		}

		return $fees;
	}

	/**
	 * Retrieve the credits applied to the order.
	 * These exist only for manually added orders.
	 *
	 * @since 3.0
	 *
	 * @return Order_Adjustment[] Order credits.
	 */
	public function get_credits() {
		// Default values
		$credits = array();

		// Fetch the fees that applied to the entire order.
		foreach ( $this->get_adjustments() as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'credit' === $adjustment->type ) {
				$credits[] = $adjustment;
			}
		}

		return $credits;
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
			$transactions = array_values(
				edd_get_order_transactions(
					array(
						'object_id'   => $this->id,
						'object_type' => 'order',
						'orderby'     => 'date_created',
						'order'       => 'ASC',
						'fields'      => 'transaction_id',
						'number'      => 1,
					)
				)
			);

			if ( $transactions ) {
				$retval = esc_attr( $transactions[0] );
			}

			// Retrieve all transaction IDs.
		} else {
			$retval = edd_get_order_transactions(
				array(
					'object_id'   => $this->id,
					'object_type' => 'order',
					'orderby'     => 'date_created',
					'order'       => 'ASC',
					'fields'      => 'transaction_id',
				)
			);
		}

		return $retval;
	}

	/**
	 * Retrieves the tax rate Adjustment object associated with the order.
	 *
	 * @since 3.0
	 * @return Adjustment|false|null
	 */
	public function get_tax_rate_object() {
		if ( $this->tax_rate_id && null === $this->tax_rate ) {
			$this->tax_rate = edd_get_adjustment( $this->tax_rate_id );
		}

		return $this->tax_rate;
	}

	/**
	 * Retrieve the tax rate associated with the order.
	 *
	 * @since 3.0
	 *
	 * @return float Tax rate percentage (0 - 100).
	 */
	public function get_tax_rate() {

		// Default rate
		$rate = 0;

		// Get rates from adjustments
		$tax_rate_object = $this->get_tax_rate_object();
		if ( is_object( $tax_rate_object ) && isset( $tax_rate_object->amount ) ) {
			$rate = $tax_rate_object->amount;
		}

		/*
		 * If we have a tax_amount, but no rate, check in order meta. This is where legacy rates are stored
		 * if they cannot be resolved to an actual adjustment object.
		 */
		if ( empty( $rate ) && abs( $this->tax ) > 0 ) {
			$rate = edd_get_order_meta( $this->id, 'tax_rate', true );
		}

		return floatval( $rate );
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
		return edd_get_notes(
			array(
				'object_id'   => $this->id,
				'object_type' => 'order',
				'order'       => 'ASC',
			)
		);
	}

	/**
	 * Check if an order is complete.
	 *
	 * @since 3.0
	 *
	 * @return bool True if the order is complete, false otherwise.
	 */
	public function is_complete() {
		return in_array( $this->status, edd_get_complete_order_statuses(), true );
	}

	/**
	 * Determines if this order is able to be resumed by the user.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_recoverable() {
		$recoverable_statuses = edd_recoverable_order_statuses();
		if ( in_array( $this->status, $recoverable_statuses, true ) && empty( $this->get_transaction_id() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the URL that a customer can use to resume an order, or false if it's not recoverable.
	 *
	 * @since 3.0
	 *
	 * @return bool|string
	 */
	public function get_recovery_url() {
		if ( ! $this->is_recoverable() ) {
			return false;
		}

		$recovery_url = add_query_arg(
			array(
				'edd_action' => 'recover_payment',
				'payment_id' => urlencode( $this->id ),
			),
			edd_get_checkout_uri()
		);

		/**
		 * Legacy recovery URL filter.
		 *
		 * @param \EDD_Payment $payment The EDD payment object.
		 */
		if ( has_filter( 'edd_payment_recovery_url' ) ) {
			$recovery_url = apply_filters( 'edd_payment_recovery_url', $recovery_url, edd_get_payment( $this->id ) );
		}

		/**
		 * The order recovery URL.
		 *
		 * @since 3.0
		 * @param string            $recovery_url The order recovery URL.
		 * @param \EDD\Orders\Order $this         The order object.
		 */
		return apply_filters( 'edd_order_recovery_url', $recovery_url, $this );
	}
}
