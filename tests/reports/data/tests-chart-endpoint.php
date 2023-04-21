<?php
namespace EDD\Tests\Reports\Data;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();
use EDD\Reports\Data\Chart_Endpoint;

/**
 * Tests for the Chart_Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 *
 * @coversDefaultClass \EDD\Reports\Data\Chart_Endpoint
 */
class Chart_Endpoint_Tests extends EDD_UnitTestCase {

	/**
	 * @covers ::check_view()
	 */
	public function test_check_view_with_valid_view_should_set_that_view() {
		$endpoint = new Chart_Endpoint( array() );

		$this->assertSame( 'chart', $endpoint->get_view() );
	}

}
