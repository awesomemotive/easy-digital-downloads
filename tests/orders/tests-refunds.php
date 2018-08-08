<?php
namespace EDD\Orders;

/**
 * Refund Tests.
 *
 * @group edd_orders
 * @group edd_refunds
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order
 */
class Refunds_Tests extends \EDD_UnitTestCase {

	/**
	 * Orders fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $orders = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$orders = parent::edd()->order->create_many( 5 );
	}
}