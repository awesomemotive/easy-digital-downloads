<?php
namespace EDD\Orders;

/**
 * Order Item Tests.
 *
 * @group edd_orders
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order_Item
 */
class Order_Item_Tests extends \EDD_UnitTestCase {

	/**
	 * Order items fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $order_items = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_items = parent::edd()->order_item->create_many( 5 );
	}


}