<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_gateways
 */
class Test_Gateways extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_payment_gateways() {
		$out = edd_get_payment_gateways();
		$this->assertArrayHasKey( 'paypal', $out );
		$this->assertArrayHasKey( 'manual', $out );

		$this->assertEquals( 'PayPal Standard', $out['paypal']['admin_label'] );
		$this->assertEquals( 'PayPal', $out['paypal']['checkout_label'] );

		$this->assertEquals( 'Test Payment', $out['manual']['admin_label'] );
		$this->assertEquals( 'Test Payment', $out['manual']['checkout_label'] );
	}

	public function test_enabled_gateways() {
		$this->assertEmpty( edd_get_enabled_payment_gateways() );
	}

	public function test_is_gateway_active() {
		$this->assertFalse( edd_is_gateway_active( 'paypal' ) );
	}

	public function test_default_gateway() {
		$this->assertEquals( 'paypal', edd_get_default_gateway() );
	}

	public function test_get_gateway_admin_label() {
		$this->assertEquals( 'paypal', edd_get_gateway_admin_label( 'paypal' ) );
		$this->assertEquals( 'manual', edd_get_gateway_admin_label( 'manual' ) );
	}

	public function test_get_gateway_checkout_label() {
		$this->assertEquals( 'paypal', edd_get_gateway_checkout_label( 'paypal' ) );
		$this->assertEquals( 'Free Purchase', edd_get_gateway_checkout_label( 'manual' ) );
	}

	public function test_show_gateways() {
		$this->assertFalse( edd_show_gateways() );
	}

	public function test_chosen_gateway() {
		$this->assertEquals( 'manual', edd_get_chosen_gateway() );
	}

	public function test_no_gateway_error() {
		edd_no_gateway_error();

		$errors = edd_get_errors();

		$this->assertArrayHasKey( 'no_gateways', $errors );
		$this->assertEquals( 'You must enable a payment gateway to use Easy Digital Downloads', $errors['no_gateways'] );
	}
}