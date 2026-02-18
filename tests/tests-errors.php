<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Errors extends EDD_UnitTestCase {

	public function setup(): void {
		parent::setUp();

		edd_set_error( 'invalid_email', 'Please enter a valid email address.' );
		edd_set_error( 'invalid_user', 'The user information is invalid.' );
		edd_set_error( 'username_incorrect', 'The username you entered does not exist' );
		edd_set_error( 'password_incorrect', 'The password you entered is incorrect' );
	}

	public function test_set_errors() {
		$errors = edd_get_errors();

		$this->assertIsArray( $errors );
		$this->assertArrayHasKey( 'invalid_email', $errors );
		$this->assertArrayHasKey( 'invalid_user', $errors );
		$this->assertArrayHasKey( 'username_incorrect', $errors );
		$this->assertArrayHasKey( 'password_incorrect', $errors );
	}

	public function test_clear_errors() {
		edd_clear_errors();
		$this->assertEmpty( edd_get_errors() );
	}

	public function test_unset_error() {
		edd_unset_error( 'invalid_email' );
		$errors = edd_get_errors();

		$expected = array(
			'invalid_user'       => 'The user information is invalid.',
			'username_incorrect' => 'The username you entered does not exist',
			'password_incorrect' => 'The password you entered is incorrect',
		);

		$this->assertEquals( $expected, $errors );
	}
}
