<?php
namespace EDD\Tests\Data\Charts\V2;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Pie_Dataset class
 *
 * @group edd_reports
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Pie_Dataset
 */
class Pie_Dataset_Tests extends EDD_UnitTestCase {

	/**
	 * @covers ::$fields
	 */
	public function test_default_fields() {
		$expected = array(
			'hoverBackgroundColor', 'hoverBorderColor',
			'hoverBorderWidth'
		);

		$pie_dataset = $this->getMockBuilder( 'EDD\\Reports\\Data\\Charts\\v2\\Pie_Dataset' )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->assertEqualSets( $expected, $pie_dataset->get_fields() );
	}

}
