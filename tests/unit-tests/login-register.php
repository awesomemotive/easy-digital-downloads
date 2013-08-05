<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_login_register
 */
class Tests_Login_Register extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_login_form() {
		$this->assertEquals( '<p class="edd-logged-in">You are already logged in</p>', edd_login_form() );
	}
}