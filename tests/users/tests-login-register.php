<?php
namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_login_register
 */
class LoginRegister extends EDD_UnitTestCase {

	public function setup(): void {
		parent::setUp();
		wp_set_current_user( 0 );
	}

	/**
	 * Test that the login form returns the expected string.
	 */
	public function test_login_form() {
		$this->assertStringContainsString( '<legend>Log into Your Account</legend>', edd_login_form() );
	}

	/**
	 * Test that the registration form return the expected output.
	 */
	public function test_register_form() {
		$this->assertStringContainsString( '<legend>Register New Account</legend>', edd_register_form() );
	}

	/**
	 * Test that there is displayed a error when the username is incorrect.
	 *
	 * @since 2.2.3
	 */
	public function test_process_login_form_incorrect_username() {

		edd_process_login_form(
			array(
				'edd_login_nonce' => wp_create_nonce( 'edd-login-nonce' ),
				'edd_user_login'  => 'wrong_username',
			)
		);

		$errors = edd_get_errors();
		$this->assertArrayHasKey( 'edd_invalid_login', $errors );
		$this->assertStringContainsString( 'Invalid username or password', $errors['edd_invalid_login'] );

		// Clear errors for other test
		edd_clear_errors();

	}

	/**
	 * Test that there is displayed a error when the wrong password is entered.
	 *
	 * @since 2.2.3
	 */
	public function test_process_login_form_correct_username_invalid_pass() {
		edd_process_login_form(
			array(
				'edd_login_nonce' => wp_create_nonce( 'edd-login-nonce' ),
				'edd_user_login'  => 'admin@example.org',
				'edd_user_pass'   => 'falsepass',
			)
		);

		$errors = edd_get_errors();
		$this->assertArrayHasKey( 'edd_invalid_login', $errors );
		$this->assertStringContainsString( 'Invalid username or password', $errors['edd_invalid_login'] );

		// Clear errors for other test
		edd_clear_errors();
	}

	/**
	 * Test correct login.
	 *
	 * @since 2.2.3
	 */
	public function test_process_login_form_correct_login() {
		try {
			edd_process_login_form(
				array(
					'edd_login_nonce' => wp_create_nonce( 'edd-login-nonce' ),
					'edd_user_login'  => 'admin@example.org',
					'edd_user_pass'   => 'password',
					'edd_redirect'    => '',
				)
			);
		} catch ( \WPDieException $e ) {

		}

		$this->assertEmpty( edd_get_errors() );
	}

	/**
	 * Test that the edd_log_user_in() function successfully logs the user in.
	 *
	 * @since 2.2.3
	 */
	public function test_log_user_in_return() {
		$this->assertTrue( edd_log_user_in( 0, '', '' ) instanceof \WP_Error );
	}

	/**
	 * Test that the edd_log_user_in() function successfully logs the user in.
	 *
	 * @since 2.2.3
	 */
	public function test_log_user_in() {
		wp_logout();
		edd_log_user_in( 1, 'admin', 'password' );
		$this->assertTrue( is_user_logged_in() );
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

		$this->assertNull(
			edd_process_register_form(
				array(
					'edd_register_submit' => '',
				)
			)
		);

		// Reset to origin
		$current_user = $origin_user;

	}

