<?php
namespace EDD\Tests\Logs\LogStorage;

use EDD\Admin\Tools\Logs\LogStorageCalculator;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Log Storage Calculator Tests
 *
 * Tests for the log storage calculation and caching.
 */
class Calculator extends EDD_UnitTestCase {

	/**
	 * Test fixtures - stores created log IDs for cleanup.
	 *
	 * @var array
	 */
	private $created_logs = array();

	/**
	 * Set up test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear any existing caches.
		LogStorageCalculator::invalidate_cache();

		// Reset log fixtures.
		$this->created_logs = array();
	}

	/**
	 * Clean up test environment.
	 */
	public function tearDown(): void {
		// Clean up created logs.
		$this->cleanup_logs();

		// Clear caches.
		LogStorageCalculator::invalidate_cache();

		parent::tearDown();
	}

	/**
	 * Test that get_storage returns an integer.
	 */
	public function test_get_storage_returns_integer() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'file_downloads';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'File downloads log type not registered' );
		}

		$storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		$this->assertIsInt( $storage );
		$this->assertGreaterThanOrEqual( 0, $storage );
	}

	/**
	 * Test that get_formatted_storage returns a string.
	 */
	public function test_get_formatted_storage_returns_string() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'file_downloads';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'File downloads log type not registered' );
		}

		$formatted = LogStorageCalculator::get_formatted_storage( $type_id, $log_types[ $type_id ] );

		$this->assertIsString( $formatted );
		$this->assertNotEmpty( $formatted );
	}

	/**
	 * Test storage calculation with real file download logs.
	 */
	public function test_storage_calculation_with_file_download_logs() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'file_downloads';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'File downloads log type not registered' );
		}

		// Get initial storage.
		LogStorageCalculator::invalidate_cache( $type_id );
		$initial_storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Create multiple file download logs using factory.
		$log_count      = 10;
		$expected_size  = 0;
		for ( $i = 0; $i < $log_count; $i++ ) {
			$log = parent::edd()->file_download_log->create_and_get(
				array(
					'product_id' => $i + 1,
					'file_id'    => $i + 1,
					'user_id'    => 1,
					'ip'         => '127.0.0.' . $i,
				)
			);
			$this->assertNotFalse( $log, 'File download log should be created' );
			$this->created_logs['file_downloads'][] = $log->id;

			// Calculate approximate expected size for this log.
			// ID = 8 bytes + data for all columns (product_id, file_id, order_id, etc.)
			$expected_size += 8; // ID column overhead
			$expected_size += strlen( (string) $log->product_id );
			$expected_size += strlen( (string) $log->file_id );
			$expected_size += strlen( (string) ( $log->order_id ?? '' ) );
			$expected_size += strlen( (string) ( $log->price_id ?? '' ) );
			$expected_size += strlen( (string) ( $log->customer_id ?? '' ) );
			$expected_size += strlen( (string) ( $log->user_id ?? '' ) );
			$expected_size += strlen( $log->ip ?? '' );
			$expected_size += strlen( $log->user_agent ?? '' );
			$expected_size += strlen( $log->date ?? '' );
		}

		// Get updated storage (invalidate cache first).
		LogStorageCalculator::invalidate_cache( $type_id );
		$updated_storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Calculate actual delta.
		$actual_delta = $updated_storage - $initial_storage;

		// Verify storage increase is within reasonable bounds.
		// Database storage differs from strlen() due to column types, but should be proportional.
		$min_expected = $expected_size * 0.5;  // At least 50% of our estimate.
		$max_expected = $expected_size * 4.0;  // No more than 4x our estimate.

		$this->assertGreaterThanOrEqual(
			$min_expected,
			$actual_delta,
			sprintf(
				'Storage delta (%d bytes) should be at least %d bytes (50%% of estimated %d bytes)',
				$actual_delta,
				$min_expected,
				$expected_size
			)
		);

		$this->assertLessThanOrEqual(
			$max_expected,
			$actual_delta,
			sprintf(
				'Storage delta (%d bytes) should be no more than %d bytes (400%% of estimated %d bytes)',
				$actual_delta,
				$max_expected,
				$expected_size
			)
		);
	}

	/**
	 * Test storage calculation with gateway error logs.
	 */
	public function test_storage_calculation_with_gateway_error_logs() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'gateway_errors';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'Gateway errors log type not registered' );
		}

		// Get initial storage.
		LogStorageCalculator::invalidate_cache( $type_id );
		$initial_storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Create multiple gateway error logs using factory.
		$log_count     = 15;
		$expected_size = 0;
		for ( $i = 0; $i < $log_count; $i++ ) {
			$log = parent::edd()->log->create_and_get(
				array(
					'type'    => 'gateway_error',
					'title'   => 'Payment Gateway Error ' . $i,
					'message' => 'This is a test error message for log entry ' . $i,
				)
			);
			$this->assertNotFalse( $log, 'Gateway error log should be created' );
			$this->created_logs['gateway_errors'][] = $log->id;

			// Calculate approximate expected size for this log.
			$expected_size += 8; // ID column overhead
			$expected_size += strlen( (string) ( $log->object_id ?? '' ) );
			$expected_size += strlen( $log->object_type ?? '' );
			$expected_size += strlen( $log->type ?? '' );
			$expected_size += strlen( $log->title ?? '' );
			$expected_size += strlen( $log->message ?? '' );
			$expected_size += strlen( $log->date ?? '' );
		}

		// Get updated storage (invalidate cache first).
		LogStorageCalculator::invalidate_cache( $type_id );
		$updated_storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Calculate actual delta.
		$actual_delta = $updated_storage - $initial_storage;

		// Verify storage increase is within reasonable bounds.
		// Database storage differs from strlen() due to column types, but should be proportional.
		$min_expected = $expected_size * 0.5;  // At least 50% of our estimate.
		$max_expected = $expected_size * 4.0;  // No more than 4x our estimate.

		$this->assertGreaterThanOrEqual(
			$min_expected,
			$actual_delta,
			sprintf(
				'Storage delta (%d bytes) should be at least %d bytes (50%% of estimated %d bytes)',
				$actual_delta,
				$min_expected,
				$expected_size
			)
		);

		$this->assertLessThanOrEqual(
			$max_expected,
			$actual_delta,
			sprintf(
				'Storage delta (%d bytes) should be no more than %d bytes (400%% of estimated %d bytes)',
				$actual_delta,
				$max_expected,
				$expected_size
			)
		);
	}

	/**
	 * Test storage calculation with API request logs.
	 */
	public function test_storage_calculation_with_api_request_logs() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'api_requests';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'API requests log type not registered' );
		}

		// Get initial storage.
		LogStorageCalculator::invalidate_cache( $type_id );
		$initial_storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Create multiple API request logs using factory.
		$log_count     = 20;
		$expected_size = 0;
		for ( $i = 0; $i < $log_count; $i++ ) {
			$log = parent::edd()->api_request_log->create_and_get(
				array(
					'token'   => 'test_token_' . $i,
					'version' => 'v2',
					'request' => '/products',
					'ip'      => '192.168.1.' . ( $i % 255 ),
				)
			);
			$this->assertNotFalse( $log, 'API request log should be created' );
			$this->created_logs['api_requests'][] = $log->id;

			// Calculate approximate expected size for this log.
			$expected_size += 8; // ID column overhead
			$expected_size += strlen( (string) ( $log->user_id ?? '' ) );
			$expected_size += strlen( $log->api_key ?? '' );
			$expected_size += strlen( $log->token ?? '' );
			$expected_size += strlen( $log->version ?? '' );
			$expected_size += strlen( $log->request ?? '' );
			$expected_size += strlen( $log->error ?? '' );
			$expected_size += strlen( $log->ip ?? '' );
			$expected_size += strlen( (string) ( $log->time ?? '' ) );
			$expected_size += strlen( $log->date ?? '' );
		}

		// Get updated storage (invalidate cache first).
		LogStorageCalculator::invalidate_cache( $type_id );
		$updated_storage = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Calculate actual delta.
		$actual_delta = $updated_storage - $initial_storage;

		// Verify storage increase is within reasonable bounds.
		// Database storage differs from strlen() due to column types, but should be proportional.
		$min_expected = $expected_size * 0.5;  // At least 50% of our estimate.
		$max_expected = $expected_size * 4.0;  // No more than 4x our estimate.

		$this->assertGreaterThanOrEqual(
			$min_expected,
			$actual_delta,
			sprintf(
				'Storage delta (%d bytes) should be at least %d bytes (50%% of estimated %d bytes)',
				$actual_delta,
				$min_expected,
				$expected_size
			)
		);

		$this->assertLessThanOrEqual(
			$max_expected,
			$actual_delta,
			sprintf(
				'Storage delta (%d bytes) should be no more than %d bytes (400%% of estimated %d bytes)',
				$actual_delta,
				$max_expected,
				$expected_size
			)
		);
	}

	/**
	 * Test that storage caching works correctly.
	 */
	public function test_storage_caching() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'file_downloads';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'File downloads log type not registered' );
		}

		// Clear cache and get initial storage.
		LogStorageCalculator::invalidate_cache( $type_id );
		$first_call = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );

		// Add a new log using factory.
		$log = parent::edd()->file_download_log->create_and_get(
			array(
				'product_id' => 999,
				'file_id'    => 999,
				'user_id'    => 1,
				'ip'         => '10.0.0.1',
			)
		);
		$this->assertNotFalse( $log, 'File download log should be created' );
		$this->created_logs['file_downloads'][] = $log->id;

		// Second call should return cached value (same as first).
		$second_call = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );
		$this->assertEquals( $first_call, $second_call, 'Storage should be cached' );

		// Invalidate cache and third call should reflect new log.
		LogStorageCalculator::invalidate_cache( $type_id );
		$third_call = LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );
		$this->assertGreaterThan( $second_call, $third_call, 'Storage should increase after cache invalidation' );
	}

	/**
	 * Test that invalidate_cache clears all log type caches when no type specified.
	 */
	public function test_invalidate_all_caches() {
		global $wpdb;

		$log_types = \EDD\Logs\Registry::get_types();

		// Prime caches for multiple log types.
		foreach ( array( 'file_downloads', 'gateway_errors', 'api_requests' ) as $type_id ) {
			if ( isset( $log_types[ $type_id ] ) ) {
				LogStorageCalculator::invalidate_cache( $type_id );
				LogStorageCalculator::get_storage( $type_id, $log_types[ $type_id ] );
			}
		}

		// Verify transient options exist in database.
		$count_before = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_edd_log_storage_%'
			)
		);
		$this->assertGreaterThan( 0, $count_before, 'Transients should exist before invalidation' );

		// Clear all caches.
		LogStorageCalculator::invalidate_cache();

		// Verify all transient options are deleted from database.
		$count_after = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_edd_log_storage_%',
				'_transient_timeout_edd_log_storage_%'
			)
		);
		$this->assertEquals( 0, $count_after, 'All transients should be deleted after invalidation' );
	}

	/**
	 * Test the edd_log_storage_table_columns filter.
	 */
	public function test_log_storage_table_columns_filter() {
		$log_types = \EDD\Logs\Registry::get_types();

		// Create a custom log type config that will trigger the filter fallback.
		$custom_type_config = array(
			'label'        => 'Test Custom Type',
			'table'        => 'test_custom_table',
			'query_class'  => 'Test_Query',
			'prunable'     => true,
			'default_days' => 30,
		);

		$filter_called = false;
		$filter_callback = function( $columns, $table ) use ( &$filter_called ) {
			$filter_called = true;
			if ( 'test_custom_table' === $table ) {
				return array(
					'test_custom_table' => array( 'column1', 'column2', 'column3' ),
				);
			}
			return $columns;
		};

		add_filter( 'edd_log_storage_table_columns', $filter_callback, 10, 2 );

		// This will fail at the database level since the table doesn't exist,
		// but we can verify the filter was called.
		$storage = LogStorageCalculator::get_storage( 'test_custom', $custom_type_config );

		// The filter should have been called even if calculation fails.
		$this->assertTrue( $filter_called, 'Filter edd_log_storage_table_columns should be called' );

		remove_filter( 'edd_log_storage_table_columns', $filter_callback, 10 );
	}

	/**
	 * Test the edd_log_table_component_map filter.
	 */
	public function test_log_table_component_map_filter() {
		$filter_called = false;
		$filter_callback = function( $map ) use ( &$filter_called ) {
			$filter_called = true;
			// Add a custom mapping.
			$map['edd_logs_custom'] = 'custom_log_component';
			return $map;
		};

		add_filter( 'edd_log_table_component_map', $filter_callback );

		// Create a config with a custom table.
		$custom_type_config = array(
			'label'        => 'Custom Log Type',
			'table'        => 'edd_logs_custom',
			'query_class'  => 'Test_Query',
			'prunable'     => true,
			'default_days' => 30,
		);

		// Attempt to get storage (will fail but should trigger filter).
		$storage = LogStorageCalculator::get_storage( 'custom', $custom_type_config );

		$this->assertTrue( $filter_called, 'Filter edd_log_table_component_map should be called' );

		remove_filter( 'edd_log_table_component_map', $filter_callback );
	}

	/**
	 * Test storage calculation returns zero for invalid table.
	 */
	public function test_storage_returns_zero_for_invalid_table() {
		$type_config = array(
			'table' => 'nonexistent_table',
		);

		$storage = LogStorageCalculator::get_storage( 'invalid', $type_config );

		$this->assertEquals( 0, $storage );
	}

	/**
	 * Test storage calculation returns zero for missing table config.
	 */
	public function test_storage_returns_zero_for_missing_table_config() {
		$type_config = array(
			'label' => 'Test Type',
		);

		$storage = LogStorageCalculator::get_storage( 'test', $type_config );

		$this->assertEquals( 0, $storage );
	}

	/**
	 * Test that formatted storage uses WordPress size_format.
	 */
	public function test_formatted_storage_uses_size_format() {
		$log_types = \EDD\Logs\Registry::get_types();
		$type_id   = 'file_downloads';

		if ( ! isset( $log_types[ $type_id ] ) ) {
			$this->markTestSkipped( 'File downloads log type not registered' );
		}

		$formatted = LogStorageCalculator::get_formatted_storage( $type_id, $log_types[ $type_id ] );

		// Should contain typical size format indicators (including "0 B").
		$this->assertMatchesRegularExpression(
			'/\d+(\.\d+)?\s*(?:bytes?|B|KB|MB|GB|TB|PB)/i',
			$formatted,
			'Formatted storage should match size format pattern'
		);
	}

	/**
	 * Helper: Clean up created logs.
	 */
	private function cleanup_logs() {
		if ( ! empty( $this->created_logs['file_downloads'] ) ) {
			parent::edd()->file_download_log->delete_many( $this->created_logs['file_downloads'] );
		}

		if ( ! empty( $this->created_logs['gateway_errors'] ) ) {
			parent::edd()->log->delete_many( $this->created_logs['gateway_errors'] );
		}

		if ( ! empty( $this->created_logs['api_requests'] ) ) {
			parent::edd()->api_request_log->delete_many( $this->created_logs['api_requests'] );
		}
	}
}
