<?php
/**
 * Pass Manager Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Tests;

/**
 * Class Pass_Manager
 *
 * @package EDD\Tests
 * @coversDefaultClass \EDD\Admin\Pass_Manager
 */
class Pass_Manager extends \EDD_UnitTestCase {

	/**
	 * Runs once before any tests are executed.
	 */
	public static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		// This is an admin file, so we need to include it manually.
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-pass-manager.php';
	}

	/**
	 * Runs before each test is executed.
	 */
	public function setUp() {
		parent::setUp();

		delete_option( 'edd_pass_licenses' );
	}

	/**
	 * @covers \EDD\Admin\Pass_Manager::has_pass
	 */
	public function test_db_with_no_passes_has_no_pass() {
		$manager = new \EDD\Admin\Pass_Manager();

		$this->assertFalse( $manager->has_pass() );
	}

	/**
	 * @covers \EDD\Admin\Pass_Manager::pass_compare
	 */
	public function test_all_access_is_higher_than_personal() {
		$this->assertTrue(
			\EDD\Admin\Pass_Manager::pass_compare(
				\EDD\Admin\Pass_Manager::ALL_ACCESS_PASS_ID,
				\EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'>'
			)
		);
	}

	/**
	 * @covers \EDD\Admin\Pass_Manager::pass_compare
	 */
	public function test_personal_pass_equals() {
		$this->assertTrue(
			\EDD\Admin\Pass_Manager::pass_compare(
				1245715,
				\EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'='
			)
		);
	}

	/**
	 * If you have both a Personal and Professional pass activated, the Professional should be highest.
	 *
	 * @covers \EDD\Admin\Pass_Manager::get_highest_pass_id
	 */
	public function test_professional_is_highest_pass() {
		$passes = array(
			'license_1' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'time_checked' => time()
			),
			'license_2' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PROFESSIONAL_PASS_ID,
				'time_checked' => time()
			),
		);

		update_option( 'edd_pass_licenses', json_encode( $passes ) );

		$manager = new \EDD\Admin\Pass_Manager();
		$this->assertSame( \EDD\Admin\Pass_Manager::PROFESSIONAL_PASS_ID, $manager->highest_pass_id );
	}

	/**
	 * If you have a pass entered, but it was last verified more than 2 months ago (1 year ago
	 * in this case), then it should not be accepted as a valid pass.
	 *
	 * @covers \EDD\Admin\Pass_Manager::get_highest_pass_id
	 */
	public function test_no_pass_id_if_pass_outside_check_window() {
		$passes = array(
			'license_1' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'time_checked' => strtotime( '-1 year' )
			)
		);

		update_option( 'edd_pass_licenses', json_encode( $passes ) );
		$manager = new \EDD\Admin\Pass_Manager();

		$this->assertFalse( $manager->has_pass() );
	}

}