	/**
	 * Test that the function returns when the submit is empty.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_return_submit() {

		$this->assertNull(
			edd_process_register_form(
				array(
					'edd_register_submit' => '',
				)
			)
		);
	}

	/**
	 * Test that 'empty' errors are displayed when certain fields are empty.
	 *
	 * @since 2.2.3
	 */
	public function test_process_register_form_empty_fields() {

		edd_process_register_form(
			array(
				'edd_register_submit' => 1,
				'edd_user_login'      => '',
				'edd_user_email'      => '',
				'edd_user_pass'       => '',
				'edd_user_pass2'      => '',
			)
		);

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

		edd_process_register_form(
			array(
				'edd_register_submit' => 1,
				'edd_user_login'      => 'admin',
				'edd_user_email'      => null,
				'edd_user_pass'       => 'password',
				'edd_user_pass2'      => 'other-password',
			)
		);
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

		edd_process_register_form(
			array(
				'edd_register_submit' => 1,
				'edd_user_login'      => 'admin#!@*&',
				'edd_user_email'      => null,
				'edd_user_pass'       => 'password',
				'edd_user_pass2'      => 'other-password',
			)
		);
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

		edd_process_register_form(
			array(
				'edd_register_submit' => 1,
				'edd_user_login'      => 'random_username',
				'edd_user_email'      => 'admin@example.org',
				'edd_payment_email'   => 'someotheradminexample.org',
				'edd_user_pass'       => '',
				'edd_user_pass2'      => '',
			)
		);
		$this->assertArrayHasKey( 'email_unavailable', edd_get_errors() );
		$this->assertArrayHasKey( 'payment_email_invalid', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	/**
	 * @covers edd_process_register_form
	 */
	public function test_process_register_form_pass2_missing() {

		edd_process_register_form(
			array(
				'edd_register_submit' => 1,
				'edd_user_login'      => 'sample_user',
				'edd_user_email'      => 'sample@edd.local',
				'edd_user_pass'       => 'password',
			)
		);
		$this->assertArrayHasKey( 'password_mismatch', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	/**
	 * @covers: edd_get_file_download_login_redirect
	 */
	public function test_get_file_download_login_redirect_no_login_redirect_page() {
		$this->assertTrue( 0 === strpos( edd_get_file_download_login_redirect( array( 'foo' => 'bar' ) ), home_url() ) );
	}

	/**
	 * @covers: edd_get_file_download_login_redirect
	 */
	public function test_get_file_download_login_redirect_page() {

		$login_redirect_page_id = wp_insert_post(
			array(
				'post_title'  => 'Login Redirect Page',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		edd_update_option( 'login_redirect_page', $login_redirect_page_id );
		$this->assertTrue( 0 === strpos( edd_get_file_download_login_redirect( array( 'foo' => 'bar' ) ), get_permalink( $login_redirect_page_id ) ) );
		edd_delete_option( 'login_redirect_page' );
	}

	public function test_login_uri_with_block_returns_uri() {
		$login_page_id = wp_insert_post(
			array(
				'post_title'   => 'Login Page',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:edd/login /-->',
			)
		);
		edd_update_option( 'login_page', $login_page_id );

		$this->assertStringContainsString( get_permalink( $login_page_id ), edd_get_login_page_uri() );

		edd_delete_option( 'login_page' );
		wp_delete_post( $login_page_id );
	}

	public function test_login_uri_without_block_returns_false() {
		$login_page_id = wp_insert_post(
			array(
				'post_title'   => 'Login Page',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:shortcode -->[edd_login]<!-- /wp:shortcode -->',
			)
		);
		edd_update_option( 'login_page', $login_page_id );

		$this->assertFalse( edd_get_login_page_uri() );

		edd_delete_option( 'login_page' );
		wp_delete_post( $login_page_id );
	}

	public function test_process_register_form_customer_email_exists() {

		$customer = edd_add_customer(
			array(
				'email'   => 'edd@edd.local',
				'name'    => 'EDD Local User',
				'user_id' => 1,
			)
		);
		edd_add_customer_email_address(
			array(
				'customer_id' => $customer,
				'email'       => 'second_email@edd.local',
				'type'        => 'secondary',
			)
		);

		edd_process_register_form(
			array(
				'edd_register_submit' => 1,
				'edd_user_login'      => 'eddlocal',
				'edd_user_email'      => 'second_email@edd.local',
				'edd_user_pass'       => 'password',
				'edd_user_pass2'      => 'password',
			)
		);
		$this->assertArrayHasKey( 'email_unavailable', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	public function test_edd_get_password_reset_link_with_invalid_user() {
		$this->assertFalse( edd_get_password_reset_link( 'invalid_user' ) );
	}

	public function test_edd_get_password_reset_link_is_core_login_url() {
		$user_id = $this->factory->user->create();
		$this->assertStringContainsString( 'wp-login.php', edd_get_password_reset_link( get_userdata( $user_id ) ) );
	}

	public function test_edd_get_password_reset_link_uses_edd_login_uri() {
		$login_page_id = wp_insert_post(
			array(
				'post_title'   => 'Login Page',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:edd/login /-->',
			)
		);
		edd_update_option( 'login_page', $login_page_id );

		$user_id = $this->factory->user->create();

		$this->assertStringContainsString( get_permalink( $login_page_id ), edd_get_password_reset_link( get_userdata( $user_id ) ) );

		edd_delete_option( 'login_page' );
		wp_delete_post( $login_page_id );
	}

	public function test_edd_validate_password_reset_empty_has_errors() {
		edd_validate_password_reset( array() );
		$this->assertArrayHasKey( 'password_reset_failed', edd_get_errors() );
	}

	public function test_edd_validate_password_reset_user_login_has_errors() {
		edd_validate_password_reset(
			array(
				'edd_resetpassword_nonce' => wp_create_nonce( 'edd-resetpassword-nonce' ),
				'user_login'              => 'admin',
				'rp_key'                  => 'key',
			)
		);
		$this->assertArrayHasKey( 'password_reset_failed', edd_get_errors() );
	}

	public function test_edd_validate_password_reset_invalid_user_has_errors() {
		edd_validate_password_reset(
			array(
				'edd_resetpassword_nonce' => wp_create_nonce( 'edd-resetpassword-nonce' ),
				'user_login'              => 'fake_user',
				'rp_key'                  => 'key',
			)
		);
		$this->assertArrayHasKey( 'password_reset_unsuccessful', edd_get_errors() );
	}
}
