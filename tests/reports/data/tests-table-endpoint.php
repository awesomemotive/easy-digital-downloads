<?php
namespace EDD\Tests\Reports\Data;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
use EDD\Reports\Data\Table_Endpoint;
new ReportsInit();

/**
 * Tests for the Tile_Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 *
 * @coversDefaultClass \EDD\Reports\Data\Table_Endpoint
 */
class Table_Endpoint_Tests extends EDD_UnitTestCase {

	/**
	 * @covers ::check_view()
	 */
	public function test_check_view_with_valid_view_should_set_that_view() {
		$endpoint = new Table_Endpoint( array() );

		$this->assertSame( 'table', $endpoint->get_view() );
	}

}
