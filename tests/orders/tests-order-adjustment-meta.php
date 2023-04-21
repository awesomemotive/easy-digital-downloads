<?php
/**
 * Order item meta tests.
 *
 * @group edd_orders
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Order_Adjustment_Meta_Tests extends EDD_UnitTestCase {

	/**
	 * Order adjustment fixture.
	 *
	 * @access protected
	 * @var    Order
	 */
	protected static $order_adjustment = null;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_adjustment = parent::edd()->order_adjustment->create_and_get();
	}

	public function tearDown(): void {
		parent::tearDown();

		edd_get_component_interface( 'order_adjustment', 'meta' )->truncate();
	}

	/**
	 * @covers ::edd_add_order_adjustment_meta
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( edd_add_order_adjustment_meta( self::$order_adjustment->id, '', '' ) );
	}

	/**
	 * @covers ::edd_add_order_adjustment_meta
	 */
	public function test_add_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_order_adjustment_meta( self::$order_adjustment->id, 'test_key', '' ) );
	}

	/**
	 * @covers ::edd_add_order_adjustment_meta
	 */
	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_add_order_adjustment_meta( self::$order_adjustment->id, 'test_key', '1' ) );
	}

	/**
	 * @covers ::edd_update_order_adjustment_meta
	 */
	public function test_update_metadata_with_empty_key_value_should_return_false() {
		$this->assertEmpty( edd_update_order_adjustment_meta( self::$order_adjustment->id, '', '' ) );
	}

	/**
	 * @covers ::edd_update_order_adjustment_meta
	 */
	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', '' ) );
	}

	/**
	 * @covers ::edd_update_order_adjustment_meta
	 */
	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( edd_update_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', '1' ) );
	}

	/**
	 * @covers ::edd_get_order_adjustment_meta
	 */
	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( edd_get_order_adjustment_meta( self::$order_adjustment->id, '' ) );
	}

	/**
	 * @covers ::edd_get_order_adjustment_meta
	 */
	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( edd_get_order_adjustment_meta( self::$order_adjustment->id, 'key_that_does_not_exist', true ) );
		edd_update_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', '1' );
		$this->assertEquals( '1', edd_get_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', true ) );
		$this->assertIsArray( edd_get_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', false ) );
	}

	/**
	 * @covers ::edd_get_order_adjustment_meta
	 */
	public function test_get_metadata_after_update_should_return_1_and_be_of_type_array() {
		edd_update_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', '1' );

		$this->assertEquals( '1', edd_get_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', true ) );
		$this->assertIsArray( edd_get_order_adjustment_meta( self::$order_adjustment->id, 'test_key_2', false ) );
	}

	/**
	 * @covers ::edd_delete_order_adjustment_meta
	 */
	public function test_delete_metadata_after_update() {
		edd_update_order_adjustment_meta( self::$order_adjustment->id, 'test_key', '1' );

		$this->assertTrue( edd_delete_order_adjustment_meta( self::$order_adjustment->id, 'test_key' ) );
		$this->assertFalse( edd_delete_order_adjustment_meta( self::$order_adjustment->id, 'key_that_does_not_exist' ) );
	}
}
