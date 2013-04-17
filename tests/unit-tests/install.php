<?php

/**
 * Test Activation Hook
 */
class Test_Activation extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_settings_general() {
		$this->assertArrayHasKey( 'purchase_page', get_option( 'edd_settings_general' ) );
		$this->assertArrayHasKey( 'success_page', get_option( 'edd_settings_general' ) );
		$this->assertArrayHasKey( 'failure_page', get_option( 'edd_settings_general' ) );
	}

	public function test_transient() {
		$transient = get_transient( '_edd_activation_redirect' );

		$this->assertEquals( 1, $transient );
	}
}