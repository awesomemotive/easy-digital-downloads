<?php

namespace EDD\Tests\Customers;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Customers Tests.
 *
 * @group edd_customers
 */
class Tests_Customer_Stats extends EDD_UnitTestCase {

	/**
	 * Stats class fixture.
	 *
	 * @var Stats
	 */
	protected static $stats;

	public static function wpSetUpBeforeClass() {
		$cusomters = parent::edd()->customer->create_many(
			5,
			array(
				'purchase_count' => wp_rand( 1, 100 ),
			)
		);
		self::$stats = new \EDD\Stats();
	}

	public function test_get_customer_count_ignore_purchase_value_returns_five() {
		$this->assertEquals( 5, self::$stats->get_customer_count() );
	}

	public function test_get_customer_count_include_purchase_value_returns_same_amount() {
		$original_customer_count = self::$stats->get_customer_count();
		$ignored_customer        = edd_add_customer(
			array(
				'email' => 'totallynewcustomer@edd.local',
				'name'  => 'Totally New Customer',
			)
		);
		$this->assertEquals( $original_customer_count, self::$stats->get_customer_count( array( 'purchase_count' => true ) ) );
	}
}
