<?php
namespace EDD\Tax_Rates;

/**
 * Tax Rate Tests.
 *
 * @group edd_tax_rates
 */
class Tests_Tax_Rates extends \EDD_UnitTestCase {

	/**
	 * Tax rates fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $tax_rates = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$tax_rates[] = parent::edd()->tax_rate->create( array(
			'country' => 'GB',
			'region'  => 'GB-LND',
			'rate'    => .13,
		) );

		self::$tax_rates[] = parent::edd()->tax_rate->create( array(
			'country' => 'US',
			'region'  => 'AL',
			'rate'    => 15,
		) );

		self::$tax_rates[] = parent::edd()->tax_rate->create( array(
			'country' => 'CA',
			'region'  => 'BC',
			'rate'    => .15,
		) );

		self::$tax_rates[] = parent::edd()->tax_rate->create( array(
			'country' => 'HK',
			'region'  => 'KOWLOON',
			'rate'    => .09,
		) );

		self::$tax_rates[] = parent::edd()->tax_rate->create( array(
			'country' => 'IN',
			'region'  => 'GJ',
			'rate'    => .63,
		) );
	}

	/**
	 * @covers ::edd_update_tax_rate
	 */
	public function test_update_should_return_true() {
		$success = edd_update_tax_rate( self::$tax_rates[0], array(
			'country' => 'GB',
		) );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::edd_update_tax_rate
	 */
	public function test_tax_rate_object_after_update_should_return_true() {
		edd_update_tax_rate( self::$tax_rates[0], array(
			'country' => 'GB',
		) );

		$tax_rate = edd_get_tax_rate_by( 'id', self::$tax_rates[0] );

		$this->assertSame( 'GB', $tax_rate->country );
	}

	/**
	 * @covers ::edd_update_tax_rate
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_tax_rate( null, array(
			'country' => 'GB',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_tax_rate
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_tax_rate( self::$tax_rates[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_tax_rate
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_tax_rate( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_number_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_offset_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'number' => 10,
			'offset' => 4,
		), OBJECT );

		$this->assertCount( 1, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_id_and_order_asc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'id',
			'order'   => 'asc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->id < $tax_rates[1]->id );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_id_and_order_desc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'id',
			'order'   => 'desc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->id > $tax_rates[1]->id );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_status__in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'status__in' => array(
				'active',
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_status__not_in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'status__not_in' => array(
				999,
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_country_and_order_asc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'country',
			'order'   => 'asc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->country < $tax_rates[1]->country );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_country_and_order_desc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'country',
			'order'   => 'desc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->country > $tax_rates[1]->country );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_country__in_should_return_1() {
		$tax_rates = edd_get_tax_rates( array(
			'country__in' => array(
				'IN',
			),
		), OBJECT );

		$this->assertCount( 1, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_country__not_in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'country__not_in' => array(
				999,
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_region_and_order_asc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'region',
			'order'   => 'asc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->region < $tax_rates[1]->region );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_region_and_order_desc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'region',
			'order'   => 'desc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->region > $tax_rates[1]->region );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_region__in_should_return_1() {
		$tax_rates = edd_get_tax_rates( array(
			'region__in' => array(
				'GJ',
			),
		), OBJECT );

		$this->assertCount( 1, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_region__not_in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'region__not_in' => array(
				999,
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_scope__in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'scope__in' => array(
				'region',
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_scope__not_in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'scope__not_in' => array(
				999,
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_rate_and_order_asc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'rate',
			'order'   => 'asc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->rate < $tax_rates[1]->rate );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_orderby_rate_and_order_desc_should_return_true() {
		$tax_rates = edd_get_tax_rates( array(
			'orderby' => 'rate',
			'order'   => 'desc',
		), OBJECT );

		$this->assertTrue( $tax_rates[0]->rate > $tax_rates[1]->rate );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_rate__in_should_return_1() {
		$tax_rates = edd_get_tax_rates( array(
			'rate__in' => array(
				15,
			),
		), OBJECT );

		$this->assertCount( 1, $tax_rates );
	}

	/**
	 * @covers ::edd_get_tax_rates
	 */
	public function test_get_tax_rates_with_rate__not_in_should_return_5() {
		$tax_rates = edd_get_tax_rates( array(
			'rate__not_in' => array(
				999,
			),
		), OBJECT );

		$this->assertCount( 5, $tax_rates );
	}
}