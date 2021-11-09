<?php
/**
 * CollectsAddressInformation.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout\Traits;

use EDD\ValueObjects\Address;

trait CollectsAddressInformation {

	/**
	 * Builds an Address object from the supplied data.
	 *
	 * @param array $data
	 *
	 * @return Address
	 * @throws \InvalidArgumentException
	 */
	protected function getUserAddress( array $data ) {
		/*
		 * This is a map of the final key we want (e.g. `line1`) to the array key
		 * it's saved under in the form data (e.g. `card_address`).
		 */
		$map = [
			'line1'       => 'card_address',
			'line2'       => 'card_address_2',
			'city'        => 'card_city',
			'region'      => 'card_state',
			'postal_code' => 'card_zip',
			'country'     => 'billing_country',
		];

		$address = [];

		foreach ( $map as $param => $dataField ) {
			$address[ $param ] = ! empty( $data[ $dataField ] ) ? sanitize_text_field( $data[ $dataField ] ) : '';
		}

		return Address::fromArray( $address );
	}

}
