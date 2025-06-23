<?php
/**
 * Address Helper Tests
 *
 * @package   EDD\Tests\Gateways\Square\Helpers
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.4.0
 */

namespace EDD\Tests\Gateways\Square\Helpers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Square\Helpers\Address as AddressHelper;

class Address extends EDD_UnitTestCase {

	public function test_format_address() {
		$address = array(
			'line1' => '123 Main St',
			'line2' => 'Apt 4B',
			'city' => 'Anytown',
			'state' => 'CA',
			'zip' => '12345',
			'country' => 'US',
			'first_name' => 'John',
			'last_name' => 'Doe',
		);

		$expected_address = array(
			'addressLine1' => '123 Main St',
			'addressLine2' => 'Apt 4B',
			'locality' => 'Anytown',
			'administrativeDistrictLevel1' => 'CA',
			'postalCode' => '12345',
			'country' => 'US',
			'firstName' => 'John',
			'lastName' => 'Doe',
		);

		$formatted_address = AddressHelper::format_address( $address );

		$this->assertIsArray( $formatted_address );
		$this->assertEquals( $expected_address, $formatted_address );
	}

	public function test_build_address_object() {
		$address = array(
			'line1' => '123 Main St',
			'line2' => 'Apt 4B',
			'city' => 'Anytown',
			'state' => 'CA',
			'zip' => '12345',
			'country' => 'US',
			'first_name' => 'John',
			'last_name' => 'Doe',
		);

		$expected_address = new \EDD\Vendor\Square\Models\Address();
		$expected_address->setAddressLine1( '123 Main St' );
		$expected_address->setAddressLine2( 'Apt 4B' );
		$expected_address->setLocality( 'Anytown' );
		$expected_address->setAdministrativeDistrictLevel1( 'CA' );
		$expected_address->setPostalCode( '12345' );
		$expected_address->setCountry( 'US' );
		$expected_address->setFirstName( 'John' );
		$expected_address->setLastName( 'Doe' );

		$address_object = AddressHelper::build_address_object( $address );

		$this->assertInstanceOf( \EDD\Vendor\Square\Models\Address::class, $address_object );
		$this->assertEquals( $expected_address, $address_object );
	}


	public function test_build_address_object_partial() {
		$address = array(
			'zip' => '12345',
			'country' => 'US',
			'first_name' => 'John',
			'last_name' => 'Doe',
		);

		$expected_address = new \EDD\Vendor\Square\Models\Address();
		$expected_address->setPostalCode( '12345' );
		$expected_address->setCountry( 'US' );
		$expected_address->setFirstName( 'John' );
		$expected_address->setLastName( 'Doe' );

		$address_object = AddressHelper::build_address_object( $address );

		$this->assertInstanceOf( \EDD\Vendor\Square\Models\Address::class, $address_object );
		$this->assertEquals( $expected_address, $address_object );
	}
}
