<?php
namespace EDD\Orders;

/**
 * Stats Tests.
 *
 * @group edd_orders
 * @group edd_stats
 *
 * @coversDefaultClass \EDD\Orders\Stats
 */
class Stats_Tests extends \EDD_UnitTestCase {

	/**
	 * Stats class fixture.
	 *
	 * @var Stats
	 */
	protected static $stats;

	/**
	 * Orders test fixture.
	 *
	 * @var array
	 */
	protected static $orders;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$stats  = new Stats();
		self::$orders = parent::edd()->order->create_many( 5 );
	}

}
