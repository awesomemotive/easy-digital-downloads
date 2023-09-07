<?php

namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\Licenses as LicenseData;

class Licenses extends EDD_UnitTestCase {

	/**
	 * The pass handler class.
	 *
	 * @var \EDD\Admin\PassHandler\Handler
	 */
	private static $pass_handler;

	public static function wpSetUpBeforeClass() {
		self::$pass_handler = new \EDD\Admin\PassHandler\Handler();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		LicenseData::delete_pro_license();
	}

	public function test_no_pro_license() {
		if ( ! class_exists( '\\EDD\\Pro\\Core' ) ) {
			$this->markTestSkipped( 'EDD Pro is not available.' );
		}
		$license = self::$pass_handler->get_pro_license();

		$this->assertEmpty( $license->key );
		$this->assertTrue( edd_is_inactive_pro() );
	}

	public function test_get_pro_license() {
		$license_key = 'daksjfg98q3kjhJ3K4Q2354';
		update_site_option( 'edd_pro_license_key', $license_key );

		$license = self::$pass_handler->get_pro_license();

		$this->assertEquals( $license_key, $license->key );
	}

	public function test_get_pro_license_data() {
		$license = LicenseData::get_pro_license();

		$this->assertEquals( 7642331, $license->payment_id );
	}

	public function test_pro_license_inactive_pro_returns_false() {
		$license = LicenseData::get_pro_license();

		$this->assertFalse( edd_is_inactive_pro() );
	}

	public function test_get_stripe_license() {
		$license = LicenseData::get_stripe_license();

		$this->assertEquals( 167, $license->item_id );
		$this->assertEquals( 'bgvear89p7ty4qbrjkc4', $license->key );
	}

	public function test_pass_manager_highest_pass_id() {
		$license = LicenseData::get_pro_license();

		$pass_manager = new \EDD\Admin\Pass_Manager();
		$this->assertEquals( 1464807, $pass_manager->highest_pass_id );
	}

	public function test_pass_manager_invalid_pass_highest_pass_id() {
		$license = LicenseData::get_pro_license(
			array(
				'pass_id' => null,
				'item_id' => 161667,
			)
		);

		$pass_manager = new \EDD\Admin\Pass_Manager();
		$this->assertNull( $pass_manager->highest_pass_id );
	}
}
