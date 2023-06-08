<?php

namespace EDD\Tests\Users;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
class Tests_Profile extends EDD_UnitTestCase {

	private static $customers;

	public static function wpSetUpBeforeClass() {
		$customers = parent::edd()->customer->create_many( 5 );

		foreach ( $customers as $customer ) {
			self::$customers[] = edd_get_customer( $customer );
		}
	}

	public function test_update_user_fails_with_existing_customer_address() {
		$customer_id = edd_add_customer(
			array(
				'name'    => 'Test User',
				'email'   => 'email@email.local',
				'user_id' => get_current_user_id(),
			)
		);
		$customer    = reset( self::$customers );
		edd_process_profile_editor_updates(
			array(
				'edd_profile_editor_submit' => true,
				'edd_email'                 => $customer->email,
				'edd_profile_editor_nonce'  => wp_create_nonce( 'edd-profile-editor-nonce' ),
				'edd_redirect'              => false,
			)
		);

		$this->assertArrayHasKey( 'email_exists', edd_get_errors() );

		// Clear errors for other test
		edd_clear_errors();
	}

	public function test_update_user_fails_with_missing_nonce() {
		$profile_update = edd_process_profile_editor_updates(
			array(
				'edd_profile_editor_submit' => true,
				'edd_redirect'              => false,
			)
		);

		$this->assertFalse( $profile_update );

		// Clear errors for other test
		edd_clear_errors();
	}
}
