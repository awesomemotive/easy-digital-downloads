<?php
namespace EDD\Tests\Sessions;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Cookie extends EDD_UnitTestCase {

	public function test_use_cart_cookie_is_true() {
		$this->assertTrue( EDD()->session->use_cart_cookie() );
	}

	public function test_use_cart_cookie_is_false() {
		define( 'EDD_USE_CART_COOKIE', false );
		$this->assertFalse( EDD()->session->use_cart_cookie() );
	}

	public function test_session_cookie_is_valid() {
		$this->set_up_cookie();

		$this->assertIsArray( EDD()->session->get_session_cookie() );
	}

	public function test_session_cookie_empty_session_key_is_false() {
		$this->set_up_cookie( array( 'session_key' => '' ) );

		$this->assertFalse( EDD()->session->get_session_cookie() );
	}

	public function test_session_cookie_invalid_hash_is_false() {
		$this->set_up_cookie( array( 'cookie_hash' => '123' ) );

		$this->assertFalse( EDD()->session->get_session_cookie() );
	}

	private function set_up_cookie( $args = array() ) {
		if ( ! is_user_logged_in() ) {
			edd_log_user_in( 1, 'admin', 'password' );
		}

		$cookie_data = wp_parse_args(
			$args,
			array(
				'session_key'        => (string) get_current_user_id(),
				'session_expiration' => time() + intval( EDD()->session->set_expiration_time() ),
				'session_expiring'   => time() + intval( EDD()->session->set_expiration_time() - HOUR_IN_SECONDS ),
			)
		);
		if ( empty( $cookie_data['cookie_hash'] ) ) {
			$to_hash                    = $cookie_data['session_key'] . '|' . $cookie_data['session_expiration'];
			$cookie_data['cookie_hash'] = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		}
		$cookie_value    = implode( '||', array_values( $cookie_data ) );
		$key             = 'edd_session_' . COOKIEHASH;
		$_COOKIE[ $key ] = $cookie_value;
	}
}
