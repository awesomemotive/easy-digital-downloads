<?php

namespace EDD\Sessions\Managers;

defined( 'ABSPATH' ) || exit;

/**
 * Manager Class
 *
 * @since 3.3.0
 */
abstract class Manager {

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	abstract public function get_session_data( string $session_key );

	/**
	 * Gets the logged in user key.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	abstract public function get_logged_in_user_key();

	/**
	 * Saves the session data to the database and updates the cache.
	 *
	 * @param string $session_key The session key.
	 * @param array  $data        The data to save.
	 * @return void
	 */
	abstract public function save( string $session_key, array $data, int $session_expiry );

	/**
	 * Starts a session. This is intentionally empty because not all session managers need to start a session.
	 *
	 * @return void
	 */
	public function start() {}

	/**
	 * Gets a session.
	 *
	 * @param string $session_key The session key.
	 * @return false|EDD\Sessions\Session
	 */
	public function get_session( $session_key ) {
		return false;
	}
}
