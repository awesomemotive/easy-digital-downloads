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
}
