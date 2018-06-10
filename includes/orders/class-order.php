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
 *
 * @property int $id
 * @property int $parent
 * @property string $number
 * @property string $status
 * @property string $date_created
 * @property string $date_completed
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
 * @property array $items
 * @property array $adjustments
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
			'order_id'      => $this->id,
			'orderby'       => 'cart_index',
			'order'         => 'ASC',
			'no_found_rows' => true
		) );

		$this->adjustments = edd_get_order_adjustments( array(
			'object_id'     => $this->id,
			'object_type'   => 'order',
			'no_found_rows' => true
		) );
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
		return edd_get_option( 'enable_sequential' )
			? $this->number
			: $this->id;
	}

	/**
	 * Retrieve the discounted amount that was applied to the order.
	 *
	 * @since 3.0
	 *
	 * @return float Order discount.
	 */
	public function get_discount() {
		return $this->discount;
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
	 * @return array Order fees.
	 */
	public function get_fees() {

		// Default values
		$fees = array();

		// Bail if no adjustments
		if ( empty( $this->adjustments ) ) {
			return $fees;
		}

		// Fetch the fees that applied to the entire order.
		foreach ( $this->adjustments as $adjustment ) {
			/** @var Order_Adjustment $adjustment */

			if ( 'fee' === $adjustment->type ) {
				$fees[] = $adjustment;
			}
		}

		// Fetch the fees that applied to specific items in the order.
		foreach ( $this->items as $item ) {
			/** @var Order_Item $item */

			foreach ( $item->fees as $fee ) {
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
		$user_info = $this->get_user_info();
		$address   = ! empty( $user_info['address'] )
			? (array) $user_info['address']
			: array();

		return wp_parse_args( $address, array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'country' => '',
			'state'   => '',
			'zip'     => ''
		) );
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
}