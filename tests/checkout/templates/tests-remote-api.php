<?php
/**
 * RemoteAPI Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Checkout\Templates\RemoteAPI;
use EDD\Tests\Helpers\Licenses as LicenseData;

/**
 * RemoteAPI tests.
 *
 * Unit tests for the RemoteAPI class that handles
 * communication with the remote template server.
 *
 * HTTP requests are disabled in the test environment.
 * These tests use the pre_http_request filter at priority 1
 * to intercept requests before the bootstrap disabler (priority 10)
 * and return controlled responses for full end-to-end coverage.
 *
 * @group checkout-templates
 */
class RemoteAPITest extends EDD_UnitTestCase {

	/**
	 * RemoteAPI instance.
	 *
	 * @var RemoteAPI
	 */
	protected $remote_api;

	/**
	 * Cache option name used by RemoteAPI.
	 *
	 * Must match the CACHE_KEY constant in RemoteAPI.
	 *
	 * @var string
	 */
	private const CACHE_OPTION = 'edd_checkout_templates_list';

	/**
	 * Set up each test.
	 *
	 * Clears the cache option and removes any leftover HTTP filters
	 * so each test starts from a clean slate.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		delete_option( self::CACHE_OPTION );
		remove_all_filters( 'pre_http_request' );
		$this->remote_api = new RemoteAPI();
	}

	/**
	 * Tear down after each test.
	 *
	 * Restores the bootstrap's HTTP disabler so later tests are not
	 * accidentally allowed to make real network calls.
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		delete_option( self::CACHE_OPTION );
		remove_all_filters( 'pre_http_request' );

		// Clean up any license data seeded by a test.
		LicenseData::delete_pro_license();
		delete_option( 'edd_pro_license_key' );
		delete_option( 'edd_pro_license' );
		delete_option( 'edd_pass_licenses' );
		remove_all_filters( 'edd_is_pro' );

		// Re-add the bootstrap HTTP disabler.
		add_filter(
			'pre_http_request',
			function( $status = false, $args = array(), $url = '' ) {
				return new \WP_Error(
					'no_reqs_in_unit_tests',
					__( 'HTTP Requests disabled for unit tests', 'easy-digital-downloads' )
				);
			}
		);
	}

	// -------------------------------------------------------------------------
	// Helper methods
	// -------------------------------------------------------------------------

	/**
	 * Mock HTTP response for RemoteAPI calls.
	 *
	 * Runs at priority 1 to override the bootstrap disabler at priority 10.
	 *
	 * @param mixed $body Response body — will be JSON-encoded.
	 * @param int   $code HTTP status code.
	 * @return void
	 */
	private function mock_http_response( $body, int $code = 200 ): void {
		add_filter(
			'pre_http_request',
			function() use ( $body, $code ) {
				return array(
					'response' => array( 'code' => $code, 'message' => '' ),
					'body'     => wp_json_encode( $body ),
					'headers'  => array(),
					'cookies'  => array(),
				);
			},
			1
		);
	}

	/**
	 * Mock HTTP error.
	 *
	 * Runs at priority 1 to override the bootstrap disabler at priority 10.
	 *
	 * @param string $code    WP_Error code.
	 * @param string $message Error message.
	 * @return void
	 */
	private function mock_http_error( string $code = 'http_error', string $message = 'Connection failed' ): void {
		add_filter(
			'pre_http_request',
			function() use ( $code, $message ) {
				return new \WP_Error( $code, $message );
			},
			1
		);
	}

	/**
	 * Get a sample template array for mocking.
	 *
	 * @param string $id Template ID.
	 * @return array
	 */
	private function get_sample_template( string $id = 'test-template' ): array {
		return array(
			'id'            => $id,
			'name'          => 'Test Template',
			'description'   => 'A test template',
			'version'       => '1.0.0',
			'author'        => 'Test',
			'tags'          => array( 'modern' ),
			'features'      => array( 'test' ),
			'product_types' => array( 'digital' ),
			'editors'       => array( 'elementor' ),
			'requirements'  => array( 'edd_version' => '3.3.0' ),
			'thumbnail'     => 'https://r2.easydigitaldownloads.com/test/thumb.png',
			'preview_url'   => 'https://r2.easydigitaldownloads.com/test/preview.html',
			'status'        => 'published',
		);
	}

