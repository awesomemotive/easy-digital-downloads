<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_upgrades
 */
class Tests_Upgrades extends EDD_UnitTestCase {

	public function setup(): void {
		parent::setUp();
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_upgrade_completion() {

		$current_upgrades = edd_get_completed_upgrades();
		// Since we mark previous upgrades as complete upon install
		$this->assertTrue( ! empty( $current_upgrades ) );
		$this->assertIsArray( $current_upgrades );

		$this->assertTrue( edd_set_upgrade_complete( 'test-upgrade-action' ) );
		$this->assertTrue( edd_has_upgrade_completed( 'test-upgrade-action' ) );
		$this->assertFalse( edd_has_upgrade_completed( 'test-upgrade-action-false' ) );

	}

}
