<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

class AdminOrderNotice extends EDD_UnitTestCase {

	/**
	 * Email ID.
	 *
	 * @var string
	 */
	private static $id = 'admin_order_notice';

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
		$email = edd_get_email( self::$id );
		$this->assertTrue( $email->is_enabled() );
	}

	public function test_admin_notice_disabled() {
		$this->assertFalse( edd_admin_notices_disabled() );
	}

	public function test_email_id_is_correct() {
		$this->assertEquals( self::$id, self::$email->email_id );
	}

	public function test_email_name_is_correct() {
		$this->assertEquals( 'Admin Sale Notification', self::$email->get_name() );
	}

	public function test_email_recipient_is_correct() {
		$this->assertEquals( 'admin', self::$email->recipient );
	}

	public function test_email_context_is_correct() {
		$this->assertEquals( 'order', self::$email->context );
	}

	public function test_email_subject() {
		$this->assertEquals( 'New download purchase - Order #{payment_id}', self::$email->subject );
	}

	public function test_email_heading() {
		$this->assertEquals( 'New Sale!', self::$email->heading );
	}

	public function test_email_body_matches_default() {
		$this->assertEquals( self::$email->get_default( 'content' ), self::$email->content );
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
				'heading'              => 'New Heading',
				'content'              => 'New Body',
				'status'               => 0,
			)
		);

		$email = self::$registry->get_email_by_id( self::$id );

		$this->assertEquals( 'New Subject', $email->subject );
		$this->assertEquals( 'New Heading', $email->heading );
		$this->assertEquals( 'New Body', $email->content );
		$this->assertFalse( $email->status );
		$this->assertTrue( edd_admin_notices_disabled() );
	}

	public function test_updating_email_meta_updates_recipient() {
		edd_update_email_meta( self::$email->email->id, 'recipients', 'batman@thebatcave.co' );

		$this->assertEquals( 'batman@thebatcave.co', self::$email->get_metadata( 'recipients' ) );
	}

	public function test_edit_url() {
		$this->assertEquals( admin_url( 'edit.php?post_type=download&page=edd-emails&email=' . self::$id ), self::$email->get_edit_url() );
	}

	public function test_row_actions() {
		$row_actions = self::$email->get_row_actions();

		$this->assertArrayHasKey( 'edit', $row_actions );
	}
}
