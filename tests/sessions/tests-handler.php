<?php
namespace EDD\Tests\Sessions;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

class Handler extends EDD_UnitTestCase {

	private static $order;
	private static $user_id;
	private static $customer_id;
	private static $checkout_page_id;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$order_id    = EDD_Helper_Payment::create_simple_payment();
		self::$order = edd_get_order( $order_id );

		// Create a test user and customer for set_customer tests
		self::$user_id = wp_create_user( 'testuser', 'password', 'test@example.com' );
		self::$customer_id = edd_add_customer( array(
			'user_id'    => self::$user_id,
			'name'       => 'John Doe',
			'email'      => 'test@example.com',
		) );

		// Create checkout page for set_customer tests
		self::$checkout_page_id = wp_insert_post( array(
			'post_title'   => 'Checkout',
			'post_content' => '[download_checkout]',
			'post_status'  => 'publish',
			'post_type'    => 'page'
		) );

		// Set the checkout page in EDD options
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	public function test_session_type_is_db() {
		$this->assertfalse( EDD()->session->use_php_sessions() );
	}

	public function test_session_component_exists() {
		$component = edd_get_component( 'session' );
		$this->assertInstanceOf( '\\EDD\\Component', $component );
	}

	public function test_set() {
		$this->assertEquals( 'bar', EDD()->session->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', EDD()->session->get( 'foo' ) );
	}

	public function test_set_new_order_purchase_key_in_session() {
		$purchase_session =array(
			'purchase_key' => self::$order->payment_key,
		);
		edd_set_purchase_session( $purchase_session );

		$session = edd_get_purchase_session();
		$this->assertEquals( self::$order->payment_key, $session['purchase_key'] );
	}

	public function test_should_start_session() {

		$blacklist = EDD()->session->get_blacklist();

		foreach( $blacklist as $uri ) {
			$this->go_to( '/' . $uri );
			$this->assertFalse( EDD()->session->should_start_session() );
		}
	}

	public function test_use_php_sessions_is_false() {
		delete_option( 'edd_session_handling' );
		$this->assertfalse( EDD()->session->use_php_sessions() );
	}

	public function test_set_customer_returns_defaults_when_not_logged_in() {
		// Ensure user is logged out
		wp_set_current_user( 0 );

		EDD()->session->set( 'customer', null );

		$result = \EDD\Sessions\Customer::set();

		// Should return default values when not logged in
		$expected_defaults = array(
			'customer_id' => '',
			'user_id'     => '',
			'name'        => '',
			'first_name'  => '',
			'last_name'   => '',
			'email'       => '',
			'address'     => array(
				'country' => '',
				'state'   => '',
				'city'    => '',
			),
		);

		$this->assertEquals( $expected_defaults, $result );
		$this->assertEquals( $expected_defaults, \EDD\Sessions\Customer::get() );
	}

	public function test_set_customer_overwrites_existing_session_data() {
		// Set existing customer data
		$existing_customer = array( 'id' => 999, 'email' => 'existing@example.com' );
		EDD()->session->set( 'customer', $existing_customer );

		// Login user
		wp_set_current_user( self::$user_id );

		$result = \EDD\Sessions\Customer::set();

		// Customer data should be overwritten with actual customer data
		$customer = edd_get_customer( self::$customer_id );
		$this->assertEquals( $customer->id, $result['customer_id'] );
		$this->assertEquals( $customer->email, $result['email'] );
		$this->assertNotEquals( $existing_customer, $result );
	}

	public function test_set_customer_returns_defaults_when_no_customer_found() {
		// Create a user without an EDD customer record
		$user_without_customer = wp_create_user( 'nocustomer', 'password', 'nocustomer@example.com' );

		// Login user without customer
		wp_set_current_user( $user_without_customer );

		$result = \EDD\Sessions\Customer::set();

		// Should return WP_User data when no EDD customer found
		$expected_result = array(
			'customer_id' => '',
			'user_id'     => (string) $user_without_customer,
			'name'        => 'nocustomer',
			'first_name'  => '',
			'last_name'   => '',
			'email'       => 'nocustomer@example.com',
			'address'     => array(
				'country' => '',
				'state'   => '',
				'city'    => '',
			),
		);

		$this->assertEquals( $expected_result, $result );

		// Clean up
		wp_delete_user( $user_without_customer );
	}

	public function test_set_customer_handles_single_name() {
		// Create customer with single name
		$single_name_user_id = wp_create_user( 'singlename', 'password', 'single@example.com' );
		$single_name_customer_id = edd_add_customer( array(
			'user_id' => $single_name_user_id,
			'name'    => 'Madonna',
			'email'   => 'single@example.com',
		) );

		// Login user
		wp_set_current_user( $single_name_user_id );

		$result = \EDD\Sessions\Customer::set();

		// Verify single name handling
		$this->assertEquals( 'Madonna', $result['first_name'] );
		$this->assertEquals( '', $result['last_name'] );
		$this->assertEquals( 'Madonna', $result['name'] );

		// Clean up
		edd_delete_customer( $single_name_customer_id );
		wp_delete_user( $single_name_user_id );
	}

	public function test_set_customer_handles_multiple_space_name() {
		// Create customer with multiple spaces in name
		$multi_space_user_id = wp_create_user( 'multispace', 'password', 'multi@example.com' );
		$multi_space_customer_id = edd_add_customer( array(
			'user_id' => $multi_space_user_id,
			'name'    => 'Mary Jane Watson Smith',
			'email'   => 'multi@example.com',
		) );

		// Login user
		wp_set_current_user( $multi_space_user_id );

		$result = \EDD\Sessions\Customer::set();

		// Verify multiple space name handling (explode with limit 2 should split into first name and rest)
		$this->assertEquals( 'Mary', $result['first_name'] );
		$this->assertEquals( 'Jane Watson Smith', $result['last_name'] );
		$this->assertEquals( 'Mary Jane Watson Smith', $result['name'] );

		// Clean up
		edd_delete_customer( $multi_space_customer_id );
		wp_delete_user( $multi_space_user_id );
	}

	public function test_get_customer_returns_session_data_when_exists() {
		// Set customer data in session
		$customer_data = array(
			'customer_id' => 123,
			'user_id'     => 456,
			'name'        => 'Test User',
			'first_name'  => 'Test',
			'last_name'   => 'User',
			'email'       => 'test@example.com',
		);
		EDD()->session->set( 'customer', $customer_data );

		$result = \EDD\Sessions\Customer::get();

		$this->assertEquals( $customer_data, $result );
	}

	public function test_get_customer_calls_set_when_logged_in_and_no_session_data() {
		// Clear customer session
		EDD()->session->set( 'customer', null );

		// Login user
		wp_set_current_user( self::$user_id );

		$result = \EDD\Sessions\Customer::get();

		// Should have called set() and returned customer data
		$customer = edd_get_customer( self::$customer_id );
		$this->assertEquals( $customer->id, $result['customer_id'] );
		$this->assertEquals( $customer->email, $result['email'] );
	}

	public function test_get_customer_returns_defaults_when_not_logged_in_and_no_session_data() {
		// Clear customer session and logout
		EDD()->session->set( 'customer', null );
		wp_set_current_user( 0 );

		$result = \EDD\Sessions\Customer::get();

		$expected_defaults = array(
			'customer_id' => '',
			'user_id'     => '',
			'name'        => '',
			'first_name'  => '',
			'last_name'   => '',
			'email'       => '',
			'address'     => array(
				'country' => '',
				'state'   => '',
				'city'    => '',
			),
		);

		$this->assertEquals( $expected_defaults, $result );
	}

	public function test_set_customer_logged_in_user_without_edd_customer_during_checkout() {
		// Create a WordPress user without an EDD customer record
		$user_id = wp_create_user( 'checkoutuser', 'password', 'checkout@example.com' );

		// Add some user meta that would be populated in a real scenario
		update_user_meta( $user_id, 'first_name', 'Checkout' );
		update_user_meta( $user_id, 'last_name', 'User' );

		// Login the user
		wp_set_current_user( $user_id );

		// Clear any existing customer session data
		EDD()->session->set( 'customer', null );

		// This should trigger the maybe_set_customer_data method
		// and should now populate user data without causing deprecation warnings
		$result = \EDD\Sessions\Customer::set();

		// Should return WP_User data since no EDD customer exists but user is logged in
		$expected_result = array(
			'customer_id' => '',
			'user_id'     => (string) $user_id,
			'name'        => 'checkoutuser',
			'first_name'  => 'Checkout',
			'last_name'   => 'User',
			'email'       => 'checkout@example.com',
			'address'     => array(
				'country' => '',
				'state'   => '',
				'city'    => '',
			),
		);

		$this->assertEquals( $expected_result, $result );

		// Clean up
		wp_delete_user( $user_id );
	}

	public function test_set_customer_with_address_data() {
		// Clear customer session
		EDD()->session->set( 'customer', null );

		// Set address data
		$address_data = array(
			'address' => array(
				'state'   => 'CA',
				'country' => 'US',
				'city'    => 'Los Angeles',
			),
		);

		$result = \EDD\Sessions\Customer::set( $address_data );

		// Should include address data in result
		$this->assertArrayHasKey( 'address', $result );
		$this->assertEquals( 'CA', $result['address']['state'] );
		$this->assertEquals( 'US', $result['address']['country'] );
		$this->assertEquals( 'Los Angeles', $result['address']['city'] );
	}

	public function test_set_customer_merges_address_with_defaults() {
		// Clear customer session
		EDD()->session->set( 'customer', null );

		// Set partial address data
		$address_data = array(
			'address' => array(
				'country' => 'CA',
			),
		);

		$result = \EDD\Sessions\Customer::set( $address_data );

		// Should merge with defaults
		$this->assertEquals( 'CA', $result['address']['country'] );
		$this->assertEquals( '', $result['address']['state'] );
	}

	public function test_get_customer_includes_address_data() {
		// Set customer data with address in session
		$customer_data = array(
			'customer_id' => 123,
			'user_id'     => 456,
			'name'        => 'Test User',
			'first_name'  => 'Test',
			'last_name'   => 'User',
			'email'       => 'test@example.com',
			'address'     => array(
				'state'   => 'NY',
				'country' => 'US',
			),
		);
		EDD()->session->set( 'customer', $customer_data );

		$result = \EDD\Sessions\Customer::get();

		$this->assertEquals( $customer_data, $result );
		$this->assertArrayHasKey( 'address', $result );
		$this->assertEquals( 'NY', $result['address']['state'] );
		$this->assertEquals( 'US', $result['address']['country'] );
	}
}
