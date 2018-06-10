<?php
/**
 * Order Adjustment Object.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Orders;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order_Adjustment Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $object_id
 * @property string $object_type
 * @property int $type_id
 * @property string $type
 * @property string $description
 * @property float $amount
 * @property string $date_completed
 * @property string $date_modified
 */
class Order_Adjustment extends Base_Object {

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
	 * @var   int
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
	 * Amount.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $amount;

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
}