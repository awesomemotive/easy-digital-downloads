<?php
/**
 * Restore Endpoint Tests
 *
 * @package     EDD\Tests\Checkout\Templates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\REST\Routes\CheckoutTemplates as ProRoute;

/**
 * RestoreEndpoint tests.
 *
 * Integration tests for the restore REST API endpoint.
 *
 * @since 3.7.0
 */
class RestoreEndpointTest extends EDD_UnitTestCase {

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

		( new ProRoute() )->register();
	}

	/**
	 * Tear down each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;

		remove_all_filters( 'wp_revisions_to_keep' );

		parent::tear_down();
	}

	/**
	 * Test restore route is registered.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_route_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/checkout-templates/restore', $routes );
	}

	/**
	 * Test restore requires authentication.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_requires_auth() {
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', 999 );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test restore requires shop management capability.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_requires_capability() {
		wp_set_current_user( self::$subscriber_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', 999 );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Test restore requires revision_id parameter.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_requires_revision_id() {
		wp_set_current_user( self::$admin_user_id );

		$request  = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Test restore rejects a non-existent revision.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_rejects_invalid_revision() {
		wp_set_current_user( self::$admin_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', 999999999 );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'invalid_revision', $data['code'] );
	}

	/**
	 * Test restore rejects a revision that belongs to a different page.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_rejects_revision_from_wrong_page() {
		wp_set_current_user( self::$admin_user_id );

		// Create a different page and save a revision for it.
		$other_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Other Page',
				'post_status' => 'publish',
			)
		);

		$revision_id = wp_save_post_revision( $other_page_id );
		if ( ! $revision_id || is_wp_error( $revision_id ) ) {
			wp_delete_post( $other_page_id, true );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'invalid_revision', $data['code'] );

		// Clean up.
		wp_delete_post( $other_page_id, true );
	}

	/**
	 * Test restore returns HTTP 200 with expected fields on success.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_success() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Restore Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Create a revision by updating the page.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['success'] );
		$this->assertSame( $page_id, $data['page_id'] );
		$this->assertArrayHasKey( 'edit_url', $data );
		$this->assertArrayHasKey( 'message', $data );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore creates a safety revision before restoring.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_creates_safety_revision_before_restoring() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Safety Revision Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Simulate an import by updating the page content.
		$imported_content = 'Imported template content';
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $imported_content,
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$this->server->dispatch( $request );

		// After restore, a revision containing the imported content should exist.
		// This proves wp_save_post_revision() ran before wp_restore_post_revision().
		$after_revisions = wp_get_post_revisions( $page_id );
		$found_safety    = false;
		foreach ( $after_revisions as $rev ) {
			if ( $imported_content === $rev->post_content ) {
				$found_safety = true;
				break;
			}
		}

		$this->assertTrue( $found_safety, 'Safety revision with imported content should exist after restore.' );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore restores the page content from the revision.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_restores_page_content() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Content Restore Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Create a revision by updating the page content.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content after import',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		// The first (most recent) revision holds the pre-update content.
		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		// Record the content stored in the revision before restoring.
		$revision_post    = get_post( $revision_id );
		$expected_content = $revision_post->post_content;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$this->server->dispatch( $request );

		$restored_page = get_post( $page_id );
		$this->assertSame( $expected_content, $restored_page->post_content );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore restores the _wp_page_template meta from backup.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_restores_page_template_meta() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Template Meta Restore Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Simulate an Elementor import: store the backup template meta.
		update_post_meta( $page_id, '_edd_checkout_template_previous_page_template', 'default' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );

		// Create a revision by updating the page.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content after import',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$this->server->dispatch( $request );

		// _wp_page_template should be restored from the backup meta.
		$this->assertSame( 'default', get_post_meta( $page_id, '_wp_page_template', true ) );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore deletes the previous template meta after restoring.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_deletes_previous_template_meta_after_restoring() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Meta Cleanup Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Store backup meta as would be done during an Elementor import.
		update_post_meta( $page_id, '_edd_checkout_template_previous_page_template', 'default' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );

		// Create a revision by updating the page.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content after import',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$this->server->dispatch( $request );

		// The backup meta key should be deleted after restoring.
		$this->assertEmpty( get_post_meta( $page_id, '_edd_checkout_template_previous_page_template', true ) );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore resets the page template to the theme default when the backed-up
	 * template was empty (the checkout page used the theme default before import).
	 *
	 * @since 3.7.0
	 */
	public function test_restore_resets_default_template_when_no_previous() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Default Template Reset Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Simulate an Elementor import over a theme-default page: the backup stores
		// an empty value (metadata_exists() is still true) and the live template
		// becomes elementor_canvas.
		update_post_meta( $page_id, '_edd_checkout_template_previous_page_template', '' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );

		// Create a revision by updating the page.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content after import',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$this->server->dispatch( $request );

		// _wp_page_template should be deleted so the page returns to the theme default.
		$this->assertFalse( metadata_exists( 'post', $page_id, '_wp_page_template' ) );

		// The backup meta key should be deleted after restoring.
		$this->assertFalse( metadata_exists( 'post', $page_id, '_edd_checkout_template_previous_page_template' ) );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore does not require a valid license.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_does_not_require_license() {
		wp_set_current_user( self::$admin_user_id );

		// Ensure no Pro license is seeded so import would be gated — restore must still work.
		delete_option( 'edd_pass_licenses' );
		delete_option( 'edd_pro_license_key' );
		delete_site_option( 'edd_pro_license_key' );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'No License Restore Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Create a revision by updating the page.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		// Restore must succeed even when the license filter returns false.
		$this->assertSame( 200, $response->get_status() );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore returns HTTP 200 even when revisions-to-keep is filtered to zero after revision creation.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_succeeds_when_revisions_disabled() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Revisions Disabled Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are already disabled in this environment.' );
		}

		// Create a revision by updating the page.
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => 'Updated checkout content',
			)
		);

		$revisions = wp_get_post_revisions( $page_id );
		if ( empty( $revisions ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revisions were created in this environment.' );
		}

		$revision    = reset( $revisions );
		$revision_id = $revision->ID;

		// AFTER the revision exists, filter revisions-to-keep to zero.
		// The existing revision record still exists in the database — the filter
		// only affects future saves. The restore should still succeed.
		add_filter( 'wp_revisions_to_keep', '__return_zero' );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore edit_url points to the Elementor editor when the checkout page is an Elementor page.
	 *
	 * @since 3.7.0
	 * @covers EDD\Pro\REST\Controllers\CheckoutTemplates::restore_template
	 */
	public function test_restore_edit_url_elementor_page() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Elementor Edit URL Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Mark page as an Elementor page.
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

		// Create a revision by updating the page.
		$revision_id = wp_save_post_revision( $page_id );
		if ( ! $revision_id || is_wp_error( $revision_id ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revision was created in this environment.' );
		}

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertStringContainsString( 'action=elementor', $data['edit_url'] );

		// Clean up.
		delete_post_meta( $page_id, '_elementor_edit_mode' );
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore edit_url points to the standard block editor when the checkout page is not an Elementor page.
	 *
	 * @since 3.7.0
	 * @covers EDD\Pro\REST\Controllers\CheckoutTemplates::restore_template
	 */
	public function test_restore_edit_url_block_page() {
		wp_set_current_user( self::$admin_user_id );

		// Create an isolated page for this test.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Block Editor Edit URL Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		if ( ! wp_revisions_enabled( get_post( $page_id ) ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'Revisions are disabled in this environment.' );
		}

		// Ensure no Elementor meta is present on this page.
		delete_post_meta( $page_id, '_elementor_edit_mode' );

		// Create a revision by updating the page.
		$revision_id = wp_save_post_revision( $page_id );
		if ( ! $revision_id || is_wp_error( $revision_id ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revision was created in this environment.' );
		}

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertStringContainsString( 'action=edit', $data['edit_url'] );
		$this->assertStringNotContainsString( 'action=elementor', $data['edit_url'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore sanitizes the revision_id parameter by converting a string like "999abc" to 999.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_sanitizes_revision_id() {
		wp_set_current_user( self::$admin_user_id );

		// The route's sanitize_callback is absint(), so "999abc" becomes 999.
		// Post 999 is very unlikely to exist as a valid revision, so expect a 400.
		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', '999abc' );
		$response = $this->server->dispatch( $request );

		// absint( "999abc" ) = 999; revision 999 almost certainly doesn't exist.
		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Test restore is denied for a user who can manage shop settings but cannot
	 * edit the checkout page (defense-in-depth edit_post check).
	 *
	 * The user must hold manage_shop_settings so can_restore() admits the request,
	 * and must NOT hold any page-editing capability so the per-page edit_post
	 * check inside restore_template() fails. No single in-repo fixture composes
	 * those two halves, so it is built here from the subscriber-creation idiom
	 * plus the add_cap idiom. A fresh user is created so the shared subscriber
	 * fixture used by the forbidden-path test is not mutated.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_denied_without_edit_post_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$user    = new \WP_User( $user_id );
		$user->add_cap( 'manage_shop_settings' );

		// Isolated checkout page with a real revision. restore_template validates
		// the revision first, so a bogus revision_id would fail earlier with
		// invalid_revision and never reach the capability check.
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Capability Check Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);
		edd_update_option( 'purchase_page', $page_id );

		$revision_id = wp_save_post_revision( $page_id );
		if ( ! $revision_id || is_wp_error( $revision_id ) ) {
			wp_delete_post( $page_id, true );
			edd_update_option( 'purchase_page', self::$checkout_page_id );
			$this->markTestSkipped( 'No revision was created in this environment.' );
		}

		wp_set_current_user( $user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'insufficient_permissions', $data['code'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}

	/**
	 * Test restore returns no_checkout_page (400) when the checkout page is unset,
	 * and that this branch fires before the capability check.
	 *
	 * A real page with a real revision is used so the revision validation (which
	 * runs first) passes -- a bogus revision_id would return invalid_revision,
	 * which is also 400, so a status-only assertion would pass vacuously. The
	 * error code is asserted to pin the specific branch.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_denied_when_no_checkout_page_configured() {
		wp_set_current_user( self::$admin_user_id );

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'No Checkout Page Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content',
			)
		);

		$revision_id = wp_save_post_revision( $page_id );
		if ( ! $revision_id || is_wp_error( $revision_id ) ) {
			wp_delete_post( $page_id, true );
			$this->markTestSkipped( 'No revision was created in this environment.' );
		}

		// Blank the checkout page so the no_checkout_page branch is reached.
		edd_update_option( 'purchase_page', 0 );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'revision_id', $revision_id );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'no_checkout_page', $data['code'] );

		// Clean up.
		wp_delete_post( $page_id, true );
		edd_update_option( 'purchase_page', self::$checkout_page_id );
	}
}
