<?php
/**
 * Tax rates tests.
 *
 * @group taxes
 */

class Tests_Tax_Rates extends EDD_UnitTestCase {

	protected $fallback_rate;
	protected $country_rate;
	protected $region_rate;

	public function setUp() {
		parent::setUp();

		$fallback_rate       = edd_add_adjustment(
			array(
				'name'        => '',
				'type'        => 'tax_rate',
				'scope'       => 'global',
				'amount_type' => 'percent',
				'amount'      => floatval( 10 ),
				'description' => '',
				'status'      => 'active',
			)
		);
		$country_rate        = edd_add_adjustment(
			array(
				'name'        => 'US',
				'type'        => 'tax_rate',
				'scope'       => 'country',
				'amount_type' => 'percent',
				'amount'      => floatval( 15 ),
				'description' => '',
				'status'      => 'active',
			)
		);
		$region_rate         = edd_add_adjustment(
			array(
				'name'        => 'US',
				'type'        => 'tax_rate',
				'scope'       => 'region',
				'amount_type' => 'percent',
				'amount'      => floatval( 9.25 ),
				'description' => 'TN',
				'status'      => 'active',
			)
		);
		$this->fallback_rate = edd_get_adjustment( $fallback_rate );
		$this->country_rate  = edd_get_adjustment( $country_rate );
		$this->region_rate   = edd_get_adjustment( $region_rate );
	}

	public function tearDown() {
		parent::tearDown();

		edd_delete_adjustment( $this->fallback_rate->id );
		edd_delete_adjustment( $this->country_rate->id );
		edd_delete_adjustment( $this->region_rate->id );
	}

	/**
	 * @covers edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_no_country_should_return_fallback_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => '',
				'region'  => '',
			)
		);

		$this->assertEquals( $this->fallback_rate->id, $tax_rate->id );
	}

	/**
	 * @covers edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_random_country_should_return_fallback_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => 'UK',
				'region'  => '',
			)
		);

		$this->assertEquals( $this->fallback_rate->id, $tax_rate->id );
	}

	/**
	 * @covers edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_this_country_should_return_country_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => 'US',
				'region'  => 'OH',
			)
		);

		$this->assertEquals( $this->country_rate->id, $tax_rate->id );
	}

	/**
	 * @covers edd_get_tax_rate_by_location
	 */
	public function test_edd_get_tax_rate_by_location_this_state_should_return_region_rate() {
		$tax_rate = edd_get_tax_rate_by_location(
			array(
				'country' => 'US',
				'region'  => 'TN',
			)
		);

		$this->assertEquals( $this->region_rate->id, $tax_rate->id );
	}

	public function test_edd_get_tax_rate_by_location_country_rate_with_description_returns_country_rate() {
		edd_update_adjustment(
			$this->country_rate->id,
			array(
				'description' => 'TN',
			)
		);
		$tax_rate     = edd_get_tax_rate_by_location(
			array(
				'country' => 'US',
				'region'  => 'KS',
			)
		);
		$country_rate = edd_get_adjustment( $this->country_rate->id );

		$this->assertEquals( $this->country_rate->id, $tax_rate->id );
	}
}
