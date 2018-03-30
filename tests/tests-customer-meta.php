<?php
class Tests_Customer_Meta extends EDD_UnitTestCase {

	protected $_customer;
	protected $_customer_id = 0;

	function setUp() {
		parent::setUp();

		$this->_customer_id = edd_add_customer( array(
			'email' => 'customer@test.com'
		) );

		$this->_customer = new EDD_Customer( $this->_customer_id );
	}

	function test_add_metadata() {
		$this->assertFalse(    edd_add_customer_meta( $this->_customer_id, '',         ''  ) );
		$this->assertNotEmpty( edd_add_customer_meta( $this->_customer_id, 'test_key', ''  ) );
		$this->assertNotEmpty( edd_add_customer_meta( $this->_customer_id, 'test_key', '1' ) );
	}

	function test_update_metadata() {
		$this->assertEmpty(    edd_update_customer_meta( $this->_customer_id, '',           ''  ) );
		$this->assertNotEmpty( edd_update_customer_meta( $this->_customer_id, 'test_key_2', ''  ) );
		$this->assertNotEmpty( edd_update_customer_meta( $this->_customer_id, 'test_key_2', '1' ) );
	}

	function test_get_metadata() {
		$this->assertEmpty( edd_get_customer_meta( $this->_customer_id ) );
		$this->assertEmpty( edd_get_customer_meta( $this->_customer_id, 'key_that_does_not_exist', true ) );

		edd_update_customer_meta( $this->_customer_id, 'test_key_2', '1' );
		$this->assertEquals( '1', edd_get_customer_meta( $this->_customer_id, 'test_key_2', true ) );
		$this->assertInternalType( 'array', edd_get_customer_meta( $this->_customer_id, 'test_key_2', false ) );
	}

	function test_delete_metadata() {
		edd_update_customer_meta( $this->_customer_id, 'test_key', '1' );
		$this->assertTrue( edd_delete_customer_meta( $this->_customer_id, 'test_key' ) );
		$this->assertFalse( edd_delete_customer_meta( $this->_customer_id, 'key_that_does_not_exist' ) );
	}
}
