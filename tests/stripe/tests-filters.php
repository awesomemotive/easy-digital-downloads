<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for filters.
 */
class Filters extends EDD_UnitTestCase {

	public function test_stripe_billing_fields_returns_default() {
		$this->assertEquals( 'full', edd_get_option( 'stripe_billing_fields', 'full' ) );
	}

	public function test_stripe_billing_fields_returns_value() {
		edd_update_option( 'stripe_billing_fields', 'none' );

		$this->assertEquals( 'none', edd_get_option( 'stripe_billing_fields', 'full' ) );
	}

	public function test_stripe_billing_fields_returns_zip_country_from_address_fields() {
		edd_update_option( 'checkout_address_fields', array( 'zip' => 1, 'country' => 1 ) );

		$this->assertEquals( 'zip_country', edd_get_option( 'stripe_billing_fields', 'full' ) );
	}
}
