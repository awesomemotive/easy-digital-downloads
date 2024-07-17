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

	private static function get_registry() {
		if ( ! self::$registry ) {
			self::$registry = edd_get_email_registry();
		}

		return self::$registry;
	}
}
