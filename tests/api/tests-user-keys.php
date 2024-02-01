<?php
namespace EDD\Tests\API;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * API List Table tests.
 *
 * @group api
 * @group admin
 */
class User_Keys extends EDD_UnitTestCase {

	/**
	 * @var int
	 */
	protected static $user_id;

	/**
	 * @var string
	 */
	protected static $api_key;

	/**
	 * @var string
	 */
	protected static $api_secret;

	/**
	 * @var string
	 */
	protected static $api_token;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		global $wpdb;

		// Create a user.
		self::$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );

		// Give the user the required caps.
		$user = new \WP_User( self::$user_id );
		$user->add_cap( 'view_shop_reports' );
		$user->add_cap( 'view_shop_sensitive_data' );
		$user->add_cap( 'manage_shop_discounts' );

		$roles = new \EDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

		// Generate an API Key for the user.
		$_POST['edd_set_api_key'] = 1;
		EDD()->api->generate_api_key( self::$user_id );
		self::$api_key    = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'edd_user_public_key' AND user_id = %d", self::$user_id ) );
		self::$api_secret = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'edd_user_secret_key' AND user_id = %d", self::$user_id ) );
		self::$api_token  = hash( 'md5', self::$api_secret . self::$api_key );
	}

	public function test_get_user_with_key() {
		$this->assertEquals( self::$user_id, EDD()->api->get_user( self::$api_key ) );
	}

	public function test_get_user_with_no_key_no_query_var() {
		$this->assertFalse( EDD()->api->get_user() );
	}

	public function test_get_user_with_no_key_has_query_var() {
		global $wp_query;
		$wp_query->query_vars['key'] = self::$api_key;

		$this->assertEquals( self::$user_id, EDD()->api->get_user() );

		unset( $wp_query->query_vars['key'] );
	}

	public function test_get_user_with_no_user() {
		$this->assertFalse( EDD()->api->get_user( 'invalid-key' ) );
	}

	public function test_get_user_public_key() {
		$this->assertEquals( self::$api_key, EDD()->api->get_user_public_key( self::$user_id ) );
	}

	public function test_get_user_public_key_no_user() {
		$this->assertEquals( '', EDD()->api->get_user_public_key() );
	}

	public function test_get_user_secret_key() {
		$this->assertEquals( self::$api_secret, EDD()->api->get_user_secret_key( self::$user_id ) );
	}

	public function test_get_user_secret_key_no_user() {
		$this->assertEquals( '', EDD()->api->get_user_secret_key() );
	}

	public function test_get_token() {
		$this->assertEquals( self::$api_token, EDD()->api->get_token( self::$user_id ) );
	}

	public function test_update_key() {
		$_POST['edd_set_api_key'] = 1;

		EDD()->api->update_key( self::$user_id );

		$user_public = EDD()->api->get_user_public_key( self::$user_id );
		$user_secret = EDD()->api->get_user_secret_key( self::$user_id );

		$this->assertNotEmpty( $user_public );
		$this->assertNotEmpty( $user_secret );

		// Since we've now changed the keys, we need to update the static vars.
		self::$api_key = $user_public;
		self::$api_secret = $user_secret;

		// Backwards compatibilty check for API Keys
		$this->assertEquals( $user_public, get_user_meta( self::$user_id, 'edd_user_public_key', true ) );
		$this->assertEquals( $user_secret, get_user_meta( self::$user_id, 'edd_user_secret_key', true ) );
	}

	public function test_revoke_key_no_user_id() {
		$this->assertFalse( EDD()->api->revoke_api_key() );
	}

	public function test_revoke_key_invalid_user() {
		$this->assertFalse( EDD()->api->revoke_api_key( 999999999 ) );
	}

	public function test_revoke_key_user_without_key() {
		$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );

		$this->assertFalse( EDD()->api->revoke_api_key( $user_id ) );
	}

	public function test_revoke_key() {
		$this->assertTrue( EDD()->api->revoke_api_key( self::$user_id ) );
	}
}
