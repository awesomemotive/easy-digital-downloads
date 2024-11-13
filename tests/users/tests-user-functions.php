<?php

namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Functions extends EDD_UnitTestCase {

	public function test_edd_connect_existing_customer_to_new_user() {
		$customer = parent::edd()->customer->create_and_get();
		$user_id  = wp_insert_user(
			array(
				'user_login' => 'test',
				'user_email' => $customer->email,
				'user_pass'  => wp_generate_password(),
			)
		);

		edd_connect_existing_customer_to_new_user( $user_id );
		$customer = edd_get_customer( $customer->id );

		$this->assertEquals( (int) $user_id, (int) $customer->user_id );
	}

	public function test_edd_connect_existing_customer_to_new_user_already_connected() {
		$customer = parent::edd()->customer->create_and_get();
		$user_id  = wp_insert_user(
			array(
				'user_login' => 'test_new',
				'user_email' => $customer->email,
				'user_pass'  => wp_generate_password(),
			)
		);

		edd_update_customer(
			$customer->id,
			array(
				'user_id' => $user_id,
			)
		);
		$customer = edd_get_customer( $customer->id );

		$this->assertEquals( (int) $user_id, (int) $customer->user_id );

		edd_add_customer_email_address(
			array(
				'customer_id' => $customer->id,
				'email'       => 'totallynewemail@edd.test',
				'type'        => 'secondary',
			)
		);

		$user_2_id = wp_insert_user(
			array(
				'user_login' => 'test_2',
				'user_email' => 'totallynewemail@edd.test',
				'user_pass'  => wp_generate_password(),
			)
		);

		edd_connect_existing_customer_to_new_user( $user_2_id );
		$customer = edd_get_customer( $customer->id );

		$this->assertNotEquals( (int) $user_2_id, (int) $customer->user_id );
	}
}
