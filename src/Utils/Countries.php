<?php
/**
 * Class to manage country related actions.
 */
namespace EDD\Utils;

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
	 * @param string $country_code
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
	 * @param string $country_code The ISO Code for the country
	 * @param string $state_code   The ISO Code for the state
	 *
	 * @return string
	 */
	public function get_state_name( $country_code = '', $state_code = '' ) {
		if ( empty( $state_code ) ) {
			return $state_code;
		}
		$states = $this->get_states( $country_code );

		return array_key_exists( $state_code, $states ) ? $states[ $state_code ] : $state_code;
	}
}
