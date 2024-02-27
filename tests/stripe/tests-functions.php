<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;

/**
 * Tests for functions.
 */
class Functions extends EDD_UnitTestCase {

	/**
	 * @covers edds_string_to_bool
	 */
	public function test_string_to_bool() {
		$this->assertTrue( edds_truthy_to_bool( 1 ) );
		$this->assertTrue( edds_truthy_to_bool( 'yes' ) );
		$this->assertTrue( edds_truthy_to_bool( 'Yes' ) );
		$this->assertTrue( edds_truthy_to_bool( 'YES' ) );
		$this->assertTrue( edds_truthy_to_bool( 'true' ) );
		$this->assertTrue( edds_truthy_to_bool( 'True' ) );
		$this->assertTrue( edds_truthy_to_bool( 'TRUE' ) );
		$this->assertFalse( edds_truthy_to_bool( 0 ) );
		$this->assertFalse( edds_truthy_to_bool( 'no' ) );
		$this->assertFalse( edds_truthy_to_bool( 'No' ) );
		$this->assertFalse( edds_truthy_to_bool( 'NO' ) );
		$this->assertFalse( edds_truthy_to_bool( 'false' ) );
		$this->assertFalse( edds_truthy_to_bool( 'False' ) );
		$this->assertFalse( edds_truthy_to_bool( 'FALSE' ) );
	}

	/**
	 * @covers edds_stripe_connect_account_country_supports_application_fees
	 */
	public function test_edds_stripe_connect_account_country_supports_application_fees() {
		edd_update_option( 'stripe_connect_account_country', 'us' );

		$this->assertTrue(
			edds_stripe_connect_account_country_supports_application_fees()
		);
	}

	/**
	 * @covers edds_stripe_connect_account_country_supports_application_fees
	 */
	public function test_edds_stripe_connect_blank_account_country_supports_application_fees() {
		$this->assertTrue(
			edds_stripe_connect_account_country_supports_application_fees()
		);
	}

	/**
	 * @covers edds_stripe_connect_account_country_supports_application_fees
	 */
	public function test_edds_stripe_connect_account_country_does_not_support_application_fees() {
		edd_update_option( 'stripe_connect_account_country', 'br' );

		$this->assertFalse(
			edds_stripe_connect_account_country_supports_application_fees()
		);
	}

	/**
	 * @covers edds_is_zero_decimal_currency
	 */
	public function test_edds_is_zero_decimal_currency() {
		edd_update_option( 'currency', 'JPY' );
		$this->assertTrue( edds_is_zero_decimal_currency() );
	}

	/**
	 * @covers edds_is_zero_decimal_currency
	 */
	public function test_edds_is_non_zero_decimal_currency() {
		edd_update_option( 'currency', 'USD' );
		$this->assertFalse( edds_is_zero_decimal_currency() );
	}
}
