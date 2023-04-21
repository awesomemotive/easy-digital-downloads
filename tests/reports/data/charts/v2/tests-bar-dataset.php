<?php
namespace EDD\Tests\Data\Charts\V2;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Init as ReportsInit;
new ReportsInit();

/**
 * Tests for the Bar_Dataset class
 *
 * @group edd_reports
 * @group edd_reports_charts
 *
 * @coversDefaultClass \EDD\Reports\Data\Charts\v2\Bar_Dataset
 */
class Bar_Dataset_Tests extends EDD_UnitTestCase {

	/**
	 * @covers ::$fields
	 */
	public function test_default_fields() {
		$expected = array(
			'borderSkipped', 'hoverBackgroundColor',
			'hoverBorderColor', 'hoverBorderWidth'
		);

		$bar_dataset = $this->getMockBuilder( 'EDD\\Reports\\Data\\Charts\\v2\\Bar_Dataset' )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->assertEqualSets( $expected, $bar_dataset->get_fields() );
	}

}
