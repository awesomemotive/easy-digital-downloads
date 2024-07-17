<?php
/**
 * Gets the session data for the Site Health data.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Loads session data into Site Health.
 *
 * @since 3.1.2
 */
class Sessions {

	/**
	 * Gets the session section data for Site Health.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Sessions', 'easy-digital-downloads' ),
			'fields' => $this->get_data(),
		);
	}

	/**
	 * Gets the array of session data.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_data() {
		$data = array(
			'session_enabled' => array(
				'label' => 'PHP Session Enabled',
				'value' => defined( PHP_SESSION_DISABLED ) && PHP_SESSION_DISABLED !== session_status() ? 'Enabled' : 'Disabled',
			),
			'session_type'    => array(
				'label' => 'Session Type',
				'value' => edd_get_component( 'session' ) ? 'Database' : 'PHP',
			),
		);

		$database_sessions = $this->get_sessions();
		$session_data      = $this->get_session_data();

		return array_merge( $data, $database_sessions, $session_data );
	}

	/**
	 * Gets the data from the $_SESSION global.
	 *
	 * @since 3.1.2
	 * @return false|array
	 */
	private function get_session_data() {
		if ( ! isset( $_SESSION ) ) {
			return array();
		}

		return array(
			'name'             => array(
				'label' => 'Session Name',
				'value' => ini_get( 'session.name' ),
			),
			'cookie_path'      => array(
				'label' => 'Cookie Path',
				'value' => ini_get( 'session.cookie_path' ),
			),
			'save_path'        => array(
				'label' => 'Save Path',
				'value' => ini_get( 'session.save_path' ),
			),
			'use_cookies'      => array(
				'label' => 'Use Cookies',
				'value' => ini_get( 'session.use_cookies' ) ? 'On' : 'Off',
			),
			'use_only_cookies' => array(
				'label' => 'Use Only Cookies',
				'value' => ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off',
			),
		);
	}

	/**
	 * Gets the number and status of sessions.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private function get_sessions() {
		if ( ! edd_get_component( 'session' ) ) {
			return array();
		}

		$query   = new \EDD\Database\Queries\Session();
		$total   = $query->query(
			array(
				'count' => true,
			)
		);
		$expired = $query->query(
			array(
				'count'                   => true,
				'session_expiry__compare' => array(
					'relation' => 'AND',
					array(
						'value'   => time(),
						'compare' => '<',
					),
				),
			)
		);

		return array(
			'sessions_all'     => array(
				'label' => 'All Sessions',
				'value' => $total,
			),
			'sessions_active'  => array(
				'label' => 'Active Sessions',
				'value' => $total - $expired,
			),
			'sessions_expired' => array(
				'label' => 'Expired Sessions',
				'value' => $expired,
			),
		);
	}
}
