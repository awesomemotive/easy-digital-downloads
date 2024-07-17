<?php

namespace EDD\Sessions\Managers;

defined( 'ABSPATH' ) || exit;

use EDD\Sessions\Session;
use EDD\Cron;

/**
 * Database Session Manager
 *
 * @since 3.3.0
 */
class Database extends Manager {

	use Traits\Query;

	/**
	 * The session.
	 *
	 * @since 3.3.0
	 * @var Session
	 */
	private $session;

	/**
	 * Database constructor.
	 */
	public function __construct() {
		wp_cache_add_non_persistent_groups( 'edd-sessions-by-session_key' );
		add_action( 'edd_setup_components', array( $this, 'setup_sessions' ) );

		// Schedule the cleanup of expired sessions.
		add_filter( 'edd_cron_schedules', array( $this, 'add_cron_schedule' ) );
		add_filter( 'edd_cron_events', array( $this, 'add_cron_event' ) );
		add_filter( 'edd_cron_components', array( $this, 'add_cron_component' ) );
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data( string $session_key ) {
		$session = $this->get_session( $session_key );

		return $session instanceof Session ? $session->session_value : array();
	}

	/**
	 * Returns the session.
	 *
	 * @since 3.3.0
	 * @param string $session_key Customer ID.
	 * @return Session
	 */
	public function get_session( $session_key ) {

		if ( is_null( $this->session ) ) {
			$session = $this->get_by_key( $session_key );

			if ( ! $session ) {
				$session = new Session();
			}

			$this->session = $session;
		}

		return $this->session;
	}

	/**
	 * Gets the logged in user key.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_logged_in_user_key() {
		return strval( get_current_user_id() );
	}

	/**
	 * Saves the session data to the database and updates the cache.
	 *
	 * @param string $session_key The session key.
	 * @param array  $data        The data to save.
	 * @return void
	 */
	public function save( string $session_key, array $data, int $session_expiry ) {
		$data = array(
			'session_key'    => $session_key,
			'session_value'  => $data,
			'session_expiry' => $session_expiry,
		);

		// Add the session to the database.
		$this->add( $data );
	}

	/**
	 * Adds our custom cron schedule.
	 *
	 * @since 3.3.0
	 *
	 * @param array $schedules The existing cron schedules.
	 *
	 * @return array
	 */
	public function add_cron_schedule( $schedules ) {
		$schedules[] = new Cron\Schedules\SessionCleanup();

		return $schedules;
	}

	/**
	 * Adds our custom cron event.
	 *
	 * @since 3.3.0
	 *
	 * @param array $events The existing cron events.
	 *
	 * @return array
	 */
	public function add_cron_event( $events ) {
		$events[] = new Cron\Events\SessionCleanup();

		return $events;
	}

	/**
	 * Adds our custom cron component.
	 *
	 * @since 3.3.0
	 *
	 * @param array $components The existing cron components.
	 *
	 * @return array
	 */
	public function add_cron_component( $components ) {
		$components[] = new Cron\Components\Sessions();

		return $components;
	}

	/**
	 * Registers the session component.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function setup_sessions() {
		edd_register_component(
			'session',
			array(
				'schema' => '\\EDD\\Database\\Schemas\\Sessions',
				'table'  => '\\EDD\\Database\\Tables\\Sessions',
				'query'  => '\\EDD\\Database\\Queries\\Session',
				'object' => '\\EDD\\Sessions\\Session',
				'meta'   => false,
			)
		);
	}
}
