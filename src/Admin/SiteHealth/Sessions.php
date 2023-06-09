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
			'edd_use_sessions' => array(
				'label' => 'EDD Use Sessions',
				'value' => defined( 'EDD_USE_PHP_SESSIONS' ) && EDD_USE_PHP_SESSIONS ? 'Enforced' : ( EDD()->session->use_php_sessions() ? 'Enabled' : 'Disabled' ),
			),
			'session_enabled'  => array(
				'label' => 'Session',
				'value' => isset( $_SESSION ) ? 'Enabled' : 'Disabled',
			),
		);

		$session_data = $this->get_session_data();

		return $session_data ? array_merge( $data, $session_data ) : $data;
	}

	/**
	 * Gets the data from the $_SESSION global.
	 *
	 * @since 3.1.2
	 * @return false|array
	 */
	private function get_session_data() {
		if ( ! isset( $_SESSION ) ) {
			return false;
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
}
