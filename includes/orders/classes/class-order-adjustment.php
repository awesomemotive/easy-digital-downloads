<?php
/**
 * Order Adjustment Object.
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
 * Order_Adjustment Class.
 *
 * @since 3.0
 *
 * @property int      $id
 * @property int      $object_id
 * @property string   $object_type
 * @property int|null $type_id
 * @property string   $type
 * @property string   $description
 * @property float    $subtotal
 * @property float    $tax
 * @property float    $total
 * @property string   $date_completed
 * @property string   $date_modified
 */
class Order_Adjustment extends \EDD\Database\Rows\Order_Adjustment {

	use Refundable_Item;

	/**
	 * Order Discount ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $id;

	/**
	 * Object ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $object_id;

	/**
	 * Object type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $object_type;

	/**
	 * Type ID.
	 *
	 * @since 3.0
	 * @var   int|null
	 */
	protected $type_id;

	/**
	 * Type.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $type;

	/**
	 * Type key.
	 *
	 * This is historically the "fee ID", or the key from the original fee array.
	 *
	 * @since 3.0
	 * @var string|null
	 */
	protected $type_key;

	/**
	 * Description.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $description;

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
	 * Total.
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
	 * Magic __toString method.
	 *
	 * @since 3.0
	 */
	public function __toString() {
		return $this->description;
	}

	/**
	 * Retrieves order adjustment records that were refunded from this original adjustment.
	 *
	 * @todo This is awful. `parent_id` column would help massively.
	 *
	 * @since 3.0
	 *
	 * @return Order_Adjustment[]|false
	 */
	public function get_refunded_items() {
		if ( null !== $this->refunded_items ) {
			return $this->refunded_items;
		}

		// Only fees are supported at this time.
		if ( 'fee' !== $this->type ) {
			return false;
		}

		/*
		 * First we need to get the order ID this is linked to. That will allow us to find
		 * matching refund records, which we can then use to find associated adjustments.
		 */

		$order_id = false;

		if ( 'order' === $this->object_type ) {
			$order_id = $this->object_id;
		} else {
			$order_item = edd_get_order_item( $this->object_id );
			if ( ! empty( $order_item->order_id ) ) {
				$order_id = $order_item->order_id;
			}
		}

		if ( empty( $order_id ) ) {
			return false;
		}

		$refund_ids = edd_get_orders( array(
			'type'   => 'refund',
			'parent' => $this->object_id,
			'fields' => 'id'
		) );

		if ( empty( $refund_ids ) ) {
			return false;
		}

		$query_args = array(
			'object_type'   => $this->object_type,
			'type'          => $this->type,
			'type_key'      => $this->type_key
		);

		if ( 'order' === $this->object_type ) {
			$query_args['object_id__in'] = $refund_ids;
		} else {
			// First we need to get IDs of all the order items.
			$order_item_ids = edd_get_order_items( array(
				'order_id__in' => $refund_ids,
				'field'        => 'id'
			) );

			if ( empty( $order_item_ids ) ) {
				return false;
			}

			$query_args['object_id__in'] = $order_item_ids;
		}

		return edd_get_order_adjustments( $query_args );
	}
}
