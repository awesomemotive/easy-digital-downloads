<?php
/**
 * Utility class for handling addresses.
 *
 * @package     EDD\Utils
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Address utility class.
 *
 * @since 3.5.0
 */
class Address {

	/**
	 * Formats an address for display.
	 *
	 * @since 3.5.0
	 * @param array|\EDD\Orders\Order_Address $address Address to format.
	 * @return string
	 */
	public static function format_for_display( $address ) {

		$address        = self::parse_address( $address );
		$address_lines  = array( $address['line1'], $address['line2'] );
		$city_zip_state = array( $address['city'], $address['zip'] );

		if ( $address['country'] && $address['state'] ) {
			$city_zip_state[] = edd_get_state_name( $address['country'], $address['state'] );
		}
		$address_lines[] = implode( ' ', array_filter( $city_zip_state ) );

		if ( $address['country'] ) {
			$address_lines[] = edd_get_country_name( $address['country'] );
		}

		$formatted_address = implode( "\n", array_filter( $address_lines ) );

		return apply_filters( 'edd_vat_address_format', $formatted_address, $address );
	}

	/**
	 * Parses an address.
	 *
	 * @since 3.5.0
	 * @param array|\EDD\Orders\Order_Address $address Address to parse.
	 * @return array
	 */
	private static function parse_address( $address ) {
		if ( $address instanceof \EDD\Orders\Order_Address ) {
			$address = array(
				'line1'   => $address->address,
				'line2'   => $address->address2,
				'city'    => $address->city,
				'zip'     => $address->postal_code,
				'country' => $address->country,
				'state'   => $address->region,
			);
		}

		return wp_parse_args(
			$address,
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'zip'     => '',
				'country' => '',
			)
		);
	}
}
