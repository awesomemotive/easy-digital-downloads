<?php

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Checkout tests.
 */
class Fields extends EDD_UnitTestCase {

	public function tearDown(): void {
		edd_delete_option( 'enable_taxes' );
		edd_delete_option( 'checkout_address_fields' );
	}

	public function test_edd_purchase_form_required_fields_no_taxes() {
		edd_update_option( 'enable_taxes', 0 );
		$required_fields = edd_purchase_form_required_fields();

		$this->assertArrayHasKey( 'edd_email', $required_fields );
		$this->assertArrayHasKey( 'edd_first', $required_fields );
	}

	public function test_edd_purchase_form_required_fields_taxes() {
		edd_update_option( 'enable_taxes', 1 );
		edd_update_option( 'stripe_billing_fields', 'full' );
		// Required for testing as there is no cart total.
		add_filter( 'edd_require_billing_address', '__return_true' );
		$required_fields = edd_purchase_form_required_fields();

		$this->assertArrayHasKey( 'edd_email', $required_fields );
		$this->assertArrayHasKey( 'edd_first', $required_fields );
		$this->assertArrayHasKey( 'card_zip', $required_fields );
		$this->assertArrayHasKey( 'billing_country', $required_fields );
	}

	public function test_edd_purchase_form_required_fields_taxes_country() {
		edd_update_option( 'checkout_address_fields', array( 'country' => 1, 'state' => 1 ) );
		edd_update_option( 'enable_taxes', 1 );
		// Required for testing as there is no cart total.
		add_filter( 'edd_require_billing_address', '__return_true' );
		$required_fields = edd_purchase_form_required_fields();

		$this->assertArrayHasKey( 'billing_country', $required_fields );
		$this->assertArrayHasKey( 'card_state', $required_fields );
	}
}
