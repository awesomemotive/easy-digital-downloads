<?php
/**
 * Import Endpoint Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\REST\Routes\CheckoutTemplates as ProRoute;
use EDD\Pro\REST\Controllers\CheckoutTemplates as ProController;
use EDD\Tests\Helpers\Licenses as LicenseData;

/**
 * ImportEndpoint tests.
 *
 * Integration tests for the Pro-only import REST API endpoint.
 *
 * @group rest-api
 */
class ImportEndpointTest extends EDD_UnitTestCase {

	/**
	 * REST server instance.
	 *
	 * @var \WP_REST_Server
	 */
	protected $server;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected static $admin_user_id;

	/**
	 * Subscriber user ID.
	 *
	 * @var int
	 */
	protected static $subscriber_user_id;

	/**
	 * Test checkout page ID.
	 *
	 * @var int
	 */
	protected static $checkout_page_id;

	/**
	 * Original purchase page option value.
	 *
	 * @var int
	 */
	protected static $original_purchase_page;

	/**
	 * Set up fixtures before class runs.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Store original purchase_page option to restore later.
		self::$original_purchase_page = edd_get_option( 'purchase_page', 0 );

		// Create admin user with shop management caps.
		self::$admin_user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		$admin = new \WP_User( self::$admin_user_id );
		$admin->add_cap( 'manage_shop_settings' );

		// Create subscriber user without caps.
		self::$subscriber_user_id = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);

		// Create checkout page.
		self::$checkout_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Checkout',
				'post_status' => 'publish',
			)
		);

		// Set as EDD checkout page.
		edd_update_option( 'purchase_page', self::$checkout_page_id );

		// Define ELEMENTOR_VERSION so ElementorImporter::can_import() returns
		// true for all tests in this class. PHP constants persist for the
		// remainder of the process, avoiding @runInSeparateProcess which
		// breaks CI when the test bootstrap re-runs activate_plugin() against
		// a WP install path that doesn't contain the plugin.
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.35.9' );
		}
	}

	/**
	 * Tear down fixtures after class runs.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		// Restore original purchase_page option.
		edd_update_option( 'purchase_page', self::$original_purchase_page );

		parent::tearDownAfterClass();
	}

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server();

		// Register routes directly to avoid side effects from firing rest_api_init globally.
		// This prevents callbacks from other parts of EDD (like API request logging) from running.
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$route = new ProRoute();
		$route->register();
	}

	/**
	 * Tear down each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;

		// Clean up any license data seeded by a test.
		LicenseData::delete_pro_license();
		delete_option( 'edd_pro_license_key' );
		delete_option( 'edd_pro_license' );
		delete_option( 'edd_pass_licenses' );
		remove_all_filters( 'edd_is_pro' );

		parent::tear_down();
	}

	/**
	 * Seed a valid Pro license with an active pass so License::can_import() passes.
	 *
	 * @return void
	 */
	private function seed_pro_license() {
		LicenseData::get_pro_license( array( 'license' => 'valid' ) );

		$passes = array(
			'license_1' => array(
				'pass_id'      => \EDD\Admin\Pass_Manager::PERSONAL_PASS_ID,
				'time_checked' => time(),
			),
		);
		update_option( 'edd_pass_licenses', wp_json_encode( $passes ) );
	}

