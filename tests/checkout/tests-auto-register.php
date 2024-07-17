<?php

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers;

class AutoRegister extends EDD_UnitTestCase {

	public static function setUpBeforeClass(): void {
		edd_update_option( 'logged_in_only', 'auto' );
		parent::setUpBeforeClass();
		wp_logout();
	}

	public static function tearDownAfterClass(): void {
		edd_delete_option( 'logged_in_only' );
		parent::tearDownAfterClass();
	}

	public function tearDown(): void {
		edd_clear_errors();
	}

	public function test_auto_register_is_enabled() {
		$this->assertEquals( 'auto', edd_get_option( 'logged_in_only' ) );
	}

	public function test_get_option_show_register_form_both_returns_login() {
		$this->assertEquals( 'login', edd_get_option( 'show_register_form', 'both' ) );
	}

	public function test_get_option_show_register_form_registration_returns_none() {
		$this->assertEquals( 'none', edd_get_option( 'show_register_form', 'registration' ) );
	}

	public function test_get_option_show_register_form_login_returns_login() {
		$this->assertEquals( 'login', edd_get_option( 'show_register_form', 'login' ) );
	}

	public function test_guest_payment_is_not_guest() {
		$guest_payment_id = Helpers\EDD_Helper_Payment::create_simple_guest_payment();
		$this->assertFalse( edd_is_guest_payment( $guest_payment_id ) );
	}

	public function test_existing_user_is_error() {
		$user_id       = $this->factory->user->create();
		$user          = get_user_by( 'id', $user_id );
		$auto_register = new \EDD\Checkout\AutoRegister();
		$auto_register->check_existing_user(
			$user,
			array(
				'guest_user_data' => array(
					'user_email' => $user->user_email,
				),
			),
			array()
		);

		$this->assertArrayHasKey( 'email_used', edd_get_errors() );
	}

	public function test_create_user_from_purchase_data_is_user_id() {
		$purchase_data = array(
			'user_info' => array(
				'email'      => 'testautoregister@edd.local',
				'first_name' => 'Test',
				'last_name'  => 'Auto Register',
			),
		);
		$auto_register = new \EDD\Checkout\AutoRegister();
		$user_id       = $auto_register->create_user( $purchase_data );

		// check that getting the user returns a WP_User object
		$user = get_user_by( 'id', $user_id );
		$this->assertInstanceOf( 'WP_User', $user );
	}
}
