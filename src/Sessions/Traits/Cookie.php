<?php

namespace EDD\Sessions\Traits;

defined( 'ABSPATH' ) || exit;

trait Cookie {

	/**
	 * The cookie name.
	 *
	 * @var string
	 */
	private $cookie = 'edd_session_' . COOKIEHASH;

	/**
	 * True when the cookie exists.
	 *
	 * @var bool
	 */
	private $has_cookie = false;

	/**
	 * Get the session cookie, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		$cookie_value = isset( $_COOKIE[ $this->cookie ] ) ? wp_unslash( $_COOKIE[ $this->cookie ] ) : false;
		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}

		$parsed_cookie = explode( '||', $cookie_value );
		if ( count( $parsed_cookie ) < 4 ) {
			return false;
		}

		list( $session_key, $session_expiration, $session_expiring, $cookie_hash ) = $parsed_cookie;

		if ( empty( $session_key ) ) {
			edd_debug_log( 'Session key is missing.' );
			return false;
		}

		// Validate hash.
		$to_hash = $session_key . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			edd_debug_log( 'Session cookie hash mismatch for: ' . $session_key );
			return false;
		}

		return array(
			'session_key'        => $session_key,
			'session_expiration' => $session_expiration,
			'session_expiring'   => $session_expiring,
		);
	}

	/**
	 * Sets the session cookie.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_session_cookie() {
		if ( empty( $this->session_key ) ) {
			$this->get_session_key();
		}
		$expiry           = $this->get_session_expiry();
		$expiring         = $this->get_session_expiring();
		$to_hash          = $this->session_key . '|' . $expiry;
		$cookie_hash      = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		$cookie_value     = $this->session_key . '||' . $expiry . '||' . $expiring . '||' . $cookie_hash;
		$this->has_cookie = true;

		edd_debug_log( 'Setting session cookie for: ' . $this->session_key );
		\EDD\Utils\Cookies::set( $this->cookie, $cookie_value, $expiry );
	}

	/**
	 * Set a cookie to identify whether the cart is empty or not.
	 * This is for hosts and caching plugins to identify if caching should be disabled.
	 * Legacy method.
	 *
	 * @param bool $set Whether to set the cookie. False will set an expired cookie.
	 * @return bool
	 */
	public function set_cart_cookie( $set = true ) {
		if ( ! $this->use_cart_cookie() ) {
			return false;
		}

		if ( ! $set ) {
			return \EDD\Utils\Cookies::set( 'edd_items_in_cart' );
		}

		return \EDD\Utils\Cookies::set( 'edd_items_in_cart', '1', time() + 30 * MINUTE_IN_SECONDS );
	}

	/**
	 * Checks if session cookie is expired, or belongs to a logged out user.
	 *
	 * @since 3.3.0
	 * @return bool Whether session cookie is valid.
	 */
	private function is_session_cookie_valid() {
		// If session is expired, session cookie is invalid.
		if ( time() > $this->get_session_expiry() ) {
			return false;
		}

		// If user has logged out, session cookie is invalid.
		if ( ! is_user_logged_in() && ! $this->is_guest( $this->session_key ) ) {
			return false;
		}

		// Session from a different user is not valid. (Although from a guest user will be valid).
		if (
			is_user_logged_in() &&
			! $this->is_guest( $this->session_key ) &&
			$this->get_logged_in_user_key() !== $this->session_key
		) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if this is an auto-generated customer ID.
	 *
	 * @since 3.3.0
	 * @param string|int $session_key Customer ID to check.
	 * @return bool Whether customer ID is randomly generated.
	 */
	private function is_guest( $session_key ) {
		$session_key = strval( $session_key );

		if ( empty( $session_key ) ) {
			return true;
		}

		return substr( $session_key, 0, 2 ) === $this->guest_prefix;
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	private function has_session() {
		return isset( $_COOKIE[ $this->cookie ] ) || $this->has_cookie || is_user_logged_in(); // @codingStandardsIgnoreLine.
	}
}
