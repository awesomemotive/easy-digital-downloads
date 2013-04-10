<?php

/**
 * Test Activation Hook
 */
class Test_Activation extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_settings_general() {
		$expected = array(
			'purchase_page' => 3,
			'success_page' => 4,
			'failure_page' => 5
		);

		$this->assertEquals( $expected, get_option( 'edd_settings_general' ) );
	}

	public function test_edd_version() {
		$this->assertEquals( EDD_VERSION, get_option( 'edd_version' ) );
	}
}