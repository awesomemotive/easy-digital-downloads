<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_login_register
 */
class Tests_Login_Register extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	/**
     * Testthat the login form returns the expected string
     */
	public function test_login_form() {
		$this->assertEquals( '<p class="edd-logged-in">You are already logged in</p>', edd_login_form() );
	}

	/**
     * Test that each of the actions are added and each hooked in with the right priority
     */
	public function test_login_actions() {
		global $wp_filter;
		$this->assertarrayHasKey( 'edd_process_login_form', $wp_filter['edd_user_login'][10] );
	}

	/**
     * Test that the edd_log_user_in() function successfully logs the user in
     */
	public function test_log_user_in() {
		wp_logout();
		edd_log_user_in( 1 );
		$this->assertTrue( is_user_logged_in() );
	}
}