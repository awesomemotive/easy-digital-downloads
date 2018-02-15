<?php

require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Defines a basic fixture to run multiple tests.
 *
 * Resets the state of the WordPress installation before and after every test.
 *
 * Includes utility functions and assertions useful for testing WordPress.
 *
 * All WordPress unit tests should inherit from this class.
 */
class EDD_UnitTestCase extends WP_UnitTestCase {

	public static function wpSetUpBeforeClass() {
		edd_install();
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();

		$this->expectDeprecatedEDD();
	}

	/**
	 * Sets up logic for the @expectEDDeprecated annotation for deprecated elements in EDD.
	 */
	function expectDeprecatedEDD() {
		$annotations = $this->getAnnotations();
		foreach ( array( 'class', 'method' ) as $depth ) {
			if ( ! empty( $annotations[ $depth ]['expectEDDeprecated'] ) ) {
				$this->expected_deprecated = array_merge( $this->expected_deprecated, $annotations[ $depth ]['expectEDDeprecated'] );
			}
		}
		add_action( 'edd_deprecated_function_run', array( $this, 'deprecated_function_run' ) );
		add_action( 'edd_deprecated_argument_run', array( $this, 'deprecated_function_run' ) );
		add_action( 'edd_deprecated_hook_run', array( $this, 'deprecated_function_run' ) );
		add_action( 'edd_deprecated_function_trigger_error', '__return_false' );
		add_action( 'edd_deprecated_argument_trigger_error', '__return_false' );
		add_action( 'edd_deprecated_hook_trigger_error', '__return_false' );
	}

	protected static function edd() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new EDD\Tests\Factory();
		}
		return $factory;
	}

	public static function tearDownAfterClass() {
		self::_delete_all_data();

		return parent::tearDownAfterClass();
	}

	protected static function _delete_all_data() {
		global $wpdb;

		foreach ( array(
			EDD()->customers->table_name,
			EDD()->customer_meta->table_name,
			EDD()->discounts->table_name,
			EDD()->discount_meta->table_name,
		) as $table ) {
			$wpdb->query( "DELETE FROM {$table}" );
		}
	}
}