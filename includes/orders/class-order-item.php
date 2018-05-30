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

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order_Item Class.
 *
 * @since 3.0
 */
class Order_Item extends Base_Object {

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
	 * Item discount.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $discount;

	/**
	 * Item tax.
	 *
	 * @since 3.0
	 * @var   float
	 */
	protected $tax;

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
	 * Retrieve order item ID.
	 *
	 * @since 3.0
	 *
	 * @return int Order item ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve the product ID.
	 *
	 * @since 3.0
	 *
	 * @return int Product ID.
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Retrieve the product name.
	 *
	 * @since 3.0
	 *
	 * @return string Product name.
	 */
	public function get_product_name() {
		return $this->product_name;
	}

	/**
	 * Retrieve price ID.
	 *
	 * @since 3.0
	 *
	 * @return int Price ID.
	 */
	public function get_price_id() {
		return $this->price_id;
	}

	/**
	 * Retrieve cart index.
	 *
	 * @since 3.0
	 *
	 * @return int Cart index.
	 */
	public function get_cart_index() {
		return $this->cart_index;
	}

	/**
	 * Retrieve item type.
	 *
	 * @since 3.0
	 *
	 * @return string Item type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Retrieve item status.
	 *
	 * @since 3.0
	 *
	 * @return string Item status.
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Retrieve item quantity.
	 *
	 * @since 3.0
	 *
	 * @return int Item quantity.
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * Retrieve item amount.
	 *
	 * @since 3.0
	 *
	 * @return float Item amount.
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Retrieve item subtotal.
	 *
	 * @since 3.0
	 *
	 * @return float Item subtotal.
	 */
	public function get_subtotal() {
		return $this->subtotal;
	}

	/**
	 * Retrieve item discount.
	 *
	 * @since 3.0
	 *
	 * @return float Item discount.
	 */
	public function get_discount() {
		return $this->discount;
	}

	/**
	 * Retrieve item tax.
	 *
	 * @since 3.0
	 *
	 * @return float Item tax.
	 */
	public function get_tax() {
		return $this->tax;
	}

	/**
	 * Retrieve item total.
	 *
	 * @since 3.0
	 *
	 * @return float Item total.
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Retrieve date created.
	 *
	 * @since 3.0
	 *
	 * @return string Date created.
	 */
	public function get_date_created() {
		$this->date_created;
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