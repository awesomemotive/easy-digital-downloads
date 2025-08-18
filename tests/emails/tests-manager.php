<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Manager extends EDD_UnitTestCase {

	private static $registry;

	public function test_get_emails_is_array() {
		$this->assertIsArray( $this->get_registry()->get_emails() );
	}

	public function test_get_emails_is_not_empty() {
		$this->assertNotEmpty( $this->get_registry()->get_emails() );
	}

	public function test_order_receipt_template_exists() {
		$this->assertTrue( array_key_exists( 'order_receipt', $this->get_registry()->get_emails() ) );
	}

	public function test_get_recipients_is_array() {
		$this->assertIsArray( $this->get_registry()->get_recipients() );
	}

	public function test_get_recipients_is_not_empty() {
		$this->assertNotEmpty( $this->get_registry()->get_recipients() );
	}

	public function test_get_recipients_is_correct() {
		$this->assertEquals(
			array(
				'customer' => __( 'Customer', 'easy-digital-downloads' ),
				'admin'    => __( 'Admin', 'easy-digital-downloads' ),
				'user'     => __( 'User', 'easy-digital-downloads' ),
			),
			$this->get_registry()->get_recipients()
		);
	}

	public function test_get_senders_is_array() {
		$this->assertIsArray( $this->get_registry()->get_senders() );
	}

	public function test_get_senders_is_not_empty() {
		$this->assertNotEmpty( $this->get_registry()->get_senders() );
	}

	public function test_get_senders_includes_edd() {
		$this->assertTrue( array_key_exists( 'edd', $this->get_registry()->get_senders() ) );
	}

	public function test_new_id_generator_returns_string() {
		$new_id = \EDD\Admin\Emails\Manager::get_new_id( 'license', 'license_new' );

		$this->assertIsString( $new_id );
		$this->assertStringContainsString( 'license_', $new_id );
	}

	public function test_new_id_generator_returns_string_32() {
		$new_id = \EDD\Admin\Emails\Manager::get_new_id( 'superduperlongstringprefixthatwillnotfitindatabase', 'license_new' );

		$this->assertIsString( $new_id );
		$this->assertEquals( 32, strlen( $new_id ) );
	}

	public function test_new_id_generator_invalid_characters_returns_string() {
		$new_id = \EDD\Admin\Emails\Manager::get_new_id( 'license_new!@#$%^&*()_+', 'license_new' );

		$this->assertIsString( $new_id );
		$this->assertStringNotContainsString( '!', $new_id );
	}

	public function test_update_reply_to_setting_enabled() {
		$email_id = 123;
		$data = array( 'use_customer_reply_to' => '1' );

		$manager = new \EDD\Admin\Emails\Manager();
		
		$reflection = new \ReflectionClass( $manager );
		$method = $reflection->getMethod( 'update_reply_to_setting' );
		$method->setAccessible( true );
		$method->invoke( $manager, $email_id, $data );

		$this->assertEquals( '1', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );

		edd_delete_email_meta( $email_id, 'use_customer_reply_to' );
	}

	public function test_update_reply_to_setting_disabled() {
		$email_id = 456;
		$data = array();

		edd_update_email_meta( $email_id, 'use_customer_reply_to', 1 );
		$this->assertEquals( '1', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );

		$manager = new \EDD\Admin\Emails\Manager();
		
		$reflection = new \ReflectionClass( $manager );
		$method = $reflection->getMethod( 'update_reply_to_setting' );
		$method->setAccessible( true );
		$method->invoke( $manager, $email_id, $data );

		$this->assertEquals( '', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );
	}

	public function test_update_reply_to_setting_empty_value() {
		$email_id = 789;
		$data = array( 'use_customer_reply_to' => '' );

		edd_update_email_meta( $email_id, 'use_customer_reply_to', 1 );
		$this->assertEquals( '1', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );

		$manager = new \EDD\Admin\Emails\Manager();
		
		$reflection = new \ReflectionClass( $manager );
		$method = $reflection->getMethod( 'update_reply_to_setting' );
		$method->setAccessible( true );
		$method->invoke( $manager, $email_id, $data );

		$this->assertEquals( '', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );
	}

	public function test_update_reply_to_setting_zero_value() {
		$email_id = 101;
		$data = array( 'use_customer_reply_to' => '0' );

		edd_update_email_meta( $email_id, 'use_customer_reply_to', 1 );
		$this->assertEquals( '1', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );

		$manager = new \EDD\Admin\Emails\Manager();
		
		$reflection = new \ReflectionClass( $manager );
		$method = $reflection->getMethod( 'update_reply_to_setting' );
		$method->setAccessible( true );
		$method->invoke( $manager, $email_id, $data );

		$this->assertEquals( '', edd_get_email_meta( $email_id, 'use_customer_reply_to', true ) );
	}

	private static function get_registry() {
		if ( ! self::$registry ) {
			self::$registry = edd_get_email_registry();
		}

		return self::$registry;
	}
}
