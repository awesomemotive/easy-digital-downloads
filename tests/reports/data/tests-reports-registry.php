<?php
namespace EDD\Reports;

if ( ! class_exists( '\EDD\Reports\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

/**
 * Tests for the Reports registry API.
 *
 * @group edd_registry
 * @group edd_reports
 *
 * @coversDefaultClass \EDD\Reports\Data\Reports_Registry
 */
class Reports_Registry_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Reports\Init
	 * @static
	 */
	protected static $reports;

	/**
	 * Reports registry fixture.
	 *
	 * @access protected
	 * @var    \EDD\Reports\Data\Reports_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports\Init();
	}

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->registry = new \EDD\Reports\Data\Reports_Registry();
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
	 * @covers ::instance()
	 */
	public function test_static_registry_should_have_instance_method() {
		$this->assertTrue( method_exists( $this->registry, 'instance' ) );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_get_report_with_invalid_report_id_should_return_an_empty_array() {
		$this->setExpectedException( '\EDD_Exception', "The 'foo' report does not exist." );

		$result = $this->registry->get_report( 'foo' );

		$this->assertEqualSets( array(), $result );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_get_report_with_invalid_report_id_should_throw_an_exception() {
		$this->setExpectedException( '\EDD_Exception', "The 'foo' report does not exist." );

		$this->registry->get_report( 'foo' );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_get_report_with_valid_report_id_should_return_that_report() {
		// Add a test report.
		$this->add_test_report();

		$expected = $this->get_expected_report();

		$this->assertEqualSetsWithIndex( $expected, $this->registry->get_report( 'foo' ) );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_remove_report_with_invalid_report_should_affect_no_change() {
		// Add a report.
		$this->add_test_report();

		// Remove an invalid report.
		$this->registry->remove_report( 'bar' );

		$expected = $this->get_expected_report( 'all_reports' );

		$this->assertEqualSetsWithIndex( $expected, $this->registry->get_reports() );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_remove_report_with_valid_report_should_remove_that_report() {
		// Add a report.
		$this->add_test_report();

		// Remove it.
		$this->registry->remove_report( 'foo' );

		$this->assertEqualSets( array(), $this->registry->get_reports() );
	}

	/**
	 * @covers ::add_report()
	 * @expectedException \EDD_Exception
	 */
	public function test_add_report_with_empty_attributes_should_return_false() {
		$this->assertFalse( $this->registry->add_report( 'foo', array() ) );
	}

	/**
	 * @covers ::add_report()
	 * @throws \EDD_Exception
	 */
	public function test_add_report_with_empty_label_should_throw_exception() {
		$this->setExpectedException( '\EDD_Exception', "The 'label' parameter for the 'foo' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'." );

		$this->registry->add_report( 'foo', array(
			'label' => ''
		) );
	}

	/**
	 * @covers ::add_report()
	 * @throws \EDD_Exception
	 */
	public function test_add_report_with_empty_endpoints_should_throw_exception() {
		$this->setExpectedException( '\EDD_Exception', "The 'endpoints' parameter for the 'foo' item is missing or invalid in 'EDD\Reports\Registry::validate_attributes'." );

		$added = $this->registry->add_report( 'foo', array(
			'label' => 'Foo',
		) );
	}

	/**
	 * @covers ::add_report()
	 * @throws \EDD_Exception
	 */
	public function test_add_report_with_no_priority_should_set_priority_10() {
		$this->registry->add_report( 'foo', array(
			'label'     => 'Foo',
			'endpoints' => array(
				'tiles' => array()
			),
		) );

		$report = $this->registry->get_report( 'foo' );

		$this->assertSame( 10, $report['priority'] );
	}

	/**
	 * @covers ::add_report()
	 * @throws \EDD_Exception
	 */
	public function test_add_report_with_priority_should_set_that_priority() {
		$this->registry->add_report( 'foo', array(
			'label'     => 'Foo',
			'priority'  => 15,
			'endpoints' => array(
				'tiles' => array()
			),
		) );

		$report = $this->registry->get_report( 'foo' );

		$this->assertSame( 15, $report['priority'] );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_reports_with_no_sort_should_return_reports_in_order_of_registration() {

		$this->add_test_reports_for_sort();

		$result = array_keys( $this->registry->get_reports() );

		$this->assertEqualSets( array( 'foo', 'bar' ), $result );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_reports_with_invalid_sort_should_return_reports_in_order_of_registration() {

		$this->add_test_reports_for_sort();

		$result = array_keys( $this->registry->get_reports( 'fake_sort' ) );

		$this->assertEqualSets( array( 'foo', 'bar' ), $result );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_reports_with_ID_sort_should_return_reports_in_alphabetical_order_by_ID() {

		$this->add_test_reports_for_sort();

		$result = array_keys( $this->registry->get_reports( 'ID' ) );

		$this->assertEqualSets( array( 'bar', 'foo' ), $result );
	}

	/**
	 * @covers ::__call()
	 * @throws \EDD_Exception
	 */
	public function test_get_reports_with_priority_sort_should_return_reports_in_order_of_priority() {

		$this->add_test_reports_for_sort();

		$result = array_keys( $this->registry->get_reports( 'priority' ) );

		$this->assertEqualSets( array( 'bar', 'foo' ), $result );
	}

	/**
	 * Helper to add a foo report to the registry.
	 */
	protected function add_test_report() {
		try {
			$this->registry->add_report( 'foo', array(
				'label' => 'Foo',
				'endpoints' => array(
					'tiles' => array()
				)
			) );
		} catch ( \EDD_Exception $exception ) {}
	}

	/**
	 * Helper to retrieve the expected value for retrieving a single report entry or all entries.
	 *
	 * @param string $as Optional. The method by which to determine which `$expected` array to return.
	 *                   Accepts 'entry' or 'all_reports'. Default 'entry'.
	 * @return array Array for comparison.
	 */
	protected function get_expected_report( $as = 'entry' ) {
		if ( ! in_array( $as, array( 'entry', 'all_reports' ) ) ) {
			$as = 'entry';
		}

		switch( $as ) {
			case 'entry':
				$expected = array(
					'id'         => 'foo',
					'label'      => 'Foo',
					'priority'   => 10,
					'capability' => 'view_shop_reports',
					'endpoints'  => array(
						'tiles' => array()
					),
				);
				break;

			case 'all_reports':
				$expected = array(
					'foo' => array(
						'id'         => 'foo',
						'label'      => 'Foo',
						'priority'   => 10,
						'capability' => 'view_shop_reports',
						'endpoints'  => array(
							'tiles' => array()
						),
					)
				);
				break;

			default:
				$expected = array();
				break;
		}

		return $expected;
	}

	/**
	 * Adds two test reports for use when comparing sorting results.
	 *
	 * @throws \EDD_Exception
	 */
	protected function add_test_reports_for_sort() {
		$this->registry->add_report( 'foo', array(
			'label'     => 'Foo',
			'priority'  => 10,
			'endpoints' => array(
				'tiles' => array()
			),
		) );

		$this->registry->add_report( 'bar', array(
			'label'     => 'Bar',
			'priority'  => 5,
			'endpoints' => array(
				'tiles' => array()
			),
		) );
	}
}
