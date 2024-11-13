<?php
namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for functions.
 */
class PaymentElements extends EDD_UnitTestCase {

	public function test_default_payment_elements_theme() {
		$this->assertEquals( 'stripe',edds_get_stripe_payment_elements_theme() );
	}

	public function test_default_payment_elements_variables() {
		$this->assertEmpty( edds_get_stripe_payment_elements_variables() );
	}

	public function test_payment_elements_rules() {
		$this->assertEmpty( edds_get_stripe_payment_elements_rules() );
	}

	public function test_default_payment_elements_layout() {
		$expected = array(
			'type'             => 'tabs',
			'defaultCollapsed' => false,
		);

		$this->assertSame( $expected, edds_get_stripe_payment_elements_layout() );
	}

	public function test_default_payment_elements_wallets() {
		$expected = array(
			'applePay'  => 'auto',
			'googlePay' => 'auto',
		);

		$this->assertSame( $expected, edds_get_stripe_payment_elements_wallets() );
	}

	public function test_disable_payment_elements_wallets() {
		$expected = array(
			'applePay'  => 'never',
			'googlePay' => 'never',
		);

		add_filter( 'edds_stripe_payment_elements_disable_wallets', '__return_true' );

		$this->assertSame( $expected, edds_get_stripe_payment_elements_wallets() );

		remove_filter( 'edds_stripe_payment_elements_disable_wallets', '__return_true' );
	}

	public function test_default_payment_elements_label_style() {
		$this->assertEquals( 'above', edds_get_stripe_payment_elements_label_style() );
	}

	public function test_default_payment_elements_fonts() {
		$this->assertEmpty( edds_get_stripe_payment_elements_fonts() );
	}

	public function test_default_payment_elements_fields() {
		$expected = array(
			'billingDetails' => array(
				'name'    => 'auto',
				'email'   => 'never', // It is not advised to change this to auto, as it will create duplicate email fields on checkout.
				'phone'   => 'never',
				'address' => 'never',
			),
		);

		$this->assertSame( $expected, edds_get_stripe_payment_elements_fields() );
	}

	public function test_default_payment_elements_terms() {
		$this->assertSame( array( 'card' => 'auto' ), edds_get_stripe_payment_elements_terms() );
	}

	public function test_default_payment_elements_payment_method_types() {
		$this->assertEmpty( edds_payment_element_payment_method_types() );
	}

	public function test_payment_method_types() {
		$this->assertIsArray( \EDD\Gateways\Stripe\PaymentMethods::list() );
	}

	public function test_payment_method_types_includes_alipay() {
		$methods = \EDD\Gateways\Stripe\PaymentMethods::list();

		$this->assertArrayHasKey( 'alipay', $methods );
	}

	public function test_payment_method_types_get_label() {
		$this->assertEquals( 'Cartes Bancaires', \EDD\Gateways\Stripe\PaymentMethods::get_label( 'cartes_bancaires' ) );
	}

	public function test_payment_method_types_get_label_invalid_is_empty() {
		$this->assertEmpty( \EDD\Gateways\Stripe\PaymentMethods::get_label( 'fake_type' ) );
	}

	public function test_order_with_payment_method_returns_label() {
		$order = parent::edd()->order->create_and_get();

		edd_add_order_meta( $order->id, 'stripe_payment_method_type', 'alipay' );

		$this->assertEquals( 'Alipay', edd_get_gateway_checkout_label( 'stripe', $order ) );
		$this->assertEquals( 'Stripe (Alipay)', edd_get_gateway_admin_label( 'stripe', $order ) );
	}

	public function test_order_without_payment_method_returns_default_label() {
		$order = parent::edd()->order->create_and_get();

		$this->assertEquals( 'Credit Card', edd_get_gateway_checkout_label( 'stripe', $order ) );
		$this->assertEquals( 'Stripe', edd_get_gateway_admin_label( 'stripe', $order ) );
	}

	public function test_order_with_invalid_payment_method_returns_default_label() {
		$order = parent::edd()->order->create_and_get();

		edd_add_order_meta( $order->id, 'stripe_payment_method_type', 'not_a_method' );

		$this->assertEquals( 'Credit Card', edd_get_gateway_checkout_label( 'stripe', $order ) );
		$this->assertEquals( 'Stripe', edd_get_gateway_admin_label( 'stripe', $order ) );
	}
}
