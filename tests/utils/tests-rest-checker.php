<?php
/**
 * tests-rest-checker.php
 *
 * @package   EDD\Tests\Utils
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\RESTChecker as Checker;

/**
 * @coversDefaultClass \EDD\Utils\RESTChecker
 * @group edd_rest
 * @group edd_utils
 */
class RESTChecker extends EDD_UnitTestCase {

	/**
	 * Download fixture for REST endpoint testing.
	 *
	 * @var \EDD_Download
	 */
	protected static $download;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a simple download for cart REST endpoint testing
		$post_id = self::factory()->post->create( array(
			'post_title'  => 'Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish',
		) );

		update_post_meta( $post_id, 'edd_price', '20.00' );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$download = edd_get_download( $post_id );
	}

	/**
	 * Runs before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear cart before each test
		edd_empty_cart();
	}

	/**
	 * Test HTTP mode returns true when REST endpoint is accessible.
	 *
	 * @covers ::__construct
	 * @covers ::is_enabled
	 */
	public function test_http_mode_returns_true_when_endpoint_accessible() {
		// Expect incorrect usage notice since we're registering routes outside rest_api_init
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		// Register the cart REST routes
		$manager = new \EDD\REST\Manager();
		$manager->register_rest_routes();

		$checker = new Checker( 'edd/v3/cart/token', false );

		// Mock successful HTTP response
		add_filter( 'pre_http_request', array( $this, 'mock_successful_http_response' ), 10, 3 );

		$this->assertTrue( $checker->is_enabled() );

		remove_filter( 'pre_http_request', array( $this, 'mock_successful_http_response' ) );
	}

	/**
	 * Test HTTP mode returns false when WP_Error is returned.
	 *
	 * @covers ::is_enabled
	 */
	public function test_http_mode_returns_false_on_wp_error() {
		$checker = new Checker( 'edd/v3/cart/token', false );

		// Mock HTTP error response
		add_filter( 'pre_http_request', array( $this, 'mock_http_error_response' ), 10, 3 );

		$this->assertFalse( $checker->is_enabled() );

		remove_filter( 'pre_http_request', array( $this, 'mock_http_error_response' ) );
	}

	/**
	 * Test HTTP mode returns false when non-200 status code is returned.
	 *
	 * @covers ::is_enabled
	 */
	public function test_http_mode_returns_false_on_non_200_status() {
		$checker = new Checker( 'edd/v3/cart/token', false );

		// Mock 404 response
		add_filter( 'pre_http_request', array( $this, 'mock_404_http_response' ), 10, 3 );

		$this->assertFalse( $checker->is_enabled() );

		remove_filter( 'pre_http_request', array( $this, 'mock_404_http_response' ) );
	}

	/**
	 * Test HTTP mode returns false when invalid JSON is returned.
	 *
	 * @covers ::is_enabled
	 */
	public function test_http_mode_returns_false_on_invalid_json() {
		$checker = new Checker( 'edd/v3/cart/token', false );

		// Mock response with invalid JSON
		add_filter( 'pre_http_request', array( $this, 'mock_invalid_json_response' ), 10, 3 );

		$this->assertFalse( $checker->is_enabled() );

		remove_filter( 'pre_http_request', array( $this, 'mock_invalid_json_response' ) );
	}

	/**
	 * Test internal mode returns true when REST endpoint is accessible.
	 *
	 * @covers ::__construct
	 * @covers ::is_enabled
	 */
	public function test_internal_mode_returns_true_when_endpoint_accessible() {
		// Expect incorrect usage notice since we're registering routes outside rest_api_init
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		// Register the cart REST routes
		$manager = new \EDD\REST\Manager();
		$manager->register_rest_routes();

		$checker = new Checker( 'edd/v3/cart/token', true );

		$this->assertTrue( $checker->is_enabled() );
	}

	/**
	 * Test internal mode returns false when endpoint doesn't exist.
	 *
	 * @covers ::is_enabled
	 */
	public function test_internal_mode_returns_false_when_endpoint_not_found() {
		$checker = new Checker( 'edd/v3/nonexistent/endpoint', true );

		$this->assertFalse( $checker->is_enabled() );
	}

	/**
	 * Test internal mode returns false when endpoint returns error.
	 *
	 * @covers ::is_enabled
	 */
	public function test_internal_mode_returns_false_on_error_response() {
		// Register a custom endpoint that returns an error
		add_action( 'rest_api_init', function() {
			register_rest_route( 'edd/v3', '/test-error', array(
				'methods'             => 'GET',
				'callback'            => function() {
					return new \WP_Error( 'test_error', 'Test error message' );
				},
				'permission_callback' => '__return_true',
			) );
		} );

		// Trigger REST API initialization
		do_action( 'rest_api_init' );

		$checker = new Checker( 'edd/v3/test-error', true );

		$this->assertFalse( $checker->is_enabled() );
	}

	/**
	 * Test internal mode returns false when endpoint returns non-200 status.
	 *
	 * @covers ::is_enabled
	 */
	public function test_internal_mode_returns_false_on_non_200_status() {
		// Register a custom endpoint that returns 403
		add_action( 'rest_api_init', function() {
			register_rest_route( 'edd/v3', '/test-forbidden', array(
				'methods'             => 'GET',
				'callback'            => function() {
					return new \WP_REST_Response( array( 'error' => 'Forbidden' ), 403 );
				},
				'permission_callback' => '__return_true',
			) );
		} );

		// Trigger REST API initialization
		do_action( 'rest_api_init' );

		$checker = new Checker( 'edd/v3/test-forbidden', true );

		$this->assertFalse( $checker->is_enabled() );
	}

	/**
	 * Test internal mode with leading slash in endpoint.
	 *
	 * @covers ::is_enabled
	 */
	public function test_internal_mode_handles_leading_slash() {
		// Expect incorrect usage notice since we're registering routes outside rest_api_init
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		// Register the cart REST routes
		$manager = new \EDD\REST\Manager();
		$manager->register_rest_routes();

		// Test with leading slash
		$checker_with_slash = new Checker( '/edd/v3/cart/token', true );
		$this->assertTrue( $checker_with_slash->is_enabled() );

		// Test without leading slash
		$checker_without_slash = new Checker( 'edd/v3/cart/token', true );
		$this->assertTrue( $checker_without_slash->is_enabled() );
	}

	/**
	 * Test SSL verification filter is applied.
	 *
	 * @covers ::is_enabled
	 */
	public function test_ssl_verification_filter_is_applied() {
		$checker = new Checker( 'edd/v3/cart/token', false );

		$filter_applied = false;
		$callback = function( $value ) use ( &$filter_applied ) {
			$filter_applied = true;
			return $value;
		};

		add_filter( 'https_local_ssl_verify', $callback );

		// Mock successful HTTP response
		add_filter( 'pre_http_request', array( $this, 'mock_successful_http_response' ), 10, 3 );

		$checker->is_enabled();

		remove_filter( 'https_local_ssl_verify', $callback );
		remove_filter( 'pre_http_request', array( $this, 'mock_successful_http_response' ) );

		$this->assertTrue( $filter_applied, 'The https_local_ssl_verify filter should have been applied' );
	}

	/**
	 * Test that constructor accepts empty endpoint.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor_accepts_empty_endpoint() {
		$checker = new Checker();
		$this->assertInstanceOf( Checker::class, $checker );
	}

	/**
	 * Test different valid endpoint formats.
	 *
	 * @covers ::is_enabled
	 */
	public function test_various_endpoint_formats() {
		// Expect incorrect usage notice since we're registering routes outside rest_api_init
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		// Register the cart REST routes
		$manager = new \EDD\REST\Manager();
		$manager->register_rest_routes();

		// Mock successful HTTP response for all requests
		add_filter( 'pre_http_request', array( $this, 'mock_successful_http_response' ), 10, 3 );

		// Test various formats
		$formats = array(
			'edd/v3/cart/token',
			'/edd/v3/cart/token',
			'edd/v3/cart/token/',
			'/edd/v3/cart/token/',
		);

		foreach ( $formats as $format ) {
			$checker = new Checker( $format, false );
			$this->assertTrue(
				$checker->is_enabled(),
				"Endpoint format '{$format}' should be valid"
			);
		}

		remove_filter( 'pre_http_request', array( $this, 'mock_successful_http_response' ) );
	}

	/**
	 * Mock a successful HTTP response.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt an HTTP request's return value.
	 * @param array                 $args    HTTP request arguments.
	 * @param string                $url     The request URL.
	 * @return array
	 */
	public function mock_successful_http_response( $preempt, $args, $url ) {
		return array(
			'headers'  => array(),
			'body'     => json_encode( array( 'success' => true, 'token' => 'test_token' ) ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
		);
	}

	/**
	 * Mock an HTTP error response.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt an HTTP request's return value.
	 * @param array                 $args    HTTP request arguments.
	 * @param string                $url     The request URL.
	 * @return \WP_Error
	 */
	public function mock_http_error_response( $preempt, $args, $url ) {
		return new \WP_Error( 'http_request_failed', 'cURL error 60: SSL certificate problem: self-signed certificate' );
	}

	/**
	 * Mock a 404 HTTP response.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt an HTTP request's return value.
	 * @param array                 $args    HTTP request arguments.
	 * @param string                $url     The request URL.
	 * @return array
	 */
	public function mock_404_http_response( $preempt, $args, $url ) {
		return array(
			'headers'  => array(),
			'body'     => json_encode( array( 'code' => 'rest_no_route', 'message' => 'No route was found' ) ),
			'response' => array(
				'code'    => 404,
				'message' => 'Not Found',
			),
		);
	}

	/**
	 * Mock an invalid JSON response.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt an HTTP request's return value.
	 * @param array                 $args    HTTP request arguments.
	 * @param string                $url     The request URL.
	 * @return array
	 */
	public function mock_invalid_json_response( $preempt, $args, $url ) {
		return array(
			'headers'  => array(),
			'body'     => 'This is not JSON',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
		);
	}
}

