<?php
/**
 * Class to manage country related actions.
 *
 * @package EDD
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Countries class.
 */
class Countries {

	/**
	 * Array of states with defined region codes.
	 *
	 * @since 3.1.4
	 * @var array
	 */
	private $states;

	/**
	 * Gets an array of states for a given country code.
	 * If no regions are found then an empty array is returned.
	 *
	 * @since 3.1.4
	 * @param string $country_code The ISO Code for the country.
	 * @return array
	 */
	public function get_states( $country_code = '' ) {
		if ( ! $this->states ) {
			$this->states = include EDD_PLUGIN_DIR . 'i18n/states.php';
		}

		if ( $country_code ) {
			return array_key_exists( $country_code, $this->states ) ? $this->states[ $country_code ] : array();
		}

		return $this->states;
	}

	/**
	 * Given a country and state code, return the state name.
	 *
	 * @since 3.1.4
	 * @param string $country_code The ISO Code for the country.
	 * @param string $state_code   The ISO Code for the state.
	 *
	 * @return string
	 */
	public function get_state_name( $country_code = '', $state_code = '' ) {
		if ( empty( $state_code ) ) {
			return $state_code;
		}

		$states = $this->get_states( $country_code );
		if ( array_key_exists( $state_code, $states ) ) {
			return $states[ $state_code ];
		}

		return 'GB' === $country_code ? $this->maybe_get_legacy_gb_states( $state_code, $country_code ) : $state_code;
	}

	/**
	 * Retrieves the legacy GB states based on the state code and country code.
	 *
	 * @since 3.3.0
	 * @param string $state_code The state code.
	 * @return mixed The legacy GB states if found, otherwise null.
	 */
	private function maybe_get_legacy_gb_states( $state_code ) {
		$states = $this->get_legacy_gb_states();
		if ( array_key_exists( $state_code, $states ) ) {
			return $states[ $state_code ];
		}

		return $state_code;
	}

	/**
	 * Retrieves the legacy states for Great Britain.
	 *
	 * @since 3.3.0
	 * @return array The array of legacy states for Great Britain.
	 */
	private function get_legacy_gb_states() {
		return include EDD_PLUGIN_DIR . 'i18n/states-gb-legacy.php';
	}
}
