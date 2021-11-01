<?php
/**
 * tests-environment-checker.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Utils;

use EDD\Utils\EnvironmentChecker;

/**
 * @coversDefaultClass \EDD\Utils\EnvironmentChecker
 */
class EnvironmentCheckerTests extends \EDD_UnitTestCase {

	/**
	 * @var EnvironmentChecker
	 */
	protected $environmentChecker;

	/**
	 * Runs once before any tests are executed.
	 */
	public static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		// This is an admin file, so we need to include it manually.
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-pass-manager.php';
	}

	/**
	 * Runs once before each test.
	 */
	public function setUp() {
		$this->environmentChecker = new EnvironmentChecker();

		// Reset pass data so it can be set explicitly for each test.
		delete_option( 'edd_pass_licenses' );
		global $edd_licensed_products;
		$edd_licensed_products = null;
	}

	/**
	 * A 2.x wildcard should match version 2.11.3.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2x_wildcard_matches_version_2_11_3() {
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2.x' ) );
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2-x' ) );
	}

	/**
	 * A 2.11.x wildcard should match version 2.11.3.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2_11x_wildcard_matches_version_2_11_3() {
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2.11.x' ) );
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2-11-x' ) );
	}

	/**
	 * A 2.x wildcard should NOT match version 3.0.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2x_wildcard_doesnt_match_version_3() {
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2.x' ) );
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2-x' ) );
	}

	/**
	 * A 2.11.x wildcard should NOT match version 3.0.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2_11x_wildcard_doesnt_match_version_3() {
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2.11.x' ) );
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2-11-x' ) );
	}

	/**
	 * A 2.11.3 exact version should match version 2.11.3.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_exact_version_matches() {
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2.11.3' ) );
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2-11-3' ) );
	}

	public function test_site_with_no_licenses() {
		$this->assertTrue( $this->environmentChecker->meetsCondition( 'free' ) );

		$conditionsThatShouldFail = array(
			'ala-carte',
			'pass-personal',
			'pass-extended',
			'pass-professional',
			'pass-all-access',
			'pass-any',
		);

		foreach( $conditionsThatShouldFail as $condition ) {
			$this->assertFalse( $this->environmentChecker->meetsCondition( $condition ) );
		}
	}

}
