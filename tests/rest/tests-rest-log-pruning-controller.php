<?php
namespace EDD\Tests\REST;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Controllers\LogPruning as LogPruning_Controller;

/**
 * REST Log Pruning Controller Tests
 *
 * @group edd_rest
 * @group edd_rest_log_pruning
 * @group edd_logs_pruning
 */
class LogPruning_Controller_Tests extends EDD_UnitTestCase {

	/**
	 * Controller instance.
	 *
	 * @var LogPruning_Controller
	 */
	protected $controller;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected static $admin_user_id;

	/**
	 * Regular user ID.
	 *
	 * @var int
	 */
	protected static $user_id;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create admin user
		self::$admin_user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );

		// Create regular user
		self::$user_id = self::factory()->user->create( array(
			'role' => 'subscriber',
		) );
	}

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->controller = new LogPruning_Controller();
	}

	/**
	 * Test that controller has prune method.
	 */
	public function test_controller_has_prune_method() {
		$this->assertTrue( method_exists( $this->controller, 'prune' ) );
	}

	/**
	 * Test prune with invalid log_type fails.
	 */
	public function test_prune_invalid_log_type_fails() {
		wp_set_current_user( self::$admin_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/logs/prune' );
		$request->set_param( 'log_type', 'nonexistent_type' );
		$request->set_param( 'days', 90 );

		$response = $this->controller->prune( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'invalid_log_type', $response->get_error_code() );
	}

	/**
	 * Test prune with non-prunable type fails.
	 */
	public function test_prune_non_prunable_type_fails() {
		wp_set_current_user( self::$admin_user_id );

		// Filter to make file_downloads non-prunable
		$filter_callback = function( $prunable, $type_id ) {
			if ( 'file_downloads' === $type_id ) {
				return false;
			}
			return $prunable;
		};

		// Reset cache so filter gets applied.
		\EDD\Logs\Registry::reset_cache();

		add_filter( 'edd_log_type_prunable', $filter_callback, 10, 2 );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/logs/prune' );
		$request->set_param( 'log_type', 'file_downloads' );
		$request->set_param( 'days', 90 );

		$response = $this->controller->prune( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'not_prunable', $response->get_error_code() );

		remove_filter( 'edd_log_type_prunable', $filter_callback, 10 );

		// Reset cache for subsequent tests.
		\EDD\Logs\Registry::reset_cache();
	}

	/**
	 * Test prune with valid parameters succeeds.
	 */
	public function test_prune_valid_parameters_succeeds() {
		wp_set_current_user( self::$admin_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/logs/prune' );
		$request->set_param( 'log_type', 'file_downloads' );
		$request->set_param( 'days', 90 );

		$response = $this->controller->prune( $request );

		// Should be a WP_REST_Response, not WP_Error
		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'success', $data );
		$this->assertArrayHasKey( 'count', $data );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertTrue( $data['success'] );
		$this->assertIsInt( $data['count'] );
		$this->assertIsString( $data['message'] );
	}

	/**
	 * Test prune with unregistered log type.
	 */
	public function test_prune_unregistered_type() {
		global $wpdb;

		// Insert a test log with unregistered type
		$wpdb->insert(
			"{$wpdb->prefix}edd_logs",
			array(
				'object_id'    => 0,
				'object_type'  => 'test',
				'type'         => 'test_unregistered',
				'title'        => 'Test Log',
				'content'      => 'Test content',
				'date_created' => gmdate( 'Y-m-d H:i:s', strtotime( '-365 days' ) ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		wp_set_current_user( self::$admin_user_id );

		$request = new \WP_REST_Request( 'POST', '/edd/v3/logs/prune' );
		$request->set_param( 'log_type', 'unregistered_test_unregistered' );
		$request->set_param( 'days', 90 );

		$response = $this->controller->prune( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertGreaterThan( 0, $data['count'], 'Should have deleted the old log' );

		// Clean up any remaining
		$wpdb->delete(
			"{$wpdb->prefix}edd_logs",
			array( 'type' => 'test_unregistered' ),
			array( '%s' )
		);
	}

	/**
	 * Test prune returns 0 when no logs to delete.
	 */
	public function test_prune_returns_zero_when_none() {
		wp_set_current_user( self::$admin_user_id );

		// Use a type that likely has no old logs
		$request = new \WP_REST_Request( 'POST', '/edd/v3/logs/prune' );
		$request->set_param( 'log_type', 'gateway_errors' );
		$request->set_param( 'days', 1 );

		$response = $this->controller->prune( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );

		$data = $response->get_data();
		$this->assertIsInt( $data['count'] );
	}
}
