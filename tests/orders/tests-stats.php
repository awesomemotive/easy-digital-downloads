<?php
namespace EDD;

/**
 * Stats Tests.
 *
 * @group edd_orders
 * @group edd_stats
 *
 * @coversDefaultClass \EDD\Stats
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

	/**
	 * @covers ::get_order_earnings
	 */
	public function test_get_order_earnings() {
		$earnings = self::$stats->get_order_earnings();

		$this->assertSame( 600.0, $earnings );
	}

	/**
	 * @covers ::get_order_earnings
	 */
	public function test_get_order_earnings_with_range_last_year_should_be_0() {
		$earnings = self::$stats->get_order_earnings(
			array(
				'range' => 'last_year',
			)
		);

		$this->assertSame( 0.00, $earnings );
	}

	/**
	 * @covers ::get_order_earnings
	 */
	public function test_get_order_earnings_with_range_last_30_days_should_be_600() {
		$earnings = self::$stats->get_order_earnings(
			array(
				'range' => 'last_30_days',
			)
		);

		$this->assertSame( 600.00, $earnings );
	}

	/**
	 * @covers ::get_order_earnings
	 */
	public function test_get_order_earnings_with_range_this_year_should_be_600() {
		$earnings = self::$stats->get_order_earnings(
			array(
				'range' => 'this_year',
			)
		);

		$this->assertSame( 600.00, $earnings );
	}

	/**
	 * @covers ::get_order_count
	 */
	public function test_get_order_count() {
		$count = self::$stats->get_order_count();

		$this->assertSame( 5, $count );
	}

	/**
	 * @covers ::get_order_count
	 */
	public function test_get_order_count_with_range_last_year_should_be_0() {
		$count = self::$stats->get_order_count(
			array(
				'range' => 'last_year',
			)
		);

		$this->assertSame( 0, $count );
	}

	/**
	 * @covers ::get_order_count
	 */
	public function test_get_order_count_with_range_last_30_days_should_be_5() {
		$count = self::$stats->get_order_count(
			array(
				'range' => 'last_30_days',
			)
		);

		$this->assertSame( 5, $count );
	}

	/**
	 * @covers ::get_order_count
	 */
	public function test_get_order_count_with_range_this_year_should_be_5() {
		$count = self::$stats->get_order_count(
			array(
				'range' => 'this_year',
			)
		);

		$this->assertSame( 5, $count );
	}
}
