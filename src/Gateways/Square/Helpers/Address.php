<?php
/**
 * Address helper for the Square integration.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Vendor\Square\Models\Address as SquareAddress;

/**
 * Address helper for the Square integration.
 *
 * @since 3.4.0
 */
class Address {

	/**
	 * The address fields to map from EDD to Square.
	 *
	 * <key> => <value>
	 *
	 * <key> is the EDD address field.
	 * <value> is the Square address field.
	 *
	 * @var array
	 */
	protected static $address_field_map = array(
		'line1'      => 'addressLine1',
		'line2'      => 'addressLine2',
		'city'       => 'locality',
		'state'      => 'administrativeDistrictLevel1',
		'zip'        => 'postalCode',
		'country'    => 'country',
		'first_name' => 'firstName',
		'last_name'  => 'lastName',
	);

	/**
	 * Get the address object for the Square API.
	 *
	 * @since 3.4.0
	 *
	 * @param array $address The address to format from EDD.
	 *
	 * @return SquareAddress The address object.
	 */
	public static function build_address_object( $address ) {
		$formatted_address = self::format_address( $address );

		$square_address = new SquareAddress();
		foreach ( $formatted_address as $key => $value ) {
			// See if the 'set<field>' method exists, converting it to ucfirst.
			$method = 'set' . ucfirst( $key );
			if ( method_exists( $square_address, $method ) ) {
				$square_address->$method( $value );
			}
		}

		return $square_address;
	}

	/**
	 * Format the address for the Square API.
	 *
	 * @since 3.4.0
	 *
	 * @param array $address The address to format from EDD.
	 *
	 * @return array The formatted address.
	 */
	public static function format_address( $address ) {
		$formatted_address = array();

		foreach ( $address as $key => $value ) {
			if ( ! array_key_exists( $key, self::$address_field_map ) ) {
				continue;
			}

			if ( 'country' === $key ) {
				$value = self::get_country( $value );
			}

			$formatted_address[ self::$address_field_map[ $key ] ] = $value;
		}

		return $formatted_address;
	}

	/**
	 * Get the country code for the Square API.
	 *
	 * @since 3.4.0
	 *
	 * @param string $country_code The country code to format.
	 *
	 * @return string The country code.
	 */
	protected static function get_country( $country_code ) {
		return strtoupper( $country_code );
	}
}