	/**
	 * Get a sample API list response.
	 *
	 * @return array
	 */
	private function get_sample_list_response(): array {
		return array(
			'templates'        => array( $this->get_sample_template() ),
			'available_tags'   => array(
				array( 'id' => 'all', 'name' => 'All Templates', 'count' => 1 ),
				array( 'id' => 'modern', 'name' => 'Modern', 'count' => 1 ),
			),
			'available_editors' => array( 'elementor' ),
			'total'            => 1,
			'can_import'       => false,
			'license_status'   => 'missing',
		);
	}

	/**
	 * Seed the cache option directly so tests can start from a cached state.
	 *
	 * @param array $data The data to cache.
	 * @return void
	 */
	private function seed_cache( array $data ): void {
		$option = wp_json_encode(
			array(
				'value'   => $data,
				'timeout' => strtotime( '+1 hour', time() ),
			)
		);
		update_option( self::CACHE_OPTION, $option, false );
	}

	// -------------------------------------------------------------------------
	// Structure tests
	// -------------------------------------------------------------------------

	/**
	 * Test get_templates returns an array.
	 */
	public function test_get_templates_returns_array() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result = $this->remote_api->get_templates();

		$this->assertIsArray( $result );
	}

	/**
	 * Test get_templates returns expected top-level structure.
	 *
	 * The return value must have 'templates' and 'available_tags' keys.
	 */
	public function test_get_templates_returns_expected_structure() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result = $this->remote_api->get_templates();

		$this->assertArrayHasKey( 'templates', $result );
		$this->assertArrayHasKey( 'available_tags', $result );
		$this->assertIsArray( $result['templates'] );
		$this->assertIsArray( $result['available_tags'] );
	}

	/**
	 * Test get_templates templates have required fields.
	 */
	public function test_get_templates_templates_have_required_fields() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result = $this->remote_api->get_templates();

		$this->assertNotEmpty( $result['templates'] );

		foreach ( $result['templates'] as $template ) {
			$this->assertArrayHasKey( 'id', $template );
			$this->assertArrayHasKey( 'name', $template );
			$this->assertArrayHasKey( 'description', $template );
			$this->assertArrayHasKey( 'thumbnail', $template );
			$this->assertArrayHasKey( 'editors', $template );
		}
	}

	/**
	 * Test get_templates returns empty structure on API failure with no cache.
	 */
	public function test_get_templates_returns_empty_on_api_failure() {
		$this->mock_http_error();

		$result = $this->remote_api->get_templates();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'templates', $result );
		$this->assertArrayHasKey( 'available_tags', $result );
		$this->assertEmpty( $result['templates'] );
		$this->assertEmpty( $result['available_tags'] );
	}

	// -------------------------------------------------------------------------
	// Cache tests
	// -------------------------------------------------------------------------

	/**
	 * Test get_templates caches the result.
	 *
	 * After the first call the option is populated; a second call with the
	 * HTTP filter removed should still return the same data from cache.
	 */
	public function test_get_templates_caches_result() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$first = $this->remote_api->get_templates();

		// Remove the mock so any live HTTP call would fail.
		remove_all_filters( 'pre_http_request' );

		$second = $this->remote_api->get_templates();

		$this->assertEquals( $first, $second );
		$this->assertNotEmpty( $second['templates'] );
	}

	/**
	 * Test get_templates returns stale cache when API fails on force-refresh.
	 */
	public function test_get_templates_returns_stale_cache_on_failure() {
		// Populate cache with valid data.
		$this->mock_http_response( $this->get_sample_list_response() );
		$cached = $this->remote_api->get_templates();
		$this->assertNotEmpty( $cached['templates'] );

		// Switch to an error response.
		remove_all_filters( 'pre_http_request' );
		$this->mock_http_error();

		// Force-refresh — API fails but stale cache should be returned.
		$result = $this->remote_api->get_templates( true );

		$this->assertNotEmpty( $result['templates'] );
		$this->assertEquals( $cached['templates'], $result['templates'] );
	}

	/**
	 * Test get_templates force_refresh bypasses cache and returns new data.
	 */
	public function test_get_templates_force_refresh_bypasses_cache() {
		// Response A — one template.
		$response_a = $this->get_sample_list_response();
		$this->mock_http_response( $response_a );
		$this->remote_api->get_templates();

		// Response B — two templates.
		remove_all_filters( 'pre_http_request' );
		$response_b                    = $this->get_sample_list_response();
		$response_b['templates'][]     = $this->get_sample_template( 'second-template' );
		$response_b['total']           = 2;
		$this->mock_http_response( $response_b );

		$result = $this->remote_api->get_templates( true );

		$this->assertCount( 2, $result['templates'] );
	}

	// -------------------------------------------------------------------------
	// Error-handling tests
	// -------------------------------------------------------------------------

	/**
	 * Test download_template returns WP_Error when API is unavailable.
	 */
	public function test_download_template_returns_wp_error_on_failure() {
		$this->mock_http_error();

		$result = $this->remote_api->download_template( 'some-template', 'elementor' );

		$this->assertWPError( $result );
	}

	/**
	 * Test download_template returns WP_Error with 'api_client_error' code on 4xx.
	 */
	public function test_download_template_returns_wp_error_on_4xx() {
		$this->mock_http_response( array(), 404 );

		$result = $this->remote_api->download_template( 'missing-template', 'elementor' );

		$this->assertWPError( $result );
		$this->assertSame( 'api_client_error', $result->get_error_code() );
	}

	/**
	 * Test 4xx error data marks the error as non-recoverable.
	 */
	public function test_download_template_4xx_error_is_not_recoverable() {
		$this->mock_http_response( array(), 404 );

		$result = $this->remote_api->download_template( 'missing-template', 'elementor' );

		$this->assertWPError( $result );
		$error_data = $result->get_error_data();
		$this->assertArrayHasKey( 'recoverable', $error_data );
		$this->assertFalse( $error_data['recoverable'] );
	}

	/**
	 * Test download_template returns WP_Error with 'api_server_error' code on 5xx.
	 */
	public function test_download_template_returns_wp_error_on_5xx() {
		$this->mock_http_response( array(), 500 );

		$result = $this->remote_api->download_template( 'some-template', 'elementor' );

		$this->assertWPError( $result );
		$this->assertSame( 'api_server_error', $result->get_error_code() );
	}

	/**
	 * Test 5xx error data marks the error as recoverable with retry.
	 */
	public function test_download_template_5xx_error_is_recoverable_with_retry() {
		$this->mock_http_response( array(), 500 );

		$result = $this->remote_api->download_template( 'some-template', 'elementor' );

		$this->assertWPError( $result );
		$error_data = $result->get_error_data();
		$this->assertArrayHasKey( 'recoverable', $error_data );
		$this->assertArrayHasKey( 'retry', $error_data );
		$this->assertTrue( $error_data['recoverable'] );
		$this->assertTrue( $error_data['retry'] );
	}

	/**
	 * Test error data contains a support URL.
	 */
	public function test_error_contains_support_url() {
		$this->mock_http_response( array(), 500 );

		$result = $this->remote_api->download_template( 'some-template', 'elementor' );

		$this->assertWPError( $result );
		$error_data = $result->get_error_data();
		$this->assertArrayHasKey( 'support_url', $error_data );
		$this->assertStringContainsString( 'easydigitaldownloads.com', $error_data['support_url'] );
	}

	// -------------------------------------------------------------------------
	// Sanitization test
	// -------------------------------------------------------------------------

	/**
	 * Test download_template sanitizes the template ID before building the URL.
	 *
	 * A path-traversal string must have characters outside the route charset
	 * [\w-] stripped (via a preg_replace character-class strip) so the
	 * request URL cannot escape the API endpoint.
	 */
	public function test_download_template_sanitizes_id() {
		$captured_url = null;

		add_filter(
			'pre_http_request',
			function( $preempt, $args, $url ) use ( &$captured_url ) {
				$captured_url = $url;
				return new \WP_Error( 'http_error', 'Captured' );
			},
			1,
			3
		);

		$this->remote_api->download_template( '../../../wp-config', 'elementor' );

		$this->assertNotNull( $captured_url );

		// sanitize_key('../../../wp-config') strips slashes and dots.
		// The resulting URL must not contain path-traversal sequences.
		$this->assertStringNotContainsString( '../', $captured_url );
		$this->assertStringNotContainsString( '..', $captured_url );
	}

	// -------------------------------------------------------------------------
	// Template validation tests
	// -------------------------------------------------------------------------

	/**
	 * Test templates have valid editor values.
	 */
	public function test_templates_have_valid_editor_values() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result        = $this->remote_api->get_templates();
		$valid_editors = array( 'elementor', 'blocks' );

		$this->assertNotEmpty( $result['templates'] );

		foreach ( $result['templates'] as $template ) {
			$this->assertArrayHasKey( 'editors', $template );
			$this->assertIsArray( $template['editors'] );
			foreach ( $template['editors'] as $editor ) {
				$this->assertContains(
					$editor,
					$valid_editors,
					sprintf( 'Template %s has invalid editor: %s', $template['id'], $editor )
				);
			}
		}
	}

	/**
	 * Test templates have a non-empty version field.
	 */
	public function test_templates_have_version_field() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result = $this->remote_api->get_templates();

		$this->assertNotEmpty( $result['templates'] );

		foreach ( $result['templates'] as $template ) {
			$this->assertArrayHasKey( 'version', $template );
			$this->assertNotEmpty( $template['version'] );
		}
	}

	/**
	 * Test templates have full (absolute) thumbnail URLs.
	 */
	public function test_templates_have_full_thumbnail_urls() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result = $this->remote_api->get_templates();

		$this->assertNotEmpty( $result['templates'] );

		foreach ( $result['templates'] as $template ) {
			if ( ! empty( $template['thumbnail'] ) ) {
				$this->assertStringStartsWith(
					'http',
					$template['thumbnail'],
					'Thumbnail URL should be absolute'
				);
			}
		}
	}

	/**
	 * Test templates have full (absolute) preview URLs.
	 */
	public function test_templates_have_full_preview_urls() {
		$this->mock_http_response( $this->get_sample_list_response() );

		$result = $this->remote_api->get_templates();

		$this->assertNotEmpty( $result['templates'] );

		foreach ( $result['templates'] as $template ) {
			if ( ! empty( $template['preview_url'] ) ) {
				$this->assertStringStartsWith(
					'http',
					$template['preview_url'],
					'Preview URL should be absolute'
				);
			}
		}
	}

	// -------------------------------------------------------------------------
	// Malformed-response / defensive-key tests
	// -------------------------------------------------------------------------

	/**
	 * Test get_templates handles a response that includes available_tags but lacks the templates key.
	 *
	 * The ?? operator on line 124 of RemoteAPI::get_templates() must prevent a PHP notice
	 * and ensure $result['templates'] is an empty array rather than null or missing.
	 *
	 * @covers \EDD\Checkout\Templates\RemoteAPI::get_templates
	 */
	public function test_get_templates_handles_response_missing_templates_key() {
		// Valid JSON that has available_tags but no templates key.
		$body = array(
			'available_tags' => array(
				array( 'id' => 'all', 'name' => 'All Templates', 'count' => 0 ),
			),
		);

		remove_all_filters( 'pre_http_request' );
		$this->mock_http_response( $body );

		// Force refresh so no cached value is returned first.
		$result = $this->remote_api->get_templates( true );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'templates', $result );
		$this->assertArrayHasKey( 'available_tags', $result );
		$this->assertIsArray( $result['templates'] );
		$this->assertSame( array(), $result['templates'] );
	}

	/**
	 * Test get_templates returns an empty payload when the API response is malformed.
	 *
	 * A valid HTTP 200 whose decoded body is an empty array (neither templates nor
	 * available_tags are present) must be treated as malformed and fall back to
	 * the empty payload, not be cached.
	 *
	 * @covers \EDD\Checkout\Templates\RemoteAPI::get_templates
	 */
	public function test_get_templates_handles_malformed_response() {
		// Ensure the transient is empty so there is no stale cache to fall back to.
		delete_option( self::CACHE_OPTION );

		// Return a well-formed HTTP 200 whose JSON body has none of the expected keys.
		$body = array( 'error' => 'oops' );

		remove_all_filters( 'pre_http_request' );
		$this->mock_http_response( $body );

		$result = $this->remote_api->get_templates( true );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'templates', $result );
		$this->assertArrayHasKey( 'available_tags', $result );
		$this->assertSame( array(), $result['templates'] );
		$this->assertSame( array(), $result['available_tags'] );
	}

	/**
	 * Test get_templates returns stale cache when the API response is malformed.
	 *
	 * When a prior successful response exists in the transient, a subsequent
	 * malformed response must return the cached value rather than an empty payload.
	 *
	 * @covers \EDD\Checkout\Templates\RemoteAPI::get_templates
	 */
	public function test_get_templates_returns_stale_cache_on_malformed_response() {
		// Pre-populate the cache with a known good value.
		$cached_value = array(
			'templates'      => array( $this->get_sample_template( 'stale-template' ) ),
			'available_tags' => array(
				array( 'id' => 'all', 'name' => 'All Templates', 'count' => 1 ),
			),
		);
		$this->seed_cache( $cached_value );

		// Now mock a malformed response (empty-keyed object — neither templates nor available_tags).
		$body = array( 'error' => 'oops' );

		remove_all_filters( 'pre_http_request' );
		$this->mock_http_response( $body );

		// Force-refresh so the code attempts a real fetch rather than returning cache early.
		$result = $this->remote_api->get_templates( true );

		// Should get back the stale cached value, not the empty fallback.
		$this->assertNotEmpty( $result['templates'] );
		$this->assertEquals( $cached_value, $result );
	}

	/**
	 * Test get_templates rejects a response whose keys are present but wrong-typed.
	 *
	 * A response like {"templates":"oops","available_tags":null} sets the expected
	 * keys but with non-array values. The guard must treat this as malformed and
	 * fall back to the empty payload rather than caching the wrong-typed data for
	 * an hour and serving a silently broken template list.
	 *
	 * @covers \EDD\Checkout\Templates\RemoteAPI::get_templates
	 */
	public function test_get_templates_rejects_wrong_typed_response() {
		// Ensure there is no stale cache to fall back to.
		delete_option( self::CACHE_OPTION );

		// Keys are present, but templates is a string and available_tags is null.
		$body = array(
			'templates'      => 'oops',
			'available_tags' => null,
		);

		remove_all_filters( 'pre_http_request' );
		$this->mock_http_response( $body );

		$result = $this->remote_api->get_templates( true );

		// The wrong-typed payload must be normalized to the empty fallback.
		$this->assertIsArray( $result['templates'] );
		$this->assertSame( array(), $result['templates'] );
		$this->assertSame( array(), $result['available_tags'] );

		// It must NOT have been cached as if it were valid.
		$this->assertFalse( get_option( self::CACHE_OPTION ) );
	}

	/**
	 * Test get_templates returns stale cache when the response is wrong-typed.
	 *
	 * A prior good value must be preferred over a wrong-typed response, matching
	 * the missing-key malformed behavior.
	 *
	 * @covers \EDD\Checkout\Templates\RemoteAPI::get_templates
	 */
	public function test_get_templates_returns_stale_cache_on_wrong_typed_response() {
		$cached_value = array(
			'templates'      => array( $this->get_sample_template( 'stale-template' ) ),
			'available_tags' => array(
				array( 'id' => 'all', 'name' => 'All Templates', 'count' => 1 ),
			),
		);
		$this->seed_cache( $cached_value );

		$body = array(
			'templates'      => 'oops',
			'available_tags' => null,
		);

		remove_all_filters( 'pre_http_request' );
		$this->mock_http_response( $body );

		$result = $this->remote_api->get_templates( true );

		$this->assertNotEmpty( $result['templates'] );
		$this->assertEquals( $cached_value, $result );
	}

	// -------------------------------------------------------------------------
	// Remote template-ID handling tests
	// -------------------------------------------------------------------------

	/**
	 * Test that an uppercase template id survives end-to-end through the import
	 * flow: the download request carries it unmutated, and the stored template-id
	 * meta equals the authoritative request id.
	 *
	 * The REST route pattern (?P<id>[\w-]+) admits uppercase, and the arg
	 * sanitize_callback (sanitize_text_field) preserves case, so an uppercase id
	 * is reachable through REST. The download payload deliberately carries a
	 * lowercased template_id, so before the fix the meta reflects the payload
	 * rather than the request. The full 200-path fixture stack is mirrored from
	 * the import-endpoint precedent because import_template() is unreachable
	 * without it.
	 *
	 * @since 3.7.0
	 */
	public function test_import_preserves_authoritative_uppercase_id() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.35.9' );
		}

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$admin    = new \WP_User( $admin_id );
		$admin->add_cap( 'manage_shop_settings' );
		wp_set_current_user( $admin_id );

		// Seed a valid Pro license so the gate passes.
		LicenseData::get_pro_license( array( 'license' => 'valid' ) );
		$passes = array(
			'license_1' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'time_checked' => time(),
			),
		);
		update_option( 'edd_pass_licenses', wp_json_encode( $passes ) );

		$original_purchase_page = edd_get_option( 'purchase_page', 0 );
		$page_id                = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Uppercase Id Import Page',
				'post_status' => 'publish',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();
		// register() runs outside rest_api_init here by design, which raises the
		// register_rest_route _doing_it_wrong notice; declare it expected.
		$this->setExpectedIncorrectUsage( 'register_rest_route' );
		( new \EDD\Pro\REST\Routes\CheckoutTemplates() )->register();

		$template_id       = 'Modern-Split';
		$elementor_content = wp_json_encode( array( array( 'elType' => 'section', 'id' => 'abc123', 'elements' => array() ) ) );

		// Two-call mock: the list fetch (empty, so no requirements) then the
		// download. The download body carries a lowercased id on purpose.
		$captured_download_url = '';
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$captured_download_url, $elementor_content ) {
				if ( false !== strpos( $url, '/download' ) ) {
					$captured_download_url = $url;
					return array(
						'response' => array( 'code' => 200, 'message' => 'OK' ),
						'body'     => wp_json_encode(
							array(
								'template_id' => 'modern-split',
								'name'        => 'Modern Split',
								'version'     => '1.0.0',
								'content'     => $elementor_content,
							)
						),
						'headers'  => array(),
						'cookies'  => array(),
					);
				}
				return array(
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'body'     => wp_json_encode( array( 'templates' => array(), 'available_tags' => array() ) ),
					'headers'  => array(),
					'cookies'  => array(),
				);
			},
			10,
			3
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/' . $template_id . '/import' );
		$request->set_param( 'id', $template_id );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $wp_rest_server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		// The outgoing download request must carry the id without case folding.
		$this->assertStringContainsString( '/' . $template_id . '/download', $captured_download_url );

		// The stored template-id meta must equal the authoritative request id.
		$this->assertSame(
			$template_id,
			get_post_meta( $page_id, \EDD\Checkout\Templates\Config\Constants::META_ID, true )
		);

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', $original_purchase_page );
		remove_all_filters( 'pre_http_request' );
		$wp_rest_server = null;
	}

	/**
	 * Test that download_template() and store_metadata() handle charset edges
	 * that can never match the REST route pattern (?P<id>[\w-]+): empty, '/', '%'.
	 *
	 * These bypass the route entirely, so they are exercised as direct unit
	 * calls. For download_template() the observable is the outgoing request URL,
	 * captured via a pre_http_request filter (mirroring the 4xx/5xx download
	 * tests); characters outside the route charset [\w-] are stripped so the URL
	 * cannot escape the API endpoint. For store_metadata() -- which is protected
	 * on AbstractImporter and not exposed by this test case's parent -- the
	 * observable is the written meta, reached via the in-repo Reflection idiom
	 * (precedent: tests-browser.php:404-406). This characterizes existing
	 * behavior (green on write).
	 *
	 * @since 3.7.0
	 */
	public function test_download_and_store_handle_charset_edges() {
		$captured = array();
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$captured ) {
				$captured[] = $url;
				return array(
					'response' => array( 'code' => 200, 'message' => '' ),
					'body'     => wp_json_encode( array() ),
					'headers'  => array(),
					'cookies'  => array(),
				);
			},
			10,
			3
		);

		$this->remote_api->download_template( '', 'elementor' );
		$this->remote_api->download_template( '/', 'elementor' );
		$this->remote_api->download_template( '%', 'elementor' );

		// Each edge char is outside [\w-] and is stripped, collapsing the id
		// segment to empty -- the request stays anchored to the download endpoint.
		foreach ( $captured as $url ) {
			$this->assertStringContainsString( '/checkout-templates//download', $url );
		}
		$this->assertStringNotContainsString( '%', $captured[2] );

		remove_all_filters( 'pre_http_request' );

		// store_metadata() writes META_ID from the template array. It is unaffected
		// by the download-path fix, so these assertions characterize existing
		// behavior (green on write). BlockImporter is a concrete AbstractImporter.
		$importer   = new \EDD\Pro\Checkout\Templates\Importer\BlockImporter();
		$reflection = new \ReflectionClass( $importer );
		$method     = $reflection->getMethod( 'store_metadata' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		foreach ( array( '', '/', '%' ) as $edge ) {
			$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
			$method->invoke( $importer, $page_id, array( 'template_id' => $edge ) );
			$this->assertSame(
				sanitize_text_field( $edge ),
				get_post_meta( $page_id, \EDD\Checkout\Templates\Config\Constants::META_ID, true )
			);
			wp_delete_post( $page_id, true );
		}
	}
}
