<?php

/**
 * @group edd_customer
 */
class Tests_EDD_Customer extends WP_UnitTestCase {

	/**
	 * Setup payment and customer.
	 *
	 * @since 2.2.4
	 */
	public function setUp() {

		$this->payment_id 	= EDD_Helper_Payment::create_simple_payment();
		$this->customer		= new EDD_Customer( get_current_user_id(), true );

	}

	/**
	 * Remove payment
	 *
	 * @since 2.2.4
	 */
	public function tearDown() {
		EDD_Helper_Payment::delete_payment( $this->payment_id );
	}

	/**
	 * Test that EDD_Customer returns false when the first argument is incorrect.
	 *
	 * @since 2.2.4
	 */
	public function test_edd_customer_returns_false() {

		$this->assertFalse( $this->customer->__construct() ); // Default value is false
		$this->assertFalse( $this->customer->__construct( 12341 ) ); // ID that does not exist.

	}

	/**
	 * Test that the constructor returns null when it succeeds.
	 *
	 * @since 2.2.4
	 */
	public function test_edd_customer_returns_null_success() {
		$this->assertNull( $this->customer->__construct( get_current_user_id(), true ) );
	}

	/**
	 * Test that the __get() returns a WP_Error when the method get_$key does not exist.
	 *
	 * @since 2.2.4
	 */
	public function test_get_error() {
		$this->assertInstanceOf( 'WP_Error', $this->customer->__get( 'abc' ) );
	}

	/**
	 * Test that the __get() returns the method reutrn when the method get_$key does exist.
	 *
	 * @since 2.2.4
	 */
	public function test_get_success() {

		$this->assertNotInstanceOf( 'WP_Error', $this->customer->__get( 'notes' ) );
		$this->assertNotInstanceOf( 'WP_Error', $this->customer->__get( 'notes_count' ) );
		$this->assertNotInstanceOf( 'WP_Error', $this->customer->__get( 'raw_notes' ) );

	}

	/**
	 * Test that the create() will return false when the first arg is empty.
	 * Second assert tests that it will return false when the email is not a email.
	 *
	 * @since 2.2.4
	 */
	public function test_create_return_false() {

		$this->assertFalse( $this->customer->create() );
		$this->assertFalse( $this->customer->create( array( 'email' => 'this_is_a_invalid@email' ) ) );

	}

	/**
	 * Test that a id is returned when a customer has been created.
	 *
	 * @since 2.2.4
	 */
	public function test_create_success() {

		// EDD_Customer without a valid user id
		$customer = new EDD_Customer( 12345 );
		$this->assertTrue( is_numeric( $customer->create( array( 'payment_ids' => '1', 'email' => 'admin@example.org' ) ) ) );

	}

	/**
	 * Test that the update() will return false when the first arg is empty.
	 *
	 * @since 2.2.4
	 */
	public function test_update_return_false() {
		$this->assertFalse( $this->customer->update() );
	}

	/**
	 * Test that true is returned when a customer has been updated.
	 *
	 * @since 2.2.4
	 */
	public function test_update_success() {
		$this->assertTrue( $this->customer->update( array( 'payment_ids' => '2', 'email' => 'admin@example.org' ) ) );
	}

	/**
	 * Test that attach_payment() return false when the $payment_id is empty.
	 *
	 * @since 2.2.4
	 */
	public function test_attach_payment_empty_payment_id() {
		$this->assertFalse( $this->customer->attach_payment( '' ) );
	}

	/**
	 * Test that attach_payment() will successfully attach a first payment.
	 *
	 * @since 2.2.4
	 */
	public function test_attach_payment_first_payment() {

		$this->customer->payment_ids = null;
		$this->assertTrue( $this->customer->attach_payment( $this->payment_id ) );

	}

	/**
	 * Test that attach_payment() will successfully attach a first payment.
	 *
	 * @since 2.2.4
	 */
	public function test_attach_payment() {
		$this->assertTrue( $this->customer->attach_payment( $this->payment_id ) );
	}

}