<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for regional support.
 */
class RegionalSupport extends EDD_UnitTestCase {

	public function setup(): void {
		parent::setUp();

		edd_stripe()->has_regional_support = null;
		edd_stripe()->regional_support     = null;
	}

	public function test_us_base_country_regional_support_is_false() {
		edd_update_option( 'base_country', 'US' );

		$this->assertFalse( edd_stripe()->has_regional_support() );
	}

	public function test_us_connect_country_regional_support_is_false() {
		edd_update_option( 'stripe_connect_account_country', 'us' );

		$this->assertFalse( edd_stripe()->has_regional_support() );
	}

	public function test_in_base_country_regional_support_is_true() {
		edd_update_option( 'base_country', 'IN' );

		$this->assertTrue( edd_stripe()->has_regional_support() );
		$this->assertTrue( edd_stripe()->regional_support->requires_card_name );
	}

	public function test_in_connect_country_regional_support_is_true() {
		edd_update_option( 'base_country', 'US' );
		edd_update_option( 'stripe_connect_account_country', 'in' );

		$this->assertTrue( edd_stripe()->has_regional_support() );
		$this->assertTrue( edd_stripe()->regional_support->requires_card_name );
	}
}
