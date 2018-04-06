<?php
/**
 * Order Discount Object.
 *
 * @package     EDD
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Orders;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order_Discount Class.
 *
 * @since 3.0.0
 */
class Order_Discount {

	/**
	 * Order Discount ID.
	 *
	 * @since 3.0.0
	 * @var   int
	 */
	protected $id;

	/**
	 * Object ID.
	 *
	 * @since 3.0.0
	 * @var   int
	 */
	protected $object_id;

	/**
	 * Object type.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $object_type;

	/**
	 * Discount ID.
	 *
	 * @since 3.0.0
	 * @var   int
	 */
	protected $discount_id;

	/**
	 * Discount amount.
	 *
	 * @since 3.0.0
	 * @var   float
	 */
	protected $amount;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @param object $order_discount Order discount data directly from the database.
	 */
	public function __construct( $order_discount ) {
		if ( is_object( $order_discount ) ) {
			foreach ( get_object_vars( $order_discount ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Retrieve order discount ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Order discount ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve object ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Object ID.
	 */
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 * Retrieve object type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Object type.
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Retrieve discount ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Discount ID.
	 */
	public function get_discount_id() {
		return $this->discount_id;
	}

	/**
	 * Retrieve discount amount.
	 *
	 * @since 3.0.0
	 *
	 * @return float Discount amount.
	 */
	public function get_amount() {
		return $this->amount;
	}
}