<?php
/**
 * Address.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\ValueObjects;

class Address {

	public $line1;

	public $line2;

	public $city;

	public $region;

	public $postal_code;

	public $country;

	/**
	 * @param array $array
	 *
	 * @return Address
	 * @throws \InvalidArgumentException
	 */
	public static function fromArray( $array ) {
		$requiredKeys = [
			'line1', 'line2', 'city', 'region', 'postal_code', 'country'
		];

		$array = array_intersect_key( $array, array_flip( $requiredKeys ) );

		if ( empty( $array ) ) {
			throw new \InvalidArgumentException(
				'Missing required address keys: ' . json_encode( $requiredKeys )
			);
		}

		$address = new self();
		foreach ( $array as $key => $value ) {
			$address->{$key} = $value;
		}

		return $address;
	}

}
