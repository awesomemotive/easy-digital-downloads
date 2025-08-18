<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class AdminOrderNoticeTemplate extends EDD_UnitTestCase {

	/**
	 * Email template object.
	 *
	 * @var \EDD\Emails\Templates\AdminOrderNotice
	 */
	private static $template;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$template = new \EDD\Emails\Templates\AdminOrderNotice();
	}

	public function test_template_has_use_customer_reply_to_meta() {
		$meta = self::$template->meta;
		$this->assertArrayHasKey( 'use_customer_reply_to', $meta );
	}

	public function test_template_default_use_customer_reply_to_value() {
		$defaults = self::$template->defaults();
		$this->assertArrayHasKey( 'use_customer_reply_to', $defaults );
		$this->assertEquals( 0, $defaults['use_customer_reply_to'] );
	}

	public function test_template_get_default_use_customer_reply_to() {
		$this->assertEquals( 0, self::$template->get_default( 'use_customer_reply_to' ) );
	}

	public function test_template_meta_defaults_to_empty_string() {
		$this->assertEquals( '', self::$template->get_metadata( 'use_customer_reply_to' ) );
	}

	public function test_template_can_edit_recipients() {
		$editable_properties = $this->get_editable_properties();
		$this->assertContains( 'recipient', $editable_properties );
	}

	public function test_template_recipients_default() {
		$defaults = self::$template->defaults();
		$this->assertArrayHasKey( 'recipients', $defaults );
		$this->assertEquals( 'admin', $defaults['recipients'] );
	}

	public function test_template_status_default() {
		$defaults = self::$template->defaults();
		$this->assertArrayHasKey( 'status', $defaults );
		$this->assertEquals( 1, $defaults['status'] );
	}

	public function test_template_can_preview() {
		$reflection = new \ReflectionClass( self::$template );
		$can_preview_property = $reflection->getProperty( 'can_preview' );
		$can_preview_property->setAccessible( true );
		$this->assertTrue( $can_preview_property->getValue( self::$template ) );
	}

	public function test_template_can_test() {
		$reflection = new \ReflectionClass( self::$template );
		$can_test_property = $reflection->getProperty( 'can_test' );
		$can_test_property->setAccessible( true );
		$this->assertTrue( $can_test_property->getValue( self::$template ) );
	}

	public function test_template_email_id() {
		$reflection = new \ReflectionClass( self::$template );
		$email_id_property = $reflection->getProperty( 'email_id' );
		$email_id_property->setAccessible( true );
		$this->assertEquals( 'admin_order_notice', $email_id_property->getValue( self::$template ) );
	}

	public function test_template_recipient() {
		$reflection = new \ReflectionClass( self::$template );
		$recipient_property = $reflection->getProperty( 'recipient' );
		$recipient_property->setAccessible( true );
		$this->assertEquals( 'admin', $recipient_property->getValue( self::$template ) );
	}

	private function get_editable_properties() {
		$reflection = new \ReflectionClass( self::$template );
		$method = $reflection->getMethod( 'get_editable_properties' );
		$method->setAccessible( true );
		return $method->invoke( self::$template );
	}
}