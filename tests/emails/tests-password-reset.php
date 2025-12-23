<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class PasswordReset extends EDD_UnitTestCase {

	/**
	 * Email ID.
	 *
	 * @var string
	 */
	private static $id = 'password_reset';

	/**
	 * Registry object.
	 *
	 * @var \EDD\Emails\Templates\Registry
	 */
	private static $registry;

	/**
	 * Email object.
	 *
	 * @var \EDD\Emails\Templates\EmailTemplate
	 */
	private static $email;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$login_page_id = wp_insert_post(
			array(
				'post_title'   => 'Login Page',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:edd/login /-->',
			)
		);
		edd_update_option( 'login_page', $login_page_id );

		self::$registry = new \EDD\Emails\Templates\Registry();
		self::$email    = self::$registry->get_email_by_id( self::$id );
	}

	public function test_email_is_enabled() {
		$this->assertTrue( self::$email->status );
	}

	public function test_email_id_is_correct() {
		$this->assertEquals( self::$id, self::$email->email_id );
	}

	public function test_email_name_is_correct() {
		$this->assertEquals( 'Password Reset', self::$email->get_name() );
	}

	public function test_email_recipient_is_correct() {
		$this->assertEquals( 'user', self::$email->recipient );
	}

	public function test_email_context_is_correct() {
		$this->assertEquals( 'user', self::$email->context );
	}

	public function test_email_subject() {
		$this->assertEquals( self::$email->get_default( 'subject' ), self::$email->subject );
	}

	public function test_email_heading() {
		$this->assertEmpty( self::$email->heading );
	}

	public function test_email_body_matches_default() {
		$this->assertEquals( self::$email->get_default( 'content' ), self::$email->content );
	}

	public function test_email_sender_is_wp() {
		$this->assertEquals( 'wp', self::$email->sender );
	}

	public function test_email_can_edit_subject_is_false() {
		$this->assertFalse( self::$email->can_edit( 'subject' ) );
	}

	public function test_email_can_edit_status_is_false() {
		$this->assertFalse( self::$email->can_edit( 'status' ) );
	}

	public function test_email_has_required_tag() {
		$this->assertEquals( 'password_reset_link', self::$email->required_tag );
	}

	/**
	 * Test that edd_retrieve_password_message validates redirect URLs with wp_validate_redirect.
	 */
	public function test_retrieve_password_message_validates_safe_internal_redirect() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		$_POST['edd_redirect']              = edd_get_login_page_uri();

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// Verify the redirect URL is included in the password reset link
		$this->assertStringContainsString( edd_get_login_page_uri(), $message );
		$this->assertStringContainsString( 'edd_action=password_reset_requested', $message );
		$this->assertStringContainsString( 'key=' . $key, $message );
		$this->assertStringContainsString( 'login=' . rawurlencode( $user->user_login ), $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}

	public function test_retrieve_password_message_blocks_external_redirect() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		$_POST['edd_redirect']              = 'https://external-site.com/somewhere';

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// wp_validate_redirect should reject external URL and fall back to default
		$this->assertStringNotContainsString( 'external-site.com', $message );
		$this->assertStringContainsString( edd_get_login_page_uri(), $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}

	public function test_retrieve_password_message_handles_missing_redirect() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		// edd_redirect not set

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// Should fall back to default (edd_get_login_page_uri)
		$this->assertStringContainsString( edd_get_login_page_uri(), $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'] );
	}

	public function test_retrieve_password_message_handles_empty_redirect() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		$_POST['edd_redirect']              = '';

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// Should fall back to default when redirect is empty
		$this->assertStringContainsString( edd_get_login_page_uri(), $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}

	public function test_retrieve_password_message_allows_relative_urls() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		$_POST['edd_redirect']              = '/my-account/';

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// Relative URLs should be allowed by wp_validate_redirect
		$this->assertStringContainsString( '/my-account/', $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}

	public function test_retrieve_password_message_allows_current_site_url() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$site_url = home_url( '/downloads/' );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		$_POST['edd_redirect']              = $site_url;

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// URLs from the current site should be allowed
		$this->assertStringContainsString( '/downloads/', $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}

	public function test_retrieve_password_message_sanitizes_javascript_url() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = wp_create_nonce( 'edd-lost-password-nonce' );
		$_POST['edd_redirect']              = 'javascript:alert("xss")';

		$message = edd_retrieve_password_message( '', $key, $user->user_login, $user );

		// JavaScript URLs should be blocked
		$this->assertStringNotContainsString( 'javascript:', $message );
		$this->assertStringContainsString( edd_get_login_page_uri(), $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}

	public function test_retrieve_password_message_without_edd_action() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$original_message = 'Original WordPress message';

		// Don't set edd_action - should return original message
		$message = edd_retrieve_password_message( $original_message, $key, $user->user_login, $user );

		$this->assertEquals( $original_message, $message );
	}

	public function test_retrieve_password_message_with_invalid_nonce() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$original_message = 'Original WordPress message';

		$_POST['edd_action']                = 'user_lost_password';
		$_POST['edd_lost-password_nonce']   = 'invalid-nonce';
		$_POST['edd_redirect']              = edd_get_login_page_uri();

		// Invalid nonce should return original message
		$message = edd_retrieve_password_message( $original_message, $key, $user->user_login, $user );

		$this->assertEquals( $original_message, $message );

		unset( $_POST['edd_action'], $_POST['edd_lost-password_nonce'], $_POST['edd_redirect'] );
	}
}