	/**
	 * Test import route is registered.
	 */
	public function test_import_route_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/checkout-templates/(?P<id>[\w-]+)/import', $routes );
	}

	/**
	 * Test can_import returns false for logged out users.
	 */
	public function test_can_import_logged_out() {
		wp_set_current_user( 0 );

		$route   = new ProRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$this->assertFalse( $route->can_import( $request ) );
	}

	/**
	 * Test can_import returns false for users without capability.
	 */
	public function test_can_import_without_capability() {
		wp_set_current_user( self::$subscriber_user_id );

		$route   = new ProRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$this->assertFalse( $route->can_import( $request ) );
	}

	/**
	 * Test can_import returns WP_Error when license is invalid.
	 */
	public function test_can_import_with_invalid_license() {
		wp_set_current_user( self::$admin_user_id );

		// Ensure the license gate fails: no license or pass seeded.
		delete_option( 'edd_pass_licenses' );
		delete_option( 'edd_pro_license_key' );
		delete_site_option( 'edd_pro_license_key' );

		$route   = new ProRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$result  = $route->can_import( $request );

		$this->assertWPError( $result );
		$this->assertSame( 'license_required', $result->get_error_code() );
	}

	/**
	 * Test can_import returns true for admin with valid license.
	 */
	public function test_can_import_with_valid_license() {
		wp_set_current_user( self::$admin_user_id );

		// Seed a valid Pro license so the gate passes.
		$this->seed_pro_license();

		$route   = new ProRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$this->assertTrue( $route->can_import( $request ) );
	}

	/**
	 * Test import returns error when no checkout page is configured.
	 */
	public function test_import_error_no_checkout_page() {
		// Temporarily remove checkout page setting.
		edd_update_option( 'purchase_page', 0 );

		wp_set_current_user( self::$admin_user_id );

		// Seed a valid Pro license so the gate passes.
		$this->seed_pro_license();

		$controller = new ProController();
		$request    = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'id', 'test-template' );
		$request->set_param( 'editor', 'elementor' );

		$response = $controller->import_template( $request );

		$this->assertWPError( $response );
		$this->assertSame( 'no_checkout_page', $response->get_error_code() );

		// Restore checkout page setting.
		edd_update_option( 'purchase_page', self::$checkout_page_id );	}

	/**
	 * Test import validates editor parameter.
	 */
	public function test_import_validates_editor() {
		wp_set_current_user( self::$admin_user_id );

		// Seed a valid Pro license so the gate passes.
		$this->seed_pro_license();

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'editor', 'invalid_editor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		// Should fail validation - editor must be 'blocks' or 'elementor'.
		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Test import requires authentication via REST.
	 */
	public function test_import_requires_auth_via_rest() {
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test import requires capability via REST.
	 */
	public function test_import_requires_capability_via_rest() {
		wp_set_current_user( self::$subscriber_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Test import is denied for a user who can manage shop settings and holds a
	 * valid license but cannot edit the checkout page (defense-in-depth edit_post
	 * check, mirroring the restore endpoint).
	 *
	 * The user must hold manage_shop_settings so can_import() admits the request,
	 * and must NOT hold any page-editing capability so the per-page edit_post check
	 * inside import_template() fails. A fresh user is created so the shared
	 * subscriber fixture is not mutated. The license check is forced to pass so the
	 * request reaches the controller's capability check rather than short-circuiting
	 * on license.
	 *
	 * @since 3.7.0
	 */
	public function test_import_denied_without_edit_post_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$user    = new \WP_User( $user_id );
		$user->add_cap( 'manage_shop_settings' );

		// Isolated checkout page owned by no one the test user can edit.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Import Capability Check Page',
				'post_status' => 'publish',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		// Force the license check to pass so the permission callback admits the
		// request and it reaches the controller's edit_post check.
		$this->seed_pro_license();

		wp_set_current_user( $user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'id', 'test-template' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'insufficient_permissions', $data['code'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );	}

	/**
	 * Test a successful import returns HTTP 200, includes expected fields, and mutates the page.
	 */
	public function test_import_returns_success_and_mutates_page() {
		wp_set_current_user( self::$admin_user_id );

		// Allow import.
		$this->seed_pro_license();

		// Create a dedicated page for this test so we can verify mutation.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Import Happy Path Page',
				'post_status' => 'publish',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		// Build the Elementor content the mock API will return.
		$elementor_content = wp_json_encode( array( array( 'elType' => 'section', 'id' => 'abc123', 'elements' => array() ) ) );

		// Mock the two outbound HTTP calls:
		//   1. RemoteAPI::get_templates() — returns an empty templates list (no requirements).
		//   2. RemoteAPI::download_template() — returns the template payload.
		// Remove the bootstrap's HTTP disabler so our mock is the sole handler.
		remove_all_filters( 'pre_http_request' );
		$call_count = 0;
		add_filter(
			'pre_http_request',
			function() use ( &$call_count, $elementor_content ) {
				$call_count++;
				if ( 1 === $call_count ) {
					// First call: get_templates() list endpoint.
					return array(
						'response' => array( 'code' => 200, 'message' => 'OK' ),
						'body'     => wp_json_encode( array( 'templates' => array(), 'available_tags' => array() ) ),
						'headers'  => array(),
						'cookies'  => array(),
					);
				}
				// Second call: download_template() endpoint.
				return array(
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'body'     => wp_json_encode(
						array(
							'template_id' => 'test-template',
							'name'        => 'Test Template',
							'version'     => '1.0.0',
							'content'     => $elementor_content,
						)
					),
					'headers'  => array(),
					'cookies'  => array(),
				);
			},
			1
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'id', 'test-template' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		// Assert HTTP 200 success.
		$this->assertSame( 200, $response->get_status() );

		// Assert response payload contains expected fields.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['success'] );
		$this->assertSame( $page_id, $data['page_id'] );

		// Assert the checkout page now has Elementor data stored.
		$stored = get_post_meta( $page_id, '_elementor_data', true );
		$this->assertNotEmpty( $stored );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test that an import request returns HTTP 400 when the template's minimum
	 * version requirements are not met by the current environment.
	 */
	public function test_import_fails_requirements_check_returns_400() {
		wp_set_current_user( self::$admin_user_id );

		// Allow import (license passes).
		$this->seed_pro_license();

		// Create a dedicated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Requirements Check Test Page',
				'post_status' => 'publish',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		// Mock the get_templates() call to return a template whose requirements
		// specify an impossibly high EDD version — ensuring validate_requirements()
		// returns a WP_Error without a second HTTP call being needed.
		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function() {
				return array(
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'body'     => wp_json_encode(
						array(
							'templates'      => array(
								array(
									'id'           => 'requirements-fail-template',
									'name'         => 'Requirements Fail Template',
									'requirements' => array(
										'edd' => '999.0.0',
									),
								),
							),
							'available_tags' => array(),
						)
					),
					'headers'  => array(),
					'cookies'  => array(),
				);
			},
			1
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/requirements-fail-template/import' );
		$request->set_param( 'id', 'requirements-fail-template' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		// Assert the endpoint returns 400 for unmet requirements.
		$this->assertSame( 400, $response->get_status() );

		// Assert the error code matches the one returned by validate_requirements().
		$data = $response->get_data();
		$this->assertSame( 'requirements_not_met', $data['code'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test that a successful import response includes a revision_id field.
	 *
	 * @since 3.7.0
	 */
	public function test_import_success_response_includes_revision_id() {
		wp_set_current_user( self::$admin_user_id );

		// Allow import.
		$this->seed_pro_license();

		// Create a dedicated page for this test.
		$page_id     = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Revision ID Test Page',
				'post_status' => 'publish',
			)
		);
		$template_id = 'test-template';
		edd_update_option( 'purchase_page', $page_id );

		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function( $preempt, $args, $url ) use ( $template_id ) {
				static $call_count = 0;
				$call_count++;
				if ( 1 === $call_count ) {
					return array(
						'response' => array( 'code' => 200 ),
						'body'     => wp_json_encode( array(
							'templates' => array( array(
								'template_id' => $template_id,
								'name'        => 'Test Template',
								'editors'     => array( 'elementor' ),
								'tags'        => array(),
								'settings'    => new \stdClass(),
							) ),
							'total' => 1,
						) ),
					);
				}
				return array(
					'response' => array( 'code' => 200 ),
					'body'     => wp_json_encode( array(
						'content' => array( array(
							'elType' => 'container', 'elements' => array(), 'settings' => array(),
						) ),
						'settings' => array( 'template' => 'elementor_canvas' ),
					) ),
				);
			},
			10,
			3
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/' . $template_id . '/import' );
		$request->set_param( 'id', $template_id );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();

		$this->assertArrayHasKey( 'revision_id', $data );
		if ( null !== $data['revision_id'] ) {
			$this->assertIsInt( $data['revision_id'] );
		}

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'wp_revisions_to_keep' );
	}

	/**
	 * Test that an Elementor import response includes a null compare_url.
	 *
	 * The response always carries the compare_url key, but for an Elementor import
	 * it must be null: Elementor stores its content in the _elementor_data meta, so
	 * a WordPress post-revision diff would show only the title -- not the layout.
	 * The "Compare with Original" link is therefore suppressed for Elementor.
	 *
	 * @since 3.7.0
	 */
	public function test_import_success_response_elementor_has_null_compare_url() {
		wp_set_current_user( self::$admin_user_id );

		// Allow import.
		$this->seed_pro_license();

		// Create a dedicated page for this test.
		$page_id     = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Compare URL Test Page',
				'post_status' => 'publish',
			)
		);
		$template_id = 'test-template';
		edd_update_option( 'purchase_page', $page_id );

		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function( $preempt, $args, $url ) use ( $template_id ) {
				static $call_count = 0;
				$call_count++;
				if ( 1 === $call_count ) {
					return array(
						'response' => array( 'code' => 200 ),
						'body'     => wp_json_encode( array(
							'templates' => array( array(
								'template_id' => $template_id,
								'name'        => 'Test Template',
								'editors'     => array( 'elementor' ),
								'tags'        => array(),
								'settings'    => new \stdClass(),
							) ),
							'total' => 1,
						) ),
					);
				}
				return array(
					'response' => array( 'code' => 200 ),
					'body'     => wp_json_encode( array(
						'content' => array( array(
							'elType' => 'container', 'elements' => array(), 'settings' => array(),
						) ),
						'settings' => array( 'template' => 'elementor_canvas' ),
					) ),
				);
			},
			10,
			3
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/' . $template_id . '/import' );
		$request->set_param( 'id', $template_id );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();

		// The key is always present, but Elementor imports must not offer a
		// compare link -- the WP revision diff would show only the title.
		$this->assertArrayHasKey( 'compare_url', $data );
		$this->assertNull( $data['compare_url'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'wp_revisions_to_keep' );
	}

	/**
	 * Test that the import is blocked when revisions are disabled.
	 *
	 * Importing overwrites the checkout page, so with revisions disabled there is
	 * no restore point. The importer must refuse rather than silently destroy the
	 * existing content.
	 *
	 * @since 3.7.0
	 */
	public function test_import_blocked_when_revisions_disabled() {
		wp_set_current_user( self::$admin_user_id );

		// Allow import.
		$this->seed_pro_license();

		// Disable revisions so wp_revisions_enabled() returns false.
		add_filter( 'wp_revisions_to_keep', '__return_zero' );

		// Create a dedicated page for this test.
		$page_id     = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'No Revisions Test Page',
				'post_status' => 'publish',
			)
		);
		$template_id = 'test-template';
		edd_update_option( 'purchase_page', $page_id );

		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function( $preempt, $args, $url ) use ( $template_id ) {
				static $call_count = 0;
				$call_count++;
				if ( 1 === $call_count ) {
					return array(
						'response' => array( 'code' => 200 ),
						'body'     => wp_json_encode( array(
							'templates' => array( array(
								'template_id' => $template_id,
								'name'        => 'Test Template',
								'editors'     => array( 'elementor' ),
								'tags'        => array(),
								'settings'    => new \stdClass(),
							) ),
							'total' => 1,
						) ),
					);
				}
				return array(
					'response' => array( 'code' => 200 ),
					'body'     => wp_json_encode( array(
						'content' => array( array(
							'elType' => 'container', 'elements' => array(), 'settings' => array(),
						) ),
						'settings' => array( 'template' => 'elementor_canvas' ),
					) ),
				);
			},
			10,
			3
		);

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/' . $template_id . '/import' );
		$request->set_param( 'id', $template_id );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		// The import must be refused with a 400 revisions_disabled error.
		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'revisions_disabled', $data['code'] );

		// The page content must be left untouched (no Elementor data written).
		$this->assertEmpty( get_post_meta( $page_id, '_elementor_data', true ) );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'wp_revisions_to_keep' );
	}

	/**
	 * Test the revision-compare predicate per importer.
	 *
	 * Block imports keep post_content, so a WordPress revision diff is meaningful
	 * and the compare link should be offered. Elementor stores its content in the
	 * _elementor_data meta, so the diff is useless and the link is suppressed.
	 *
	 * @since 3.7.0
	 */
	public function test_supports_revision_compare_per_importer() {
		$block_importer     = new \EDD\Pro\Checkout\Templates\Importer\BlockImporter();
		$elementor_importer = new \EDD\Pro\Checkout\Templates\Importer\ElementorImporter();

		$block_method = new \ReflectionMethod( $block_importer, 'supports_revision_compare' );
		if (PHP_VERSION_ID < 80100) {
            $block_method->setAccessible( true );
        }

		$elementor_method = new \ReflectionMethod( $elementor_importer, 'supports_revision_compare' );
		if (PHP_VERSION_ID < 80100) {
            $elementor_method->setAccessible( true );
        }

		$this->assertTrue( $block_method->invoke( $block_importer ), 'Block importer should support revision compare.' );
		$this->assertFalse( $elementor_method->invoke( $elementor_importer ), 'Elementor importer should not support revision compare.' );
	}

}
