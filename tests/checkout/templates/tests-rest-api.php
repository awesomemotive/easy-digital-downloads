<?php
/**
 * Checkout Templates REST API Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Routes\CheckoutTemplates as CheckoutTemplatesRoute;
use EDD\Pro\REST\Routes\CheckoutTemplates as ProRoute;
use EDD\Pro\REST\Controllers\CheckoutTemplates as ProController;
use EDD\Tests\Helpers\Licenses as LicenseData;

/**
 * RestAPI tests.
 *
 * Integration tests for the Checkout Templates REST API endpoints.
 *
 * @group rest-api
 */
class RestAPITest extends EDD_UnitTestCase {

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
	 * Set up fixtures before class runs.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Create admin user with shop management caps.
		self::$admin_user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);

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

		$route = new CheckoutTemplatesRoute();
		$route->register();

		// Register the Pro import/restore routes.
		$pro_route = new ProRoute();
		$pro_route->register();
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
	 * Test routes are registered.
	 */
	public function test_routes_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/checkout-templates', $routes );
		$this->assertArrayHasKey( '/edd/v3/checkout-templates/(?P<id>[\w-]+)/import', $routes );
	}

	/**
	 * Test get_templates requires authentication.
	 */
	public function test_get_templates_requires_auth() {
		wp_set_current_user( 0 );

		$request  = new \WP_REST_Request( 'GET', '/edd/v3/checkout-templates' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test get_templates requires manage_shop_settings capability.
	 */
	public function test_get_templates_requires_capability() {
		wp_set_current_user( self::$subscriber_user_id );

		$request  = new \WP_REST_Request( 'GET', '/edd/v3/checkout-templates' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Test get_templates returns templates for authorized users.
	 */
	public function test_get_templates_returns_templates() {
		// Enable mock mode for testing.
		if ( ! defined( 'EDD_CTI_USE_MOCKS' ) ) {
			define( 'EDD_CTI_USE_MOCKS', true );
		}

		wp_set_current_user( self::$admin_user_id );

		$request  = new \WP_REST_Request( 'GET', '/edd/v3/checkout-templates' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'templates', $data );
		$this->assertArrayHasKey( 'can_import', $data );
		$this->assertArrayHasKey( 'license_status', $data );
		$this->assertIsArray( $data['templates'] );
	}

	/**
	 * Test import_template requires authentication.
	 */
	public function test_import_template_requires_auth() {
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test import_template requires capability.
	 */
	public function test_import_template_requires_capability() {
		wp_set_current_user( self::$subscriber_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'editor', 'elementor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Test import_template validates editor parameter.
	 */
	public function test_import_template_validates_editor() {
		wp_set_current_user( self::$admin_user_id );

		// Seed a valid Pro license so the gate passes and we can test validation.
		$this->seed_pro_license();

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$request->set_param( 'editor', 'invalid_editor' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		// Should fail validation - editor must be 'blocks' or 'elementor'.
		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Test check_permission returns true for admin with capability.
	 */
	public function test_can_manage_with_capability() {
		wp_set_current_user( self::$admin_user_id );

		$route   = new CheckoutTemplatesRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$this->assertTrue( $route->check_permission( $request ) );
	}

	/**
	 * Test check_permission returns WP_Error without capability.
	 */
	public function test_can_manage_without_capability() {
		wp_set_current_user( self::$subscriber_user_id );

		$route   = new CheckoutTemplatesRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$result  = $route->check_permission( $request );
		$this->assertWPError( $result );
	}

	/**
	 * Test check_permission returns WP_Error for logged out users.
	 */
	public function test_can_manage_logged_out() {
		wp_set_current_user( 0 );

		$route   = new CheckoutTemplatesRoute();
		$request = new \WP_REST_Request();
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$result  = $route->check_permission( $request );
		$this->assertWPError( $result );
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
	 * Test template list filter is applied.
	 */
	public function test_templates_filter_is_applied() {
		if ( ! defined( 'EDD_CTI_USE_MOCKS' ) ) {
			define( 'EDD_CTI_USE_MOCKS', true );
		}

		wp_set_current_user( self::$admin_user_id );

		$filter_called = false;

		add_filter(
			'edd_checkout_templates',
			function( $templates ) use ( &$filter_called ) {
				$filter_called = true;
				return $templates;
			}
		);

		$request  = new \WP_REST_Request( 'GET', '/edd/v3/checkout-templates' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$this->server->dispatch( $request );

		$this->assertTrue( $filter_called );
	}

	/**
	 * U2 — resolve_editor_importer() returns the WordPress requirement error for the block editor.
	 *
	 * The block editor is WordPress core, so the message must tell the user to update
	 * WordPress (never to "install/update the Block Editor") and must carry no install
	 * action. The block-editor key is Constants::EDITOR_BLOCKS ('blocks'); its importer
	 * can_import() is false unconditionally today, so this branch fires on any host.
	 */
	public function test_resolve_editor_importer_blocks_unavailable() {
		$controller = new ProController();
		$result     = $controller->resolve_editor_importer( \EDD\Checkout\Templates\Config\Constants::EDITOR_BLOCKS );

		$this->assertWPError( $result );
		$this->assertSame( 'importer_unavailable', $result->get_error_code() );

		$message = $result->get_error_message();
		$this->assertStringContainsString( 'WordPress', $message );
		$this->assertStringContainsString( '6.7', $message );
		$this->assertStringNotContainsString( 'Block Editor', $message );

		$data = $result->get_error_data();
		$this->assertArrayNotHasKey( 'action', $data );
		$this->assertArrayNotHasKey( 'action_url', $data );
	}
}
