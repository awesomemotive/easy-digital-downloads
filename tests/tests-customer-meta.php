<?php
class Tests_Customer_Meta extends WP_UnitTestCase {

	protected $_customer;
	protected $_customer_id = 0;

	function setUp() {
		parent::setUp();

		$args = array(
			'email' => 'customer@test.com'
		);

		$this->_customer_id = EDD()->customers->add( $args );

		$this->_customer = new EDD_Customer( $this->_customer_id );

	}

	function test_add_metadata() {
		$this->assertFalse( $this->_customer->add_meta( '', '' ) );
		$this->assertNotEmpty( $this->_customer->add_meta( 'test_key', '' ) );
		$this->assertNotEmpty( $this->_customer->add_meta( 'test_key', '1' ) );
	}

	function test_update_metadata() {
		$this->assertEmpty( $this->_customer->update_meta( '', '' ) );
		$this->assertNotEmpty( $this->_customer->update_meta( 'test_key_2' , '' ) );
		$this->assertNotEmpty( $this->_customer->update_meta( 'test_key_2', '1' ) );
	}

	function test_get_metadata() {
		$this->assertEmpty( $this->_customer->get_meta() );
		$this->assertEmpty( $this->_customer->get_meta( 'key_that_does_not_exist', true ) );
		$this->_customer->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', $this->_customer->get_meta( 'test_key_2', true ) );
		$this->assertInternalType( 'array', $this->_customer->get_meta( 'test_key_2', false ) );
	}

	function test_delete_metadata() {
		$this->_customer->update_meta( 'test_key', '1' );
		$this->assertTrue( $this->_customer->delete_meta( 'test_key' ) );
		$this->assertFalse( $this->_customer->delete_meta( 'key_that_does_not_exist' ) );
	}

}