<?php
/**
 * Country Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


/**
 * Get Shop Base Country
 *
 * @since 1.6
 * @return string $country The two letter country code for the shop's base country
 */
function edd_get_shop_country() {
	$country = edd_get_option( 'base_country', 'US' );

	return apply_filters( 'edd_shop_country', $country );
}

/**
 * Get Shop Base State
 *
 * @since 1.6
 * @return string $state The shop's base state name
 */
function edd_get_shop_state() {
	$state = edd_get_option( 'base_state', false );

	return apply_filters( 'edd_shop_state', $state );
}

/**
 * Get Shop States
 *
 * @since 1.6
 *
 * @param string $country
 * @return array A list of states for the selected country
 */
function edd_get_shop_states( $country = null ) {
	if ( empty( $country ) ) {
		$country = edd_get_shop_country();
	}

	$countries = new EDD\Utils\Countries();
	$states    = $countries->get_states( $country );

	return apply_filters( 'edd_shop_states', $states, $country );
}

/**
 * Get Country List
 *
 * @since 1.0
 * @return array $countries A list of the available countries
 */
function edd_get_country_list() {
	return apply_filters( 'edd_countries', include EDD_PLUGIN_DIR . 'i18n/countries.php' );
}

/**
 * Get States List
 *
 * @since       1.2
 * @return      array
 */
function edd_get_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_us_states', $countries->get_states( 'US' ) );
}

/**
 * Get Angola States
 *
 * @since 2.8.5
 * @return array $states A list of states
 */
function edd_get_angola_provinces_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_angola_provinces', $countries->get_states( 'AO' ) );
}

/**
 * Get Provinces List
 *
 * @since       1.2
 * @return      array
 */
function edd_get_provinces_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_canada_provinces', $countries->get_states( 'CA' ) );
}

/**
 * Get Australian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_australian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_australian_states', $countries->get_states( 'AU' ) );
}

/**
 * Get Bangladeshi States (districts)
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_bangladeshi_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_bangladeshi_states', $countries->get_states( 'BD' ) );
}

/**
 * Get Brazil States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_brazil_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_brazil_states', $countries->get_states( 'BR' ) );
}

/**
 * Get Bulgarian States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_bulgarian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_bulgarian_states', $countries->get_states( 'BG' ) );
}

/**
 * Get Hong Kong States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_hong_kong_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_hong_kong_states', $countries->get_states( 'HK' ) );
}

/**
 * Get Hungary States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_hungary_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_hungary_states', $countries->get_states( 'HU' ) );
}

/**
 * Get Japanese States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_japanese_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_japanese_states', $countries->get_states( 'JP' ) );
}

/**
 * Get Chinese States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_chinese_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_chinese_states', $countries->get_states( 'CN' ) );
}

/**
 * Get United Kingdom States
 *
 * @since 2.9
 * @return array $states A list of states
 */
function edd_get_united_kingdom_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_united_kingdom_states', $countries->get_states( 'GB' ) );
}

/**
 * Get New Zealand States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_new_zealand_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_new_zealand_states', $countries->get_states( 'NZ' ) );
}

/**
 * Get Peruvian States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_peruvian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_peruvian_states', $countries->get_states( 'PE' ) );
}

/**
 * Get Indonesian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_indonesian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_indonesia_states', $countries->get_states( 'ID' ) );
}

/**
 * Get Indian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_indian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_indian_states', $countries->get_states( 'IN' ) );
}

/**
 * Get Iranian States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_iranian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_iranian_states', $countries->get_states( 'IR' ) );
}

/**
 * Get Italian Provinces
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_italian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_italian_states', $countries->get_states( 'IT' ) );
}

/**
 * Get Malaysian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_malaysian_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_malaysian_states', $countries->get_states( 'MY' ) );
}

/**
 * Get Mexican States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_mexican_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_mexican_states', $countries->get_states( 'MX' ) );
}

/**
 * Get Nepalese States (Districts)
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_nepalese_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_nepalese_states', $countries->get_states( 'NP' ) );
}

/**
 * Get South African States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_south_african_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_south_african_states', $countries->get_states( 'ZA' ) );
}

/**
 * Get Thailand States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_thailand_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_thailand_states', $countries->get_states( 'TH' ) );
}

/**
 * Get Turkey States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_turkey_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_turkey_states', $countries->get_states( 'TR' ) );
}

/**
 * Get Spain States
 *
 * @since 2.2
 * @return array $states A list of states
 */
function edd_get_spain_states_list() {
	$countries = new EDD\Utils\Countries();

	return apply_filters( 'edd_spain_states', $countries->get_states( 'ES' ) );
}

/**
 * Returns a list of Netherland's provinces.
 *
 * @since 3.0
 * @return array $states A list of Netherland's provinces.
 */
function edd_get_netherlands_provinces_list() {
	$countries = new EDD\Utils\Countries();

	/**
	 * Filters the list of Netherland's provinces.
	 *
	 * @since 3.0
	 *
	 * @param array $states A list of Netherland's provinces.
	 */
	return apply_filters( 'edd_netherlands_provinces', $countries->get_states( 'NL' ) );
}

/**
 * Given a country code, return the country name
 *
 * @since 2.8.7
 * @param string $country_code The ISO Code for the country
 *
 * @return string
 */
function edd_get_country_name( $country_code = '' ) {
	$country_list = edd_get_country_list();
	$country_name = isset( $country_list[ $country_code ] ) ? $country_list[ $country_code ] : $country_code;

	return apply_filters( 'edd_get_country_name', $country_name, $country_code );
}

/**
 * Given a country and state code, return the state name
 *
 * @since 2.9
 * @param string $country_code The ISO Code for the country
 * @param string $state_code The ISO Code for the state
 *
 * @return string
 */
function edd_get_state_name( $country_code = '', $state_code = '' ) {
	if ( empty( $country_code ) ) {
		$country_code = edd_get_shop_country();
	}
	$countries  = new EDD\Utils\Countries();
	$state_name = $countries->get_state_name( $country_code, $state_code );

	return apply_filters( 'edd_get_state_name', $state_name, $state_code );
}
