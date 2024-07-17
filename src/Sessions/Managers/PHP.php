<?php

namespace EDD\Sessions\Managers;

defined( 'ABSPATH' ) || exit;

/**
 * PHP Session Manager
 *
 * @since 3.3.0
 */
class PHP extends Manager {

	/**
	 * Setup cookie and customer ID.
	 * Legacy method.
	 *
	 * @since 3.3.0
	 */
	public function start() {
		if ( headers_sent() ) {
			return;
		}

		if ( defined( 'PHP_SESSION_ACTIVE' ) && ( session_status() !== PHP_SESSION_ACTIVE ) ) {
			session_start();
		}
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data( string $session_key ) {
		return isset( $_SESSION[ $session_key ] ) && is_array( $_SESSION[ $session_key ] )
			? $_SESSION[ $session_key ]
			: array();
	}

	/**
	 * Gets the logged in user key.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_logged_in_user_key() {
		$user_key = 'edd_' . get_current_user_id();
		if ( is_multisite() ) {
			$user_key .= '_' . get_current_blog_id();
		}

		return $user_key;
	}

	/**
	 * Saves the session data to the database and updates the cache.
	 *
	 * @param string $session_key    The session key.
	 * @param array  $data           The data to save.
	 * @param int    $session_expiry The session expiry time.
	 * @return void
	 */
	public function save( string $session_key, array $data, int $session_expiry ) {
		$_SESSION[ $session_key ] = $data;
	}

	/**
	 * Deletes a session.
	 *
	 * @param string $session_key The session key.
	 * @return void
	 */
	public function delete( string $session_key ) {
		unset( $_SESSION[ $session_key ] );
	}
}
