<?php
/**
 * Stats Tests.
 *
 * @group edd_orders
 * @group edd_stats
 *
 * @coversDefaultClass \EDD\Stats
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Stats;
class Stats_Tests extends EDD_UnitTestCase {

	/**
	 * Stats class fixture.
	 *
	 * @var Stats
	 */
	protected static $stats;

	/**
	 * Orders test fixture.
	 *
	 * @var int[]
	 */
	protected static $orders;

	/**
	 * Refunds test fixture.
	 *
	 * @var array
	 */
	protected static $refunds;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$stats  = new Stats();
		self::$orders = parent::edd()->order->create_many( 5 );

		// Refund two of those orders.
		for ( $i = 0; $i < 2; $i++ ) {
			self::$refunds[] = edd_refund_order( self::$orders[ $i ] );
		}

		// Add a variable download with price ID 0 to order 3.
		edd_add_order_item(
			array(
				'order_id'     => 3,
				'product_id'   => 2,
				'price_id'     => 0,
				'product_name' => 'Variable Test Download Product - Simple',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'discount'     => 0,
				'tax'          => 0,
				'total'        => 20,
				'quantity'     => 1,
			)
		);
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
		$earnings = self::$stats->get_order_earnings( array(
			'range' => 'last_year',
		) );

		$this->assertSame( 0.00, $earnings );
	}

	/**
	 * @covers ::get_order_earnings
	 */
	public function test_get_order_earnings_with_range_last_30_days_should_be_600() {
		$earnings = self::$stats->get_order_earnings( array(
			'range' => 'last_30_days',
		) );

		$this->assertSame( 600.00, $earnings );
	}

	/**
	 * @covers ::get_order_earnings
	 */
	public function test_get_order_earnings_with_range_this_year_should_be_600() {
		$earnings = self::$stats->get_order_earnings( array(
			'range' => 'this_year',
		) );

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
		$count = self::$stats->get_order_count( array(
			'range' => 'last_year',
		) );

		$this->assertSame( 0, $count );
	}

	/**
	 * @covers ::get_order_count
	 */
	public function test_get_order_count_with_range_last_30_days_should_be_5() {
		$count = self::$stats->get_order_count( array(
			'range' => 'last_30_days',
		) );

		$this->assertSame( 5, $count );
	}

	/**
	 * @covers ::get_order_count
	 */
	public function test_get_order_count_with_range_this_year_should_be_5() {
		$count = self::$stats->get_order_count( array(
			'range' => 'this_year',
		) );

		$this->assertSame( 5, $count );
	}

	public function test_get_order_refund_count_with_range_last_year_should_be_0() {
		$count = self::$stats->get_order_refund_count( array(
			'range' => 'last_year',
		) );

		$this->assertSame( 0, $count );
	}

	public function test_get_order_refund_count_with_range_this_year_should_be_2() {
		$count = self::$stats->get_order_refund_count( array(
			'range' => 'this_year',
		) );

		$this->assertSame( 2, $count );
	}

	public function test_get_order_item_refund_count_with_range_last_year_should_be_0() {
		$count = self::$stats->get_order_item_refund_count( array(
			'range' => 'last_year',
		) );

		$this->assertSame( 0, $count );
	}

	public function test_get_order_item_refund_count_with_range_this_year_should_be_2() {
		$count = self::$stats->get_order_item_refund_count( array(
			'range' => 'this_year',
		) );

		$this->assertSame( 2, $count );
	}

	/**
	 * @covers ::get_order_item_count
	 */
	public function test_get_order_item_count_no_price_id_should_be_3() {
		$count = self::$stats->get_order_item_count(
			array(
				'product_id' => 1,
			)
		);

		$this->assertSame( 3, $count );
	}

	/**
	 * @covers ::get_order_item_count
	 */
	public function test_get_order_item_count_null_price_id_should_be_3() {
		$count = self::$stats->get_order_item_count(
			array(
				'product_id' => 1,
				'price_id'   => null,
			)
		);

		$this->assertSame( 3, $count );
	}

	/**
	 * @covers ::get_order_item_count
	 */
	public function test_get_order_item_count_invalid_price_id_should_be_0() {
		$count = self::$stats->get_order_item_count(
			array(
				'product_id' => 1,
				'price_id'   => 2,
			)
		);

		$this->assertSame( 0, $count );
	}

	/**
	 * @covers ::get_order_item_count
	 */
	public function test_get_order_item_count_zero_price_id_should_be_1() {
		$count = self::$stats->get_order_item_count(
			array(
				'product_id' => 2,
				'price_id'   => 0,
			)
		);

		$this->assertSame( 1, $count );
	}

	public function test_get_order_item_refund_count_with_invalid_price_id_should_be_0() {
		$count = self::$stats->get_order_item_refund_count(
			array(
				'product_id' => 1,
				'price_id'   => 2,
			)
		);

		$this->assertSame( 0, $count );
	}

	/**
	 * @covers ::get_order_refund_amount
	 */
	public function test_get_order_refund_amount_with_range_last_year_should_be_0() {
		$earnings = self::$stats->get_order_refund_amount( array(
			'range' => 'last_year',
		) );

		$this->assertSame( 0.00, $earnings );
	}

	/**
	 * @covers ::get_order_refund_amount
	 */
	public function test_get_order_refund_amount_with_range_this_year_should_be_240() {
		$earnings = self::$stats->get_order_refund_amount( array(
			'range' => 'this_year',
		) );

		$this->assertSame( 240.00, $earnings );
	}

	/**
	 * @covers ::get_refund_rate
	 */
	public function test_get_get_refund_rate_with_range_last_year_should_be_0() {
		$refund_rate = self::$stats->get_refund_rate( array(
			'range'  => 'last_year',
			'output' => 'raw',
			'status' => edd_get_gross_order_statuses(),
		) );

		$this->assertSame( 0, $refund_rate );
	}

	/**
	 * @covers ::get_refund_rate
	 */
	public function test_get_get_refund_rate_with_range_this_year_should_be_40() {
		$refund_rate = self::$stats->get_refund_rate( array(
			'range'  => 'this_year',
			'output' => 'raw',
			'status' => edd_get_gross_order_statuses(),
		) );

		$this->assertSame( 40.0, $refund_rate );
	}

	/**
	 * @covers ::generate_relative_data
	 */
	public function test_generate_relative_data_should_be_without_change() {
		$relative_data = self::$stats->generate_relative_data( 100, 100 );

		$this->assertTrue( $relative_data['no_change'] );
	}

	/**
	 * @covers ::generate_relative_data
	 */
	public function test_generate_relative_data_should_not_be_comparable() {
		$relative_data = self::$stats->generate_relative_data( 100, 0 );

		$this->assertFalse( $relative_data['comparable'] );
	}

	/**
	 * @covers ::generate_relative_data
	 */
	public function test_generate_relative_data_should_be_positive_change() {
		$relative_data = self::$stats->generate_relative_data( 100, 50 );

		$this->assertTrue( $relative_data['positive_change'] );
	}

	/**
	 * @covers ::generate_relative_data
	 */
	public function test_generate_relative_data_should_be_positive_reversed_change() {
		$relative_data = self::$stats->generate_relative_data( 50, 100, true );

		$this->assertTrue( $relative_data['positive_change'] );
	}

	/**
	 * @covers ::generate_relative_data
	 */
	public function test_generate_relative_data_should_be_negative_change() {
		$relative_data = self::$stats->generate_relative_data( 50, 100 );

		$this->assertFalse( $relative_data['positive_change'] );
	}
}
