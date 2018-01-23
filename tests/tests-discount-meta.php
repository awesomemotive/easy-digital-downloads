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
	protected static $discount;

	/**
	 * Set up fixtures.
	 *
	 * @access public
	 */
	public static function wpSetUpBeforeClass() {
		$discount_id    = EDD_Helper_Discount::create_simple_percent_discount();
		self::$discount = new EDD_Discount( $discount_id );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( self::$discount->add_meta( '', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_empty_value_should_be_empty() {
		$this->assertNotEmpty( self::$discount->add_meta( 'test_key', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$discount->add_meta( 'test_key', '1' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_be_empty() {
		$this->assertEmpty( self::$discount->update_meta( '', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$discount->update_meta( 'test_key_2', '' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$discount->update_meta( 'test_key_2', '1' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( self::$discount->get_meta() );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( self::$discount->get_meta( 'key_that_does_not_exist', true ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_that_value() {
		self::$discount->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', self::$discount->get_meta( 'test_key_2', true ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata_with_valid_key_should_return_true() {
		self::$discount->update_meta( 'test_key', '1' );
		$this->assertTrue( self::$discount->delete_meta( 'test_key' ) );
	}

	/**
	 * @covers EDD_DB_Discount_Meta::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata_with_invalid_key_should_return_false() {
		$this->assertFalse( self::$discount->delete_meta( 'key_that_does_not_exist' ) );
	}
}