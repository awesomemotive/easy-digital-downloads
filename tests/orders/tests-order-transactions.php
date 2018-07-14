<?php
namespace EDD\Orders;

/**
 * Order Transaction Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order_Item
 */
class Order_Transaction_Tests extends \EDD_UnitTestCase {

	/**
	 * Order transactions fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $order_transactions = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_transactions = parent::edd()->order_transaction->create_many( 5 );
	}
}