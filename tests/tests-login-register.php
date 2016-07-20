<?php


/**
 * @group edd_login_register
 */
class Tests_Login_Register extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		wp_set_current_user(0);
	}

	/**
	 * Test that the login form returns the expected string.
	 */
	public function test_login_form() {
		$this->assertContains( '<legend>Log into Your Account</legend>', edd_login_form() );
	}

	/**
	 * Test that the registration form return the expected output.
	 */
	public function test_register_form() {
		$this->assertContains( '<legend>Register New Account</legend>', edd_register_form() );
	}

	/**
	 * Test that there is displayed a error when the username is incorrect.
	 *
	 * @since 2.2.3
	 */
	public function test_process_login_form_incorrect_username() {

		edd_process_login_form( array(
			'edd_login_nonce' => wp_create_nonce( 'edd-login-nonce' ),
			'edd_user_login'  => 'wrong_username',
		) );

		$this->assertArrayHasKey( 'username_incorrect', edd_get_errors() );
		$this->assertContains( 'The username you entered does not exist', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();

	}

	/**
	 * Test that there is displayed a error when the wrong password is entered.
	 *
	 * @since 2.2.3
	 */
	public function test_process_login_form_correct_username_invalid_pass() {

		edd_process_login_form( array(
			'edd_login_nonce' => wp_create_nonce( 'edd-login-nonce' ),
			'edd_user_login'  => 'admin@example.org',
			'edd_user_pass'   => 'falsepass',
		) );

		$this->assertArrayHasKey( 'password_incorrect', edd_get_errors() );
		$this->assertContains( 'The password you entered is incorrect', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();

	}

	/**
	 * Test correct login.
	 *
	 * @since 2.2.3
	 */
	public function test_process_login_form_correct_login() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		ob_start();
			edd_process_login_form( array(
				'edd_login_nonce' 	=> wp_create_nonce( 'edd-login-nonce' ),
				'edd_user_login' 	=> 'admin@example.org',
				'edd_user_pass' 	=> 'password',
			) );
			$return = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( edd_get_errors() );
		*/
	}

	/**
	 * Test that the edd_log_user_in() function successfully logs the user in.
	 *
	 * @since 2.2.3
	 */
	public function test_log_user_in_return() {
		$this->assertNull( edd_log_user_in( 0, '', '' ) );
	}

	/**
	 * Test that the edd_log_user_in() function successfully logs the user in.
	 *
	 * @since 2.2.3
	 */
	public function test_log_user_in() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		wp_logout();
		edd_log_user_in( 1 );
		$this->assertTrue( is_user_logged_in() );
		*/
	}

	/**
	 * Test that the function returns when the user is already logged in.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_logged_in() {

		global $current_user;
		$origin_user  = $current_user;
		$current_user = wp_set_current_user( 1 );

		$_POST['edd_register_submit'] = '';
		$this->assertNull( edd_process_register_form( array() ) );

		// Reset to origin
		$current_user = $origin_user;

	}

	/**
	 * Test that the function returns when the submit is empty.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_return_submit() {

		$_POST['edd_register_submit'] = '';
		$this->assertNull( edd_process_register_form( array(
			'edd_register_submit' => '',
		) ) );

	}

	/**
	 * Test that 'empty' errors are displayed when certain fields are empty.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_empty_fields() {

		$_POST['edd_register_submit'] = 1;
		$_POST['edd_user_pass']       = '';
		$_POST['edd_user_pass2']      = '';

		edd_process_register_form( array(
			'edd_register_submit' => 1,
			'edd_user_login'      => '',
			'edd_user_email'      => '',
		) );

		$errors = edd_get_errors();
		$this->assertArrayHasKey( 'empty_username', $errors );
		$this->assertArrayHasKey( 'email_invalid', $errors );
		$this->assertArrayHasKey( 'empty_password', $errors );

		// Clear errors for other test
		edd_clear_errors();

	}

	/**
	 * Test that a error is displayed when the username already exists.
	 * Also tests the password mismatch.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_username_exists() {

		$_POST['edd_register_submit'] = 1;
		$_POST['edd_user_pass']       = 'password';
		$_POST['edd_user_pass2']      = 'other-password';

		edd_process_register_form( array(
			'edd_register_submit' => 1,
			'edd_user_login'      => 'admin',
			'edd_user_email'      => null,
		) );
		$this->assertArrayHasKey( 'username_unavailable', edd_get_errors() );
		$this->assertArrayHasKey( 'password_mismatch', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	/**
	 * Test that a error is displayed when the username is invalid.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_username_invalid() {

		$_POST['edd_register_submit'] 	= 1;
		$_POST['edd_user_pass'] 		= 'password';
		$_POST['edd_user_pass2'] 		= 'other-password';
		edd_process_register_form( array(
			'edd_register_submit' 	=> 1,
			'edd_user_login' 		=> 'admin#!@*&',
			'edd_user_email' 		=> null,
		) );
		$this->assertArrayHasKey( 'username_invalid', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	/**
	 * Test that a error is displayed when the email is already taken.
	 * Test that a error is displayed when the payment email is incorrect.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_payment_email_incorrect() {

		$_POST['edd_register_submit'] 	= 1;
		$_POST['edd_user_pass'] 		= '';
		$_POST['edd_user_pass2'] 		= '';
		edd_process_register_form( array(
			'edd_register_submit' 	=> 1,
			'edd_user_login' 		=> 'random_username',
			'edd_user_email' 		=> 'admin@example.org',
			'edd_payment_email' 	=> 'someotheradminexample.org',
		) );
		$this->assertArrayHasKey( 'email_unavailable', edd_get_errors() );
		$this->assertArrayHasKey( 'payment_email_invalid', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	/**
	 * Test that the registration success.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_success() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		$_POST['edd_register_submit'] 	= 1;
		$_POST['edd_user_pass'] 		= 'password';
		$_POST['edd_user_pass2'] 		= 'password';
		edd_process_register_form( array(
			'edd_register_submit' 	=> 1,
			'edd_user_login' 		=> 'random_username',
			'edd_user_email' 		=> 'random_username@example.org',
			'edd_payment_email' 	=> 'random_username@example.org',
			'edd_user_pass' 		=> 'password',
			'edd_redirect' 			=> '/',
		) );

		// Clear errors for other test
		edd_clear_errors();
		*/
	}

}
