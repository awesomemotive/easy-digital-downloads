<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class OrderRefund extends EDD_UnitTestCase {

	/**
	 * Email ID.
	 *
	 * @var string
	 */
	private static $id = 'order_refund';

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
		$this->assertFalse( self::$email->status );
	}

	public function test_email_id_is_correct() {
		$this->assertEquals( self::$id, self::$email->email_id );
	}

	public function test_email_name_is_correct() {
		$this->assertEquals( 'Refund Issued', self::$email->get_name() );
	}

	public function test_email_recipient_is_correct() {
		$this->assertEquals( 'customer', self::$email->recipient );
	}

	public function test_email_context_is_correct() {
		$this->assertEquals( 'refund', self::$email->context );
	}

	public function test_email_subject() {
		$this->assertEquals( 'Your order has been refunded', self::$email->subject );
	}

	public function test_email_heading() {
		$this->assertEmpty( self::$email->heading );
	}

	public function test_email_body_matches_default() {
		$this->assertEquals( self::$email->get_default( 'content' ), self::$email->content );
	}
}
