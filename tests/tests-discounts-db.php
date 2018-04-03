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
		$args = array(
			array(
				'name'              => '$10 Off',
				'code'              => '10FLAT',
				'status'            => 'active',
				'type'              => 'flat',
				'amount'            => '10',
				'max_uses'          => 10,
				'use_count'         => 54,
				'min_cart_price'    => 128,
				'product_condition' => 'all',
				'start_date'        => '2010-12-12 00:00:00',
				'end_date'          => '2050-12-31 23:59:59'
			),
			array(
				'name'              => '20 Percent Off',
				'code'              => '20OFF',
				'status'            => 'active',
				'type'              => 'percent',
				'amount'            => '20',
				'max_uses'          => 10,
				'use_count'         => 54,
				'min_cart_price'    => 128,
				'product_condition' => 'all',
				'start_date'        => '2010-12-12 00:00:00',
				'end_date'          => '2050-12-31 23:59:59'
			)
		);

		foreach ( $args as $discount ) {
			edd_add_discount( $discount );
			self::$discounts[] = edd_get_discount_by_code( $discount['code'] );
		}
	}

	/**
	 * @covers ::insert()
	 */
	public function test_insert() {
		$id = edd_add_discount( array(
			'code'   => 'TEST',
			'status' => 'active',
		) );

		$this->assertTrue( $id > 0 );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update() {
		$success = (bool) edd_update_discount( self::$discounts[0]->id, array(
			'code' => 'NEWCODE',
		) );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::update()
	 */
	public function test_update_without_id_should_fail() {
		$success = (bool) edd_update_discount( null, array(
			'code' => 'NEWCODE',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_should_return_true() {
		$success = (bool) edd_delete_discount( self::$discounts[0]->id );

		$this->assertTrue( $success );
	}

	/**
	 * @covers ::delete()
	 */
	public function test_delete_without_id_should_fail() {
		$success = (bool) edd_delete_discount();

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts() {
		$discounts = edd_get_discounts();

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_returns_expected_discount() {
		$discounts = edd_get_discounts();

		$this->assertEquals( '20OFF', $discounts[0]->code );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_number_should_return_true() {
		$discounts = edd_get_discounts( array(
			'number' => 10,
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_offset_should_return_true() {
		$discounts = edd_get_discounts( array(
			'offset' => 1,
		) );

		$this->assertCount( 1, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_offset_order_asc() {
		$discounts = edd_get_discounts( array(
			'offset' => 1,
			'order' => 'asc',
		) );

		$this->assertCount( 1, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_search_by_code() {
		$discounts = edd_get_discounts( array(
			'search' => '10FLAT',
		) );

		$this->assertEquals( '10FLAT', $discounts[0]->code );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_search_by_name() {
		$discounts = edd_get_discounts( array(
			'search' => '$10 Off',
		) );

		$this->assertTrue( '10FLAT' === $discounts[0]->code );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_order_asc() {
		$discounts = edd_get_discounts( array(
			'order' => 'asc',
		) );

		$this->assertTrue( $discounts[0]->id < $discounts[1]->id );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_order_desc() {
		$discounts = edd_get_discounts( array(
			'order' => 'desc',
		) );

		$this->assertTrue( $discounts[0]->id > $discounts[1]->id );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_orderby() {
		$discounts = edd_get_discounts( array(
			'orderby' => 'code',
			'order'   => 'asc',
		) );

		$this->assertTrue( strcmp( $discounts[0]->code, $discounts[1]->code ) < 0 );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_type() {
		$discounts = edd_get_discounts( array(
			'type' => 'percent',
		) );

		$this->assertCount( 1, $discounts );

		$discounts = edd_get_discounts( array(
			'type' => 'flat',
		) );

		$this->assertCount( 1, $discounts );

		$discounts = edd_get_discounts( array(
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
		$discounts = edd_get_discounts( array(
			'status' => 'active',
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_date_created() {
		$discounts = edd_get_discounts( array(
			'date_created_query' => array(
				'year'  => date( 'Y', strtotime( 'now' ) ),
				'month' => date( 'm', strtotime( 'now' ) ),
				'day'   => date( 'd', strtotime( 'now' ) ),
			)
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_created_start_date() {
		$discounts = edd_get_discounts( array(
			'date_created_query' => array(
				'year'  => date( 'Y', strtotime( 'now' ) ),
				'month' => date( 'm', strtotime( 'now' ) ),
				'day'   => date( 'd', strtotime( 'now' ) ),
			)
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_created_end_date() {
		$discounts = edd_get_discounts( array(
			'date_created_query' => array(
				'year'  => date( 'Y', strtotime( 'now' ) ),
				'month' => date( 'm', strtotime( 'now' ) ),
				'day'   => date( 'd', strtotime( 'now' ) ),
			)
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_end_date() {
		$discounts = edd_get_discounts( array(
			'end_date' => '2050-12-31 23:59:59',
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::get_discounts()
	 */
	public function test_get_discounts_with_start_date() {
		$discounts = edd_get_discounts( array(
			'start_date' => '2010-12-12 00:00:00',
		) );

		$this->assertCount( 2, $discounts );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count() {
		$this->assertEquals( 2, edd_get_discount_count() );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_search() {
		$discounts = count( edd_get_discounts( array(
			'search' => '20OFF',
		) ) );

		$this->assertEquals( 1, $discounts );

		$discounts = count( edd_get_discounts( array(
			'search' => 'FREE',
		) ) );

		$this->assertEquals( 0, $discounts );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_active_status() {
		$this->assertEquals( 2,count( edd_get_discounts( array(
			'status' => 'active',
		) ) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_expired_status() {
		$discount_id = EDD_Helper_Discount::created_expired_flat_discount();

		$this->assertSame( 1,count( edd_get_discounts( array(
			'status' => 'expired',
		) ) ) );

		edd_delete_discount( $discount_id );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_type() {
		$this->assertSame( 1,count( edd_get_discounts( array(
			'type' => 'percent',
		) ) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_date_created() {
		$this->assertSame( 2, count( edd_get_discounts( array(
			'date_created_query' => array(
				'year'  => date( 'Y', strtotime( 'now' ) ),
				'month' => date( 'm', strtotime( 'now' ) ),
				'day'   => date( 'd', strtotime( 'now' ) ),
			)
		) ) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_start_date() {
		$this->assertSame( 2, count( edd_get_discounts( array(
			'start_date_query' => array(
				array(
					'year'   => 2010,
					'month'  => 12,
					'day'    => 12,
				),
				'hour'   => 0,
				'minute' => 0,
				'second' => 0,
			)
		) ) ) );
	}

	/**
	 * @covers ::count()
	 */
	public function test_count_with_end_date() {
		$discounts = edd_get_discounts( array(
			'end_date_query' => array(
				array(
					'year'   => 2050,
					'month'  => 12,
					'day'    => 12,
				),
				'hour'   => 23,
				'minute' => 59,
				'second' => 59,
			)
		) );

		$this->assertSame( 2, count( $discounts ) );
	}

	/**
	 * @covers ::counts_by_status()
	 */
	public function test_counts_by_status_active_discounts() {
		$counts = edd_get_discount_counts();

		$this->assertSame( 2, $counts['active'] );
	}

	/**
	 * @covers ::counts_by_status()
	 */
	public function test_counts_by_status_inactive_discounts() {
		$counts = edd_get_discount_counts();

		$this->assertSame( 0, $counts['inactive'] );
	}

	/**
	 * @covers ::counts_by_status()
	 */
	public function test_counts_by_status_expired_discounts() {
		$counts = edd_get_discount_counts();

		$this->assertSame( 0, $counts['expired'] );
	}
}
