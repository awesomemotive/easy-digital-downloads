<?php

namespace EDD\Tests\Gateways;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Registry extends EDD_UnitTestCase {

	public function test_mock_gateway() {
		// use mocked builder to register a gateway with id foo and label Foo
		$gateway = $this->mock_Base_Object();
		$gateway->method( 'get_admin_label' )->willReturn( 'Sample Gateway' );
		$gateway->method( 'get_checkout_label' )->willReturn( 'Sample Gateway' );

		$this->assertEquals( 'Sample Gateway', $gateway->get_admin_label() );
		$this->assertEquals( 'Sample Gateway', $gateway->get_checkout_label() );
		$this->assertTrue( $gateway instanceof \EDD\Gateways\Gateway );
	}

	public function test_legacy_gateway_registration() {
		add_filter( 'edd_payment_gateways', array( $this, 'register_gateway_with_legacy_filter' ) );

		$gateways = edd_get_payment_gateways();

		$this->assertArrayHasKey( 'sample', $gateways );
		$this->assertEquals( 'Sample', $gateways['sample']['admin_label'] );
		$this->assertArrayHasKey( 'stripe', $gateways );
		$this->assertEquals( 'Stripe', $gateways['stripe']['admin_label'] );
	}

	public function test_legacy_gateway_registration_get_enabled() {
		add_filter( 'edd_payment_gateways', array( $this, 'register_gateway_with_legacy_filter' ) );
		edd_update_option( 'gateways', array( 'sample' => 1, 'stripe' => 1 ) );

		$gateways = edd_get_enabled_payment_gateways();

		$this->assertArrayHasKey( 'sample', $gateways );
		$this->assertEquals( 'Sample', $gateways['sample']['admin_label'] );
		$this->assertArrayHasKey( 'stripe', $gateways );
		$this->assertEquals( 'Stripe', $gateways['stripe']['admin_label'] );

		edd_delete_option( 'gateways' );
	}

	public function register_gateway_with_legacy_filter( $gateways ) {
		// Format: ID => Name.
		$gateways['sample'] = array(
			'admin_label'    => 'Sample',
			'checkout_label' => __( 'Sample', 'easy-digital-downloads' ),
			'supports'       => array(
				'buy_now',
			),
			'icons'          => array(
				'mastercard',
				'visa',
				'discover',
				'americanexpress',
			),
		);

		return $gateways;
	}

	public function test_gateway_class_stripe() {
		$gateway = new \EDD\Gateways\Stripe\Gateway();

		$this->assertTrue( $gateway instanceof \EDD\Gateways\Gateway );
		$this->assertEquals( 'Stripe', $gateway->get_admin_label() );
		$this->assertEquals( 'Credit Card', $gateway->get_checkout_label() );
		$this->assertEquals( 'stripe', $gateway->get_id() );
		$this->assertTrue( in_array( 'buy_now', $gateway->get_supports(), true ) );
		$this->assertTrue( in_array( 'visa', $gateway->get_icons(), true ) );
	}

	public function test_gateway_class_paypal_commerce() {
		$gateway = new \EDD\Gateways\PayPal\Gateway();

		$this->assertTrue( $gateway instanceof \EDD\Gateways\Gateway );
		$this->assertEquals( 'PayPal', $gateway->get_admin_label() );
		$this->assertEquals( 'PayPal', $gateway->get_checkout_label() );
		$this->assertEquals( 'paypal_commerce', $gateway->get_id() );
		$this->assertTrue( in_array( 'buy_now', $gateway->get_supports(), true ) );
		$this->assertTrue( in_array( 'paypal', $gateway->get_icons(), true ) );
	}

	public function test_gateway_class_store_gateway() {
		$gateway = new \EDD\Gateways\StoreGateway\Gateway();

		$this->assertTrue( $gateway instanceof \EDD\Gateways\Gateway );
		$this->assertEquals( 'Store Gateway', $gateway->get_admin_label() );
		$this->assertEquals( 'Store Gateway', $gateway->get_checkout_label() );
		$this->assertEquals( 'manual', $gateway->get_id() );
		$this->assertEmpty( $gateway->get_supports(), true );
	}

	/**
	 * Mocks a copy of the Base_Object abstract class.
	 *
	 * @return \EDD\Gateways\Gateway
	 */
	protected function mock_Base_Object() {
		return $this->getMockForAbstractClass( '\EDD\Gateways\Gateway' );
	}
}
