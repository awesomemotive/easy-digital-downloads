<?php
/**
 * API key issuing tests.
 */

namespace EDD\Tests\API;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests when a key pair may be issued from a user's own profile.
 *
 * The profile screen renders the key controls only where the store allows user keys or the caller
 * can manage shop settings. These cases drive the save handler, which decides whether a submission
 * is honored, so the two agree.
 *
 * @group api
 */
class UserKeySetting extends EDD_UnitTestCase {

	/**
	 * A user holding none of EDD's shop capabilities.
	 *
	 * @var int
	 */
	protected static $subscriber_id;

	public static function wpSetUpBeforeClass() {
		$roles = new \EDD_Roles();
		$roles->add_roles();
		$roles->add_caps();

		self::$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	public function setUp(): void {
		parent::setUp();

		EDD()->api->revoke_api_key( self::$subscriber_id );
		edd_delete_option( 'api_allow_user_keys' );
	}

	public function tearDown(): void {
		EDD()->api->revoke_api_key( self::$subscriber_id );
		edd_delete_option( 'api_allow_user_keys' );

		unset( $_POST['edd_set_api_key'] );
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * The premise: the actor may edit its own profile, holds no shop capability, and starts with
	 * no key. Without all three, neither case below means anything.
	 */
	public function test_the_actor_is_what_these_cases_assume() {
		wp_set_current_user( self::$subscriber_id );

		$this->assertTrue(
			current_user_can( 'edit_user', self::$subscriber_id ),
			'Every user may edit its own profile; that is the gate the handler currently relies on.'
		);
		$this->assertFalse(
			current_user_can( 'manage_shop_settings' ),
			'The actor must not be able to manage shop settings, or the setting is not what decides this.'
		);
		$this->assertEmpty(
			$this->public_key_for( self::$subscriber_id ),
			'The actor must start with no key.'
		);
		$this->assertFalse(
			(bool) edd_get_option( 'api_allow_user_keys', false ),
			'User keys must be disallowed by default, which is what the refusal case relies on.'
		);
	}

	/**
	 * With user keys disallowed, a submission from the user's own profile is not honored.
	 */
	public function test_no_key_is_issued_when_user_keys_are_disallowed() {
		wp_set_current_user( self::$subscriber_id );

		$_POST['edd_set_api_key'] = 1;
		edd_update_user_api_key( self::$subscriber_id );

		$this->assertEmpty(
			$this->public_key_for( self::$subscriber_id ),
			'No key may be issued while the store disallows user keys.'
		);
	}

	/**
	 * The paired positive: with the setting on, the same submission is honored.
	 */
	public function test_a_key_is_issued_when_user_keys_are_allowed() {
		edd_update_option( 'api_allow_user_keys', '1' );
		wp_set_current_user( self::$subscriber_id );

		$_POST['edd_set_api_key'] = 1;
		edd_update_user_api_key( self::$subscriber_id );

		$this->assertNotEmpty(
			$this->public_key_for( self::$subscriber_id ),
			'A key must still be issued where the store allows user keys.'
		);
	}

	/**
	 * A key already issued can still be cleared by its owner after the store turns the setting off.
	 *
	 * The setting decides who may be given a key, not who may get rid of one, and a user whose key
	 * predates the setting change would otherwise have no way to remove it.
	 */
	public function test_an_existing_key_can_still_be_revoked_when_user_keys_are_disallowed() {
		edd_update_option( 'api_allow_user_keys', '1' );
		wp_set_current_user( self::$subscriber_id );

		$_POST['edd_set_api_key'] = 1;
		edd_update_user_api_key( self::$subscriber_id );

		$this->assertNotEmpty(
			$this->public_key_for( self::$subscriber_id ),
			'Fixture: the user must actually hold a key before the setting is turned off.'
		);

		edd_delete_option( 'api_allow_user_keys' );

		$this->assertEmpty(
			edd_get_option( 'api_allow_user_keys', false ),
			'Fixture: the store must now disallow user keys.'
		);

		$_POST['edd_set_api_key'] = 1;
		edd_update_user_api_key( self::$subscriber_id );

		$this->assertEmpty(
			$this->public_key_for( self::$subscriber_id ),
			'A user must be able to clear a key they already hold once the setting is off.'
		);
	}

	/**
	 * Someone who can manage shop settings is unaffected by the setting, matching the render gate,
	 * which lets them through either way.
	 *
	 * Asserted on the actor's own profile rather than someone else's, because editing another user
	 * needs `edit_users` and multisite additionally refuses it to anyone who is not a super admin,
	 * so a cross-user fixture would be testing WordPress's install type rather than this handler.
	 */
	public function test_a_shop_settings_manager_may_still_issue_a_key() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$this->assertTrue(
			current_user_can( 'manage_shop_settings' ),
			'The actor must hold the capability that bypasses the setting.'
		);
		$this->assertFalse(
			(bool) edd_get_option( 'api_allow_user_keys', false ),
			'The setting must be off, or the capability is not what allows this.'
		);
		$this->assertEmpty( $this->public_key_for( $admin_id ), 'The actor must start with no key.' );

		$_POST['edd_set_api_key'] = 1;
		edd_update_user_api_key( $admin_id );

		$this->assertNotEmpty(
			$this->public_key_for( $admin_id ),
			'A shop settings manager must be able to issue a key regardless of the setting.'
		);

		EDD()->api->revoke_api_key( $admin_id );
	}

	/**
	 * Reads a user's public key out of the inverted meta the API stores it in, where the generated
	 * key is the meta key and the label is the value.
	 *
	 * @param int $user_id The user to read.
	 * @return string
	 */
	private function public_key_for( $user_id ) {
		global $wpdb;

		return (string) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_key FROM {$wpdb->usermeta} WHERE meta_value = 'edd_user_public_key' AND user_id = %d",
				$user_id
			)
		);
	}
}
