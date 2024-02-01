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

use EDD\Refundable_Item;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order_Item Class.
 *
 * @since 3.0
 *
 * @property int                $id
 * @property int                $parent
 * @property int                $order_id
 * @property int                $product_id
 * @property string             $product_name
 * @property int|null           $price_id
 * @property int                $cart_index
 * @property string             $type
 * @property string             $status
 * @property int                $quantity
 * @property int                $amount
 * @property float              $subtotal
 * @property float              $tax
 * @property float              $discount
 * @property float              $total
 * @property float              $rate
 * @property string             $date_created
 * @property string             $date_modified
 * @property Order_Adjustment[] $adjustments
 */
class Order_Item extends \EDD\Database\Rows\Order_Item {

	use Refundable_Item;

	/**
	 * Order Item ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $id;

	/**
	 * Parent ID. This is only used for order items attached to refunds. The ID references the
	 * original order item that was refunded.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $parent;

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
	 * @var   int|null
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
		if ( is_admin() && ( function_exists( 'edd_doing_ajax' ) && ! edd_doing_ajax() ) ) {
			/**
			 * Allow the product name to be filtered within the admin.
			 * @since 3.0
			 * @param string $product_name  The order item name.
			 * @param EDD\Orders\Order_Item The order item object.
			 */
			return apply_filters( 'edd_order_details_item_name', $this->product_name, $this );
		}

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

		return edd_get_order_items( array(
			'parent' => $this->id
		) );
	}

	/**
	 * Checks the order item status to determine whether assets can be delivered.
	 *
	 * @since 3.0
	 * @return bool
	 */
	public function is_deliverable() {
		return in_array( $this->status, edd_get_deliverable_order_item_statuses(), true ) && $this->quantity > 0;
	}


	/**
	 * Retrieves the net total for this order item.
	 *
	 * @since 3.2.6
	 * @return float Item net total.
	 */
	public function get_net_total() {
		$net_total = $this->total -  floatval( $this->tax );

		$net_total = array_reduce(
			$this->get_refunded_items(),
			function( $total, $refund_item ) {
				return $total + $refund_item->total;
			},
			$net_total
		);

		/**
		 * Allow item net total to be filtered.
		 *
		 * @since 3.2.6
		 * @param float $net_total Item net total.
		 * @param EDD\Orders\Order_Item $this Order item object.
		 */
		return (float) apply_filters( 'edd_order_item_net_total', $net_total, $this );
	}
}
