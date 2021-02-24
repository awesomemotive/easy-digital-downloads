<?php
/**
 * Order Item Object.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Orders;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order_Item Class.
 *
 * @since 3.0
 *
 * @property int    $id
 * @property int    $order_id
 * @property int    $product_id
 * @property string $product_name
 * @property int    $price_id
 * @property int    $cart_index
 * @property string $type
 * @property string $status
 * @property int    $quantity
 * @property int    $amount
 * @property float  $subtotal
 * @property float  $tax
 * @property float  $discount
 * @property float  $total
 * @property string $date_created
 * @property string $date_modified
 * @property Order_Adjustment[] $adjustments
 */
class Order_Item extends \EDD\Database\Rows\Order_Item {

	/**
	 * Order Item ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $id;

	/**
	 * Order ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $order_id;

	/**
	 * Product ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $product_id;

	/**
	 * Product Name.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $product_name;

	/**
	 * Price ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $price_id;

	/**
	 * Cart index.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $cart_index;

	/**
	 * Item type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $type;

	/**
	 * Item status.
	 */
	protected $status;

	/**
	 * Item quantity.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $quantity;

	/**
	 * Item amount.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $amount;

	/**
	 * Item subtotal.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $subtotal;

	/**
	 * Item tax.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $tax;

	/**
	 * Item discount.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $discount;

	/**
	 * Item total.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $total;

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
	 * Order item adjustments.
	 *
	 * @since 3.0
	 * @var   \EDD\Orders\Order_Adjustment[]
	 */
	protected $adjustments = null;

	/**
	 * Refunded order items for this item.
	 *
	 * When this item has been refunded, matching order item records are created and associated with the
	 * refund record. These are those order items.
	 *
	 * @since 3.0
	 * @var null|Order_Item[]|false
	 */
	protected $refunded_items = null;

	/**
	 * Magic getter for immutability.
	 *
	 * @since 3.0
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		if ( 'adjustments' === $key && null === $this->adjustments ) {
			$this->adjustments = edd_get_order_adjustments( array(
				'object_id'     => $this->id,
				'object_type'   => 'order_item',
				'no_found_rows' => true,
				'order'         => 'ASC',
			) );
		}

		return parent::__get( $key );
	}

	/**
	 * Retrieve fees applied to this order item.
	 *
	 * @since 3.0
	 *
	 * @return array $fees Fees applied to this item.
	 */
	public function get_fees() {
		return edd_get_order_adjustments( array(
			'object_id'   => $this->id,
			'object_type' => 'order_item',
			'type'        => 'fee',
			'order'       => 'ASC',
		) );
	}

	/**
	 * Get an order item name, including any price ID name appended to the end.
	 *
	 * @since 3.0
	 *
	 * @return string The product name including any price ID name.
	 */
	public function get_order_item_name() {
		return $this->product_name;
	}

	/**
	 * Retrieves order item records that were refunded from this original order item.
	 *
	 * @since 3.0
	 *
	 * @return Order_Item[]|false
	 */
	public function get_refunded_items() {
		if ( null !== $this->refunded_items ) {
			return $this->refunded_items;
		}

		$refund_ids = edd_get_orders( array(
			'type'   => 'refund',
			'parent' => $this->order_id,
			'fields' => 'id'
		) );

		if ( empty( $refund_ids ) ) {
			return false;
		}

		return edd_get_order_items( array(
			'order_id__in' => $refund_ids,
			'product_id'   => $this->product_id,
			'price_id'     => $this->price_id
		) );
	}

	/**
	 * The maximum amounts that can be refunded. This starts with the original order item amounts, subtracts
	 * discounts, and subtracts what's already been refunded.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_refundable_amounts() {
		$maximums = array(
			'subtotal' => $this->subtotal - $this->discount,
			'tax'      => $this->tax,
			'total'    => $this->total
		);

		$refunded_items = $this->get_refunded_items();

		if ( ! empty( $refunded_items ) ) {
			foreach ( $refunded_items as $refunded_item ) {
				// We're adding numbers here, because `$refund_item` has negative amounts already.
				$maximums['subtotal'] += $refunded_item->subtotal;
				$maximums['tax']      += $refunded_item->tax;
				$maximums['total']    += $refunded_item->total;
			}
		}

		$maximums['subtotal'] = number_format( $maximums['subtotal'], edd_currency_decimal_filter(), '.', '' );
		$maximums['tax']      = number_format( $maximums['tax'], edd_currency_decimal_filter(), '.', '' );
		$maximums['total']    = number_format( $maximums['total'], edd_currency_decimal_filter(), '.', '' );

		return $maximums;
	}
}
