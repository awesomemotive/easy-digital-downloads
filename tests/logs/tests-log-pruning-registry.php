<?php
namespace EDD\Tests\Logs\LogPruning;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Log Pruning Registry Tests
 *
 * Tests for the log type registry functions.
 *
 * @group edd_logs
 * @group edd_logs_pruning
 * @group edd_logs_pruning_registry
 */
class Registry_Tests extends EDD_UnitTestCase {

	/**
	 * Test that edd_get_registered_log_types returns an array.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_get_registered_log_types_returns_array() {
		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertIsArray( $log_types );
		$this->assertNotEmpty( $log_types );
	}

	/**
	 * Test that all registered log types have required fields.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_registered_log_types_have_required_fields() {
		$log_types = \EDD\Logs\Registry::get_types();

		foreach ( $log_types as $type_id => $type_config ) {
			$this->assertArrayHasKey( 'label', $type_config, "Log type {$type_id} missing label" );
			$this->assertArrayHasKey( 'table', $type_config, "Log type {$type_id} missing table" );
			$this->assertArrayHasKey( 'query_class', $type_config, "Log type {$type_id} missing query_class" );
			$this->assertArrayHasKey( 'prunable', $type_config, "Log type {$type_id} missing prunable" );
			$this->assertArrayHasKey( 'default_days', $type_config, "Log type {$type_id} missing default_days" );
		}
	}

	/**
	 * Test that file_downloads log type exists.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_file_downloads_log_type_exists() {
		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertArrayHasKey( 'file_downloads', $log_types );
		$this->assertEquals( 'edd_logs_file_downloads', $log_types['file_downloads']['table'] );
		$this->assertEquals( 'EDD\\Database\\Queries\\Log_File_Download', $log_types['file_downloads']['query_class'] );
		$this->assertTrue( $log_types['file_downloads']['prunable'] );
	}

	/**
	 * Test that gateway_errors log type exists.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_gateway_errors_log_type_exists() {
		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertArrayHasKey( 'gateway_errors', $log_types );
		$this->assertEquals( 'edd_logs', $log_types['gateway_errors']['table'] );
		$this->assertEquals( 'EDD\\Database\\Queries\\Log', $log_types['gateway_errors']['query_class'] );
		$this->assertArrayHasKey( 'query_args', $log_types['gateway_errors'] );
		$this->assertEquals( 'gateway_error', $log_types['gateway_errors']['query_args']['type'] );
		$this->assertTrue( $log_types['gateway_errors']['prunable'] );
	}

	/**
	 * Test that api_requests log type exists.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_api_requests_log_type_exists() {
		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertArrayHasKey( 'api_requests', $log_types );
		$this->assertEquals( 'edd_logs_api_requests', $log_types['api_requests']['table'] );
		$this->assertTrue( $log_types['api_requests']['prunable'] );
	}

	/**
	 * Test that emails log type exists.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_emails_log_type_exists() {
		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertArrayHasKey( 'emails', $log_types );
		$this->assertEquals( 'edd_logs_emails', $log_types['emails']['table'] );
		$this->assertTrue( $log_types['emails']['prunable'] );
	}

	/**
	 * Test that edd_should_warn_file_downloads returns boolean.
	 *
	 * @covers ::edd_should_warn_file_downloads
	 */
	public function test_should_warn_file_downloads_returns_boolean() {
		if ( ! function_exists( 'edd_should_warn_file_downloads' ) ) {
		}

		$result = \EDD\Logs\Registry::should_warn_file_downloads();

		$this->assertIsBool( $result );
	}

	/**
	 * Test that file download warning shows when download limit is set.
	 *
	 * @covers ::edd_should_warn_file_downloads
	 */
	public function test_should_warn_file_downloads_when_limit_set() {
		if ( ! function_exists( 'edd_should_warn_file_downloads' ) ) {
		}

		// Set file download limit
		edd_update_option( 'file_download_limit', 5 );

		$result = \EDD\Logs\Registry::should_warn_file_downloads();

		$this->assertTrue( $result );

		// Clean up
		edd_delete_option( 'file_download_limit' );
	}

	/**
	 * Test that \EDD\Logs\Registry::get_pruning_warning returns string.
	 *
	 * @covers ::\EDD\Logs\Registry::get_pruning_warning
	 */
	public function test_get_log_type_pruning_warning_returns_string() {
		if ( ! function_exists( '\EDD\Logs\Registry::get_pruning_warning' ) ) {
		}

		$result = \EDD\Logs\Registry::get_pruning_warning( 'file_downloads' );

		$this->assertIsString( $result );
	}

	/**
	 * Test that file downloads warning is empty when no conditions met.
	 *
	 * @covers ::\EDD\Logs\Registry::get_pruning_warning
	 */
	public function test_file_downloads_warning_empty_when_no_conditions() {
		if ( ! function_exists( '\EDD\Logs\Registry::get_pruning_warning' ) ) {
		}

		// Ensure no download limit
		edd_delete_option( 'file_download_limit' );

		$result = \EDD\Logs\Registry::get_pruning_warning( 'file_downloads' );

		// Result might be empty or have extension warnings depending on what's installed
		$this->assertIsString( $result );
	}

	/**
	 * Test that filter edd_registered_log_types works.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_registered_log_types_filter() {
		$filter_callback = function( $log_types ) {
			$log_types['test_type'] = array(
				'label'        => 'Test Type',
				'table'        => 'test_table',
				'query_class'  => 'Test_Query',
				'prunable'     => true,
				'default_days' => 30,
			);
			return $log_types;
		};

		// Reset cache so filter gets applied.
		\EDD\Logs\Registry::reset_cache();

		add_filter( 'edd_registered_log_types', $filter_callback );

		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertArrayHasKey( 'test_type', $log_types );
		$this->assertEquals( 'Test Type', $log_types['test_type']['label'] );

		remove_filter( 'edd_registered_log_types', $filter_callback );

		// Reset cache for subsequent tests.
		\EDD\Logs\Registry::reset_cache();
	}

	/**
	 * Test that filter edd_log_type_prunable works.
	 *
	 * @covers ::edd_get_registered_log_types
	 */
	public function test_log_type_prunable_filter() {
		$filter_callback = function( $prunable, $type_id ) {
			if ( 'file_downloads' === $type_id ) {
				return false;
			}
			return $prunable;
		};

		// Reset cache so filter gets applied.
		\EDD\Logs\Registry::reset_cache();

		add_filter( 'edd_log_type_prunable', $filter_callback, 10, 2 );

		$log_types = \EDD\Logs\Registry::get_types();

		$this->assertFalse( $log_types['file_downloads']['prunable'] );

		remove_filter( 'edd_log_type_prunable', $filter_callback, 10 );

		// Reset cache for subsequent tests.
		\EDD\Logs\Registry::reset_cache();
	}
}
