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

	/**
	 * Holds the original GMT offset for restoration during class tear down.
	 *
	 * @var string
	 */
	public static $original_gmt_offset;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		edd_install();

		global $current_user;

		$current_user = new WP_User(1);
		$current_user->set_role('administrator');
		wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
		add_filter( 'edd_log_email_errors', '__return_false' );
	}

	public static function tearDownAfterClass() {
		self::_delete_all_edd_data();

		delete_option( 'gmt_offset' );

		EDD()->utils->get_gmt_offset( true );

		parent::tearDownAfterClass();
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

	protected static function _delete_all_edd_data() {
		$components = EDD()->components;

		foreach ( $components as $component ) {
			$thing = $component->get_interface( 'table' );

			if ( $thing instanceof \EDD\Database\Table ) {
				$thing->truncate();
			}

			$thing = $component->get_interface( 'meta' );

			if ( $thing instanceof \EDD\Database\Table ) {
				$thing->truncate();
			}
		}
	}

	/**
	 * Checks if all items in the array are of the given type.
	 *
	 * @param string $type   Type to check against.
	 * @param array  $actual Supplied array to check.
	 */
	public function assertContainsOnlyType( $type, $actual ) {
		$standard_types = array(
			'numeric', 'integer', 'int', 'float', 'string', 'boolean', 'bool',
			'null', 'array', 'object', 'resource', 'scalar'
		);


		if ( in_array( $type, $standard_types, true ) ) {
			if ( class_exists( 'PHPUnit\Framework\Constraint\isType' ) ) {
				$constraint = new \PHPUnit\Framework\Constraint\isType( $type );
			} else {
				$constraint = new \PHPUnit_Framework_Constraint_IsType( $type );
			}
		} else {
			if ( class_exists( 'PHPUnit\Framework\Constraint\IsInstanceOf' ) ) {
				$constraint = new \PHPUnit\Framework\Constraint\IsInstanceOf( $type );
			} else {
				$constraint = new \PHPUnit_Framework_Constraint_IsInstanceOf( $type );
			}
		}

		foreach ( $actual as $item ) {
			if ( class_exists( '\PHPUnit\Framework\Assert' ) ) {
				\PHPUnit\Framework\Assert::assertThat( $item, $constraint );
			} else {
				\PHPUnit_Framework_Assert::assertThat( $item, $constraint );
			}
		}
	}

}
