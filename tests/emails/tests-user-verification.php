<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class UserVerification extends EDD_UnitTestCase {

	/**
	 * Email ID.
	 *
	 * @var string
	 */
	private static $id = 'user_verification';

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

		self::$registry = edd_get_email_registry();
		self::$email    = self::$registry->get_email_by_id( self::$id );
	}

	public function test_email_is_enabled() {
		$this->assertTrue( self::$email->status );
	}

	public function test_email_id_is_correct() {
		$this->assertEquals( self::$id, self::$email->email_id );
	}

	public function test_email_name_is_correct() {
		$this->assertEquals( 'User Verification', self::$email->get_name() );
	}

	public function test_email_recipient_is_correct() {
		$this->assertEquals( 'user', self::$email->recipient );
	}

	public function test_email_context_is_correct() {
		$this->assertEquals( 'user', self::$email->context );
	}

	public function test_email_subject() {
		$this->assertEquals( 'Verify your account', self::$email->get_default( 'subject' ) );
	}

	public function test_email_heading() {
		$this->assertEquals( 'Verify your account', self::$email->get_default( 'heading' ) );
	}

	public function test_email_body_matches_default() {
		$this->assertEquals( self::$email->get_default( 'content' ), self::$email->content );
	}

	public function test_row_actions() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );

		$row_actions = self::$email->get_row_actions();

		$this->assertArrayHasKey( 'edit', $row_actions );
		$this->assertArrayHasKey( 'view', $row_actions );
		$this->assertArrayNotHasKey( 'test', $row_actions );
	}

	public function test_saving_email_should_fail() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );

		$templates = new \EDD\Admin\Emails\Manager();
		$templates->save(
			array(
				'edd_save_email_nonce' => wp_create_nonce( 'edd_save_email' ),
				'email_id'             => self::$id,
				'subject'              => 'New Subject',
				'content'              => 'New Body',
				'status'               => 0,
			)
		);

		$email = self::$registry->get_email_by_id( self::$id );

		$this->assertEquals( self::$email->get_default( 'subject' ), $email->subject );
		$this->assertEquals( self::$email->get_default( 'content' ), $email->content );
		$this->assertTrue( $email->status );
	}

	public function test_saving_email() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );

		$templates = new \EDD\Admin\Emails\Manager();
		$templates->save(
			array(
				'edd_save_email_nonce' => wp_create_nonce( 'edd_save_email' ),
				'email_id'             => self::$id,
				'subject'              => 'New Subject',
				'content'              => '{verification_url}',
				'status'               => 0,
			)
		);

		$email = self::$registry->get_email_by_id( self::$id );

		$this->assertEquals( 'New Subject', $email->subject );
		$this->assertEquals( '{verification_url}', $email->content );
		$this->assertTrue( $email->status );
	}

	public function test_email_has_required_tag() {
		$this->assertEquals( 'verification_url', self::$email->required_tag );
	}
}
