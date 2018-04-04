<?php
namespace EDD\Customers;

/**
 * Customer Meta Tests.
 *
 * @group edd_customers_db
 * @group database
 * @group edd_customers
 */
class Tests_Customer_Meta extends \EDD_UnitTestCase {

	/**
	 * Customer fixture.
	 *
	 * @access protected
	 * @var    \EDD_Customer
	 */
	protected static $customer = null;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$customer = parent::edd()->customer->create_and_get();
	}

	public function tearDown() {
		parent::tearDown();

		edd_get_component_interface( 'customer', 'meta' )->delete_all();
	}

	/**
	 * @covers \EDD_DB_Note_Meta::add_meta()
	 * @covers Note::add_meta()
	 */
	public function test_add_metadata_with_empty_key_value_should_return_false() {
		$this->assertFalse( self::$customer->add_meta( '', '' ) );
	}

	public function test_add_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$customer->add_meta( 'test_key', '' ) );
	}

	public function test_add_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$customer->add_meta( 'test_key', '1' ) );
	}

	public function test_update_metadata_with_empty_key_value_should_return_false() {
		$this->assertEmpty( self::$customer->update_meta( '', '' ) );
	}

	public function test_update_metadata_with_empty_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$customer->update_meta( 'test_key_2', '' ) );
	}

	public function test_update_metadata_with_key_value_should_not_be_empty() {
		$this->assertNotEmpty( self::$customer->update_meta( 'test_key_2', '1' ) );
	}

	public function test_get_metadata_with_no_args_should_be_empty() {
		$this->assertEmpty( self::$customer->get_meta() );
	}

	public function test_get_metadata_with_invalid_key_should_be_empty() {
		$this->assertEmpty( self::$customer->get_meta( 'key_that_does_not_exist', true ) );
		self::$customer->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', self::$customer->get_meta( 'test_key_2', true ) );
		$this->assertInternalType( 'array', self::$customer->get_meta( 'test_key_2', false ) );
	}

	public function test_get_metadata_after_update_should_return_1_and_be_of_type_array() {
		self::$customer->update_meta( 'test_key_2', '1' );

		$this->assertEquals( '1', self::$customer->get_meta( 'test_key_2', true ) );
		$this->assertInternalType( 'array', self::$customer->get_meta( 'test_key_2', false ) );
	}

	public function test_delete_metadata_after_update() {
		self::$customer->update_meta( 'test_key', '1' );

		$this->assertTrue( self::$customer->delete_meta( 'test_key' ) );
		$this->assertFalse( self::$customer->delete_meta( 'key_that_does_not_exist' ) );
	}
}
