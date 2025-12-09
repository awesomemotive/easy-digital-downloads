<?php
/**
 * Tests for Recommendations API
 *
 * @group edd_recommendations
 * @group edd_pro
 */
namespace EDD\Tests\Recommendations;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Recommendations\API as RecommendationsAPI;
use EDD\Pro\Recommendations\Products;

class API extends EDD_UnitTestCase {

	/**
	 * API instance.
	 *
	 * @var API
	 */
	protected $api;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. Recommendations API tests require EDD Pro.' );
		}

		parent::setUp();

		$this->api = new RecommendationsAPI();

		// Set up necessary options for API to work.
		edd_update_option( 'cart_recommendations', true );
		update_site_option( 'edd_pro_license_key', 'test_license_key' );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		edd_delete_option( 'cart_recommendations' );
		delete_site_option( 'edd_pro_license_key' );

		parent::tearDown();
	}

	/**
	 * Test API can be instantiated.
	 */
	public function test_api_initialization() {
		$this->assertInstanceOf( RecommendationsAPI::class, $this->api );
	}

	/**
	 * Test can_make_request returns true with valid setup.
	 */
	public function test_can_make_request_with_valid_setup() {
		$this->assertTrue( $this->api->can_make_request() );
	}

	/**
	 * Test can_make_request returns false without license key.
	 */
	public function test_can_make_request_without_license() {
		delete_site_option( 'edd_pro_license_key' );

		$this->assertFalse( $this->api->can_make_request() );
	}

	/**
	 * Test can_make_request returns false without recommendations enabled.
	 */
	public function test_can_make_request_without_recommendations_enabled() {
		edd_update_option( 'cart_recommendations', false );

		$this->assertFalse( $this->api->can_make_request() );
	}

	/**
	 * Test ingest returns error when no products provided.
	 */
	public function test_ingest_returns_error_with_no_products() {
		$result = $this->api->ingest( array() );

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_error', $result->get_error_code() );
	}

	/**
	 * Test ingest returns error when API unavailable.
	 */
	public function test_ingest_returns_error_when_api_unavailable() {
		delete_site_option( 'edd_pro_license_key' );

		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);
		$result = $this->api->ingest( array( $download_id ) );

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_unavailable', $result->get_error_code() );
	}

	/**
	 * Test get_recommendations returns error when no product IDs provided.
	 */
	public function test_get_recommendations_returns_error_without_product_ids() {
		$result = $this->api->get_recommendations( array() );

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_error', $result->get_error_code() );
	}

	/**
	 * Test get_recommendations returns error when API unavailable.
	 */
	public function test_get_recommendations_returns_error_when_api_unavailable() {
		delete_site_option( 'edd_pro_license_key' );

		$result = $this->api->get_recommendations(
			array(
				'product_ids' => array( 1, 2, 3 ),
			)
		);

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_unavailable', $result->get_error_code() );
	}

	/**
	 * Test get_recommendations with valid args parses correctly.
	 */
	public function test_get_recommendations_parses_args() {
		// Mock the Request class to capture the args.
		$captured_body = null;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$captured_body ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$captured_body = $parsed_args['body'];

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode(
							array(
								'recommendations' => array(
									'1' => array(
										array(
											'product_id' => '123',
											'score'      => 0.95,
										),
									),
								),
							)
						),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$this->api->get_recommendations(
			array(
				'product_ids' => array( 1, 2 ),
				'type'        => 'similar',
				'limit'       => 10,
				'exclude_ids' => array( 3 ),
			)
		);

		$this->assertNotNull( $captured_body );

		$body = json_decode( $captured_body, true );
		$this->assertIsArray( $body );
		$this->assertEquals( array( '1', '2' ), $body['product_ids'] );
		$this->assertEquals( 'similar', $body['type'] );
		$this->assertEquals( 10, $body['limit'] );
		$this->assertEquals( array( '3' ), $body['exclude_ids'] );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_recommendations converts product IDs to strings.
	 */
	public function test_get_recommendations_converts_ids_to_strings() {
		$captured_body = null;

		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) use ( &$captured_body ) {
				if ( strpos( $url, 'recommendations/query' ) !== false ) {
					$captured_body = $parsed_args['body'];

					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'recommendations' => array() ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$this->api->get_recommendations(
			array(
				'product_ids' => array( 1, 2, 3 ),
			)
		);

		$body = json_decode( $captured_body, true );
		$this->assertIsArray( $body['product_ids'] );

		foreach ( $body['product_ids'] as $id ) {
			$this->assertIsString( $id );
		}

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_status returns error when API unavailable.
	 */
	public function test_get_status_returns_error_when_api_unavailable() {
		delete_site_option( 'edd_pro_license_key' );

		$result = $this->api->get_status();

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_unavailable', $result->get_error_code() );
	}

	/**
	 * Test delete returns error without proper permissions.
	 */
	public function test_delete_returns_error_without_permissions() {
		// Create a user without permissions.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$result = $this->api->delete();

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_error', $result->get_error_code() );
	}

	/**
	 * Test delete works with proper permissions.
	 */
	public function test_delete_with_permissions() {
		// Create an admin user.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Mock the API response.
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/data' ) !== false && 'DELETE' === $parsed_args['method'] ) {
					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'success' => true ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$result = $this->api->delete();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test get_license_key method.
	 */
	public function test_get_license_key() {
		update_site_option( 'edd_pro_license_key', '  test_key_123  ' );

		$reflection = new \ReflectionClass( $this->api );
		$method     = $reflection->getMethod( 'get_license_key' );
		$method->setAccessible( true );

		$key = $method->invoke( $this->api );

		$this->assertEquals( 'test_key_123', $key );
	}

	/**
	 * Test get_store_url method.
	 */
	public function test_get_store_url() {
		$reflection = new \ReflectionClass( $this->api );
		$method     = $reflection->getMethod( 'get_store_url' );
		$method->setAccessible( true );

		$url = $method->invoke( $this->api );

		$this->assertNotEmpty( $url );
		$this->assertIsString( $url );
	}

	/**
	 * Test make_request returns error on non-200 response.
	 */
	public function test_make_request_returns_error_on_non_200_response() {
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/status' ) !== false ) {
					return array(
						'response' => array(
							'code'    => 500,
							'message' => 'Internal Server Error',
						),
						'body'     => '',
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$result = $this->api->get_status();

		$this->assertWPError( $result );
		$this->assertEquals( 'recommendations_error', $result->get_error_code() );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test successful API call returns parsed JSON.
	 */
	public function test_successful_api_call_returns_parsed_json() {
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/status' ) !== false ) {
					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode(
							array(
								'status'         => 'active',
								'total_products' => 100,
							)
						),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$result = $this->api->get_status();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'status', $result );
		$this->assertEquals( 'active', $result['status'] );
		$this->assertEquals( 100, $result['total_products'] );

		remove_all_filters( 'pre_http_request' );
	}
}
