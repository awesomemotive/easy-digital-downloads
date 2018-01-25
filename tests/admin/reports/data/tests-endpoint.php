<?php
namespace EDD\Admin\Reports\Data;

if ( ! class_exists( '\EDD\Admin\Reports' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/class-edd-reports.php' );
}

/**
 * Tests for the Endpoint object.
 *
 * @group edd_registry
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 */
class Endpoint_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Admin\Reports
	 * @static
	 */
	protected static $reports;

	/**
	 * Endpoint registry fixture.
	 *
	 * @access protected
	 * @var    \EDD\Admin\Reports\Data\Endpoint_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Admin\Reports();
	}

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->registry = new \EDD\Admin\Reports\Data\Endpoint_Registry();
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->registry->exchangeArray( array() );

		parent::tearDown();
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_view()
	 */
	public function test_get_view_when_created_with_invalid_view_should_be_null() {
		$endpoint = $this->mock_Endpoint( array() );

		$this->assertNull( $endpoint->get_view() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::check_view()
	 * @group edd_errors
	 */
	public function test_check_view_with_invalid_view_should_flag_WP_Error() {
		// Execute the invisible method via the constructor.
		$endpoint = $this->mock_Endpoint( array() );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::check_view()
	 * @group edd_errors
	 */
	public function test_check_view_with_invalid_view_should_flag_WP_Error_including_code_invalid_view() {
		// Execute the invisible method via the constructor.
		$endpoint = $this->mock_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'invalid_view', $errors->get_error_code() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_args()
	 */
	public function test_get_display_args_when_created_without_display_args_should_return_an_empty_array() {
		$endpoint = $this->mock_Endpoint( array() );

		$this->assertEqualSets( array(), $endpoint->get_display_args() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_args()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_args()
	 */
	public function test_get_display_args_when_created_with_display_args_should_return_those_args() {
		$expected = array( 'something', 'goes', 'here' );

		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_callback' => function() {
						echo 'Hello, world!';
					},
					'display_args' => $expected
				),
			),
		) );

		$this->assertEqualSets( $expected, $endpoint->get_display_args() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_callback()
	 */
	public function test_get_display_callback_when_created_without_display_callback_should_return_null() {
		$endpoint = $this->mock_Endpoint( array() );

		$this->assertNull( $endpoint->get_display_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_callback()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_callback()
	 */
	public function test_get_display_callback_when_created_with_display_callback_should_return_that_callback() {
		$endpoint = new Tile_Endpoint( array(
			'views' => array(
				'tile' => array(
					'display_callback' => '__return_false'
				),
			),
		) );

		$this->assertSame( '__return_false', $endpoint->get_display_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_data_callback()
	 */
	public function test_get_data_callback_when_created_without_data_callback_should_return_null() {
		$endpoint = $this->mock_Endpoint( array() );

		$this->assertNull( $endpoint->get_data_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_data_callback()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_data_callback()
	 */
	public function test_get_data_callback_when_created_with_data_callback_should_return_that_callback() {
		$endpoint = new Tile_Endpoint( array(
			'views' => array(
				'tile' => array(
					'data_callback' => '__return_false'
				),
			),
		) );

		$this->assertSame( '__return_false', $endpoint->get_data_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_args()
	 * @group edd_errors
	 */
	public function test_set_display_args_with_non_array_display_args_should_flag_WP_Error_including_code_invalid_view_arg_type() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => 'something',
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'invalid_view_arg_type', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_props()
	 * @group edd_errors
	 */
	public function test_set_display_props_with_empty_view_display_callback_should_flag_WP_Error() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'  => array( 'something' ),
					'data_callback' => '__return_false',
				),
			),
		) );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_props()
	 * @group edd_errors
	 */
	public function test_set_display_props_with_empty_view_display_callback_should_flag_WP_Error_including_code_missing_display_callback() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'  => array( 'something' ),
					'data_callback' => '__return_false',
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'missing_display_callback', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_callback()
	 * @group edd_errors
	 */
	public function test_set_display_callback_with_non_callable_display_callback_should_flag_WP_Error_including_code_invalid_view_arg_type() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'something' ),
					'display_callback' => 'something',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'invalid_view_arg_type', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_props()
	 * @group edd_errors
	 */
	public function test_set_display_props_with_empty_view_data_callback_should_flag_WP_Error() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'something' ),
					'display_callback' => '__return_false',
				),
			),
		) );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_props()
	 * @group edd_errors
	 */
	public function test_set_display_props_with_empty_view_data_callback_should_flag_WP_Error_including_code_missing_data_callback() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'something' ),
					'display_callback' => '__return_false',
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'missing_data_callback', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_data_callback()
	 * @group edd_errors
	 */
	public function test_set_data_callback_with_non_callable_data_callback_should_flag_WP_Error_including_code_invalid_view_arg_type() {
		// Execute the invisible method via the constructor.
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'something' ),
					'display_callback' => '__return_false',
					'data_callback'    => 'something',
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'invalid_view_arg_type', $errors->get_error_codes() );
	}

	/**
	 * Mocks a copy of the Endpoint abstract class.
	 *
	 * @param array $args
	 * @return \EDD\Admin\Reports\Data\Endpoint Mocked Endpoint instance.
	 */
	protected function mock_Endpoint( $args ) {
		return $this->getMockForAbstractClass( '\EDD\Admin\Reports\Data\Endpoint', array( $args ) );
	}

}
