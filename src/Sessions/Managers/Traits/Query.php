<?php

namespace EDD\Sessions\Managers\Traits;

defined( 'ABSPATH' ) || exit;

use EDD\Database\Queries\Session as DB;

/**
 * Query trait
 *
 * @since 3.3.0
 */
trait Query {

	/**
	 * Adds a session to the database.
	 *
	 * @param array $data The array of data to add.
	 * @return false|int
	 */
	protected function add( $data = array() ) {
		if ( empty( $data ) || empty( $data['session_key'] ) ) {
			return false;
		}

		$exists = $this->get_by_key( $data['session_key'] );
		if ( empty( $data['session_value'] ) ) {
			if ( $exists ) {
				return $this->delete( $data['session_key'] );
			}

			return false;
		}

		$data['session_value'] = maybe_serialize( $data['session_value'] );

		if ( $exists ) {
			return $this->update( $exists->session_id, $data );
		}

		$query = new DB();

		return $query->add_item( $data );
	}

	/**
	 * Updates a session in the database.
	 *
	 * @since 3.3.0
	 * @param int   $session_id The ID of the session to update.
	 * @param array $data       The array of data to add.
	 * @return bool
	 */
	protected function update( $session_id, $data = array() ) {
		if ( empty( $session_id ) || empty( $data ) ) {
			return false;
		}
		$query = new DB();

		return $query->update_item( $session_id, $data );
	}

	/**
	 * Gets a session from the database.
	 *
	 * @since 3.3.0
	 * @param string $session_key The value to query by.
	 * @return null|EDD\Sessions\Session
	 */
	protected function get_by_key( $session_key ) {
		// This is necessary because Auto Register looks for this before the component is registered.
		if ( ! edd_get_component( 'session' ) || empty( $session_key ) ) {
			return false;
		}
		$query = new DB();

		return $query->get_item_by( 'session_key', $session_key );
	}

	/**
	 * Deletes the session from the database.
	 *
	 * @since 3.3.0
	 * @param string $session_key The session key to delete.
	 * @return bool
	 */
	public function delete( $session_key ) {
		$session_id = $this->get_by_key( $session_key );
		$query      = new DB();

		return $query->delete_item( $session_id );
	}
}
