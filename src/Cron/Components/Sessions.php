<?php
/**
 * Session related cron events.
 *
 * @package EDD\Cron\Components
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Queries\Session;

/**
 * Sessions Class
 *
 * @since 3.3.0
 */
class Sessions extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @var string
	 */
	protected static $id = 'sessions';

	/**
	 * Gets the array of subscribed events.
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_cleanup_sessions' => 'remove_expired_sessions',
		);
	}

	/**
	 * Deletes all expired sessions from the database.
	 * This uses Berlin instead of directly querying the database
	 * to make use of Berlin's caching support.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function remove_expired_sessions() {
		$query            = new Session();
		$expired_sessions = $query->query(
			array(
				'number'                  => 500,
				'order'                   => 'ASC',
				'orderby'                 => 'session_expiry',
				'session_expiry__compare' => array(
					'relation' => 'AND',
					array(
						'value'   => time(),
						'compare' => '<',
					),
				),
			)
		);

		if ( empty( $expired_sessions ) ) {
			return;
		}

		foreach ( $expired_sessions as $session ) {
			$query->delete_item( $session->session_id );
		}
	}
}
