<?php

/**
 * Discount DB Tests
 *
 * @covers EDD_DB_Discounts
 * @group edd_discounts_db
 * @group database
 * @group edd_discounts
 */
class Tests_Discounts_DB extends EDD_UnitTestCase {

	protected static $db;
	public $discount_id;

	public static function wpSetUpBeforeClass() {
		self::$db = EDD()->discounts;
		parent::wpSetUpBeforeClass();
	}

	public function setUp() {
		$this->discount_id = EDD_Helper_Discount::create_simple_percent_discount();
	}

	public function tearDown() {
		self::_delete_all_data();
		parent::tearDown();
	}

	/**
	 * @covers EDD_DB_Discounts::get_columns()
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
			'notes'             => '%s',
		);

		$this->assertSame( $expected, self::$db->get_columns() );
	}

	/**
	 * @covers EDD_DB_Discounts::get_column_defaults()
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
			'notes'             => '',
		);

		$this->assertSame( $expected, self::$db->get_column_defaults() );
	}

	/**
	 * @covers EDD_DB_Discounts::insert()
	 */
	public function test_insert() {
		$id = self::$db->insert( array(
			'code'   => 'TEST',
			'status' => 'active',
		) );

		$this->assertInternalType( 'int', $id );
		$this->assertTrue( $id > 0 );
	}

	/**
	 * @covers EDD_DB_Discounts::update()
	 */
	public function test_update() {
		$success = self::$db->update( $this->discount_id, array(
			'code' => 'NEWCODE',
		) );
		$this->assertTrue( $success );
	}

