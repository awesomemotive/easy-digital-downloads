<?php

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Checkout\Address as CheckoutAddress;
use EDD\Tests\Helpers\EDD_Helper_Download as Download;

/**
 * Checkout tests.
 */
class Address extends EDD_UnitTestCase {

	public function tearDown(): void {
		edd_delete_option( 'stripe_billing_fields');
		edd_delete_option( 'enable_taxes' );
		edd_delete_option( 'checkout_address_fields' );
		EDD()->session->set( 'customer', null );
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	public function test_address_fields_default_empty() {
		$address = new CheckoutAddress();

		$this->assertEmpty( $address->get_fields() );
	}

	public function test_address_fields_custom() {
		edd_update_option(
			'checkout_address_fields',
			array(
				'address' => 1,
				'city'    => 1,
				'state'   => 1,
				'zip'     => 1,
				'country' => 1,
			)
		);

		$address = new CheckoutAddress();

		$this->assertCount( 5, $address->get_fields() );
	}

	public function test_address_fields_over_stripe_billing_fields() {
		edd_update_option( 'stripe_billing_fields', 'full' );
		edd_update_option( 'checkout_address_fields', array( 'address' => 1 ) );

		$address = new CheckoutAddress();

		$this->assertCount( 1, $address->get_fields() );
	}

	public function test_address_fields_full_include_address() {
		edd_update_option( 'stripe_billing_fields', 'full' );

		$address = new CheckoutAddress();

		$this->assertTrue( in_array( 'address', $address->get_fields() ) );
	}

	public function test_address_fields_country_has_one_field() {
		edd_update_option( 'checkout_address_fields', array( 'country' => 1 ) );

		$address = new CheckoutAddress();

		$this->assertCount( 1, $address->get_fields() );
	}

	public function test_address_fields_zip_country_has_two_fields() {
		edd_update_option( 'stripe_billing_fields', 'zip_country' );

		$address = new CheckoutAddress();

		$this->assertCount( 2, $address->get_fields() );
	}

	public function test_address_fields_manually_set() {
		edd_update_option( 'stripe_billing_fields', 'full' );

		$address = new CheckoutAddress();
		$this->assertCount( 6, $address->get_fields() );

		$address->fields = array( 'country' );
		$this->assertCount( 1, $address->get_fields() );
	}

	public function test_address_fields_empty_taxes_enabled() {
		edd_update_option( 'enable_taxes', 1 );
		$download = Download::create_simple_download();
		edd_add_to_cart( $download->ID );

		$address = new CheckoutAddress();

		$this->assertNotEmpty( $address->get_fields() );
	}

	/**
	 * Test set_up_customer returns default customer data when not logged in.
	 */
	public function test_set_up_customer_not_logged_in() {
		$address = new CheckoutAddress();

		// Use reflection to call protected method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$customer = $method->invoke( $address );

		$this->assertIsArray( $customer );
		$this->assertArrayHasKey( 'address', $customer );
		$this->assertEmpty( $customer['address']['country'] );
	}

	/**
	 * Test set_up_customer uses session data when available.
	 */
	public function test_set_up_customer_with_session_data() {
		EDD()->session->set( 'customer', array(
			'address' => array(
				'country' => 'US',
				'state'   => 'CA',
			),
		) );

		$address = new CheckoutAddress();

		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$customer = $method->invoke( $address );

		$this->assertEquals( 'US', $customer['address']['country'] );
		$this->assertEquals( 'CA', $customer['address']['state'] );
	}

	/**
	 * Test set_up_customer uses saved user address when available.
	 */
	public function test_set_up_customer_uses_saved_address() {
		// Create a user and customer
		$user_id = $this->factory->user->create( array(
			'user_email' => 'test-address@example.com',
		) );
		$customer_id = edd_add_customer( array(
			'user_id' => $user_id,
			'email'   => 'test-address@example.com',
		) );

		edd_maybe_add_customer_address( $customer_id, array(
			'country'     => 'GB',
			'region'      => 'ENG',
			'city'        => 'London',
			'postal_code' => 'SW1A 1AA',
		) );

		wp_set_current_user( $user_id );

		$address = new CheckoutAddress();

		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$result = $method->invoke( $address );

		// Should use saved address data
		$this->assertEquals( 'GB', $result['address']['country'] );
		$this->assertEquals( 'ENG', $result['address']['state'] );
		$this->assertEquals( 'London', $result['address']['city'] );
		$this->assertEquals( 'SW1A 1AA', $result['address']['zip'] );

		edd_delete_customer( $customer_id );
	}

	/**
	 * Test set_up_customer when logged in but no saved address exists.
	 */
	public function test_set_up_customer_logged_in_no_saved_address() {
		$user_id = $this->factory->user->create( array(
			'user_email' => 'no-address@example.com',
		) );
		$customer_id = edd_add_customer( array(
			'user_id' => $user_id,
			'email'   => 'no-address@example.com',
		) );

		wp_set_current_user( $user_id );

		$address = new CheckoutAddress();

		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$result = $method->invoke( $address );

		// Should have default empty address since no saved address
		$this->assertEmpty( $result['address']['country'] );
		$this->assertEmpty( $result['address']['state'] );
		// But should have customer info
		$this->assertEquals( $customer_id, $result['customer_id'] );
		$this->assertEquals( 'no-address@example.com', $result['email'] );

		edd_delete_customer( $customer_id );
	}

	/**
	 * Test that saved address populates all address fields.
	 */
	public function test_set_up_customer_with_complete_saved_address() {
		$user_id = $this->factory->user->create( array(
			'user_email' => 'complete-address@example.com',
		) );
		$customer_id = edd_add_customer( array(
			'user_id' => $user_id,
			'email'   => 'complete-address@example.com',
		) );

		edd_maybe_add_customer_address( $customer_id, array(
			'country'     => 'AU',
			'region'      => 'NSW',
			'city'        => 'Sydney',
			'postal_code' => '2000',
			'address'     => '123 Test Street',
		) );

		wp_set_current_user( $user_id );

		$address = new CheckoutAddress();

		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$result = $method->invoke( $address );

		// All fields should come from saved address
		$this->assertEquals( 'AU', $result['address']['country'] );
		$this->assertEquals( 'NSW', $result['address']['state'] );
		$this->assertEquals( 'Sydney', $result['address']['city'] );
		$this->assertEquals( '2000', $result['address']['zip'] );
		$this->assertEquals( '123 Test Street', $result['address']['line1'] );

		edd_delete_customer( $customer_id );
	}
}
