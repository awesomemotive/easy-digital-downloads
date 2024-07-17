<?php

namespace EDD\Sessions;

defined( 'ABSPATH' ) || exit;

use EDD\Database\Row;

/**
 * Class Session
 *
 * @since 3.3.0
 * @package EDD\Sessions
 */
class Session extends Row {

	/**
	 * Session ID.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	protected $session_id;

	/**
	 * Session key.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $session_key;

	/**
	 * Session value.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $session_value;

	/**
	 * Session expiry.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	protected $session_expiry;

	/**
	 * Gets the session value, unserialized.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_session_value() {
		$session_value = $this->session_value;
		if ( empty( $session_value ) ) {
			return array();
		}

		return maybe_unserialize( $session_value );
	}
}
