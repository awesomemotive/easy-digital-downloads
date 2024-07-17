<?php

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Cookies Class
 *
 * @since 3.3.0
 */
class Cookies {

	/**
	 * Helper function to set a cookie.
	 * If the value is empty and the expiration is not null, the cookie will be deleted.
	 *
	 * @since 3.3.0
	 * @param string $cookie     The cookie name.
	 * @param string $value      The cookie value.
	 * @param int    $expiration The expiration timestamp. Use `expired` to set the cookie to expire immediately.
	 * @return bool
	 */
	public static function set( string $cookie, string $value = '', int $expiration = null ) {
		if ( headers_sent() ) {
			return false;
		}
		if ( empty( $value ) && ! is_null( $expiration ) ) {
			$expiration = null;
		}
		$cookie_options = self::get_options( $expiration );

		// We want to create a new cookie.
		if ( $expiration ) {
			// If the cookie is already set and the value is the same, we don't need to do anything.
			if ( isset( $_COOKIE[ $cookie ] ) && $_COOKIE[ $cookie ] === $value ) {
				return true;
			}

			return setcookie( $cookie, $value, $cookie_options );
		}

		// We are expiring/deleting a cookie.
		if ( isset( $_COOKIE[ $cookie ] ) ) {
			$success = setcookie( $cookie, '', $cookie_options );
			unset( $_COOKIE[ $cookie ] );

			return $success;
		}

		return false;
	}

	/**
	 * Gets the cookie options.
	 *
	 * @since 3.3.0
	 * @param null|int $expiration The expiration timestamp. Use null to set the cookie to expire immediately.
	 * @return array
	 */
	private static function get_options( $expiration ) {
		return array(
			'expires'  => self::get_expiration( $expiration ),
			'path'     => COOKIEPATH,
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		);
	}

	/**
	 * Retrieves the expiration time for a cookie.
	 *
	 * @since 3.3.0
	 * @param int|string $expiration The expiration time for the cookie.
	 * @return int The calculated expiration time for the cookie.
	 */
	private static function get_expiration( $expiration ) {
		if ( is_null( $expiration ) ) {
			$expiration = time() - 3600;
		}

		return $expiration;
	}
}
