<?php


/**
 * @group edd_login_register
 */
class Tests_Login_Register extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->_user_id );
	}

	/**
     * Testthat the login form returns the expected string
     */
	public function test_login_form() {
		$this->assertContains( 'You are already logged in', edd_login_form() );
	}

	/**
     * Test that the edd_log_user_in() function successfully logs the user in
     */
	public function test_log_user_in() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		wp_logout();
		edd_log_user_in( 1 );
		$this->assertTrue( is_user_logged_in() );
		*/
	}
}
