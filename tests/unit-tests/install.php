<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_activation
 */
class Tests_Activation extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_settings_general() {
		$this->assertArrayHasKey( 'purchase_page', get_option( 'edd_settings_general' ) );
		$this->assertArrayHasKey( 'success_page', get_option( 'edd_settings_general' ) );
		$this->assertArrayHasKey( 'failure_page', get_option( 'edd_settings_general' ) );
	}
}