	/**
	 * @covers EDD_DB_Discounts::update()
	 */
	public function test_update_without_id_should_fail() {
		$success = self::$db->update( null, array(
			'code' => 'NEWCODE',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers EDD_DB_Discounts::delete()
	 */
	public function test_delete() {
		$success = self::$db->delete( $this->discount_id );
		$this->assertTrue( $success );
	}

	/**
	 * @covers EDD_DB_Discounts::delete()
	 */
	public function test_delete_without_id_should_fail() {
		$success = self::$db->delete( '' );
		$this->assertFalse( $success );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts() {
		$discounts = self::$db->get_discounts();
		$this->assertTrue( count( $discounts ) === 1 );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_returns_expected_discount() {
		$discounts = self::$db->get_discounts();
		$this->assertTrue( '20OFF' === $discounts[0]->code );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_number() {
		EDD_Helper_Discount::create_simple_percent_discount();

		$discounts = self::$db->get_discounts( array(
			'number' => 1,
		) );
		$this->assertTrue( count( $discounts ) === 1 );

		$discounts = self::$db->get_discounts( array(
			'number' => 2,
		) );
		$this->assertTrue( count( $discounts ) === 2 );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_offset() {
		EDD_Helper_Discount::create_simple_percent_discount();
		EDD_Helper_Discount::create_simple_percent_discount();

		$discounts = self::$db->get_discounts( array(
			'offset' => 1,
		) );
		$this->assertTrue( count( $discounts ) === 2 );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_offset_order_asc() {
		EDD_Helper_Discount::create_simple_percent_discount();
		EDD_Helper_Discount::create_simple_percent_discount();

		$discounts = self::$db->get_discounts( array(
			'offset' => 1,
			'order' => 'asc',
		) );
		$this->assertTrue( 2 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_search_by_code() {
		EDD_Helper_Discount::create_simple_flat_discount();
		$discounts = self::$db->get_discounts( array(
			'search' => '10FLAT',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
		$this->assertTrue( '10FLAT' === $discounts[0]->code );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_search_by_name() {
		EDD_Helper_Discount::create_simple_flat_discount();
		$discounts = self::$db->get_discounts( array(
			'search' => '$10 Off',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
		$this->assertTrue( '10FLAT' === $discounts[0]->code );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_order_asc() {
		EDD_Helper_Discount::create_simple_flat_discount();

		$discounts = self::$db->get_discounts( array(
			'order' => 'asc',
		) );

		$this->assertTrue( 2 === count( $discounts ) );
		$this->assertTrue( $discounts[0]->id < $discounts[1]->id );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_order_desc() {
		EDD_Helper_Discount::create_simple_flat_discount();

		$discounts = self::$db->get_discounts( array(
			'order' => 'desc',
		) );

		$this->assertTrue( 2 === count( $discounts ) );
		$this->assertTrue( $discounts[0]->id > $discounts[1]->id );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_orderby() {
		EDD_Helper_Discount::create_simple_flat_discount();

		$discounts = self::$db->get_discounts( array(
			'orderby' => 'code',
			'order'   => 'asc',
		) );

		$this->assertTrue( strcmp( $discounts[0]->code, $discounts[1]->code ) < 0 );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_type() {
		$discounts = self::$db->get_discounts( array(
			'type' => 'percent',
		) );

		$this->assertTrue( 1 === count( $discounts ) );

		EDD_Helper_Discount::create_simple_flat_discount();
		EDD_Helper_Discount::create_simple_flat_discount();

		$discounts = self::$db->get_discounts( array(
			'type' => 'flat',
		) );

		$this->assertTrue( 2 === count( $discounts ) );

		$discounts = self::$db->get_discounts( array(
			'type' => array(
				'percent',
				'flat',
			),
		) );

		$this->assertTrue( 3 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_status() {
		$discounts = self::$db->get_discounts( array(
			'status' => 'active',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_date_created() {
		$discounts = self::$db->get_discounts( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_created_start_date() {
		$discounts = self::$db->get_discounts( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_created_end_date() {
		$discounts = self::$db->get_discounts( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_end_date() {
		$discounts = self::$db->get_discounts( array(
			'end_date' => '2050-12-31 23:59:59',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::get_discounts()
	 */
	public function test_get_discounts_with_start_date() {
		$discounts = self::$db->get_discounts( array(
			'start_date' => '2010-12-12 23:59:59',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count() {
		$this->assertTrue( 1 === self::$db->count() );

		EDD_Helper_Discount::create_simple_flat_discount();
		EDD_Helper_Discount::create_simple_flat_discount();

		$this->assertTrue( 3 === self::$db->count() );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count_with_search() {
		$discounts = self::$db->count( array(
			'search' => '20OFF',
		) );

		$this->assertTrue( 1 === $discounts );

		$discounts = self::$db->count( array(
			'search' => 'FREE',
		) );

		$this->assertTrue( 0 === $discounts );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count_with_status() {
		$this->assertTrue( 1 === self::$db->count( array(
			'status' => 'active',
		) ) );

		EDD_Helper_Discount::created_expired_flat_discount();

		$this->assertTrue( 1 === self::$db->count( array(
			'status' => 'expired',
		) ) );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count_with_type() {
		$this->assertTrue( 1 === self::$db->count( array(
			'type' => 'percent',
		) ) );

		EDD_Helper_Discount::create_simple_percent_discount();

		$this->assertTrue( 2 === self::$db->count( array(
			'type' => 'percent',
		) ) );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count_with_date_created() {
		$this->assertTrue( 1 === self::$db->count( array(
			'date_created' => date( 'Y-m-d H:i:s', strtotime( 'now' ) ),
		) ) );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count_with_start_date() {
		$discounts = self::$db->count( array(
			'start_date' => '2010-12-12 23:59:59',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::count()
	 */
	public function test_count_with_end_date() {
		$discounts = self::$db->count( array(
			'end_date' => '2050-12-31 23:59:59',
		) );

		$this->assertTrue( 1 === count( $discounts ) );
	}

	/**
	 * @covers EDD_DB_Discounts::counts_by_status()
	 */
	public function test_counts_by_status() {
		$counts = self::$db->counts_by_status();

		$this->assertEquals( 1, $counts->active );
		$this->assertEquals( 0, $counts->inactive );
		$this->assertEquals( 0, $counts->expired );
	}

}