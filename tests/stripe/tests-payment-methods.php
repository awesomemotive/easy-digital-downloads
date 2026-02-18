<?php
/**
 * Tests for Stripe PaymentMethods class.
 *
 * @package     EDD\Tests\Stripe
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Stripe\PaymentMethods;

/**
 * Tests for the PaymentMethods class.
 *
 * @group edd_stripe
 * @group edd_stripe_payment_methods
 *
 * @coversDefaultClass \EDD\Gateways\Stripe\PaymentMethods
 */
class PaymentMethods_Tests extends EDD_UnitTestCase {

	/**
	 * Test that Sofort is not in the registered payment methods.
	 *
	 * Sofort has been deprecated by Stripe and replaced with Klarna.
	 *
	 * @see https://docs.stripe.com/payments/sofort/replace
	 * @covers ::list
	 */
	public function test_sofort_not_in_payment_methods_list() {
		$methods = PaymentMethods::list();

		$this->assertArrayNotHasKey( 'sofort', $methods );
	}

	/**
	 * Test that Sofort class does not exist.
	 *
	 * The Sofort.php file should have been deleted as part of the removal.
	 *
	 * @see https://docs.stripe.com/payments/sofort/replace
	 */
	public function test_sofort_class_does_not_exist() {
		$this->assertFalse(
			class_exists( '\EDD\Gateways\Stripe\PaymentMethods\Sofort', false ),
			'Sofort class should not exist after removal'
		);
	}

	/**
	 * Test that get_payment_method returns false for sofort.
	 *
	 * @covers ::get_payment_method
	 */
	public function test_get_payment_method_returns_false_for_sofort() {
		$payment_method = PaymentMethods::get_payment_method( 'sofort' );

		$this->assertFalse( $payment_method );
	}

	/**
	 * Test that get_label returns legacy label for sofort.
	 *
	 * Sofort may still exist in historical order data, so we return a legacy
	 * label to properly display the payment method to users.
	 *
	 * @covers ::get_label
	 */
	public function test_get_label_returns_legacy_label_for_sofort() {
		$label = PaymentMethods::get_label( 'sofort' );

		$this->assertSame( 'Sofort (Legacy)', $label );
	}

	/**
	 * Test that Klarna is in the payment methods list.
	 *
	 * Klarna replaces Sofort per Stripe's documentation.
	 *
	 * @see https://docs.stripe.com/payments/sofort/replace
	 * @covers ::list
	 */
	public function test_klarna_is_in_payment_methods_list() {
		$methods = PaymentMethods::list();

		$this->assertArrayHasKey( 'klarna', $methods );
	}

	/**
	 * Test that get_payment_method returns class for klarna.
	 *
	 * @covers ::get_payment_method
	 */
	public function test_get_payment_method_returns_class_for_klarna() {
		$payment_method = PaymentMethods::get_payment_method( 'klarna' );

		$this->assertSame(
			'EDD\Gateways\Stripe\PaymentMethods\Klarna',
			$payment_method
		);
	}

	/**
	 * Test that payment methods list returns expected methods.
	 *
	 * @covers ::list
	 */
	public function test_list_returns_array_of_payment_methods() {
		$methods = PaymentMethods::list();

		$this->assertIsArray( $methods );
		$this->assertNotEmpty( $methods );

		// Verify some expected methods are present.
		$expected_methods = array(
			'card',
			'link',
			'klarna',
			'ideal',
			'bancontact',
		);

		foreach ( $expected_methods as $method ) {
			$this->assertArrayHasKey(
				$method,
				$methods,
				"Expected payment method '{$method}' not found in list"
			);
		}
	}

	/**
	 * Test that each registered payment method has a corresponding class.
	 *
	 * @covers ::list
	 * @covers ::get_payment_method
	 */
	public function test_all_registered_methods_have_classes() {
		$methods = PaymentMethods::list();

		foreach ( $methods as $method => $label ) {
			$class = PaymentMethods::get_payment_method( $method );
			$this->assertNotFalse(
				$class,
				"Payment method '{$method}' does not have a corresponding class"
			);
			$this->assertTrue(
				class_exists( $class ),
				"Class '{$class}' for payment method '{$method}' does not exist"
			);
		}
	}

	/**
	 * Test that each registered payment method returns a label.
	 *
	 * @covers ::list
	 * @covers ::get_label
	 */
	public function test_all_registered_methods_have_labels() {
		$methods = PaymentMethods::list();

		foreach ( $methods as $method => $label ) {
			$this->assertNotEmpty(
				$label,
				"Payment method '{$method}' has an empty label"
			);
			$this->assertIsString(
				$label,
				"Payment method '{$method}' label is not a string"
			);
		}
	}

