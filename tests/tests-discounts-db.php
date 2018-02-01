<?php

/**
 * Discount DB Tests
 *
 * @covers EDD_DB_Discounts
 * @group edd_discounts_db
 * @group database
 * @group edd_discounts
 *
 * @coversDefaultClass EDD_DB_Discounts
 */
class Tests_Discounts_DB extends EDD_UnitTestCase {

	/**
	 * Discounts test fixture.
	 *
	 * @var array
	 */
	protected static $discounts;

	public static function wpSetUpBeforeClass() {
		$discount_id       = EDD_Helper_Discount::create_simple_flat_discount();
		self::$discounts[] = new EDD_Discount( $discount_id );

		$discount_id = EDD_Helper_Discount::create_simple_percent_discount();
		self::$discounts[] = new EDD_Discount( $discount_id );
	}

	/**
	 * @covers ::get_columns()
	 */
	public function test_get_columns() {
		$expected = array(
			'id'                => '%d',
			'name'              => '%s',
			'code'              => '%s',
			'status'            => '%s',
			'type'              => '%s',
			'amount'            => '%s',
			'description'       => '%s',
			'date_created'      => '%s',
			'start_date'        => '%s',
			'end_date'          => '%s',
			'max_uses'          => '%d',
			'use_count'         => '%d',
			'min_cart_price'    => '%f',
			'once_per_customer' => '%d',
			'product_condition' => '%s',
			'scope'             => '%s',
		);

		$this->assertEqualSets( $expected, EDD()->discounts->get_columns() );
	}

	/**
	 * @covers ::get_column_defaults()
	 */
	public function test_get_column_defaults() {
		$expected = array(
			'id'                => 0,
			'name'              => '',
			'code'              => '',
			'status'            => '',
			'type'              => '',
			'amount'            => '',
			'description'       => '',
			'max_uses'          => 0,
			'use_count'         => 0,
			'min_cart_price'    => 0.00,
			'once_per_customer' => 0,
			'product_condition' => '',
			'scope'             => 'global',
			'date_created'      => date( 'Y-m-d H:i:s' ),
			'start_date'        => '0000-00-00 00:00:00',
			'end_date'          => '0000-00-00 00:00:00',
		);

		$this->assertEqualSets( $expected, EDD()->discounts->get_column_defaults() );
	}

	/**
	 * @covers ::insert()
	 */
	public function test_insert() {
		$id = EDD()->discounts->insert( array(
			'code'   => 'TEST',
			'status' => 'active',
		) );

		$this->assertTrue( $id > 0 );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update() {
		$success = EDD()->discounts->update( self::$discounts[0]->code, array(
			'code' => 'NEWCODE',
		) );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update_without_id_should_fail() {
		$success = EDD()->discounts->update( null, array(
			'code' => 'NEWCODE',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_should_return_true() {
		$success = EDD()->discounts->delete( self::$discounts[0]->code );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_without_id_should_fail() {
		$success = EDD()->discounts->delete( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts() {
		$discounts = EDD()->discounts->get_discounts();

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_returns_expected_discount() {
		$discounts = EDD()->discounts->get_discounts();

		$this->assertEquals( '20OFF', $discounts[0]->code );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_number_should_return_true() {
		$discounts = EDD()->discounts->get_discounts( array(
			'number' => 10,
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_offset_should_return_true() {
		$discounts = EDD()->discounts->get_discounts( array(
			'offset' => 1,
		) );

		$this->assertCount( 1, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_offset_order_asc() {
		$discounts = EDD()->discounts->get_discounts( array(
			'offset' => 1,
			'order' => 'asc',
		) );

		$this->assertCount( 1, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_search_by_code() {
		$discounts = EDD()->discounts->get_discounts( array(
			'search' => '10FLAT',
		) );

		$this->assertEquals( '10FLAT', $discounts[0]->code );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_search_by_name() {
		$discounts = EDD()->discounts->get_discounts( array(
			'search' => '$10 Off',
		) );

		$this->assertTrue( '10FLAT' === $discounts[0]->code );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_order_asc() {
		$discounts = EDD()->discounts->get_discounts( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $discounts[0]->id < $discounts[1]->id );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_order_desc() {
		$discounts = EDD()->discounts->get_discounts( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $discounts[0]->id > $discounts[1]->id );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_orderby() {
		$discounts = EDD()->discounts->get_discounts( array(
			'orderby' => 'code',
			'order'   => 'asc',
		) );

		$this->assertTrue( strcmp( $discounts[0]->code, $discounts[1]->code ) < 0 );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_type() {
		$discounts = EDD()->discounts->get_discounts( array(
			'type' => 'percent',
		) );

		$this->assertCount( 1, $discounts );

		$discounts = EDD()->discounts->get_discounts( array(
			'type' => 'flat',
		) );

		$this->assertCount( 1, $discounts );

		$discounts = EDD()->discounts->get_discounts( array(
			'type' => array(
				'percent',
				'flat',
			),
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_status() {
		$discounts = EDD()->discounts->get_discounts( array(
			'status' => 'active',
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_date_created() {
		$discounts = EDD()->discounts->get_discounts( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_created_start_date() {
		$discounts = EDD()->discounts->get_discounts( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_created_end_date() {
		$discounts = EDD()->discounts->get_discounts( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_end_date() {
		$discounts = EDD()->discounts->get_discounts( array(
			'end_date' => '2050-12-31 23:59:59',
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_start_date() {
		$discounts = EDD()->discounts->get_discounts( array(
			'start_date' => '2010-12-12 23:59:59',
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 2, EDD()->discounts->count() );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_search() {
		$discounts = EDD()->discounts->count( array(
			'search' => '20OFF',
		) );

		$this->assertEquals( 1, $discounts );

		$discounts = EDD()->discounts->count( array(
			'search' => 'FREE',
		) );

		$this->assertEquals( 0, $discounts );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_active_status() {
		$this->assertEquals( 2, EDD()->discounts->count( array(
			'status' => 'active',
		) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_expired_status() {
		$discount_id = EDD_Helper_Discount::created_expired_flat_discount();

		$this->assertSame( 1, EDD()->discounts->count( array(
			'status' => 'expired',
		) ) );

		EDD()->discounts->delete( $discount_id );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_type() {
		$this->assertSame( 1, EDD()->discounts->count( array(
			'type' => 'percent',
		) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_date_created() {
		$this->assertSame( 2, EDD()->discounts->count( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_start_date() {
		$this->assertSame( 2, EDD()->discounts->count( array(
			'start_date' => '2010-12-12 23:59:59',
		) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_end_date() {
		$this->assertSame( 2, EDD()->discounts->count( array(
			'end_date' => '2050-12-31 23:59:59',
		) ) );
	}

	/**
	 * @covers ::counts_by_status()
	 */
	public function test_counts_by_status_active_discounts() {
		$counts = EDD()->discounts->counts_by_status();

		$this->assertSame( 2, $counts->active );
	}

	/**
	 * @covers ::counts_by_status()
	 */
	public function test_counts_by_status_inactive_discounts() {
		$counts = EDD()->discounts->counts_by_status();

		$this->assertSame( 0, $counts->inactive );
	}

	/**
	 * @covers ::counts_by_status()
	 */
	public function test_counts_by_status_expired_discounts() {
		$counts = EDD()->discounts->counts_by_status();

		$this->assertSame( 0, $counts->expired );
	}

}