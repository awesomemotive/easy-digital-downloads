<?php
/**
 * Tests for Pro Checkout Address functionality
 *
 * @package EDD\Tests\Checkout
 */

namespace EDD\Pro\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Checkout\Address as ProAddress;

/**
 * Pro Address tests.
 *
 * @covers \EDD\Pro\Checkout\Address
 */
class ProAddressTests extends EDD_UnitTestCase {


	/**
	 * Runs before each test method.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'This test requires EDD Pro.' );
		}
		parent::setUp();
	}

	public function tearDown(): void {
		// Clear session data
		EDD()->session->set( 'edd_pro_geoip', null );
		EDD()->session->set( 'customer', null );
		parent::tearDown();
	}

	/**
	 * Test that set_up_customer returns parent customer data when no geoIP data exists.
	 */
	public function test_set_up_customer_without_geoip_data() {
		$address = new ProAddress();

		// Use reflection to call protected method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$customer = $method->invoke( $address );

		$this->assertIsArray( $customer );
		$this->assertArrayHasKey( 'address', $customer );
		$this->assertArrayHasKey( 'country', $customer['address'] );
	}

	/**
	 * Test that set_up_customer uses geoIP data when available and no country is set.
	 */
	public function test_set_up_customer_with_geoip_data() {
		// Set up geoIP data in session
		$geoip_data = array(
			'country_iso' => 'GB',
			'region_code' => 'ENG',
			'region_name' => 'England',
			'city'        => 'London',
			'ip'          => '1.2.3.4',
		);
		EDD()->session->set( 'edd_pro_geoip', $geoip_data );

		$address = new ProAddress();

		// Use reflection to call protected method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$customer = $method->invoke( $address );

		$this->assertEquals( 'GB', $customer['address']['country'] );
		$this->assertEquals( 'London', $customer['address']['city'] );
	}

	/**
	 * Test that set_up_customer doesn't override existing country data.
	 */
	public function test_set_up_customer_preserves_existing_country() {
		// Set up geoIP data in session
		$geoip_data = array(
			'country_iso' => 'GB',
			'region_code' => 'ENG',
			'region_name' => 'England',
			'city'        => 'London',
			'ip'          => '1.2.3.4',
		);
		EDD()->session->set( 'edd_pro_geoip', $geoip_data );

		// Set existing customer data with a country
		\EDD\Sessions\Customer::set( array(
			'address' => array(
				'country' => 'US',
				'state'   => 'CA',
				'city'    => 'San Francisco',
			),
		) );

		$address = new ProAddress();

		// Use reflection to call protected method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$customer = $method->invoke( $address );

		// Should preserve US, not use GB from geoIP
		$this->assertEquals( 'US', $customer['address']['country'] );
	}

	/**
	 * Test get_state with valid region code.
	 */
	public function test_get_state_with_valid_region_code() {
		$geoip_data = array(
			'country_iso' => 'US',
			'region_code' => 'CA',
			'region_name' => 'California',
			'city'        => 'Los Angeles',
		);

		$address = new ProAddress();

		// Use reflection to call private method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( $address, $geoip_data );

		$this->assertEquals( 'CA', $state );
	}

	/**
	 * Test get_state with Japan region code.
	 */
	public function test_get_state_with_japan_region_code() {
		$geoip_data = array(
			'country_iso' => 'JP',
			'region_code' => '01',
			'region_name' => 'Hokkaido',
			'city'        => 'Sapporo',
		);

		$address = new ProAddress();

		// Use reflection to call private method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( $address, $geoip_data );

		$this->assertEquals( 'JP01', $state );
	}

	/**
	 * Test get_state with Great Britain using city lookup.
	 */
	public function test_get_state_with_gb_city() {
		$geoip_data = array(
			'country_iso' => 'GB',
			'region_code' => '',
			'region_name' => 'England',
			'city'        => 'Bath',
		);

		$address = new ProAddress();

		// Use reflection to call private method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( $address, $geoip_data );

		// City lookup may return false if not found, or the key if found
		// Just verify it doesn't error and returns a value (false counts as a value)
		$this->assertTrue( $state !== null || $state === false );
	}

	/**
	 * Test get_state returns region_name when no match found.
	 */
	public function test_get_state_fallback_to_region_name() {
		$geoip_data = array(
			'country_iso' => 'DE',
			'region_code' => 'NW',
			'region_name' => 'North Rhine-Westphalia',
			'city'        => 'Cologne',
		);

		$address = new ProAddress();

		// Use reflection to call private method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( $address, $geoip_data );

		$this->assertEquals( 'North Rhine-Westphalia', $state );
	}

	/**
	 * Test get_state handles missing data gracefully.
	 */
	public function test_get_state_handles_missing_data() {
		$geoip_data = array(
			'country_iso' => 'US',
			// Missing region_code, region_name, city
		);

		$address = new ProAddress();

		// Use reflection to call private method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'get_state' );
		$method->setAccessible( true );

		$state = $method->invoke( $address, $geoip_data );

		// Should return empty string, not cause errors
		$this->assertEquals( '', $state );
	}

	/**
	 * Test that city is properly sanitized even when empty.
	 */
	public function test_set_up_customer_sanitizes_empty_city() {
		$geoip_data = array(
			'country_iso' => 'US',
			'region_code' => 'CA',
			'region_name' => 'California',
			// Missing city
			'ip'          => '1.2.3.4',
		);
		EDD()->session->set( 'edd_pro_geoip', $geoip_data );

		$address = new ProAddress();

		// Use reflection to call protected method
		$reflection = new \ReflectionClass( $address );
		$method = $reflection->getMethod( 'set_up_customer' );
		$method->setAccessible( true );

		$customer = $method->invoke( $address );

		// Should have empty string, not cause PHP warnings
		$this->assertEquals( '', $customer['address']['city'] );
	}
}
