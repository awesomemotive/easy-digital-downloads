<?php
/**
 * Tests Login/Register Functions
 */
class Test_Login_Register extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_login_form() {
		$this->assertEquals( '<p class="edd-logged-in">You are already logged in</p>', edd_login_form() );
	}
}