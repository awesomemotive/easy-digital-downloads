<?php

namespace EDD\Tests\Stripe;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\Licenses as LicenseData;

class ApplicationFee extends EDD_UnitTestCase {

	public static function wpSetUpBeforeClass() {
		edd_update_option( 'stripe_connect_account_id', 'acct_1234567890' );
		edd_update_option( 'stripe_connect_account_country', 'us' );
	}

	public function tearDown(): void {
		parent::tearDown();
		LicenseData::delete_pro_license();
		LicenseData::delete_stripe_license();
		edd_stripe()->application_fee->reset_license();
	}

	public function test_edds_application_fee_is_3() {
		$this->assertStringContainsString( '3%', edd_stripe()->application_fee->get_fee_message() );
	}

	public function test_edds_application_fee_amount_is_30() {
		$this->assertEquals( 30, edd_stripe()->application_fee->get_application_fee_amount( 1000 ) );
	}

	public function test_stripe_account_not_connected_has_application_fee_is_false() {
		edd_update_option( 'stripe_connect_account_id', '' );

		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );

		edd_update_option( 'stripe_connect_account_id', 'acct_1234567890' );
	}

	public function test_stripe_account_country_india_has_application_fee_is_false() {
		edd_update_option( 'stripe_connect_account_country', 'in' );

		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );

		edd_update_option( 'stripe_connect_account_country', 'us' );
	}

	public function test_lifetime_pass_has_application_fee_is_false() {
		LicenseData::get_pro_license();

		$license = new \EDD\Gateways\Stripe\License();
		$this->assertFalse( $license->is_expiring_soon() );
		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );
		$this->assertEmpty( edd_stripe()->application_fee->get_fee_message() );
		$this->assertEquals( 'Valid License', edd_stripe()->application_fee->get_status() );
	}

	public function test_edd_is_pro_fee_message_says_upgrade() {
		add_filter( 'edd_is_pro', '__return_false' );
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertStringContainsString( 'Upgrade to Pro', edd_stripe()->application_fee->get_fee_message() );

		remove_filter( 'edd_is_pro', '__return_false' );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_personal_pass_has_application_fee_is_true() {
		$pro_license = LicenseData::get_pro_license(
			array(
				'pass_id' => 1245715,
			)
		);

		$this->assertTrue( edd_stripe()->application_fee->has_application_fee() );
	}

	public function test_stripe_license_has_application_fee_is_false() {
		LicenseData::get_stripe_license();

		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );
	}

	public function test_personal_pass_plus_stripe_license_has_application_fee_is_false() {
		LicenseData::get_pro_license(
			array(
				'pass_id' => 1245715,
			)
		);
		LicenseData::get_stripe_license();

		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );
	}

	public function test_missing_pass_new_install_grace_period_has_application_fee_is_false() {
		set_transient( 'edd_stripe_new_install', time() - DAY_IN_SECONDS );
		add_filter( 'wp_doing_ajax', '__return_true' );

		// We test that the license is actually in the new install grace period in addition to checking the application fee.
		$license = new \EDD\Gateways\Stripe\License();
		$this->assertTrue( $license->is_in_new_install_grace_period() );
		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );
		if ( edd_is_pro() ) {
			$this->assertStringContainsString( 'You are in a grace period', edd_stripe()->application_fee->get_fee_message() );
		}

		delete_transient( 'edd_stripe_new_install' );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_stripe_license_expired_yesterday_has_application_fee_is_false() {
		LicenseData::get_stripe_license(
			array(
				'license' => 'expired',
				'expires' => date( 'Y-m-d', strtotime( '-1 day' ) ),
			)
		);

		add_filter( 'wp_doing_ajax', '__return_true' );

		// We test that the license is actually showing as expired in addition to checking the application fee.
		$license = new \EDD\Gateways\Stripe\License();
		$this->assertTrue( $license->is_expired() );
		$this->assertFalse( $license->is_expiring_soon() );
		$this->assertFalse( edd_stripe()->application_fee->has_application_fee() );
		$this->assertStringContainsString( 'you are in a grace period.', edd_stripe()->application_fee->get_fee_message() );
		$this->assertEquals( 'Grace Period', edd_stripe()->application_fee->get_status() );

		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_stripe_license_expired_last_month_has_application_fee_is_true() {
		LicenseData::get_stripe_license(
			array(
				'license' => 'expired',
				'expires' => date( 'Y-m-d', strtotime( '-1 month' ) ),
			)
		);

		add_filter( 'wp_doing_ajax', '__return_true' );

		// We test that the license is actually showing as expired in addition to checking the application fee.
		$license = new \EDD\Gateways\Stripe\License();
		$this->assertTrue( $license->is_expired() );
		$this->assertTrue( edd_stripe()->application_fee->has_application_fee() );
		$this->assertStringContainsString( 'Your license expired', edd_stripe()->application_fee->get_fee_message() );

		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_stripe_license_expiring_next_week_is_expiring_soon_is_true() {
		LicenseData::get_stripe_license(
			array(
				'expires' => date( 'Y-m-d', strtotime( '+1 week' ) ),
			)
		);

		$license = new \EDD\Gateways\Stripe\License();
		$this->assertTrue( $license->is_expiring_soon() );
	}

	public function test_pro_license_disabled_has_application_fee_is_true() {
		$pro_license = LicenseData::get_pro_license(
			array(
				'license' => 'invalid',
				'error'   => 'disabled',
				'success' => false,
			)
		);

		add_filter( 'wp_doing_ajax', '__return_true' );

		$license = new \EDD\Gateways\Stripe\License();
		$this->assertFalse( $license->is_license_valid() );
		$this->assertTrue( edd_stripe()->application_fee->has_application_fee() );
		if ( edd_is_pro() ) {
			$this->assertStringContainsString( 'Activate or upgrade your license', edd_stripe()->application_fee->get_fee_message() );
		}
		$this->assertEquals( 'License Error: disabled', edd_stripe()->application_fee->get_status() );

		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_stripe_license_expired_last_month_default_sl_response_is_expired() {
		LicenseData::get_stripe_license(
			array(
				'success' => false,
				'license' => 'invalid',
				'error'   => 'expired',
				'expires' => date( 'Y-m-d', strtotime( '-1 month' ) ),
			)
		);

		// We test that the license is actually showing as expired in addition to checking the application fee.
		$license = new \EDD\Gateways\Stripe\License();
		$this->assertTrue( $license->is_expired() );
	}
}
