<?php
/**
 * Tax rates tests.
 *
 * @group taxes
 */
namespace EDD\Tests\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class TaxRates extends EDD_UnitTestCase {

	protected static $fallback_rate;
	protected static $country_rate;
	protected static $region_rate;

	public static function wpSetUpBeforeClass() {

		$fallback_rate       = edd_add_tax_rate(
			array(
				'scope'  => 'global',
				'amount' => floatval( 10 ),
			)
		);
		$country_rate        = edd_add_tax_rate(
			array(
				'name'   => 'US',
				'scope'  => 'country',
				'amount' => floatval( 15 ),
			)
		);
		$region_rate         = edd_add_tax_rate(
			array(
				'name'        => 'US',
				'scope'       => 'region',
				'amount'      => floatval( 9.25 ),
				'description' => 'TN',
			)
		);
		self::$fallback_rate = edd_get_tax_rate_by( $fallback_rate );
		self::$country_rate  = edd_get_tax_rate_by( $country_rate );
		self::$region_rate   = edd_get_tax_rate_by( $region_rate );
	}

	public static function wpTearDownAfterClass() {
		edd_delete_tax_rate( self::$fallback_rate->id );
		edd_delete_tax_rate( self::$country_rate->id );
		edd_delete_tax_rate( self::$region_rate->id );
	}

	public function test_get_tax_rate_counts() {
		$counts = edd_get_tax_rate_counts();

		$this->assertIsArray( $counts );
		$this->assertEquals( 3, $counts['total'] );
		$this->assertEquals( 3, $counts['active'] );
	}

	/**
	 * @covers ::edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_no_country_should_return_fallback_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => '',
				'region'  => '',
			)
		);

		$this->assertEquals( self::$fallback_rate->id, $tax_rate->id );
	}

	/**
	 * @covers ::edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_random_country_should_return_fallback_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => 'UK',
				'region'  => '',
			)
		);

		$this->assertEquals( self::$fallback_rate->id, $tax_rate->id );
	}

	/**
	 * @covers ::edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_this_country_should_return_country_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => 'US',
				'region'  => 'OH',
			)
		);

		$this->assertEquals( self::$country_rate->id, $tax_rate->id );
	}

	/**
	 * @covers ::edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_this_state_should_return_region_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => 'US',
				'region'  => 'TN',
			)
		);

		$this->assertEquals( self::$region_rate->id, $tax_rate->id );
	}

	/**
	 * @covers ::edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_country_rate_with_description_returns_country_rate() {
		edd_update_tax_rate(
			self::$country_rate->id,
			array(
				'state' => 'TN',
			)
		);
		$country_rate = edd_get_tax_rate_by( self::$country_rate->id );
		$tax_rate     = edd_get_tax_rate_by_location(
			array(
				'country' => 'US',
				'region'  => 'KS',
			)
		);

		$this->assertEquals( self::$country_rate->id, $tax_rate->id );
	}

	public function test_tax_rate_name_returns_country() {
		$this->assertEquals( 'US', self::$country_rate->name );
	}

	public function test_tax_rate_description_returns_state() {
		$this->assertEquals( 'TN', self::$region_rate->description );
	}

	public function test_deleting_tax_rate_is_true() {
		$this->assertEquals( 1, edd_delete_tax_rate( self::$fallback_rate->id ) );
	}
}
