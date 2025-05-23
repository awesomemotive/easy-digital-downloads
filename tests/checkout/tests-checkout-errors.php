<?php

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Errors extends EDD_UnitTestCase {

	public function tearDown(): void {
		edd_delete_option( 'banned_emails' );
		edd_clear_errors();
	}

	public function test_edd_check_purchase_email_no_banned() {
		edd_check_purchase_email( array(), array() );
		$this->assertEmpty( edd_get_errors() );
	}

	public function test_edd_check_purchase_email_banned() {
		edd_update_option( 'banned_emails', array( 'test@edd.local' ) );
		edd_check_purchase_email( array(), array( 'edd_email' => 'test@edd.local' ) );

		$this->assertArrayHasKey( 'email_banned', edd_get_errors() );
	}

	public function test_edd_check_purchase_email_not_banned() {
		edd_update_option( 'banned_emails', array( 'test@edd.local' ) );
		edd_check_purchase_email( array(), array( 'edd_email' => 'newemail@edd.local' ) );

		$this->assertEmpty( edd_get_errors() );
	}

	public function test_edd_check_purchase_email_no_edd_email() {
		edd_update_option( 'banned_emails', array( 'test@edd.local' ) );
		edd_check_purchase_email( array(), array() );

		$this->assertEmpty( edd_get_errors() );
	}

	public function test_existing_user_is_error() {
		wp_logout();
		$user_id = $this->factory->user->create();
		$user    = get_user_by( 'id', $user_id );
		edd_add_customer(
			array(
				'email'   => $user->user_email,
				'user_id' => $user->ID,
				'name'    => $user->display_name,
			)
		);
		$errors  = new \EDD\Checkout\Errors();
		$errors->check_existing_users(
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

	public function test_existing_email_different_customer_is_error() {
		wp_logout();
		$user_id = $this->factory->user->create();
		$user    = get_user_by( 'id', $user_id );
		$customer_id = edd_add_customer(
			array(
				'email'   => $user->user_email,
				'user_id' => $user->ID,
				'name'    => $user->display_name,
			)
		);
		$user_2_id = $this->factory->user->create();
		$user_2    = get_user_by( 'id', $user_2_id );
		edd_add_customer_email_address(
			array(
				'email'       => $user_2->user_email,
				'customer_id' => $customer_id,
			)
		);
		// log this user in
		wp_set_current_user( $user_2_id );
		$_POST['email'] = $user_2->user_email;
		$errors   = new \EDD\Checkout\Errors();
		$response = $errors->check_email_ajax();

		$this->assertInstanceOf( 'WP_Error', $response );

		edd_checkout_check_existing_email( array(), array() );

		$this->assertArrayHasKey( 'edd-customer-email-exists', edd_get_errors() );
	}

	public function test_existing_email_deleted_customer_is_okay() {
		wp_logout();
		$user_id = $this->factory->user->create();
		$user    = get_user_by( 'id', $user_id );
		$customer_id = edd_add_customer(
			array(
				'email'   => $user->user_email,
				'user_id' => $user->ID,
				'name'    => $user->display_name,
			)
		);
		$user_2_id = $this->factory->user->create();
		$user_2    = get_user_by( 'id', $user_2_id );
		edd_add_customer_email_address(
			array(
				'email'       => $user_2->user_email,
				'customer_id' => $customer_id,
				'type'        => 'secondary',
			)
		);
		edd_delete_customer( $customer_id );
		// log this user in
		wp_set_current_user( $user_2_id );
		$_POST['email'] = $user_2->user_email;
		$errors = new \EDD\Checkout\Errors();

		$this->assertTrue( $errors->check_email_ajax() );

		edd_checkout_check_existing_email( array(), array() );

		$this->assertEmpty( edd_get_errors() );
	}
}
