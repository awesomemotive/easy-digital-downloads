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

use EDD\Database\Rows as Rows;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Transaction Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property int $object_id
 * @property string $object_type
 * @property string $transaction_id
 * @property string $gateway
 * @property string $status
 * @property float $total
 * @property string $date_created
 * @property string $date_modified
 */
class Order_Transaction extends Rows\Order_Transaction {

	/**
	 * Order Transaction ID.
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
	 * Object type
	 *
	 * @since 3.0
	 * @var   int
	 */
	protected $object_type;

	/**
	 * Transaction ID.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $transaction_id;

	/**
	 * Gateway.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $gateway;

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
