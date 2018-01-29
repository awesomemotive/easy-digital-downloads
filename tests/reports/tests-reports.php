<?php
namespace EDD\Reports;

if ( ! class_exists( '\EDD\Reports\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

/**
 * Tests for the Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 */
class Reports_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Reports\Init
	 * @static
	 */
	protected static $reports;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports\Init();
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @group edd_errors
	 */
	public function test_get_endpoint_with_invalid_endpoint_id_should_return_WP_Error() {
		$result = get_endpoint( 'fake', 'tile' );

		$this->assertWPError( $result );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @group edd_errors
	 */
	public function test_get_endpoint_with_invalid_endpoint_id_should_return_WP_Error_code_invalid_endpoint() {
		$result = get_endpoint( 'fake', 'tile' );

		$this->assertSame( 'invalid_endpoint', $result->get_error_code() );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoint_with_valid_endpoint_id_valid_type_should_return_an_Endpoint_object() {
		register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'some_value' ),
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		$result = get_endpoint( 'foo', 'tile' );

		$this->assertInstanceOf( 'EDD\Reports\Data\Endpoint', $result );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @group edd_errors
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoint_with_valid_endpoint_id_invalid_type_should_return_WP_Error_including_invalid_view_error_code() {
		register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'some_value' ),
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$result = get_endpoint( 'foo', 'fake' );

		$this->assertSame( 'invalid_view', $result->get_error_code() );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_with_invalid_view_should_leave_it_intact() {
		$expected = array(
			'fake' => array(
				'display_callback' => '__return_false'
			),
		);

		$this->assertEqualSetsWithIndex( $expected, parse_endpoint_views( $expected ) );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_with_valid_view_should_inject_defaults() {
		$expected = array(
			'tile' => array(
				'data_callback'    => '__return_zero',
				'display_callback' => __NAMESPACE__ . '\\default_display_tile',
				'display_args'     => array(
					'type'             => '' ,
					'context'          => 'primary',
					'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
				),
			),
		);

		$views = array(
			'tile' => array(
				'data_callback' => '__return_zero',
			),
		);

		$this->assertEqualSetsWithIndex( $expected, parse_endpoint_views( $views ) );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_should_strip_invalid_fields() {
		$views = array(
			'tile' => array(
				'fake_field' => 'foo',
			),
		);

		$result = parse_endpoint_views( $views );

		$this->assertArrayNotHasKey( 'fake_field', $result['tile'] );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_should_inject_default_display_args() {
		$expected = array(
			'type'             => 'number',
			'context'          => 'primary',
			'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
		);

		$views = array(
			'tile' => array(
				'display_args' => array(
					'type' => 'number',
				)
			)
		);

		$result = parse_endpoint_views( $views );

		$this->assertEqualSetsWithIndex( $expected, $result['tile']['display_args'] );
	}

	/**
	 * @covers ::\EDD\Reports\is_view_valid()
	 */
	public function test_is_view_valid_with_valid_view_should_return_true() {
		$this->assertTrue( is_view_valid( 'tile' ) );
	}

	/**
	 * @covers ::\EDD\Reports\is_view_valid()
	 */
	public function test_is_view_valid_with_invalid_view_should_return_false() {
		$this->assertFalse( is_view_valid( 'fake' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_handler()
	 */
	public function test_get_endpoint_handler_with_valid_view_should_return_the_handler() {
		$expected = 'EDD\Reports\Data\Tile_Endpoint';

		$this->assertSame( $expected, get_endpoint_handler( 'tile' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_handler()
	 */
	public function test_get_endpoint_handler_with_invalid_view_should_return_empty() {
		$this->assertSame( '', get_endpoint_handler( 'fake' ) );
	}

}
