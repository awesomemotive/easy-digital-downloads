<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD\Vendor\Stripe\Stripe;
use \EDD_Stripe_API;

/**
 * Tests for EDD_Stripe_Api class.
 *
 * @covers EDD_Stripe_API
 */
class API extends EDD_UnitTestCase {

	public function setUp(): void {
		edd_update_option( 'test_secret_key', 'sk_test_123' );
		edd_update_option( 'live_secret_key', 'sk_live_123' );
	}

	public function test_set_api_key_test() {
		edd_update_option( 'test_mode', true );
		$api = new EDD_Stripe_API;
		$api->set_api_key();

		$this->assertSame( 'sk_test_123', Stripe::$apiKey );
	}

	public function test_set_api_key_live() {
		edd_update_option( 'test_mode', false );
		$api = new EDD_Stripe_API;
		$api->set_api_key();

		$this->assertSame( 'sk_live_123', Stripe::$apiKey );
	}

	public function test_set_app_info() {
		$api = new EDD_Stripe_API();
		$api->set_app_info();

		$appinfo = array(
			'WordPress Easy Digital Downloads - Stripe',
			EDD_VERSION,
			esc_url( site_url() ),
			EDD_STRIPE_PARTNER_ID,
		);

		$this->assertEqualSets( $appinfo, Stripe::$appInfo );
	}

	public function test_set_api_version() {
		$api = new EDD_Stripe_API;
		$api->set_api_version();

		$this->assertSame( EDD_STRIPE_API_VERSION, Stripe::$apiVersion );
	}

	public function test_no_conflict() {
		// Mock some other plugin making requests.
		Stripe::setApiVersion( '123' );

		$api = new EDD_Stripe_API;
		$api->set_api_version();

		$this->assertSame( EDD_STRIPE_API_VERSION, Stripe::$apiVersion );
	}

	/**
	 * @covers edds_api_request
	 */
	public function test_request() {
		$this->expectException( \EDD\Vendor\Stripe\Exception\AuthenticationException::class );

		$request = edds_api_request( 'Customer', 'retrieve', 123 );
	}

	/**
	 * @covers edds_api_request
	 */
	public function test_invalid_request() {
		$this->expectException( \EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found::class );

		$request = edds_api_request( 'UnknownObject', 'retrieve', 123 );
	}
}
