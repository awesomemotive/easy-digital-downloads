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
 * @property int      $parent
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
	 * Parent ID. This is only used for order adjustments attached to refunds. The ID references the
	 * original adjustment that was refunded.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $parent;

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
	 * @since 3.0
	 *
	 * @return Order_Adjustment[]|false
	 */
	public function get_refunded_items() {
		if ( null !== $this->refunded_items ) {
			return $this->refunded_items;
		}

		// Only fees and credits are supported.
		if ( ! in_array( $this->type, array( 'fee', 'credit' ) ) ) {
			return false;
		}

		return edd_get_order_adjustments( array(
			'parent' => $this->id
		) );
	}

	/**
	 * Backwards compatibility for the `amount` property, which is now the `total`.
	 *
	 * @since 3.1.0.4
	 */
	public function get_amount() {
		return $this->total;
	}
}
