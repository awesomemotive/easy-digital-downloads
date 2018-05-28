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
	 * Retrieve type ID.
	 *
	 * @since 3.0
	 *
	 * @return int Type ID.
	 */
	public function get_type_id() {
		return $this->type_id;
	}

	/**
	 * Retrieve type.
	 *
	 * @since 3.0
	 *
	 * @return string Type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Retrieve description.
	 *
	 * @since 3.0
	 *
	 * @return string Description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Retrieve adjustment amount.
	 *
	 * @since 3.0
	 *
	 * @return float Adjustment amount.
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Retrieve date created.
	 *
	 * @since 3.0
	 *
	 * @return string Date created.
	 */
	public function get_date_created() {
		return $this->date_created;
	}

	/**
	 * Retrieve date modified.
	 *
	 * @since 3.0
	 *
	 * @return string Date modified.
	 */
	public function get_date_modified() {
		return $this->date_modified;
	}
}