<?php
namespace EDD\Discounts;

/**
 * Test for the discount meta table.
 *
 * @covers \EDD\Database\Queries\Adjustment
 * @group edd_discounts_db
 * @group database
 * @group edd_discounts
 */
class Tests_Meta extends \EDD_UnitTestCase {

	/**
	 * Discount object test fixture.
	 *
	 * @access protected
	 * @var    int
	 */
	protected static $discount_id;

	/**
	 * Set up fixtures.
	 *
	 * @access public
	 */
	public static function wpSetUpBeforeClass() {
		self::$discount_id = self::edd()->discount->create_object( array(
			'name'              => '20 Percent Off',
			'code'              => '20OFF',
			'status'            => 'active',
			'type'              => 'percent',
			'amount'            => '20',
			'use_count'         => 54,
			'max_uses'          => 10,
			'min_charge_amount' => 128,
			'product_condition' => 'all',
			'start_date'        => '2010-12-12 00:00:00',
			'end_date'          => '2050-12-31 23:59:59'
		) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_be_null() {
		$this->assertFalse( edd_add_adjustment_meta( self::$discount_id, '', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_empty_value_should_be_empty() {
		$this->assertNotEmpty( edd_add_adjustment_meta( self::$discount_id, 'test_key', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::add_meta()
	 * @covers EDD_Discount::add_meta()
	 */
	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_adjustment_meta( self::$discount_id, 'test_key', '1' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_empty_key_value_should_be_empty() {
		$this->assertEmpty( edd_update_adjustment_meta( self::$discount_id, '', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_adjustment_meta( self::$discount_id, 'test_key_2', '' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::update_meta()
	 * @covers EDD_Discount::update_meta()
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_adjustment_meta( self::$discount_id, 'test_key_2', '1' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_with_no_args_should_return_array() {
		$this->assertSame( 1, count( edd_get_adjustment_meta( self::$discount_id ) ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( edd_get_adjustment_meta( self::$discount_id, 'key_that_does_not_exist', true ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::get_meta()
	 * @covers EDD_Discount::get_meta()
	 */
	public function test_get_metadata_after_update_should_return_that_value() {
		edd_update_adjustment_meta( self::$discount_id, 'test_key_2', '1' );
		$this->assertEquals( '1', edd_get_adjustment_meta( self::$discount_id, 'test_key_2', true ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata_with_valid_key_should_return_true() {
		edd_update_adjustment_meta( self::$discount_id, 'test_key', '1' );
		$this->assertTrue( edd_delete_adjustment_meta( self::$discount_id, 'test_key' ) );
	}

	/**
	 * @covers \EDD\Database\Queries\Adjustment::delete_meta()
	 * @covers EDD_Discount::delete_meta()
	 */
	public function test_delete_metadata_with_invalid_key_should_return_false() {
		$this->assertFalse( edd_delete_adjustment_meta( self::$discount_id,  'key_that_does_not_exist' ) );
	}

	/**
	 * @covers \edd_get_discount_product_condition()
	 */
	public function test_discount_product_condition() {
		$this->assertSame( 'all', edd_get_discount_product_condition( self::$discount_id ) );
	}
}
