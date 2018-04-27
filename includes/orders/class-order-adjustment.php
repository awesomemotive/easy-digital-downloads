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
	 * Retrieve order discount ID.
	 *
	 * @since 3.0
	 *
	 * @return int Order discount ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve object ID.
	 *
	 * @since 3.0
	 *
	 * @return int Object ID.
	 */
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 * Retrieve object type.
	 *
	 * @since 3.0
	 *
	 * @return string Object type.
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Retrieve discount ID.
	 *
	 * @since 3.0
	 *
	 * @return int Discount ID.
	 */
	public function get_discount_id() {
		return $this->discount_id;
	}

	/**
	 * Retrieve discount amount.
	 *
	 * @since 3.0
	 *
	 * @return float Discount amount.
	 */
	public function get_amount() {
		return $this->amount;
	}
}