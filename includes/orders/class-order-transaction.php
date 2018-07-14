<?php
/**
 * Order Transaction Object.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Orders;

use EDD\Database\Objects as Objects;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Transaction Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $order_id
 * @property int $order_item_id
 * @property string $gateway
 * @property string $transaction_id
 * @property string $status
 * @property float $total
 * @property string $date_created
 * @property string $date_modified
 * @property \EDD\Orders\Order $order
 * @property \EDD\Orders\Order_Item $order_item
 */
class Order_Transaction extends Objects\Order_Transaction {

	/**
	 * Order Transaction ID.
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
	 * Order Item ID.
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $order_item_id;

	/**
	 * Gateway.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $gateway;

	/**
	 * Transaction ID.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $transaction_id;

	/**
	 * Status.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $status;

	/**
	 * Total amount.
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
	 * Order.
	 *
	 * @since 3.0
	 * @var   \EDD\Orders\Order
	 */
	protected $order;

	/**
	 * Order Item.
	 *
	 * @since 3.0
	 * @var   \EDD\Orders\Order_Item
	 */
	protected $order_item;

	/**
	 * Magic getter for immutability.
	 *
	 * @since 3.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		if ( 'order' === $key && null === $this->order ) {
			$this->order = edd_get_order( $this->order_id );
		} elseif ( 'order_item' === $key && null === $this->order_item ) {
			$this->order_item = edd_get_order_item( $this->order_item_id );
		}

		return parent::__get( $key );
	}

	/**
	 * Check if a transaction is complete.
	 *
	 * @since 3.0
	 *
	 * @return bool True if the transaction is complete, false otherwise.
	 */
	public function is_complete() {
		return ( 'complete' === $this->status );
	}
}
