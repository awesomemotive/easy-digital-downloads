<?php

/**
 * Test for the discount meta table.
 *
 * @covers EDD_DB_Discount_Meta
 * @group edd_discounts_db
 * @group database
 * @group edd_discounts
 */
class Tests_Discount_Meta extends EDD_UnitTestCase {

	/**
	 * Discount object test fixture.
	 *
	 * @access protected
	 * @var    EDD_Discount
	 */
	protected static $discount_id;

	/**
	 * Set up fixtures.
	 *
	 * @access public
	 */
	public static function wpSetUpBeforeClass() {
		self::$discount_id = EDD_Helper_Discount::create_simple_percent_discount();
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( edd_add_discount_meta( self::$discount_id, '', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_empty_value_should_be_empty() {
		$this->assertNotEmpty( edd_add_discount_meta( self::$discount_id, 'test_key', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_discount_meta( self::$discount_id, 'test_key', '1' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_be_empty() {
		$this->assertEmpty( edd_update_discount_meta( self::$discount_id, '', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_discount_meta( self::$discount_id, 'test_key_2', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_discount_meta( self::$discount_id, 'test_key_2', '1' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( edd_get_discount_meta( self::$discount_id ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( edd_get_discount_meta( self::$discount_id, 'key_that_does_not_exist', true ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_that_value() {
		edd_update_discount_meta( self::$discount_id, 'test_key_2', '1' );
		$this->assertEquals( '1', edd_get_discount_meta( self::$discount_id, 'test_key_2', true ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata_with_valid_key_should_return_true() {
		edd_update_discount_meta( self::$discount_id, 'test_key', '1' );
		$this->assertTrue( edd_delete_discount_meta( self::$discount_id, 'test_key' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata_with_invalid_key_should_return_false() {
		$this->assertFalse( edd_delete_discount_meta( self::$discount_id,  'key_that_does_not_exist' ) );
	}
}
