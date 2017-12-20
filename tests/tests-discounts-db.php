<?php

/**
 * @group edd_discounts_db
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
		edd_empty_cart();
		parent::tearDown();
	}

	public function test_get_columns() {

		$expected = array(
			'id'                  => '%d',
			'name'                => '%s',
			'code'                => '%s',
			'status'              => '%s',
			'type'                => '%s',
			'amount'              => '%s',
			'description'         => '%s',
			'max_uses'            => '%d',
			'use_count'           => '%d',
			'min_cart_price'      => '%f',
			'once_per_customer'   => '%d',
			'product_reqs'        => '%s',
			'product_condition'   => '%s',
			'scope'               => '%s',
			'created_date'        => '%s',
			'start_date'          => '%s',
			'end_date'            => '%s',
			'notes'               => '%s',
		);

		$this->assertSame( $expected, self::$db->get_columns() );

	}

	public function test_get_column_defaults() {

		$expected = array(
			'id'                  => 0,
			'name'                => '',
			'code'                => '',
			'status'              => '',
			'type'                => '',
			'amount'              => '',
			'description'         => '',
			'max_uses'            => 0,
			'use_count'           => 0,
			'min_cart_price'      => 0.00,
			'once_per_customer'   => 0,
			'product_reqs'        => '',
			'product_condition'   => '',
			'scope'               => 'global',
			'created_date'        => date( 'Y-m-d H:i:s' ),
			'start_date'          => '0000-00-00 00:00:00',
			'end_date'            => '0000-00-00 00:00:00',
			'notes'               => '',
		);

		$this->assertSame( $expected, self::$db->get_column_defaults() );
		
	}

	public function test_insert() {

		$id = self::$db->insert( array(
		 'code' => 'TEST',
		 'status' => 'active'
		) );

		$this->assertInternalType( 'int', $id );
		$this->assertTrue( $id > 0 );

	}

	public function test_update() {

		$success = self::$db->update( $this->discount_id, array( 'code' => 'NEWCODE' ) );

		$this->assertTrue( $success );

	}

	public function test_update_without_id_should_fail() {

		$success = self::$db->update( null, array( 'code' => 'NEWCODE' ) );

		$this->assertFalse( $success );

	}


	public function test_delete() {

		$success = self::$db->delete( $this->discount_id );
		$this->assertTrue( $success );

	}

	public function test_delete_without_id_should_fail() {

		$success = self::$db->delete( '' );
		$this->assertFalse( $success );

	}

	public function test_get_discounts() {

		$discounts = self::$db->get_discounts();
		$this->assertTrue( count( $discounts ) == 1 );

	}

	public function test_get_discounts_returns_expected_discount() {

		$discounts = self::$db->get_discounts();
		$this->assertTrue( $discounts[0]->code == '20OFF' );

	}

	public function test_get_discounts_with_number() {

		$d2 = EDD_Helper_Discount::create_simple_percent_discount();

		$discounts = self::$db->get_discounts( array( 'number' => 1 ) );

		$this->assertTrue( count( $discounts ) == 1 );

		$discounts = self::$db->get_discounts( array( 'number' => 2 ) );
	
		$this->assertTrue( count( $discounts ) == 2 );
	
	}

	public function test_get_discounts_with_offset() {

		$d2 = EDD_Helper_Discount::create_simple_percent_discount();
		$d3 = EDD_Helper_Discount::create_simple_percent_discount();

		$discounts = self::$db->get_discounts( array( 'offset' => 1 ) );
		$this->assertTrue( count( $discounts ) == 2 );
	
	}

	public function test_get_discounts_with_offset_order_asc() {

		$d2 = EDD_Helper_Discount::create_simple_percent_discount();
		$d3 = EDD_Helper_Discount::create_simple_percent_discount();

		$discounts = self::$db->get_discounts( array( 'offset' => 1, 'order' => 'asc' ) );

		$this->assertTrue( count( $discounts ) == 2 );
	
	}

	public function test_get_discounts_with_search_by_code() {
		$flat = EDD_Helper_Discount::create_simple_flat_discount();
		$discounts = self::$db->get_discounts( array( 'search' => '10FLAT' ) );

		$this->assertTrue( count( $discounts ) == 1 );
		$this->assertTrue( $discounts[0]->code == '10FLAT' );
	}

	public function test_get_discounts_with_search_by_name() {
		$flat = EDD_Helper_Discount::create_simple_flat_discount();
		$discounts = self::$db->get_discounts( array( 'search' => '$10 Off' ) );

		$this->assertTrue( count( $discounts ) == 1 );
		$this->assertTrue( $discounts[0]->code == '10FLAT' );
	}

	public function test_get_discounts_with_order_asc() {
		
	}

	public function test_get_discounts_with_order_desc() {
		
	}

	public function test_get_discounts_with_orderby() {
		
	}

	public function test_get_discounts_with_type() {
		
	}

	public function test_get_discounts_with_status() {
		
	}

	public function test_get_discounts_with_created_date() {
		
	}

	public function test_get_discounts_with_end_date() {
		
	}

	public function test_get_discounts_with_start_date() {
		
	}

	public function test_count() {
		
	}

	public function test_count_with_search() {
		
	}

	public function test_count_with_status() {
		
	}

	public function test_count_with_type() {
		
	}

	public function test_count_with_created_date() {
		
	}

	public function test_count_with_end_date() {
		
	}

	public function test_count_with_start_date() {
		
	}

}