	/**
	 * Test that get_payment_method correctly converts method names to class names.
	 *
	 * @covers ::get_payment_method
	 * @dataProvider payment_method_class_name_provider
	 *
	 * @param string $method_id     The method identifier.
	 * @param string $expected_class The expected class name.
	 */
	public function test_get_payment_method_converts_names_correctly( $method_id, $expected_class ) {
		$result = PaymentMethods::get_payment_method( $method_id );

		// Pass false to class_exists to prevent autoloading non-existent classes.
		if ( class_exists( $expected_class, false ) ) {
			$this->assertSame( $expected_class, $result );
		} else {
			$this->assertFalse( $result );
		}
	}

	/**
	 * Data provider for payment method class name conversion tests.
	 *
	 * @return array
	 */
	public function payment_method_class_name_provider() {
		return array(
			'simple name'       => array( 'card', 'EDD\Gateways\Stripe\PaymentMethods\Card' ),
			'underscore name'   => array( 'us_bank_account', 'EDD\Gateways\Stripe\PaymentMethods\UsBankAccount' ),
			'two word name'     => array( 'sepa_debit', 'EDD\Gateways\Stripe\PaymentMethods\SepaDebit' ),
			'nonexistent'       => array( 'nonexistent', 'EDD\Gateways\Stripe\PaymentMethods\Nonexistent' ),
			'removed sofort'    => array( 'sofort', 'EDD\Gateways\Stripe\PaymentMethods\Sofort' ),
		);
	}

	/**
	 * Test that get_payment_method returns false for invalid methods.
	 *
	 * @covers ::get_payment_method
	 */
	public function test_get_payment_method_returns_false_for_invalid_method() {
		$this->assertFalse( PaymentMethods::get_payment_method( 'invalid_method' ) );
		$this->assertFalse( PaymentMethods::get_payment_method( '' ) );
		$this->assertFalse( PaymentMethods::get_payment_method( '123' ) );
	}

	/**
	 * Test that get_label returns legacy labels for deprecated payment methods.
	 *
	 * Historical order data may contain deprecated payment method types that
	 * no longer have associated classes. These should still display a
	 * meaningful label to users.
	 *
	 * @covers ::get_label
	 */
	public function test_get_label_returns_legacy_labels_for_deprecated_methods() {
		// Sofort is a legacy method - should return legacy label.
		$sofort_label = PaymentMethods::get_label( 'sofort' );
		$this->assertSame( 'Sofort (Legacy)', $sofort_label );

		// Non-existent/invalid methods should still return empty string.
		$invalid_label = PaymentMethods::get_label( 'totally_fake_method' );
		$this->assertEmpty( $invalid_label );
	}

	/**
	 * Test that get_legacy_methods returns array of deprecated payment methods.
	 *
	 * @covers ::get_legacy_methods
	 */
	public function test_get_legacy_methods_returns_array() {
		$legacy_methods = PaymentMethods::get_legacy_methods();

		$this->assertIsArray( $legacy_methods );
	}

	/**
	 * Test that get_legacy_methods contains Sofort.
	 *
	 * Sofort was deprecated by Stripe and replaced with Klarna.
	 *
	 * @see https://docs.stripe.com/payments/sofort/replace
	 * @covers ::get_legacy_methods
	 */
	public function test_get_legacy_methods_contains_sofort() {
		$legacy_methods = PaymentMethods::get_legacy_methods();

		$this->assertArrayHasKey( 'sofort', $legacy_methods );
		$this->assertSame( 'Sofort (Legacy)', $legacy_methods['sofort'] );
	}

	/**
	 * Test that legacy methods are not in the active payment methods list.
	 *
	 * Legacy methods should only be available for historical data display,
	 * not for new payment processing.
	 *
	 * @covers ::list
	 * @covers ::get_legacy_methods
	 */
	public function test_legacy_methods_not_in_active_list() {
		$active_methods = PaymentMethods::list();
		$legacy_methods = PaymentMethods::get_legacy_methods();

		foreach ( array_keys( $legacy_methods ) as $legacy_method ) {
			$this->assertArrayNotHasKey(
				$legacy_method,
				$active_methods,
				"Legacy method '{$legacy_method}' should not be in active methods list"
			);
		}
	}
}
