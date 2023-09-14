<?php

namespace EDD\Tests\Stripe\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\Licenses as LicenseData;
use EDD\Gateways\Stripe\Admin\LicenseManager as Manager;

class LicenseManager extends EDD_UnitTestCase {

	/**
	 * The original user.
	 *
	 * @var \WP_User
	 */
	private static $original_user;

	public static function wpSetUpBeforeClass() {
		require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';
		require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices.php';
		require_once EDDS_PLUGIN_DIR . '/includes/admin/notices.php';

		edd_update_option( 'stripe_connect_account_id', 'acct_1234567890' );
		edd_update_option( 'stripe_connect_account_country', 'us' );
	}

	public function setUp(): void {
		parent::setUp();
		add_filter( 'edd_is_gateway_active', '__return_true' );
		add_filter( 'edd_is_pro', '__return_false' );

		global $current_user;
		self::$original_user = $current_user;
		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
		$current_user->add_cap( 'manage_shop_settings' );
	}

	public function tearDown(): void {
		parent::tearDown();
		LicenseData::delete_pro_license();
		LicenseData::delete_stripe_license();
		edd_stripe()->application_fee->reset_license();
		remove_filter( 'edd_is_gateway_active', '__return_true' );
		remove_filter( 'edd_is_pro', '__return_false' );
		global $current_user;
		$current_user = self::$original_user;
	}

	public function test_admin_notice_valid_license_empty() {
		LicenseData::get_pro_license();

		$this->assertEmpty( $this->get_notice() );
	}

	public function test_admin_notice_expired_license_edd_is_pro_is_empty() {
		remove_filter( 'edd_is_pro', '__return_false' );
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD is not Pro' );
		}
		LicenseData::get_stripe_license(
			array(
				'license' => 'expired',
				'expires' => date( 'Y-m-d', strtotime( '-1 month' ) ),
			)
		);

		$this->assertEmpty( $this->get_notice() );
	}

	public function test_admin_notice_expired_license() {
		LicenseData::get_stripe_license(
			array(
				'license' => 'expired',
				'expires' => date( 'Y-m-d', strtotime( '-1 month' ) ),
			)
		);

		$this->assertStringContainsString( 'You are now paying additional fees with every Stripe transaction.', $this->get_notice() );
	}

	public function test_admin_notice_grace_period_license() {
		LicenseData::get_stripe_license(
			array(
				'license' => 'expired',
				'expires' => date( 'Y-m-d', strtotime( '-1 day' ) ),
			)
		);

		$this->assertStringContainsString( 'Renew your license before', $this->get_notice() );
	}

	private function get_notice() {
		$license_manager = new Manager();
		ob_start();
		$license_manager->register_admin_notices();

		return ob_get_clean();
	}
}
