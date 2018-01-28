<?php
namespace EDD\Admin\Reports\Data;

if ( ! class_exists( '\EDD\Admin\Reports' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/class-edd-reports.php' );
}

/**
 * Tests for the Report object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 */
class Report_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Admin\Reports
	 * @static
	 */
	protected static $reports;

	/**
	 * Reports registry fixture.
	 *
	 * @access protected
	 * @var    Reports_Registry
	 */
	protected $reports_registry;

	/**
	 * Reports registry fixture.
	 *
	 * @access protected
	 * @var    Data\Report_Registry
	 */
	protected $endpoints_registry;

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

		$this->reports_registry   = new \EDD\Admin\Reports\Data\Reports_Registry();
		$this->endpoints_registry = new \EDD\Admin\Reports\Data\Endpoint_Registry();
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->reports_registry->exchangeArray( array() );
		$this->endpoints_registry->exchangeArray( array() );

		parent::tearDown();
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report
	 * @group edd_errors
	 */
	public function test_Report_with_empty_endpoints_should_flag_WP_Error() {
		$report = new Report( array(
			'id'        => 'foo',
			'label'     => 'Foo',
			'endpoints' => array(),
		) );

		$this->assertTrue( $report->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::$endpoints
	 * @group edd_errors
	 */
	public function test_Report_with_empty_endpoints_should_flag_WP_Error_including_code_missing_endpoints() {
		$report = new Report( array(
			'id'        => 'foo',
			'label'     => 'Foo',
			'endpoints' => array(),
		) );

		$this->assertContains( 'missing_endpoints', $report->get_errors()->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::$capability
	 * @group edd_errors
	 */
	public function test_Report_with_empty_capability_should_flag_WP_Error_including_code_missing_capability() {
		$report = new Report( array(
			'id'         => 'foo',
			'label'      => 'Foo',
			'endpoints'  => array(),
			'capability' => '',
		) );

		$this->assertContains( 'missing_capability', $report->get_errors()->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::build_endpoints()
	 * @throws \EDD_Exception
	 */
	public function test_build_endpoints_with_empty_array_should_add_no_new_endpoints() {
		$report = new Report( array() );

		$report->build_endpoints( array() );

		$this->assertEqualSets( array(), $report->get_endpoints() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::build_endpoints()
	 * @throws \EDD_Exception
	 */
	public function test_build_endpoints_with_invalid_view_group_should_throw_exception() {
		$this->setExpectedException( '\EDD_Exception', "The 'fake' view group does not correspond to a known endpoint view type." );

		$report = new Report( array(
			'id'    => 'foo',
			'label' => 'Foo',
		) );

		$report->build_endpoints( array( 'fake' => array() ) );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::parse_view_groups()
	 */
	public function test_parse_view_groups_should_return_group_view_key_value_pairs() {
		$report = new Report( array(
			'id'    => 'foo',
			'label' => 'Foo',
		) );

		$expected = array(
			'tiles'  => 'tile',
			'charts' => 'chart',
			'tables' => 'table',
			'graphs' => 'graph',
		);

		$this->assertEqualSetsWithIndex( $expected, $report->parse_view_groups() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::validate_endpoint()
	 */
	public function test_validate_endpoint_passed_a_WP_Error_object_should_add_a_new_error_to_errors() {
		$report = new Report( array(
			'id'    => 'foo',
			'label' => 'Foo',
		) );

		$report->validate_endpoint( 'tiles', new \WP_Error( 'foo' ) );

		$errors = $report->get_errors();

		$this->assertContains( 'foo', $report->get_errors()->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::validate_endpoint()
	 */
	public function test_validate_endpoint_passed_an_endpoint_with_errors_should_add_that_error() {
		$report = new Report( array(
			'id'    => 'foo',
			'label' => 'Foo',
		) );

		$endpoint = $this->mock_Endpoint( array(
			'view' => 'tile',
			'atts' => array()
		) );

		$report->validate_endpoint( 'tiles', $endpoint );

		$this->assertContains( 'invalid_endpoint', $report->get_errors()->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::validate_endpoint()
	 */
	public function test_validate_endpoint_passed_a_legitimate_endpoint_should_add_it_to_the_endpoints_array() {
		$endpoint = new Tile_Endpoint( array(
			'id'    => 'foo',
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		// Add a completely valid endpoint.
		$report = new Report( array(
			'id'        => 'foo',
			'label'     => 'Foo',
			'endpoints' => array(),
		) );

		$report->validate_endpoint( 'tiles', $endpoint );

		$this->assertArrayHasKey( 'foo', $report->get_endpoints( 'tiles' ) );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::get_endpoints()
	 */
	public function test_get_endpoints_with_empty_view_group_should_return_all_endpoints() {
		$report = new Report( array(
			'id' => 'foo',
			'label' => 'Foo',
			'endpoints' => array(
				'tiles' => array(
					new Tile_Endpoint( array(
						'id'    => 'foo',
						'label' => 'Foo',
						'views' => array(
							'tile' => array(
								'display_callback' => '__return_false',
								'data_callback'    => '__return_false',
							),
						),
					) ),
					new Tile_Endpoint( array(
						'id'    => 'bar',
						'label' => 'Bar',
						'views' => array(
							'tile' => array(
								'display_callback' => '__return_false',
								'data_callback'    => '__return_false',
							),
						),
					) ),
				),
			)
		) );

		$all_endpoints = $report->get_endpoints();

		$actual = array();

		foreach ( $all_endpoints as $view_group => $endpoints ) {
			foreach ( $endpoints as $endpoint_id => $endpoint ) {
				$actual[] = $endpoint_id;
			}
		}

		$this->assertEqualSets( array( 'foo', 'bar' ), $actual );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::get_endpoints()
	 */
	public function test_get_endpoints_with_invalid_view_group_should_return_all_endpoints() {
		$report = new Report( array(
			'id' => 'foo',
			'label' => 'Foo',
			'endpoints' => array(
				'tiles' => array(
					new Tile_Endpoint( array(
						'id'    => 'foo',
						'label' => 'Foo',
						'views' => array(
							'tile' => array(
								'display_callback' => '__return_false',
								'data_callback'    => '__return_false',
							),
						),
					) ),
					new Tile_Endpoint( array(
						'id'    => 'bar',
						'label' => 'Bar',
						'views' => array(
							'tile' => array(
								'display_callback' => '__return_false',
								'data_callback'    => '__return_false',
							),
						),
					) ),
				),
			)
		) );

		$all_endpoints = $report->get_endpoints( 'fake' );

		$actual = array();

		foreach ( $all_endpoints as $view_group => $endpoints ) {
			foreach ( $endpoints as $endpoint_id => $endpoint ) {
				$actual[] = $endpoint_id;
			}
		}

		$this->assertEqualSets( array( 'foo', 'bar' ), $actual );
	}

	/**
	 * @covers \EDD\Admin\Reports\Data\Report::get_endpoints()
	 */
	public function test_get_endpoints_with_valid_view_group_should_return_all_endpoints() {
		$report = new Report( array(
			'id' => 'foo',
			'label' => 'Foo',
			'endpoints' => array(
				'tiles' => array(
					new Tile_Endpoint( array(
						'id'    => 'foo',
						'label' => 'Foo',
						'views' => array(
							'tile' => array(
								'display_callback' => '__return_false',
								'data_callback'    => '__return_false',
							),
						),
					) ),
				),
				'tables' => array(
					$this->mock_Endpoint( array(
						'id'    => 'bar',
						'label' => 'Bar',
						'views' => array(
							'table' => array(
								'display_callback' => '__return_false',
								'data_callback'    => '__return_false',
							),
						),
					) ),
				),
			)
		) );

		$tables = $report->get_endpoints( 'tiles' );

		$actual = array();

		foreach ( $tables as $endpoint_id => $endpoint ) {
			$actual[] = $endpoint_id;
		}

		$this->assertEqualSets( array( 'foo' ), $actual );
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
