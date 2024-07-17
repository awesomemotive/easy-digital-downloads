<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class OrderReceipt extends EDD_UnitTestCase {

	/**
	 * Email ID.
	 *
	 * @var string
	 */
	private static $id = 'order_receipt';

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

	public function test_email_id_is_correct() {
		$this->assertEquals( self::$id, self::$email->email_id );
	}

	public function test_email_name_is_correct() {
		$this->assertEquals( 'Purchase Receipt', self::$email->get_name() );
	}

	public function test_email_recipient_is_correct() {
		$this->assertEquals( 'customer', self::$email->recipient );
	}

	public function test_email_context_is_correct() {
		$this->assertEquals( 'order', self::$email->context );
	}

	public function test_email_subject() {
		$this->assertEquals( 'Purchase Receipt', self::$email->subject );
	}

	public function test_email_heading() {
		$this->assertEquals( 'Purchase Receipt', self::$email->heading );
	}

	public function test_email_body_matches_default() {
		$this->assertEquals( self::$email->get_default( 'content' ), self::$email->content );
	}

	public function test_saving_email() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );

		$manager = new \EDD\Admin\Emails\Manager();
		$manager->save(
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
	}

	public function test_order_receipt_preview_email() {
		$order_id          = \EDD\Tests\Helpers\EDD_Helper_Payment::create_simple_payment();
		$email             = new \EDD\Emails\Types\OrderReceipt( edd_get_order( $order_id ) );
		$email->is_preview = true;
		$preview           = $email->get_preview();

		$this->assertStringContainsString( 'Please click on the link(s) below to download your files.', $preview );
	}

	/**
	 * @expectedDeprecated edd_get_option( 'purchase_receipt' )
	 *
	 * @return void
	 */
	public function test_legacy_option_handling() {
		$this->assertEquals( self::$email->content, edd_get_option( 'purchase_receipt' ) );
	}
}
