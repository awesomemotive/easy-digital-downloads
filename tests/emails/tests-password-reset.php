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
}
