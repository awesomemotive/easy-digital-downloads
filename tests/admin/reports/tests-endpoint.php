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
		$endpoint = new Endpoint( 'fake', array() );

		$this->assertNull( $endpoint->get_view() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_view()
	 */
	public function test_get_view_when_created_with_valid_view_should_be_that_view() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertSame( 'tile', $endpoint->get_view() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_view()
	 * @group edd_errors
	 */
	public function test_set_view_with_invalid_view_should_flag_WP_Error() {
		// Execute the invisible method via the constructor.
		$endpoint = new Endpoint( array(
			'view' => 'fake',
			'atts' => array()
		) );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_view()
	 * @group edd_errors
	 */
	public function test_set_view_with_invalid_view_should_flag_WP_Error_including_code_invalid_view() {
		// Execute the invisible method via the constructor.
		$endpoint = new Endpoint( array(
			'view' => 'fake',
			'atts' => array()
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'invalid_view', $errors->get_error_code() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_view()
	 */
	public function test_set_view_with_valid_view_should_set_that_view() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertSame( 'tile', $endpoint->get_view() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_id()
	 */
	public function test_get_id_when_created_without_an_id_should_return_null() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertNull( $endpoint->get_id() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_id()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_id()
	 */
	public function test_get_id_when_created_with_an_id_should_return_that_id() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array( 'id' => 'foo' )
		) );

		$this->assertSame( 'foo', $endpoint->get_id() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_label()
	 */
	public function test_get_label_when_created_without_a_label_should_return_null() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertNull( $endpoint->get_label() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_label()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_label()
	 */
	public function test_get_label_when_created_with_a_label_should_return_that_label() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'label' => 'Foo',
			)
		) );

		$this->assertSame( 'Foo', $endpoint->get_label() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_id_should_flag_WP_Error() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
				),
			),
		) );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_id_should_flag_WP_Error_including_code_missing_object_id() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'missing_object_id', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_object_label_should_flag_WP_Error() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
				),
			),
		) );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_object_label_should_flag_WP_Error_including_code_missing_object_label() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'missing_object_label', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_props()
	 * @group edd_errors
	 */
	public function test_set_display_props_with_empty_view_display_args_should_be_treated_as_optional() {
		// Execute the invisible method via the constructor.
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array(),
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
				),
			),
		) );

		$this->assertFalse( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_args()
	 */
	public function test_get_display_args_when_created_without_display_args_should_return_an_empty_array() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertEqualSets( array(), $endpoint->get_display_args() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_args()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_args()
	 */
	public function test_get_display_args_when_created_with_display_args_should_return_those_args() {
		$expected = array( 'something', 'goes', 'here' );

		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'views' => array(
					'tile' => array(
						'display_args' => $expected
					),
				),
			),
		) );

		$this->assertEqualSets( $expected, $endpoint->get_display_args() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_callback()
	 */
	public function test_get_display_callback_when_created_without_display_callback_should_return_null() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertNull( $endpoint->get_display_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_display_callback()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_display_callback()
	 */
	public function test_get_display_callback_when_created_with_display_callback_should_return_that_callback() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'views' => array(
					'tile' => array(
						'display_callback' => '__return_false'
					),
				),
			),
		) );

		$this->assertSame( '__return_false', $endpoint->get_display_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_data_callback()
	 */
	public function test_get_data_callback_when_created_without_data_callback_should_return_null() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertNull( $endpoint->get_data_callback() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_data_callback()
	 * @covers \EDD\Admin\Reports\Data\Endpoint::set_data_callback()
	 */
	public function test_get_data_callback_when_created_with_data_callback_should_return_that_callback() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'views' => array(
					'tile' => array(
						'data_callback' => '__return_false'
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => 'something',
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'  => array( 'something' ),
						'data_callback' => '__return_false',
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'  => array( 'something' ),
						'data_callback' => '__return_false',
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => 'something',
						'data_callback'    => '__return_false',
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
					),
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
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_args'     => array( 'something' ),
						'display_callback' => '__return_false',
						'data_callback'    => 'something',
					),
				),
			),
		) );

		$errors = $endpoint->get_errors();

		$this->assertContains( 'invalid_view_arg_type', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::has_errors()
	 */
	public function test_has_errors_if_no_errors_should_return_false() {
		// Add a completely valid endpoint.
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array(
				'id'    => 'foo',
				'label' => 'Foo',
				'views' => array(
					'tile' => array(
						'display_callback' => '__return_false',
						'data_callback'    => '__return_false',
					),
				),
			),
		) );

		$this->assertFalse( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::has_errors()
	 */
	public function test_has_errors_if_errors_should_return_true() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertTrue( $endpoint->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Endpoint::get_errors()
	 */
	public function test_get_errors_should_return_WP_Error_object() {
		$endpoint = new Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$this->assertWPError( $endpoint->get_errors() );
	}

}
