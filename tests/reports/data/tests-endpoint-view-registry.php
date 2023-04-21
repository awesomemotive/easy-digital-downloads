<?php
namespace EDD\Tests\Reports\Data;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Report registry API.
 *
 * @group edd_registry
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_reports_endpoints_views
 *
 * @coversDefaultClass \EDD\Reports\Data\Endpoint_View_Registry
 */
class Endpoint_View_Registry_Tests extends EDD_UnitTestCase {

	/**
	 * Report registry fixture.
	 *
	 * @access protected
	 * @var    \EDD\Reports\Data\Endpoint_View_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public function setup(): void {
		parent::setUp();

		$this->registry = new \EDD\Reports\Data\Endpoint_View_Registry();
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown(): void {
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
	public function test_get_endpoint_view_with_invalid_endpoint_view_id_should_return_an_empty_array() {
		$this->expectException( 'EDD\Utils\Exception' );
		$this->expectExceptionMessage( "The 'foo' endpoint view does not exist." );

		$result = $this->registry->get_endpoint_view( 'foo' );

		$this->assertEqualSets( array(), $result );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_get_endpoint_view_with_invalid_report_id_should_throw_an_exception() {
		$this->expectException( 'EDD\Utils\Exception' );
		$this->expectExceptionMessage( "The 'foo' endpoint view does not exist." );

		$this->registry->get_endpoint_view( 'foo' );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_get_endpoint_view_with_valid_view_id_should_return_that_report() {
		// Add core views.
		$this->add_core_views();

		$expected = $this->get_expected_view( 'tile' );

		$this->assertEqualSetsWithIndex( $expected, $this->registry->get_endpoint_view( 'tile' ) );
	}

	/**
	 * @covers ::__call()
	 */
	public function test_remove_item_should_affect_no_change() {
		// Add core views.
		$this->add_core_views();

		// Remove an invalid report.
		$this->registry->remove_item( 'tile' );

		$this->assertEqualSetsWithIndex( $this->registry->get_core_views(), $this->registry->get_endpoint_views() );
	}

	/**
	 * @covers ::register_endpoint_view()
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_view_with_non_core_view_id_should_throw_exception() {
		$this->expectException( 'EDD\Utils\Exception' );
		$this->expectExceptionMessage( "The 'foo' endpoint view is invalid." );

		$this->registry->register_endpoint_view( 'foo', array() );
	}

	/**
	 * @covers ::register_endpoint_view()
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_view_with_group_callback_should_set_that_group_callback() {
		$expected = '__return_false';

		$this->registry->register_endpoint_view( 'tile', array(
			'group_callback' => $expected,
		) );

		$view = $this->registry->get_endpoint_view( 'tile' );

		$this->assertSame( $expected, $view['group_callback'] );
	}

	/**
	 * @covers ::register_endpoint_view()
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_view_with_handler_should_set_that_handler() {
		$expected = '__return_false';

		$this->registry->register_endpoint_view( 'tile', array(
			'handler' => $expected,
		) );

		$view = $this->registry->get_endpoint_view( 'tile' );

		$this->assertSame( $expected, $view['handler'] );
	}

	/**
	 * @covers ::register_endpoint_view()
	 * @throws \EDD_Exception
	 */
	public function test_register_endpoint_view_with_fields_display_callback_should_set_that_display_callback() {
		$expected = '__return_false';

		$this->registry->register_endpoint_view( 'tile', array(
			'fields' => array(
				'display_callback' => $expected,
			),
		) );

		$view = $this->registry->get_endpoint_view( 'tile' );

		$this->assertSame( $expected, $view['fields']['display_callback'] );
	}

	/**
	 * Helper to add a foo report to the registry.
	 */
	protected function add_core_views() {
		$core_views = $this->registry->get_core_views();

		try {
			foreach ( $core_views as $view_id => $atts ) {
				$this->registry->register_endpoint_view( $view_id, $atts );
			}
		} catch ( \EDD_Exception $exception ) {}
	}

	/**
	 * Helper to retrieve the expected value for retrieving a single report entry or all entries.
	 *
	 * @param string $as Optional. The method by which to determine which `$expected` array to return.
	 *                   Accepts 'entry' or 'all_reports'. Default 'entry'.
	 * @return array Array for comparison.
	 */
	protected function get_expected_view( $as = 'tile' ) {
		return $this->registry->get_core_view( $as );
	}

}
