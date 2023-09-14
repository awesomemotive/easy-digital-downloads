<?php
/**
 * Promotional Notice Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Tests;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Promos\Notices\License_Upgrade_Notice;
use EDD\Admin\Promos\Notices\Notice;
use EDD\Tests\Helpers\Licenses as LicenseData;

class LicenseUpgradeNotice extends EDD_UnitTestCase {

	/**
	 * Runs once before any tests are executed.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// These are admin files, so we need to include them manually.
		require_once EDD_PLUGIN_DIR . 'includes/libraries/class-persistent-dismissible.php';
	}

	/**
	 * Runs once before each test.
	 *
	 * Deletes the pass licenses option so we can customize this per test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Always start with no option.
		delete_option( 'edd_pass_licenses' );
		add_filter( 'edd_is_pro', '__return_false' );
		update_option( 'edd_onboarding_completed', true, false );

		// Reset global variable.
		global $edd_licensed_products;
		$edd_licensed_products = array();

		// Set current user.
		global $current_user;
		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
		wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
	}

	public function tearDown(): void {
		parent::tearDown();
		add_filter( 'edd_is_pro', '__return_true' );
	}

	/**
	 * Asserts that a notice contains a string of text.
	 *
	 * @param string $contains
	 * @param Notice $notice
	 */
	private function assertNoticeContains( $contains, Notice $notice ) {
		ob_start();
		$notice->display();
		$notice_content = ob_get_clean();

		$this->assertTrue( false !== strpos( strtolower( $notice_content ), strtolower( $contains ) ) );
	}

	/**
	 * No license keys activated at all.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_notice_should_display_if_no_license_keys() {
		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'You are using the free version of Easy Digital Downloads', $notice );
	}

	/**
	 * We have a license key entered, but pass data hasn't been parsed yet.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 */
	public function test_notice_not_display_if_license_but_no_pass_data_yet() {
		// Simulate that we have a license key.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
		$this->assertFalse( $notice->should_display() );
	}

	/**
	 * Individual license key activated.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_individual_license_activated() {
		// We have pass data, but no passes.
		update_option( 'edd_pass_licenses', json_encode( array() ) );

		// Simulate that we have a license key though.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'For access to additional Easy Digital Downloads extensions to grow your store', $notice );
	}

	/**
	 * Personal Pass activated.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_personal_pass_license_activated() {
		// Set Personal Pass.
		update_option( 'edd_pass_licenses', json_encode( array(
			'license_key' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'time_checked' => time()
			)
		) ) );

		// Simulate that we have a license key.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'You are using Easy Digital Downloads with a Personal pass.', $notice );
	}

	/**
	 * Extended Pass activated.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_extended_pass_license_activated() {
		// Set Extended Pass.
		update_option( 'edd_pass_licenses', json_encode( array(
			'license_key' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::EXTENDED_PASS_ID,
				'time_checked' => time()
			)
		) ) );

		// Simulate that we have a license key.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'Grow your business and make more money with affiliate marketing.', $notice );
	}

	/**
	 * All Access Pass activated.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_all_access_pass_license_activated() {
		// Set Pass.
		update_option( 'edd_pass_licenses', json_encode( array(
			'license_key' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::ALL_ACCESS_PASS_ID,
				'time_checked' => time()
			)
		) ) );

		// Simulate that we have a license key.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'Grow your business and make more money with affiliate marketing.', $notice );
	}

	/**
	 * Lifetime All Access Pass activated.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_lifetime_all_access_pass_license_activated() {
		// Set Pass.
		update_option( 'edd_pass_licenses', json_encode( array(
			'license_key' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::ALL_ACCESS_PASS_LIFETIME_ID,
				'time_checked' => time()
			)
		) ) );

		// Simulate that we have a license key.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'Grow your business and make more money with affiliate marketing.', $notice );
	}

	/**
	 * Lifetime All Access Pass *and* Personal Pass activated.
	 * We should get the notice relevant to the All Access.
	 *
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_should_display
	 * @covers \EDD\Admin\Promos\Notices\License_Upgrade_Notice::_display
	 */
	public function test_aap_wins_over_personal_pass() {
		// Set Pass.
		update_option( 'edd_pass_licenses', json_encode( array(
			'license_key_1' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::ALL_ACCESS_PASS_LIFETIME_ID,
				'time_checked' => time()
			),
			'license_key_2' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'time_checked' => time()
			)
		) ) );

		// Simulate that we have 2 license keys.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key_1', 'license_key_2' );

		$notice = new License_Upgrade_Notice();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'Grow your business and make more money with affiliate marketing.', $notice );
	}

	/**
	 * Someone running the pro code without a pass license should always see a notice.
	 *
	 * @return void
	 */
	public function test_inactive_pro_sees_notice() {
		// skip this test if the inactive pro class doesn't exist
		if ( ! class_exists( '\\EDD\\Pro\\Admin\\Promos\\Notices\\InactivePro' ) ) {
			$this->markTestSkipped( 'Inactive Pro class does not exist.' );
		}
		add_filter( 'edd_is_pro', '__return_true' );

		$notice = new \EDD\Pro\Admin\Promos\Notices\InactivePro();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'You are using Easy Digital Downloads (Pro) without an active license key.', $notice );
	}

	public function test_inactive_pro_expired_license_sees_notice() {
		// skip this test if the inactive pro class doesn't exist
		if ( ! class_exists( '\\EDD\\Pro\\Admin\\Promos\\Notices\\InactivePro' ) ) {
			$this->markTestSkipped( 'Inactive Pro class does not exist.' );
		}
		add_filter( 'edd_is_pro', '__return_true' );

		LicenseData::get_pro_license(
			array(
				'license' => 'expired',
				'expires' => strtotime( '-1 day' ),
			)
		);
		$notice = new \EDD\Pro\Admin\Promos\Notices\InactivePro();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'Your license for Easy Digital Downloads (Pro) has expired.', $notice );
	}

	public function test_inactive_pro_expired_license_with_subscription_id_sees_notice() {
		// skip this test if the inactive pro class doesn't exist
		if ( ! class_exists( '\\EDD\\Pro\\Admin\\Promos\\Notices\\InactivePro' ) ) {
			$this->markTestSkipped( 'Inactive Pro class does not exist.' );
		}
		add_filter( 'edd_is_pro', '__return_true' );

		LicenseData::get_pro_license(
			array(
				'license'         => 'expired',
				'expires'         => strtotime( '-1 day' ),
				'subscription_id' => 1234,
			)
		);
		$notice = new \EDD\Pro\Admin\Promos\Notices\InactivePro();
		$this->assertTrue( $notice->should_display() );
		$this->assertNoticeContains( 'The last attempt to renew your subscription for Easy Digital Downloads (Pro) failed.', $notice );
	}
}
