<?php
namespace EDD\Tests\Logs\LogPruning;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Components\LogPruning;

/**
 * Log Pruning Component Tests
 *
 * Tests for the LogPruning cron component.
 *
 * @group edd_logs
 * @group edd_logs_pruning
 * @group edd_logs_pruning_component
 * @group edd_cron
 */
class Component_Tests extends EDD_UnitTestCase {

	/**
	 * Component instance.
	 *
	 * @var LogPruning
	 */
	protected $component;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Load the registry
		$this->component = new LogPruning();
	}

	/**
	 * Test that component has correct ID.
	 */
	public function test_component_has_id() {
		$reflection = new \ReflectionClass( $this->component );
		$property = $reflection->getProperty( 'id' );
		$property->setAccessible( true );
		$id = $property->getValue();

		$this->assertEquals( 'log_pruning', $id );
	}

	/**
	 * Test that get_subscribed_events returns array.
	 */
	public function test_get_subscribed_events_returns_array() {
		$events = LogPruning::get_subscribed_events();

		$this->assertIsArray( $events );
	}

	/**
	 * Test that subscribed events include init hook.
	 */
	public function test_subscribed_events_includes_init() {
		$events = LogPruning::get_subscribed_events();

		$this->assertArrayHasKey( 'init', $events );
		$this->assertEquals( 'register_pruning_hooks', $events['init'] );
	}

	/**
	 * Test that register_pruning_hooks does nothing when globally disabled.
	 */
	public function test_register_pruning_hooks_disabled_globally() {
		// Disable pruning globally.
		edd_update_option( 'edd_log_pruning_settings', array(
			'enabled' => false,
		) );

		// Remove any existing hooks.
		remove_all_actions( 'edd_prune_logs_file_downloads' );

		$this->component->register_pruning_hooks();

		// No hooks should be registered.
		$this->assertFalse( has_action( 'edd_prune_logs_file_downloads' ) );

		// Clean up.
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test that register_pruning_hooks registers hooks for enabled types.
	 */
	public function test_register_pruning_hooks_enabled_types() {
		// Enable pruning globally.
		edd_update_option( 'log_pruning_enabled', true );

		// Configure individual log types.
		edd_update_option( 'edd_log_pruning_settings', array(
			'log_types' => array(
				'file_downloads' => array(
					'enabled' => true,
					'days'    => 90,
				),
				'gateway_errors' => array(
					'enabled' => false,
					'days'    => 30,
				),
			),
		) );

		// Remove any existing hooks.
		remove_all_actions( 'edd_prune_logs_file_downloads' );
		remove_all_actions( 'edd_prune_logs_gateway_errors' );

		$this->component->register_pruning_hooks();

		// file_downloads should have a hook registered.
		$this->assertNotFalse( has_action( 'edd_prune_logs_file_downloads' ) );

		// gateway_errors should NOT have a hook registered (disabled).
		$this->assertFalse( has_action( 'edd_prune_logs_gateway_errors' ) );

		// Clean up.
		remove_all_actions( 'edd_prune_logs_file_downloads' );
		edd_delete_option( 'log_pruning_enabled' );
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test that register_pruning_hooks handles unregistered types.
	 */
	public function test_register_pruning_hooks_unregistered_types() {
		// Enable pruning globally.
		edd_update_option( 'log_pruning_enabled', true );

		// Configure unregistered type.
		edd_update_option( 'edd_log_pruning_settings', array(
			'log_types' => array(
				'unregistered_custom_type' => array(
					'enabled' => true,
					'days'    => 60,
				),
			),
		) );

		// Remove any existing hooks.
		remove_all_actions( 'edd_prune_logs_unregistered_custom_type' );

		$this->component->register_pruning_hooks();

		// The unregistered type should have a hook registered.
		$this->assertNotFalse( has_action( 'edd_prune_logs_unregistered_custom_type' ) );

		// Clean up.
		remove_all_actions( 'edd_prune_logs_unregistered_custom_type' );
		edd_delete_option( 'log_pruning_enabled' );
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test prune_log_type with invalid inputs returns 0.
	 */
	public function test_prune_log_type_invalid_inputs() {
		// Empty type_id
		$result = LogPruning::prune_log_type( '', array(), 30 );
		$this->assertEquals( 0, $result );

		// Empty type_config
		$result = LogPruning::prune_log_type( 'test', array(), 30 );
		$this->assertEquals( 0, $result );

		// Invalid days
		$result = LogPruning::prune_log_type( 'test', array( 'prunable' => true ), 0 );
		$this->assertEquals( 0, $result );
	}

	/**
	 * Test prune_log_type with non-prunable type returns 0.
	 */
	public function test_prune_log_type_non_prunable() {
		$type_config = array(
			'label'       => 'Test Type',
			'table'       => 'test_table',
			'query_class' => 'Test_Query',
			'prunable'    => false,
		);

		$result = LogPruning::prune_log_type( 'test', $type_config, 30 );

		$this->assertEquals( 0, $result );
	}

	/**
	 * Test prune_log_type with missing query class returns 0.
	 */
	public function test_prune_log_type_missing_query_class() {
		$type_config = array(
			'label'       => 'Test Type',
			'table'       => 'test_table',
			'query_class' => 'NonExistentQueryClass',
			'prunable'    => true,
		);

		$result = LogPruning::prune_log_type( 'test', $type_config, 30 );

		$this->assertEquals( 0, $result );
	}

	/**
	 * Test get_prune_count with invalid inputs returns 0.
	 */
	public function test_get_prune_count_invalid_inputs() {
		// Empty type_config
		$result = LogPruning::get_prune_count( array(), 30 );
		$this->assertEquals( 0, $result );

		// Invalid days
		$result = LogPruning::get_prune_count( array( 'prunable' => true ), 0 );
		$this->assertEquals( 0, $result );
	}

	/**
	 * Test get_prune_count with non-prunable type returns 0.
	 */
	public function test_get_prune_count_non_prunable() {
		$type_config = array(
			'label'       => 'Test Type',
			'table'       => 'test_table',
			'query_class' => 'Test_Query',
			'prunable'    => false,
		);

		$result = LogPruning::get_prune_count( $type_config, 30 );

		$this->assertEquals( 0, $result );
	}

	/**
	 * Test get_prune_count with missing query class returns 0.
	 */
	public function test_get_prune_count_missing_query_class() {
		$type_config = array(
			'label'       => 'Test Type',
			'table'       => 'test_table',
			'query_class' => 'NonExistentQueryClass',
			'prunable'    => true,
		);

		$result = LogPruning::get_prune_count( $type_config, 30 );

		$this->assertEquals( 0, $result );
	}

	/**
	 * Test that batch_size is clamped to valid range.
	 *
	 * We test this indirectly by verifying the method doesn't fail with extreme values.
	 */
	public function test_batch_size_clamped() {
		$type_config = array(
			'label'       => 'Test Type',
			'table'       => 'edd_logs',
			'query_class' => 'EDD\\Database\\Queries\\Log',
			'query_args'  => array( 'type' => 'nonexistent_type' ),
			'prunable'    => true,
		);

		// Should not throw errors even with extreme batch sizes
		$result = LogPruning::prune_log_type( 'test', $type_config, 90, 10 );
		$this->assertIsInt( $result );

		$result = LogPruning::prune_log_type( 'test', $type_config, 90, 5000 );
		$this->assertIsInt( $result );
	}

	/**
	 * Test prune_single_log_type when pruning is disabled globally.
	 */
	public function test_prune_single_log_type_disabled_globally() {
		// Disable pruning globally
		edd_update_option( 'edd_log_pruning_settings', array(
			'enabled' => false,
		) );

		// Simulate the hook being called
		$this->component->prune_single_log_type();

		// No assertions needed - just verify it doesn't error
		$this->assertTrue( true );

		// Clean up
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test prune_single_log_type when type is disabled.
	 */
	public function test_prune_single_log_type_type_disabled() {
		// Enable globally but disable file_downloads type
		edd_update_option( 'edd_log_pruning_settings', array(
			'enabled'   => true,
			'log_types' => array(
				'file_downloads' => array(
					'enabled' => false,
					'days'    => 90,
				),
			),
		) );

		// Simulate being called for file_downloads
		add_filter( 'current_filter', function() {
			return 'edd_prune_logs_file_downloads';
		} );

		$this->component->prune_single_log_type();

		// Clean up
		remove_all_filters( 'current_filter' );
		edd_delete_option( 'edd_log_pruning_settings' );

		// No assertions needed - just verify it doesn't error
		$this->assertTrue( true );
	}

	/**
	 * Test Registry::get_unregistered_type_config with valid unregistered type.
	 *
	 * Note: This method was moved from Component to Registry for DRY.
	 */
	public function test_get_unregistered_type_config() {
		$result = \EDD\Logs\Registry::get_unregistered_type_config( 'unregistered_test_type' );

		$this->assertIsArray( $result );
		$this->assertEquals( 'edd_logs', $result['table'] );
		$this->assertEquals( 'EDD\\Database\\Queries\\Log', $result['query_class'] );
		$this->assertEquals( array( 'type' => 'test_type' ), $result['query_args'] );
		$this->assertTrue( $result['prunable'] );
	}

	/**
	 * Test Registry::get_unregistered_type_config with unprefixed type.
	 *
	 * The Registry method accepts both prefixed and unprefixed type IDs.
	 */
	public function test_get_unregistered_type_config_unprefixed() {
		$result = \EDD\Logs\Registry::get_unregistered_type_config( 'test_type' );

		$this->assertIsArray( $result );
		$this->assertEquals( 'edd_logs', $result['table'] );
		$this->assertEquals( array( 'type' => 'test_type' ), $result['query_args'] );
	}

	/**
	 * Test Registry::get_unregistered_type_config with empty type returns null.
	 */
	public function test_get_unregistered_type_config_empty_returns_null() {
		$result = \EDD\Logs\Registry::get_unregistered_type_config( '' );

		$this->assertNull( $result );
	}
}
