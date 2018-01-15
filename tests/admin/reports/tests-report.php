<?php
namespace EDD\Admin\Reports;

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

		$this->reports_registry   = new \EDD\Admin\Reports\Reports_Registry();
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
	 * @covers \EDD\Admin\Reports\Report::get_id()
	 */
	public function test_get_id_when_created_without_an_id_should_return_null() {
		$report = new Report( array() );

		$this->assertNull( $report->get_id() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::get_id()
	 * @covers \EDD\Admin\Reports\Report::set_id()
	 */
	public function test_get_id_when_created_with_an_id_should_return_that_id() {
		$report = new Report( array( 'id' => 'foo' ) );

		$this->assertSame( 'foo', $report->get_id() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::get_label()
	 */
	public function test_get_label_when_created_without_a_label_should_return_null() {
		$report = new Report( array() );

		$this->assertNull( $report->get_label() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::get_label()
	 * @covers \EDD\Admin\Reports\Report::set_label()
	 */
	public function test_get_label_when_created_with_a_label_should_return_that_label() {
		$report = new Report( array( 'label' => 'Foo' ) );

		$this->assertSame( 'Foo', $report->get_label() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_id_should_flag_WP_Error() {
		$report = new Report( array(
			'label'     => 'Foo',
			'endpoints' => array(),
		) );

		$this->assertTrue( $report->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_id_should_flag_WP_Error_including_code_missing_report_id() {
		$report = new Report( array(
			'label'     => 'Foo',
			'endpoints' => array(),
		) );

		$errors = $report->get_errors();

		$this->assertContains( 'missing_report_id', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_report_label_should_flag_WP_Error() {
		$report = new Report( array(
			'id'        => 'foo',
			'endpoints' => array(),
		) );

		$this->assertTrue( $report->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::set_props()
	 * @group edd_errors
	 */
	public function test_set_props_with_missing_report_label_should_flag_WP_Error_including_code_missing_endpoint_label() {
		$report = new Report( array(
			'id'        => 'foo',
			'endpoints' => array(),
		) );

		$errors = $report->get_errors();

		$this->assertContains( 'missing_report_label', $errors->get_error_codes() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::has_errors()
	 */
	public function test_Report_has_errors_if_no_errors_should_return_false() {
		// Add a completely valid endpoint.
		$report = new Report( array(
			'id'        => 'foo',
			'label'     => 'Foo',
			'endpoints' => array(
				new Data\Endpoint( 'tile', array(
					'id'    => 'foo',
					'label' => 'Foo',
					'views' => array(
						'tile' => array(
							'display_callback' => '__return_false',
							'data_callback'    => '__return_false',
						),
					),
				) )
			),
		) );

		$this->assertFalse( $report->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::has_errors()
	 */
	public function test_Report_has_errors_if_errors_should_return_true() {
		$report = new Report( array() );

		$this->assertTrue( $report->has_errors() );
	}

	/**
	 * @covers \EDD\Admin\Reports\Report::get_errors()
	 */
	public function test_Report_get_errors_should_return_WP_Error_object() {
		$report = new Report( array() );

		$this->assertWPError( $report->get_errors() );
	}

}
