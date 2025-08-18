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

	public function test_default_use_customer_reply_to_meta() {
		$this->assertEquals( '', self::$email->get_metadata( 'use_customer_reply_to' ) );
		$this->assertEquals( 0, self::$email->get_default( 'use_customer_reply_to' ) );
	}

	public function test_use_customer_reply_to_setting_save() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );

		$templates = new \EDD\Admin\Emails\Manager();
		$templates->save(
			array(
				'edd_save_email_nonce'   => wp_create_nonce( 'edd_save_email' ),
				'email_id'               => self::$id,
				'use_customer_reply_to'  => '1',
				'subject'                => 'Test Subject',
				'heading'                => 'Test Heading',
				'content'                => 'Test Body',
			)
		);

		$email = self::$registry->get_email_by_id( self::$id );
		$this->assertEquals( '1', $email->get_metadata( 'use_customer_reply_to' ) );
	}

	public function test_use_customer_reply_to_setting_uncheck() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );

		edd_update_email_meta( self::$email->email->id, 'use_customer_reply_to', 1 );

		$templates = new \EDD\Admin\Emails\Manager();
		$templates->save(
			array(
				'edd_save_email_nonce' => wp_create_nonce( 'edd_save_email' ),
				'email_id'             => self::$id,
				'subject'              => 'Test Subject',
				'heading'              => 'Test Heading',
				'content'              => 'Test Body',
			)
		);

		$email = self::$registry->get_email_by_id( self::$id );
		$this->assertEquals( '', $email->get_metadata( 'use_customer_reply_to' ) );
	}

	public function test_reply_to_header_with_customer_setting_enabled() {
		$payment = EDD_Helper_Payment::create_simple_payment();
		$order   = edd_get_order( $payment );

		edd_update_email_meta( self::$email->email->id, 'use_customer_reply_to', 1 );

		$admin_notice = new \EDD\Emails\Types\AdminOrderNotice( $order );
		
		$reflection = new \ReflectionClass( $admin_notice );
		$set_headers_method = $reflection->getMethod( 'set_headers' );
		$set_headers_method->setAccessible( true );
		$set_headers_method->invoke( $admin_notice );

		$this->assertTrue( has_filter( 'edd_email_headers_array' ) );

		$headers = apply_filters( 'edd_email_headers_array', array(
			'From'         => 'test@example.com',
			'Reply-To'     => 'admin@example.com',
			'Content-Type' => 'text/html; charset=utf-8',
		) );

		$this->assertEquals( $order->email, $headers['Reply-To'] );

		edd_delete_email_meta( self::$email->email->id, 'use_customer_reply_to' );
	}

	public function test_reply_to_header_with_customer_setting_disabled() {
		$payment = EDD_Helper_Payment::create_simple_payment();
		$order   = edd_get_order( $payment );

		$admin_notice = new \EDD\Emails\Types\AdminOrderNotice( $order );
		
		$reflection = new \ReflectionClass( $admin_notice );
		$set_headers_method = $reflection->getMethod( 'set_headers' );
		$set_headers_method->setAccessible( true );
		$set_headers_method->invoke( $admin_notice );

		$headers = apply_filters( 'edd_email_headers_array', array(
			'From'         => 'test@example.com',
			'Reply-To'     => 'admin@example.com',
			'Content-Type' => 'text/html; charset=utf-8',
		) );

		$this->assertEquals( 'admin@example.com', $headers['Reply-To'] );
	}

	public function test_reply_to_header_without_customer_email() {
		$payment = EDD_Helper_Payment::create_simple_payment();
		$order   = edd_get_order( $payment );
		edd_update_order( $order->id, array( 'email' => '' ) );
		$order = edd_get_order( $order->id );

		edd_update_email_meta( self::$email->email->id, 'use_customer_reply_to', 1 );

		$admin_notice = new \EDD\Emails\Types\AdminOrderNotice( $order );
		
		$reflection = new \ReflectionClass( $admin_notice );
		$set_headers_method = $reflection->getMethod( 'set_headers' );
		$set_headers_method->setAccessible( true );
		$set_headers_method->invoke( $admin_notice );

		$headers = apply_filters( 'edd_email_headers_array', array(
			'From'         => 'test@example.com',
			'Reply-To'     => 'admin@example.com',
			'Content-Type' => 'text/html; charset=utf-8',
		) );

		$this->assertEquals( 'admin@example.com', $headers['Reply-To'] );

		edd_delete_email_meta( self::$email->email->id, 'use_customer_reply_to' );
	}

	public function test_reply_to_header_with_null_order() {
		edd_update_email_meta( self::$email->email->id, 'use_customer_reply_to', 1 );

		$admin_notice = new \EDD\Emails\Types\AdminOrderNotice( false );
		
		$reflection = new \ReflectionClass( $admin_notice );
		$set_headers_method = $reflection->getMethod( 'set_headers' );
		$set_headers_method->setAccessible( true );
		$set_headers_method->invoke( $admin_notice );

		$headers = apply_filters( 'edd_email_headers_array', array(
			'From'         => 'test@example.com',
			'Reply-To'     => 'admin@example.com',
			'Content-Type' => 'text/html; charset=utf-8',
		) );

		$this->assertEquals( 'admin@example.com', $headers['Reply-To'] );

		edd_delete_email_meta( self::$email->email->id, 'use_customer_reply_to' );
	}
}
