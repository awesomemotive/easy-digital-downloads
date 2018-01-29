<?php
namespace EDD\Reports\Data;

if ( ! class_exists( '\EDD\Reports' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-reports-init.php' );
}

/**
 * Tests for the Tile_Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 */
class Tile_Endpoint_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Reports
	 * @static
	 */
	protected static $reports;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports();
	}

	/**
	 * @covers \EDD\Reports\Data\Endpoint::set_view()
	 */
	public function test_set_view_with_valid_view_should_set_that_view() {
		$endpoint = new Tile_Endpoint( array() );

		$this->assertSame( 'tile', $endpoint->get_view() );
	}

}
