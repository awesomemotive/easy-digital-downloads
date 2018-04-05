<?php
namespace EDD\Reports\Data;

if ( ! class_exists( '\EDD\Reports\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

new \EDD\Reports\Init();

/**
 * Tests for the Tile_Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 *
 * @coversDefaultClass \EDD\Reports\Data\Tile_Endpoint
 */
class Tile_Endpoint_Tests extends \EDD_UnitTestCase {

	/**
	 * @covers ::check_view()
	 */
	public function test_check_view_with_valid_view_should_set_that_view() {
		$endpoint = new Tile_Endpoint( array() );

		$this->assertSame( 'tile', $endpoint->get_view() );
	}

}
